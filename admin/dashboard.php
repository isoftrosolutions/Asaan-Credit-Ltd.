<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'Admin Dashboard';
require __DIR__ . '/../includes/layout-admin.php';
?>
<h2>Platform Analytics</h2>
<?php
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
?>
<div class="stats-grid" style="margin:1.5rem 0;">
  <div class="stat-card"><div class="stat-value"><?= number_format($stats['total_users']) ?></div><div class="stat-label">Total Users</div></div>
  <div class="stat-card"><div class="stat-value"><?= number_format($stats['total_verified']) ?></div><div class="stat-label">Verified</div></div>
  <div class="stat-card"><div class="stat-value"><?= number_format($stats['total_businesses']) ?></div><div class="stat-label">Businesses</div></div>
  <div class="stat-card"><div class="stat-value"><?= number_format($stats['total_pitches']) ?></div><div class="stat-label">Pitches</div></div>
  <div class="stat-card"><div class="stat-value"><?= number_format($stats['total_franchises']) ?></div><div class="stat-label">Franchises</div></div>
  <div class="stat-card"><div class="stat-value"><?= number_format($stats['total_interest_requests']) ?></div><div class="stat-label">Interest Requests</div></div>
  <div class="stat-card"><div class="stat-value"><?= number_format($stats['total_matches']) ?></div><div class="stat-label">Matches</div></div>
  <div class="stat-card"><div class="stat-value"><?= number_format($stats['total_reports']) ?></div><div class="stat-label">Open Reports</div></div>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;">
  <div class="card">
    <h4>Recent Activity</h4>
    <p style="font-size:1.25rem;font-weight:700;color:var(--color-primary-vivid);"><?= $recentSignups ?></p>
    <p style="color:var(--color-text-muted);font-size:0.85rem;">New users in the last 7 days</p>
  </div>
  <div class="card">
    <h4>Verification Queue</h4>
    <p style="font-size:1.25rem;font-weight:700;color:var(--color-primary-vivid);"><?= $pendingVerification ?></p>
    <p style="color:var(--color-text-muted);font-size:0.85rem;">Pending document verifications</p>
    <?php if ($pendingVerification > 0): ?>
      <a href="<?= APP_URL ?>/admin/verification" class="btn btn-sm btn-primary" style="margin-top:0.5rem;">Review Now</a>
    <?php endif; ?>
  </div>
</div>
<div class="card" style="margin-top:1rem;">
  <h4>Quick Links</h4>
  <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.5rem;">
    <a href="<?= APP_URL ?>/admin/users" class="btn btn-sm btn-secondary">Manage Users</a>
    <a href="<?= APP_URL ?>/admin/pitches" class="btn btn-sm btn-secondary">Moderate Pitches</a>
    <a href="<?= APP_URL ?>/admin/reports" class="btn btn-sm btn-secondary">Reports</a>
    <a href="<?= APP_URL ?>/admin/broadcast" class="btn btn-sm btn-secondary">Send Broadcast</a>
    <a href="<?= APP_URL ?>/admin/analytics" class="btn btn-sm btn-secondary">View Analytics</a>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
