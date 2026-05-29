<?php
require __DIR__ . '/../config/bootstrap.php';

if (current_user()) {
    redirect('/dashboard');
}

$error = '';
$email = $_POST['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Invalid credentials.';
        } elseif ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $error = 'Account temporarily locked. Try again after 15 minutes.';
        } elseif (!password_verify($password, $user['password'])) {
            $attempts = (int)$user['failed_login_attempts'] + 1;
            if ($attempts >= 5) {
                db()->prepare('UPDATE users SET failed_login_attempts = ?, locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id = ?')->execute([$attempts, $user['id']]);
                $error = 'Account locked due to too many failed attempts. Try again after 15 minutes.';
            } else {
                db()->prepare('UPDATE users SET failed_login_attempts = ? WHERE id = ?')->execute([$attempts, $user['id']]);
                $error = 'Invalid credentials.';
            }
        } elseif (empty($user['is_admin'])) {
            $error = 'Access denied. Admin privileges required.';
        } else {
            db()->prepare('UPDATE users SET failed_login_attempts = 0, locked_until = NULL, last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);
            session_regenerate_id(true);
            unset($user['password']);
            $_SESSION['user'] = $user;
            admin_log('admin_login', 'user', $user['id']);
            redirect('/admin');
        }
    }
}

$pageTitle = 'Admin Login';
require __DIR__ . '/../includes/header.php';
?>
<div class="container" style="padding-top:4rem;max-width:440px;margin:0 auto;">
  <div class="card" style="padding:2rem;">
    <h2 style="margin-bottom:0.25rem;">Admin Login</h2>
    <p style="color:#666;margin-bottom:1.5rem;">Sign in with your admin credentials</p>
    <?php if ($error): ?>
      <div class="flash flash-error"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="input-group">
        <label>Email</label>
        <input type="email" name="email" class="input" value="<?= e($email) ?>" required autocomplete="email">
      </div>
      <div class="input-group">
        <label>Password</label>
        <input type="password" name="password" class="input" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem;">Sign In</button>
    </form>
    <p style="margin-top:1rem;font-size:0.85rem;text-align:center;">
      <a href="<?= APP_URL ?>/login" style="color:#C41E3A;">Back to main site</a>
    </p>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
