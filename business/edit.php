<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

$user = current_user();
$userId = (int)$user['id'];
$businessId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($businessId < 1) {
    $stmt = db()->prepare('SELECT id FROM businesses WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1');
    $stmt->execute([$userId]);
    $lastId = (int)$stmt->fetchColumn();
    if ($lastId > 0) { redirect('/business/edit?id=' . $lastId); }
    flash_set('error', 'No business listing found. Create one first.');
    redirect('/business/create');
}

$stmt = db()->prepare('SELECT * FROM businesses WHERE id = ? AND user_id = ?');
$stmt->execute([$businessId, $userId]);
$business = $stmt->fetch();

if (!$business) {
    flash_set('error', 'Business not found.');
    redirect('/business/dashboard');
}

$sectors = db()->query('SELECT id, name FROM sectors WHERE is_active = 1 ORDER BY name')->fetchAll();
$countries = db()->query('SELECT id, name FROM countries WHERE is_active = 1 ORDER BY name')->fetchAll();
$states = db()->query('SELECT id, name, country_id FROM states WHERE is_active = 1 ORDER BY name')->fetchAll();
$cities = db()->query('SELECT id, name, state_id FROM cities WHERE is_active = 1 ORDER BY name')->fetchAll();

$mediaItems = db()->prepare('SELECT * FROM business_media WHERE business_id = ? ORDER BY sort_order');
$mediaItems->execute([$businessId]);
$mediaItems = $mediaItems->fetchAll();

