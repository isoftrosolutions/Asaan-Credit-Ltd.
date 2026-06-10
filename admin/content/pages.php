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

<details style="margin-bottom:var(--space-5);background:var(--dash-card);border:1px solid var(--dash-border);border-radius:var(--dash-radius-card);padding:var(--space-5);">
  <summary style="cursor:pointer;font-weight:600;font-size:0.95rem;color:var(--dash-primary);">+ Create new page</summary>
  <form method="post" style="margin-top:var(--space-4);">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" value="create">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
      <div class="input-group">
        <label>Title</label>
        <input type="text" name="title" class="input" required>
      </div>
      <div class="input-group">
        <label>Slug (URL path)</label>
        <input type="text" name="slug" class="input" placeholder="e.g. about, contact" required>
      </div>
    </div>
    <div class="input-group">
      <label>Meta description</label>
      <input type="text" name="meta_description" class="input" maxlength="320">
    </div>
    <div class="input-group">
      <label>Content (HTML)</label>
      <textarea name="content_html" class="input" rows="12" style="font-family:monospace;font-size:0.85rem;resize:vertical;"></textarea>
    </div>
    <button type="submit" class="btn btn-sm btn-primary">Create Page</button>
  </form>
</details>

<?php if (empty($pages)): ?>
<div class="dash-panel">
  <?php ui_empty_state(['icon' => 'document', 'title' => 'No pages yet', 'text' => 'Create your first page above.']); ?>
</div>
<?php else: ?>
<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>Page</th><th>Slug</th><th class="ta-center">Active</th><th>Updated</th><th class="ta-right">Actions</th>
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
            <div style="position:absolute;right:0;top:100%;z-index:10;background:var(--dash-card);border:1px solid var(--dash-border);border-radius:var(--dash-radius-card);box-shadow:var(--dash-shadow-hover);padding:var(--space-4);width:680px;max-width:90vw;margin-top:4px;">
              <form method="post">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                  <div class="input-group">
                    <label>Title</label>
                    <input type="text" name="title" class="input" value="<?= e($p['title']) ?>" required>
                  </div>
                  <div class="input-group">
                    <label>Slug</label>
                    <input type="text" name="slug" class="input" value="<?= e($p['slug']) ?>" required>
                  </div>
                </div>
                <div class="input-group">
                  <label>Meta description</label>
                  <input type="text" name="meta_description" class="input" value="<?= e($p['meta_description'] ?? '') ?>" maxlength="320">
                </div>
                <div class="input-group">
                  <label>
                    <input type="checkbox" name="is_active" value="1" <?= $p['is_active'] ? 'checked' : '' ?>>
                    Active
                  </label>
                </div>
                <div class="input-group">
                  <label>Content (HTML)</label>
                  <textarea name="content_html" class="input" rows="14" style="font-family:monospace;font-size:0.85rem;resize:vertical;"><?= e($p['content_html']) ?></textarea>
                </div>
                <div style="display:flex;gap:var(--space-3);">
                  <button type="submit" class="btn btn-sm btn-primary">Save</button>
                  <span style="margin-left:auto;">
                    <a href="/<?= e($p['slug']) ?>" target="_blank" class="btn btn-sm btn-outline" style="text-decoration:none;">View page</a>
                  </span>
                </div>
              </form>
              <form method="post" style="margin-top:var(--space-3);padding-top:var(--space-3);border-top:1px solid var(--dash-border);" onsubmit="return confirm('Delete this page permanently?');">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
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
<?php require __DIR__ . '/../../includes/footer.php'; ?>
