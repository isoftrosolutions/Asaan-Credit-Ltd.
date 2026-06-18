<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role([ROLE_INVESTOR, 'individual_investor']);

$user = current_user();
$userId = $user['id'];

$stmt = db()->prepare('SELECT * FROM investor_profiles WHERE user_id = ?');
$stmt->execute([$userId]);
$profile = $stmt->fetch();

$stmt = db()->query('SELECT id, name FROM sectors WHERE is_active = 1 ORDER BY name');
$sectors = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $pastInvestments = (int)($_POST['past_investments'] ?? 0);
    $portfolioCompanies = trim($_POST['portfolio_companies'] ?? '');
    $totalCapitalDeployed = $_POST['total_capital_deployed'] !== '' ? (float)$_POST['total_capital_deployed'] : null;
    $preferredSectors = !empty($_POST['preferred_sectors']) ? json_encode($_POST['preferred_sectors']) : null;
    $preferredStages = !empty($_POST['preferred_stages']) ? json_encode($_POST['preferred_stages']) : null;
    $ticketMin = $_POST['ticket_min'] !== '' ? (float)$_POST['ticket_min'] : null;
    $ticketMax = $_POST['ticket_max'] !== '' ? (float)$_POST['ticket_max'] : null;
    $preferredGeography = !empty($_POST['preferred_geography']) ? json_encode($_POST['preferred_geography']) : null;
    $references = trim($_POST['references'] ?? '');

    $profilePhoto = null;
    if (!empty($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
        $destDir = upload_path('profile-photos');
        $uploaded = handle_upload($_FILES['profile_photo'], $allowedMime, UPLOAD_MAX_BYTES_PHOTO, $destDir);
        if ($uploaded) {
            $profilePhoto = '/public/uploads/profile-photos/' . $uploaded;
            $stmt = db()->prepare('UPDATE users SET profile_photo = ? WHERE id = ?');
            $stmt->execute([$profilePhoto, $userId]);
        }
    }

    $stmt = db()->prepare('
        INSERT INTO investor_profiles (user_id, past_investments, portfolio_companies, total_capital_deployed, preferred_sectors, preferred_stages, ticket_min, ticket_max, preferred_geography, `references`, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE past_investments = VALUES(past_investments), portfolio_companies = VALUES(portfolio_companies), total_capital_deployed = VALUES(total_capital_deployed), preferred_sectors = VALUES(preferred_sectors), preferred_stages = VALUES(preferred_stages), ticket_min = VALUES(ticket_min), ticket_max = VALUES(ticket_max), preferred_geography = VALUES(preferred_geography), `references` = VALUES(`references`), updated_at = NOW()
    ');
    $stmt->execute([$userId, $pastInvestments, $portfolioCompanies, $totalCapitalDeployed, $preferredSectors, $preferredStages, $ticketMin, $ticketMax, $preferredGeography, $references]);

    $stmt = db()->prepare('UPDATE users SET bio = ?, linkedin_url = ? WHERE id = ?');
    $stmt->execute([trim($_POST['bio'] ?? ''), trim($_POST['linkedin_url'] ?? ''), $userId]);

    if (!empty($_POST['name'])) {
        $stmt = db()->prepare('UPDATE users SET name = ? WHERE id = ?');
        $stmt->execute([trim($_POST['name']), $userId]);
    }
    if (!empty($_POST['organization_name'])) {
        $stmt = db()->prepare('UPDATE users SET company_name = ? WHERE id = ?');
        $stmt->execute([trim($_POST['organization_name']), $userId]);
    }

    $_SESSION['user']['name'] = $user['name'];
    if (!empty($_POST['name'])) $_SESSION['user']['name'] = trim($_POST['name']);

    flash_set('success', 'Investor profile created successfully.');
    redirect('/dashboard');
}

$pageTitle = 'Create Investor Profile';
require __DIR__ . '/../includes/layout-dashboard.php';
?>
<div style="max-width:720px; margin:0 auto; padding:2rem 0 3rem;">
  <h2 style="margin-bottom:0.25rem;">Investor / Buyer Profile</h2>
  <p style="color:var(--color-text-muted); margin-bottom:1.5rem;">Define your investment mandate and get matched with opportunities.</p>

  <form method="POST" class="form-steps" novalidate style="padding:0;" enctype="multipart/form-data">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">

    <div class="form-step-progress" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="3">
      <div class="step-indicator">
        <div class="step-segment active" data-step="1">
          <div class="step-number"><span class="step-check">&#10003;</span><span class="step-num">1</span></div>
          <span class="step-label">Basic Info</span>
        </div>
        <div class="step-line"><div class="step-line-fill" style="width:0%"></div></div>
        <div class="step-segment" data-step="2">
          <div class="step-number">2</div>
          <span class="step-label">Investment</span>
        </div>
        <div class="step-line"><div class="step-line-fill" style="width:0%"></div></div>
        <div class="step-segment" data-step="3">
          <div class="step-number">3</div>
          <span class="step-label">Preferences</span>
        </div>
      </div>
    </div>

    <div class="step-panel" data-step="1">
      <div class="card" style="padding:1.5rem;">
        <h4>Basic Information</h4>
        <div class="r-2">
          <div class="input-group">
            <label>Full Name / Organization</label>
            <input type="text" name="name" class="input" value="<?= e(old('name', $user['name'] ?? '')) ?>" placeholder="e.g., Ramesh Thapa">
          </div>
          <div class="input-group">
            <label>Investor Type</label>
            <select name="investor_type" class="input">
              <option value="">Select type</option>
              <option value="individual"<?= old('investor_type') === 'individual' ? ' selected' : '' ?>>Individual Investor</option>
              <option value="angel"<?= old('investor_type') === 'angel' ? ' selected' : '' ?>>Angel Investor</option>
              <option value="venture_capital"<?= old('investor_type') === 'venture_capital' ? ' selected' : '' ?>>Venture Capital</option>
              <option value="private_equity"<?= old('investor_type') === 'private_equity' ? ' selected' : '' ?>>Private Equity</option>
              <option value="family_office"<?= old('investor_type') === 'family_office' ? ' selected' : '' ?>>Family Office</option>
              <option value="corporate"<?= old('investor_type') === 'corporate' ? ' selected' : '' ?>>Corporate Acquirer</option>
              <option value="lender"<?= old('investor_type') === 'lender' ? ' selected' : '' ?>>Lender / NBFC</option>
              <option value="advisor"<?= old('investor_type') === 'advisor' ? ' selected' : '' ?>>M&A Advisor</option>
            </select>
          </div>
          <div class="input-group">
            <label>Organization Name</label>
            <input type="text" name="organization_name" class="input" value="<?= e(old('organization_name', $user['company_name'] ?? '')) ?>" placeholder="e.g., Thapa Capital">
          </div>
          <div class="input-group">
            <label>Designation</label>
            <input type="text" name="designation" class="input" value="<?= e(old('designation', '')) ?>" placeholder="e.g., Managing Partner">
          </div>
          <div class="input-group" style="grid-column:1/-1;">
            <label>LinkedIn / Website URL</label>
            <input type="url" name="linkedin_url" class="input" value="<?= e(old('linkedin_url', $user['linkedin_url'] ?? '')) ?>" placeholder="https://linkedin.com/in/yourprofile">
          </div>
          <div class="input-group" style="grid-column:1/-1;">
            <label>Profile Photo</label>
            <input type="file" name="profile_photo" class="input" accept="image/jpeg,image/png,image/webp" onchange="previewProfilePhoto(this)">
            <div id="profile-photo-preview" style="margin-top:0.5rem;display:none;">
              <img src="" alt="Preview" style="width:120px;height:120px;object-fit:cover;border-radius:50%;border:1px solid var(--dash-border);">
            </div>
            <p style="font-size:0.8rem;color:var(--color-text-muted);margin-top:0.25rem;">Max 2MB. JPEG, PNG, WebP.</p>
          </div>
          <div class="input-group" style="grid-column:1/-1;">
            <label>Bio</label>
            <textarea name="bio" class="input" placeholder="Tell businesses about your background, investment thesis, and what you're looking for..." style="min-height:100px;"><?= e(old('bio', $user['bio'] ?? '')) ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <div class="step-panel" data-step="2" style="display:none">
      <div class="card" style="padding:1.5rem;">
        <h4>Investment Details</h4>
        <div class="r-2">
          <div class="input-group">
            <label>Past Investments (count)</label>
            <input type="number" name="past_investments" class="input" value="<?= e(old('past_investments', $profile['past_investments'] ?? '0')) ?>" min="0">
          </div>
          <div class="input-group">
            <label>Total Capital Deployed (NPR)</label>
            <input type="text" name="total_capital_deployed" class="input" value="<?= e(old('total_capital_deployed', $profile['total_capital_deployed'] ?? '')) ?>" placeholder="e.g., 50000000">
          </div>
          <div class="input-group" style="grid-column:1/-1;">
            <label>Portfolio Companies</label>
            <textarea name="portfolio_companies" class="input" style="min-height:60px;" placeholder="List notable companies you have invested in"><?= e(old('portfolio_companies', $profile['portfolio_companies'] ?? '')) ?></textarea>
          </div>
          <div class="input-group" style="grid-column:1/-1;">
            <label>Investment References</label>
            <textarea name="references" class="input" style="min-height:60px;" placeholder="References or past deal highlights"><?= e(old('references', $profile['references'] ?? '')) ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <div class="step-panel" data-step="3" style="display:none">
      <div class="card" style="padding:1.5rem;">
        <h4>Preferences</h4>
        <div class="r-2">
          <div class="input-group" style="grid-column:1/-1;">
            <label>Preferred Sectors</label>
            <div style="display:flex; flex-wrap:wrap; gap:6px; margin-top:0.25rem;">
              <?php $selectedSectors = json_decode($profile['preferred_sectors'] ?? '[]', true) ?: []; ?>
              <?php foreach ($sectors as $s): ?>
              <label class="preference-tag<?= in_array($s['name'], $selectedSectors) ? ' selected' : '' ?>" style="display:inline-flex;align-items:center;gap:4px;padding:6px 14px;border-radius:999px;font-size:0.85rem;margin:2px;cursor:pointer;user-select:none;background:<?= in_array($s['name'], $selectedSectors) ? 'var(--color-primary-vivid)' : 'rgba(152,32,42,0.1)' ?>;color:<?= in_array($s['name'], $selectedSectors) ? 'var(--color-text-inverse)' : 'var(--color-text-muted)' ?>;">
                <input type="checkbox" name="preferred_sectors[]" value="<?= e($s['name']) ?>" <?= in_array($s['name'], $selectedSectors) ? 'checked' : '' ?> style="display:none;">
                <?= e($s['name']) ?>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="input-group" style="grid-column:1/-1;">
            <label>Preferred Stages</label>
            <div style="display:flex; flex-wrap:wrap; gap:6px; margin-top:0.25rem;">
              <?php $stages = ['Idea', 'MVP', 'Early Revenue', 'Growth', 'Established'];
              $selectedStages = json_decode($profile['preferred_stages'] ?? '[]', true) ?: []; ?>
              <?php foreach ($stages as $stage): ?>
              <label class="preference-tag<?= in_array($stage, $selectedStages) ? ' selected' : '' ?>" style="display:inline-flex;align-items:center;gap:4px;padding:6px 14px;border-radius:999px;font-size:0.85rem;margin:2px;cursor:pointer;user-select:none;background:<?= in_array($stage, $selectedStages) ? 'var(--color-primary-vivid)' : 'rgba(152,32,42,0.1)' ?>;color:<?= in_array($stage, $selectedStages) ? 'var(--color-text-inverse)' : 'var(--color-text-muted)' ?>;">
                <input type="checkbox" name="preferred_stages[]" value="<?= e($stage) ?>" <?= in_array($stage, $selectedStages) ? 'checked' : '' ?> style="display:none;">
                <?= e($stage) ?>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="input-group">
            <label>Ticket Min (NPR)</label>
            <input type="text" name="ticket_min" class="input" value="<?= e(old('ticket_min', $profile['ticket_min'] ?? '')) ?>" placeholder="e.g., 5000000">
          </div>
          <div class="input-group">
            <label>Ticket Max (NPR)</label>
            <input type="text" name="ticket_max" class="input" value="<?= e(old('ticket_max', $profile['ticket_max'] ?? '')) ?>" placeholder="e.g., 100000000">
          </div>
          <div class="input-group" style="grid-column:1/-1;">
            <label>Preferred Geography</label>
            <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:0.25rem;">
              <?php $geos = ['Bagmati', 'Gandaki', 'Lumbini', 'Koshi', 'Karnali', 'Sudurpashchim'];
              $selectedGeos = json_decode($profile['preferred_geography'] ?? '[]', true) ?: []; ?>
              <?php foreach ($geos as $geo): ?>
              <label style="display:flex;align-items:center;gap:6px;background:var(--color-bg-soft);padding:8px 14px;border-radius:999px;cursor:pointer;">
                <input type="checkbox" name="preferred_geography[]" value="<?= e($geo) ?>" <?= in_array($geo, $selectedGeos) ? 'checked' : '' ?>>
                <?= e($geo) ?>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="step-nav">
      <button type="button" class="btn btn-outline btn-step-back" style="display:none">Back</button>
      <div class="step-nav-right">
        <button type="button" class="btn btn-primary btn-step-next">Next</button>
        <button type="submit" class="btn btn-primary btn-step-submit" style="display:none">Save Profile</button>
      </div>
    </div>
  </form>
</div>

<script src="<?= APP_URL ?>/assets/form-steps.js"></script>
<script>
function previewProfilePhoto(input) {
    var preview = document.getElementById('profile-photo-preview');
    var img = preview.querySelector('img');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) { img.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}
function initPreferenceTags() {
  document.querySelectorAll('.preference-tag').forEach(function(tag) {
    tag.addEventListener('click', function() {
      var cb = this.querySelector('input[type=checkbox]');
      if (!cb) return;
      cb.checked = !cb.checked;
      this.style.background = cb.checked ? 'var(--color-primary-vivid)' : 'rgba(152,32,42,0.1)';
      this.style.color = cb.checked ? 'var(--color-text-inverse)' : 'var(--color-text-muted)';
    });
  });
}
initFormSteps({});
initPreferenceTags();
document.querySelector('.form-steps')?.addEventListener('submit', function() {
  var btn = this.querySelector('.btn-step-submit');
  if (btn) btn.disabled = true;
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
