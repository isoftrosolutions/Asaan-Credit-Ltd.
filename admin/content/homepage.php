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

$stmt = db()->query('SELECT * FROM homepage_contents ORDER BY `key` ASC');
$contents = $stmt->fetchAll();

$grouped = [];
foreach ($contents as $c) {
    $prefix = explode('_', $c['key'])[0] ?? 'general';
    $grouped[$prefix][] = $c;
}
?>
<h2>Edit Homepage Content</h2>
<form method="post">
  <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
  <?php foreach ($grouped as $group => $items): ?>
  <div class="card" style="margin-bottom:1rem;">
    <h4 style="text-transform:capitalize;margin-bottom:0.5rem;"><?= e($group) ?></h4>
    <?php foreach ($items as $c): ?>
    <div class="input-group">
      <label><?= e(str_replace('_', ' ', $c['key'])) ?></label>
      <?php if (strlen($c['value'] ?? '') > 100): ?>
        <textarea name="values[<?= e($c['key']) ?>]" class="input" rows="4" style="resize:vertical;"><?= e($c['value'] ?? '') ?></textarea>
      <?php else: ?>
        <input type="text" name="values[<?= e($c['key']) ?>]" class="input" value="<?= e($c['value'] ?? '') ?>">
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>
  <button type="submit" class="btn btn-primary">Save All Changes</button>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
