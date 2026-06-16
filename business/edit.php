<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role([ROLE_BUSINESS_OWNER, 'owner', 'ceo', 'cfo']);

$user = current_user();
$userId = (int)$user['id'];
$businessId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($businessId < 1) {
    $stmt = db()->prepare('SELECT id FROM businesses WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1');
    $stmt->execute([$userId]);
    $lastId = (int)$stmt->fetchColumn();
    if ($lastId > 0) { redirect('/business/edit.php?id=' . $lastId); }
    flash_set('error', 'No business listing found. Create one first.');
    redirect('/business/create.php');
}

$stmt = db()->prepare('SELECT * FROM businesses WHERE id = ? AND user_id = ?');
$stmt->execute([$businessId, $userId]);
$business = $stmt->fetch();

if (!$business) {
    flash_set('error', 'Business not found.');
    redirect('/business/dashboard.php');
}

$sectors = db()->query('SELECT id, name FROM sectors WHERE is_active = 1 ORDER BY name')->fetchAll();
$countries = db()->query('SELECT id, name FROM countries WHERE is_active = 1 ORDER BY name')->fetchAll();
$states = db()->query('SELECT id, name, country_id FROM states WHERE is_active = 1 ORDER BY name')->fetchAll();
$cities = db()->query('SELECT id, name, state_id FROM cities WHERE is_active = 1 ORDER BY name')->fetchAll();

$mediaItems = db()->prepare('SELECT * FROM business_media WHERE business_id = ? ORDER BY sort_order');
$mediaItems->execute([$businessId]);
$mediaItems = $mediaItems->fetchAll();

$assets = db()->prepare('SELECT * FROM business_assets WHERE business_id = ? ORDER BY id');
$assets->execute([$businessId]);
$assets = $assets->fetchAll();

$financials = db()->prepare('SELECT * FROM business_financials WHERE business_id = ? ORDER BY fiscal_year DESC');
$financials->execute([$businessId]);
$financials = $financials->fetchAll();

$listingTypes = [
    'business_sale' => 'Business for Sale',
    'investment' => 'Investment Opportunity',
    'partial_stake' => 'Partial Stake Sale',
    'loan' => 'Business Loan',
    'asset_sale' => 'Asset Sale',
    'franchise' => 'Franchise Opportunity',
    'partner' => 'Looking for Partner',
];

