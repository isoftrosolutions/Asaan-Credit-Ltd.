<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role([ROLE_INVESTOR, 'individual_investor']);

$user = current_user();
$userId = $user['id'];

$stmt = db()->prepare('SELECT * FROM investor_profiles WHERE user_id = ?');
$stmt->execute([$userId]);
$profile = $stmt->fetch();

if (!$profile) {
    redirect('/investor/profile-create.php');
}

$stmt = db()->query('SELECT id, name FROM sectors WHERE is_active = 1 ORDER BY name');
$sectors = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $pastInvestments = (int)($_POST['past_investments'] ?? 0);
    $portfolioCompanies = trim($_POST['portfolio_companies'] ?? '');
    $totalCapitalDeployed = $_POST['total_capital_deployed'] !== '' ? (float)$_POST['total_capital_deployed'] : null;
    $preferredSectors = !empty($_POST['preferred_sectors']) ? json_encode($_POST['preferred_sectors']) : '[]';
    $preferredStages = !empty($_POST['preferred_stages']) ? json_encode($_POST['preferred_stages']) : '[]';
    $ticketMin = $_POST['ticket_min'] !== '' ? (float)$_POST['ticket_min'] : null;
    $ticketMax = $_POST['ticket_max'] !== '' ? (float)$_POST['ticket_max'] : null;
    $preferredGeography = !empty($_POST['preferred_geography']) ? json_encode($_POST['preferred_geography']) : '[]';
    $references = trim($_POST['references'] ?? '');

    $stmt = db()->prepare('
        UPDATE investor_profiles SET
            past_investments = ?, portfolio_companies = ?, total_capital_deployed = ?,
            preferred_sectors = ?, preferred_stages = ?, ticket_min = ?, ticket_max = ?,
            preferred_geography = ?, `references` = ?, updated_at = NOW()
        WHERE user_id = ?
    ');
    $stmt->execute([$pastInvestments, $portfolioCompanies, $totalCapitalDeployed, $preferredSectors, $preferredStages, $ticketMin, $ticketMax, $preferredGeography, $references, $userId]);

    $stmt = db()->prepare('UPDATE users SET bio = ?, linkedin_url = ? WHERE id = ?');
    $stmt->execute([trim($_POST['bio'] ?? ''), trim($_POST['linkedin_url'] ?? ''), $userId]);

    if (!empty($_POST['name'])) {
        $stmt = db()->prepare('UPDATE users SET name = ? WHERE id = ?');
        $stmt->execute([trim($_POST['name']), $userId]);
        $_SESSION['user']['name'] = trim($_POST['name']);
    }
    if (!empty($_POST['organization_name'])) {
        $stmt = db()->prepare('UPDATE users SET company_name = ? WHERE id = ?');
        $stmt->execute([trim($_POST['organization_name']), $userId]);
    }

    flash_set('success', 'Profile updated successfully.');
    redirect('/dashboard');
}

$pageTitle = 'Edit Investor Profile';
require __DIR__ . '/../includes/layout-dashboard.php';

ui_page_header('Edit Investor Profile', 'Keep your profile current to get sharper matches.');
?>
<div class="dash-panel dash-panel-pad dash-form">
  <form method="POST" class="form-steps" style="padding:0;">
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
            <label>Full Name</label>
            <input type="text" name="name" class="input" value="<?= e($user['name'] ?? '') ?>">
          </div>
          <div class="input-group">
            <label>Investor Type</label>
            <select name="investor_type" class="input">
              <option value="">Select type</option>
              <option value="individual">Individual Investor</option>
              <option value="angel">Angel Investor</option>
              <option value="venture_capital">Venture Capital</option>
              <option value="private_equity">Private Equity</option>
              <option value="family_office">Family Office</option>
              <option value="corporate">Corporate Acquirer</option>
              <option value="lender">Lender / NBFC</option>
              <option value="advisor">M&A Advisor</option>
            </select>
          </div>
          <div class="input-group">
            <label>Organization Name</label>
            <input type="text" name="organization_name" class="input" value="<?= e($user['company_name'] ?? '') ?>" placeholder="e.g., Thapa Capital">
          </div>
          <div class="input-group">
            <label>Designation</label>
            <input type="text" name="designation" class="input" value="" placeholder="e.g., Managing Partner">
          </div>
          <div class="input-group" style="grid-column:1/-1;">
            <label>LinkedIn / Website</label>
            <input type="url" name="linkedin_url" class="input" value="<?= e($user['linkedin_url'] ?? '') ?>">
          </div>
          <div class="input-group" style="grid-column:1/-1;">
            <label>Bio</label>
            <textarea name="bio" class="input" style="min-height:100px;"><?= e($user['bio'] ?? '') ?></textarea>
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
            <input type="number" name="past_investments" class="input" value="<?= e($profile['past_investments'] ?? '0') ?>" min="0">
          </div>
          <div class="input-group">
            <label>Total Capital Deployed (NPR)</label>
            <input type="text" name="total_capital_deployed" class="input" value="<?= e($profile['total_capital_deployed'] ?? '') ?>">
          </div>
          <div class="input-group" style="grid-column:1/-1;">
            <label>Portfolio Companies</label>
            <textarea name="portfolio_companies" class="input" style="min-height:60px;"><?= e($profile['portfolio_companies'] ?? '') ?></textarea>
          </div>
          <div class="input-group" style="grid-column:1/-1;">
            <label>References</label>
            <textarea name="references" class="input" style="min-height:60px;"><?= e($profile['references'] ?? '') ?></textarea>
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
            <input type="text" name="ticket_min" class="input" value="<?= e($profile['ticket_min'] ?? '') ?>">
          </div>
          <div class="input-group">
            <label>Ticket Max (NPR)</label>
            <input type="text" name="ticket_max" class="input" value="<?= e($profile['ticket_max'] ?? '') ?>">
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
        <button type="submit" class="btn btn-primary btn-step-submit" style="display:none">Save changes</button>
      </div>
    </div>
    <div style="text-align:center;margin-top:0.75rem;">
      <a href="<?= APP_URL ?>/dashboard" class="btn btn-outline" style="display:inline-flex;">Cancel</a>
    </div>
  </form>
</div>

<div style="margin-top:var(--space-5);font-size:.88rem;display:flex;gap:var(--space-5);flex-wrap:wrap;">
  <a href="<?= APP_URL ?>/investor/preferences-edit" class="dash-section-link">Edit investment preferences <?= ui_icon_str('arrowRight') ?></a>
  <a href="<?= APP_URL ?>/investor/documents-edit" class="dash-section-link">Manage verification documents <?= ui_icon_str('arrowRight') ?></a>
</div>

<script src="/assets/form-steps.js"></script>
<script>
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
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
