<?php
/**
 * Admin shell — same PHP chrome as layout-dashboard.php but forces the admin
 * sidebar (ADMIN_LINKS) and "Administrator" role label. Pages emit content then
 * `require includes/footer.php`, which closes the <main>.
 */
require_admin();
$dashChrome = true;
$hidePublicFooter = true;

require __DIR__ . '/header.php';
require __DIR__ . '/ui.php';

$__dashUser = $user ?? current_user() ?? ['name' => 'Admin', 'role' => 'admin'];
$__dashUnread = $unreadCount ?? 0;
?>
<div class="dash-shell">
  <?php
    ui_sidebar($__dashUser, $__dashUnread, true);
    ui_topbar($__dashUser, $__dashUnread);
  ?>
  <main class="dash-main">
