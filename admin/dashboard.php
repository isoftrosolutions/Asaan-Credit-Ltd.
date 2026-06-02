<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$stats = [];
$queries = [
    'total_users' => 'SELECT COUNT(*) FROM users',
    'total_verified' => "SELECT COUNT(*) FROM users WHERE verification_status = 'verified'",
    'total_businesses' => 'SELECT COUNT(*) FROM businesses',
    'total_pitches' => 'SELECT COUNT(*) FROM pitches',
    'total_franchises' => 'SELECT COUNT(*) FROM franchises',
    'total_interest_requests' => 'SELECT COUNT(*) FROM interest_requests',
    'total_matches' => 'SELECT COUNT(*) FROM matches',
    'total_reports' => "SELECT COUNT(*) FROM reports WHERE status = 'open'",
];
foreach ($queries as $key => $sql) {
    $stats[$key] = (int)db()->query($sql)->fetchColumn();
}
$recentSignups = (int)db()->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
$pendingVerification = (int)db()->query("SELECT COUNT(*) FROM verification_documents WHERE status = 'pending'")->fetchColumn();

$pageTitle = 'Admin Dashboard';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('Platform Analytics', 'A live snapshot of activity across the marketplace.');
?>

<div class="dash-stats">
  <?php
    ui_stat_card(['label' => 'Total users', 'value' => number_format($stats['total_users']), 'icon' => 'users', 'tone' => 'info']);
    ui_stat_card(['label' => 'Verified', 'value' => number_format($stats['total_verified']), 'icon' => 'check', 'tone' => 'success']);
    ui_stat_card(['label' => 'Businesses', 'value' => number_format($stats['total_businesses']), 'icon' => 'briefcase', 'tone' => 'primary']);
    ui_stat_card(['label' => 'Pitches', 'value' => number_format($stats['total_pitches']), 'icon' => 'chart', 'tone' => 'warning']);
    ui_stat_card(['label' => 'Franchises', 'value' => number_format($stats['total_franchises']), 'icon' => 'tag', 'tone' => 'info']);
    ui_stat_card(['label' => 'Interest requests', 'value' => number_format($stats['total_interest_requests']), 'icon' => 'share', 'tone' => 'success']);
    ui_stat_card(['label' => 'Matches', 'value' => number_format($stats['total_matches']), 'icon' => 'matches', 'tone' => 'primary']);
    ui_stat_card(['label' => 'Open reports', 'value' => number_format($stats['total_reports']), 'icon' => 'lock', 'tone' => $stats['total_reports'] > 0 ? 'warning' : 'success']);
  ?>
</div>

<div class="dash-cols" style="margin-top:var(--space-8);">
  <div style="display:flex;flex-direction:column;gap:var(--space-4);">
    <div class="dash-qa-grid">
      <?php
        ui_quick_action(['title' => 'Manage users', 'desc' => 'View & moderate accounts', 'icon' => 'users', 'href' => APP_URL . '/admin/users', 'tone' => 'info']);
        ui_quick_action(['title' => 'Moderate pitches', 'desc' => 'Review listings', 'icon' => 'chart', 'href' => APP_URL . '/admin/pitches', 'tone' => 'primary']);
        ui_quick_action(['title' => 'Reports', 'desc' => 'Handle flagged content', 'icon' => 'lock', 'href' => APP_URL . '/admin/reports', 'tone' => 'warning']);
        ui_quick_action(['title' => 'Send broadcast', 'desc' => 'Message all members', 'icon' => 'share', 'href' => APP_URL . '/admin/broadcast', 'tone' => 'success']);
        ui_quick_action(['title' => 'View analytics', 'desc' => 'Deeper platform metrics', 'icon' => 'chart', 'href' => APP_URL . '/admin/analytics', 'tone' => 'info']);
        ui_quick_action(['title' => 'Verification queue', 'desc' => 'Approve documents', 'icon' => 'document', 'href' => APP_URL . '/admin/verification', 'tone' => 'primary']);
      ?>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:var(--space-4);">
    <div class="dash-panel dash-panel-pad">
      <div class="dash-def-label">New users (last 7 days)</div>
      <div class="dash-stat-value" style="margin-top:6px;"><?= number_format($recentSignups) ?></div>
    </div>
    <div class="dash-panel dash-panel-pad">
      <div class="dash-def-label">Pending verifications</div>
      <div class="dash-stat-value" style="margin-top:6px;"><?= number_format($pendingVerification) ?></div>
      <?php if ($pendingVerification > 0): ?>
        <a href="<?= APP_URL ?>/admin/verification" class="btn btn-sm btn-primary" style="margin-top:var(--space-3);">Review now</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
