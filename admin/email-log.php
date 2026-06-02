<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$statusFilter = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 30;

$where = ['1=1'];
$params = [];
if ($statusFilter) { $where[] = 'el.status = ?'; $params[] = $statusFilter; }
if ($dateFrom) { $where[] = 'el.sent_at >= ?'; $params[] = $dateFrom . ' 00:00:00'; }
if ($dateTo) { $where[] = 'el.sent_at <= ?'; $params[] = $dateTo . ' 23:59:59'; }
if ($search) { $where[] = '(el.recipient LIKE ? OR el.subject LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

$whereClause = implode(' AND ', $where);
$countStmt = db()->prepare("SELECT COUNT(*) FROM email_log el WHERE $whereClause");
$countStmt->execute($params);
$pagi = paginate($countStmt, $page, $perPage);

$stmt = db()->prepare("SELECT el.*, u.name AS admin_name FROM email_log el LEFT JOIN users u ON u.id = el.sent_by WHERE $whereClause ORDER BY el.sent_at DESC LIMIT {$pagi['perPage']} OFFSET {$pagi['offset']}");
$stmt->execute($params);
$logs = $stmt->fetchAll();

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="email-log-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Recipient', 'Subject', 'Template', 'Status', 'Error', 'Sent By', 'Sent At']);
    foreach ($logs as $l) {
        fputcsv($out, [$l['id'], $l['recipient'], $l['subject'], $l['template_key'] ?? '—', $l['status'], $l['admin_name'] ?? '—', $l['sent_at']]);
    }
    fclose($out);
    exit;
}

$pageTitle = 'Email Log';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('Email Log', 'History of all emails sent from the platform.');
?>
<form class="flex flex-wrap gap-2 items-end" style="margin-bottom:var(--space-6);">
  <div class="input-group" style="flex:1;min-width:180px;">
    <label>Search</label>
    <input type="text" name="search" class="input" placeholder="Recipient or subject…" value="<?= e($search) ?>">
  </div>
  <div class="input-group">
    <label>Status</label>
    <select name="status" class="select">
      <option value="">All</option>
      <option value="sent" <?= $statusFilter === 'sent' ? 'selected' : '' ?>>Sent</option>
      <option value="failed" <?= $statusFilter === 'failed' ? 'selected' : '' ?>>Failed</option>
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
  <a href="<?= APP_URL ?>/admin/email-log" class="btn btn-sm btn-outline">Clear</a>
  <a href="?export=csv&<?= e(http_build_query($_GET)) ?>" class="btn btn-sm btn-outline">CSV export</a>
</form>

<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>Recipient</th><th>Subject</th><th>Template</th>
        <th class="ta-center">Status</th><th>Error</th><th>Sent By</th><th>Date</th>
      </tr></thead>
      <tbody>
      <?php foreach ($logs as $l): ?>
        <tr>
          <td><span class="t-strong"><?= e($l['recipient']) ?></span></td>
          <td style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($l['subject']) ?>"><?= e($l['subject']) ?></td>
          <td class="t-muted"><?= e($l['template_key'] ?? '—') ?></td>
          <td class="ta-center">
            <span class="dash-pill <?= $l['status'] === 'sent' ? 'published' : 'draft' ?>"><?= e($l['status']) ?></span>
          </td>
          <td class="t-muted" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($l['error'] ?? '') ?>"><?= e($l['error'] ?? '—') ?></td>
          <td class="t-muted"><?= e($l['admin_name'] ?? '—') ?></td>
          <td class="t-muted"><?= date_human($l['sent_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($logs)): ?>
        <tr><td colspan="7"><?php ui_empty_state(['icon' => 'mail', 'title' => 'No emails sent yet', 'text' => 'Emails will appear here once they are sent from the platform.']); ?></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= render_pagination($pagi['page'], $pagi['lastPage'], '/admin/email-log?' . http_build_query(array_filter(['status' => $statusFilter, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => $search]))) ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
