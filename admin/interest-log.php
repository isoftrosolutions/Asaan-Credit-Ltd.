<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$statusFilter = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where = ['1=1'];
$params = [];
if ($statusFilter) { $where[] = 'ir.status = ?'; $params[] = $statusFilter; }
if ($dateFrom) { $where[] = 'ir.created_at >= ?'; $params[] = $dateFrom . ' 00:00:00'; }
if ($dateTo) { $where[] = 'ir.created_at <= ?'; $params[] = $dateTo . ' 23:59:59'; }

$whereClause = implode(' AND ', $where);
$countStmt = db()->prepare("SELECT COUNT(*) FROM interest_requests ir JOIN users s ON s.id = ir.sender_id JOIN users r ON r.id = ir.receiver_id LEFT JOIN pitches p ON p.id = ir.pitch_id WHERE $whereClause");
$countStmt->execute($params);
$pagi = paginate($countStmt, $page, $perPage);

$stmt = db()->prepare("SELECT ir.*, s.name AS sender_name, s.email AS sender_email, r.name AS receiver_name, r.email AS receiver_email, p.tagline AS pitch_title FROM interest_requests ir JOIN users s ON s.id = ir.sender_id JOIN users r ON r.id = ir.receiver_id LEFT JOIN pitches p ON p.id = ir.pitch_id WHERE $whereClause ORDER BY ir.created_at DESC LIMIT {$pagi['perPage']} OFFSET {$pagi['offset']}");
$stmt->execute($params);
$requests = $stmt->fetchAll();

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="interest-requests-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Sender', 'Receiver', 'Pitch', 'Status', 'Created', 'Responded']);
    foreach ($requests as $ir) {
        fputcsv($out, [$ir['id'], $ir['sender_email'], $ir['receiver_email'], $ir['pitch_title'] ?? '—', $ir['status'], $ir['created_at'], $ir['responded_at'] ?? '']);
    }
    fclose($out);
    exit;
}
$pageTitle = 'Interest Request Log';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('Interest Request Log', 'Every interest request across the platform.');
?>
<form method="get" class="dash-filterbar">
  <div class="input-group">
    <label>Status</label>
    <select name="status" class="select">
      <option value="">All</option>
      <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
      <option value="accepted" <?= $statusFilter === 'accepted' ? 'selected' : '' ?>>Accepted</option>
      <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
      <option value="withdrawn" <?= $statusFilter === 'withdrawn' ? 'selected' : '' ?>>Withdrawn</option>
    </select>
  </div>
  <div class="input-group">
    <label>From</label>
    <input type="date" name="date_from" class="input" value="<?= e($dateFrom) ?>">
  </div>
  <div class="input-group">
    <label>To</label>
    <input type="date" name="date_to" class="input" value="<?= e($dateTo) ?>">
  </div>
  <button type="submit" class="btn btn-sm btn-primary">Filter</button>
  <a href="<?= APP_URL ?>/admin/interest-log" class="btn btn-sm btn-outline">Clear</a>
  <a href="?export=csv&<?= e(http_build_query($_GET)) ?>" class="btn btn-sm btn-outline">CSV export</a>
</form>

<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>ID</th><th>Sender</th><th>Receiver</th><th>Pitch</th>
        <th class="ta-center">Status</th><th>Created</th><th>Responded</th>
      </tr></thead>
      <tbody>
      <?php $statusPill = ['pending' => 'open', 'accepted' => 'published', 'rejected' => 'draft', 'withdrawn' => 'draft']; ?>
      <?php foreach ($requests as $ir): ?>
        <tr>
          <td class="t-muted"><?= $ir['id'] ?></td>
          <td><span class="t-strong"><?= e($ir['sender_name']) ?></span><br><span class="t-muted"><?= e($ir['sender_email']) ?></span></td>
          <td><span class="t-strong"><?= e($ir['receiver_name']) ?></span><br><span class="t-muted"><?= e($ir['receiver_email']) ?></span></td>
          <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($ir['pitch_title'] ?? '—') ?></td>
          <td class="ta-center"><span class="dash-pill <?= $statusPill[$ir['status']] ?? 'draft' ?>"><?= e($ir['status']) ?></span></td>
          <td class="t-muted"><?= date_human($ir['created_at']) ?></td>
          <td class="t-muted"><?= $ir['responded_at'] ? date_human($ir['responded_at']) : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($requests)): ?>
        <tr><td colspan="7"><?php ui_empty_state(['icon' => 'share', 'title' => 'No interest requests found', 'text' => 'Try adjusting your filters.']); ?></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= render_pagination($pagi['page'], $pagi['lastPage'], '/admin/interest-log?' . http_build_query(array_filter(['status' => $statusFilter, 'date_from' => $dateFrom, 'date_to' => $dateTo]))) ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