$documents = db()->prepare('SELECT * FROM business_documents WHERE business_id = ? ORDER BY sort_order');
$documents->execute([$businessId]);
$documents = $documents->fetchAll();

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
    $province = trim($_POST['province'] ?? '');
    $district = trim($_POST['district'] ?? '');
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
    $loanAmount = !empty($_POST['loan_amount']) ? (float)$_POST['loan_amount'] : null;
    $loanInterestPct = !empty($_POST['loan_interest_pct']) ? (float)$_POST['loan_interest_pct'] : null;
    $description = trim($_POST['description'] ?? '');
    $overview = trim($_POST['overview'] ?? '');
    $productsServices = trim($_POST['products_services'] ?? '');
    $reasonForSale = trim($_POST['reason_for_sale'] ?? '');
    $assetsIncluded = trim($_POST['assets_included'] ?? '');
    $facilities = trim($_POST['facilities'] ?? '');
    $capitalization = trim($_POST['capitalization'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    if (!in_array($status, ['draft', 'pending', 'approved', 'rejected', 'sold'], true)) {
        $status = 'draft';
    }

    if ($businessName === '' || $listingType === '') {
        flash_set('error', 'Business name and listing type are required.');
        redirect_back();
    }

    $isPublished = ($status === 'approved') ? 1 : 0;

    $slug = $business['slug'] ?: unique_slug(generate_slug($businessName), 'businesses');

    $db->beginTransaction();
    try {
        $updateStmt = $db->prepare('UPDATE businesses SET business_name = ?, slug = ?, listing_type = ?, sector_id = ?, country_id = ?, state_id = ?, city_id = ?, province = ?, district = ?, established_year = ?, employee_count = ?, legal_entity_type = ?, monthly_revenue = ?, annual_revenue = ?, ebitda_pct = ?, asking_price = ?, funding_required = ?, stake_offered_pct = ?, valuation = ?, loan_amount = ?, loan_interest_pct = ?, description = ?, overview = ?, products_services = ?, reason_for_sale = ?, assets_included = ?, facilities = ?, capitalization = ?, status = ?, is_published = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
        $updateStmt->execute([$businessName, $slug, $listingType, $sectorId, $countryId, $stateId, $cityId, $province, $district, $establishedYear, $employeeCount, $legalEntityType, $monthlyRevenue, $annualRevenue, $ebitdaPct, $askingPrice, $fundingRequired, $stakeOfferedPct, $valuation, $loanAmount, $loanInterestPct, $description, $overview, $productsServices, $reasonForSale, $assetsIncluded, $facilities, $capitalization, $status, $isPublished, $businessId, $userId]);

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

        // Handle document delete
        if (!empty($_POST['delete_document'])) {
            $deleteDocIds = array_map('intval', $_POST['delete_document']);
            $placeholders = implode(',', array_fill(0, count($deleteDocIds), '?'));
            $delDocStmt = $db->prepare("SELECT file_path FROM business_documents WHERE id IN ($placeholders) AND business_id = ?");
            $delDocStmt->execute(array_merge($deleteDocIds, [$businessId]));
            $toDelete = $delDocStmt->fetchAll();
            $db->prepare("DELETE FROM business_documents WHERE id IN ($placeholders) AND business_id = ?")->execute(array_merge($deleteDocIds, [$businessId]));
            $docDir = upload_path('business-documents');
            foreach ($toDelete as $d) {
                $path = $docDir . '/' . basename($d['file_path']);
                if (file_exists($path)) unlink($path);
            }
        }

        // Handle document uploads
        if (!empty($_FILES['documents'])) {
            $docFiles = $_FILES['documents'];
            $allowedDocMime = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            $docDestDir = upload_path('business-documents');
            $maxSortStmt = $db->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM business_documents WHERE business_id = ?');
            $maxSortStmt->execute([$businessId]);
            $sortOrder = (int)$maxSortStmt->fetchColumn();
            $batchDesc = trim($_POST['document_desc'] ?? '');
            foreach ($docFiles['tmp_name'] as $i => $tmpName) {
                if ($docFiles['error'][$i] !== UPLOAD_ERR_OK) continue;
                $file = ['name' => $docFiles['name'][$i], 'tmp_name' => $tmpName, 'size' => $docFiles['size'][$i], 'error' => $docFiles['error'][$i]];
                $filename = handle_upload($file, $allowedDocMime, UPLOAD_MAX_BYTES, $docDestDir);
                if ($filename) {
                    $mimeType = mime_content_type($docDestDir . '/' . $filename);
                    $db->prepare('INSERT INTO business_documents (business_id, original_name, file_path, file_size, file_type, description, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())')
                        ->execute([$businessId, $file['name'], 'business-documents/' . $filename, $file['size'], $mimeType, $batchDesc, $sortOrder]);
                    $sortOrder++;
                }
            }
        }

        $db->prepare('DELETE FROM business_assets WHERE business_id = ?')->execute([$businessId]);
        if (!empty($_POST['asset_name'])) {
            $aStmt = $db->prepare('INSERT INTO business_assets (business_id, asset_name, asset_type, estimated_value, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
            foreach ($_POST['asset_name'] as $i => $name) {
                $name = trim($name);
                if ($name === '') continue;
                $aStmt->execute([$businessId, $name, $_POST['asset_type'][$i] ?? null, !empty($_POST['asset_value'][$i]) ? (float)$_POST['asset_value'][$i] : null, trim($_POST['asset_desc'][$i] ?? '')]);
            }
        }

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
<style>
.edit-wrap {
  display:grid;grid-template-columns:1fr 380px;gap:32px;align-items:start;
}
@media (max-width:1100px) {
  .edit-wrap { grid-template-columns:1fr; }
}

.edit-form { min-width:0; }

.edit-section {
  background:#fff;border:1px solid var(--dash-border);border-radius:12px;
  margin-bottom:20px;overflow:hidden;
}
.edit-section-header {
  display:flex;align-items:center;justify-content:space-between;
  padding:16px 20px;cursor:pointer;user-select:none;
  transition:background 150ms;
}
.edit-section-header:hover { background:var(--color-bg-soft); }
.edit-section-header h3 { margin:0;font-size:15px;font-weight:700;color:var(--dash-ink); }
.edit-section-header .sec-toggle {
  width:24px;height:24px;display:flex;align-items:center;justify-content:center;
  transition:transform 200ms;color:var(--dash-ink-soft);font-size:18px;
}
.edit-section.collapsed .edit-section-body { display:none; }
.edit-section.collapsed .sec-toggle { transform:rotate(-90deg); }
.edit-section-body { padding:0 20px 20px; }

.edit-grid { display:grid;grid-template-columns:1fr 1fr;gap:14px; }
.edit-grid-3 { display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px; }
.edit-grid-full { display:grid;grid-template-columns:1fr;gap:14px; }
.edit-grid .input-group, .edit-grid-3 .input-group, .edit-grid-full .input-group { margin-bottom:0; }

.edit-grid .input label, .edit-grid-3 .input label, .edit-grid-full .input label {
  font-size:12px;font-weight:600;color:var(--dash-ink-soft);margin-bottom:4px;display:block;
}
.edit-grid .input input, .edit-grid .input select, .edit-grid .input textarea,
.edit-grid-3 .input input, .edit-grid-3 .input select, .edit-grid-3 .input textarea,
.edit-grid-full .input input, .edit-grid-full .input select, .edit-grid-full .input textarea {
  width:100%;padding:9px 12px;border:1px solid var(--dash-border);border-radius:8px;
  font-size:14px;color:var(--dash-ink);background:#fff;transition:border-color 200ms,box-shadow 200ms;
  box-sizing:border-box;
}
.edit-grid .input input:focus, .edit-grid .input select:focus, .edit-grid .input textarea:focus,
.edit-grid-3 .input input:focus, .edit-grid-3 .input select:focus, .edit-grid-3 .input textarea:focus,
.edit-grid-full .input input:focus, .edit-grid-full .input select:focus, .edit-grid-full .input textarea:focus {
  border-color:var(--color-primary);box-shadow:0 0 0 3px rgba(107,29,34,0.1);outline:none;
}

.edit-actions { display:flex;gap:12px;align-items:center;padding:16px 0; }
.edit-actions .btn { font-size:14px;padding:10px 24px; }

/* ── Live Preview ── */
.edit-preview {
  position:sticky;top:88px;
  background:#fff;border:1px solid var(--dash-border);border-radius:12px;overflow:hidden;
}
.edit-preview-head {
  padding:14px 18px;border-bottom:1px solid var(--dash-border);
  display:flex;align-items:center;justify-content:space-between;
  background:var(--color-bg-soft);
}
.edit-preview-head h3 { margin:0;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:var(--dash-ink-soft); }
.edit-preview-head .preview-badge {
  font-size:10px;padding:2px 8px;border-radius:999px;
  background:rgba(107,29,34,0.08);color:var(--color-primary);font-weight:600;
}
.edit-preview-body { padding:20px; }

.preview-card {
  border:1px solid var(--dash-border);border-radius:10px;overflow:hidden;
  background:#fff;transition:box-shadow 200ms;
}
.preview-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.06); }
.preview-card-img {
  width:100%;height:160px;object-fit:cover;background:var(--color-bg-soft);
  display:flex;align-items:center;justify-content:center;color:var(--dash-ink-soft);font-size:13px;
}
.preview-card-img img { width:100%;height:100%;object-fit:cover; }
.preview-card-body { padding:16px; }
.preview-card-badges { display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px; }
.preview-badge-type {
  font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;
  padding:2px 8px;border-radius:999px;background:rgba(107,29,34,0.08);color:var(--color-primary);
}
.preview-badge-sector {
  font-size:10px;font-weight:600;padding:2px 8px;border-radius:999px;
  background:var(--color-bg-soft);color:var(--dash-ink-soft);
}
.preview-card-title { font-size:18px;font-weight:700;color:var(--dash-ink);margin:0 0 4px; }
.preview-card-loc { font-size:13px;color:var(--dash-ink-soft);margin-bottom:10px; }
.preview-card-metrics { display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px; }
.preview-metric {
  background:var(--color-bg-soft);border-radius:8px;padding:10px;
}
.preview-metric-label { font-size:10px;text-transform:uppercase;letter-spacing:0.04em;color:var(--dash-ink-soft);margin-bottom:2px; }
.preview-metric-value { font-size:15px;font-weight:700;color:var(--dash-ink); }
.preview-card-desc { font-size:13px;line-height:1.5;color:var(--dash-ink-soft);margin:0;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden; }

/* Mobile media grid */
.edit-media-grid {
  display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:12px;
}
.edit-media-item {
  position:relative;border-radius:8px;overflow:hidden;
  border:1px solid var(--dash-border);
}
.edit-media-item img, .edit-media-item video { width:100%;height:90px;object-fit:cover;display:block; }
.edit-media-del {
  position:absolute;top:4px;right:4px;
  background:rgba(0,0,0,0.55);color:#fff;border:none;border-radius:4px;
  padding:2px 6px;font-size:11px;cursor:pointer;
}

/* Upload dropzone */
.upload-dropzone {
  border:2px dashed var(--dash-border);border-radius:10px;
  padding:28px 16px;text-align:center;cursor:pointer;
  transition:border-color 200ms,background 200ms;position:relative;
}
.upload-dropzone:hover { border-color:var(--color-primary);background:rgba(107,29,34,0.03); }
.upload-dropzone.drag-over { border-color:var(--color-primary);background:rgba(107,29,34,0.06); }
.upload-dropzone-content { pointer-events:none; }
.upload-dropzone-content svg { color:var(--dash-ink-soft);margin-bottom:8px; }
.upload-dropzone-content p { margin:0 0 4px;font-size:14px;font-weight:600;color:var(--dash-ink); }
.upload-dropzone-content span { font-size:12px;color:var(--dash-ink-soft); }

/* Upload preview grid */
.upload-preview { display:flex;flex-wrap:wrap;gap:10px;margin-top:14px; }
.upload-preview-item {
  position:relative;width:100px;height:100px;border-radius:8px;
  overflow:hidden;border:1px solid var(--dash-border);
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  background:var(--color-bg-soft);
}
.upload-preview-item img { width:100%;height:100%;object-fit:cover;display:block; }
.upload-preview-icon { font-size:11px;font-weight:700;color:var(--dash-ink-soft); }
.upload-preview-name {
  position:absolute;bottom:0;left:0;right:0;
  font-size:9px;padding:2px 4px;background:rgba(0,0,0,0.55);color:#fff;
  text-overflow:ellipsis;overflow:hidden;white-space:nowrap;text-align:center;
}
.upload-preview-remove {
  position:absolute;top:2px;right:2px;width:20px;height:20px;border:none;
  background:rgba(0,0,0,0.55);color:#fff;border-radius:50%;
  font-size:14px;line-height:1;cursor:pointer;display:flex;
  align-items:center;justify-content:center;
}
.upload-preview-remove:hover { background:rgba(200,40,40,0.8); }

/* Repeater rows */
.repeater-row {
  display:grid;grid-template-columns:1fr;gap:10px;
  padding:14px;background:var(--color-bg-soft);border-radius:8px;margin-bottom:10px;
  position:relative;
}
.repeater-row .repeater-fields { display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px; }
@media (max-width:600px) {
  .repeater-row .repeater-fields { grid-template-columns:1fr; }
}
.repeater-remove {
  position:absolute;top:8px;right:8px;
  width:24px;height:24px;border-radius:50%;border:none;
  background:rgba(200,50,50,0.12);color:var(--color-error);
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;font-size:14px;transition:background 150ms;
}
.repeater-remove:hover { background:rgba(200,50,50,0.2); }
</style>

<div class="edit-wrap">
  <div class="edit-form">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
      <div>
        <h2 style="margin:0 0 4px;">Edit Business Listing</h2>
        <p style="margin:0;color:var(--dash-ink-soft);font-size:14px;">Update your business information. Changes are reflected in the live preview.</p>
      </div>
      <a href="<?= APP_URL ?>/business/<?= e($business['slug'] ?: $business['id']) ?>" class="btn btn-sm btn-outline" target="_blank">View Live</a>
    </div>

    <form method="POST" enctype="multipart/form-data" id="editForm" novalidate>
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="id" value="<?= $businessId ?>">

      <!-- ═══ Basic Info ═══ -->
      <div class="edit-section">
        <div class="edit-section-header" onclick="this.parentElement.classList.toggle('collapsed')">
          <h3>Basic Information</h3>
          <span class="sec-toggle">▼</span>
        </div>
        <div class="edit-section-body">
          <div class="edit-grid">
            <div class="input">
              <label>Business Name <span style="color:var(--color-error);">*</span></label>
              <input type="text" name="business_name" class="preview-input" data-preview="name" value="<?= e($business['business_name']) ?>" required>
            </div>
            <div class="input">
              <label>Listing Type <span style="color:var(--color-error);">*</span></label>
              <select name="listing_type" class="preview-input" data-preview="type" required>
                <option value="">Select type...</option>
                <?php foreach ($listingTypes as $val => $label): ?>
                <option value="<?= $val ?>" <?= $business['listing_type'] === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="input">
              <label>Sector / Industry</label>
              <select name="sector_id" class="preview-input" data-preview="sector">
                <option value="">Select sector...</option>
                <?php foreach ($sectors as $s): ?>
                <option value="<?= $s['id'] ?>" <?= (int)$business['sector_id'] === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="input">
              <label>Legal Entity Type</label>
              <select name="legal_entity_type" class="preview-input" data-preview="entity">
                <option value="">Select...</option>
                <?php foreach ($legalEntityTypes as $let): ?>
                <option value="<?= e($let) ?>" <?= $business['legal_entity_type'] === $let ? 'selected' : '' ?>><?= e($let) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="input">
              <label>Country</label>
              <select name="country_id" onchange="updateStates(this.value)">
                <option value="">Select country...</option>
                <?php foreach ($countries as $c): ?>
                <option value="<?= $c['id'] ?>" <?= (int)$business['country_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="input">
              <label>State / Province (dropdown)</label>
              <select name="state_id" onchange="updateCities(this.value)">
                <option value="">Select state...</option>
                <?php foreach ($states as $s): ?>
                <option value="<?= $s['id'] ?>" data-country="<?= $s['country_id'] ?>" <?= (int)$business['state_id'] === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="input">
              <label>City / District (dropdown)</label>
              <select name="city_id">
                <option value="">Select city...</option>
                <?php foreach ($cities as $c): ?>
                <option value="<?= $c['id'] ?>" data-state="<?= $c['state_id'] ?>" <?= (int)$business['city_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="input">
              <label>Province (text, fallback)</label>
              <input type="text" name="province" class="preview-input" data-preview="province" value="<?= e($business['province'] ?? '') ?>" placeholder="e.g. Bagmati">
            </div>
            <div class="input">
              <label>District (text, fallback)</label>
              <input type="text" name="district" class="preview-input" data-preview="district" value="<?= e($business['district'] ?? '') ?>" placeholder="e.g. Kathmandu">
            </div>
            <div class="input">
              <label>Established Year</label>
              <input type="number" name="established_year" data-preview="year" class="preview-input" min="1900" max="<?= date('Y') ?>" value="<?= e($business['established_year']) ?>">
            </div>
            <div class="input">
              <label>Employee Count</label>
              <input type="number" name="employee_count" class="preview-input" data-preview="employees" min="0" value="<?= e($business['employee_count']) ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- ═══ Financial ═══ -->
      <div class="edit-section">
        <div class="edit-section-header" onclick="this.parentElement.classList.toggle('collapsed')">
          <h3>Financial Details</h3>
          <span class="sec-toggle">▼</span>
        </div>
        <div class="edit-section-body">
          <div class="edit-grid">
            <div class="input">
              <label>Monthly Revenue (NPR)</label>
              <input type="number" name="monthly_revenue" class="preview-input" data-preview="monthly" min="0" step="0.01" value="<?= e($business['monthly_revenue']) ?>">
            </div>
            <div class="input">
              <label>Annual Revenue (NPR)</label>
              <input type="number" name="annual_revenue" class="preview-input" data-preview="revenue" min="0" step="0.01" value="<?= e($business['annual_revenue']) ?>">
            </div>
            <div class="input">
              <label>EBITDA Margin (%)</label>
              <input type="number" name="ebitda_pct" class="preview-input" data-preview="ebitda" min="0" max="100" step="0.01" value="<?= e($business['ebitda_pct']) ?>">
            </div>
            <div class="input">
              <label>Asking Price (NPR)</label>
              <input type="number" name="asking_price" class="preview-input" data-preview="price" min="0" step="0.01" value="<?= e($business['asking_price']) ?>">
            </div>
            <div class="input">
              <label>Funding Required (NPR)</label>
              <input type="number" name="funding_required" min="0" step="0.01" value="<?= e($business['funding_required']) ?>">
            </div>
            <div class="input">
              <label>Valuation (NPR)</label>
              <input type="number" name="valuation" min="0" step="0.01" value="<?= e($business['valuation']) ?>">
            </div>
            <div class="input">
              <label>Stake Offered (%)</label>
              <input type="number" name="stake_offered_pct" class="preview-input" data-preview="stake" min="0" max="100" step="0.01" value="<?= e($business['stake_offered_pct']) ?>">
            </div>
            <div class="input">
              <label>Loan Amount (NPR)</label>
              <input type="number" name="loan_amount" min="0" step="0.01" value="<?= e($business['loan_amount']) ?>">
            </div>
            <div class="input">
              <label>Loan Interest (%)</label>
              <input type="number" name="loan_interest_pct" min="0" max="100" step="0.01" value="<?= e($business['loan_interest_pct']) ?>">
            </div>
          </div>

          <div style="margin-top:18px;">
            <h4 style="font-size:14px;font-weight:600;margin:0 0 10px;color:var(--dash-ink);">Historical Financials</h4>
            <div id="financials-container">
              <?php if (!empty($financials)): foreach ($financials as $f): ?>
              <div class="repeater-row financial-row">
                <button type="button" class="repeater-remove" onclick="this.closest('.financial-row').remove()">✕</button>
                <div class="repeater-fields">
                  <div class="input"><label>Fiscal Year</label><input type="number" name="financial_year[]" class="input" value="<?= $f['fiscal_year'] ?>" min="2000"></div>
                  <div class="input"><label>Revenue</label><input type="number" name="financial_revenue[]" class="input" step="0.01" value="<?= $f['revenue'] ?>"></div>
                  <div class="input"><label>Expenses</label><input type="number" name="financial_expenses[]" class="input" step="0.01" value="<?= $f['expenses'] ?>"></div>
                  <div class="input"><label>Profit</label><input type="number" name="financial_profit[]" class="input" step="0.01" value="<?= $f['profit'] ?>"></div>
                  <div class="input"><label>EBITDA</label><input type="number" name="financial_ebitda[]" class="input" step="0.01" value="<?= $f['ebitda'] ?>"></div>
                </div>
              </div>
              <?php endforeach; else: ?>
              <div class="repeater-row financial-row">
                <button type="button" class="repeater-remove" onclick="this.closest('.financial-row').remove()">✕</button>
                <div class="repeater-fields">
                  <div class="input"><label>Fiscal Year</label><input type="number" name="financial_year[]" class="input" placeholder="e.g. 2024" min="2000"></div>
                  <div class="input"><label>Revenue</label><input type="number" name="financial_revenue[]" class="input" step="0.01"></div>
                  <div class="input"><label>Expenses</label><input type="number" name="financial_expenses[]" class="input" step="0.01"></div>
                  <div class="input"><label>Profit</label><input type="number" name="financial_profit[]" class="input" step="0.01"></div>
                  <div class="input"><label>EBITDA</label><input type="number" name="financial_ebitda[]" class="input" step="0.01"></div>
                </div>
              </div>
              <?php endif; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline" onclick="addFinancialRow()" style="margin-top:4px;">+ Add Year</button>
          </div>
        </div>
      </div>

      <!-- ═══ Description ═══ -->
      <div class="edit-section">
        <div class="edit-section-header" onclick="this.parentElement.classList.toggle('collapsed')">
          <h3>Description &amp; Details</h3>
          <span class="sec-toggle">▼</span>
        </div>
        <div class="edit-section-body">
          <div class="edit-grid-full">
            <div class="input">
              <label>Short Description <span style="color:var(--dash-ink-soft);font-weight:400;">(shown in cards)</span></label>
              <textarea name="description" class="preview-input" data-preview="desc" style="min-height:70px;"><?= e($business['description']) ?></textarea>
            </div>
            <div class="input">
              <label>Business Overview</label>
              <textarea name="overview" style="min-height:120px;"><?= e($business['overview']) ?></textarea>
            </div>
            <div class="input">
              <label>Products &amp; Services</label>
              <textarea name="products_services" style="min-height:100px;"><?= e($business['products_services']) ?></textarea>
            </div>
            <div class="input">
              <label>Reason for Sale</label>
              <textarea name="reason_for_sale" style="min-height:80px;"><?= e($business['reason_for_sale']) ?></textarea>
            </div>
            <div class="input">
              <label>Facilities</label>
              <textarea name="facilities" style="min-height:80px;"><?= e($business['facilities']) ?></textarea>
            </div>
            <div class="input">
              <label>Capitalization</label>
              <textarea name="capitalization" style="min-height:80px;"><?= e($business['capitalization']) ?></textarea>
            </div>
            <div class="input">
              <label>Assets Included <span style="color:var(--dash-ink-soft);font-weight:400;">(text description)</span></label>
              <textarea name="assets_included" style="min-height:80px;"><?= e($business['assets_included']) ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══ Assets ═══ -->
      <div class="edit-section">
        <div class="edit-section-header" onclick="this.parentElement.classList.toggle('collapsed')">
          <h3>Assets</h3>
          <span class="sec-toggle">▼</span>
        </div>
        <div class="edit-section-body">
          <div id="assets-container">
            <?php if (!empty($assets)): foreach ($assets as $a): ?>
            <div class="repeater-row asset-row">
              <button type="button" class="repeater-remove" onclick="this.closest('.asset-row').remove()">✕</button>
              <div class="repeater-fields" style="grid-template-columns:2fr 1fr 1fr 2fr;">
                <div class="input"><label>Asset Name</label><input type="text" name="asset_name[]" class="input" value="<?= e($a['asset_name']) ?>"></div>
                <div class="input"><label>Type</label><select name="asset_type[]" class="input"><?php foreach (['land','building','equipment','inventory','vehicle','intellectual_property','other'] as $at): ?><option value="<?= $at ?>" <?= $a['asset_type'] === $at ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$at)) ?></option><?php endforeach; ?></select></div>
                <div class="input"><label>Value</label><input type="number" name="asset_value[]" class="input" step="0.01" value="<?= $a['estimated_value'] ?>"></div>
                <div class="input"><label>Description</label><input type="text" name="asset_desc[]" class="input" value="<?= e($a['description']) ?>"></div>
              </div>
            </div>
            <?php endforeach; else: ?>
            <div class="repeater-row asset-row">
              <button type="button" class="repeater-remove" onclick="this.closest('.asset-row').remove()">✕</button>
              <div class="repeater-fields" style="grid-template-columns:2fr 1fr 1fr 2fr;">
                <div class="input"><label>Asset Name</label><input type="text" name="asset_name[]" class="input" placeholder="e.g. Building"></div>
                <div class="input"><label>Type</label><select name="asset_type[]" class="input"><option value="">Select...</option><option value="land">Land</option><option value="building">Building</option><option value="equipment">Equipment</option><option value="inventory">Inventory</option><option value="vehicle">Vehicle</option><option value="intellectual_property">IP</option><option value="other">Other</option></select></div>
                <div class="input"><label>Value</label><input type="number" name="asset_value[]" class="input" step="0.01"></div>
                <div class="input"><label>Description</label><input type="text" name="asset_desc[]" class="input"></div>
              </div>
            </div>
            <?php endif; ?>
          </div>
          <button type="button" class="btn btn-sm btn-outline" onclick="addAssetRow()" style="margin-top:4px;">+ Add Asset</button>
        </div>
      </div>

      <!-- ═══ Documents ═══ -->
      <div class="edit-section">
        <div class="edit-section-header" onclick="this.parentElement.classList.toggle('collapsed')">
          <h3>Documents <span style="font-weight:400;font-size:12px;color:var(--dash-ink-soft);">(premium only)</span></h3>
          <span class="sec-toggle">▼</span>
        </div>
        <div class="edit-section-body">
          <?php if (!empty($documents)): ?>
          <div style="margin-bottom:14px;">
            <h4 style="font-size:13px;font-weight:600;margin:0 0 8px;color:var(--dash-ink);">Uploaded Documents</h4>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
              <?php foreach ($documents as $d): ?>
              <label class="edit-media-item" style="display:flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid var(--dash-border);border-radius:8px;cursor:pointer;background:var(--color-bg-soft);">
                <input type="checkbox" name="delete_document[]" value="<?= $d['id'] ?>" style="accent-color:var(--color-error);">
                <div>
                  <span style="font-size:12px;font-weight:600;color:var(--dash-ink);display:block;">
                    <?php
                    $ext = strtolower(pathinfo($d['original_name'], PATHINFO_EXTENSION));
                    $icon = $ext === 'pdf' ? 'file-pdf' : 'file-word';
                    ?>
                    <i class="fas fa-<?= $icon ?>" style="margin-right:4px;color:var(--color-primary);"></i>
                    <?= e($d['original_name']) ?>
                  </span>
                  <span style="font-size:11px;color:var(--dash-ink-soft);">
                    <?= $d['file_size'] ? number_format($d['file_size'] / 1024, 1) . ' KB' : '' ?>
                    <?= $d['description'] ? ' — ' . e($d['description']) : '' ?>
                  </span>
                </div>
              </label>
              <?php endforeach; ?>
            </div>
            <p style="font-size:11px;color:var(--color-error);margin-top:6px;">Check a document and save to delete it.</p>
          </div>
          <?php endif; ?>

          <div class="input">
            <label>Add New Documents</label>
            <div class="upload-dropzone" id="docDropzone">
              <div class="upload-dropzone-content">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36">
                  <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p>Drop documents here or click to browse</p>
                <span>PDF, DOC, DOCX up to 10MB each</span>
              </div>
              <input type="file" name="documents[]" id="docInput" multiple accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" hidden>
            </div>
            <div class="upload-preview" id="docPreview"></div>
          </div>
          <div class="input" style="margin-top:8px;">
            <label>Description <span style="font-weight:400;font-size:11px;color:var(--dash-ink-soft);">(optional — applies to all new docs)</span></label>
            <input type="text" name="document_desc" class="input" placeholder="e.g. Financial Statement 2024" style="font-size:13px;">
          </div>
        </div>
      </div>

      <!-- ═══ Media & Publish ═══ -->
      <div class="edit-section">
        <div class="edit-section-header" onclick="this.parentElement.classList.toggle('collapsed')">
          <h3>Media &amp; Publish</h3>
          <span class="sec-toggle">▼</span>
        </div>
        <div class="edit-section-body">
          <div style="margin-top:18px;">
            <h4 style="font-size:14px;font-weight:600;margin:0 0 10px;color:var(--dash-ink);">Gallery Media</h4>
            <?php if (!empty($mediaItems)): ?>
            <div class="edit-media-grid" style="margin-bottom:14px;">
              <?php foreach ($mediaItems as $m): ?>
              <div class="edit-media-item">
                <?php if ($m['media_type'] === 'image'): ?>
                <img src="<?= upload_url($m['file_url']) ?>" alt="">
                <?php elseif ($m['media_type'] === 'video'): ?>
                <video src="<?= upload_url($m['file_url']) ?>"></video>
                <?php else: ?>
                <div style="width:100%;height:90px;display:flex;align-items:center;justify-content:center;background:var(--color-bg-soft);font-size:11px;color:var(--dash-ink-soft);">PDF</div>
                <?php endif; ?>
                <label class="edit-media-del">
                  <input type="checkbox" name="delete_media[]" value="<?= $m['id'] ?>"> Delete
                </label>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="input">
              <label>Add New Images / Videos</label>
              <div class="upload-dropzone" id="uploadDropzone">
                <div class="upload-dropzone-content">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36">
                    <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                  </svg>
                  <p>Drop files here or click to browse</p>
                  <span>JPEG, PNG, WebP, MP4, PDF up to 2MB each</span>
                </div>
                <input type="file" name="media[]" id="mediaInput" multiple accept="image/jpeg,image/png,image/webp,video/mp4,application/pdf" hidden>
              </div>
              <div class="upload-preview" id="uploadPreview"></div>
            </div>
          </div>

          <div style="margin-top:18px;padding-top:16px;border-top:1px solid var(--dash-border);">
            <div class="edit-grid">
              <div class="input">
                <label>Status</label>
                <select name="status">
                  <option value="draft" <?= $business['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                  <option value="pending" <?= $business['status'] === 'pending' ? 'selected' : '' ?>>Pending Approval</option>
                  <option value="approved" <?= $business['status'] === 'approved' ? 'selected' : '' ?>>Approved (Published)</option>
                  <option value="sold" <?= $business['status'] === 'sold' ? 'selected' : '' ?>>Sold / Closed</option>
                </select>
              </div>
            </div>
          </div>

          <div class="edit-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="<?= APP_URL ?>/business/<?= e($business['slug'] ?: $business['id']) ?>" class="btn btn-outline">Cancel</a>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- ═══ Live Preview ═══ -->
  <div class="edit-preview" id="livePreview">
    <div class="edit-preview-head">
      <h3>Live Preview</h3>
      <span class="preview-badge">Auto-update</span>
    </div>
    <div class="edit-preview-body">
      <div class="preview-card">
        <div class="preview-card-body">
          <div class="preview-card-badges">
            <span class="preview-badge-type" id="previewType"><?= e($listingTypes[$business['listing_type']] ?? 'Business for Sale') ?></span>
            <span class="preview-badge-sector" id="previewSector"><?php $sectorName = ''; foreach ($sectors as $s) { if ((int)$s['id'] === (int)$business['sector_id']) { $sectorName = $s['name']; break; } } echo e($sectorName); ?></span>
          </div>
          <h3 class="preview-card-title" id="previewName"><?= e($business['business_name']) ?></h3>
          <div class="preview-card-loc" id="previewLoc">
            <?php
            $locParts = array_filter([$business['district'] ?? '', $business['province'] ?? '']);
            echo !empty($locParts) ? e(implode(', ', $locParts)) : 'Nepal';
            ?>
          </div>
          <div class="preview-card-metrics">
            <div class="preview-metric">
              <div class="preview-metric-label">Asking Price</div>
              <div class="preview-metric-value" id="previewPrice"><?= money($business['asking_price'] ?? 0) ?></div>
            </div>
            <div class="preview-metric">
              <div class="preview-metric-label">Annual Revenue</div>
              <div class="preview-metric-value" id="previewRevenue"><?= money($business['annual_revenue'] ?? 0) ?></div>
            </div>
            <div class="preview-metric">
              <div class="preview-metric-label">EBITDA</div>
              <div class="preview-metric-value" id="previewEbitda"><?= ($business['ebitda_pct'] ?? '') ? e($business['ebitda_pct']) . '%' : '—' ?></div>
            </div>
            <div class="preview-metric">
              <div class="preview-metric-label"><?= !empty($business['stake_offered_pct']) ? 'Stake' : 'Type' ?></div>
              <div class="preview-metric-value" id="previewStake"><?= !empty($business['stake_offered_pct']) ? e($business['stake_offered_pct']) . '%' : e($listingTypes[$business['listing_type']] ?? '—') ?></div>
            </div>
          </div>
          <p class="preview-card-desc" id="previewDesc"><?= e(mb_substr(strip_tags($business['description'] ?? ''), 0, 200)) ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function updateStates(countryId) {
    document.querySelectorAll('select[name="state_id"] option').forEach(function(o) {
        o.style.display = o.value === '' || o.dataset.country == countryId ? '' : 'none';
    });
}

function updateCities(stateId) {
    document.querySelectorAll('select[name="city_id"] option').forEach(function(o) {
        o.style.display = o.value === '' || o.dataset.state == stateId ? '' : 'none';
    });
}

function addFinancialRow() {
    var c = document.getElementById('financials-container');
    var t = c.querySelector('.financial-row');
    if (!t) return;
    var n = t.cloneNode(true);
    n.querySelectorAll('input').forEach(function(i) { i.value = ''; });
    c.appendChild(n);
}

function addAssetRow() {
    var c = document.getElementById('assets-container');
    var t = c.querySelector('.asset-row');
    if (!t) return;
    var n = t.cloneNode(true);
    n.querySelectorAll('input, select').forEach(function(i) { i.value = ''; });
    c.appendChild(n);
}

document.addEventListener('DOMContentLoaded', function() {
    var cid = document.querySelector('select[name="country_id"]');
    if (cid) { updateStates(cid.value); }
    var sid = document.querySelector('select[name="state_id"]');
    if (sid) { updateCities(sid.value); }
});

// Live preview
(function() {
    var typeLabels = <?= json_encode($listingTypes) ?>;
    var sectors = <?= json_encode($sectors) ?>;

    function money(val) {
        var n = parseFloat(val) || 0;
        return 'NPR ' + n.toLocaleString('en-IN');
    }

    function updatePreview() {
        // Name
        var name = document.querySelector('[name="business_name"]');
        document.getElementById('previewName').textContent = name ? (name.value || 'Business Name') : 'Business Name';

        // Type badge
        var type = document.querySelector('[name="listing_type"]');
        var typeVal = type ? type.value : '';
        document.getElementById('previewType').textContent = typeLabels[typeVal] || 'Business for Sale';

        // Sector badge
        var sector = document.querySelector('[name="sector_id"]');
        var sectorId = sector ? sector.value : '';
        var sectorName = '';
        sectors.forEach(function(s) {
            if (s.id == sectorId) sectorName = s.name;
        });
        document.getElementById('previewSector').textContent = sectorName;

        // Location
        var province = document.querySelector('[name="province"]');
        var district = document.querySelector('[name="district"]');
        var parts = [];
        if (district && district.value) parts.push(district.value);
        if (province && province.value) parts.push(province.value);
        document.getElementById('previewLoc').textContent = parts.length ? parts.join(', ') : 'Nepal';

        // Price
        var price = document.querySelector('[name="asking_price"]');
        document.getElementById('previewPrice').textContent = price && price.value ? money(price.value) : '—';

        // Revenue
        var revenue = document.querySelector('[name="annual_revenue"]');
        document.getElementById('previewRevenue').textContent = revenue && revenue.value ? money(revenue.value) : '—';

        // EBITDA
        var ebitda = document.querySelector('[name="ebitda_pct"]');
        document.getElementById('previewEbitda').textContent = ebitda && ebitda.value ? ebitda.value + '%' : '—';

        // Stake / Type
        var stake = document.querySelector('[name="stake_offered_pct"]');
        document.getElementById('previewStake').textContent = stake && stake.value ? stake.value + '%' : (typeLabels[typeVal] || '—');

        // Description
        var desc = document.querySelector('[name="description"]');
        var descText = desc ? desc.value : '';
        document.getElementById('previewDesc').textContent = descText ? descText.substring(0, 200) : 'No description yet.';

    }

    document.querySelectorAll('.preview-input, [name="description"], [name="province"], [name="district"]').forEach(function(el) {
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });

    updatePreview();
})();

// Drag & drop upload with live preview
(function() {
    var dropzone = document.getElementById('uploadDropzone');
    var input = document.getElementById('mediaInput');
    var preview = document.getElementById('uploadPreview');
    var fileList = [];

    function showPreviews() {
        preview.innerHTML = '';
        fileList.forEach(function(f, idx) {
            var div = document.createElement('div');
            div.className = 'upload-preview-item';
            if (f.type.startsWith('image/')) {
                var img = document.createElement('img');
                img.src = URL.createObjectURL(f);
                img.alt = f.name;
                div.appendChild(img);
            } else {
                var icon = document.createElement('div');
                icon.className = 'upload-preview-icon';
                icon.textContent = f.type.includes('pdf') ? 'PDF' : 'VID';
                div.appendChild(icon);
            }
            var name = document.createElement('span');
            name.className = 'upload-preview-name';
            name.textContent = f.name;
            div.appendChild(name);
            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'upload-preview-remove';
            remove.innerHTML = '&times;';
            remove.setAttribute('aria-label', 'Remove ' + f.name);
            remove.onclick = function() {
                fileList.splice(idx, 1);
                syncInput();
                showPreviews();
            };
            div.appendChild(remove);
            preview.appendChild(div);
        });
    }

    function syncInput() {
        var dt = new DataTransfer();
        fileList.forEach(function(f) { dt.items.add(f); });
        input.files = dt.files;
    }

    function addFiles(files) {
        for (var i = 0; i < files.length; i++) {
            fileList.push(files[i]);
        }
        syncInput();
        showPreviews();
    }

    input.addEventListener('change', function() {
        if (this.files) addFiles(this.files);
        this.value = '';
    });

    dropzone.addEventListener('click', function() { input.click(); });

    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.add('drag-over');
    });

    dropzone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('drag-over');
    });

    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('drag-over');
        if (e.dataTransfer.files) addFiles(e.dataTransfer.files);
    });
})();

