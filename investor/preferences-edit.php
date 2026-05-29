<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role(ROLE_INVESTOR);

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

    $preferredSectors = !empty($_POST['preferred_sectors']) ? json_encode($_POST['preferred_sectors']) : '[]';
    $preferredStages = !empty($_POST['preferred_stages']) ? json_encode($_POST['preferred_stages']) : '[]';
    $ticketMin = $_POST['ticket_min'] !== '' ? (float)$_POST['ticket_min'] : null;
    $ticketMax = $_POST['ticket_max'] !== '' ? (float)$_POST['ticket_max'] : null;
    $preferredGeography = !empty($_POST['preferred_geography']) ? json_encode($_POST['preferred_geography']) : '[]';

    $stmt = db()->prepare('
        UPDATE investor_profiles SET
            preferred_sectors = ?, preferred_stages = ?, ticket_min = ?, ticket_max = ?,
            preferred_geography = ?, updated_at = NOW()
        WHERE user_id = ?
    ');
    $stmt->execute([$preferredSectors, $preferredStages, $ticketMin, $ticketMax, $preferredGeography, $userId]);

    flash_set('success', 'Investment preferences updated successfully.');
    redirect('/dashboard');
}

$pageTitle = 'Edit Investment Preferences';
require __DIR__ . '/../includes/layout-dashboard.php';
?>
<div style="max-width:780px; margin:0 auto; padding:2rem 0 4rem;">
  <div style="margin-bottom:1.5rem;">
    <h2 style="margin:0 0 0.25rem;">Edit Investment Preferences</h2>
    <p style="color:#666;">These preferences power your Smart Suggestions. Update anytime.</p>
  </div>

  <form method="POST">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">

    <div class="card">
      <h4>Preferred Sectors</h4>
      <p style="font-size:0.9rem; color:#666; margin-bottom:0.75rem;">Select all that apply</p>
      <div style="display:flex; flex-wrap:wrap; gap:6px;">
        <?php $selectedSectors = json_decode($profile['preferred_sectors'] ?? '[]', true) ?: []; ?>
        <?php foreach ($sectors as $s): ?>
        <label class="preference-tag<?= in_array($s['name'], $selectedSectors) ? ' selected' : '' ?>" style="display:inline-flex;align-items:center;gap:4px;padding:6px 14px;border-radius:999px;font-size:0.85rem;margin:2px;cursor:pointer;user-select:none;background:<?= in_array($s['name'], $selectedSectors) ? '#C41E3A' : 'rgba(196,30,58,0.1)' ?>;color:<?= in_array($s['name'], $selectedSectors) ? '#fff' : '#aa3700' ?>;">
          <input type="checkbox" name="preferred_sectors[]" value="<?= e($s['name']) ?>" <?= in_array($s['name'], $selectedSectors) ? 'checked' : '' ?> style="display:none;">
          <?= e($s['name']) ?>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card" style="margin-top:1.5rem;">
      <h4>Preferred Startup Stages</h4>
      <div style="display:flex; flex-wrap:wrap; gap:6px; margin-top:0.5rem;">
        <?php $stages = ['Idea', 'MVP', 'Early Revenue', 'Growth', 'Established'];
        $selectedStages = json_decode($profile['preferred_stages'] ?? '[]', true) ?: []; ?>
        <?php foreach ($stages as $stage): ?>
        <label class="preference-tag<?= in_array($stage, $selectedStages) ? ' selected' : '' ?>" style="display:inline-flex;align-items:center;gap:4px;padding:6px 14px;border-radius:999px;font-size:0.85rem;margin:2px;cursor:pointer;user-select:none;background:<?= in_array($stage, $selectedStages) ? '#C41E3A' : 'rgba(196,30,58,0.1)' ?>;color:<?= in_array($stage, $selectedStages) ? '#fff' : '#aa3700' ?>;">
          <input type="checkbox" name="preferred_stages[]" value="<?= e($stage) ?>" <?= in_array($stage, $selectedStages) ? 'checked' : '' ?> style="display:none;">
          <?= e($stage) ?>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card" style="margin-top:1.5rem;">
      <h4>Ticket Size Range (NPR)</h4>
      <div style="display:flex; gap:1rem; align-items:center;">
        <div class="input-group" style="flex:1;">
          <label>Minimum</label>
          <input type="text" name="ticket_min" class="input" value="<?= e($profile['ticket_min'] ?? '') ?>" placeholder="e.g., 10000000">
        </div>
        <div class="input-group" style="flex:1;">
          <label>Maximum</label>
          <input type="text" name="ticket_max" class="input" value="<?= e($profile['ticket_max'] ?? '') ?>" placeholder="e.g., 100000000">
        </div>
      </div>
      <div style="font-size:0.8rem; color:#888; margin-top:0.5rem;">Typical range: NPR 10M – 100M</div>
    </div>

    <div class="card" style="margin-top:1.5rem;">
      <h4>Preferred Geography</h4>
      <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:0.5rem;">
        <?php $geos = ['Bagmati', 'Gandaki', 'Lumbini', 'Koshi', 'Karnali', 'Sudurpashchim'];
        $selectedGeos = json_decode($profile['preferred_geography'] ?? '[]', true) ?: []; ?>
        <?php foreach ($geos as $geo): ?>
        <label style="display:flex;align-items:center;gap:6px;background:#f6f3f1;padding:8px 14px;border-radius:999px;cursor:pointer;">
          <input type="checkbox" name="preferred_geography[]" value="<?= e($geo) ?>" <?= in_array($geo, $selectedGeos) ? 'checked' : '' ?>>
          <?= e($geo) ?>
        </label>
        <?php endforeach; ?>
      </div>
      <label style="display:flex;align-items:center;gap:8px;margin-top:1rem;cursor:pointer;">
        <input type="checkbox" <?= count($selectedGeos) === 6 ? 'checked' : '' ?>>
        <span>Open to opportunities <strong>anywhere in Nepal</strong></span>
      </label>
    </div>

    <div style="margin-top:2rem; display:flex; gap:1rem;">
      <a href="<?= APP_URL ?>/dashboard" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Save Preferences</button>
    </div>

    <div style="margin-top:1.5rem; font-size:0.8rem; color:#888;">
      Changes will update your Smart Match score within 24 hours.
    </div>
  </form>
</div>

<script>
document.querySelectorAll('.preference-tag').forEach(function(tag) {
  tag.addEventListener('click', function() {
    var cb = this.querySelector('input[type=checkbox]');
    cb.checked = !cb.checked;
    if (cb.checked) {
      this.style.background = '#C41E3A';
      this.style.color = '#fff';
    } else {
      this.style.background = 'rgba(196,30,58,0.1)';
      this.style.color = '#aa3700';
    }
  });
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
