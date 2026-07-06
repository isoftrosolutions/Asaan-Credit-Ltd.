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

$stmt = db()->prepare("SELECT kv.*, u.name AS user_name, u.email, u.role AS user_role FROM kyc_verifications kv JOIN users u ON u.id = kv.user_id WHERE $whereClause ORDER BY kv.submitted_at DESC, kv.created_at DESC LIMIT {$pagi['perPage']} OFFSET {$pagi['offset']}");
$stmt->execute($params);
$records = $stmt->fetchAll();

$pageTitle = 'KYC Verifications — Admin';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('KYC Verifications', '<strong>' . $totalPending . '</strong> pending review.');
?>
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
        <th>User</th><th>Role</th><th>Full Name</th><th>Document</th><th class="ta-center">Status</th><th>Submitted</th><th class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($records as $r): ?>
      <?php
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
            <button type="button" class="btn btn-sm btn-outline" onclick="showKYCModal(<?= $r['id'] ?>)">Review</button>
            <?php if ($r['status'] === 'pending'): ?>
            <form method="post" style="display:inline;">
              <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
              <input type="hidden" name="kyc_id" value="<?= $r['id'] ?>">
              <input type="hidden" name="action" value="approve">
              <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Verify this KYC?')">Approve</button>
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

<?php foreach ($records as $r): ?>
<div class="kyc-modal-overlay" id="kycModal<?= $r['id'] ?>" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;overflow-y:auto;" onclick="if(event.target===this)closeKYCModal(<?= $r['id'] ?>)">
  <div style="max-width:800px;margin:40px auto;background:#fff;border-radius:20px;padding:32px;position:relative;box-shadow:0 20px 60px rgba(0,0,0,.15);">
    <button type="button" onclick="closeKYCModal(<?= $r['id'] ?>)" style="position:absolute;top:16px;right:16px;background:none;border:none;font-size:24px;cursor:pointer;color:#6b7280;">&times;</button>
    <h2 style="margin:0 0 4px;font-size:22px;">KYC Review</h2>
    <p style="color:#6b7280;margin:0 0 20px;"><?= e($r['user_name']) ?> &middot; <?= e($r['full_name'] ?: 'N/A') ?> &middot; Submitted <?= $r['submitted_at'] ? date('M j, Y g:i a', strtotime($r['submitted_at'])) : '—' ?></p>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
        <small style="color:#6b7280;display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Full Name</small>
        <b style="font-size:14px;"><?= e($r['full_name'] ?: '—') ?></b>
      </div>
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
        <small style="color:#6b7280;display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Date of Birth</small>
        <b style="font-size:14px;"><?= $r['date_of_birth'] ? date('M j, Y', strtotime($r['date_of_birth'])) : '—' ?></b>
      </div>
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
        <small style="color:#6b7280;display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Father's Name</small>
        <b style="font-size:14px;"><?= e($r['father_name'] ?: '—') ?></b>
      </div>
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
        <small style="color:#6b7280;display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">ID Document</small>
        <b style="font-size:14px;"><?= e(ucfirst(str_replace('_', ' ', $r['id_document_type'] ?: '—'))) ?></b>
      </div>
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
        <small style="color:#6b7280;display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">ID Number</small>
        <b style="font-size:14px;"><?= e($r['id_document_number'] ?: '—') ?></b>
      </div>
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
        <small style="color:#6b7280;display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">PAN / VAT</small>
        <b style="font-size:14px;"><?= e($r['pan_vat'] ?: '—') ?></b>
      </div>
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;grid-column:span 2;">
        <small style="color:#6b7280;display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Permanent Address</small>
        <b style="font-size:14px;"><?= e($r['permanent_address'] ?: '—') ?></b>
      </div>
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
        <small style="color:#6b7280;display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Current Address</small>
        <b style="font-size:14px;"><?= e($r['current_address'] ?: '—') ?></b>
      </div>
      <?php if ($r['business_name']): ?>
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
        <small style="color:#6b7280;display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Business Name</small>
        <b style="font-size:14px;"><?= e($r['business_name']) ?></b>
      </div>
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
        <small style="color:#6b7280;display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Business Type</small>
        <b style="font-size:14px;"><?= e(ucfirst(str_replace('_', ' ', $r['business_type'] ?: '—'))) ?></b>
      </div>
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
        <small style="color:#6b7280;display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Registration No.</small>
        <b style="font-size:14px;"><?= e($r['registration_number'] ?: '—') ?></b>
      </div>
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
        <small style="color:#6b7280;display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Industry</small>
        <b style="font-size:14px;"><?= e($r['industry'] ?: '—') ?></b>
      </div>
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
        <small style="color:#6b7280;display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Intent</small>
        <b style="font-size:14px;"><?= e($r['intent'] ?: '—') ?></b>
      </div>
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
        <small style="color:#6b7280;display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Monthly Sales</small>
        <b style="font-size:14px;"><?= $r['monthly_sales'] ? money($r['monthly_sales']) : '—' ?></b>
      </div>
      <?php endif; ?>
      <?php if ($r['investor_type']): ?>
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
        <small style="color:#6b7280;display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Investor Type</small>
        <b style="font-size:14px;"><?= e(ucfirst(str_replace('_', ' ', $r['investor_type']))) ?></b>
      </div>
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
        <small style="color:#6b7280;display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Ticket Range</small>
        <b style="font-size:14px;"><?= $r['investment_ticket_min'] ? money($r['investment_ticket_min']) : '—' ?> - <?= $r['investment_ticket_max'] ? money($r['investment_ticket_max']) : '—' ?></b>
      </div>
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
        <small style="color:#6b7280;display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Preferred Sectors</small>
        <b style="font-size:14px;"><?= e($r['preferred_sectors'] ?: '—') ?></b>
      </div>
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
        <small style="color:#6b7280;display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">Capital Deployed</small>
        <b style="font-size:14px;"><?= $r['total_capital_deployed'] ? money($r['total_capital_deployed']) : '—' ?></b>
      </div>
      <?php endif; ?>
    </div>

    <h3 style="margin:16px 0 8px;font-size:15px;">Uploaded Documents</h3>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px;">
      <?php $docs = [
        'id_front_path' => 'ID Front',
        'id_back_path' => 'ID Back',
        'registration_cert_path' => 'Registration Cert',
        'pan_cert_path' => 'PAN Certificate',
        'financial_proof_path' => 'Financial Proof',
        'business_photo_path' => 'Business Photo',
      ]; foreach ($docs as $col => $label):
        $path = $r[$col] ?? null;
      ?>
      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
        <small style="color:#6b7280;display:block;font-size:11px;margin-bottom:6px;font-weight:600;"><?= $label ?></small>
        <?php if ($path): ?>
        <a href="<?= e(upload_url($path)) ?>" target="_blank" rel="noopener" style="font-size:13px;">View file &nearr;</a>
        <?php else: ?>
        <span style="color:#9ca3af;font-size:13px;">Not provided</span>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($r['rejection_reason']): ?>
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:16px;margin-bottom:16px;">
      <small style="color:#991b1b;font-weight:700;display:block;margin-bottom:4px;">Rejection Reason</small>
      <?= e($r['rejection_reason']) ?>
    </div>
    <?php endif; ?>

    <div style="display:flex;gap:10px;justify-content:flex-end;border-top:1px solid #e5e7eb;padding-top:16px;">
      <button type="button" class="btn btn-outline" onclick="closeKYCModal(<?= $r['id'] ?>)">Close</button>
      <?php if ($r['status'] === 'pending'): ?>
      <form method="post" style="display:inline;">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
        <input type="hidden" name="kyc_id" value="<?= $r['id'] ?>">
        <input type="hidden" name="action" value="approve">
        <button type="submit" class="btn btn-primary" onclick="return confirm('Verify this KYC?')">Approve</button>
      </form>
      <form method="post" style="display:inline;" onsubmit="var r=prompt('Enter rejection reason:');if(!r||!r.trim()){alert('Reason required');return false;}this.rejection_reason.value=r;">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
        <input type="hidden" name="kyc_id" value="<?= $r['id'] ?>">
        <input type="hidden" name="action" value="reject">
        <input type="hidden" name="rejection_reason" value="">
        <button type="submit" class="btn btn-outline">Reject</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endforeach; ?>

<script>
function showKYCModal(id) {
  document.getElementById('kycModal' + id).style.display = 'block';
  document.body.style.overflow = 'hidden';
}
function closeKYCModal(id) {
  document.getElementById('kycModal' + id).style.display = 'none';
  document.body.style.overflow = '';
}
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>