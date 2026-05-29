<?php
require __DIR__ . '/../../config/bootstrap.php';
require_admin();

$pageTitle = 'Manage FAQs';
require __DIR__ . '/../../includes/layout-admin.php';

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        if ($question && $answer) {
            db()->prepare('INSERT INTO faqs (question, answer, sort_order) VALUES (?, ?, ?)')->execute([$question, $answer, $sortOrder]);
            admin_log('create_faq', 'faq', null, ['question' => $question]);
            flash_set('success', 'FAQ created.');
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        if ($id && $question && $answer) {
            db()->prepare('UPDATE faqs SET question = ?, answer = ?, sort_order = ? WHERE id = ?')->execute([$question, $answer, $sortOrder, $id]);
            admin_log('edit_faq', 'faq', $id);
            flash_set('success', 'FAQ updated.');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            db()->prepare('DELETE FROM faqs WHERE id = ?')->execute([$id]);
            admin_log('delete_faq', 'faq', $id);
            flash_set('success', 'FAQ deleted.');
        }
    } elseif ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare('UPDATE faqs SET is_active = NOT is_active WHERE id = ?')->execute([$id]);
        admin_log('toggle_faq', 'faq', $id);
        flash_set('success', 'FAQ toggled.');
    }
    redirect('/admin/faqs');
}

$stmt = db()->query('SELECT * FROM faqs ORDER BY sort_order ASC, id ASC');
$faqs = $stmt->fetchAll();
?>
<h2>Manage FAQs</h2>
<div class="card" style="max-width:600px;">
  <h4>Add New FAQ</h4>
  <form method="post">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" value="create">
    <div class="input-group">
      <label>Question</label>
      <input type="text" name="question" class="input" required>
    </div>
    <div class="input-group">
      <label>Answer</label>
      <textarea name="answer" class="input" rows="4" required style="resize:vertical;"></textarea>
    </div>
    <div class="input-group">
      <label>Sort Order</label>
      <input type="number" name="sort_order" class="input" value="0" style="width:80px;">
    </div>
    <button type="submit" class="btn btn-sm btn-primary">Create</button>
  </form>
</div>
<div class="card" style="margin-top:1rem;">
  <table style="width:100%;">
    <tr style="border-bottom:1px solid #eae8e6;">
      <th style="text-align:left;padding:8px;">Order</th>
      <th style="text-align:left;padding:8px;">Question</th>
      <th style="padding:8px;">Active</th>
      <th style="padding:8px;">Actions</th>
    </tr>
    <?php foreach ($faqs as $f): ?>
    <tr style="border-bottom:1px solid #eee;">
      <td style="padding:10px 8px;"><?= $f['sort_order'] ?></td>
      <td style="padding:10px 8px;font-weight:600;"><?= e($f['question']) ?></td>
      <td style="padding:10px 8px;"><?= $f['is_active'] ? '<span style="color:#166534;">Yes</span>' : '<span style="color:#b91c1c;">No</span>' ?></td>
      <td style="padding:10px 8px;white-space:nowrap;">
        <form method="post" style="display:inline;">
          <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="toggle">
          <input type="hidden" name="id" value="<?= $f['id'] ?>">
          <button type="submit" class="btn btn-sm btn-outline"><?= $f['is_active'] ? 'Deactivate' : 'Activate' ?></button>
        </form>
        <details style="display:inline;vertical-align:middle;">
          <summary style="font-size:0.8rem;cursor:pointer;color:#C41E3A;display:inline;margin-left:0.25rem;">Edit</summary>
          <form method="post" style="margin-top:0.25rem;display:flex;flex-direction:column;gap:0.25rem;min-width:300px;">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $f['id'] ?>">
            <input type="text" name="question" class="input" value="<?= e($f['question']) ?>" style="font-size:0.8rem;padding:4px 8px;" required>
            <textarea name="answer" class="input" style="font-size:0.8rem;padding:4px 8px;" rows="3" required><?= e($f['answer']) ?></textarea>
            <div><input type="number" name="sort_order" class="input" value="<?= $f['sort_order'] ?>" style="font-size:0.8rem;padding:4px 8px;width:60px;display:inline;"> <button type="submit" class="btn btn-sm btn-primary">Save</button></div>
          </form>
        </details>
        <form method="post" style="display:inline;">
          <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $f['id'] ?>">
          <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this FAQ?')">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
