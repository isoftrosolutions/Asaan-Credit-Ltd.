<?php
require __DIR__ . '/../config/bootstrap.php';

if (current_user()) {
    redirect('/dashboard');
}

$token = $_GET['token'] ?? '';

if ($token === '') {
    flash_set('error', 'Invalid password reset link.');
    redirect('/login');
}

$tokenHash = reset_token_hash($token);

$stmt = db()->prepare('SELECT email, created_at FROM password_reset_tokens WHERE token = ? AND type = ? LIMIT 1');
$stmt->execute([$tokenHash, 'password']);
$row = $stmt->fetch();

if (!$row) {
    flash_set('error', 'Invalid or expired password reset link.');
    redirect('/login');
}

$expires = strtotime($row['created_at']) + 86400;
if (time() > $expires) {
    $stmt = db()->prepare('DELETE FROM password_reset_tokens WHERE token = ? AND type = ?');
    $stmt->execute([$tokenHash, 'password']);
    flash_set('error', 'Password reset link has expired. Please request a new one.');
    redirect('/forgot-password');
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        db()->beginTransaction();

        $stmt = db()->prepare('UPDATE users SET password = ? WHERE email = ?');
        $stmt->execute([$hash, $row['email']]);

        $stmt = db()->prepare('DELETE FROM password_reset_tokens WHERE email = ? AND type = ?');
        $stmt->execute([$row['email'], 'password']);

        db()->commit();

        // Clear any lockout so the user can log in immediately with the new password.
        db()->prepare('UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE email = ?')
            ->execute([$row['email']]);

        $success = true;
    }
}

$pageTitle = 'Reset Password — ' . APP_NAME;
$pageDescription = 'Reset your password for Asaan Capital Ltd.';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<style>
.auth-container-narrow { max-width: 460px; margin: 4rem auto; padding: 2.5rem; background: var(--color-bg); border-radius: 2.5rem; box-shadow: 0 10px 40px -15px rgba(0,0,0,0.08); }
</style>
<div class="auth-container-narrow">
    <div class="auth-header">
        <h2 style="margin-bottom:0.25rem;">Set new password</h2>
        <p style="color:var(--color-text-muted);font-size:0.95rem;">Choose a strong password for your account</p>
    </div>

    <?php if ($success): ?>
        <meta http-equiv="refresh" content="3;url=/login">
        <div class="flash flash-success" style="margin-bottom:1rem;">Password reset successful! Redirecting you to log in…</div>
        <div style="text-align:center;margin-top:1rem;">
            <a href="/login" class="btn btn-primary" style="display:inline-block;">Log in now</a>
        </div>
    <?php else: ?>
        <form method="post" action="/reset-password?token=<?= e($token) ?>">
            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

            <?php if ($error): ?>
                <div class="flash flash-error" style="margin-bottom:1rem;"><?= e($error) ?></div>
            <?php endif; ?>

            <div class="input-group">
                <label>New password</label>
                <input type="password" name="password" class="input" placeholder="Min 8 characters" minlength="8" required autofocus>
                <div style="font-size:0.75rem;color:var(--color-text-muted);margin-top:4px;">Min 8 characters</div>
            </div>

            <div class="input-group">
                <label>Confirm new password</label>
                <input type="password" name="confirm_password" class="input" placeholder="Repeat password" minlength="8" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;">Reset Password</button>
        </form>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
