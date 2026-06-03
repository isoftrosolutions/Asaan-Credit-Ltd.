<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role(ROLE_ADVISOR);

$user = current_user();
$userId = (int)$user['id'];
$specialtyOptions = [
    'm_and_a' => 'M&A Advisory',
    'brokerage' => 'Business Brokerage',
    'legal' => 'Legal',
    'consulting' => 'Consulting',
    'due_diligence' => 'Due Diligence',
];
$feeStructures = ['success_fee' => 'Success Fee', 'retainer' => 'Retainer', 'hourly' => 'Hourly'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $firmName = trim($_POST['firm_name'] ?? '');
    $specialties = !empty($_POST['specialties']) ? $_POST['specialties'] : [];
    $yearsExperience = !empty($_POST['years_experience']) ? (int)$_POST['years_experience'] : null;
    $pastDealsCount = !empty($_POST['past_deals_count']) ? (int)$_POST['past_deals_count'] : null;
    $totalDealValue = !empty($_POST['total_deal_value']) ? (float)$_POST['total_deal_value'] : null;
    $credentials = trim($_POST['credentials'] ?? '');
    $barCouncilId = trim($_POST['bar_council_id'] ?? '');
    $serviceFeeStructure = $_POST['service_fee_structure'] ?? '';
    $feeMin = !empty($_POST['fee_min']) ? (float)$_POST['fee_min'] : null;
    $feeMax = !empty($_POST['fee_max']) ? (float)$_POST['fee_max'] : null;
    $description = trim($_POST['description'] ?? '');

    if ($firmName === '') {
        $error = 'Firm name is required.';
    }
    if ($serviceFeeStructure !== '' && !array_key_exists($serviceFeeStructure, $feeStructures)) {
        $error = 'Invalid fee structure selection.';
    }

    if (!$error) {
        $stmt = db()->prepare('INSERT INTO advisors (user_id, firm_name, specialties, years_experience, past_deals_count, total_deal_value, credentials, bar_council_id, service_fee_structure, fee_min, fee_max, description, is_published, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())');
        $stmt->execute([$userId, $firmName, json_encode($specialties), $yearsExperience, $pastDealsCount, $totalDealValue, $credentials, $barCouncilId, $serviceFeeStructure, $feeMin, $feeMax, $description]);
        flash_set('success', 'Advisor profile created successfully.');
        redirect('/advisor/dashboard');
    }

    $_SESSION['_flash_error'] = $error;
}

$pageTitle = 'Create Advisor Profile';
require __DIR__ . '/../includes/layout-dashboard.php';
?>
<h2 style="margin-bottom:0.25rem;">Advisor / Professional Profile</h2>
<p style="color:var(--color-text-muted);margin-bottom:1.5rem;">Register as an M&A advisor, business broker, consultant, or law firm.</p>

<form method="post" style="max-width:640px;">
  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">

  <div class="r-2">
    <div class="input-group">
      <label>Full Name / Firm Name <span style="color:var(--color-error);">*</span></label>
      <input type="text" name="firm_name" class="input" value="<?= e(old('firm_name')) ?>" placeholder="e.g., Krishna & Associates" required>
    </div>
    <div class="input-group">
      <label>Years of Experience</label>
      <input type="number" name="years_experience" class="input" value="<?= e(old('years_experience')) ?>" placeholder="e.g., 15" min="0">
    </div>
    <div class="input-group">
      <label>Past Deals Closed</label>
      <input type="number" name="past_deals_count" class="input" value="<?= e(old('past_deals_count')) ?>" placeholder="e.g., 50" min="0">
    </div>
    <div class="input-group">
      <label>Total Deal Value (NPR)</label>
      <input type="number" name="total_deal_value" class="input" value="<?= e(old('total_deal_value')) ?>" placeholder="e.g., 50000000" step="0.01" min="0">
    </div>
    <div class="input-group">
      <label>Service Fee Structure</label>
      <select name="service_fee_structure" class="input">
        <option value="">Select fee structure</option>
        <?php foreach ($feeStructures as $val => $label): ?>
        <option value="<?= $val ?>"><?= $label ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="input-group">
      <label>Fee Min (NPR)</label>
      <input type="number" name="fee_min" class="input" value="<?= e(old('fee_min')) ?>" placeholder="e.g., 50000" step="0.01" min="0">
    </div>
    <div class="input-group">
      <label>Fee Max (NPR)</label>
      <input type="number" name="fee_max" class="input" value="<?= e(old('fee_max')) ?>" placeholder="e.g., 200000" step="0.01" min="0">
    </div>
    <div class="input-group">
      <label>Bar Council ID (if applicable)</label>
      <input type="text" name="bar_council_id" class="input" value="<?= e(old('bar_council_id')) ?>" placeholder="e.g., NBC-1234">
    </div>
    <div class="input-group" style="grid-column:1/-1;">
      <label>Credentials</label>
      <textarea name="credentials" class="input" placeholder="List your qualifications, certifications, and professional memberships..." style="min-height:80px;"><?= e(old('credentials')) ?></textarea>
    </div>
    <div class="input-group" style="grid-column:1/-1;">
      <label>Specialties</label>
      <div style="display:flex;flex-wrap:wrap;gap:0.75rem;margin-top:0.5rem;">
        <?php foreach ($specialtyOptions as $val => $label): ?>
        <label style="display:flex;align-items:center;gap:0.4rem;font-size:0.9rem;">
          <input type="checkbox" name="specialties[]" value="<?= $val ?>"> <?= $label ?>
        </label>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="input-group" style="grid-column:1/-1;">
      <label>Description & Track Record</label>
      <textarea name="description" class="input" placeholder="Describe your expertise, past deals closed, industries served..." style="min-height:100px;"><?= e(old('description')) ?></textarea>
    </div>
  </div>

  <button type="submit" class="btn btn-primary" style="width:100%;margin-top:1.5rem;">Submit for Review</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
</div></div>
