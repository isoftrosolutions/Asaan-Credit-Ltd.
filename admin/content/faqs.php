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
<link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<style>
  trix-editor {
    min-height: 180px;
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
  .faq-edit-panel {
    background: var(--dash-card);
    border: 1px solid var(--dash-border);
    border-radius: var(--dash-radius-card);
    box-shadow: var(--dash-shadow-hover);
    padding: var(--space-4);
    min-width: 380px;
    max-width: 90vw;
    margin-top: 6px;
  }
</style>

<div class="dash-pagehead">
  <div class="dash-pagehead-text">
    <h1 class="dash-pagehead-title">Manage FAQs</h1>
    <p class="dash-pagehead-sub"><strong><?= count($faqs) ?></strong> questions</p>
  </div>
</div>

<div class="dash-panel dash-panel-pad" style="margin-bottom:var(--space-5);">
  <details>
    <summary style="cursor:pointer;font-weight:600;font-size:0.95rem;color:var(--dash-primary);padding:4px 0;">+ Add new FAQ</summary>
    <form method="post" style="margin-top:var(--space-4);">
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="create">
      <div class="input-group">
        <label>Question</label>
        <input type="text" name="question" class="input" required placeholder="e.g. How do I verify my account?">
      </div>
      <div class="input-group">
        <label>Answer</label>
        <input type="hidden" id="create-answer" name="answer">
        <trix-editor input="create-answer" placeholder="Write the answer here…"></trix-editor>
      </div>
      <div class="input-group">
        <label>Display order <span style="font-weight:400;color:var(--dash-ink-soft);">(lower numbers appear first)</span></label>
        <input type="number" name="sort_order" class="input" value="0" style="width:100px;">
      </div>
      <button type="submit" class="btn btn-sm btn-primary">Create FAQ</button>
    </form>
  </details>
</div>

<?php if (empty($faqs)): ?>
<div class="dash-panel">
  <?php ui_empty_state(['icon' => 'bell', 'title' => 'No FAQs yet', 'text' => 'Add common questions and answers using the form above.']); ?>
</div>
<?php else: ?>
<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th style="width:50px;">Order</th><th>Question</th><th style="width:80px;" class="ta-center">Active</th><th style="width:200px;" class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
    <?php foreach ($faqs as $f): ?>
    <tr>
      <td class="t-muted ta-center"><?= $f['sort_order'] ?></td>
      <td><span class="t-strong"><?= e($f['question']) ?></span></td>
      <td class="ta-center">
        <span class="dash-pill <?= $f['is_active'] ? 'published' : 'draft' ?>"><?= $f['is_active'] ? 'Yes' : 'No' ?></span>
      </td>
      <td class="ta-right">
        <span class="dash-table-actions">
          <form method="post" style="display:inline;">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= $f['id'] ?>">
            <button type="submit" class="btn btn-sm btn-outline"><?= $f['is_active'] ? 'Deactivate' : 'Activate' ?></button>
          </form>
          <details style="display:inline-block;position:relative;">
            <summary class="btn btn-sm btn-outline" style="cursor:pointer;display:inline-flex;">Edit</summary>
            <div class="faq-edit-panel" style="position:absolute;right:0;top:100%;z-index:10;">
              <form method="post" style="display:flex;flex-direction:column;gap:var(--space-3);">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                <div class="input-group" style="margin:0;">
                  <label style="font-size:0.82rem;">Question</label>
                  <input type="text" name="question" class="input" value="<?= e($f['question']) ?>" style="font-size:0.85rem;" required>
                </div>
                <div class="input-group" style="margin:0;">
                  <label style="font-size:0.82rem;">Answer</label>
                  <input type="hidden" id="edit-answer-<?= $f['id'] ?>" name="answer" value="<?= e($f['answer']) ?>">
                  <trix-editor input="edit-answer-<?= $f['id'] ?>" style="min-height:140px;"></trix-editor>
                </div>
                <div style="display:flex;gap:var(--space-3);align-items:flex-end;">
                  <div class="input-group" style="margin:0;">
                    <label style="font-size:0.82rem;">Order</label>
                    <input type="number" name="sort_order" class="input" value="<?= $f['sort_order'] ?>" style="font-size:0.85rem;width:70px;">
                  </div>
                  <button type="submit" class="btn btn-sm btn-primary">Save</button>
                </div>
              </form>
              <form method="post" style="margin-top:var(--space-3);padding-top:var(--space-3);border-top:1px solid var(--dash-border);">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this FAQ permanently?')">Delete</button>
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
<?php require __DIR__ . '/../../includes/footer.php'; ?>
