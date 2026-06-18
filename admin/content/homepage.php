<?php
require __DIR__ . '/../../config/bootstrap.php';
require_admin();

$pageTitle = 'Edit Homepage Content';
require __DIR__ . '/../../includes/layout-admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $values = $_POST['values'] ?? [];
    $stmt = db()->prepare('UPDATE homepage_contents SET value = ?, updated_at = NOW() WHERE `key` = ?');
    foreach ($values as $key => $value) {
        $stmt->execute([trim($value), $key]);
    }
    admin_log('update_homepage', 'homepage_contents', null, ['keys' => array_keys($values)]);
    flash_set('success', 'Homepage content updated.');
    redirect('/admin/homepage');
}

$stmt = db()->query('SELECT * FROM homepage_contents ORDER BY FIELD(`key`, "hero_title","hero_subtitle","stats_businesses","stats_investors","stats_matches","stats_deal_value")');
$contents = $stmt->fetchAll();

$fields = [
    'hero_title' => [
        'label' => 'Hero Title',
        'desc' => 'The big headline at the top of your homepage. Use <span class="highlight">text</span> to make words stand out in brand color.',
        'type' => 'editor',
        'group' => 'Hero Section',
        'group_desc' => 'This is the first thing visitors see on your homepage.',
    ],
    'hero_subtitle' => [
        'label' => 'Hero Subtitle',
        'desc' => 'The supporting text below the headline. Explain what your platform does in 1-2 sentences.',
        'type' => 'textarea',
        'group' => 'Hero Section',
    ],
    'stats_businesses' => [
        'label' => 'Businesses Listed',
        'desc' => 'Shown in the stats bar. Example: 67,500+',
        'type' => 'text',
        'group' => 'Trust Statistics',
        'group_desc' => 'These numbers build trust with visitors. Update them as your platform grows.',
        'icon' => 'briefcase',
    ],
    'stats_investors' => [
        'label' => 'Investors',
        'desc' => 'Shown in the stats bar. Example: 44,000+',
        'type' => 'text',
        'group' => 'Trust Statistics',
        'icon' => 'users',
    ],
    'stats_matches' => [
        'label' => 'Successful Matches',
        'desc' => 'Shown in the stats bar. Example: 12,800+',
        'type' => 'text',
        'group' => 'Trust Statistics',
        'icon' => 'matches',
    ],
    'stats_deal_value' => [
        'label' => 'Total Deal Value',
        'desc' => 'Shown in the stats bar. Example: NPR 850 Cr+',
        'type' => 'text',
        'group' => 'Trust Statistics',
        'icon' => 'chart',
    ],
];

