<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$pageTitle = 'Interest Request Log';
require __DIR__ . '/../includes/layout-admin.php';

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
?>
<h2>Interest Request Log</h2>
<div class="card" style="margin-bottom:1rem;">
  <form method="get" style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:flex-end;">
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
    <a href="<?= APP_URL ?>/admin/interest-log" class="btn btn-sm btn-secondary">Clear</a>
    <a href="?export=csv&<?= http_build_query($_GET) ?>" class="btn btn-sm btn-outline">CSV Export</a>
  </form>
</div>
<div class="card">
  <table style="width:100%;">
    <tr style="border-bottom:1px solid #eae8e6;">
      <th style="text-align:left;padding:8px;">ID</th>
      <th style="text-align:left;padding:8px;">Sender</th>
      <th style="text-align:left;padding:8px;">Receiver</th>
      <th style="padding:8px;">Pitch</th>
      <th style="padding:8px;">Status</th>
      <th style="padding:8px;">Created</th>
      <th style="padding:8px;">Responded</th>
    </tr>
    <?php foreach ($requests as $ir): ?>
    <tr style="border-bottom:1px solid #eee;">
      <td style="padding:10px 8px;"><?= $ir['id'] ?></td>
      <td style="padding:10px 8px;font-weight:600;"><?= e($ir['sender_name']) ?><br><small style="color:#666;"><?= e($ir['sender_email']) ?></small></td>
      <td style="padding:10px 8px;font-weight:600;"><?= e($ir['receiver_name']) ?><br><small style="color:#666;"><?= e($ir['receiver_email']) ?></small></td>
      <td style="padding:10px 8px;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($ir['pitch_title'] ?? '—') ?></td>
      <td style="padding:10px 8px;">
        <?php $statusColors = ['pending' => '#d97706', 'accepted' => '#166534', 'rejected' => '#b91c1c', 'withdrawn' => '#666']; ?>
        <span style="color:<?= $statusColors[$ir['status']] ?? '#666' ?>;font-weight:600;"><?= e($ir['status']) ?></span>
      </td>
      <td style="padding:10px 8px;font-size:0.85rem;"><?= date_human($ir['created_at']) ?></td>
      <td style="padding:10px 8px;font-size:0.85rem;"><?= $ir['responded_at'] ? date_human($ir['responded_at']) : '—' ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($requests)): ?>
    <tr><td colspan="7" style="padding:20px;text-align:center;color:#888;">No interest requests found.</td></tr>
    <?php endif; ?>
  </table>
</div>
<?= render_pagination($pagi['page'], $pagi['lastPage'], '/admin/interest-log?' . http_build_query(array_filter(['status' => $statusFilter, 'date_from' => $dateFrom, 'date_to' => $dateTo]))) ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
