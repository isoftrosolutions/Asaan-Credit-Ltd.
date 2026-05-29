<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role(ROLE_ADVISOR);

$user = current_user();
$userId = (int)$user['id'];

$stmt = db()->prepare('SELECT * FROM advisors WHERE user_id = ?');
$stmt->execute([$userId]);
$advisor = $stmt->fetch();

if (!$advisor) {
    http_response_code(404);
    echo '<h1>Advisor profile not found.</h1>';
    exit;
}

$selectedSpecialties = json_decode($advisor['specialties'] ?? '[]', true) ?: [];
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
        $stmt = db()->prepare('UPDATE advisors SET firm_name = ?, specialties = ?, years_experience = ?, past_deals_count = ?, total_deal_value = ?, credentials = ?, bar_council_id = ?, service_fee_structure = ?, fee_min = ?, fee_max = ?, description = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
        $stmt->execute([$firmName, json_encode($specialties), $yearsExperience, $pastDealsCount, $totalDealValue, $credentials, $barCouncilId, $serviceFeeStructure, $feeMin, $feeMax, $description, $advisor['id'], $userId]);
        flash_set('success', 'Advisor profile updated successfully.');
        redirect('/advisor/dashboard');
    }

    $_SESSION['_flash_error'] = $error;
}

$pageTitle = 'Edit Advisor Profile';
require __DIR__ . '/../includes/layout-dashboard.php';
?>
<h2 style="margin-bottom:0.25rem;">Edit Advisor Profile</h2>
<p style="color:#666;margin-bottom:1.5rem;">Update your professional profile for <strong><?= e($advisor['firm_name']) ?></strong>.</p>

<form method="post" style="max-width:640px;">
  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
    <div class="input-group">
      <label>Full Name / Firm Name <span style="color:#b91c1c;">*</span></label>
      <input type="text" name="firm_name" class="input" value="<?= e($advisor['firm_name']) ?>" required>
    </div>
    <div class="input-group">
      <label>Years of Experience</label>
      <input type="number" name="years_experience" class="input" value="<?= e($advisor['years_experience']) ?>" min="0">
    </div>
    <div class="input-group">
      <label>Past Deals Closed</label>
      <input type="number" name="past_deals_count" class="input" value="<?= e($advisor['past_deals_count']) ?>" min="0">
    </div>
    <div class="input-group">
      <label>Total Deal Value (NPR)</label>
      <input type="number" name="total_deal_value" class="input" value="<?= e($advisor['total_deal_value']) ?>" step="0.01" min="0">
    </div>
    <div class="input-group">
      <label>Service Fee Structure</label>
      <select name="service_fee_structure" class="input">
        <option value="">Select fee structure</option>
        <?php foreach ($feeStructures as $val => $label): ?>
        <option value="<?= $val ?>" <?= $advisor['service_fee_structure'] === $val ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="input-group">
      <label>Fee Min (NPR)</label>
      <input type="number" name="fee_min" class="input" value="<?= e($advisor['fee_min']) ?>" step="0.01" min="0">
    </div>
    <div class="input-group">
      <label>Fee Max (NPR)</label>
      <input type="number" name="fee_max" class="input" value="<?= e($advisor['fee_max']) ?>" step="0.01" min="0">
    </div>
    <div class="input-group">
      <label>Bar Council ID (if applicable)</label>
      <input type="text" name="bar_council_id" class="input" value="<?= e($advisor['bar_council_id']) ?>">
    </div>
    <div class="input-group" style="grid-column:1/-1;">
      <label>Credentials</label>
      <textarea name="credentials" class="input" style="min-height:80px;"><?= e($advisor['credentials']) ?></textarea>
    </div>
    <div class="input-group" style="grid-column:1/-1;">
      <label>Specialties</label>
      <div style="display:flex;flex-wrap:wrap;gap:0.75rem;margin-top:0.5rem;">
        <?php foreach ($specialtyOptions as $val => $label): ?>
        <label style="display:flex;align-items:center;gap:0.4rem;font-size:0.9rem;">
          <input type="checkbox" name="specialties[]" value="<?= $val ?>" <?= in_array($val, $selectedSpecialties) ? 'checked' : '' ?>> <?= $label ?>
        </label>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="input-group" style="grid-column:1/-1;">
      <label>Description & Track Record</label>
      <textarea name="description" class="input" style="min-height:100px;"><?= e($advisor['description']) ?></textarea>
    </div>
  </div>

  <button type="submit" class="btn btn-primary" style="width:100%;margin-top:1.5rem;">Update Profile</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
</div></div>
