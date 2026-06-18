<?php
require __DIR__ . '/../../config/bootstrap.php';
require_admin();

$pageTitle = 'Edit Sector';
require __DIR__ . '/../../includes/layout-admin.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if (!$id) {
    flash_set('error', 'No sector specified.');
    redirect('/admin/sectors');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'update') {
        $name = trim($_POST['name'] ?? '');
        $slug = $_POST['slug'] ?? '';
        if (!$slug) $slug = generate_slug($name);
        if ($name) {
            db()->prepare('UPDATE sectors SET name = ?, slug = ? WHERE id = ?')->execute([$name, $slug, $id]);
            admin_log('edit_sector', 'sector', $id);
            flash_set('success', 'Sector updated.');
            redirect('/admin/sectors');
        } else {
            flash_set('error', 'Sector name is required.');
            redirect('/admin/sectors/edit?id=' . $id);
        }
    } elseif ($action === 'delete') {
        db()->prepare('DELETE FROM sectors WHERE id = ?')->execute([$id]);
        admin_log('delete_sector', 'sector', $id);
        flash_set('success', 'Sector deleted.');
        redirect('/admin/sectors');
    }
}

$stmt = db()->prepare('SELECT * FROM sectors WHERE id = ?');
$stmt->execute([$id]);
$s = $stmt->fetch();

if (!$s) {
    flash_set('error', 'Sector not found.');
    redirect('/admin/sectors');
}
?>

<div class="dash-pagehead">
  <div class="dash-pagehead-text">
    <h1 class="dash-pagehead-title">Edit Sector</h1>
    <p class="dash-pagehead-sub">Editing: <strong><?= e($s['name']) ?></strong></p>
  </div>
  <div class="dash-pagehead-actions">
    <a href="/admin/sectors" class="btn btn-sm btn-outline">&larr; Back to sectors</a>
  </div>
</div>

<div class="dash-panel dash-panel-pad" style="max-width:520px;">
  <form method="post">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" value="<?= $s['id'] ?>">

    <div class="input-group">
      <label>Sector name</label>
      <input type="text" name="name" class="input" value="<?= e($s['name']) ?>" required placeholder="e.g. Artificial Intelligence">
    </div>

    <div class="input-group">
      <label>URL slug <span style="font-weight:400;color:var(--dash-ink-soft);">(leave blank to auto-generate)</span></label>
      <input type="text" name="slug" class="input" value="<?= e($s['slug']) ?>" placeholder="auto-generated">
    </div>

    <div style="display:flex;gap:var(--space-3);padding-top:var(--space-4);border-top:1px solid var(--dash-border);">
      <button type="submit" class="btn btn-primary">Save changes</button>
      <a href="/admin/sectors" class="btn btn-outline">Cancel</a>
      <span style="margin-left:auto;">
        <button type="submit" class="btn btn-sm btn-danger" form="delete-form">Delete sector</button>
      </span>
    </div>
  </form>

  <form id="delete-form" method="post" onsubmit="return confirm('Delete this sector permanently?')" style="display:none;">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" value="<?= $s['id'] ?>">
  </form>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
