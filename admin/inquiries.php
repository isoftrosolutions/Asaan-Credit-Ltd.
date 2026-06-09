<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$statusFilter = $_GET['status'] ?? '';
$businessFilter = (int)($_GET['business_id'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where = ['1=1'];
$params = [];
if ($statusFilter) { $where[] = 'bi.status = ?'; $params[] = $statusFilter; }
if ($businessFilter) { $where[] = 'bi.business_id = ?'; $params[] = $businessFilter; }

$whereClause = implode(' AND ', $where);
$countStmt = db()->prepare("SELECT COUNT(*) FROM business_inquiries bi JOIN users u ON u.id = bi.user_id JOIN businesses b ON b.id = bi.business_id WHERE $whereClause");
$countStmt->execute($params);
$pagi = paginate($countStmt, $page, $perPage);

$stmt = db()->prepare("SELECT bi.*, u.name AS investor_name, u.email AS investor_email, b.business_name, b.slug AS business_slug FROM business_inquiries bi JOIN users u ON u.id = bi.user_id JOIN businesses b ON b.id = bi.business_id WHERE $whereClause ORDER BY bi.created_at DESC LIMIT {$pagi['perPage']} OFFSET {$pagi['offset']}");
$stmt->execute($params);
$inquiries = $stmt->fetchAll();

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="business-inquiries-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Investor', 'Business', 'Message', 'Status', 'Created']);
    foreach ($inquiries as $inq) {
        fputcsv($out, [$inq['id'], $inq['investor_email'], $inq['business_name'], $inq['message'], $inq['status'], $inq['created_at']]);
    }
    fclose($out);
    exit;
}

$pageTitle = 'Business Inquiries';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('Business Inquiries', 'All contact requests sent to business sellers.');
?>
<form method="get" class="dash-filterbar">
  <div class="input-group">
    <label>Status</label>
    <select name="status" class="select">
      <option value="">All</option>
      <option value="new" <?= $statusFilter === 'new' ? 'selected' : '' ?>>New</option>
      <option value="read" <?= $statusFilter === 'read' ? 'selected' : '' ?>>Read</option>
      <option value="replied" <?= $statusFilter === 'replied' ? 'selected' : '' ?>>Replied</option>
      <option value="archived" <?= $statusFilter === 'archived' ? 'selected' : '' ?>>Archived</option>
    </select>
  </div>
  <div class="input-group">
    <label>Business ID</label>
    <input type="number" name="business_id" class="input" style="width:100px;" value="<?= $businessFilter ?>">
  </div>
  <button type="submit" class="btn btn-sm btn-primary">Filter</button>
  <a href="<?= APP_URL ?>/admin/inquiries" class="btn btn-sm btn-outline">Clear</a>
  <a href="?export=csv&<?= e(http_build_query($_GET)) ?>" class="btn btn-sm btn-outline">CSV export</a>
</form>

<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>ID</th><th>Investor</th><th>Business</th><th>Message</th>
        <th class="ta-center">Status</th><th>Created</th><th class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
      <?php $statusPill = ['new' => 'open', 'read' => 'published', 'replied' => 'published', 'archived' => 'draft']; ?>
      <?php foreach ($inquiries as $inq): ?>
        <tr>
          <td class="t-muted"><?= $inq['id'] ?></td>
          <td><span class="t-strong"><?= e($inq['investor_name']) ?></span><br><span class="t-muted"><?= e($inq['investor_email']) ?></span></td>
          <td><a href="<?= APP_URL ?>/business/<?= $inq['business_slug'] ?: $inq['business_id'] ?>" class="dash-section-link" target="_blank"><?= e($inq['business_name']) ?></a></td>
          <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($inq['message']) ?>"><?= e($inq['message']) ?></td>
          <td class="ta-center"><span class="dash-pill <?= $statusPill[$inq['status']] ?? 'draft' ?>"><?= e($inq['status']) ?></span></td>
          <td class="t-muted"><?= date_human($inq['created_at']) ?></td>
          <td class="ta-right">
            <span class="dash-table-actions">
              <?php if ($inq['status'] === 'new'): ?>
              <form method="post" action="<?= APP_URL ?>/admin/inquiry-action" style="display:inline;">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="id" value="<?= $inq['id'] ?>">
                <input type="hidden" name="action" value="mark_read">
                <button type="submit" class="btn btn-sm btn-outline">Mark Read</button>
              </form>
              <?php endif; ?>
              <form method="post" action="<?= APP_URL ?>/admin/inquiry-action" style="display:inline;" onsubmit="return confirm('Archive this inquiry?')">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="id" value="<?= $inq['id'] ?>">
                <input type="hidden" name="action" value="archive">
                <button type="submit" class="btn btn-sm btn-outline">Archive</button>
              </form>
            </span>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($inquiries)): ?>
        <tr><td colspan="7"><?php ui_empty_state(['icon' => 'mail', 'title' => 'No inquiries found', 'text' => 'Try adjusting your filters.']); ?></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= render_pagination($pagi['page'], $pagi['lastPage'], '/admin/inquiries?' . http_build_query(array_filter(['status' => $statusFilter, 'business_id' => $businessFilter]))) ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
