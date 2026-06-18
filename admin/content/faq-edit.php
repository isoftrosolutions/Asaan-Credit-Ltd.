<?php
require __DIR__ . '/../../config/bootstrap.php';
require_admin();

$pageTitle = 'Edit FAQ';
require __DIR__ . '/../../includes/layout-admin.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if (!$id) {
    flash_set('error', 'No FAQ specified.');
    redirect('/admin/faqs');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'update') {
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        if ($question && $answer) {
            db()->prepare('UPDATE faqs SET question = ?, answer = ?, sort_order = ? WHERE id = ?')->execute([$question, $answer, $sortOrder, $id]);
            admin_log('edit_faq', 'faq', $id);
            flash_set('success', 'FAQ updated.');
            redirect('/admin/faqs');
        } else {
            flash_set('error', 'Question and answer are required.');
            redirect('/admin/faqs/edit?id=' . $id);
        }
    } elseif ($action === 'delete') {
        db()->prepare('DELETE FROM faqs WHERE id = ?')->execute([$id]);
        admin_log('delete_faq', 'faq', $id);
        flash_set('success', 'FAQ deleted.');
        redirect('/admin/faqs');
    }
}

$stmt = db()->prepare('SELECT * FROM faqs WHERE id = ?');
$stmt->execute([$id]);
$f = $stmt->fetch();

if (!$f) {
    flash_set('error', 'FAQ not found.');
    redirect('/admin/faqs');
}
?>
<link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<style>
  trix-editor {
    min-height: 240px;
    max-height: 400px;
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
    <h1 class="dash-pagehead-title">Edit FAQ</h1>
    <p class="dash-pagehead-sub">Editing: <strong><?= e($f['question']) ?></strong></p>
  </div>
  <div class="dash-pagehead-actions">
    <a href="/admin/faqs" class="btn btn-sm btn-outline">&larr; Back to FAQs</a>
  </div>
</div>

<div class="dash-panel dash-panel-pad" style="max-width:700px;">
  <form method="post">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" value="<?= $f['id'] ?>">

    <div class="input-group">
      <label>Question</label>
      <input type="text" name="question" class="input" value="<?= e($f['question']) ?>" required placeholder="e.g. How do I verify my account?">
    </div>

    <div class="input-group">
      <label>Answer</label>
      <input type="hidden" id="faq-answer" name="answer" value="<?= e($f['answer']) ?>">
      <trix-editor input="faq-answer"></trix-editor>
    </div>

    <div class="input-group">
      <label>Display order <span style="font-weight:400;color:var(--dash-ink-soft);">(lower numbers appear first)</span></label>
      <input type="number" name="sort_order" class="input" value="<?= $f['sort_order'] ?>" style="width:100px;">
    </div>

    <div style="display:flex;gap:var(--space-3);padding-top:var(--space-4);border-top:1px solid var(--dash-border);">
      <button type="submit" class="btn btn-primary">Save changes</button>
      <a href="/admin/faqs" class="btn btn-outline">Cancel</a>
      <span style="margin-left:auto;">
        <button type="submit" class="btn btn-sm btn-danger" form="delete-form">Delete FAQ</button>
      </span>
    </div>
  </form>

  <form id="delete-form" method="post" onsubmit="return confirm('Delete this FAQ permanently?')" style="display:none;">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" value="<?= $f['id'] ?>">
  </form>
</div>

<script src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