// Document drag & drop upload preview
(function() {
    var dropzone = document.getElementById('docDropzone');
    var input = document.getElementById('docInput');
    var preview = document.getElementById('docPreview');
    var fileList = [];

    function showPreviews() {
        preview.innerHTML = '';
        fileList.forEach(function(f, idx) {
            var div = document.createElement('div');
            div.className = 'upload-preview-item';
            var ext = f.name.split('.').pop().toLowerCase();
            var icon = ext === 'pdf' ? 'PDF' : 'DOC';
            var iconEl = document.createElement('div');
            iconEl.className = 'upload-preview-icon';
            iconEl.textContent = icon;
            div.appendChild(iconEl);
            var name = document.createElement('span');
            name.className = 'upload-preview-name';
            name.textContent = f.name;
            div.appendChild(name);
            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'upload-preview-remove';
            remove.innerHTML = '&times;';
            remove.setAttribute('aria-label', 'Remove ' + f.name);
            remove.onclick = function() {
                fileList.splice(idx, 1);
                syncInput();
                showPreviews();
            };
            div.appendChild(remove);
            preview.appendChild(div);
        });
    }

    function syncInput() {
        var dt = new DataTransfer();
        fileList.forEach(function(f) { dt.items.add(f); });
        input.files = dt.files;
    }

    function addFiles(files) {
        for (var i = 0; i < files.length; i++) {
            fileList.push(files[i]);
        }
        syncInput();
        showPreviews();
    }

    input.addEventListener('change', function() {
        if (this.files) addFiles(this.files);
        this.value = '';
    });

    dropzone.addEventListener('click', function() { input.click(); });

    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.add('drag-over');
    });

    dropzone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('drag-over');
    });

    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('drag-over');
        if (e.dataTransfer.files) addFiles(e.dataTransfer.files);
    });
})();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
