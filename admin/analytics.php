<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$pageTitle = 'Analytics';
require __DIR__ . '/../includes/layout-admin.php';

$userGrowth = db()->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY month ORDER BY month ASC")->fetchAll();

$dealFlow = db()->query("SELECT status, COUNT(*) AS total FROM interest_requests GROUP BY status")->fetchAll();

$topSectors = db()->query("SELECT s.name, COUNT(*) AS total FROM businesses b JOIN sectors s ON s.id = b.sector_id WHERE b.is_published = 1 GROUP BY s.id ORDER BY total DESC LIMIT 10")->fetchAll();

$usersByRole = db()->query("SELECT role, COUNT(*) AS total FROM users GROUP BY role ORDER BY total DESC")->fetchAll();
ui_page_header('Deep Analytics', 'Platform trends across users, deal flow and sectors.');
?>
<div class="r-2" style="gap:var(--space-4);">
  <div class="dash-panel dash-panel-pad">
    <div class="dash-panel-title" style="margin-bottom:var(--space-3);">User growth (12 months)</div>
    <canvas id="userGrowthChart" height="200"></canvas>
  </div>
  <div class="dash-panel dash-panel-pad">
    <div class="dash-panel-title" style="margin-bottom:var(--space-3);">Deal flow by status</div>
    <canvas id="dealFlowChart" height="200"></canvas>
  </div>
  <div class="dash-panel dash-panel-pad">
    <div class="dash-panel-title" style="margin-bottom:var(--space-3);">Top sectors</div>
    <canvas id="topSectorsChart" height="200"></canvas>
  </div>
  <div class="dash-panel dash-panel-pad">
    <div class="dash-panel-title" style="margin-bottom:var(--space-3);">Users by role</div>
    <canvas id="usersByRoleChart" height="200"></canvas>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
<?php
$growthLabels = json_encode(array_column($userGrowth, 'month'));
$growthData = json_encode(array_column($userGrowth, 'total'));
$dealLabels = json_encode(array_column($dealFlow, 'status'));
$dealData = json_encode(array_column($dealFlow, 'total'));
$sectorLabels = json_encode(array_column($topSectors, 'name'));
$sectorData = json_encode(array_column($topSectors, 'total'));
$roleLabels = json_encode(array_column($usersByRole, 'role'));
$roleData = json_encode(array_column($usersByRole, 'total'));
?>
new Chart(document.getElementById('userGrowthChart'), {
  type: 'line',
  data: { labels: <?= $growthLabels ?>, datasets: [{ label: 'New Users', data: <?= $growthData ?>, borderColor: 'var(--color-primary-vivid)', backgroundColor: 'rgba(152,32,42,0.1)', fill: true, tension: 0.3 }] },
  options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
});
new Chart(document.getElementById('dealFlowChart'), {
  type: 'doughnut',
  data: { labels: <?= $dealLabels ?>, datasets: [{ data: <?= $dealData ?>, backgroundColor: ['var(--color-warning)','var(--color-success)','var(--color-error)','var(--color-text-muted)','#2563eb'] }] }
});
new Chart(document.getElementById('topSectorsChart'), {
  type: 'bar',
  data: { labels: <?= $sectorLabels ?>, datasets: [{ label: 'Businesses', data: <?= $sectorData ?>, backgroundColor: 'var(--color-primary-vivid)' }] },
  options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
});
new Chart(document.getElementById('usersByRoleChart'), {
  type: 'pie',
  data: { labels: <?= $roleLabels ?>, datasets: [{ data: <?= $roleData ?>, backgroundColor: ['var(--color-primary-vivid)','#2563eb','var(--color-warning)','var(--color-success)','#7c3aed','#0891b2'] }] }
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
