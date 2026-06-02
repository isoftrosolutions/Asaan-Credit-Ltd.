<?php
require __DIR__ . '/../config/bootstrap.php';

if (current_user()) {
    redirect('/dashboard');
}

$sent = false;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $email = trim($_POST['email'] ?? '');

    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $stmt = db()->prepare('SELECT created_at FROM password_reset_tokens WHERE email = ? AND type = ? ORDER BY created_at DESC LIMIT 1');
            $stmt->execute([$email, 'password']);
            $last = $stmt->fetchColumn();
            $throttled = $last && (time() - strtotime($last)) < 60;

            if (!$throttled) {
                $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                $stmt = db()->prepare('DELETE FROM password_reset_tokens WHERE email = ? AND type = ?');
                $stmt->execute([$email, 'password']);

                $stmt = db()->prepare('INSERT INTO password_reset_tokens (email, token, type, created_at) VALUES (?, ?, ?, ?)');
                $stmt->execute([$email, reset_token_hash($otp), 'password', date('Y-m-d H:i:s')]);

                send_password_reset_email($email, $otp);
            }
        }
    }

    $sent = true;
}

$pageTitle = 'Forgot Password — ' . APP_NAME;
$pageDescription = 'Reset your password for Asaan Capital Ltd. Enter your email to receive a one-time code.';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<style>
.auth-container-narrow { max-width: 460px; margin: 4rem auto; padding: 2.5rem; background: var(--color-bg); border-radius: 2.5rem; box-shadow: 0 10px 40px -15px rgba(0,0,0,0.08); }
</style>
<div class="auth-container-narrow">
    <div class="auth-header">
        <h2 style="margin-bottom:0.25rem;">Reset your password</h2>
        <p style="color:var(--color-text-muted);font-size:0.95rem;">Enter your email to receive a one-time code</p>
    </div>

    <?php if ($sent): ?>
        <div class="flash flash-success" style="margin-bottom:1rem;">
            If an account with that email exists, a one-time code has been sent. Please check your email.
        </div>
        <div style="text-align:center;">
            <a href="/reset-password?email=<?= e(urlencode($email)) ?>" class="btn btn-primary" style="display:inline-block;">Enter Code</a>
        </div>
        <div style="text-align:center;margin-top:1rem;">
            <a href="/login" style="color:var(--color-primary-vivid);">Back to login</a>
        </div>
    <?php else: ?>
        <form method="post" action="/forgot-password">
            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

            <div class="input-group">
                <label>Email address</label>
                <input type="email" name="email" class="input" value="<?= e(old('email')) ?>" placeholder="you@example.com" required autofocus>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;">Send OTP Code</button>
        </form>

        <div style="margin-top:1.5rem;text-align:center;font-size:0.9rem;">
            Remember your password? <a href="/login" style="color:var(--color-primary-vivid);font-weight:600;">Log in</a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
