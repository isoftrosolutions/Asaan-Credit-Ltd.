<?php
require __DIR__ . '/../config/bootstrap.php';

if (current_user()) {
    redirect('/dashboard');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
        $_SESSION['_old'] = ['email' => $email];
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Invalid email or password.';
            $_SESSION['_old'] = ['email' => $email];
        } elseif (!empty($user['is_suspended'])) {
            $error = 'Your account has been suspended. Contact support for assistance.';
            $_SESSION['_old'] = ['email' => $email];
        } elseif ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $minutes = ceil((strtotime($user['locked_until']) - time()) / 60);
            $error = 'Account temporarily locked. Try again in ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '') . '.';
            $_SESSION['_old'] = ['email' => $email];
        } elseif (!password_verify($password, $user['password'])) {
            $attempts = (int)$user['failed_login_attempts'] + 1;
            $lockUntil = null;
            if ($attempts >= 5) {
                $lockUntil = date('Y-m-d H:i:s', time() + 900);
            }
            $stmt = db()->prepare('UPDATE users SET failed_login_attempts = ?, locked_until = ? WHERE id = ?');
            $stmt->execute([$attempts, $lockUntil, $user['id']]);
            $error = 'Invalid email or password.';
            $_SESSION['_old'] = ['email' => $email];
        } else {
            $stmt = db()->prepare('UPDATE users SET failed_login_attempts = 0, locked_until = NULL, last_login_at = NOW() WHERE id = ?');
            $stmt->execute([$user['id']]);

            session_regenerate_id(true);
            unset($user['password']);
            $_SESSION['user'] = $user;
            $_SESSION['_created'] = true;

            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $hashedToken = password_hash($token, PASSWORD_BCRYPT);
                $stmt = db()->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
                $stmt->execute([$hashedToken, $user['id']]);
                setcookie('remember', $user['id'] . ':' . $token, time() + 86400 * 30, '/', '', !empty($_SERVER['HTTPS']), true);
            }

            redirect('/dashboard');
        }
    }
}

$pageTitle = 'Log In — ' . APP_NAME;
$pageDescription = 'Log in to your Asaan Capital Ltd account. Access your dashboard, matches, and connections.';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<style>
.auth-container-narrow { max-width: 460px; margin: 4rem auto; padding: 2.5rem; background: var(--color-bg); border-radius: 2.5rem; box-shadow: 0 10px 40px -15px rgba(0,0,0,0.08); }
</style>
<div class="auth-container-narrow">
    <div class="auth-header">
        <h2 style="margin-bottom:0.25rem;">Welcome back</h2>
        <p style="color:var(--color-text-muted);font-size:0.95rem;">Sign in to access your dashboard and matches</p>
    </div>

    <form method="post" action="/login">
        <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

        <?php if ($error): ?>
            <div class="flash flash-error" style="margin-bottom:1rem;"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="input-group">
            <label>Email address</label>
            <input type="email" name="email" class="input" value="<?= e(old('email')) ?>" placeholder="you@example.com" required autofocus>
        </div>

        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" class="input" placeholder="Enter your password" required>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.85rem;margin-bottom:1.5rem;">
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                <input type="checkbox" name="remember" style="accent-color:var(--color-primary-vivid);"> Remember me
            </label>
            <a href="/forgot-password" style="color:var(--color-primary-vivid);text-decoration:none;">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;">Log in</button>
    </form>

    <div style="margin-top:1.5rem;text-align:center;font-size:0.9rem;">
        Don't have an account? <a href="/signup" style="color:var(--color-primary-vivid);font-weight:600;">Sign up free</a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
