<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role(ROLE_FRANCHISOR);

$user = current_user();
$userId = (int)$user['id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id < 1) {
    $stmt = db()->prepare('SELECT id FROM franchises WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1');
    $stmt->execute([$userId]);
    $lastId = (int)$stmt->fetchColumn();
    if ($lastId > 0) {
        redirect('/franchise/edit.php?id=' . $lastId);
    }
    flash_set('error', 'No franchise found. Create one first.');
    redirect('/franchise/create.php');
}

$stmt = db()->prepare('SELECT * FROM franchises WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $userId]);
$franchise = $stmt->fetch();

if (!$franchise) {
    http_response_code(404);
    echo '<h1>Franchise not found</h1>';
    exit;
}

$sectors = db()->query('SELECT id, name FROM sectors ORDER BY name')->fetchAll();
$error = '';

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
    $logoPath = $franchise['logo_path'];

    if ($brandName === '') {
        $error = 'Brand name is required.';
    }

    if (!$error && isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploaded = handle_upload($_FILES['logo'], ['image/jpeg', 'image/png', 'image/webp'], UPLOAD_MAX_BYTES_PHOTO, upload_path('franchise-logos'));
        if (!$uploaded) {
            $error = 'Logo upload failed. Allowed: JPG, PNG, WebP under 2MB.';
        } else {
            if ($franchise['logo_path']) {
                $oldPath = upload_path('franchise-logos') . '/' . $franchise['logo_path'];
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $logoPath = $uploaded;
        }
    }

    if (!$error) {
        $stmt = db()->prepare('UPDATE franchises SET brand_name = ?, sector_id = ?, established_year = ?, existing_units = ?, countries_present = ?, description = ?, ideal_partner_profile = ?, franchise_fee = ?, royalty_pct = ?, marketing_fee_pct = ?, total_investment_min = ?, total_investment_max = ?, expected_payback_months = ?, training_provided = ?, territory_protection = ?, logo_path = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
        $stmt->execute([$brandName, $sectorId, $establishedYear, $existingUnits, $countriesPresent, $description, $idealPartnerProfile, $franchiseFee, $royaltyPct, $marketingFeePct, $investmentMin, $investmentMax, $paybackMonths, $trainingProvided, $territoryProtection, $logoPath, $id, $userId]);
        flash_set('success', 'Franchise profile updated successfully.');
        redirect('/franchise/dashboard');
    }

    $_SESSION['_flash_error'] = $error;
}

$pageTitle = 'Edit Franchise - ' . $franchise['brand_name'];
require __DIR__ . '/../includes/layout-dashboard.php';
?>
<h2 style="margin-bottom:0.25rem;">Edit Franchise Profile</h2>
<p style="color:var(--color-text-muted);margin-bottom:1.5rem;">Update your franchise listing for <strong><?= e($franchise['brand_name']) ?></strong>.</p>

<form method="post" enctype="multipart/form-data" style="max-width:640px;">
  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">

  <div class="r-2">
    <div class="input-group">
      <label>Brand Name <span style="color:var(--color-error);">*</span></label>
      <input type="text" name="brand_name" class="input" value="<?= e($franchise['brand_name']) ?>" required>
    </div>
    <div class="input-group">
      <label>Industry</label>
      <select name="sector_id" class="input">
        <option value="">Select industry</option>
        <?php foreach ($sectors as $s): ?>
        <option value="<?= $s['id'] ?>" <?= (int)$s['id'] === (int)$franchise['sector_id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="input-group">
      <label>Year Established</label>
      <input type="number" name="established_year" class="input" value="<?= e($franchise['established_year']) ?>" min="1900" max="<?= date('Y') ?>">
    </div>
    <div class="input-group">
      <label>Number of Franchisees</label>
      <input type="number" name="existing_units" class="input" value="<?= e($franchise['existing_units']) ?>" min="0">
    </div>
    <div class="input-group">
      <label>Expanding In</label>
      <input type="text" name="countries_present" class="input" value="<?= e($franchise['countries_present']) ?>" placeholder="e.g., Nepal, North India">
    </div>
    <div class="input-group">
      <label>Franchise Fee (NPR)</label>
      <input type="number" name="franchise_fee" class="input" value="<?= e($franchise['franchise_fee']) ?>" step="0.01" min="0">
    </div>
    <div class="input-group">
      <label>Royalty (%)</label>
      <input type="number" name="royalty_pct" class="input" value="<?= e($franchise['royalty_pct']) ?>" step="0.01" min="0" max="100">
    </div>
    <div class="input-group">
      <label>Marketing Fee (%)</label>
      <input type="number" name="marketing_fee_pct" class="input" value="<?= e($franchise['marketing_fee_pct']) ?>" step="0.01" min="0" max="100">
    </div>
    <div class="input-group">
      <label>Total Investment Min (NPR)</label>
      <input type="number" name="total_investment_min" class="input" value="<?= e($franchise['total_investment_min']) ?>" step="0.01" min="0">
    </div>
    <div class="input-group">
      <label>Total Investment Max (NPR)</label>
      <input type="number" name="total_investment_max" class="input" value="<?= e($franchise['total_investment_max']) ?>" step="0.01" min="0">
    </div>
    <div class="input-group">
      <label>Expected Payback (months)</label>
      <input type="number" name="expected_payback_months" class="input" value="<?= e($franchise['expected_payback_months']) ?>" min="0">
    </div>
    <div class="input-group" style="display:flex;align-items:center;gap:1rem;padding-top:1.5rem;">
      <label style="display:flex;align-items:center;gap:0.5rem;">
        <input type="checkbox" name="training_provided" value="1" <?= $franchise['training_provided'] ? 'checked' : '' ?>> Training Provided
      </label>
      <label style="display:flex;align-items:center;gap:0.5rem;">
        <input type="checkbox" name="territory_protection" value="1" <?= $franchise['territory_protection'] ? 'checked' : '' ?>> Territory Protection
      </label>
    </div>
    <div class="input-group">
      <label>Brand Logo</label>
      <input type="file" name="logo" class="input" accept="image/jpeg,image/png,image/webp">
      <?php if ($franchise['logo_path']): ?>
      <div style="margin-top:0.5rem;"><img src="<?= APP_URL ?>/public/uploads/franchise-logos/<?= e($franchise['logo_path']) ?>" style="max-height:60px;border-radius:8px;" alt="Current logo"></div>
      <?php endif; ?>
    </div>
    <div class="input-group" style="grid-column:1/-1;">
      <label>Brand Description</label>
      <textarea name="description" class="input" style="min-height:100px;"><?= e($franchise['description']) ?></textarea>
    </div>
    <div class="input-group" style="grid-column:1/-1;">
      <label>Ideal Partner Profile</label>
      <textarea name="ideal_partner_profile" class="input" style="min-height:80px;"><?= e($franchise['ideal_partner_profile']) ?></textarea>
    </div>
  </div>

  <button type="submit" class="btn btn-primary" style="width:100%;margin-top:1.5rem;">Update Profile</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
</div></div>
