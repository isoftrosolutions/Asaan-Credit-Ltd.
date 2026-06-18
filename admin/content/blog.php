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

$pageTitle = 'Manage Blog';
require __DIR__ . '/../../includes/layout-admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'edit') {
        $id      = (int)($_POST['id'] ?? 0);
        $title   = trim($_POST['title'] ?? '');
        $slug    = trim($_POST['slug'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $body    = trim($_POST['body'] ?? '');
        $author  = trim($_POST['author'] ?? '') ?: 'Asaan Capital';
        $status  = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        $slug    = blog_slugify($slug !== '' ? $slug : $title);

        if ($title && $body) {
            if ($action === 'create') {
                $slug = blog_unique_slug($slug);
                $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
                db()->prepare('INSERT INTO blog_posts (title, slug, excerpt, body, author, status, published_at, created_at, updated_at) VALUES (?,?,?,?,?,?,?,NOW(),NOW())')
                    ->execute([$title, $slug, $excerpt, $body, $author, $status, $publishedAt]);
                admin_log('create_blog_post', 'blog_post', (int)db()->lastInsertId(), ['title' => $title]);
                flash_set('success', 'Post created.');
            } else {
                $slug = blog_unique_slug($slug, $id);
                $cur = db()->prepare('SELECT published_at FROM blog_posts WHERE id = ?');
                $cur->execute([$id]);
                $existingPub = $cur->fetchColumn();
                $publishedAt = $existingPub ?: ($status === 'published' ? date('Y-m-d H:i:s') : null);
                db()->prepare('UPDATE blog_posts SET title=?, slug=?, excerpt=?, body=?, author=?, status=?, published_at=?, updated_at=NOW() WHERE id=?')
                    ->execute([$title, $slug, $excerpt, $body, $author, $status, $publishedAt, $id]);
                admin_log('edit_blog_post', 'blog_post', $id);
                flash_set('success', 'Post updated.');
            }
        } else {
            flash_set('error', 'Title and body are required.');
        }
    } elseif ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $cur = db()->prepare('SELECT status, published_at FROM blog_posts WHERE id = ?');
            $cur->execute([$id]);
            $row = $cur->fetch();
            if ($row) {
                $new = $row['status'] === 'published' ? 'draft' : 'published';
                $pub = $row['published_at'] ?: ($new === 'published' ? date('Y-m-d H:i:s') : null);
                db()->prepare('UPDATE blog_posts SET status=?, published_at=?, updated_at=NOW() WHERE id=?')->execute([$new, $pub, $id]);
                admin_log('toggle_blog_post', 'blog_post', $id);
                flash_set('success', 'Post ' . ($new === 'published' ? 'published' : 'set to draft') . '.');
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            db()->prepare('DELETE FROM blog_posts WHERE id = ?')->execute([$id]);
            admin_log('delete_blog_post', 'blog_post', $id);
            flash_set('success', 'Post deleted.');
        }
    }
    redirect('/admin/blog');
}

$posts = db()->query('SELECT * FROM blog_posts ORDER BY COALESCE(published_at, created_at) DESC, id DESC')->fetchAll();
?>
<link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<style>
  trix-editor {
    min-height: 240px;
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
    <h1 class="dash-pagehead-title">Manage Blog</h1>
    <p class="dash-pagehead-sub"><strong><?= count($posts) ?></strong> posts</p>
  </div>
</div>

<div class="dash-panel dash-panel-pad" style="margin-bottom:var(--space-5);">
  <details>
    <summary style="cursor:pointer;font-weight:600;font-size:0.95rem;color:var(--dash-primary);padding:4px 0;">+ Write new post</summary>
    <form method="post" style="margin-top:var(--space-4);">
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="create">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="input-group">
          <label>Post title</label>
          <input type="text" name="title" class="input" required placeholder="e.g. How to Value Your Business">
        </div>
        <div class="input-group">
          <label>URL slug <span style="font-weight:400;color:var(--dash-ink-soft);">(auto-generated from title if left blank)</span></label>
          <input type="text" name="slug" class="input" placeholder="leave blank to auto-generate">
        </div>
      </div>
      <div class="input-group">
        <label>Excerpt <span style="font-weight:400;color:var(--dash-ink-soft);">(short summary shown on blog listing cards)</span></label>
        <textarea name="excerpt" class="input" rows="2" style="resize:vertical;" placeholder="A brief preview of what this post is about…"></textarea>
      </div>
      <div class="input-group">
        <label>Post body</label>
        <input type="hidden" id="create-body" name="body">
        <trix-editor input="create-body" placeholder="Start writing your blog post here…"></trix-editor>
      </div>
      <div style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;">
        <div class="input-group" style="flex:1;min-width:160px;">
          <label>Author</label>
          <input type="text" name="author" class="input" value="Asaan Capital">
        </div>
        <div class="input-group" style="flex:1;min-width:160px;">
          <label>Status</label>
          <select name="status" class="input">
            <option value="draft">Draft</option>
            <option value="published">Published</option>
          </select>
        </div>
        <div class="input-group">
          <button type="submit" class="btn btn-primary">Create post</button>
        </div>
      </div>
    </form>
  </details>
</div>

<?php if (empty($posts)): ?>
<div class="dash-panel">
  <?php ui_empty_state(['icon' => 'document', 'title' => 'No blog posts yet', 'text' => 'Write your first post using the form above.']); ?>
</div>
<?php else: ?>
<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>Title</th><th class="ta-center">Status</th><th class="ta-center">Published</th><th class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
    <?php foreach ($posts as $p): ?>
    <tr>
      <td>
        <span class="t-strong"><?= e($p['title']) ?></span>
        <div class="t-muted">/blog/<?= e($p['slug']) ?></div>
      </td>
      <td class="ta-center">
        <span class="dash-pill <?= $p['status'] === 'published' ? 'published' : 'draft' ?>"><?= $p['status'] === 'published' ? 'Published' : 'Draft' ?></span>
      </td>
      <td class="t-muted ta-center"><?= $p['published_at'] ? e(date('M j, Y', strtotime($p['published_at']))) : '—' ?></td>
      <td class="ta-right">
        <span class="dash-table-actions">
          <form method="post" style="display:inline;">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button type="submit" class="btn btn-sm btn-outline"><?= $p['status'] === 'published' ? 'Unpublish' : 'Publish' ?></button>
          </form>
          <a href="/admin/blog/edit?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline" style="text-decoration:none;">Edit</a>
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
<?php require __DIR__ . '/../../includes/footer.php'; ?>
