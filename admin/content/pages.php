<?php
require __DIR__ . '/../../config/bootstrap.php';
require_admin();

$pageTitle = 'Manage Pages';
require __DIR__ . '/../../includes/layout-admin.php';

$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'update' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
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
        } else {
            flash_set('error', 'Title and slug are required.');
        }
        redirect('/admin/pages');
    }

    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $contentHtml = $_POST['content_html'] ?? '';
        $metaDesc = trim($_POST['meta_description'] ?? '');

        if ($title && $slug) {
            try {
                $stmt = $db->prepare("INSERT INTO pages (slug, title, content_html, meta_description, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
                $stmt->execute([$slug, $title, $contentHtml, $metaDesc ?: null]);
                admin_log('create_page', 'pages', $db->lastInsertId(), ['slug' => $slug]);
                flash_set('success', 'Page created.');
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    flash_set('error', 'A page with this slug already exists.');
                } else {
                    flash_set('error', 'Database error.');
                }
            }
        } else {
            flash_set('error', 'Title and slug are required.');
        }
        redirect('/admin/pages');
    }

    if ($action === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $db->prepare("DELETE FROM pages WHERE id = ?")->execute([$id]);
        admin_log('delete_page', 'pages', $id, []);
        flash_set('success', 'Page deleted.');
        redirect('/admin/pages');
    }
}

$pages = $db->query("SELECT * FROM pages ORDER BY slug ASC")->fetchAll();

