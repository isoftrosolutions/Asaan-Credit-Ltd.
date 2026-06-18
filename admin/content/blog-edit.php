<?php
require __DIR__ . '/../../config/bootstrap.php';
require_admin();

function blog_slugify(string $s): string {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    $s = trim($s, '-');
    return $s !== '' ? $s : 'post';
}

function blog_unique_slug(string $slug, ?int $ignoreId = null): string {
    $base = $slug; $i = 1;
    while (true) {
        $sql = 'SELECT COUNT(*) FROM blog_posts WHERE slug = ?' . ($ignoreId ? ' AND id <> ?' : '');
        $stmt = db()->prepare($sql);
        $stmt->execute($ignoreId ? [$slug, $ignoreId] : [$slug]);
        if ((int)$stmt->fetchColumn() === 0) return $slug;
        $slug = $base . '-' . (++$i);
    }
}

$pageTitle = 'Edit Blog Post';
require __DIR__ . '/../../includes/layout-admin.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if (!$id) {
    flash_set('error', 'No post specified.');
    redirect('/admin/blog');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $title   = trim($_POST['title'] ?? '');
    $slug    = trim($_POST['slug'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $body    = trim($_POST['body'] ?? '');
    $author  = trim($_POST['author'] ?? '') ?: 'Asaan Capital';
    $status  = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
    $slug    = blog_slugify($slug !== '' ? $slug : $title);

    if ($title && $body) {
        $slug = blog_unique_slug($slug, $id);
        $cur = db()->prepare('SELECT published_at FROM blog_posts WHERE id = ?');
        $cur->execute([$id]);
        $existingPub = $cur->fetchColumn();
        $publishedAt = $existingPub ?: ($status === 'published' ? date('Y-m-d H:i:s') : null);
        db()->prepare('UPDATE blog_posts SET title=?, slug=?, excerpt=?, body=?, author=?, status=?, published_at=?, updated_at=NOW() WHERE id=?')
            ->execute([$title, $slug, $excerpt, $body, $author, $status, $publishedAt, $id]);
        admin_log('edit_blog_post', 'blog_post', $id);
        flash_set('success', 'Post updated.');
        redirect('/admin/blog');
    } else {
        flash_set('error', 'Title and body are required.');
        redirect('/admin/blog/edit?id=' . $id);
    }
}

$stmt = db()->prepare('SELECT * FROM blog_posts WHERE id = ?');
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    flash_set('error', 'Post not found.');
    redirect('/admin/blog');
}
?>
<link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<style>
  trix-editor {
    min-height: 300px;
    max-height: 500px;
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
</style>

<div class="dash-pagehead">
  <div class="dash-pagehead-text">
    <h1 class="dash-pagehead-title">Edit Blog Post</h1>
    <p class="dash-pagehead-sub">Editing: <strong><?= e($p['title']) ?></strong></p>
  </div>
  <div class="dash-pagehead-actions">
    <a href="/admin/blog" class="btn btn-sm btn-outline">&larr; Back to posts</a>
  </div>
</div>

<div class="dash-panel dash-panel-pad" style="max-width:860px;">
  <form method="post">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
    <input type="hidden" name="id" value="<?= $p['id'] ?>">

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
      <div class="input-group">
        <label>Post title</label>
        <input type="text" name="title" class="input" value="<?= e($p['title']) ?>" required>
      </div>
      <div class="input-group">
        <label>URL slug <span style="font-weight:400;color:var(--dash-ink-soft);">(auto-generated if blank)</span></label>
        <input type="text" name="slug" class="input" value="<?= e($p['slug']) ?>">
      </div>
    </div>

    <div class="input-group">
      <label>Excerpt <span style="font-weight:400;color:var(--dash-ink-soft);">(short summary shown on blog listing cards)</span></label>
      <textarea name="excerpt" class="input" rows="2" style="resize:vertical;"><?= e($p['excerpt']) ?></textarea>
    </div>

    <div class="input-group">
      <label>Body</label>
      <input type="hidden" id="post-body" name="body" value="<?= e($p['body']) ?>">
      <trix-editor input="post-body"></trix-editor>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:var(--space-4);align-items:end;">
      <div class="input-group">
        <label>Author</label>
        <input type="text" name="author" class="input" value="<?= e($p['author']) ?>">
      </div>
      <div class="input-group">
        <label>Status</label>
        <select name="status" class="input">
          <option value="draft"<?= $p['status'] === 'draft' ? ' selected' : '' ?>>Draft</option>
          <option value="published"<?= $p['status'] === 'published' ? ' selected' : '' ?>>Published</option>
        </select>
      </div>
      <div class="input-group" style="display:flex;gap:var(--space-3);padding-top:22px;">
        <button type="submit" class="btn btn-primary">Save changes</button>
        <a href="/admin/blog" class="btn btn-outline">Cancel</a>
      </div>
    </div>
  </form>

  <form method="post" style="margin-top:var(--space-5);padding-top:var(--space-5);border-top:1px solid var(--dash-border);">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
    <input type="hidden" name="id" value="<?= $p['id'] ?>">
    <input type="hidden" name="action" value="delete">
    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this post permanently?')">Delete post</button>
  </form>
</div>

<script src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
<script>
document.addEventListener('trix-attachment-add', function(event) {
  var attachment = event.attachment;
  if (!attachment.file) return;

  var form = new FormData();
  form.append('file', attachment.file);
  form.append('<?= CSRF_TOKEN_NAME ?>', '<?= csrf_token() ?>');

  fetch('<?= APP_URL ?>/admin/blog/image-upload', {
    method: 'POST',
    body: form,
    credentials: 'same-origin'
  })
    .then(function(response) {
      if (!response.ok) throw new Error('Upload failed');
      return response.json();
    })
    .then(function(data) {
      attachment.setAttributes({ url: data.url, href: data.url });
    })
    .catch(function() {
      attachment.remove();
      alert('Image upload failed. Please use JPG, PNG, or WebP under 3MB.');
    });
});
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
