<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

$user  = current_user();
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Re-fetch the stored hash (the session copy has the password stripped).
    $stmt = db()->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$user['id']]);
    $hash = $stmt->fetchColumn();

    if ($current === '' || $new === '' || $confirm === '') {
        $error = 'Please fill in all fields.';
    } elseif (!$hash || !password_verify($current, $hash)) {
        $error = 'Your current password is incorrect.';
    } elseif (strlen($new) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match.';
    } elseif (password_verify($new, $hash)) {
        $error = 'New password must be different from your current password.';
    } else {
        $newHash = password_hash($new, PASSWORD_BCRYPT);
        db()->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?')
            ->execute([$newHash, $user['id']]);
        $success = true;
    }
}

$pageTitle = 'Change Password';
require __DIR__ . '/../includes/layout-dashboard.php';
?>

<div class="settings-page">
    <div class="breadcrumbs">
        <a href="/notifications/settings">Settings</a> <span>/</span>
        <span>Change Password</span>
    </div>

    <h2 style="margin-top:1.5rem;">Change Password</h2>

    <?php if ($success): ?>
        <div class="flash flash-success" style="margin-top:1.5rem;">Your password has been updated.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="flash flash-error" style="margin-top:1.5rem;"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="/change-password" style="max-width:460px;" data-password-match>
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">

        <div class="card" style="margin-top:1.5rem;">
            <div class="input-group">
                <label>Current password</label>
                <input type="password" name="current_password" class="input" required autocomplete="current-password" autofocus>
            </div>
            <div class="input-group">
                <label>New password</label>
                <input type="password" name="password" class="input" placeholder="Min 8 characters" minlength="8" required autocomplete="new-password" data-pw-new>
            </div>
            <div class="input-group" style="margin-bottom:0;">
                <label>Confirm new password</label>
                <input type="password" name="confirm_password" class="input" placeholder="Repeat new password" minlength="8" required autocomplete="new-password" data-pw-confirm>
                <div class="pw-match-hint" style="font-size:0.75rem;margin-top:4px;"></div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top:1.5rem;">Update Password</button>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
