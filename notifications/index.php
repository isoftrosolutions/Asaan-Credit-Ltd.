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

$nActions = '';
if ($unreadCount > 0) {
    $nActions .= '<form method="post" action="' . APP_URL . '/notifications/mark-read" style="display:inline;">'
        . '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . csrf_token() . '">'
        . '<input type="hidden" name="all" value="1">'
        . '<button type="submit" class="btn btn-sm btn-outline">Mark all read</button></form>';
}
$nActions .= '<a href="' . APP_URL . '/notifications/settings" class="btn btn-sm btn-outline">' . ui_icon_str('settings') . ' Settings</a>';

ui_page_header('Notifications', $unreadCount > 0 ? '<strong>' . $unreadCount . '</strong> unread' : 'You&rsquo;re all caught up.', $nActions);
?>

<?php if (empty($all)): ?>
  <div class="dash-panel">
    <?php ui_empty_state(['icon' => 'bell', 'title' => 'No notifications yet', 'text' => 'Updates about matches and interest requests will appear here.']); ?>
  </div>
<?php else: ?>
  <div class="dash-panel dash-list">
    <?php foreach ($all as $n): ?>
      <div class="dash-listrow<?= $n['is_read'] ? '' : ' unread' ?>" style="flex-wrap:wrap;align-items:flex-start;">
        <div class="dash-listrow-main">
          <div class="dash-listrow-title"><?= e($n['title']) ?></div>
          <?php if ($n['body']): ?><div class="dash-listrow-sub"><?= e($n['body']) ?></div><?php endif; ?>
          <?php if ($n['action_url']): ?><div style="margin-top:var(--space-3);"><a href="<?= e($n['action_url']) ?>" class="btn btn-sm btn-primary">View</a></div><?php endif; ?>
        </div>
        <div class="dash-listrow-actions" style="flex-direction:column;align-items:flex-end;gap:6px;">
          <span class="dash-tl-time"><?= date_human($n['created_at']) ?></span>
          <?php if (!$n['is_read']): ?>
            <form method="post" action="<?= APP_URL ?>/notifications/mark-read">
              <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
              <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
              <button type="submit" class="btn btn-sm btn-ghost">Mark read</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
