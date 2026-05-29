<?php
require __DIR__ . '/../config/bootstrap.php';

if (current_user()) {
    redirect('/dashboard');
}

$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $email = trim($_POST['email'] ?? '');

    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));

            $stmt = db()->prepare('DELETE FROM password_reset_tokens WHERE email = ? AND type = ?');
            $stmt->execute([$email, 'password']);

            $stmt = db()->prepare('INSERT INTO password_reset_tokens (email, token, type, created_at) VALUES (?, ?, ?, ?)');
            $stmt->execute([$email, $token, 'password', date('Y-m-d H:i:s')]);

            send_password_reset_email($email, $token);
        }
    }

    $sent = true;
}

$pageTitle = 'Forgot Password — ' . APP_NAME;
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<style>
.auth-container-narrow { max-width: 460px; margin: 4rem auto; padding: 2.5rem; background: white; border-radius: 2.5rem; box-shadow: 0 10px 40px -15px rgba(0,0,0,0.08); }
</style>
<div class="auth-container-narrow">
    <div class="auth-header">
        <h2 style="margin-bottom:0.25rem;">Reset your password</h2>
        <p style="color:#666;font-size:0.95rem;">Enter your email and we'll send you a reset link</p>
    </div>

    <?php if ($sent): ?>
        <div class="flash flash-success" style="margin-bottom:1rem;">
            If an account with that email exists, a password reset link has been sent. Please check your email.
        </div>
        <div style="text-align:center;margin-top:1rem;">
            <a href="/login" style="color:#C41E3A;">Back to login</a>
        </div>
    <?php else: ?>
        <form method="post" action="/forgot-password">
            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

            <div class="input-group">
                <label>Email address</label>
                <input type="email" name="email" class="input" value="<?= e(old('email')) ?>" placeholder="you@example.com" required autofocus>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;">Send Reset Link</button>
        </form>

        <div style="margin-top:1.5rem;text-align:center;font-size:0.9rem;">
            Remember your password? <a href="/login" style="color:#C41E3A;font-weight:600;">Log in</a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
