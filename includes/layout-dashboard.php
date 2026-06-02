<?php
/**
 * Dashboard shell (Phase 1 redesign). Renders the chrome in PHP — fixed sidebar
 * + topbar — instead of the JS-injected header. Pages that `require` this file
 * get an open <main class="dash-main"> that includes/footer.php closes.
 *
 * Contract for pages: set $pageTitle before requiring this, emit content, then
 * `require includes/footer.php`. All business logic stays in the page.
 */
$dashChrome = true;          // header.php: skip JS injectHeader; footer.php: close <main>
$hidePublicFooter = true;    // no public marketing footer on dashboards

require __DIR__ . '/header.php';   // <head>, <body>, scripts, (empty) #header-root
require __DIR__ . '/ui.php';

$__dashUser = $user ?? current_user() ?? ['name' => 'User', 'role' => 'investor'];
$__dashUnread = $unreadCount ?? 0;
$__dashIsAdmin = !empty($isAdmin);
?>
<div class="dash-shell">
  <?php
    ui_sidebar($__dashUser, $__dashUnread, $__dashIsAdmin);
    ui_topbar($__dashUser, $__dashUnread);
  ?>
  <main class="dash-main">
