<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

$defaults = [
    'email_interest' => 1,
    'email_match' => 1,
    'email_digest' => 0,
    'email_announcement' => 1,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $prefs = [
        'email_interest' => !empty($_POST['email_interest']) ? 1 : 0,
        'email_match' => !empty($_POST['email_match']) ? 1 : 0,
        'email_digest' => !empty($_POST['email_digest']) ? 1 : 0,
        'email_announcement' => !empty($_POST['email_announcement']) ? 1 : 0,
    ];

    $_SESSION['notification_prefs'] = $prefs;

    flash_set('success', 'Notification preferences updated.');
    redirect('/notifications/settings');
}

$prefs = array_merge($defaults, $_SESSION['notification_prefs'] ?? []);

$pageTitle = 'Notification Settings';
require __DIR__ . '/../includes/layout-dashboard.php';
?>

<div class="settings-page">
    <div class="breadcrumbs">
        <a href="/notifications">Notifications</a> <span>/</span>
        <span>Settings</span>
    </div>

    <h2 style="margin-top:1.5rem;">Notifications &amp; Account Settings</h2>

    <form method="post" action="/notifications/settings">
        <?php $token = csrf_token(); ?>
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $token ?>">

        <div class="card" style="margin-top:1.5rem;">
            <h4>Email Notifications</h4>
            <label style="display:block; margin:12px 0; display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="email_interest" value="1" <?= $prefs['email_interest'] ? 'checked' : '' ?> style="accent-color:var(--accent);"> New interest requests
            </label>
            <label style="display:block; margin:12px 0; display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="email_match" value="1" <?= $prefs['email_match'] ? 'checked' : '' ?> style="accent-color:var(--accent);"> Match confirmations
            </label>
            <label style="display:block; margin:12px 0; display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="email_digest" value="1" <?= $prefs['email_digest'] ? 'checked' : '' ?> style="accent-color:var(--accent);"> Weekly digest of new matches
            </label>
            <label style="display:block; margin:12px 0; display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="email_announcement" value="1" <?= $prefs['email_announcement'] ? 'checked' : '' ?> style="accent-color:var(--accent);"> Platform announcements
            </label>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top:1.5rem;">Save Preferences</button>
    </form>

    <div class="card" style="margin-top:1.5rem;">
        <h4>Security</h4>
        <p style="color:var(--color-text-muted);font-size:0.9rem;margin:8px 0 14px;">Update the password you use to sign in.</p>
        <a href="/change-password" class="btn btn-outline">Change Password</a>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