ui_page_header('Manage Pages', '<strong>' . count($pages) . '</strong> pages');
?>
<link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<style>
  trix-editor {
    min-height: 280px;
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
  trix-toolbar .trix-button-group {
    border: none;
    margin-bottom: 0;
  }
  trix-toolbar .trix-button {
    border: none;
    border-radius: 4px;
    height: 2.2em;
  }
  trix-toolbar .trix-button:hover {
    background: rgba(107, 29, 34, 0.08);
  }
  trix-toolbar .trix-button.trix-active {
    background: rgba(107, 29, 34, 0.15);
    color: var(--color-primary);
  }
  trix-toolbar .trix-button-group:not(:first-child) {
    margin-left: 6px;
  }
  .meta-counter {
    text-align: right;
    font-size: 0.78rem;
    color: var(--dash-ink-soft);
    margin-top: 4px;
  }
  .meta-counter.warning { color: var(--dash-warning); }
  .meta-counter.danger { color: var(--dash-error); }
  .page-edit-panel {
    position: absolute;
    right: 0;
    top: 100%;
    z-index: 10;
    background: var(--dash-card);
    border: 1px solid var(--dash-border);
    border-radius: var(--dash-radius-card);
    box-shadow: var(--dash-shadow-hover);
    padding: var(--space-5);
    width: 760px;
    max-width: 92vw;
    margin-top: 4px;
  }
  @media (max-width: 768px) {
    .page-edit-panel { width: 90vw; right: -40px; }
  }
</style>

<div class="dash-panel dash-panel-pad" style="margin-bottom:var(--space-5);">
  <details>
    <summary style="cursor:pointer;font-weight:600;font-size:0.95rem;color:var(--dash-primary);padding:4px 0;">+ Create new page</summary>
    <form method="post" style="margin-top:var(--space-4);">
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="create">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="input-group">
          <label>Page title</label>
          <input type="text" name="title" class="input" required placeholder="e.g. About Us">
        </div>
        <div class="input-group">
          <label>URL slug</label>
          <input type="text" name="slug" class="input" placeholder="e.g. about" required>
          <span style="font-size:0.78rem;color:var(--dash-ink-soft);">The web address of this page. Use lowercase, no spaces.</span>
        </div>
      </div>
      <div class="input-group">
        <label>Meta description <span style="font-weight:400;color:var(--dash-ink-soft);">(shown in search results)</span></label>
        <input type="text" name="meta_description" class="input" maxlength="320" placeholder="A short summary for Google search results…">
      </div>
      <div class="input-group">
        <label>Page content</label>
        <div id="create-editor-wrapper"></div>
        <input type="hidden" id="create-content" name="content_html">
        <trix-editor input="create-content" placeholder="Start writing your page content here…"></trix-editor>
      </div>
      <button type="submit" class="btn btn-sm btn-primary">Create Page</button>
    </form>
  </details>
</div>

<?php if (empty($pages)): ?>
<div class="dash-panel">
  <?php ui_empty_state(['icon' => 'document', 'title' => 'No pages yet', 'text' => 'Create your first page using the form above.']); ?>
</div>
<?php else: ?>
<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>Page</th><th>Slug</th><th class="ta-center">Status</th><th>Last updated</th><th class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
    <?php foreach ($pages as $p): ?>
    <tr>
      <td><span class="t-strong"><?= e($p['title']) ?></span></td>
      <td><code>/<?= e($p['slug']) ?></code></td>
      <td class="ta-center">
        <span class="dash-pill <?= $p['is_active'] ? 'published' : 'draft' ?>"><?= $p['is_active'] ? 'Active' : 'Inactive' ?></span>
      </td>
      <td class="t-muted"><?= date_human($p['updated_at']) ?></td>
      <td class="ta-right">
        <span class="dash-table-actions">
          <details style="display:inline-block;position:relative;">
            <summary class="btn btn-sm btn-outline" style="cursor:pointer;display:inline-flex;">Edit</summary>
            <div class="page-edit-panel">
              <form method="post">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                  <div class="input-group">
                    <label>Page title</label>
                    <input type="text" name="title" class="input" value="<?= e($p['title']) ?>" required>
                  </div>
                  <div class="input-group">
                    <label>URL slug</label>
                    <input type="text" name="slug" class="input" value="<?= e($p['slug']) ?>" required>
                    <span style="font-size:0.75rem;color:var(--dash-ink-soft);">e.g. about, contact, terms</span>
                  </div>
                </div>
                <div class="input-group">
                  <label>Meta description</label>
                  <input type="text" name="meta_description" class="input" value="<?= e($p['meta_description'] ?? '') ?>" maxlength="320" id="meta-<?= $p['id'] ?>">
                  <div class="meta-counter" id="meta-counter-<?= $p['id'] ?>">0 / 320 characters</div>
                </div>
                <div class="input-group">
                  <label style="display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" name="is_active" value="1" <?= $p['is_active'] ? 'checked' : '' ?>>
                    Active <span style="font-weight:400;color:var(--dash-ink-soft);font-size:0.82rem;">— visitors can see this page</span>
                  </label>
                </div>
                <div class="input-group">
                  <label>Page content</label>
                  <input type="hidden" id="edit-content-<?= $p['id'] ?>" name="content_html" value="<?= e($p['content_html']) ?>">
                  <trix-editor input="edit-content-<?= $p['id'] ?>" placeholder="Edit your page content here…"></trix-editor>
                </div>
                <div style="display:flex;gap:var(--space-3);align-items:center;">
                  <button type="submit" class="btn btn-sm btn-primary">Save changes</button>
                  <a href="/<?= e($p['slug']) ?>" target="_blank" class="btn btn-sm btn-outline" style="text-decoration:none;">Preview page</a>
                </div>
              </form>
              <form method="post" style="margin-top:var(--space-3);padding-top:var(--space-3);border-top:1px solid var(--dash-border);" onsubmit="return confirm('Delete this page permanently? This cannot be undone.');">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Delete this page</button>
              </form>
            </div>
          </details>
        </span>
      </td>
    </tr>
    <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<script src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
<script>
(function() {
  document.querySelectorAll('[id^="meta-"]').forEach(function(input) {
    if (!input.id.startsWith('meta-')) return;
    var id = input.id.replace('meta-', '');
    var counter = document.getElementById('meta-counter-' + id);
    if (!counter) return;
    function update() {
      var len = input.value.length;
      counter.textContent = len + ' / 320 characters';
      counter.className = 'meta-counter';
      if (len > 280) counter.classList.add('warning');
      if (len > 310) counter.classList.add('danger');
    }
    input.addEventListener('input', update);
    update();
  });
})();
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
