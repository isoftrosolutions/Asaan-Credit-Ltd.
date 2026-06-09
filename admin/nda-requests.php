<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$statusFilter = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where = ['1=1'];
$params = [];
if ($statusFilter) { $where[] = 'nr.signed = ?'; $params[] = $statusFilter === 'signed' ? 1 : 0; }

$whereClause = implode(' AND ', $where);
$countStmt = db()->prepare("SELECT COUNT(*) FROM nda_requests nr JOIN users u ON u.id = nr.investor_id JOIN businesses b ON b.id = nr.business_id WHERE $whereClause");
$countStmt->execute($params);
$pagi = paginate($countStmt, $page, $perPage);

$stmt = db()->prepare("SELECT nr.*, u.name AS investor_name, u.email AS investor_email, b.business_name, b.slug AS business_slug FROM nda_requests nr JOIN users u ON u.id = nr.investor_id JOIN businesses b ON b.id = nr.business_id WHERE $whereClause ORDER BY nr.signed_at DESC LIMIT {$pagi['perPage']} OFFSET {$pagi['offset']}");
$stmt->execute($params);
$requests = $stmt->fetchAll();

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="nda-requests-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Investor', 'Business', 'Signed', 'Signed At']);
    foreach ($requests as $nr) {
        fputcsv($out, [$nr['id'], $nr['investor_email'], $nr['business_name'], $nr['signed'] ? 'Yes' : 'No', $nr['signed_at'] ?? '']);
    }
    fclose($out);
    exit;
}

$pageTitle = 'NDA Requests';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('NDA Requests', 'Investors who have signed NDAs to access business documents.');
?>
<form method="get" class="dash-filterbar">
  <div class="input-group">
    <label>Status</label>
    <select name="status" class="select">
      <option value="">All</option>
      <option value="signed" <?= $statusFilter === 'signed' ? 'selected' : '' ?>>Signed</option>
      <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
    </select>
  </div>
  <button type="submit" class="btn btn-sm btn-primary">Filter</button>
  <a href="<?= APP_URL ?>/admin/nda-requests" class="btn btn-sm btn-outline">Clear</a>
  <a href="?export=csv&<?= e(http_build_query($_GET)) ?>" class="btn btn-sm btn-outline">CSV export</a>
</form>

<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>ID</th><th>Investor</th><th>Business</th>
        <th class="ta-center">Signed</th><th>Signed At</th>
      </tr></thead>
      <tbody>
      <?php foreach ($requests as $nr): ?>
        <tr>
          <td class="t-muted"><?= $nr['id'] ?></td>
          <td><span class="t-strong"><?= e($nr['investor_name']) ?></span><br><span class="t-muted"><?= e($nr['investor_email']) ?></span></td>
          <td><a href="<?= APP_URL ?>/business/<?= $nr['business_slug'] ?: $nr['business_id'] ?>" class="dash-section-link" target="_blank"><?= e($nr['business_name']) ?></a></td>
          <td class="ta-center"><span class="dash-pill <?= $nr['signed'] ? 'published' : 'draft' ?>"><?= $nr['signed'] ? 'Signed' : 'Pending' ?></span></td>
          <td class="t-muted"><?= $nr['signed_at'] ? date_human($nr['signed_at']) : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($requests)): ?>
        <tr><td colspan="5"><?php ui_empty_state(['icon' => 'document', 'title' => 'No NDA requests found', 'text' => 'NDA requests will appear here when investors sign them.']); ?></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= render_pagination($pagi['page'], $pagi['lastPage'], '/admin/nda-requests?' . http_build_query(array_filter(['status' => $statusFilter]))) ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
