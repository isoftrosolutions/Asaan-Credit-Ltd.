<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

$user = current_user();
$userId = (int)$user['id'];
$db = db();

$notifications = $db->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 30');
$notifications->execute([$userId]);
$all = $notifications->fetchAll();

$unreadCount = 0;
foreach ($all as $n) {
    if (!$n['is_read']) {
        $unreadCount++;
    }
}

$pageTitle = 'Notifications';
require __DIR__ . '/../includes/layout-dashboard.php';
?>

<div class="notifications-page">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;">
        <h2>Notifications</h2>
        <div style="display:flex; gap:0.5rem;">
            <?php if ($unreadCount > 0): ?>
                <form method="post" action="/notifications/mark-read" style="display:inline;">
                    <?php $token = csrf_token(); ?>
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $token ?>">
                    <input type="hidden" name="all" value="1">
                    <button type="submit" class="btn btn-sm btn-secondary">Mark all as read</button>
                </form>
            <?php endif; ?>
            <a href="/notifications/settings" class="btn btn-sm btn-secondary">Settings</a>
        </div>
    </div>

    <?php if (empty($all)): ?>
        <div class="card">
            <p style="text-align:center; padding:2rem; color:#888;">No notifications yet.</p>
        </div>
    <?php else: ?>
        <div class="notifications-list">
            <?php foreach ($all as $n): ?>
                <div class="card" style="margin-bottom:0.5rem; <?= $n['is_read'] ? '' : 'border-left:3px solid var(--accent);' ?>">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem;">
                        <div style="flex:1;">
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <strong><?= e($n['title']) ?></strong>
                                <?php if (!$n['is_read']): ?>
                                    <span class="badge badge-accent" style="font-size:0.7rem;">New</span>
                                <?php endif; ?>
                            </div>
                            <p style="margin:0.25rem 0 0; color:#555; font-size:0.9rem;"><?= e($n['body']) ?></p>
                        </div>
                        <div style="text-align:right; flex-shrink:0;">
                            <span style="font-size:0.8rem; color:#999;"><?= date_human($n['created_at']) ?></span>
                            <?php if (!$n['is_read']): ?>
                                <form method="post" action="/notifications/mark-read" style="margin-top:0.25rem;">
                                    <?php $token2 = csrf_token(); ?>
                                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $token2 ?>">
                                    <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                                    <button type="submit" class="btn btn-xs btn-link">Mark read</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($n['action_url']): ?>
                        <div style="margin-top:0.5rem;">
                            <a href="<?= e($n['action_url']) ?>" class="btn btn-sm btn-primary">View</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
