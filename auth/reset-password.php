<?php
require __DIR__ . '/../config/bootstrap.php';

if (current_user()) {
    redirect('/dashboard');
}

$error = '';
$success = false;

$email = trim($_GET['email'] ?? ($_POST['email'] ?? ''));
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_set('error', 'Invalid or missing email address.');
    redirect('/forgot-password');
}

// Fetch user name for display
$userName = 'User';
$stmt = db()->prepare('SELECT name FROM users WHERE email = ?');
$stmt->execute([$email]);
$userRow = $stmt->fetch();
$userName = $userRow ? $userRow['name'] : '';
// If email does not exist in users table, redirect
if (!$userRow) {
    flash_set('error', 'No account found with that email.');
    redirect('/forgot-password');
}

// Session-based OTP state
$otpVerified = $_SESSION['otp_verified_email'] ?? null;
$otpAttempts = $_SESSION['otp_attempts'] ?? 0;

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    csrf_check();

    $submittedOtp = trim($_POST['otp']);

    if ($otpAttempts >= 3) {
        $stmt = db()->prepare('DELETE FROM password_reset_tokens WHERE email = ? AND type = ?');
        $stmt->execute([$email, 'password']);
        $_SESSION['otp_attempts'] = 0;
        $error = 'Too many failed attempts. Please request a new code.';
    } else {
        $stmt = db()->prepare('SELECT token, created_at FROM password_reset_tokens WHERE email = ? AND type = ? LIMIT 1');
        $stmt->execute([$email, 'password']);
        $row = $stmt->fetch();

        if (!$row) {
            $error = 'No reset request found. Please request a new code.';
        } elseif (time() > strtotime($row['created_at']) + 600) {
            $stmt = db()->prepare('DELETE FROM password_reset_tokens WHERE email = ? AND type = ?');
            $stmt->execute([$email, 'password']);
            $error = 'Code has expired. Please request a new one.';
        } elseif (!hash_equals($row['token'], reset_token_hash($submittedOtp))) {
            $_SESSION['otp_attempts'] = ++$otpAttempts;
            $remaining = 3 - $otpAttempts;
            $error = $remaining > 0
                ? "Invalid code. $remaining attempt(s) remaining."
                : 'Too many failed attempts. Please request a new code.';
            if ($otpAttempts >= 3) {
                $stmt = db()->prepare('DELETE FROM password_reset_tokens WHERE email = ? AND type = ?');
                $stmt->execute([$email, 'password']);
            }
        } else {
            $_SESSION['otp_verified_email'] = $email;
            $_SESSION['otp_attempts'] = 0;
        }
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    csrf_check();

    if ($_SESSION['otp_verified_email'] ?? null !== $email) {
        $error = 'Please verify your OTP code first.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);

            db()->beginTransaction();
            $stmt = db()->prepare('UPDATE users SET password = ? WHERE email = ?');
            $stmt->execute([$hash, $email]);
            $stmt = db()->prepare('DELETE FROM password_reset_tokens WHERE email = ? AND type = ?');
            $stmt->execute([$email, 'password']);
            db()->commit();

            db()->prepare('UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE email = ?')
                ->execute([$email]);

            unset($_SESSION['otp_verified_email'], $_SESSION['otp_attempts']);

            $success = true;
        }
    }
}

$otpVerified = ($_SESSION['otp_verified_email'] ?? null) === $email;

$pageTitle = 'Reset Password — ' . APP_NAME;
$pageDescription = 'Reset your password for Asaan Capital Ltd.';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<style>
.auth-container-narrow { max-width: 460px; margin: 4rem auto; padding: 2.5rem; background: var(--color-bg); border-radius: 2.5rem; box-shadow: 0 10px 40px -15px rgba(0,0,0,0.08); }
</style>
<div class="auth-container-narrow">
    <?php if ($success): ?>
        <meta http-equiv="refresh" content="3;url=/login">
        <div class="auth-header">
            <h2 style="margin-bottom:0.25rem;">Password changed</h2>
        </div>
        <div class="flash flash-success" style="margin-bottom:1rem;">Password reset successful! Redirecting you to log in…</div>
        <div style="text-align:center;margin-top:1rem;">
            <a href="/login" class="btn btn-primary" style="display:inline-block;">Log in now</a>
        </div>
    <?php elseif ($otpVerified): ?>
        <div class="auth-header">
            <h2 style="margin-bottom:0.25rem;">Set new password</h2>
            <p style="color:var(--color-text-muted);font-size:0.95rem;">Choose a strong password for <?= e($email) ?></p>
        </div>
        <?php if ($error): ?>
            <div class="flash flash-error" style="margin-bottom:1rem;"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/reset-password?email=<?= e(urlencode($email)) ?>">
            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="email" value="<?= e($email) ?>">

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
    <?php else: ?>
        <div class="auth-header">
            <h2 style="margin-bottom:0.25rem;">Enter verification code</h2>
            <p style="color:var(--color-text-muted);font-size:0.95rem;">We sent a 6-digit code to <?= e($email) ?></p>
        </div>
        <?php if ($error): ?>
            <div class="flash flash-error" style="margin-bottom:1rem;"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/reset-password?email=<?= e(urlencode($email)) ?>">
            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="email" value="<?= e($email) ?>">

            <div class="input-group">
                <label>One-time code</label>
                <input type="text" name="otp" class="input" placeholder="000000" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" required autofocus style="font-size:1.5rem;text-align:center;letter-spacing:6px;font-family:monospace;">
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;">Verify Code</button>
        </form>
        <div style="margin-top:1.5rem;text-align:center;font-size:0.9rem;">
            <a href="/forgot-password" style="color:var(--color-primary-vivid);">Request a new code</a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>