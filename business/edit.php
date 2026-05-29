<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role(ROLE_BUSINESS_OWNER);

$user = current_user();
$userId = (int)$user['id'];
$businessId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($businessId < 1) {
    flash_set('error', 'Invalid business ID.');
    redirect('/business/dashboard.php');
}

$stmt = db()->prepare('SELECT * FROM businesses WHERE id = ? AND user_id = ?');
$stmt->execute([$businessId, $userId]);
$business = $stmt->fetch();

if (!$business) {
    flash_set('error', 'Business not found.');
    redirect('/business/dashboard.php');
}

$sectors = db()->query('SELECT id, name FROM sectors WHERE is_active = 1 ORDER BY name')->fetchAll();

$provinces = ['Bagmati', 'Gandaki', 'Karnali', 'Lumbini', 'Sudurpashchim', 'Koshi', 'Madhesh'];

$listingTypes = [
    'sale' => 'Business for Sale',
    'partial_stake' => 'Partial Stake Sale',
    'investment' => 'Investment Opportunity',
    'loan' => 'Business Loan',
    'asset_sale' => 'Asset Sale',
];

$photoStmt = db()->prepare('SELECT * FROM business_photos WHERE business_id = ? ORDER BY sort_order');
$photoStmt->execute([$businessId]);
$photos = $photoStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $businessName = trim($_POST['business_name'] ?? '');
    $listingType = $_POST['listing_type'] ?? '';
    $sectorId = !empty($_POST['sector_id']) ? (int)$_POST['sector_id'] : null;
    $province = trim($_POST['province'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $establishedYear = !empty($_POST['established_year']) ? (int)$_POST['established_year'] : null;
    $employeeCount = !empty($_POST['employee_count']) ? (int)$_POST['employee_count'] : null;
    $annualRevenue = !empty($_POST['annual_revenue']) ? (float)$_POST['annual_revenue'] : null;
    $ebitdaPct = !empty($_POST['ebitda_pct']) ? (float)$_POST['ebitda_pct'] : null;
    $askingPrice = !empty($_POST['asking_price']) ? (float)$_POST['asking_price'] : null;
    $stakeOfferedPct = !empty($_POST['stake_offered_pct']) ? (float)$_POST['stake_offered_pct'] : null;
    $loanAmount = !empty($_POST['loan_amount']) ? (float)$_POST['loan_amount'] : null;
    $loanInterestPct = !empty($_POST['loan_interest_pct']) ? (float)$_POST['loan_interest_pct'] : null;
    $description = trim($_POST['description'] ?? '');
    $reasonForSale = trim($_POST['reason_for_sale'] ?? '');
    $assetsIncluded = trim($_POST['assets_included'] ?? '');
    $isPublished = !empty($_POST['is_published']) ? 1 : 0;

    if ($businessName === '' || $listingType === '') {
        flash_set('error', 'Business name and listing type are required.');
        redirect_back();
    }

    $db = db();
    $db->beginTransaction();

    try {
        $updateStmt = $db->prepare('UPDATE businesses SET business_name = ?, listing_type = ?, sector_id = ?, province = ?, district = ?, established_year = ?, employee_count = ?, annual_revenue = ?, ebitda_pct = ?, asking_price = ?, stake_offered_pct = ?, loan_amount = ?, loan_interest_pct = ?, description = ?, reason_for_sale = ?, assets_included = ?, is_published = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
        $updateStmt->execute([$businessName, $listingType, $sectorId, $province, $district, $establishedYear, $employeeCount, $annualRevenue, $ebitdaPct, $askingPrice, $stakeOfferedPct, $loanAmount, $loanInterestPct, $description, $reasonForSale, $assetsIncluded, $isPublished, $businessId, $userId]);

        if (!empty($_POST['delete_photos'])) {
            $deleteIds = array_map('intval', $_POST['delete_photos']);
            $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
            $delStmt = $db->prepare("SELECT file_path FROM business_photos WHERE id IN ($placeholders) AND business_id = ?");
            $delStmt->execute(array_merge($deleteIds, [$businessId]));
            $toDelete = $delStmt->fetchAll();

            $delExec = $db->prepare("DELETE FROM business_photos WHERE id IN ($placeholders) AND business_id = ?");
            $delExec->execute(array_merge($deleteIds, [$businessId]));

            $destDir = upload_path('business-photos');
            foreach ($toDelete as $p) {
                $path = $destDir . '/' . $p['file_path'];
                if (file_exists($path)) unlink($path);
            }
        }

        if (!empty($_FILES['photos'])) {
            $files = $_FILES['photos'];
            $maxSortStmt = $db->prepare('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM business_photos WHERE business_id = ?');
            $maxSortStmt->execute([$businessId]);
            $sortOrder = (int)$maxSortStmt->fetchColumn();
            $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
            $destDir = upload_path('business-photos');

            foreach ($files['tmp_name'] as $i => $tmpName) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
                $file = [
                    'name' => $files['name'][$i],
                    'tmp_name' => $tmpName,
                    'size' => $files['size'][$i],
                    'error' => $files['error'][$i],
                ];
                $filename = handle_upload($file, $allowedMime, UPLOAD_MAX_BYTES_PHOTO, $destDir);
                if ($filename) {
                    $photoStmt = $db->prepare('INSERT INTO business_photos (business_id, file_path, sort_order, created_at) VALUES (?, ?, ?, NOW())');
                    $photoStmt->execute([$businessId, $filename, $sortOrder]);
                    $sortOrder++;
                }
            }
        }

        $db->commit();
        flash_set('success', 'Business listing updated successfully.');
        redirect('/business/edit.php?id=' . $businessId);
    } catch (\Throwable $e) {
        $db->rollBack();
        flash_set('error', 'Failed to update listing. Please try again.');
        if (DEBUG_MODE) error_log('business edit error: ' . $e->getMessage());
        redirect_back();
    }
}

$pageTitle = 'Edit Business Listing';
require __DIR__ . '/../includes/layout-dashboard.php';
?>
<h2 style="margin-bottom:0.25rem;">Edit Business Listing</h2>
<p style="color:#666;">Update your business information and photos.</p>

<form method="POST" enctype="multipart/form-data" style="margin-top:1.5rem;">
    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="id" value="<?= $businessId ?>">

    <div class="card" style="margin-bottom:1.5rem;">
        <h4>Basic Information</h4>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="input-group">
                <label>Business Name <span class="required">*</span></label>
                <input type="text" name="business_name" class="input" value="<?= e($business['business_name']) ?>" required>
            </div>
            <div class="input-group">
                <label>Listing Type <span class="required">*</span></label>
                <select name="listing_type" class="input" required>
                    <option value="">Select type...</option>
                    <?php foreach ($listingTypes as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $business['listing_type'] === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Sector / Industry</label>
                <select name="sector_id" class="input">
                    <option value="">Select sector...</option>
                    <?php foreach ($sectors as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= (int)$business['sector_id'] === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Province</label>
                <select name="province" class="input">
                    <option value="">Select province...</option>
                    <?php foreach ($provinces as $p): ?>
                    <option value="<?= $p ?>" <?= $business['province'] === $p ? 'selected' : '' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>District</label>
                <input type="text" name="district" class="input" value="<?= e($business['district']) ?>">
            </div>
            <div class="input-group">
                <label>Established Year</label>
                <input type="number" name="established_year" class="input" min="1900" max="<?= date('Y') ?>" value="<?= e($business['established_year']) ?>">
            </div>
            <div class="input-group">
                <label>Employee Count</label>
                <input type="number" name="employee_count" class="input" min="0" value="<?= e($business['employee_count']) ?>">
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
        <h4>Financial Details</h4>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="input-group">
                <label>Annual Revenue (NPR)</label>
                <input type="number" name="annual_revenue" class="input" min="0" step="0.01" value="<?= e($business['annual_revenue']) ?>">
            </div>
            <div class="input-group">
                <label>EBITDA Margin (%)</label>
                <input type="number" name="ebitda_pct" class="input" min="0" max="100" step="0.01" value="<?= e($business['ebitda_pct']) ?>">
            </div>
            <div class="input-group">
                <label>Asking Price (NPR)</label>
                <input type="number" name="asking_price" class="input" min="0" step="0.01" value="<?= e($business['asking_price']) ?>">
            </div>
            <div class="input-group">
                <label>Stake Offered (%)</label>
                <input type="number" name="stake_offered_pct" class="input" min="0" max="100" step="0.01" value="<?= e($business['stake_offered_pct']) ?>">
            </div>
            <div class="input-group">
                <label>Loan Amount (NPR)</label>
                <input type="number" name="loan_amount" class="input" min="0" step="0.01" value="<?= e($business['loan_amount']) ?>">
            </div>
            <div class="input-group">
                <label>Loan Interest Rate (%)</label>
                <input type="number" name="loan_interest_pct" class="input" min="0" max="100" step="0.01" value="<?= e($business['loan_interest_pct']) ?>">
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
        <h4>Description & Details</h4>
        <div class="input-group">
            <label>Description</label>
            <textarea name="description" class="input" style="min-height:120px;"><?= e($business['description']) ?></textarea>
        </div>
        <div class="input-group">
            <label>Reason for Sale</label>
            <textarea name="reason_for_sale" class="input" style="min-height:80px;"><?= e($business['reason_for_sale']) ?></textarea>
        </div>
        <div class="input-group">
            <label>Assets Included</label>
            <textarea name="assets_included" class="input" style="min-height:80px;"><?= e($business['assets_included']) ?></textarea>
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
        <h4>Photos</h4>
        <?php if (!empty($photos)): ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:1rem;margin-bottom:1rem;">
            <?php foreach ($photos as $photo): ?>
            <div style="position:relative;">
                <img src="<?= APP_URL ?>/public/uploads/business-photos/<?= e($photo['file_path']) ?>" alt="Business photo" style="width:100%;height:100px;object-fit:cover;border-radius:0.5rem;">
                <label style="position:absolute;top:4px;right:4px;background:rgba(0,0,0,0.6);color:#fff;padding:2px 6px;border-radius:4px;font-size:0.75rem;cursor:pointer;">
                    <input type="checkbox" name="delete_photos[]" value="<?= $photo['id'] ?>"> Delete
                </label>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="input-group">
            <label>Add New Photos</label>
            <input type="file" name="photos[]" class="input" multiple accept="image/jpeg,image/png,image/webp">
            <p style="font-size:0.8rem;color:#888;margin-top:0.25rem;">Max 2MB per photo. JPEG, PNG, WebP accepted.</p>
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
        <label style="display:flex;align-items:center;gap:0.5rem;">
            <input type="checkbox" name="is_published" value="1" <?= $business['is_published'] ? 'checked' : '' ?>>
            Published
        </label>
    </div>

    <div style="display:flex;gap:1rem;">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="dashboard.php" class="btn btn-outline">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
</div></div>
