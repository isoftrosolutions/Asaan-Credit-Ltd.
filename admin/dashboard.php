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
$pendingBizCount = (int)db()->query("SELECT COUNT(*) FROM businesses WHERE status = 'pending'")->fetchColumn();
$pendingPitchCount = (int)db()->query("SELECT COUNT(*) FROM pitches WHERE is_published = 0")->fetchColumn();
$recentBiz = db()->query("SELECT id, business_name, slug, status, created_at FROM businesses ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentPitches = db()->query("SELECT p.id, p.tagline, p.is_published, p.is_hidden, p.created_at, u.name FROM pitches p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 5")->fetchAll();
$statusLabels = ['draft' => 'Draft', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'sold' => 'Sold'];

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
        ui_quick_action(['title' => 'Manage businesses', 'desc' => 'Approve & manage listings', 'icon' => 'briefcase', 'href' => APP_URL . '/admin/businesses', 'tone' => 'primary']);
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
    <div class="dash-panel dash-panel-pad">
      <div class="dash-def-label">Pending approvals</div>
      <div class="dash-stat-value" style="margin-top:6px;"><?= number_format($pendingBizCount) ?> businesses</div>
      <div class="dash-stat-value" style="margin-top:2px;font-size:1rem;"><?= number_format($pendingPitchCount) ?> pitches</div>
      <?php if ($pendingBizCount > 0 || $pendingPitchCount > 0): ?>
        <div style="margin-top:var(--space-3);display:flex;gap:8px;">
          <a href="<?= APP_URL ?>/admin/businesses" class="btn btn-sm btn-primary">Review businesses</a>
          <a href="<?= APP_URL ?>/admin/pitches" class="btn btn-sm btn-outline">Review pitches</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if (!empty($recentBiz) || !empty($recentPitches)): ?>
<div class="dash-cols" style="margin-top:var(--space-8);">
  <?php if (!empty($recentBiz)): ?>
  <div class="dash-panel" style="flex:1;">
    <div class="dash-panel-head">
      <span class="dash-panel-title">Recent Businesses</span>
      <a href="<?= APP_URL ?>/admin/businesses" class="dash-section-link">Manage <?php ui_icon('arrowRight'); ?></a>
    </div>
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead><tr><th>Name</th><th class="ta-center">Status</th><th class="ta-right">Date</th></tr></thead>
        <tbody>
        <?php foreach ($recentBiz as $b): ?>
          <tr>
            <td>
              <span class="t-strong"><?= e($b['business_name']) ?></span>
            </td>
            <td class="ta-center"><span class="dash-pill <?= $b['status'] === 'approved' ? 'published' : ($b['status'] === 'pending' ? 'pending' : 'draft') ?>"><?= $statusLabels[$b['status']] ?? $b['status'] ?></span></td>
            <td class="ta-right t-muted"><?= date_human($b['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
  <?php if (!empty($recentPitches)): ?>
  <div class="dash-panel" style="flex:1;">
    <div class="dash-panel-head">
      <span class="dash-panel-title">Recent Pitches</span>
      <a href="<?= APP_URL ?>/admin/pitches" class="dash-section-link">Manage <?php ui_icon('arrowRight'); ?></a>
    </div>
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead><tr><th>Tagline</th><th>By</th><th class="ta-center">Status</th><th class="ta-right">Date</th></tr></thead>
        <tbody>
        <?php foreach ($recentPitches as $p): ?>
          <tr>
            <td><span class="t-strong"><?= e(mb_substr($p['tagline'], 0, 40)) ?></span></td>
            <td class="t-muted"><?= e($p['name']) ?></td>
            <td class="ta-center">
              <?php if (!$p['is_published']): ?><span class="dash-pill draft">Draft</span>
              <?php elseif ($p['is_hidden']): ?><span class="dash-pill draft">Hidden</span>
              <?php else: ?><span class="dash-pill published">Published</span>
              <?php endif; ?>
            </td>
            <td class="ta-right t-muted"><?= date_human($p['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
