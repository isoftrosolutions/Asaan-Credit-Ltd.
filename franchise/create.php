<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role(ROLE_FRANCHISOR);

$user = current_user();
$error = '';
$sectors = db()->query('SELECT id, name FROM sectors ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $brandName = trim($_POST['brand_name'] ?? '');
    $sectorId = !empty($_POST['sector_id']) ? (int)$_POST['sector_id'] : null;
    $establishedYear = !empty($_POST['established_year']) ? (int)$_POST['established_year'] : null;
    $existingUnits = !empty($_POST['existing_units']) ? (int)$_POST['existing_units'] : null;
    $countriesPresent = trim($_POST['countries_present'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $idealPartnerProfile = trim($_POST['ideal_partner_profile'] ?? '');
    $franchiseFee = !empty($_POST['franchise_fee']) ? (float)$_POST['franchise_fee'] : null;
    $royaltyPct = !empty($_POST['royalty_pct']) ? (float)$_POST['royalty_pct'] : null;
    $marketingFeePct = !empty($_POST['marketing_fee_pct']) ? (float)$_POST['marketing_fee_pct'] : null;
    $investmentMin = !empty($_POST['total_investment_min']) ? (float)$_POST['total_investment_min'] : null;
    $investmentMax = !empty($_POST['total_investment_max']) ? (float)$_POST['total_investment_max'] : null;
    $paybackMonths = !empty($_POST['expected_payback_months']) ? (int)$_POST['expected_payback_months'] : null;
    $trainingProvided = !empty($_POST['training_provided']) ? 1 : 0;
    $territoryProtection = !empty($_POST['territory_protection']) ? 1 : 0;
    $logoPath = null;

    if ($brandName === '') {
        $error = 'Brand name is required.';
    }

    if (!$error && isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $logoPath = handle_upload($_FILES['logo'], ['image/jpeg', 'image/png', 'image/webp'], UPLOAD_MAX_BYTES_PHOTO, upload_path('franchise-logos'));
        if (!$logoPath) {
            $error = 'Logo upload failed. Allowed: JPG, PNG, WebP under 2MB.';
        }
    }

    if (!$error) {
        $stmt = db()->prepare('INSERT INTO franchises (user_id, brand_name, sector_id, established_year, existing_units, countries_present, description, ideal_partner_profile, franchise_fee, royalty_pct, marketing_fee_pct, total_investment_min, total_investment_max, expected_payback_months, training_provided, territory_protection, logo_path, is_published, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())');
        $stmt->execute([$userId, $brandName, $sectorId, $establishedYear, $existingUnits, $countriesPresent, $description, $idealPartnerProfile, $franchiseFee, $royaltyPct, $marketingFeePct, $investmentMin, $investmentMax, $paybackMonths, $trainingProvided, $territoryProtection, $logoPath]);
        flash_set('success', 'Franchise profile created successfully.');
        redirect('/franchise/dashboard');
    }

    $_SESSION['_flash_error'] = $error;
}

$pageTitle = 'Create Franchise Profile';
require __DIR__ . '/../includes/layout-dashboard.php';
?>
<h2 style="margin-bottom:0.25rem;">Franchise / Brand Profile</h2>
<p style="color:#666;margin-bottom:1.5rem;">Expand your brand by connecting with qualified franchisees.</p>

<form method="post" enctype="multipart/form-data" style="max-width:640px;">
  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
    <div class="input-group">
      <label>Brand Name <span style="color:#b91c1c;">*</span></label>
      <input type="text" name="brand_name" class="input" value="<?= e(old('brand_name')) ?>" placeholder="e.g., Foodie's Point" required>
    </div>
    <div class="input-group">
      <label>Industry</label>
      <select name="sector_id" class="input">
        <option value="">Select industry</option>
        <?php foreach ($sectors as $s): ?>
        <option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="input-group">
      <label>Year Established</label>
      <input type="number" name="established_year" class="input" value="<?= e(old('established_year')) ?>" placeholder="e.g., 2016" min="1900" max="<?= date('Y') ?>">
    </div>
    <div class="input-group">
      <label>Number of Franchisees</label>
      <input type="number" name="existing_units" class="input" value="<?= e(old('existing_units')) ?>" placeholder="e.g., 48" min="0">
    </div>
    <div class="input-group">
      <label>Expanding In</label>
      <input type="text" name="countries_present" class="input" value="<?= e(old('countries_present')) ?>" placeholder="e.g., Nepal, North India">
    </div>
    <div class="input-group">
      <label>Franchise Fee (NPR)</label>
      <input type="number" name="franchise_fee" class="input" value="<?= e(old('franchise_fee')) ?>" placeholder="e.g., 500000" step="0.01" min="0">
    </div>
    <div class="input-group">
      <label>Royalty (%)</label>
      <input type="number" name="royalty_pct" class="input" value="<?= e(old('royalty_pct')) ?>" placeholder="e.g., 5" step="0.01" min="0" max="100">
    </div>
    <div class="input-group">
      <label>Marketing Fee (%)</label>
      <input type="number" name="marketing_fee_pct" class="input" value="<?= e(old('marketing_fee_pct')) ?>" placeholder="e.g., 2" step="0.01" min="0" max="100">
    </div>
    <div class="input-group">
      <label>Total Investment Min (NPR)</label>
      <input type="number" name="total_investment_min" class="input" value="<?= e(old('total_investment_min')) ?>" placeholder="e.g., 1500000" step="0.01" min="0">
    </div>
    <div class="input-group">
      <label>Total Investment Max (NPR)</label>
      <input type="number" name="total_investment_max" class="input" value="<?= e(old('total_investment_max')) ?>" placeholder="e.g., 2500000" step="0.01" min="0">
    </div>
    <div class="input-group">
      <label>Expected Payback (months)</label>
      <input type="number" name="expected_payback_months" class="input" value="<?= e(old('expected_payback_months')) ?>" placeholder="e.g., 24" min="0">
    </div>
    <div class="input-group" style="display:flex;align-items:center;gap:1rem;padding-top:1.5rem;">
      <label style="display:flex;align-items:center;gap:0.5rem;">
        <input type="checkbox" name="training_provided" value="1" checked> Training Provided
      </label>
      <label style="display:flex;align-items:center;gap:0.5rem;">
        <input type="checkbox" name="territory_protection" value="1"> Territory Protection
      </label>
    </div>
    <div class="input-group">
      <label>Brand Logo</label>
      <input type="file" name="logo" class="input" accept="image/jpeg,image/png,image/webp">
    </div>
    <div class="input-group" style="grid-column:1/-1;">
      <label>Brand Description</label>
      <textarea name="description" class="input" placeholder="Describe your brand, support system, and franchise opportunity..." style="min-height:100px;"><?= e(old('description')) ?></textarea>
    </div>
    <div class="input-group" style="grid-column:1/-1;">
      <label>Ideal Partner Profile</label>
      <textarea name="ideal_partner_profile" class="input" placeholder="Describe your ideal franchise partner..." style="min-height:80px;"><?= e(old('ideal_partner_profile')) ?></textarea>
    </div>
  </div>

  <button type="submit" class="btn btn-primary" style="width:100%;margin-top:1.5rem;">Submit for Review</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
</div></div>
