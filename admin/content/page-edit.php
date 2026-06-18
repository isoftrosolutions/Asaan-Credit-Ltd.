<?php
require __DIR__ . '/../../config/bootstrap.php';
require_admin();

$pageTitle = 'Edit Page';
require __DIR__ . '/../../includes/layout-admin.php';

$db = db();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if (!$id) {
    flash_set('error', 'No page specified.');
    redirect('/admin/pages');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $contentHtml = $_POST['content_html'] ?? '';
    $metaDesc = trim($_POST['meta_description'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($title && $slug) {
        $stmt = $db->prepare("UPDATE pages SET title = ?, slug = ?, content_html = ?, meta_description = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$title, $slug, $contentHtml, $metaDesc ?: null, $isActive, $id]);
        admin_log('update_page', 'pages', $id, ['slug' => $slug]);
        flash_set('success', 'Page updated.');
        redirect('/admin/pages');
    } else {
        flash_set('error', 'Title and slug are required.');
        redirect('/admin/pages/edit?id=' . $id);
    }
}

$stmt = $db->prepare("SELECT * FROM pages WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    flash_set('error', 'Page not found.');
    redirect('/admin/pages');
}
?>
<link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<style>
  trix-editor {
    min-height: 320px;
    max-height: 600px;
    overflow-y: auto;
    border-radius: var(--dash-radius-ctl);
    border: 1px solid var(--dash-border);
    font-size: 0.92rem;
    line-height: 1.6;
  }
  trix-editor:focus {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(107, 29, 34, 0.1);
    outline: none;
  }
  trix-toolbar {
    border: 1px solid var(--dash-border);
    border-bottom: none;
    border-radius: var(--dash-radius-ctl) var(--dash-radius-ctl) 0 0;
    background: var(--dash-bg);
    padding: 4px 8px;
  }
  trix-toolbar .trix-button-group { border: none; margin-bottom: 0; }
  trix-toolbar .trix-button { border: none; border-radius: 4px; height: 2.2em; }
  trix-toolbar .trix-button:hover { background: rgba(107, 29, 34, 0.08); }
  trix-toolbar .trix-button.trix-active { background: rgba(107, 29, 34, 0.15); color: var(--color-primary); }
  trix-toolbar .trix-button-group:not(:first-child) { margin-left: 6px; }
  .meta-counter { text-align: right; font-size: 0.78rem; color: var(--dash-ink-soft); margin-top: 4px; }
  .meta-counter.warning { color: var(--dash-warning); }
  .meta-counter.danger { color: var(--dash-error); }
</style>

<div class="dash-pagehead">
  <div class="dash-pagehead-text">
    <h1 class="dash-pagehead-title">Edit Page</h1>
    <p class="dash-pagehead-sub">Editing: <strong><?= e($p['title']) ?></strong></p>
  </div>
  <div class="dash-pagehead-actions">
    <a href="/admin/pages" class="btn btn-sm btn-outline">&larr; Back to pages</a>
    <a href="/<?= e($p['slug']) ?>" target="_blank" class="btn btn-sm btn-outline">Preview page</a>
  </div>
</div>

<div class="dash-panel dash-panel-pad" style="max-width:860px;">
  <form method="post">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
    <input type="hidden" name="id" value="<?= $p['id'] ?>">

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
      <div class="input-group">
        <label>Page title</label>
        <input type="text" name="title" class="input" value="<?= e($p['title']) ?>" required>
      </div>
      <div class="input-group">
        <label>URL slug</label>
        <input type="text" name="slug" class="input" value="<?= e($p['slug']) ?>" required>
        <span style="font-size:0.78rem;color:var(--dash-ink-soft);">The web address. Use lowercase, no spaces.</span>
      </div>
    </div>

    <div class="input-group">
      <label>Meta description <span style="font-weight:400;color:var(--dash-ink-soft);">(shown in search results)</span></label>
      <input type="text" name="meta_description" class="input" value="<?= e($p['meta_description'] ?? '') ?>" maxlength="320" id="meta-desc">
      <div class="meta-counter" id="meta-counter">0 / 320 characters</div>
    </div>

    <div class="input-group">
      <label style="display:flex;align-items:center;gap:8px;">
        <input type="checkbox" name="is_active" value="1" <?= $p['is_active'] ? 'checked' : '' ?>>
        Active <span style="font-weight:400;color:var(--dash-ink-soft);font-size:0.82rem;">— visitors can see this page</span>
      </label>
    </div>

    <div class="input-group">
      <label>Page content</label>
      <input type="hidden" id="page-content" name="content_html" value="<?= e($p['content_html']) ?>">
      <trix-editor input="page-content" placeholder="Edit your page content here…"></trix-editor>
    </div>

    <div style="display:flex;gap:var(--space-3);padding-top:var(--space-4);border-top:1px solid var(--dash-border);">
      <button type="submit" class="btn btn-primary">Save changes</button>
      <a href="/admin/pages" class="btn btn-outline">Cancel</a>
      <span style="margin-left:auto;">
        <form method="post" style="display:inline;" onsubmit="return confirm('Delete this page permanently? This cannot be undone.');">
          <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
          <input type="hidden" name="id" value="<?= $p['id'] ?>">
          <input type="hidden" name="action" value="delete">
          <button type="submit" class="btn btn-sm btn-danger">Delete page</button>
        </form>
      </span>
    </div>
  </form>
</div>

<script src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
<script>
(function() {
  var input = document.getElementById('meta-desc');
  var counter = document.getElementById('meta-counter');
  if (input && counter) {
    function update() {
      var len = input.value.length;
      counter.textContent = len + ' / 320 characters';
      counter.className = 'meta-counter';
      if (len > 280) counter.classList.add('warning');
      if (len > 310) counter.classList.add('danger');
    }
    input.addEventListener('input', update);
    update();
  }
})();
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
