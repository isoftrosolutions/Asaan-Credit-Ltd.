<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$user = current_user();
$error = '';
$success = '';

// Handle update single template
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');

    if ($id && $subject && $body) {
        $stmt = db()->prepare("UPDATE email_templates SET subject = ?, body = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$subject, $body, $id]);
        admin_log('update_email_template', 'email_templates', $id, []);
        flash_set('success', 'Template updated.');
    } else {
        flash_set('error', 'Subject and body are required.');
    }
    redirect('/admin/email-templates');
}

// Handle reset to default
if (isset($_GET['reset']) && isset($_GET['key'])) {
    csrf_check();
    $key = $_GET['key'];
    $templates = include __DIR__ . '/../config/email_templates.php';
    if (isset($templates[$key])) {
        $tpl = $templates[$key];
        $stmt = db()->prepare("UPDATE email_templates SET subject = ?, body = ?, updated_at = NOW() WHERE template_key = ?");
        $stmt->execute([$tpl['subject'], $tpl['body'], $key]);
        flash_set('success', 'Template reset to default.');
    }
    redirect('/admin/email-templates');
}

// Fetch all templates from DB
$tplStmt = db()->query("SELECT * FROM email_templates ORDER BY name ASC");
$templates = $tplStmt->fetchAll(PDO::FETCH_ASSOC);

// If DB is empty, seed from file
if (empty($templates)) {
    $fileTemplates = include __DIR__ . '/../config/email_templates.php';
    $insert = db()->prepare("INSERT INTO email_templates (template_key, name, subject, body, variables, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
    foreach ($fileTemplates as $key => $tpl) {
        $insert->execute([$key, $tpl['name'], $tpl['subject'], $tpl['body'], json_encode($tpl['variables'] ?? [])]);
    }
    $tplStmt = db()->query("SELECT * FROM email_templates ORDER BY name ASC");
    $templates = $tplStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Template preview data
$previewVars = [
    'user_name'         => 'John Doe',
    'verification_link' => APP_URL . '/verify-email?token=abc123',
    'reset_link'        => APP_URL . '/reset-password?token=abc123',
    'login_url'         => APP_URL . '/login',
    'role'              => 'Investor',
    'user_email'        => 'john@example.com',
    'sender_name'       => 'Ramesh Thapa',
    'sender_role'       => 'Investor',
    'listing_type'      => 'business',
    'listing_name'      => 'Enterprise Software Co.',
    'message'           => 'I am interested in learning more about your business.',
    'matched_user_name' => 'Anjali K.C.',
    'matched_user_role' => 'Entrepreneur',
    'context_type'      => 'pitch',
    'context_name'      => 'AI-powered Cold Storage',
    'responder_name'    => 'Sunita Sharma',
    'subject'           => 'Platform Update',
    'message_preview'   => 'Thank you for your interest. Let\'s schedule a call to discuss.',
    'rejection_reason'  => 'The uploaded document is blurry. Please upload a clearer version.',
];

$pageTitle = 'Email Templates';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('Email Templates', 'Manage transactional email templates sent to users.');
?>
<style>
.tpl-grid { display:grid; gap:20px; }
.tpl-card { border:1px solid var(--color-border); border-radius:12px; background:var(--color-bg); overflow:hidden; }
.tpl-card-head { padding:16px 20px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--color-border); cursor:pointer; user-select:none; }
.tpl-card-head:hover { background:var(--color-bg-soft); }
.tpl-card-head h3 { margin:0; font-size:15px; font-weight:700; color:var(--color-text-heading); }
.tpl-card-head .tpl-badge { font-size:11px; padding:2px 10px; border-radius:20px; background:var(--color-bg-soft); color:var(--color-text-muted); font-weight:600; }
.tpl-card-body { padding:20px; display:none; }
.tpl-card-body.open { display:block; }
.tpl-preview { background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:16px; margin-top:12px; max-height:400px; overflow-y:auto; font-size:13px; }
.tpl-preview iframe { width:100%; border:none; min-height:300px; }
</style>

<div class="tpl-grid">
<?php foreach ($templates as $tpl):
    $tplVars = json_decode($tpl['variables'] ?? '[]', true) ?: [];
    // Build preview
    $previewSubject = $tpl['subject'];
    $previewBody = $tpl['body'];
    foreach ($previewVars as $vk => $vv) {
        $previewSubject = str_replace('{{' . $vk . '}}', $vv, $previewSubject);
        $previewBody = str_replace('{{' . $vk . '}}', $vv, $previewBody);
    }
    // Clean any remaining unfilled placeholders
    $previewSubject = preg_replace('/\{\{\w+\}\}/', '', $previewSubject);
    $previewBody = preg_replace('/\{\{\w+\}\}/', '', $previewBody);
?>
  <div class="tpl-card">
    <div class="tpl-card-head" onclick="this.nextElementSibling.classList.toggle('open')">
      <div>
        <h3><?= e($tpl['name']) ?></h3>
        <span style="font-size:12px;color:var(--color-text-muted);"><?= e($tpl['template_key']) ?></span>
      </div>
      <div style="display:flex;align-items:center;gap:12px;">
        <span class="tpl-badge"><?= count($tplVars) ?> variables</span>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
      </div>
    </div>
    <div class="tpl-card-body">
      <form method="post">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?= $tpl['id'] ?>">

        <div class="input-group">
          <label>Subject</label>
          <input type="text" name="subject" class="input" value="<?= e($tpl['subject']) ?>" required>
        </div>

        <div class="input-group">
          <label>Body (HTML)</label>
          <textarea name="body" class="input" rows="12" style="font-family:monospace;font-size:13px;line-height:1.5;" required><?= e($tpl['body']) ?></textarea>
        </div>

        <?php if (!empty($tplVars)): ?>
        <div style="margin-bottom:16px;">
          <span style="font-size:12px;font-weight:600;color:var(--color-text-muted);">Available variables:</span>
          <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px;">
            <?php foreach ($tplVars as $var): ?>
              <code style="font-size:12px;background:var(--color-bg-soft);padding:3px 8px;border-radius:4px;border:1px solid var(--color-border);"><?= e('{{' . $var . '}}') ?></code>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <div style="display:flex;gap:10px;">
          <button type="submit" class="btn btn-primary btn-sm">Save Template</button>
          <a href="?reset=1&key=<?= e($tpl['template_key']) ?>&<?= CSRF_TOKEN_NAME ?>=<?= csrf_token() ?>" class="btn btn-sm btn-ghost" onclick="return confirm('Reset this template to its default content?')">Reset to Default</a>
          <button type="button" class="btn btn-sm btn-outline" onclick="previewTemplate(this)">Preview</button>
        </div>
      </form>

      <div class="tpl-preview" id="preview-<?= $tpl['id'] ?>" style="display:none;">
        <div style="font-size:12px;color:var(--color-text-muted);margin-bottom:8px;"><strong>Subject:</strong> <?= e($previewSubject) ?></div>
        <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;background:#fff;">
          <iframe srcdoc="<?= e($previewBody) ?>" style="width:100%;min-height:350px;border:none;"></iframe>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<script>
function previewTemplate(btn) {
  var card = btn.closest('.tpl-card');
  var preview = card.querySelector('.tpl-preview');
  if (preview.style.display === 'none') {
    preview.style.display = 'block';
    btn.textContent = 'Hide Preview';
  } else {
    preview.style.display = 'none';
    btn.textContent = 'Preview';
  }
}
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
