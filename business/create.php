<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role(ROLE_BUSINESS_OWNER);

$user = current_user();
$userId = (int)$user['id'];

$sectors = db()->query('SELECT id, name FROM sectors WHERE is_active = 1 ORDER BY name')->fetchAll();

$provinces = ['Bagmati', 'Gandaki', 'Karnali', 'Lumbini', 'Sudurpashchim', 'Koshi', 'Madhesh'];

$listingTypes = [
    'sale' => 'Business for Sale',
    'partial_stake' => 'Partial Stake Sale',
    'investment' => 'Investment Opportunity',
    'loan' => 'Business Loan',
    'asset_sale' => 'Asset Sale',
];

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
        $stmt = $db->prepare('INSERT INTO businesses (user_id, business_name, listing_type, sector_id, province, district, established_year, employee_count, annual_revenue, ebitda_pct, asking_price, stake_offered_pct, loan_amount, loan_interest_pct, description, reason_for_sale, assets_included, is_published, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
        $stmt->execute([$userId, $businessName, $listingType, $sectorId, $province, $district, $establishedYear, $employeeCount, $annualRevenue, $ebitdaPct, $askingPrice, $stakeOfferedPct, $loanAmount, $loanInterestPct, $description, $reasonForSale, $assetsIncluded, $isPublished]);
        $businessId = (int)$db->lastInsertId();

        if (!empty($_FILES['photos'])) {
            $files = $_FILES['photos'];
            $sortOrder = 0;
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
        flash_set('success', 'Business listing created successfully.');
        redirect('/business/edit.php?id=' . $businessId);
    } catch (\Throwable $e) {
        $db->rollBack();
        flash_set('error', 'Failed to create listing. Please try again.');
        if (DEBUG_MODE) error_log('business create error: ' . $e->getMessage());
        redirect_back();
    }
}

$pageTitle = 'Create Business Listing';
require __DIR__ . '/../includes/layout-dashboard.php';
?>
<h2 style="margin-bottom:0.25rem;">List Your Business</h2>
<p style="color:var(--color-text-muted);">Connect with thousands of pre-verified investors and buyers.</p>

<form method="POST" enctype="multipart/form-data" class="form-steps">
    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

    <div class="form-step-progress dash-version" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="3">
        <div class="step-indicator">
            <div class="step-segment active" data-step="1">
                <div class="step-number"><span class="step-check">&#10003;</span><span class="step-num">1</span></div>
                <span class="step-label">Basic Info</span>
            </div>
            <div class="step-line"><div class="step-line-fill" style="width:0%"></div></div>
            <div class="step-segment" data-step="2">
                <div class="step-number">2</div>
                <span class="step-label">Financial</span>
            </div>
            <div class="step-line"><div class="step-line-fill" style="width:0%"></div></div>
            <div class="step-segment" data-step="3">
                <div class="step-number">3</div>
                <span class="step-label">Photos &amp; Details</span>
            </div>
        </div>
    </div>

    <div class="step-panel" data-step="1">
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Basic Information</h4>
            <div class="r-2">
                <div class="input-group">
                    <label>Business Name <span class="required">*</span></label>
                    <input type="text" name="business_name" class="input" value="<?= e(old('business_name')) ?>" required>
                </div>
                <div class="input-group">
                    <label>Listing Type <span class="required">*</span></label>
                    <select name="listing_type" class="input" required>
                        <option value="">Select type...</option>
                        <?php foreach ($listingTypes as $val => $label): ?>
                        <option value="<?= $val ?>" <?= old('listing_type') === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>Sector / Industry</label>
                    <select name="sector_id" class="input">
                        <option value="">Select sector...</option>
                        <?php foreach ($sectors as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= old('sector_id') == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>Province</label>
                    <select name="province" class="input">
                        <option value="">Select province...</option>
                        <?php foreach ($provinces as $p): ?>
                        <option value="<?= $p ?>" <?= old('province') === $p ? 'selected' : '' ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>District</label>
                    <input type="text" name="district" class="input" value="<?= e(old('district')) ?>">
                </div>
                <div class="input-group">
                    <label>Established Year</label>
                    <input type="number" name="established_year" class="input" min="1900" max="<?= date('Y') ?>" value="<?= e(old('established_year')) ?>">
                </div>
                <div class="input-group">
                    <label>Employee Count</label>
                    <input type="number" name="employee_count" class="input" min="0" value="<?= e(old('employee_count')) ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="step-panel" data-step="2" style="display:none">
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Financial Details</h4>
            <div class="r-2">
                <div class="input-group">
                    <label>Annual Revenue (NPR)</label>
                    <input type="number" name="annual_revenue" class="input" min="0" step="0.01" value="<?= e(old('annual_revenue')) ?>">
                </div>
                <div class="input-group">
                    <label>EBITDA Margin (%)</label>
                    <input type="number" name="ebitda_pct" class="input" min="0" max="100" step="0.01" value="<?= e(old('ebitda_pct')) ?>">
                </div>
                <div class="input-group">
                    <label>Asking Price (NPR)</label>
                    <input type="number" name="asking_price" class="input" min="0" step="0.01" value="<?= e(old('asking_price')) ?>">
                </div>
                <div class="input-group">
                    <label>Stake Offered (%)</label>
                    <input type="number" name="stake_offered_pct" class="input" min="0" max="100" step="0.01" value="<?= e(old('stake_offered_pct')) ?>">
                </div>
                <div class="input-group">
                    <label>Loan Amount (NPR)</label>
                    <input type="number" name="loan_amount" class="input" min="0" step="0.01" value="<?= e(old('loan_amount')) ?>">
                </div>
                <div class="input-group">
                    <label>Loan Interest Rate (%)</label>
                    <input type="number" name="loan_interest_pct" class="input" min="0" max="100" step="0.01" value="<?= e(old('loan_interest_pct')) ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="step-panel" data-step="3" style="display:none">
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Description &amp; Details</h4>
            <div class="input-group">
                <label>Description</label>
                <textarea name="description" class="input" style="min-height:120px;"><?= e(old('description')) ?></textarea>
            </div>
            <div class="input-group">
                <label>Reason for Sale</label>
                <textarea name="reason_for_sale" class="input" style="min-height:80px;"><?= e(old('reason_for_sale')) ?></textarea>
            </div>
            <div class="input-group">
                <label>Assets Included</label>
                <textarea name="assets_included" class="input" style="min-height:80px;"><?= e(old('assets_included')) ?></textarea>
            </div>
        </div>

        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Photos</h4>
            <div class="input-group">
                <label>Upload Business Photos</label>
                <input type="file" name="photos[]" class="input" multiple accept="image/jpeg,image/png,image/webp">
                <p style="font-size:0.8rem;color:var(--color-text-muted);margin-top:0.25rem;">Max 2MB per photo. JPEG, PNG, WebP accepted.</p>
            </div>
        </div>

        <div class="card" style="margin-bottom:1.5rem;">
            <label style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" name="is_published" value="1" checked>
                Publish immediately
            </label>
        </div>
    </div>

    <div class="step-nav">
        <button type="button" class="btn btn-outline btn-step-back" style="display:none">Back</button>
        <div class="step-nav-right">
            <button type="button" class="btn btn-primary btn-step-next">Next</button>
            <button type="submit" class="btn btn-primary btn-step-submit" style="display:none">Create Listing</button>
            <a href="dashboard.php" class="btn btn-outline" style="margin-left:8px;">Cancel</a>
        </div>
    </div>
</form>

<script src="/assets/form-steps.js"></script>
<script>initFormSteps();</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
</div></div>
