<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

$user = current_user();
$userId = (int)$user['id'];

$role = $user['role'];
$isInvestor = in_array($role, ['investor', 'individual_investor']);
$isBusinessOwner = in_array($role, ['owner', 'business_owner', 'ceo', 'cfo']);
$allowedRoles = array_merge(
    ['investor', 'individual_investor', 'owner', 'business_owner', 'ceo', 'cfo', 'entrepreneur', 'franchisor', 'advisor', 'broker']
);
if (!in_array($role, $allowedRoles)) {
    redirect('/dashboard');
}

$destDir = PUBLIC_UPLOADS_PATH . '/kyc-documents';
$allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
$maxBytes = 10 * 1024 * 1024;

$existing = db()->prepare("SELECT * FROM kyc_verifications WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$existing->execute([$userId]);
$kyc = $existing->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (empty($kyc) || $kyc['status'] === 'rejected') {
        $fullName = trim($_POST['full_name'] ?? $user['name']);
        $dob = $_POST['date_of_birth'] ?? null;
        $fatherName = trim($_POST['father_name'] ?? '');
        $idDocType = $_POST['id_document_type'] ?? 'citizenship';
        $idDocNumber = trim($_POST['id_document_number'] ?? '');
        $permAddr = trim($_POST['permanent_address'] ?? '');
        $currAddr = trim($_POST['current_address'] ?? '');

        $bizName = trim($_POST['business_name'] ?? '');
        $bizType = $_POST['business_type'] ?? '';
        $regNumber = trim($_POST['registration_number'] ?? '');
        $regDate = $_POST['registration_date'] ?? null;
        $panVat = trim($_POST['pan_vat'] ?? '');
        $industry = $_POST['industry'] ?? '';
        $bizAddr = trim($_POST['business_address'] ?? '');
        $bizUrl = trim($_POST['business_url'] ?? '');
        $monthlySales = $_POST['monthly_sales'] ?? null;
        $intent = $_POST['intent'] ?? '';
        $bizDesc = trim($_POST['business_description'] ?? '');

        $invType = $_POST['investor_type'] ?? '';
        $ticketMin = $_POST['investment_ticket_min'] ?? null;
        $ticketMax = $_POST['investment_ticket_max'] ?? null;
        $prefSectors = $_POST['preferred_sectors'] ?? '';
        $capitalDeployed = $_POST['total_capital_deployed'] ?? null;
        $pastInv = $_POST['past_investments'] ?? null;

        $idFront = null;
        $idBack = null;
        $regCert = null;
        $panCert = null;
        $finProof = null;
        $bizPhoto = null;

        if (!empty($_FILES['id_front']['name'])) {
            $idFront = handle_upload($_FILES['id_front'], $allowedMime, $maxBytes, $destDir);
        }
        if (!empty($_FILES['id_back']['name'])) {
            $idBack = handle_upload($_FILES['id_back'], $allowedMime, $maxBytes, $destDir);
        }
        if (!empty($_FILES['registration_cert']['name'])) {
            $regCert = handle_upload($_FILES['registration_cert'], $allowedMime, $maxBytes, $destDir);
        }
        if (!empty($_FILES['pan_cert']['name'])) {
            $panCert = handle_upload($_FILES['pan_cert'], $allowedMime, $maxBytes, $destDir);
        }
        if (!empty($_FILES['financial_proof']['name'])) {
            $finProof = handle_upload($_FILES['financial_proof'], $allowedMime, $maxBytes, $destDir);
        }
        if (!empty($_FILES['business_photo']['name'])) {
            $bizPhoto = handle_upload($_FILES['business_photo'], $allowedMime, $maxBytes, $destDir);
        }

        if ($kyc) {
            $idFront = $idFront ?: $kyc['id_front_path'];
            $idBack = $idBack ?: $kyc['id_back_path'];
            $regCert = $regCert ?: $kyc['registration_cert_path'];
            $panCert = $panCert ?: $kyc['pan_cert_path'];
            $finProof = $finProof ?: $kyc['financial_proof_path'];
            $bizPhoto = $bizPhoto ?: $kyc['business_photo_path'];

            $upd = db()->prepare("UPDATE kyc_verifications SET
                full_name=?, date_of_birth=?, father_name=?, id_document_type=?, id_document_number=?,
                permanent_address=?, current_address=?, business_name=?, business_type=?, registration_number=?,
                registration_date=?, pan_vat=?, industry=?, business_address=?, business_url=?,
                monthly_sales=?, intent=?, business_description=?, investor_type=?, investment_ticket_min=?,
                investment_ticket_max=?, preferred_sectors=?, total_capital_deployed=?, past_investments=?,
                id_front_path=?, id_back_path=?, registration_cert_path=?, pan_cert_path=?,
                financial_proof_path=?, business_photo_path=?, status='pending', submitted_at=NOW()
                WHERE id=?");
            $upd->execute([$fullName, $dob, $fatherName, $idDocType, $idDocNumber,
                $permAddr, $currAddr, $bizName, $bizType, $regNumber,
                $regDate, $panVat, $industry, $bizAddr, $bizUrl,
                $monthlySales, $intent, $bizDesc, $invType, $ticketMin,
                $ticketMax, $prefSectors, $capitalDeployed, $pastInv,
                $idFront, $idBack, $regCert, $panCert,
                $finProof, $bizPhoto, $kyc['id']]);
        } else {
            $ins = db()->prepare("INSERT INTO kyc_verifications
                (user_id, role, status, full_name, date_of_birth, father_name, id_document_type, id_document_number,
                 permanent_address, current_address, business_name, business_type, registration_number,
                 registration_date, pan_vat, industry, business_address, business_url,
                 monthly_sales, intent, business_description, investor_type, investment_ticket_min,
                 investment_ticket_max, preferred_sectors, total_capital_deployed, past_investments,
                 id_front_path, id_back_path, registration_cert_path, pan_cert_path,
                 financial_proof_path, business_photo_path, submitted_at)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");
            $ins->execute([$userId, $role, 'pending', $fullName, $dob, $fatherName, $idDocType, $idDocNumber,
                $permAddr, $currAddr, $bizName, $bizType, $regNumber,
                $regDate, $panVat, $industry, $bizAddr, $bizUrl,
                $monthlySales, $intent, $bizDesc, $invType, $ticketMin,
                $ticketMax, $prefSectors, $capitalDeployed, $pastInv,
                $idFront, $idBack, $regCert, $panCert,
                $finProof, $bizPhoto]);
        }

        flash_set('success', 'KYC submitted successfully. Our team will review within 48 hours.');
        redirect('/kyc');
    }

    redirect('/kyc');
}

$pageTitle = 'KYC Verification — ' . APP_NAME;
$forcePublicHeader = false;
require __DIR__ . '/../includes/layout-dashboard.php';

$kycStatus = $kyc['status'] ?? 'none';
$isVerified = $kycStatus === 'verified';
$isPending = $kycStatus === 'pending';
$isRejected = $kycStatus === 'rejected';
$isDraft = $kyc && $kycStatus === 'draft';

$docTypes = [
    'citizenship' => 'Citizenship',
    'passport' => 'Passport',
    'license' => 'Driving License',
    'voter_id' => 'Voter ID',
    'national_id' => 'National ID',
];

$bizTypes = [
    'private_limited' => 'Private Limited',
    'partnership' => 'Partnership',
    'sole_proprietorship' => 'Sole Proprietorship',
    'startup' => 'Startup / Unregistered',
];
$industries = ['Retail / Trading', 'IT / Software', 'Manufacturing', 'Education', 'Tourism / Travel', 'Food / Restaurant', 'Healthcare', 'Agriculture', 'Real Estate', 'Construction', 'Transport / Logistics', 'Energy / Renewable', 'Financial Services', 'Media / Entertainment', 'Other'];
$intents = ['Looking for investment', 'Selling full business', 'Selling partial stake', 'Looking for loan', 'Franchise / Distributorship'];
$invTypes = ['angel' => 'Angel Investor', 'venture_capital' => 'Venture Capital', 'private_equity' => 'Private Equity', 'family_office' => 'Family Office', 'corporate' => 'Corporate Investor', 'lender' => 'Lender', 'individual' => 'Individual Investor'];
$sectorOptions = db()->query("SELECT name FROM sectors WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
?>
<style>
.kyc-wrap { max-width:1100px; margin:0 auto; padding:24px 16px 48px; }
.kyc-hero { display:grid; grid-template-columns:1.1fr 0.9fr; gap:24px; margin-bottom:24px; align-items:stretch; }
.kyc-panel { background:#fff; border-radius:18px; border:1px solid var(--dash-border); box-shadow:var(--dash-shadow); padding:28px; }
.kyc-panel h1 { font-size:clamp(28px,4vw,44px); letter-spacing:-0.04em; margin:0 0 12px; line-height:1.05; }
.kyc-lead { color:var(--dash-ink-soft); font-size:15px; line-height:1.65; margin:0 0 20px; }
.kyc-hero-actions { display:flex; flex-wrap:wrap; gap:10px; }
.kyc-side { padding:20px; display:flex; flex-direction:column; gap:12px; }
.kyc-score { background:var(--color-primary); color:#fff; border-radius:14px; padding:20px; }
.kyc-score small { opacity:.7; display:block; margin-bottom:6px; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; }
.kyc-score strong { font-size:36px; letter-spacing:-.06em; }
.kyc-mini { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.kyc-metric { background:var(--color-bg); border-radius:12px; padding:12px; border:1px solid var(--dash-border); }
.kyc-metric b { display:block; font-size:14px; }
.kyc-metric small { color:var(--dash-ink-soft); font-size:11px; }

.kyc-layout { display:grid; grid-template-columns:260px 1fr; gap:24px; align-items:start; }
.kyc-steps { position:sticky; top:16px; background:#fff; border-radius:18px; border:1px solid var(--dash-border); box-shadow:var(--dash-shadow); padding:16px; }
.kyc-step { display:flex; gap:12px; padding:12px; border-radius:12px; cursor:pointer; border:1px solid transparent; margin-bottom:4px; }
.kyc-step.active { background:var(--color-primary); color:#fff; }
.kyc-step.done { background:#ecfdf5; color:#065f46; border-color:#a7f3d0; }
.kyc-step-num { width:28px; height:28px; border-radius:8px; display:grid; place-items:center; background:#f3f4f6; color:var(--dash-ink); font-weight:800; font-size:13px; flex:0 0 auto; }
.kyc-step.active .kyc-step-num { background:#fff; color:var(--color-primary); }
.kyc-step.done .kyc-step-num { background:#a7f3d0; color:#065f46; }
.kyc-step-title { font-weight:700; font-size:13px; margin-bottom:1px; }
.kyc-step-sub { font-size:11px; opacity:.7; line-height:1.3; }

.kyc-form { background:#fff; border-radius:18px; border:1px solid var(--dash-border); box-shadow:var(--dash-shadow); padding:24px; min-height:500px; }
.kyc-section { display:none; animation:fadeIn .2s ease; }
.kyc-section.active { display:block; }
@keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }
.kyc-section-head { display:flex; justify-content:space-between; gap:12px; align-items:start; margin-bottom:16px; border-bottom:1px solid var(--dash-border); padding-bottom:16px; }
.kyc-section-head h2 { margin:0 0 4px; font-size:22px; letter-spacing:-.03em; }
.kyc-section-head p { margin:0; color:var(--dash-ink-soft); font-size:13px; line-height:1.5; }
.kyc-source { display:inline-flex; align-items:center; gap:4px; padding:4px 10px; border-radius:999px; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; font-size:11px; font-weight:700; white-space:nowrap; }
.kyc-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; }
.kyc-field { display:flex; flex-direction:column; gap:6px; }
.kyc-field.full { grid-column:1/-1; }
.kyc-field label { font-weight:700; font-size:12px; color:#374151; text-transform:uppercase; letter-spacing:.04em; }
.kyc-field .input { width:100%; border:1px solid var(--dash-border); border-radius:10px; padding:10px 12px; font-size:13px; outline:none; background:#fff; }
.kyc-field .input:focus { border-color:var(--color-primary); box-shadow:0 0 0 3px rgba(107,29,34,.08); }
.kyc-field .input[readonly] { background:#f9fafb; color:#4b5563; }
.kyc-field .hint { color:var(--dash-ink-soft); font-size:11px; line-height:1.4; }
.kyc-upload-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
.kyc-upload { border:1.5px dashed #d1d5db; border-radius:14px; padding:16px; background:#fafafa; min-height:140px; display:flex; flex-direction:column; gap:8px; cursor:pointer; transition:all .2s; position:relative; }
.kyc-upload:hover { border-color:var(--color-primary); background:#fff; }
.kyc-upload.drag-over { border-color:var(--color-primary); background:#fef2f2; border-style:solid; box-shadow:0 0 0 3px rgba(107,29,34,.08); }
.kyc-upload.has-file { border-style:solid; border-color:#a7f3d0; background:#f0fdf4; }
.kyc-upload b { font-size:13px; }
.kyc-upload small { color:var(--dash-ink-soft); font-size:11px; line-height:1.3; }
.kyc-upload .input-file-hidden { display:none; }
.kyc-upload-icon { font-size:28px; line-height:1; margin-bottom:2px; opacity:.4; transition:opacity .2s; }
.kyc-upload:hover .kyc-upload-icon { opacity:.7; }
.kyc-upload-preview { width:100%; max-height:120px; object-fit:contain; border-radius:8px; background:#fff; margin-top:4px; }
.kyc-upload-meta { display:flex; align-items:center; gap:6px; font-size:11px; color:var(--dash-ink-soft); flex-wrap:wrap; }
.kyc-upload-meta .remove { color:var(--color-error); cursor:pointer; font-weight:700; margin-left:auto; padding:2px 6px; border-radius:6px; }
.kyc-upload-meta .remove:hover { background:#fef2f2; }
.kyc-upload-status { position:absolute; top:8px; right:8px; width:20px; height:20px; border-radius:50%; display:grid; place-items:center; font-size:11px; font-weight:700; }
.kyc-review-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:16px; }
.kyc-review-box { background:#f9fafb; border:1px solid var(--dash-border); border-radius:12px; padding:12px; }
.kyc-review-box small { color:var(--dash-ink-soft); display:block; margin-bottom:3px; font-size:11px; text-transform:uppercase; letter-spacing:.04em; font-weight:600; }
.kyc-review-box b { font-size:13px; overflow-wrap:anywhere; }
.kyc-risk { display:grid; gap:8px; margin-top:12px; }
.kyc-risk-item { display:flex; justify-content:space-between; align-items:center; padding:10px 12px; border-radius:12px; border:1px solid var(--dash-border); background:#fff; gap:8px; font-size:13px; }
.kyc-badge { font-size:11px; font-weight:800; padding:4px 8px; border-radius:999px; white-space:nowrap; }
.kyc-badge.ok { background:#ecfdf5; color:#047857; }
.kyc-badge.warn { background:#fff7ed; color:#b45309; }
.kyc-badge.danger { background:#fef2f2; color:#b91c1c; }
.kyc-actions { display:flex; justify-content:space-between; gap:12px; margin-top:20px; border-top:1px solid var(--dash-border); padding-top:16px; }
.kyc-declaration { display:flex; align-items:flex-start; gap:10px; background:#f9fafb; border:1px solid var(--dash-border); border-radius:12px; padding:12px; line-height:1.5; margin-top:14px; font-size:13px; color:#374151; }
.kyc-declaration input { width:auto; margin-top:3px; }
.kyc-alert { padding:14px 18px; border-radius:14px; margin-bottom:20px; font-size:14px; line-height:1.5; }
.kyc-alert.success { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
.kyc-alert.warn { background:#fff7ed; color:#9a3412; border:1px solid #fed7aa; }
.kyc-alert.error { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
.kyc-alert.info { background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; }
.hidden { display:none !important; }

@media (max-width:880px) {
  .kyc-hero, .kyc-layout { grid-template-columns:1fr; }
  .kyc-steps { position:static; }
  .kyc-grid, .kyc-upload-grid, .kyc-review-grid { grid-template-columns:1fr; }
  .kyc-section-head { flex-direction:column; }
}
</style>

<div class="kyc-wrap">
  <?php if ($isVerified): ?>
  <div class="kyc-alert success">
    <strong>KYC Verified ✓</strong> — Your identity has been verified. You can now fully access all platform features.
    <?php if ($kyc['reviewed_at']): ?><br><small>Reviewed on <?= date('F j, Y', strtotime($kyc['reviewed_at'])) ?></small><?php endif; ?>
  </div>
  <?php elseif ($isPending): ?>
  <div class="kyc-alert info">
    <strong>KYC under review</strong> — Your documents are being reviewed. This typically takes 24-48 hours.
    <?php if ($kyc['submitted_at']): ?><br><small>Submitted on <?= date('F j, Y', strtotime($kyc['submitted_at'])) ?></small><?php endif; ?>
  </div>
  <?php elseif ($isRejected): ?>
  <div class="kyc-alert error">
    <strong>KYC rejected</strong> — <?= e($kyc['rejection_reason'] ?? 'Please correct the issues below and resubmit.') ?>
  </div>
  <?php endif; ?>

  <?php if (!$isVerified): ?>
  <div class="kyc-hero">
    <div class="kyc-panel">
      <h1><?= $isInvestor ? 'Verify your investor identity' : 'Verify your business identity' ?></h1>
      <p class="kyc-lead">One-time verification to build trust. We pre-fill what we already know — you just fill in the gaps and upload documents.</p>
      <div class="kyc-hero-actions">
        <?php if ($isPending): ?>
        <span class="btn btn-primary" style="opacity:.7;cursor:default;">Under review</span>
        <?php else: ?>
        <button class="btn btn-primary" onclick="goStep(0)"><?= $isRejected || $isDraft ? 'Continue verification' : 'Start verification' ?></button>
        <?php endif; ?>
      </div>
    </div>
    <aside class="kyc-panel kyc-side">
      <div class="kyc-score">
        <small>Trust score</small>
        <strong id="trustScore"><?= $kyc ? '65%' : '0%' ?></strong>
        <div style="margin-top:6px;opacity:.8;font-size:13px;">Complete KYC to unlock full platform access.</div>
      </div>
      <div class="kyc-mini">
        <div class="kyc-metric"><b id="filledCount">0/<?= $isBusinessOwner ? '18' : '15' ?></b><small>Fields done</small></div>
        <div class="kyc-metric"><b><?= $isBusinessOwner ? '6' : '4' ?> docs</b><small>Required uploads</small></div>
        <div class="kyc-metric"><b>Manual</b><small>Analyst review</small></div>
        <div class="kyc-metric"><b>Private</b><small>Never shared</small></div>
      </div>
    </aside>
  </div>

  <div class="kyc-layout">
    <aside class="kyc-steps">
      <?php $steps = [
        ['Owner identity', 'Personal & ID details'],
        [$isBusinessOwner ? 'Business profile' : 'Investment profile', $isBusinessOwner ? 'Registration, PAN, industry' : 'Preferences, sectors, ticket size'],
        ['Documents', 'Upload supporting files'],
        ['Review & submit', 'Risk checks, consent, submit'],
      ]; foreach ($steps as $i => $s): ?>
      <div class="kyc-step <?= $i === 0 ? 'active' : '' ?>" onclick="goStep(<?= $i ?>)">
        <div class="kyc-step-num"><?= $i + 1 ?></div>
        <div>
          <div class="kyc-step-title"><?= $s[0] ?></div>
          <div class="kyc-step-sub"><?= $s[1] ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </aside>

    <form class="kyc-form" method="POST" enctype="multipart/form-data" onsubmit="return validateKYC()">
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
      <input type="hidden" name="full_name" value="<?= e($kyc['full_name'] ?? $user['name']) ?>">

      <!-- Step 1: Owner Identity -->
      <div class="kyc-section active" data-step="0">
        <div class="kyc-section-head">
          <div>
            <h2>Owner identity</h2>
            <p>Pre-filled from your account. Confirm or add missing fields.</p>
          </div>
          <span class="kyc-source">From your account</span>
        </div>
        <div class="kyc-grid">
          <div class="kyc-field">
            <label>Full name</label>
            <input class="input" value="<?= e($user['name']) ?>" readonly>
          </div>
          <div class="kyc-field">
            <label>Mobile number</label>
            <input class="input" value="<?= e($user['phone'] ?? '') ?>" readonly>
          </div>
          <div class="kyc-field">
            <label>Email address</label>
            <input class="input" value="<?= e($user['email']) ?>" readonly>
          </div>
          <div class="kyc-field">
            <label>ID document type</label>
            <select name="id_document_type" class="input" required>
              <?php foreach ($docTypes as $val => $label): ?>
              <option value="<?= $val ?>" <?= ($kyc['id_document_type'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="kyc-field">
            <label>ID document number</label>
            <input class="input" name="id_document_number" value="<?= e($kyc['id_document_number'] ?? '') ?>" placeholder="Enter document number" required>
          </div>
          <div class="kyc-field">
            <label>Date of birth</label>
            <input class="input" name="date_of_birth" type="date" value="<?= e($kyc['date_of_birth'] ?? '') ?>" required>
          </div>
          <div class="kyc-field">
            <label>Father's name</label>
            <input class="input" name="father_name" value="<?= e($kyc['father_name'] ?? '') ?>" placeholder="As written on your ID">
          </div>
          <div class="kyc-field">
            <label>Permanent address</label>
            <input class="input" name="permanent_address" value="<?= e($kyc['permanent_address'] ?? ($user['district'] ? $user['province'] . ', ' . $user['district'] : '')) ?>" placeholder="Province, district, municipality, ward" required>
          </div>
          <div class="kyc-field">
            <label>Current address</label>
            <input class="input" name="current_address" value="<?= e($kyc['current_address'] ?? '') ?>" placeholder="If same as permanent, repeat">
          </div>
        </div>
        <div class="kyc-actions">
          <span></span>
          <button type="button" class="btn btn-primary" onclick="nextStep()">Continue</button>
        </div>
      </div>

      <!-- Step 2: Business or Investment Profile -->
      <div class="kyc-section" data-step="1">
        <div class="kyc-section-head">
          <div>
            <h2><?= $isBusinessOwner ? 'Business profile' : 'Investment profile' ?></h2>
            <p><?= $isBusinessOwner ? 'Your business registration and financial details.' : 'Your investment preferences and track record.' ?></p>
          </div>
          <span class="kyc-source">From your profile</span>
        </div>

        <?php if ($isBusinessOwner): ?>
        <div class="kyc-grid">
          <div class="kyc-field">
            <label>Business name</label>
            <input class="input" name="business_name" value="<?= e($kyc['business_name'] ?? '') ?>" placeholder="Registered business name" required>
          </div>
          <div class="kyc-field">
            <label>Business type</label>
            <select name="business_type" class="input" required onchange="toggleBizType(this.value)">
              <option value="">Select type</option>
              <?php foreach ($bizTypes as $val => $label): ?>
              <option value="<?= $val ?>" <?= ($kyc['business_type'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="kyc-field company-field">
            <label>Company registration number</label>
            <input class="input" name="registration_number" value="<?= e($kyc['registration_number'] ?? '') ?>" placeholder="OCR registration number">
          </div>
          <div class="kyc-field company-field">
            <label>Registration date</label>
            <input class="input" name="registration_date" type="date" value="<?= e($kyc['registration_date'] ?? '') ?>">
          </div>
          <div class="kyc-field">
            <label>PAN / VAT number</label>
            <input class="input" name="pan_vat" value="<?= e($kyc['pan_vat'] ?? '') ?>" placeholder="9-digit PAN/VAT">
          </div>
          <div class="kyc-field">
            <label>Industry</label>
            <select name="industry" class="input">
              <option value="">Select industry</option>
              <?php foreach ($industries as $ind): ?>
              <option value="<?= $ind ?>" <?= ($kyc['industry'] ?? '') === $ind ? 'selected' : '' ?>><?= $ind ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="kyc-field full">
            <label>Business address</label>
            <input class="input" name="business_address" value="<?= e($kyc['business_address'] ?? '') ?>" placeholder="Province, district, municipality, ward" required>
          </div>
          <div class="kyc-field">
            <label>Website / Facebook page</label>
            <input class="input" name="business_url" value="<?= e($kyc['business_url'] ?? '') ?>" placeholder="https://">
          </div>
          <div class="kyc-field">
            <label>Monthly run-rate sales (NPR)</label>
            <input class="input" name="monthly_sales" type="number" value="<?= e($kyc['monthly_sales'] ?? '') ?>" placeholder="Approximate monthly revenue">
          </div>
          <div class="kyc-field">
            <label>Investment / sale intent</label>
            <select name="intent" class="input">
              <option value="">Select intent</option>
              <?php foreach ($intents as $int): ?>
              <option value="<?= $int ?>" <?= ($kyc['intent'] ?? '') === $int ? 'selected' : '' ?>><?= $int ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="kyc-field full">
            <label>Short business description</label>
            <textarea class="input" name="business_description" rows="3" placeholder="What the business does, years operating, customers, and reason for raising/selling."><?= e($kyc['business_description'] ?? '') ?></textarea>
          </div>
        </div>
        <?php else: ?>
        <div class="kyc-grid">
          <div class="kyc-field">
            <label>Investor type</label>
            <select name="investor_type" class="input">
              <option value="">Select type</option>
              <?php foreach ($invTypes as $val => $label): ?>
              <option value="<?= $val ?>" <?= ($kyc['investor_type'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="kyc-field">
            <label>Preferred sectors</label>
            <select name="preferred_sectors" class="input">
              <option value="">All sectors</option>
              <?php foreach ($sectorOptions as $sec): ?>
              <option value="<?= e($sec) ?>" <?= ($kyc['preferred_sectors'] ?? '') === $sec ? 'selected' : '' ?>><?= e($sec) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="kyc-field">
            <label>Minimum ticket size (NPR)</label>
            <input class="input" name="investment_ticket_min" type="number" value="<?= e($kyc['investment_ticket_min'] ?? '') ?>" placeholder="Min investment per deal">
          </div>
          <div class="kyc-field">
            <label>Maximum ticket size (NPR)</label>
            <input class="input" name="investment_ticket_max" type="number" value="<?= e($kyc['investment_ticket_max'] ?? '') ?>" placeholder="Max investment per deal">
          </div>
          <div class="kyc-field">
            <label>Total capital deployed (NPR)</label>
            <input class="input" name="total_capital_deployed" type="number" value="<?= e($kyc['total_capital_deployed'] ?? '') ?>" placeholder="Approximate total invested">
          </div>
          <div class="kyc-field">
            <label>Past investments (count)</label>
            <input class="input" name="past_investments" type="number" value="<?= e($kyc['past_investments'] ?? '') ?>" placeholder="Number of previous deals">
          </div>
        </div>
        <?php endif; ?>

        <div class="kyc-actions">
          <button type="button" class="btn btn-outline" onclick="prevStep()">Back</button>
          <button type="button" class="btn btn-primary" onclick="nextStep()">Continue</button>
        </div>
      </div>

      <!-- Step 3: Documents -->
      <div class="kyc-section" data-step="2">
        <div class="kyc-section-head">
          <div>
            <h2>Documents</h2>
            <p>Upload clear scans or photos. Accepted: JPG, PNG, PDF (max 10MB each).</p>
          </div>
          <span class="kyc-source">Stored securely</span>
        </div>
        <div class="kyc-upload-grid">
          <div class="kyc-upload" data-name="id_front" data-label="ID Front">
            <span class="kyc-upload-icon">🪪</span>
            <div><b>ID front</b><br><small>Citizenship, passport, license, or voter ID front side.</small></div>
            <input class="input-file-hidden" type="file" name="id_front" accept="image/*,.pdf" <?= empty($kyc['id_front_path']) ? 'required' : '' ?>>
            <?php if (!empty($kyc['id_front_path'])): ?><small class="kyc-upload-meta"><span class="prev-label">✓ Uploaded</span></small><?php endif; ?>
          </div>
          <div class="kyc-upload" data-name="id_back" data-label="ID Back">
            <span class="kyc-upload-icon">🪪</span>
            <div><b>ID back</b><br><small>Back side of your ID document.</small></div>
            <input class="input-file-hidden" type="file" name="id_back" accept="image/*,.pdf" <?= empty($kyc['id_back_path']) ? 'required' : '' ?>>
            <?php if (!empty($kyc['id_back_path'])): ?><small class="kyc-upload-meta"><span class="prev-label">✓ Uploaded</span></small><?php endif; ?>
          </div>
          <?php if ($isBusinessOwner): ?>
          <div class="kyc-upload company-field" data-name="registration_cert" data-label="Registration Cert">
            <span class="kyc-upload-icon">📄</span>
            <div><b>Company registration cert</b><br><small>OCR certificate or local registration proof.</small></div>
            <input class="input-file-hidden" type="file" name="registration_cert" accept="image/*,.pdf">
            <?php if (!empty($kyc['registration_cert_path'])): ?><small class="kyc-upload-meta"><span class="prev-label">✓ Uploaded</span></small><?php endif; ?>
          </div>
          <?php endif; ?>
          <div class="kyc-upload" data-name="pan_cert" data-label="PAN Certificate">
            <span class="kyc-upload-icon">📋</span>
            <div><b>PAN / VAT certificate</b><br><small>PAN or VAT registration certificate if available.</small></div>
            <input class="input-file-hidden" type="file" name="pan_cert" accept="image/*,.pdf">
            <?php if (!empty($kyc['pan_cert_path'])): ?><small class="kyc-upload-meta"><span class="prev-label">✓ Uploaded</span></small><?php endif; ?>
          </div>
          <div class="kyc-upload" data-name="financial_proof" data-label="Financial Proof">
            <span class="kyc-upload-icon">💰</span>
            <div><b>Financial proof</b><br><small>Bank statement, sales report, invoices, or tax filing.</small></div>
            <input class="input-file-hidden" type="file" name="financial_proof" accept="image/*,.pdf">
            <?php if (!empty($kyc['financial_proof_path'])): ?><small class="kyc-upload-meta"><span class="prev-label">✓ Uploaded</span></small><?php endif; ?>
          </div>
          <div class="kyc-upload" data-name="business_photo" data-label="Business Photo">
            <span class="kyc-upload-icon">📸</span>
            <div><b>Business proof photo</b><br><small>Shop front, office, product, or operating proof.</small></div>
            <input class="input-file-hidden" type="file" name="business_photo" accept="image/*,.pdf">
            <?php if (!empty($kyc['business_photo_path'])): ?><small class="kyc-upload-meta"><span class="prev-label">✓ Uploaded</span></small><?php endif; ?>
          </div>
        </div>
        <div class="kyc-actions">
          <button type="button" class="btn btn-outline" onclick="prevStep()">Back</button>
          <button type="button" class="btn btn-primary" onclick="goStep(3);buildReview()">Review</button>
        </div>
      </div>

      <!-- Step 4: Review & Submit -->
      <div class="kyc-section" data-step="3">
        <div class="kyc-section-head">
          <div>
            <h2>Review & submit</h2>
            <p>Confirm your details before submitting for verification.</p>
          </div>
          <span class="kyc-source">Final step</span>
        </div>
        <div class="kyc-review-grid" id="reviewGrid"></div>

        <h3 style="margin:16px 0 8px;font-size:15px;">Automated checks</h3>
        <div class="kyc-risk">
          <div class="kyc-risk-item">
            <span>Mobile and email verified</span>
            <span class="kyc-badge ok">Passed</span>
          </div>
          <div class="kyc-risk-item">
            <span>PAN/VAT format check</span>
            <span class="kyc-badge warn" id="panCheck">Needs review</span>
          </div>
          <div class="kyc-risk-item">
            <span><?= $isBusinessOwner ? 'Company registration match' : 'Investor profile completeness' ?></span>
            <span class="kyc-badge warn">Manual check</span>
          </div>
          <div class="kyc-risk-item">
            <span>Duplicate profile check</span>
            <span class="kyc-badge ok">No duplicate found</span>
          </div>
          <div class="kyc-risk-item">
            <span>Document clarity & consistency</span>
            <span class="kyc-badge warn">Analyst review</span>
          </div>
        </div>

        <label class="kyc-declaration">
          <input type="checkbox" id="kycConsent" required>
          <span>I confirm that all details and documents are true. False information may lead to account rejection, profile removal, or legal reporting.</span>
        </label>

        <div class="kyc-actions">
          <button type="button" class="btn btn-outline" onclick="prevStep()">Back</button>
          <button type="submit" class="btn btn-primary">Submit for verification</button>
        </div>
      </div>
    </form>
  </div>
  <?php endif; ?>
</div>

<script>
let currentStep = 0;

function q(name) { return document.querySelector(`[name="${name}"]`); }

function goStep(index) {
  currentStep = index;
  document.querySelectorAll('.kyc-section').forEach((s, i) => s.classList.toggle('active', i === index));
  document.querySelectorAll('.kyc-step').forEach((s, i) => {
    s.classList.toggle('active', i === index);
    s.classList.toggle('done', i < index);
  });
  updateProgress();
  if (index === 3) buildReview();
  document.querySelector('.kyc-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function nextStep() { if (currentStep < 3) goStep(currentStep + 1); }
function prevStep() { if (currentStep > 0) goStep(currentStep - 1); }

function toggleBizType(val) {
  const show = val !== 'startup' && val !== 'sole_proprietorship';
  document.querySelectorAll('.company-field').forEach(el => el.classList.toggle('hidden', !show));
}

String.prototype.toNepaliNum = function(){return this.replace(/\d/g,d=>'०१२३४५६७८९'[d]);};

function updateProgress() {
  const fields = [...document.querySelectorAll('.kyc-form .input, .kyc-form textarea')];
  const completed = fields.filter(f => f.value && f.type !== 'file' && !f.closest('.hidden')).length;
  const visible = fields.filter(f => f.type !== 'file' && !f.closest('.hidden')).length;
  const docs = document.querySelectorAll('.kyc-upload.has-file');
  const totalDocs = document.querySelectorAll('.kyc-upload:not(.hidden)').length;
  const docPct = totalDocs ? Math.round(docs.length / totalDocs * 25) : 0;
  const fieldPct = visible ? Math.round(completed / visible * 75) : 0;
  const pct = Math.min(100, docPct + fieldPct);
  document.getElementById('filledCount').innerText = completed + '/' + visible + ' + ' + docs.length + '/' + totalDocs + ' docs';
  document.getElementById('trustScore').innerText = Math.max(<?= $kyc ? '35' : '0' ?>, pct) + '%';
}

function buildReview() {
  const labels = ['full_name','id_document_type','id_document_number','date_of_birth','father_name','permanent_address','current_address'];
  if (document.querySelector('[name="business_name"]')) {
    labels.push('business_name','business_type','registration_number','pan_vat','industry','business_address','intent','monthly_sales');
  } else {
    labels.push('investor_type','preferred_sectors','investment_ticket_min','investment_ticket_max','total_capital_deployed','past_investments');
  }
  const html = labels.map(name => {
    const el = q(name);
    const val = el ? (el.value || 'Not provided') : 'Not provided';
    const label = name.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase());
    return '<div class="kyc-review-box"><small>' + label + '</small><b>' + escHtml(val) + '</b></div>';
  }).join('');
  document.getElementById('reviewGrid').innerHTML = html || '<p style="color:var(--dash-ink-soft);">Review data will appear here.</p>';

  const pan = (q('pan_vat')?.value || '').replace(/\D/g, '');
  const pc = document.getElementById('panCheck');
  if (/^\d{9}$/.test(pan)) { pc.className = 'kyc-badge ok'; pc.innerText = 'Format passed'; }
  else { pc.className = 'kyc-badge warn'; pc.innerText = 'Needs review'; }
}

function escHtml(s) {
  const d = document.createElement('div');
  d.textContent = s;
  return d.innerHTML;
}

function validateKYC() {
  if (!document.getElementById('kycConsent')?.checked) {
    alert('Please accept the declaration before submitting.');
    return false;
  }
  return true;
}

function formatFileSize(bytes) {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / 1048576).toFixed(1) + ' MB';
}

function setupFileUploads() {
  document.querySelectorAll('.kyc-upload').forEach(zone => {
    const input = zone.querySelector('.input-file-hidden');
    if (!input) return;

    zone.addEventListener('click', e => {
      if (e.target.closest('.remove')) return;
      input.click();
    });

    zone.addEventListener('dragover', e => {
      e.preventDefault();
      e.stopPropagation();
      zone.classList.add('drag-over');
    });
    zone.addEventListener('dragleave', e => {
      e.preventDefault();
      e.stopPropagation();
      zone.classList.remove('drag-over');
    });
    zone.addEventListener('drop', e => {
      e.preventDefault();
      e.stopPropagation();
      zone.classList.remove('drag-over');
      if (e.dataTransfer.files.length) {
        input.files = e.dataTransfer.files;
        input.dispatchEvent(new Event('change'));
      }
    });

    input.addEventListener('change', function() {
      const file = this.files[0];
      if (!file) return;

      const meta = zone.querySelector('.kyc-upload-meta') || zone.appendChild(document.createElement('small'));
      if (!zone.querySelector('.kyc-upload-meta')) {
        meta.className = 'kyc-upload-meta';
      }

      const existingPreview = zone.querySelector('.kyc-upload-preview');
      if (existingPreview) existingPreview.remove();

      const existingStatus = zone.querySelector('.kyc-upload-status');
      if (existingStatus) existingStatus.remove();

      zone.classList.add('has-file');
      zone.querySelector('.input-file-hidden')?.removeAttribute('required');

      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const img = document.createElement('img');
          img.className = 'kyc-upload-preview';
          img.src = e.target.result;
          img.alt = file.name;
          zone.insertBefore(img, meta);
        };
        reader.readAsDataURL(file);
        meta.innerHTML = '<span class="prev-label">📷 ' + file.name + ' (' + formatFileSize(file.size) + ')</span><span class="remove" onclick="removeFile(this)">Remove</span>';
      } else {
        meta.innerHTML = '<span class="prev-label">📄 ' + file.name + ' (' + formatFileSize(file.size) + ')</span><span class="remove" onclick="removeFile(this)">Remove</span>';
      }
      const status = document.createElement('span');
      status.className = 'kyc-upload-status';
      status.style.cssText = 'background:#a7f3d0;color:#065f46;';
      status.textContent = '✓';
      zone.appendChild(status);
      updateProgress();
    });

    if (zone.querySelector('.prev-label')) {
      zone.classList.add('has-file');
    }
  });
}

function removeFile(el) {
  const zone = el.closest('.kyc-upload');
  const input = zone.querySelector('.input-file-hidden');
  if (input) {
    input.value = '';
    if (zone.dataset.required === 'true' || input.hasAttribute('required')) {
    } else {
      input.removeAttribute('required');
    }
  }
  zone.classList.remove('has-file');
  const preview = zone.querySelector('.kyc-upload-preview');
  if (preview) preview.remove();
  const status = zone.querySelector('.kyc-upload-status');
  if (status) status.remove();
  const meta = zone.querySelector('.kyc-upload-meta');
  if (meta) meta.innerHTML = '<span class="prev-label">Click or drag to upload</span>';
  updateProgress();
}

document.addEventListener('keydown', e => {
  if (e.key === 'Enter' && document.activeElement?.closest('.kyc-form')) {
    const section = document.querySelector('.kyc-section.active');
    if (section && !section.querySelector('button[type="submit"]')) {
      e.preventDefault();
      nextStep();
    }
  }
});

<?php if ($kyc): ?>
toggleBizType('<?= e($kyc['business_type'] ?? '') ?>');
<?php endif; ?>
setupFileUploads();
updateProgress();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