$legalEntityTypes = ['Sole Proprietorship', 'Partnership', 'Private Limited', 'Public Limited', 'NGO/INGO', 'Co-operative', 'Other'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $db = db();

    $businessName = trim($_POST['business_name'] ?? '');
    $listingType = $_POST['listing_type'] ?? '';
    $sectorId = !empty($_POST['sector_id']) ? (int)$_POST['sector_id'] : null;
    $countryId = !empty($_POST['country_id']) ? (int)$_POST['country_id'] : null;
    $stateId = !empty($_POST['state_id']) ? (int)$_POST['state_id'] : null;
    $cityId = !empty($_POST['city_id']) ? (int)$_POST['city_id'] : null;
    $establishedYear = !empty($_POST['established_year']) ? (int)$_POST['established_year'] : null;
    $employeeCount = !empty($_POST['employee_count']) ? (int)$_POST['employee_count'] : null;
    $legalEntityType = trim($_POST['legal_entity_type'] ?? '');
    $monthlyRevenue = !empty($_POST['monthly_revenue']) ? (float)$_POST['monthly_revenue'] : null;
    $annualRevenue = !empty($_POST['annual_revenue']) ? (float)$_POST['annual_revenue'] : null;
    $ebitdaPct = !empty($_POST['ebitda_pct']) ? (float)$_POST['ebitda_pct'] : null;
    $askingPrice = !empty($_POST['asking_price']) ? (float)$_POST['asking_price'] : null;
    $fundingRequired = !empty($_POST['funding_required']) ? (float)$_POST['funding_required'] : null;
    $stakeOfferedPct = !empty($_POST['stake_offered_pct']) ? (float)$_POST['stake_offered_pct'] : null;
    $valuation = !empty($_POST['valuation']) ? (float)$_POST['valuation'] : null;
    $description = trim($_POST['description'] ?? '');
    $overview = trim($_POST['overview'] ?? '');
    $productsServices = trim($_POST['products_services'] ?? '');
    $reasonForSale = trim($_POST['reason_for_sale'] ?? '');
    $assetsIncluded = trim($_POST['assets_included'] ?? '');
    $facilities = trim($_POST['facilities'] ?? '');
    $capitalization = trim($_POST['capitalization'] ?? '');
    $thumbnailUrl = $business['thumbnail_url'];
    $status = $_POST['status'] ?? 'draft';
    if (!in_array($status, ['draft', 'pending', 'approved', 'rejected', 'sold'], true)) {
        $status = 'draft';
    }

    if ($businessName === '' || $listingType === '') {
        flash_set('error', 'Business name and listing type are required.');
        redirect_back();
    }

    // Handle thumbnail upload
    if (!empty($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
        $destDir = upload_path('business-thumbnails');
        $uploaded = handle_upload($_FILES['thumbnail'], $allowedMime, UPLOAD_MAX_BYTES_PHOTO, $destDir);
        if ($uploaded) {
            $thumbnailUrl = 'business-thumbnails/' . $uploaded;
        }
    } elseif (isset($_POST['thumbnail_url']) && trim($_POST['thumbnail_url']) !== '') {
        $thumbnailUrl = trim($_POST['thumbnail_url']);
    }

    $isPublished = ($status === 'approved') ? 1 : 0;

    $slug = $business['slug'] ?: unique_slug(generate_slug($businessName), 'businesses');

    $db->beginTransaction();
    try {
        $updateStmt = $db->prepare('UPDATE businesses SET business_name = ?, slug = ?, listing_type = ?, sector_id = ?, country_id = ?, state_id = ?, city_id = ?, established_year = ?, employee_count = ?, legal_entity_type = ?, monthly_revenue = ?, annual_revenue = ?, ebitda_pct = ?, asking_price = ?, funding_required = ?, stake_offered_pct = ?, valuation = ?, description = ?, overview = ?, products_services = ?, reason_for_sale = ?, assets_included = ?, facilities = ?, capitalization = ?, thumbnail_url = ?, status = ?, is_published = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
        $updateStmt->execute([$businessName, $slug, $listingType, $sectorId, $countryId, $stateId, $cityId, $establishedYear, $employeeCount, $legalEntityType, $monthlyRevenue, $annualRevenue, $ebitdaPct, $askingPrice, $fundingRequired, $stakeOfferedPct, $valuation, $description, $overview, $productsServices, $reasonForSale, $assetsIncluded, $facilities, $capitalization, $thumbnailUrl, $status, $isPublished, $businessId, $userId]);

        // Delete media
        if (!empty($_POST['delete_media'])) {
            $deleteIds = array_map('intval', $_POST['delete_media']);
            $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
            $delStmt = $db->prepare("SELECT file_url FROM business_media WHERE id IN ($placeholders) AND business_id = ?");
            $delStmt->execute(array_merge($deleteIds, [$businessId]));
            $toDelete = $delStmt->fetchAll();
            $db->prepare("DELETE FROM business_media WHERE id IN ($placeholders) AND business_id = ?")->execute(array_merge($deleteIds, [$businessId]));
            $destDir = upload_path('business-photos');
            foreach ($toDelete as $m) {
                $path = $destDir . '/' . basename($m['file_url']);
                if (file_exists($path)) unlink($path);
            }
        }

        // Upload new media
        if (!empty($_FILES['media'])) {
            $files = $_FILES['media'];
            $maxSortStmt = $db->prepare('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM business_media WHERE business_id = ?');
            $maxSortStmt->execute([$businessId]);
            $sortOrder = (int)$maxSortStmt->fetchColumn();
            $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'video/mp4', 'application/pdf'];
            $destDir = upload_path('business-photos');
            foreach ($files['tmp_name'] as $i => $tmpName) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
                $file = ['name' => $files['name'][$i], 'tmp_name' => $tmpName, 'size' => $files['size'][$i], 'error' => $files['error'][$i]];
                $filename = handle_upload($file, $allowedMime, UPLOAD_MAX_BYTES_PHOTO, $destDir);
                if ($filename) {
                    $mimeType = mime_content_type($destDir . '/' . $filename);
                    $mediaType = str_starts_with($mimeType, 'video') ? 'video' : (str_starts_with($mimeType, 'application') ? 'document' : 'image');
                    $db->prepare('INSERT INTO business_media (business_id, file_url, media_type, sort_order, created_at) VALUES (?, ?, ?, ?, NOW())')->execute([$businessId, 'business-photos/' . $filename, $mediaType, $sortOrder]);
                    $sortOrder++;
                }
            }
        }

        // Handle assets
        $db->prepare('DELETE FROM business_assets WHERE business_id = ?')->execute([$businessId]);
        if (!empty($_POST['asset_name'])) {
            $aStmt = $db->prepare('INSERT INTO business_assets (business_id, asset_name, asset_type, estimated_value, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
            foreach ($_POST['asset_name'] as $i => $name) {
                $name = trim($name);
                if ($name === '') continue;
                $aStmt->execute([$businessId, $name, $_POST['asset_type'][$i] ?? null, !empty($_POST['asset_value'][$i]) ? (float)$_POST['asset_value'][$i] : null, trim($_POST['asset_desc'][$i] ?? '')]);
            }
        }

        // Handle financials
        $db->prepare('DELETE FROM business_financials WHERE business_id = ?')->execute([$businessId]);
        if (!empty($_POST['financial_year'])) {
            $fStmt = $db->prepare('INSERT INTO business_financials (business_id, fiscal_year, revenue, expenses, profit, ebitda, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())');
            foreach ($_POST['financial_year'] as $i => $year) {
                $year = (int)$year;
                if ($year < 2000) continue;
                $fStmt->execute([$businessId, $year, !empty($_POST['financial_revenue'][$i]) ? (float)$_POST['financial_revenue'][$i] : null, !empty($_POST['financial_expenses'][$i]) ? (float)$_POST['financial_expenses'][$i] : null, !empty($_POST['financial_profit'][$i]) ? (float)$_POST['financial_profit'][$i] : null, !empty($_POST['financial_ebitda'][$i]) ? (float)$_POST['financial_ebitda'][$i] : null]);
            }
        }

        $db->commit();
        flash_set('success', 'Business listing updated successfully.');
        redirect('/business/' . $slug);
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
<p style="color:var(--color-text-muted);">Update your business information across all sections.</p>

<form method="POST" enctype="multipart/form-data" class="form-steps" novalidate>
    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="id" value="<?= $businessId ?>">

    <div class="form-step-progress dash-version" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="5">
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
                <span class="step-label">Details</span>
            </div>
            <div class="step-line"><div class="step-line-fill" style="width:0%"></div></div>
            <div class="step-segment" data-step="4">
                <div class="step-number">4</div>
                <span class="step-label">Assets</span>
            </div>
            <div class="step-line"><div class="step-line-fill" style="width:0%"></div></div>
            <div class="step-segment" data-step="5">
                <div class="step-number">5</div>
                <span class="step-label">Media</span>
            </div>
        </div>
    </div>

    <!-- Step 1: Basic Info -->
    <div class="step-panel" data-step="1">
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Basic Information</h4>
            <div class="r-2">
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
                    <label>Legal Entity Type</label>
                    <select name="legal_entity_type" class="input">
                        <option value="">Select...</option>
                        <?php foreach ($legalEntityTypes as $let): ?>
                        <option value="<?= e($let) ?>" <?= $business['legal_entity_type'] === $let ? 'selected' : '' ?>><?= e($let) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>Country</label>
                    <select name="country_id" class="input" onchange="updateStates(this.value)">
                        <option value="">Select country...</option>
                        <?php foreach ($countries as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (int)$business['country_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>State / Province</label>
                    <select name="state_id" class="input" onchange="updateCities(this.value)">
                        <option value="">Select state...</option>
                        <?php foreach ($states as $s): ?>
                        <option value="<?= $s['id'] ?>" data-country="<?= $s['country_id'] ?>" <?= (int)$business['state_id'] === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>City / District</label>
                    <select name="city_id" class="input">
                        <option value="">Select city...</option>
                        <?php foreach ($cities as $c): ?>
                        <option value="<?= $c['id'] ?>" data-state="<?= $c['state_id'] ?>" <?= (int)$business['city_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
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
    </div>

    <!-- Step 2: Financial -->
    <div class="step-panel" data-step="2" style="display:none">
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Financial Details</h4>
            <div class="r-2">
                <div class="input-group">
                    <label>Monthly Revenue (NPR)</label>
                    <input type="number" name="monthly_revenue" class="input" min="0" step="0.01" value="<?= e($business['monthly_revenue']) ?>">
                </div>
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
                    <label>Funding Required (NPR)</label>
                    <input type="number" name="funding_required" class="input" min="0" step="0.01" value="<?= e($business['funding_required']) ?>">
                </div>
                <div class="input-group">
                    <label>Valuation (NPR)</label>
                    <input type="number" name="valuation" class="input" min="0" step="0.01" value="<?= e($business['valuation']) ?>">
                </div>
                <div class="input-group">
                    <label>Stake Offered (%)</label>
                    <input type="number" name="stake_offered_pct" class="input" min="0" max="100" step="0.01" value="<?= e($business['stake_offered_pct']) ?>">
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Historical Financials</h4>
            <div id="financials-container">
                <?php if (!empty($financials)): foreach ($financials as $f): ?>
                <div class="financial-row r-2" style="margin-bottom:0.75rem;">
                    <div class="input-group"><label>Fiscal Year</label><input type="number" name="financial_year[]" class="input" value="<?= $f['fiscal_year'] ?>" min="2000"></div>
                    <div class="input-group"><label>Revenue</label><input type="number" name="financial_revenue[]" class="input" step="0.01" value="<?= $f['revenue'] ?>"></div>
                    <div class="input-group"><label>Expenses</label><input type="number" name="financial_expenses[]" class="input" step="0.01" value="<?= $f['expenses'] ?>"></div>
                    <div class="input-group"><label>Profit</label><input type="number" name="financial_profit[]" class="input" step="0.01" value="<?= $f['profit'] ?>"></div>
                    <div class="input-group"><label>EBITDA</label><input type="number" name="financial_ebitda[]" class="input" step="0.01" value="<?= $f['ebitda'] ?>"></div>
                </div>
                <?php endforeach; else: ?>
                <div class="financial-row r-2" style="margin-bottom:0.75rem;">
                    <div class="input-group"><label>Fiscal Year</label><input type="number" name="financial_year[]" class="input" placeholder="e.g. 2024" min="2000"></div>
                    <div class="input-group"><label>Revenue</label><input type="number" name="financial_revenue[]" class="input" step="0.01"></div>
                    <div class="input-group"><label>Expenses</label><input type="number" name="financial_expenses[]" class="input" step="0.01"></div>
                    <div class="input-group"><label>Profit</label><input type="number" name="financial_profit[]" class="input" step="0.01"></div>
                    <div class="input-group"><label>EBITDA</label><input type="number" name="financial_ebitda[]" class="input" step="0.01"></div>
                </div>
                <?php endif; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline" onclick="addFinancialRow()">+ Add Another Year</button>
        </div>
    </div>

    <!-- Step 3: Details -->
    <div class="step-panel" data-step="3" style="display:none">
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Description &amp; Overview</h4>
            <div class="input-group">
                <label>Short Description</label>
                <textarea name="description" class="input" style="min-height:80px;"><?= e($business['description']) ?></textarea>
            </div>
            <div class="input-group">
                <label>Business Overview</label>
                <textarea name="overview" class="input" style="min-height:120px;"><?= e($business['overview']) ?></textarea>
            </div>
            <div class="input-group">
                <label>Products &amp; Services</label>
                <textarea name="products_services" class="input" style="min-height:100px;"><?= e($business['products_services']) ?></textarea>
            </div>
            <div class="input-group">
                <label>Reason for Sale</label>
                <textarea name="reason_for_sale" class="input" style="min-height:80px;"><?= e($business['reason_for_sale']) ?></textarea>
            </div>
            <div class="input-group">
                <label>Facilities</label>
                <textarea name="facilities" class="input" style="min-height:80px;"><?= e($business['facilities']) ?></textarea>
            </div>
            <div class="input-group">
                <label>Capitalization</label>
                <textarea name="capitalization" class="input" style="min-height:80px;"><?= e($business['capitalization']) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Step 4: Assets -->
    <div class="step-panel" data-step="4" style="display:none">
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Assets</h4>
            <div id="assets-container">
                <?php if (!empty($assets)): foreach ($assets as $a): ?>
                <div class="asset-row" style="display:grid;grid-template-columns:2fr 1fr 1fr 2fr;gap:0.75rem;margin-bottom:0.75rem;align-items:end;">
                    <div class="input-group"><label>Asset Name</label><input type="text" name="asset_name[]" class="input" value="<?= e($a['asset_name']) ?>"></div>
                    <div class="input-group"><label>Type</label><select name="asset_type[]" class="input"><?php foreach (['land','building','equipment','inventory','vehicle','intellectual_property','other'] as $at): ?><option value="<?= $at ?>" <?= $a['asset_type'] === $at ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$at)) ?></option><?php endforeach; ?></select></div>
                    <div class="input-group"><label>Value</label><input type="number" name="asset_value[]" class="input" step="0.01" value="<?= $a['estimated_value'] ?>"></div>
                    <div class="input-group"><label>Description</label><input type="text" name="asset_desc[]" class="input" value="<?= e($a['description']) ?>"></div>
                </div>
                <?php endforeach; else: ?>
                <div class="asset-row" style="display:grid;grid-template-columns:2fr 1fr 1fr 2fr;gap:0.75rem;margin-bottom:0.75rem;align-items:end;">
                    <div class="input-group"><label>Asset Name</label><input type="text" name="asset_name[]" class="input" placeholder="e.g. Building"></div>
                    <div class="input-group"><label>Type</label><select name="asset_type[]" class="input"><option value="">Select...</option><option value="land">Land</option><option value="building">Building</option><option value="equipment">Equipment</option><option value="inventory">Inventory</option><option value="vehicle">Vehicle</option><option value="intellectual_property">IP</option><option value="other">Other</option></select></div>
                    <div class="input-group"><label>Value</label><input type="number" name="asset_value[]" class="input" step="0.01"></div>
                    <div class="input-group"><label>Description</label><input type="text" name="asset_desc[]" class="input"></div>
                </div>
                <?php endif; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline" onclick="addAssetRow()">+ Add Another Asset</button>
        </div>
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Additional Text Assets</h4>
            <div class="input-group">
                <textarea name="assets_included" class="input" style="min-height:80px;"><?= e($business['assets_included']) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Step 5: Media & Publish -->
    <div class="step-panel" data-step="5" style="display:none">
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Thumbnail Image</h4>
            <div class="input-group">
                <label>Upload Business Logo / Thumbnail</label>
                <input type="file" name="thumbnail" class="input" accept="image/jpeg,image/png,image/webp" onchange="previewThumbnail(this)">
                <div id="thumbnail-preview" style="margin-top:0.5rem;display:none;">
                    <img src="" alt="Preview" style="width:200px;height:150px;object-fit:cover;border-radius:8px;border:1px solid var(--dash-border);">
                </div>
                <p style="font-size:0.8rem;color:var(--color-text-muted);margin-top:0.25rem;">Max 2MB. JPEG, PNG, WebP.</p>
            </div>
            <?php if (!empty($business['thumbnail_url'])): ?>
            <div class="input-group" style="margin-top:0.75rem;">
                <label>Current Thumbnail</label>
                <div style="margin-top:0.25rem;"><img src="<?= upload_url($business['thumbnail_url']) ?>" alt="" style="width:200px;height:150px;object-fit:cover;border-radius:8px;border:1px solid var(--dash-border);"></div>
            </div>
            <?php endif; ?>
            <div class="input-group" style="margin-top:0.75rem;">
                <label>Or paste an image URL <span style="font-weight:400;font-size:0.8rem;color:var(--color-text-muted);">(external link, optional)</span></label>
                <input type="url" name="thumbnail_url" class="input" value="<?= e($business['thumbnail_url'] ?? '') ?>" placeholder="https://images.unsplash.com/photo-...">
            </div>
        </div>
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Media</h4>
            <?php if (!empty($mediaItems)): ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:1rem;margin-bottom:1rem;">
                <?php foreach ($mediaItems as $m): ?>
                <div style="position:relative;">
                    <?php if ($m['media_type'] === 'image'): ?>
                    <img src="<?= upload_url($m['file_url']) ?>" alt="" style="width:100%;height:100px;object-fit:cover;border-radius:0.5rem;">
                    <?php elseif ($m['media_type'] === 'video'): ?>
                    <video style="width:100%;height:100px;object-fit:cover;border-radius:0.5rem;" src="<?= upload_url($m['file_url']) ?>"></video>
                    <?php else: ?>
                    <div style="width:100%;height:100px;display:flex;align-items:center;justify-content:center;background:var(--color-bg-soft);border-radius:0.5rem;font-size:0.75rem;">PDF</div>
                    <?php endif; ?>
                    <label style="position:absolute;top:4px;right:4px;background:rgba(0,0,0,0.6);color:#fff;padding:2px 6px;border-radius:4px;font-size:0.75rem;cursor:pointer;">
                        <input type="checkbox" name="delete_media[]" value="<?= $m['id'] ?>"> Delete
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="input-group">
                <label>Add New Media</label>
                <input type="file" name="media[]" class="input" multiple accept="image/jpeg,image/png,image/webp,video/mp4,application/pdf">
            </div>
        </div>
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Publish Settings</h4>
            <div class="input-group">
                <label>Status</label>
                <select name="status" class="input">
                    <option value="draft" <?= $business['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="pending" <?= $business['status'] === 'pending' ? 'selected' : '' ?>>Pending Approval</option>
                    <option value="approved" <?= $business['status'] === 'approved' ? 'selected' : '' ?>>Approved (Published)</option>
                    <option value="sold" <?= $business['status'] === 'sold' ? 'selected' : '' ?>>Sold / Closed</option>
                </select>
            </div>
        </div>
    </div>

    <div class="step-nav">
        <button type="button" class="btn btn-outline btn-step-back" style="display:none">Back</button>
        <div class="step-nav-right">
            <button type="button" class="btn btn-primary btn-step-next">Next</button>
            <button type="submit" class="btn btn-primary btn-step-submit" style="display:none">Save Changes</button>
            <a href="<?= APP_URL ?>/dashboard" class="btn btn-outline" style="margin-left:8px;">Cancel</a>
        </div>
    </div>
</form>

<script>
function previewThumbnail(input) {
    var preview = document.getElementById('thumbnail-preview');
    var img = preview.querySelector('img');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) { img.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}
function updateStates(countryId) {
    document.querySelectorAll('select[name="state_id"] option').forEach(o => {
        o.style.display = o.value === '' || o.dataset.country == countryId ? '' : 'none';
    });
}
function updateCities(stateId) {
    document.querySelectorAll('select[name="city_id"] option').forEach(o => {
        o.style.display = o.value === '' || o.dataset.state == stateId ? '' : 'none';
    });
}
function addFinancialRow() {
    var c = document.getElementById('financials-container');
    var t = c.querySelector('.financial-row');
    var n = t.cloneNode(true);
    n.querySelectorAll('input').forEach(i => i.value = '');
    c.appendChild(n);
}
function addAssetRow() {
    var c = document.getElementById('assets-container');
    var t = c.querySelector('.asset-row');
    var n = t.cloneNode(true);
    n.querySelectorAll('input, select').forEach(i => i.value = '');
    c.appendChild(n);
}
document.addEventListener('DOMContentLoaded', function() {
    var cid = document.querySelector('select[name="country_id"]').value;
    if (cid) updateStates(cid);
    var sid = document.querySelector('select[name="state_id"]').value;
    if (sid) updateCities(sid);
});
</script>

<script src="<?= APP_URL ?>/assets/form-steps.js"></script>
<script>
initFormSteps();
document.querySelector('.form-steps')?.addEventListener('submit', function() {
  var btn = this.querySelector('.btn-step-submit');
  if (btn) btn.disabled = true;
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
</div></div>
