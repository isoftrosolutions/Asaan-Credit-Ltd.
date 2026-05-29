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
        $slug = $_POST['slug'] ?? generate_slug($name);
        if ($name) {
            db()->prepare('INSERT INTO sectors (name, slug, is_active) VALUES (?, ?, 1)')->execute([$name, $slug]);
            admin_log('create_sector', 'sector', null, ['name' => $name, 'slug' => $slug]);
            flash_set('success', 'Sector created.');
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = $_POST['slug'] ?? generate_slug($name);
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
<h2>Manage Sectors</h2>
<div class="card" style="max-width:500px;">
  <h4>Add New Sector</h4>
  <form method="post">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" value="create">
    <div class="input-group">
      <label>Name</label>
      <input type="text" name="name" class="input" required>
    </div>
    <div class="input-group">
      <label>Slug (leave blank to auto-generate)</label>
      <input type="text" name="slug" class="input" placeholder="auto-generated">
    </div>
    <button type="submit" class="btn btn-sm btn-primary">Create</button>
  </form>
</div>
<div class="card" style="margin-top:1rem;">
  <table style="width:100%;">
    <tr style="border-bottom:1px solid var(--color-border);">
      <th style="text-align:left;padding:8px;">ID</th>
      <th style="text-align:left;padding:8px;">Name</th>
      <th style="text-align:left;padding:8px;">Slug</th>
      <th style="padding:8px;">Active</th>
      <th style="padding:8px;">Actions</th>
    </tr>
    <?php foreach ($sectors as $s): ?>
    <tr style="border-bottom:1px solid var(--color-border);">
      <td style="padding:10px 8px;"><?= $s['id'] ?></td>
      <td style="padding:10px 8px;font-weight:600;"><?= e($s['name']) ?></td>
      <td style="padding:10px 8px;"><code><?= e($s['slug']) ?></code></td>
      <td style="padding:10px 8px;"><?= $s['is_active'] ? '<span style="color:var(--color-success);">Yes</span>' : '<span style="color:var(--color-error);">No</span>' ?></td>
      <td style="padding:10px 8px;">
        <form method="post" style="display:inline;">
          <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="toggle">
          <input type="hidden" name="id" value="<?= $s['id'] ?>">
          <button type="submit" class="btn btn-sm btn-outline"><?= $s['is_active'] ? 'Deactivate' : 'Activate' ?></button>
        </form>
        <details style="display:inline;vertical-align:middle;">
          <summary style="font-size:0.8rem;cursor:pointer;color:var(--color-primary-vivid);display:inline;margin-left:0.25rem;">Edit</summary>
          <form method="post" style="margin-top:0.25rem;">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $s['id'] ?>">
            <input type="text" name="name" class="input" value="<?= e($s['name']) ?>" style="font-size:0.8rem;padding:4px 8px;width:180px;" required>
            <input type="text" name="slug" class="input" value="<?= e($s['slug']) ?>" style="font-size:0.8rem;padding:4px 8px;width:150px;">
            <button type="submit" class="btn btn-sm btn-primary">Save</button>
          </form>
        </details>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
