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
                // Set published_at the first time a post goes live.
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
<h2>Manage Blog</h2>
<div class="card" style="max-width:680px;">
  <h4>Add New Post</h4>
  <form method="post">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" value="create">
    <div class="input-group">
      <label>Title</label>
      <input type="text" name="title" class="input" required>
    </div>
    <div class="input-group">
      <label>Slug <span style="font-weight:400;color:var(--color-text-muted);">(optional — auto-generated from title)</span></label>
      <input type="text" name="slug" class="input" placeholder="my-post-title">
    </div>
    <div class="input-group">
      <label>Excerpt <span style="font-weight:400;color:var(--color-text-muted);">(short summary for cards)</span></label>
      <textarea name="excerpt" class="input" rows="2" style="resize:vertical;"></textarea>
    </div>
    <div class="input-group">
      <label>Body <span style="font-weight:400;color:var(--color-text-muted);">(plain text; blank line = new paragraph)</span></label>
      <textarea name="body" class="input" rows="8" required style="resize:vertical;"></textarea>
    </div>
    <div style="display:flex;gap:1rem;flex-wrap:wrap;">
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
    </div>
    <button type="submit" class="btn btn-sm btn-primary">Create</button>
  </form>
</div>

<div class="card" style="margin-top:1rem;">
  <table style="width:100%;">
    <tr style="border-bottom:1px solid var(--color-border);">
      <th style="text-align:left;padding:8px;">Title</th>
      <th style="padding:8px;">Status</th>
      <th style="padding:8px;">Published</th>
      <th style="padding:8px;">Actions</th>
    </tr>
    <?php foreach ($posts as $p): ?>
    <tr style="border-bottom:1px solid var(--color-border);">
      <td style="padding:10px 8px;font-weight:600;">
        <?= e($p['title']) ?>
        <div style="font-weight:400;font-size:0.75rem;color:var(--color-text-muted);">/blog/<?= e($p['slug']) ?></div>
      </td>
      <td style="padding:10px 8px;text-align:center;"><?= $p['status'] === 'published' ? '<span style="color:var(--color-success);">Published</span>' : '<span style="color:var(--color-text-muted);">Draft</span>' ?></td>
      <td style="padding:10px 8px;text-align:center;font-size:0.8rem;color:var(--color-text-muted);"><?= $p['published_at'] ? e(date('M j, Y', strtotime($p['published_at']))) : '—' ?></td>
      <td style="padding:10px 8px;white-space:nowrap;">
        <form method="post" style="display:inline;">
          <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="toggle">
          <input type="hidden" name="id" value="<?= $p['id'] ?>">
          <button type="submit" class="btn btn-sm btn-outline"><?= $p['status'] === 'published' ? 'Unpublish' : 'Publish' ?></button>
        </form>
        <details style="display:inline;vertical-align:middle;">
          <summary style="font-size:0.8rem;cursor:pointer;color:var(--color-primary-vivid);display:inline;margin-left:0.25rem;">Edit</summary>
          <form method="post" style="margin-top:0.5rem;display:flex;flex-direction:column;gap:0.4rem;min-width:340px;">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <input type="text" name="title" class="input" value="<?= e($p['title']) ?>" style="font-size:0.85rem;" required>
            <input type="text" name="slug" class="input" value="<?= e($p['slug']) ?>" style="font-size:0.85rem;">
            <textarea name="excerpt" class="input" rows="2" style="font-size:0.85rem;"><?= e($p['excerpt']) ?></textarea>
            <textarea name="body" class="input" rows="6" style="font-size:0.85rem;" required><?= e($p['body']) ?></textarea>
            <div style="display:flex;gap:0.5rem;">
              <input type="text" name="author" class="input" value="<?= e($p['author']) ?>" style="font-size:0.85rem;flex:1;">
              <select name="status" class="input" style="font-size:0.85rem;width:130px;">
                <option value="draft"<?= $p['status'] === 'draft' ? ' selected' : '' ?>>Draft</option>
                <option value="published"<?= $p['status'] === 'published' ? ' selected' : '' ?>>Published</option>
              </select>
            </div>
            <button type="submit" class="btn btn-sm btn-primary">Save</button>
          </form>
        </details>
        <form method="post" style="display:inline;">
          <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $p['id'] ?>">
          <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this post?')">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
