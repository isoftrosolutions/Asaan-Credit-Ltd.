<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$statusFilter = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where = ['1=1'];
$params = [];
if ($statusFilter) {
    $where[] = 'status = ?';
    $params[] = $statusFilter;
}

$whereClause = implode(' AND ', $where);
$countStmt = db()->prepare("SELECT COUNT(*) FROM contact_messages WHERE $whereClause");
$countStmt->execute($params);
$pagi = paginate($countStmt, $page, $perPage);

$stmt = db()->prepare("SELECT * FROM contact_messages WHERE $whereClause ORDER BY created_at DESC LIMIT {$pagi['perPage']} OFFSET {$pagi['offset']}");
$stmt->execute($params);
$messages = $stmt->fetchAll();

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="contact-messages-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Name', 'Email', 'Subject', 'Message', 'Status', 'Created']);
    foreach ($messages as $msg) {
        fputcsv($out, [$msg['id'], $msg['name'], $msg['email'], $msg['subject'], $msg['message'], $msg['status'], $msg['created_at']]);
    }
    fclose($out);
    exit;
}

$pageTitle = 'Contact Messages';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('Contact Messages', 'General messages submitted through the public contact form.');
?>
<form method="get" class="dash-filterbar">
  <div class="input-group">
    <label>Status</label>
    <select name="status" class="select">
      <option value="">All</option>
      <option value="new" <?= $statusFilter === 'new' ? 'selected' : '' ?>>New</option>
      <option value="read" <?= $statusFilter === 'read' ? 'selected' : '' ?>>Read</option>
      <option value="archived" <?= $statusFilter === 'archived' ? 'selected' : '' ?>>Archived</option>
    </select>
  </div>
  <button type="submit" class="btn btn-sm btn-primary">Filter</button>
  <a href="<?= APP_URL ?>/admin/contact-messages" class="btn btn-sm btn-outline">Clear</a>
  <a href="?export=csv&<?= e(http_build_query($_GET)) ?>" class="btn btn-sm btn-outline">CSV export</a>
</form>

<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Sender</th>
          <th>Subject</th>
          <th>Message</th>
          <th class="ta-center">Status</th>
          <th>Created</th>
          <th class="ta-right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php $statusPill = ['new' => 'open', 'read' => 'published', 'archived' => 'draft']; ?>
      <?php foreach ($messages as $msg): ?>
        <tr>
          <td class="t-muted"><?= (int)$msg['id'] ?></td>
          <td>
            <span class="t-strong"><?= e($msg['name']) ?></span><br>
            <a href="mailto:<?= e($msg['email']) ?>" class="t-muted"><?= e($msg['email']) ?></a>
          </td>
          <td><span class="t-strong"><?= e($msg['subject']) ?></span></td>
          <td style="max-width:260px;">
            <details>
              <summary style="cursor:pointer;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e(mb_substr($msg['message'], 0, 90)) ?><?= mb_strlen($msg['message']) > 90 ? '...' : '' ?></summary>
              <div style="margin-top:8px;white-space:pre-wrap;color:var(--dash-ink);line-height:1.5;"><?= e($msg['message']) ?></div>
            </details>
          </td>
          <td class="ta-center"><span class="dash-pill <?= $statusPill[$msg['status']] ?? 'draft' ?>"><?= e($msg['status']) ?></span></td>
          <td class="t-muted"><?= date_human($msg['created_at']) ?></td>
          <td class="ta-right">
            <span class="dash-table-actions">
              <?php if ($msg['status'] === 'new'): ?>
              <form method="post" action="<?= APP_URL ?>/admin/contact-message-action" style="display:inline;">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="id" value="<?= (int)$msg['id'] ?>">
                <input type="hidden" name="action" value="mark_read">
                <button type="submit" class="btn btn-sm btn-outline">Mark Read</button>
              </form>
              <?php endif; ?>
              <form method="post" action="<?= APP_URL ?>/admin/contact-message-action" style="display:inline;" onsubmit="return confirm('Archive this message?')">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="id" value="<?= (int)$msg['id'] ?>">
                <input type="hidden" name="action" value="archive">
                <button type="submit" class="btn btn-sm btn-outline">Archive</button>
              </form>
              <form method="post" action="<?= APP_URL ?>/admin/contact-message-action" style="display:inline;" onsubmit="return confirm('Delete this message permanently?')">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="id" value="<?= (int)$msg['id'] ?>">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn btn-sm btn-outline">Delete</button>
              </form>
            </span>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($messages)): ?>
        <tr><td colspan="7"><?php ui_empty_state(['icon' => 'mail', 'title' => 'No contact messages found', 'text' => 'Public contact form submissions will appear here.']); ?></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= render_pagination($pagi['page'], $pagi['lastPage'], '/admin/contact-messages?' . http_build_query(array_filter(['status' => $statusFilter]))) ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>

