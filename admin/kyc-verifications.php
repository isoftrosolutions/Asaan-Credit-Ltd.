<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $kycId = (int)($_POST['kyc_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $stmt = db()->prepare('SELECT kv.*, u.name AS user_name, u.email FROM kyc_verifications kv JOIN users u ON u.id = kv.user_id WHERE kv.id = ?');
    $stmt->execute([$kycId]);
    $target = $stmt->fetch();

    if (!$target) {
        flash_set('error', 'KYC record not found.');
        redirect('/admin/kyc-verifications');
    }

    if ($action === 'approve') {
        db()->prepare("UPDATE kyc_verifications SET status = 'verified', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?")->execute([(int)$user['id'], $kycId]);
        $notifStmt = db()->prepare('INSERT INTO notifications (user_id, type, title, body, action_url, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $notifStmt->execute([$target['user_id'], 'verification', 'KYC Verified', 'Your KYC verification has been approved. You can now access all platform features.', '/dashboard']);
        send_kyc_approved_email($target['email'], $target['user_name']);
        admin_log('approve_kyc', 'kyc_verifications', $kycId, ['user_id' => $target['user_id'], 'email' => $target['email']]);
        flash_set('success', 'KYC verified successfully.');
    } elseif ($action === 'reject') {
        $reason = trim($_POST['rejection_reason'] ?? '');
        if (!$reason) {
            flash_set('error', 'Please provide a rejection reason.');
            redirect('/admin/kyc-verifications');
        }
        db()->prepare("UPDATE kyc_verifications SET status = 'rejected', rejection_reason = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?")->execute([$reason, (int)$user['id'], $kycId]);
        $notifStmt = db()->prepare('INSERT INTO notifications (user_id, type, title, body, action_url, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $notifStmt->execute([$target['user_id'], 'verification', 'KYC Rejected', 'Your KYC verification was rejected: ' . $reason, '/kyc']);
        send_kyc_rejected_email($target['email'], $target['user_name'], $reason);
        admin_log('reject_kyc', 'kyc_verifications', $kycId, ['user_id' => $target['user_id'], 'email' => $target['email'], 'reason' => $reason]);
        flash_set('success', 'KYC rejected.');
    }
    redirect('/admin/kyc-verifications');
}

$statusFilter = $_GET['status'] ?? 'pending';
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where = ['1=1'];
$params = [];
if (in_array($statusFilter, ['pending', 'verified', 'rejected', 'draft'])) {
    $where[] = 'kv.status = ?';
    $params[] = $statusFilter;
}
if ($search) {
    $where[] = '(u.name LIKE ? OR u.email LIKE ? OR kv.full_name LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$whereClause = implode(' AND ', $where);

$countStmt = db()->prepare("SELECT COUNT(*) FROM kyc_verifications kv JOIN users u ON u.id = kv.user_id WHERE $whereClause");
$countStmt->execute($params);
$pagi = paginate($countStmt, $page, $perPage);
$totalPending = db()->query("SELECT COUNT(*) FROM kyc_verifications WHERE status = 'pending'")->fetchColumn();
$totalVerified = db()->query("SELECT COUNT(*) FROM kyc_verifications WHERE status = 'verified'")->fetchColumn();
$totalRejected = db()->query("SELECT COUNT(*) FROM kyc_verifications WHERE status = 'rejected'")->fetchColumn();

$stmt = db()->prepare("SELECT kv.*, u.name AS user_name, u.email, u.role AS user_role FROM kyc_verifications kv JOIN users u ON u.id = kv.user_id WHERE $whereClause ORDER BY kv.submitted_at DESC, kv.created_at DESC LIMIT {$pagi['perPage']} OFFSET {$pagi['offset']}");
$stmt->execute($params);
$records = $stmt->fetchAll();

$pageTitle = 'KYC Verifications — Admin';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('KYC Verifications', '<strong>' . $totalPending . '</strong> pending, <strong>' . $totalVerified . '</strong> verified, <strong>' . $totalRejected . '</strong> rejected.');
?>

<div class="dash-stats" style="margin-bottom:20px;">
  <div class="dash-stat" onclick="window.location='?status=pending'" style="cursor:pointer;">
    <div class="dash-stat-top">
      <span class="dash-stat-ico" style="background:rgba(245,158,11,.12);color:var(--dash-warning);"><?php ui_icon('clock'); ?></span>
    </div>
    <div class="dash-stat-value"><?= number_format($totalPending) ?></div>
    <div class="dash-stat-label">Pending review</div>
  </div>
  <div class="dash-stat" onclick="window.location='?status=verified'" style="cursor:pointer;">
    <div class="dash-stat-top">
      <span class="dash-stat-ico" style="background:rgba(16,185,129,.1);color:var(--dash-success);"><?php ui_icon('check'); ?></span>
    </div>
    <div class="dash-stat-value"><?= number_format($totalVerified) ?></div>
    <div class="dash-stat-label">Verified</div>
  </div>
  <div class="dash-stat" onclick="window.location='?status=rejected'" style="cursor:pointer;">
    <div class="dash-stat-top">
      <span class="dash-stat-ico" style="background:rgba(239,68,68,.1);color:var(--color-error);"><?php ui_icon('close'); ?></span>
    </div>
    <div class="dash-stat-value"><?= number_format($totalRejected) ?></div>
    <div class="dash-stat-label">Rejected</div>
  </div>
  <div class="dash-stat" onclick="window.location='?status=draft'" style="cursor:pointer;">
    <div class="dash-stat-top">
      <span class="dash-stat-ico" style="background:rgba(107,114,128,.1);color:#6b7280;"><?php ui_icon('document'); ?></span>
    </div>
    <div class="dash-stat-value"><?= number_format((int)db()->query("SELECT COUNT(*) FROM kyc_verifications WHERE status = 'draft'")->fetchColumn()) ?></div>
    <div class="dash-stat-label">Drafts</div>
  </div>
</div>

<form method="get" class="dash-filterbar">
  <div class="input-group grow">
    <label>Search</label>
    <input type="text" name="search" class="input" value="<?= e($search) ?>" placeholder="Name or email...">
  </div>
  <div class="input-group">
    <label>Status</label>
    <select name="status" class="select" onchange="this.form.submit()">
      <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
      <option value="verified" <?= $statusFilter === 'verified' ? 'selected' : '' ?>>Verified</option>
      <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
      <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
    </select>
  </div>
  <button type="submit" class="btn btn-sm btn-primary">Filter</button>
  <a href="<?= APP_URL ?>/admin/kyc-verifications" class="btn btn-sm btn-outline">Clear</a>
</form>

<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>User</th><th>Role</th><th>Full Name</th><th>ID Type</th><th class="ta-center">Status</th><th>Submitted</th><th class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($records as $r):
        $roleLabel = ucfirst(str_replace('_', ' ', $r['user_role']));
        $statusPillClass = $r['status'] === 'verified' ? 'published' : ($r['status'] === 'rejected' ? 'rejected' : ($r['status'] === 'pending' ? 'pending' : 'draft'));
      ?>
      <tr>
        <td>
          <span class="t-strong"><?= e($r['user_name']) ?></span><br>
          <span class="t-muted"><?= e($r['email']) ?></span>
        </td>
        <td><span class="dash-pill open"><?= e($roleLabel) ?></span></td>
        <td><?= e($r['full_name'] ?: '—') ?></td>
        <td><?= e($r['id_document_type'] ? ucfirst(str_replace('_', ' ', $r['id_document_type'])) : '—') ?></td>
        <td class="ta-center"><span class="dash-pill <?= $statusPillClass ?>"><?= e(ucfirst($r['status'])) ?></span></td>
        <td class="t-muted"><?= $r['submitted_at'] ? date_human($r['submitted_at']) : ($r['created_at'] ? date_human($r['created_at']) : '—') ?></td>
        <td class="ta-right">
          <span class="dash-table-actions">
            <a href="<?= APP_URL ?>/admin/kyc-review?id=<?= $r['id'] ?>" class="btn btn-sm btn-primary">Review</a>
            <?php if ($r['status'] === 'pending'): ?>
            <form method="post" style="display:inline;">
              <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
              <input type="hidden" name="kyc_id" value="<?= $r['id'] ?>">
              <input type="hidden" name="action" value="approve">
              <button type="submit" class="btn btn-sm btn-outline" onclick="return confirm('Verify this KYC?')">Approve</button>
            </form>
            <form method="post" style="display:inline;" onsubmit="var r=prompt('Enter rejection reason:');if(!r||!r.trim()){alert('Reason required');return false;}this.rejection_reason.value=r;">
              <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
              <input type="hidden" name="kyc_id" value="<?= $r['id'] ?>">
              <input type="hidden" name="action" value="reject">
              <input type="hidden" name="rejection_reason" value="">
              <button type="submit" class="btn btn-sm btn-outline">Reject</button>
            </form>
            <?php endif; ?>
          </span>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($records)): ?>
        <tr><td colspan="7"><?php ui_empty_state(['icon' => 'check', 'title' => 'No records found', 'text' => 'No KYC records match the current filter.']); ?></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= render_pagination($pagi['page'], $pagi['lastPage'], '/admin/kyc-verifications?' . http_build_query(array_filter(['status' => $statusFilter, 'search' => $search]))) ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>