<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role([ROLE_BUSINESS_OWNER, 'owner', 'ceo', 'cfo']);

$user = current_user();
$userId = (int)$user['id'];

$sectors = db()->query('SELECT id, name FROM sectors WHERE is_active = 1 ORDER BY name')->fetchAll();
$countries = db()->query('SELECT id, name FROM countries WHERE is_active = 1 ORDER BY name')->fetchAll();
$states = db()->query('SELECT id, name, country_id FROM states WHERE is_active = 1 ORDER BY name')->fetchAll();
$cities = db()->query('SELECT id, name, state_id FROM cities WHERE is_active = 1 ORDER BY name')->fetchAll();

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
    $thumbnailUrl = null;
    $status = 'approved';
    $isPublished = 1;

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
            $thumbnailUrl = '/public/uploads/business-thumbnails/' . $uploaded;
        }
    } else {
        $thumbnailUrl = trim($_POST['thumbnail_url'] ?? '');
    }

    $slug = generate_slug($businessName);

    $db->beginTransaction();
    try {
        $stmt = $db->prepare('INSERT INTO businesses (user_id, business_name, slug, listing_type, sector_id, country_id, state_id, city_id, established_year, employee_count, legal_entity_type, monthly_revenue, annual_revenue, ebitda_pct, asking_price, funding_required, stake_offered_pct, valuation, description, overview, products_services, reason_for_sale, assets_included, facilities, capitalization, thumbnail_url, status, is_published, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
        $stmt->execute([$userId, $businessName, $slug, $listingType, $sectorId, $countryId, $stateId, $cityId, $establishedYear, $employeeCount, $legalEntityType, $monthlyRevenue, $annualRevenue, $ebitdaPct, $askingPrice, $fundingRequired, $stakeOfferedPct, $valuation, $description, $overview, $productsServices, $reasonForSale, $assetsIncluded, $facilities, $capitalization, $thumbnailUrl, $status, $isPublished]);
        $businessId = (int)$db->lastInsertId();

        // Handle media upload
        if (!empty($_FILES['media'])) {
            $files = $_FILES['media'];
            $sortOrder = 0;
            $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'video/mp4', 'application/pdf'];
            $destDir = upload_path('business-photos');
            foreach ($files['tmp_name'] as $i => $tmpName) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
                $file = ['name' => $files['name'][$i], 'tmp_name' => $tmpName, 'size' => $files['size'][$i], 'error' => $files['error'][$i]];
                $filename = handle_upload($file, $allowedMime, UPLOAD_MAX_BYTES_PHOTO, $destDir);
                if ($filename) {
                    $mimeType = mime_content_type($destDir . '/' . $filename);
                    $mediaType = str_starts_with($mimeType, 'video') ? 'video' : (str_starts_with($mimeType, 'application') ? 'document' : 'image');
                    $db->prepare('INSERT INTO business_media (business_id, file_url, media_type, sort_order, created_at) VALUES (?, ?, ?, ?, NOW())')->execute([$businessId, '/public/uploads/business-photos/' . $filename, $mediaType, $sortOrder]);
                    $sortOrder++;
                }
            }
        }

        // Handle assets
        if (!empty($_POST['asset_name'])) {
            $aStmt = $db->prepare('INSERT INTO business_assets (business_id, asset_name, asset_type, estimated_value, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
            foreach ($_POST['asset_name'] as $i => $name) {
                $name = trim($name);
                if ($name === '') continue;
                $aStmt->execute([$businessId, $name, $_POST['asset_type'][$i] ?? null, !empty($_POST['asset_value'][$i]) ? (float)$_POST['asset_value'][$i] : null, trim($_POST['asset_desc'][$i] ?? '')]);
            }
        }

        // Handle financials
        if (!empty($_POST['financial_year'])) {
            $fStmt = $db->prepare('INSERT INTO business_financials (business_id, fiscal_year, revenue, expenses, profit, ebitda, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())');
            foreach ($_POST['financial_year'] as $i => $year) {
                $year = (int)$year;
                if ($year < 2000) continue;
                $fStmt->execute([$businessId, $year, !empty($_POST['financial_revenue'][$i]) ? (float)$_POST['financial_revenue'][$i] : null, !empty($_POST['financial_expenses'][$i]) ? (float)$_POST['financial_expenses'][$i] : null, !empty($_POST['financial_profit'][$i]) ? (float)$_POST['financial_profit'][$i] : null, !empty($_POST['financial_ebitda'][$i]) ? (float)$_POST['financial_ebitda'][$i] : null]);
            }
        }

        $db->commit();
        flash_set('success', 'Business listing created successfully.');
        redirect('/business/' . $slug);
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
                    <label>Legal Entity Type</label>
                    <select name="legal_entity_type" class="input">
                        <option value="">Select...</option>
                        <?php foreach ($legalEntityTypes as $let): ?>
                        <option value="<?= e($let) ?>" <?= old('legal_entity_type') === $let ? 'selected' : '' ?>><?= e($let) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>Country</label>
                    <select name="country_id" class="input" onchange="updateStates(this.value)">
                        <option value="">Select country...</option>
                        <?php foreach ($countries as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= old('country_id') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>State / Province</label>
                    <select name="state_id" class="input" onchange="updateCities(this.value)">
                        <option value="">Select state...</option>
                        <?php foreach ($states as $s): ?>
                        <option value="<?= $s['id'] ?>" data-country="<?= $s['country_id'] ?>" <?= old('state_id') == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>City / District</label>
                    <select name="city_id" class="input">
                        <option value="">Select city...</option>
                        <?php foreach ($cities as $c): ?>
                        <option value="<?= $c['id'] ?>" data-state="<?= $c['state_id'] ?>" <?= old('city_id') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
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

    <!-- Step 2: Financial -->
    <div class="step-panel" data-step="2" style="display:none">
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Financial Details</h4>
            <div class="r-2">
                <div class="input-group">
                    <label>Monthly Revenue (NPR)</label>
                    <input type="number" name="monthly_revenue" class="input" min="0" step="0.01" value="<?= e(old('monthly_revenue')) ?>">
                </div>
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
                    <label>Funding Required (NPR)</label>
                    <input type="number" name="funding_required" class="input" min="0" step="0.01" value="<?= e(old('funding_required')) ?>">
                </div>
                <div class="input-group">
                    <label>Valuation (NPR)</label>
                    <input type="number" name="valuation" class="input" min="0" step="0.01" value="<?= e(old('valuation')) ?>">
                </div>
                <div class="input-group">
                    <label>Stake Offered (%)</label>
                    <input type="number" name="stake_offered_pct" class="input" min="0" max="100" step="0.01" value="<?= e(old('stake_offered_pct')) ?>">
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Historical Financials <span style="font-weight:400;font-size:0.8rem;color:var(--color-text-muted);">(optional, add past fiscal years)</span></h4>
            <div id="financials-container">
                <div class="financial-row r-2" style="margin-bottom:0.75rem;">
                    <div class="input-group">
                        <label>Fiscal Year</label>
                        <input type="number" name="financial_year[]" class="input" placeholder="e.g. 2024" min="2000" max="<?= date('Y') ?>">
                    </div>
                    <div class="input-group">
                        <label>Revenue (NPR)</label>
                        <input type="number" name="financial_revenue[]" class="input" step="0.01">
                    </div>
                    <div class="input-group">
                        <label>Expenses (NPR)</label>
                        <input type="number" name="financial_expenses[]" class="input" step="0.01">
                    </div>
                    <div class="input-group">
                        <label>Profit (NPR)</label>
                        <input type="number" name="financial_profit[]" class="input" step="0.01">
                    </div>
                    <div class="input-group">
                        <label>EBITDA (NPR)</label>
                        <input type="number" name="financial_ebitda[]" class="input" step="0.01">
                    </div>
                </div>
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
                <textarea name="description" class="input" style="min-height:80px;" placeholder="Brief summary for listing cards"><?= e(old('description')) ?></textarea>
            </div>
            <div class="input-group">
                <label>Business Overview</label>
                <textarea name="overview" class="input" style="min-height:120px;" placeholder="Detailed overview of the business, its history, market position, and growth potential"><?= e(old('overview')) ?></textarea>
            </div>
            <div class="input-group">
                <label>Products &amp; Services</label>
                <textarea name="products_services" class="input" style="min-height:100px;" placeholder="Describe the products or services offered"><?= e(old('products_services')) ?></textarea>
            </div>
            <div class="input-group">
                <label>Reason for Sale / Fundraising</label>
                <textarea name="reason_for_sale" class="input" style="min-height:80px;"><?= e(old('reason_for_sale')) ?></textarea>
            </div>
            <div class="input-group">
                <label>Facilities</label>
                <textarea name="facilities" class="input" style="min-height:80px;" placeholder="Office space, manufacturing facilities, warehouses, etc."><?= e(old('facilities')) ?></textarea>
            </div>
            <div class="input-group">
                <label>Capitalization</label>
                <textarea name="capitalization" class="input" style="min-height:80px;" placeholder="Capital structure, debt, equity breakdown"><?= e(old('capitalization')) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Step 4: Assets -->
    <div class="step-panel" data-step="4" style="display:none">
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Assets Included</h4>
            <div id="assets-container">
                <div class="asset-row" style="display:grid;grid-template-columns:2fr 1fr 1fr 2fr;gap:0.75rem;margin-bottom:0.75rem;align-items:end;">
                    <div class="input-group">
                        <label>Asset Name</label>
                        <input type="text" name="asset_name[]" class="input" placeholder="e.g. Delivery vehicles">
                    </div>
                    <div class="input-group">
                        <label>Type</label>
                        <select name="asset_type[]" class="input">
                            <option value="">Select...</option>
                            <option value="land">Land</option>
                            <option value="building">Building</option>
                            <option value="equipment">Equipment</option>
                            <option value="inventory">Inventory</option>
                            <option value="vehicle">Vehicle</option>
                            <option value="intellectual_property">IP</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Value (NPR)</label>
                        <input type="number" name="asset_value[]" class="input" step="0.01">
                    </div>
                    <div class="input-group">
                        <label>Description</label>
                        <input type="text" name="asset_desc[]" class="input" placeholder="Optional details">
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline" onclick="addAssetRow()">+ Add Another Asset</button>
        </div>
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Additional Text Assets</h4>
            <div class="input-group">
                <textarea name="assets_included" class="input" style="min-height:80px;" placeholder="Any other assets not listed above"><?= e(old('assets_included')) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Step 5: Media -->
    <div class="step-panel" data-step="5" style="display:none">
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Thumbnail Image</h4>
            <div class="input-group">
                <label>Upload Business Logo / Thumbnail</label>
                <input type="file" name="thumbnail" class="input" accept="image/jpeg,image/png,image/webp" onchange="previewThumbnail(this)">
                <div id="thumbnail-preview" style="margin-top:0.5rem;display:none;">
                    <img src="" alt="Preview" style="width:200px;height:150px;object-fit:cover;border-radius:8px;border:1px solid var(--dash-border);">
                </div>
                <p style="font-size:0.8rem;color:var(--color-text-muted);margin-top:0.25rem;">Max 2MB. JPEG, PNG, WebP. Recommended: 400x300px.</p>
            </div>
            <div class="input-group" style="margin-top:0.75rem;">
                <label>Or paste an image URL <span style="font-weight:400;font-size:0.8rem;color:var(--color-text-muted);">(external link, optional)</span></label>
                <input type="url" name="thumbnail_url" class="input" value="<?= e(old('thumbnail_url')) ?>" placeholder="https://images.unsplash.com/photo-...">
            </div>
        </div>
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Photos &amp; Media</h4>
            <div class="input-group">
                <label>Upload Images, Videos, or Documents</label>
                <input type="file" name="media[]" class="input" multiple accept="image/jpeg,image/png,image/webp,video/mp4,application/pdf">
                <p style="font-size:0.8rem;color:var(--color-text-muted);margin-top:0.25rem;">Max 2MB per file. JPEG, PNG, WebP, MP4, PDF accepted.</p>
            </div>
        </div>
    </div>

    <div class="step-nav">
        <button type="button" class="btn btn-outline btn-step-back" style="display:none">Back</button>
        <div class="step-nav-right">
            <button type="button" class="btn btn-primary btn-step-next">Next</button>
            <button type="submit" class="btn btn-primary btn-step-submit" style="display:none">Create Listing</button>
            <a href="/dashboard" class="btn btn-outline" style="margin-left:8px;">Cancel</a>
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
// Init location filters on page load
document.addEventListener('DOMContentLoaded', function() {
    var cid = document.querySelector('select[name="country_id"]').value;
    if (cid) updateStates(cid);
    var sid = document.querySelector('select[name="state_id"]').value;
    if (sid) updateCities(sid);
});
</script>

<script src="/assets/form-steps.js"></script>
<script>initFormSteps();</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
</div></div>
