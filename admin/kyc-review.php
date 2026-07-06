<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$admin = current_user();
$kycId = (int)($_GET['id'] ?? 0);

if (!$kycId) {
    flash_set('error', 'No KYC record specified.');
    redirect('/admin/kyc-verifications');
}

$stmt = db()->prepare("
    SELECT kv.*, u.name AS user_name, u.email, u.role AS user_role, u.phone, u.created_at AS user_since,
           u.verification_status, u.is_premium, u.is_suspended,
           r.name AS reviewer_name
    FROM kyc_verifications kv
    JOIN users u ON u.id = kv.user_id
    LEFT JOIN users r ON r.id = kv.reviewed_by
    WHERE kv.id = ?
");
$stmt->execute([$kycId]);
$r = $stmt->fetch();

if (!$r) {
    flash_set('error', 'KYC record not found.');
    redirect('/admin/kyc-verifications');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'approve') {
        db()->prepare("UPDATE kyc_verifications SET status = 'verified', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?")->execute([(int)$admin['id'], $kycId]);
        $notifStmt = db()->prepare('INSERT INTO notifications (user_id, type, title, body, action_url, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $notifStmt->execute([$r['user_id'], 'verification', 'KYC Verified', 'Your KYC verification has been approved. You can now access all platform features.', '/dashboard']);
        send_kyc_approved_email($r['email'], $r['user_name']);
        admin_log('approve_kyc', 'kyc_verifications', $kycId, ['user_id' => $r['user_id'], 'email' => $r['email']]);
        flash_set('success', 'KYC verified successfully.');
        redirect('/admin/kyc-verifications');
    } elseif ($action === 'reject') {
        $reason = trim($_POST['rejection_reason'] ?? '');
        if (!$reason) {
            $error = 'Please provide a rejection reason.';
        } else {
            db()->prepare("UPDATE kyc_verifications SET status = 'rejected', rejection_reason = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?")->execute([$reason, (int)$admin['id'], $kycId]);
            $notifStmt = db()->prepare('INSERT INTO notifications (user_id, type, title, body, action_url, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
            $notifStmt->execute([$r['user_id'], 'verification', 'KYC Rejected', 'Your KYC verification was rejected: ' . $reason, '/kyc']);
            send_kyc_rejected_email($r['email'], $r['user_name'], $reason);
            admin_log('reject_kyc', 'kyc_verifications', $kycId, ['user_id' => $r['user_id'], 'email' => $r['email'], 'reason' => $reason]);
            flash_set('success', 'KYC rejected.');
            redirect('/admin/kyc-verifications');
        }
    }
}

$isBiz = !empty($r['business_name']);
$isInv = !empty($r['investor_type']);
$statusClass = $r['status'] === 'verified' ? 'published' : ($r['status'] === 'rejected' ? 'rejected' : ($r['status'] === 'pending' ? 'pending' : 'draft'));
$roleLabel = ucfirst(str_replace('_', ' ', $r['user_role']));
$pageTitle = 'KYC Review — ' . e($r['user_name']);
require __DIR__ . '/../includes/layout-admin.php';
?>
<style>
.kyc-detail-header { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:24px; }
.kyc-detail-user { display:flex; align-items:center; gap:16px; }
.kyc-detail-avatar { width:56px; height:56px; border-radius:16px; background:var(--color-primary); color:#fff; display:grid; place-items:center; font-size:22px; font-weight:800; letter-spacing:-.04em; flex:0 0 auto; }
.kyc-detail-meta h1 { margin:0; font-size:22px; letter-spacing:-.03em; }
.kyc-detail-meta p { margin:2px 0 0; color:var(--dash-ink-soft); font-size:14px; }
.kyc-detail-actions { display:flex; gap:8px; }

.kyc-stat-row { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:24px; }
.kyc-stat-card { background:#fff; border:1px solid var(--dash-border); border-radius:14px; padding:16px; box-shadow:var(--dash-shadow); }
.kyc-stat-card small { color:var(--dash-ink-soft); font-size:11px; text-transform:uppercase; letter-spacing:.04em; font-weight:600; display:block; margin-bottom:4px; }
.kyc-stat-card strong { font-size:20px; letter-spacing:-.03em; }
.kyc-stat-card .stat-sub { font-size:12px; color:var(--dash-ink-soft); margin-top:2px; }

.kyc-section-card { background:#fff; border:1px solid var(--dash-border); border-radius:16px; box-shadow:var(--dash-shadow); margin-bottom:20px; overflow:hidden; }
.kyc-section-card-head { display:flex; align-items:center; gap:12px; padding:16px 20px; border-bottom:1px solid var(--dash-border); background:var(--color-bg-soft); }
.kyc-section-card-head h3 { margin:0; font-size:15px; font-weight:600; }
.kyc-section-card-head .badge { margin-left:auto; }
.kyc-section-card-body { padding:20px; }

.kyc-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
.kyc-grid .full { grid-column:1/-1; }
.kyc-field { display:flex; flex-direction:column; gap:3px; }
.kyc-field .label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:var(--dash-ink-soft); }
.kyc-field .value { font-size:14px; color:var(--dash-ink); word-break:break-word; }
.kyc-field .value.na { color:#9ca3af; font-style:italic; }

.kyc-docs { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
.kyc-doc { border:1px solid var(--dash-border); border-radius:14px; overflow:hidden; background:#fafafa; }
.kyc-doc-img { width:100%; height:160px; object-fit:cover; background:#f3f4f6; display:flex; align-items:center; justify-content:center; color:#9ca3af; font-size:13px; }
.kyc-doc-img img { width:100%; height:100%; object-fit:contain; }
.kyc-doc-foot { padding:10px 14px; display:flex; align-items:center; justify-content:space-between; gap:8px; border-top:1px solid var(--dash-border); background:#fff; }
.kyc-doc-foot small { font-size:12px; font-weight:600; }
.kyc-doc-foot a { font-size:12px; font-weight:600; color:var(--color-primary); text-decoration:none; }
.kyc-doc-foot a:hover { text-decoration:underline; }
.kyc-doc-na { display:flex; align-items:center; justify-content:center; height:160px; background:#f9fafb; color:#d1d5db; font-size:32px; }

.kyc-reject-form { background:#fef2f2; border:1px solid #fecaca; border-radius:14px; padding:20px; margin-top:16px; display:none; }
.kyc-reject-form.open { display:block; }
.kyc-reject-form textarea { width:100%; border:1px solid #fca5a5; border-radius:10px; padding:12px; font-size:14px; resize:vertical; min-height:80px; outline:none; }
.kyc-reject-form textarea:focus { border-color:var(--color-error); box-shadow:0 0 0 3px rgba(152,32,42,.1); }

.kyc-timeline { margin-top:16px; }
.kyc-tl-item { display:flex; gap:12px; padding:12px 0; border-bottom:1px solid var(--dash-border); }
.kyc-tl-item:last-child { border-bottom:0; }
.kyc-tl-icon { width:32px; height:32px; border-radius:10px; display:grid; place-items:center; flex:0 0 auto; font-size:14px; }
.kyc-tl-icon.created { background:#eff6ff; color:#1d4ed8; }
.kyc-tl-icon.submitted { background:#eff6ff; color:#1d4ed8; }
.kyc-tl-icon.verified { background:#ecfdf5; color:#047857; }
.kyc-tl-icon.rejected { background:#fef2f2; color:#b91c1c; }
.kyc-tl-body { flex:1; }
.kyc-tl-body b { display:block; font-size:13px; }
.kyc-tl-body small { color:var(--dash-ink-soft); font-size:12px; }

@media (max-width:880px) {
  .kyc-stat-row { grid-template-columns:repeat(2,1fr); }
  .kyc-grid { grid-template-columns:1fr; }
  .kyc-docs { grid-template-columns:1fr; }
  .kyc-detail-header { flex-direction:column; align-items:flex-start; }
}
</style>

<div class="kyc-detail-header">
  <div class="kyc-detail-user">
    <div class="kyc-detail-avatar"><?= e(mb_substr($r['user_name'], 0, 2)) ?></div>
    <div class="kyc-detail-meta">
      <h1><?= e($r['full_name'] ?: $r['user_name']) ?></h1>
      <p>
        <a href="mailto:<?= e($r['email']) ?>" style="color:inherit;"><?= e($r['email']) ?></a>
        &middot; <span class="dash-pill <?= $statusClass ?>"><?= e(ucfirst($r['status'])) ?></span>
        &middot; <?= e($roleLabel) ?>
      </p>
    </div>
  </div>
  <div class="kyc-detail-actions">
    <a href="<?= APP_URL ?>/admin/kyc-verifications" class="btn btn-outline btn-sm">&larr; Back</a>
    <?php if ($r['status'] === 'pending'): ?>
    <form method="post" style="display:inline;">
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="approve">
      <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Verify this KYC?')">Approve</button>
    </form>
    <button type="button" class="btn btn-outline btn-sm" onclick="toggleRejectForm()">Reject</button>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($error)): ?>
<div class="kyc-alert error"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($r['rejection_reason']): ?>
<div class="kyc-alert error" style="margin-bottom:20px;">
  <strong>Rejected:</strong> <?= e($r['rejection_reason']) ?>
</div>
<?php endif; ?>

<div class="kyc-stat-row">
  <div class="kyc-stat-card">
    <small>Status</small>
    <strong style="color:<?= $r['status'] === 'verified' ? '#047857' : ($r['status'] === 'rejected' ? '#b91c1c' : ($r['status'] === 'pending' ? '#b45309' : '#6b7280')) ?>"><?= e(ucfirst($r['status'])) ?></strong>
    <?php if ($r['reviewed_at']): ?><div class="stat-sub">Reviewed <?= date('M j, Y', strtotime($r['reviewed_at'])) ?></div><?php endif; ?>
  </div>
  <div class="kyc-stat-card">
    <small>Submitted</small>
    <strong><?= $r['submitted_at'] ? date('M j, Y', strtotime($r['submitted_at'])) : '—' ?></strong>
    <?php if ($r['submitted_at']): ?><div class="stat-sub"><?= date('g:i a', strtotime($r['submitted_at'])) ?></div><?php endif; ?>
  </div>
  <div class="kyc-stat-card">
    <small>Account age</small>
    <strong><?= date_human($r['user_since']) ?></strong>
    <div class="stat-sub"><?= $r['verification_status'] === 'verified' ? 'Email verified' : 'Not verified' ?></div>
  </div>
  <div class="kyc-stat-card">
    <small>ID Document</small>
    <strong><?= e(ucfirst(str_replace('_', ' ', $r['id_document_type'] ?: '—'))) ?></strong>
    <div class="stat-sub"><?= e($r['id_document_number'] ?: '—') ?></div>
  </div>
</div>

<div class="kyc-section-card">
  <div class="kyc-section-card-head">
    <h3>Personal Information</h3>
    <span class="badge dash-pill open">ID: <?= e(ucfirst(str_replace('_', ' ', $r['id_document_type']))) ?></span>
  </div>
  <div class="kyc-section-card-body">
    <div class="kyc-grid">
      <div class="kyc-field">
        <span class="label">Full Name</span>
        <span class="value"><?= e($r['full_name'] ?: '—') ?></span>
      </div>
      <div class="kyc-field">
        <span class="label">Date of Birth</span>
        <span class="value"><?= $r['date_of_birth'] ? date('F j, Y', strtotime($r['date_of_birth'])) : '<span class="na">Not provided</span>' ?></span>
      </div>
      <div class="kyc-field">
        <span class="label">Father's Name</span>
        <span class="value"><?= e($r['father_name'] ?: '<span class="na">Not provided</span>') ?></span>
      </div>
      <div class="kyc-field full">
        <span class="label">Permanent Address</span>
        <span class="value"><?= e($r['permanent_address'] ?: '<span class="na">Not provided</span>') ?></span>
      </div>
      <div class="kyc-field full">
        <span class="label">Current Address</span>
        <span class="value"><?= e($r['current_address'] ?: '<span class="na">Not provided</span>') ?></span>
      </div>
    </div>
  </div>
</div>

<?php if ($isBiz): ?>
<div class="kyc-section-card">
  <div class="kyc-section-card-head">
    <h3>Business Profile</h3>
    <span class="badge dash-pill published">Business Owner</span>
  </div>
  <div class="kyc-section-card-body">
    <div class="kyc-grid">
      <div class="kyc-field">
        <span class="label">Business Name</span>
        <span class="value"><?= e($r['business_name']) ?></span>
      </div>
      <div class="kyc-field">
        <span class="label">Business Type</span>
        <span class="value"><?= e(ucfirst(str_replace('_', ' ', $r['business_type'] ?: '—'))) ?></span>
      </div>
      <div class="kyc-field">
        <span class="label">Registration Number</span>
        <span class="value"><?= e($r['registration_number'] ?: '<span class="na">Not provided</span>') ?></span>
      </div>
      <div class="kyc-field">
        <span class="label">Registration Date</span>
        <span class="value"><?= $r['registration_date'] ? date('F j, Y', strtotime($r['registration_date'])) : '<span class="na">Not provided</span>' ?></span>
      </div>
      <div class="kyc-field">
        <span class="label">PAN / VAT</span>
        <span class="value"><?= e($r['pan_vat'] ?: '<span class="na">Not provided</span>') ?></span>
      </div>
      <div class="kyc-field">
        <span class="label">Industry</span>
        <span class="value"><?= e($r['industry'] ?: '<span class="na">Not provided</span>') ?></span>
      </div>
      <div class="kyc-field full">
        <span class="label">Business Address</span>
        <span class="value"><?= e($r['business_address'] ?: '<span class="na">Not provided</span>') ?></span>
      </div>
      <div class="kyc-field">
        <span class="label">Website</span>
        <span class="value"><?= $r['business_url'] ? '<a href="' . e($r['business_url']) . '" target="_blank" rel="noopener">' . e($r['business_url']) . '</a>' : '<span class="na">Not provided</span>' ?></span>
      </div>
      <div class="kyc-field">
        <span class="label">Monthly Sales</span>
        <span class="value"><?= $r['monthly_sales'] ? money($r['monthly_sales']) . '/mo' : '<span class="na">Not provided</span>' ?></span>
      </div>
      <div class="kyc-field">
        <span class="label">Intent</span>
        <span class="value"><?= e($r['intent'] ?: '<span class="na">Not provided</span>') ?></span>
      </div>
      <?php if ($r['business_description']): ?>
      <div class="kyc-field full">
        <span class="label">Description</span>
        <span class="value" style="line-height:1.6;"><?= e($r['business_description']) ?></span>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($isInv): ?>
<div class="kyc-section-card">
  <div class="kyc-section-card-head">
    <h3>Investment Profile</h3>
    <span class="badge dash-pill open">Investor</span>
  </div>
  <div class="kyc-section-card-body">
    <div class="kyc-grid">
      <div class="kyc-field">
        <span class="label">Investor Type</span>
        <span class="value"><?= e(ucfirst(str_replace('_', ' ', $r['investor_type']))) ?></span>
      </div>
      <div class="kyc-field">
        <span class="label">Ticket Size Range</span>
        <span class="value"><?= $r['investment_ticket_min'] ? money($r['investment_ticket_min']) : '—' ?> &ndash; <?= $r['investment_ticket_max'] ? money($r['investment_ticket_max']) : '—' ?></span>
      </div>
      <div class="kyc-field">
        <span class="label">Preferred Sectors</span>
        <span class="value"><?= e($r['preferred_sectors'] ?: '<span class="na">All sectors</span>') ?></span>
      </div>
      <div class="kyc-field">
        <span class="label">Capital Deployed</span>
        <span class="value"><?= $r['total_capital_deployed'] ? money($r['total_capital_deployed']) : '<span class="na">Not provided</span>' ?></span>
      </div>
      <div class="kyc-field">
        <span class="label">Past Investments</span>
        <span class="value"><?= $r['past_investments'] ? number_format((int)$r['past_investments']) . ' deals' : '<span class="na">Not provided</span>' ?></span>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="kyc-section-card">
  <div class="kyc-section-card-head">
    <h3>Uploaded Documents</h3>
    <span class="badge dash-pill open"><?php
      $docCount = 0;
      foreach (['id_front_path','id_back_path','registration_cert_path','pan_cert_path','financial_proof_path','business_photo_path'] as $c) {
        if (!empty($r[$c])) $docCount++;
      }
      echo $docCount; ?>/6 uploaded
    </span>
  </div>
  <div class="kyc-section-card-body">
    <div class="kyc-docs">
      <?php
      $docs = [
        'id_front_path' => ['label' => 'ID Front', 'icon' => '🪪'],
        'id_back_path' => ['label' => 'ID Back', 'icon' => '🪪'],
        'registration_cert_path' => ['label' => 'Registration Certificate', 'icon' => '📄'],
        'pan_cert_path' => ['label' => 'PAN / VAT Certificate', 'icon' => '📋'],
        'financial_proof_path' => ['label' => 'Financial Proof', 'icon' => '💰'],
        'business_photo_path' => ['label' => 'Business Photo', 'icon' => '📸'],
      ];
      foreach ($docs as $col => $info):
        $path = $r[$col] ?? null;
        if ($path && !str_contains($path, '/')) $path = 'kyc-documents/' . $path;
      ?>\n      <div class="kyc-doc">
        <?php if ($path):
          $url = upload_url($path);
          $isImage = preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $path);
        ?>
        <div class="kyc-doc-img">
          <?php if ($isImage): ?>
          <img src="<?= e($url) ?>" alt="<?= e($info['label']) ?>" loading="lazy">
          <?php else: ?>
          <div style="text-align:center;">
            <div style="font-size:40px;margin-bottom:4px;">📄</div>
            <div style="font-size:12px;">PDF Document</div>
          </div>
          <?php endif; ?>
        </div>
        <div class="kyc-doc-foot">
          <small><?= e($info['label']) ?></small>
          <a href="<?= e($url) ?>" target="_blank" rel="noopener">Open &nearr;</a>
        </div>
        <?php else: ?>
        <div class="kyc-doc-na"><?= $info['icon'] ?></div>
        <div class="kyc-doc-foot">
          <small style="color:#9ca3af;"><?= e($info['label']) ?></small>
          <span style="color:#d1d5db;font-size:11px;">Not uploaded</span>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php if ($r['status'] === 'pending'): ?>
<div class="kyc-section-card" style="border-color:var(--color-primary);">
  <div class="kyc-section-card-head" style="background:rgba(107,29,34,.04);">
    <h3>Review Decision</h3>
  </div>
  <div class="kyc-section-card-body">
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <form method="post" style="display:inline;">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="approve">
        <button type="submit" class="btn btn-primary" onclick="return confirm('Verify this KYC?')">Approve KYC</button>
      </form>
      <button type="button" class="btn btn-outline" onclick="toggleRejectForm()">Reject with reason</button>
    </div>

    <div class="kyc-reject-form" id="rejectForm">
      <form method="post">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="reject">
        <label style="font-weight:700;font-size:13px;display:block;margin-bottom:8px;color:#991b1b;">Rejection reason</label>
        <textarea name="rejection_reason" placeholder="Explain why the KYC is being rejected. This will be sent to the user via email and notification."></textarea>
        <div style="display:flex;gap:8px;margin-top:10px;">
          <button type="submit" class="btn btn-primary" style="background:var(--color-error);border-color:var(--color-error);">Confirm rejection</button>
          <button type="button" class="btn btn-outline" onclick="toggleRejectForm()">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="kyc-section-card">
  <div class="kyc-section-card-head">
    <h3>Activity Log</h3>
  </div>
  <div class="kyc-section-card-body">
    <div class="kyc-timeline">
      <?php if ($r['reviewed_at']): ?>
      <div class="kyc-tl-item">
        <div class="kyc-tl-icon <?= $r['status'] === 'verified' ? 'verified' : 'rejected' ?>">
          <?= $r['status'] === 'verified' ? '✓' : '✗' ?>
        </div>
        <div class="kyc-tl-body">
          <b><?= $r['status'] === 'verified' ? 'KYC Verified' : 'KYC Rejected' ?></b>
          <small><?= date('F j, Y g:i a', strtotime($r['reviewed_at'])) ?> by <?= e($r['reviewer_name'] ?: 'Admin') ?></small>
        </div>
      </div>
      <?php endif; ?>
      <?php if ($r['submitted_at']): ?>
      <div class="kyc-tl-item">
        <div class="kyc-tl-icon submitted">S</div>
        <div class="kyc-tl-body">
          <b>KYC Submitted</b>
          <small><?= date('F j, Y g:i a', strtotime($r['submitted_at'])) ?> by <?= e($r['user_name']) ?></small>
        </div>
      </div>
      <?php endif; ?>
      <div class="kyc-tl-item">
        <div class="kyc-tl-icon created">+</div>
        <div class="kyc-tl-body">
          <b>KYC Record Created</b>
          <small><?= date('F j, Y g:i a', strtotime($r['created_at'])) ?></small>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function toggleRejectForm() {
  document.getElementById('rejectForm').classList.toggle('open');
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>