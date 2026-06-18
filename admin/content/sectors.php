<?php
require __DIR__ . '/../../config/bootstrap.php';
require_admin();

$pageTitle = 'Manage Sectors';
require __DIR__ . '/../../includes/layout-admin.php';

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $slug = $_POST['slug'] ?? '';
        if (!$slug) $slug = generate_slug($name);
        if ($name) {
            db()->prepare('INSERT INTO sectors (name, slug, is_active) VALUES (?, ?, 1)')->execute([$name, $slug]);
            admin_log('create_sector', 'sector', null, ['name' => $name, 'slug' => $slug]);
            flash_set('success', 'Sector created.');
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = $_POST['slug'] ?? '';
        if (!$slug) $slug = generate_slug($name);
        if ($id && $name) {
            db()->prepare('UPDATE sectors SET name = ?, slug = ? WHERE id = ?')->execute([$name, $slug, $id]);
            admin_log('edit_sector', 'sector', $id);
            flash_set('success', 'Sector updated.');
        }
    } elseif ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = db()->prepare('SELECT is_active FROM sectors WHERE id = ?');
        $stmt->execute([$id]);
        $sector = $stmt->fetch();
        if ($sector) {
            $newStatus = $sector['is_active'] ? 0 : 1;
            db()->prepare('UPDATE sectors SET is_active = ? WHERE id = ?')->execute([$newStatus, $id]);
            admin_log('toggle_sector', 'sector', $id, ['is_active' => $newStatus]);
            flash_set('success', 'Sector toggled.');
        }
    }
    redirect('/admin/sectors');
}

$stmt = db()->query('SELECT * FROM sectors ORDER BY name ASC');
$sectors = $stmt->fetchAll();
?>
<style>
  .slug-preview {
    font-size: 0.78rem;
    color: var(--dash-ink-soft);
    margin-top: 4px;
    min-height: 1.2em;
  }
  .slug-preview code {
    background: var(--dash-bg);
    padding: 1px 6px;
    border-radius: 3px;
    font-size: 0.82rem;
  }
</style>

<div class="dash-pagehead">
  <div class="dash-pagehead-text">
    <h1 class="dash-pagehead-title">Manage Sectors</h1>
    <p class="dash-pagehead-sub"><strong><?= count($sectors) ?></strong> sectors — used to categorize businesses, pitches, and investor preferences</p>
  </div>
</div>

<div class="dash-panel dash-panel-pad" style="margin-bottom:var(--space-5);">
  <details>
    <summary style="cursor:pointer;font-weight:600;font-size:0.95rem;color:var(--dash-primary);padding:4px 0;">+ Add new sector</summary>
    <form method="post" style="margin-top:var(--space-4);max-width:480px;">
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="create">
      <div class="input-group">
        <label>Sector name</label>
        <input type="text" name="name" class="input" id="sector-name" required placeholder="e.g. Artificial Intelligence">
      </div>
      <div class="input-group">
        <label>URL slug <span style="font-weight:400;color:var(--dash-ink-soft);">(leave blank to auto-generate from name)</span></label>
        <input type="text" name="slug" class="input" id="sector-slug" placeholder="auto-generated">
        <div class="slug-preview" id="slug-preview">URL: <code>../browse?category=<span id="slug-text"></span></code></div>
      </div>
      <button type="submit" class="btn btn-sm btn-primary">Create sector</button>
    </form>
  </details>
</div>

<?php if (empty($sectors)): ?>
<div class="dash-panel">
  <?php ui_empty_state(['icon' => 'tag', 'title' => 'No sectors yet', 'text' => 'Sectors help organize listings on the platform. Add your first sector above.']); ?>
</div>
<?php else: ?>
<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th style="width:60px;">ID</th><th>Name</th><th>Slug</th><th style="width:80px;" class="ta-center">Active</th><th style="width:220px;" class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
    <?php foreach ($sectors as $s): ?>
    <tr>
      <td class="t-muted"><?= $s['id'] ?></td>
      <td><span class="t-strong"><?= e($s['name']) ?></span></td>
      <td><code><?= e($s['slug']) ?></code></td>
      <td class="ta-center">
        <span class="dash-pill <?= $s['is_active'] ? 'published' : 'draft' ?>"><?= $s['is_active'] ? 'Active' : 'Inactive' ?></span>
      </td>
      <td class="ta-right">
        <span class="dash-table-actions">
          <form method="post" style="display:inline;">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= $s['id'] ?>">
            <button type="submit" class="btn btn-sm btn-outline"><?= $s['is_active'] ? 'Deactivate' : 'Activate' ?></button>
          </form>
          <a href="/admin/sectors/edit?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline" style="text-decoration:none;">Edit</a>
        </span>
      </td>
    </tr>
    <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<script>
(function() {
  var nameInput = document.getElementById('sector-name');
  var slugInput = document.getElementById('sector-slug');
  var slugText = document.getElementById('slug-text');
  if (nameInput && slugInput && slugText) {
    var origSlug = slugInput.value;
    nameInput.addEventListener('input', function() {
      if (slugInput.value === '' || slugInput.value === origSlug || slugInput.dataset.auto === '1') {
        var slug = nameInput.value.toLowerCase()
          .replace(/[^a-z0-9]+/g, '-')
          .replace(/^-+|-+$/g, '');
        slugInput.value = slug;
        slugInput.dataset.auto = '1';
        slugText.textContent = slug;
      }
    });
    slugInput.addEventListener('input', function() {
      slugInput.dataset.auto = '';
      slugText.textContent = slugInput.value || nameInput.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    });
    slugInput.addEventListener('focus', function() {
      if (slugInput.dataset.auto === '1') slugInput.select();
    });
    nameInput.dispatchEvent(new Event('input'));
  }
})();
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