$grouped = [];
foreach ($contents as $c) {
    $meta = $fields[$c['key']] ?? ['label' => $c['key'], 'desc' => '', 'type' => (strlen($c['value'] ?? '') > 100 ? 'textarea' : 'text'), 'group' => 'Other'];
    $meta['key'] = $c['key'];
    $meta['value'] = $c['value'] ?? '';
    $grouped[$meta['group']][] = $meta;
}
?>
<style>
  .hp-group { margin-bottom: var(--space-5); }
  .hp-group-header { margin-bottom: var(--space-4); }
  .hp-group-title { font-size: 1.1rem; font-weight: 700; color: var(--dash-ink); }
  .hp-group-desc { font-size: 0.85rem; color: var(--dash-ink-soft); margin-top: 2px; }
  .hp-field { margin-bottom: var(--space-4); }
  .hp-field:last-child { margin-bottom: 0; }
  .hp-label { display: block; font-size: 0.88rem; font-weight: 600; color: var(--dash-ink); margin-bottom: 4px; }
  .hp-desc { font-size: 0.8rem; color: var(--dash-ink-soft); margin-bottom: 6px; line-height: 1.4; }
  .hp-stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4); }
  .hp-stat-card { border: 1px solid var(--dash-border); border-radius: var(--dash-radius-ctl); padding: var(--space-4); background: var(--dash-bg); }
  .hp-stat-icon { font-size: 1.2rem; color: var(--dash-primary); margin-bottom: var(--space-2); display: block; }
  .hp-stat-card .hp-label { font-size: 0.82rem; }
  .hp-stat-card .hp-desc { font-size: 0.75rem; }
  .hp-stat-card input { font-size: 1.1rem; font-weight: 700; }
  .hp-preview { background: var(--dash-bg); border: 1px dashed var(--dash-border); border-radius: var(--dash-radius-ctl); padding: var(--space-4); margin-top: var(--space-3); font-size: 0.85rem; }
  .hp-preview-title { font-weight: 600; color: var(--dash-ink-soft); margin-bottom: var(--space-2); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
  .hp-preview-content { color: var(--dash-ink); line-height: 1.5; }
  .hp-preview-content .highlight { color: var(--color-primary-vivid); font-weight: 600; }
  @media (max-width: 640px) { .hp-stats-grid { grid-template-columns: 1fr; } }
</style>

<div class="dash-pagehead">
  <div class="dash-pagehead-text">
    <h1 class="dash-pagehead-title">Edit Homepage Content</h1>
    <p class="dash-pagehead-sub">Update what visitors see on your homepage. Changes take effect immediately.</p>
  </div>
</div>

<form method="post">
  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">

  <?php foreach ($grouped as $group => $items): ?>
  <div class="dash-panel dash-panel-pad hp-group">
    <div class="hp-group-header">
      <div class="hp-group-title"><?= e($group) ?></div>
      <?php if (!empty($items[0]['group_desc'])): ?>
        <div class="hp-group-desc"><?= e($items[0]['group_desc']) ?></div>
      <?php endif; ?>
    </div>

    <?php if ($group === 'Trust Statistics'): ?>
    <div class="hp-stats-grid">
      <?php foreach ($items as $f): ?>
      <div class="hp-stat-card">
        <span class="hp-stat-icon"><i class="fas fa-<?= e($f['icon'] ?? 'chart') ?>"></i></span>
        <label class="hp-label" for="val-<?= e($f['key']) ?>"><?= e($f['label']) ?></label>
        <div class="hp-desc"><?= e($f['desc']) ?></div>
        <input type="text" id="val-<?= e($f['key']) ?>" name="values[<?= e($f['key']) ?>]" class="input" value="<?= e($f['value']) ?>">
      </div>
      <?php endforeach; ?>
    </div>

    <?php else: ?>
    <?php foreach ($items as $f): ?>
    <div class="hp-field">
      <label class="hp-label" for="val-<?= e($f['key']) ?>"><?= e($f['label']) ?></label>
      <div class="hp-desc"><?= e($f['desc']) ?></div>
      <?php if ($f['type'] === 'editor' || $f['type'] === 'textarea'): ?>
        <textarea id="val-<?= e($f['key']) ?>" name="values[<?= e($f['key']) ?>]" class="input" rows="4" style="resize:vertical;"><?= e($f['value']) ?></textarea>
      <?php else: ?>
        <input type="text" id="val-<?= e($f['key']) ?>" name="values[<?= e($f['key']) ?>]" class="input" value="<?= e($f['value']) ?>">
      <?php endif; ?>
      <?php if ($f['key'] === 'hero_title' || $f['key'] === 'hero_subtitle'): ?>
      <div class="hp-preview">
        <div class="hp-preview-title">Live preview</div>
        <div class="hp-preview-content" id="preview-<?= e($f['key']) ?>"><?= $f['value'] ?></div>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>

  <div class="dash-panel dash-panel-pad" style="text-align:right;">
    <button type="submit" class="btn btn-primary" style="padding:12px 32px;">Save all changes</button>
  </div>
</form>

<script>
(function() {
  var previewFields = ['hero_title', 'hero_subtitle'];
  previewFields.forEach(function(key) {
    var input = document.getElementById('val-' + key);
    var preview = document.getElementById('preview-' + key);
    if (input && preview) {
      input.addEventListener('input', function() {
        preview.innerHTML = input.value;
      });
    }
  });
})();
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
