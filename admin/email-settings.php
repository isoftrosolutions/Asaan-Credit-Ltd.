<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$user = current_user();

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    csrf_check();
    $host       = trim($_POST['smtp_host'] ?? 'smtp.gmail.com');
    $port       = (int)($_POST['smtp_port'] ?? 587);
    $encryption = $_POST['smtp_encryption'] ?? 'tls';
    $username   = trim($_POST['smtp_username'] ?? '');
    $password   = trim($_POST['smtp_password'] ?? '');
    $fromEmail  = trim($_POST['from_email'] ?? 'noreply@asaancapital.com');
    $fromName   = trim($_POST['from_name'] ?? 'Asaan Capital Ltd');
    $isActive   = isset($_POST['is_active']) ? 1 : 0;

    // If password is masked (unchanged), keep existing
    if ($password === '********') {
        $existing = db()->query("SELECT smtp_password FROM email_settings WHERE is_active = 1 LIMIT 1")->fetchColumn();
        $password = $existing ?: '';
    }

    // Check if row exists
    $existingId = db()->query("SELECT id FROM email_settings LIMIT 1")->fetchColumn();

    if ($existingId) {
        $stmt = db()->prepare("UPDATE email_settings SET smtp_host = ?, smtp_port = ?, smtp_encryption = ?, smtp_username = ?, smtp_password = ?, from_email = ?, from_name = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$host, $port, $encryption, $username, $password, $fromEmail, $fromName, $isActive, $existingId]);
    } else {
        $stmt = db()->prepare("INSERT INTO email_settings (smtp_host, smtp_port, smtp_encryption, smtp_username, smtp_password, from_email, from_name, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$host, $port, $encryption, $username, $password, $fromEmail, $fromName, $isActive]);
    }

    admin_log('update_email_settings', 'email_settings', $existingId ?: db()->lastInsertId(), ['is_active' => $isActive]);
    flash_set('success', 'Email settings saved.');
    redirect('/admin/email-settings');
}

// Handle test send
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'test') {
    csrf_check();
    $testEmail = trim($_POST['test_email'] ?? $user['email']);
    if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        flash_set('error', 'Invalid test email address.');
        redirect('/admin/email-settings');
    }

    ob_start();
    require __DIR__ . '/../includes/layout-admin.php';
    $shell = ob_get_clean();

    $body = '<div style="font-family:Inter,sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#fff;box-shadow:0 10px 30px rgba(0,0,0,0.02);">
        <div style="text-align:center;margin-bottom:32px;">
            <span style="font-size:28px;font-weight:900;color:#1E4866;">Asaan<span style="color:#98202A;">Capital</span></span>
        </div>
        <h2 style="color:#1E7A4D;font-size:22px;font-weight:800;text-align:center;">Test Email Successful!</h2>
        <p style="font-size:15px;line-height:1.6;color:#5A5A5A;text-align:center;">Your SMTP configuration is working correctly. This email confirms that Asaan Capital can send emails through your configured mail server.</p>
        <div style="background:#F8F8F8;padding:20px;border-radius:12px;margin:24px 0;border:1px solid #ECECEC;font-size:13px;color:#5A5A5A;">
            <p style="margin:4px 0;"><strong>Host:</strong> ' . e($_POST['smtp_host'] ?? SMTP_HOST) . '</p>
            <p style="margin:4px 0;"><strong>Port:</strong> ' . ((int)($_POST['smtp_port'] ?? SMTP_PORT)) . '</p>
            <p style="margin:4px 0;"><strong>Encryption:</strong> ' . e($_POST['smtp_encryption'] ?? SMTP_ENCRYPTION) . '</p>
            <p style="margin:4px 0;"><strong>Username:</strong> ' . e($_POST['smtp_username'] ?? SMTP_USER) . '</p>
        </div>
        <p style="font-size:13px;color:#C3C6C5;text-align:center;">Asaan Capital Ltd — Kathmandu, Nepal</p>
    </div>';

    $sent = send_mail($testEmail, 'Test Email from Asaan Capital', $body);
    if ($sent) {
        flash_set('success', 'Test email sent to ' . e($testEmail) . '. Check your inbox.');
    } else {
        flash_set('error', 'Failed to send test email. Check your SMTP settings.');
    }
    redirect('/admin/email-settings');
}

// Load existing settings
$settings = db()->query("SELECT * FROM email_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);

$pageTitle = 'Email Settings';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('Email Settings', 'Configure SMTP for sending transactional emails.');
?>
<style>
.email-settings-grid { display:grid; grid-template-columns: 1fr 1fr; gap:24px; }
@media (max-width:768px) { .email-settings-grid { grid-template-columns:1fr; } }
</style>

<div class="email-settings-grid">
  <div class="dash-panel dash-panel-pad dash-form">
    <h3 style="font-size:15px;font-weight:700;color:#1a1a1a;margin:0 0 20px;">SMTP Configuration</h3>
    <form method="post">
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="save">

      <div class="input-group">
        <label>SMTP Host</label>
        <input type="text" name="smtp_host" class="input" value="<?= e($settings['smtp_host'] ?? 'smtp.gmail.com') ?>" required>
      </div>

      <div class="input-group">
        <label>SMTP Port</label>
        <input type="number" name="smtp_port" class="input" value="<?= e($settings['smtp_port'] ?? '587') ?>" required>
      </div>

      <div class="input-group">
        <label>Encryption</label>
        <select name="smtp_encryption" class="select" required>
          <option value="tls" <?= ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS (port 587)</option>
          <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL (port 465)</option>
          <option value="none" <?= ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
        </select>
      </div>

      <div class="input-group">
        <label>SMTP Username</label>
        <input type="text" name="smtp_username" class="input" value="<?= e($settings['smtp_username'] ?? '') ?>" placeholder="your@gmail.com" required>
      </div>

      <div class="input-group">
        <label>SMTP Password</label>
        <input type="password" name="smtp_password" class="input" value="<?= $settings['smtp_password'] ? '********' : '' ?>" placeholder="<?= $settings['smtp_password'] ? 'Leave blank to keep current' : 'Gmail App Password' ?>">
        <p style="font-size:12px;color:#5A5A5A;margin:6px 0 0;">For Gmail, use an <a href="https://support.google.com/accounts/answer/185833" target="_blank" style="color:#98202A;">App Password</a> (not your regular password).</p>
      </div>

      <div style="margin:20px 0;">
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
          <input type="checkbox" name="is_active" value="1" <?= ($settings['is_active'] ?? 0) ? 'checked' : '' ?> style="width:18px;height:18px;accent-color:#98202A;">
          <span style="font-weight:600;font-size:14px;">Active — use these credentials to send emails</span>
        </label>
      </div>

      <div class="dash-form-actions" style="border-top:none;padding-top:0;">
        <button type="submit" class="btn btn-primary">Save Settings</button>
      </div>
    </form>
  </div>

  <div>
    <div class="dash-panel dash-panel-pad dash-form" style="margin-bottom:24px;">
      <h3 style="font-size:15px;font-weight:700;color:#1a1a1a;margin:0 0 20px;">From Identity</h3>
      <form method="post">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="save">

        <div class="input-group">
          <label>From Email</label>
          <input type="email" name="from_email" class="input" value="<?= e($settings['from_email'] ?? 'noreply@asaancapital.com') ?>" required>
        </div>

        <div class="input-group">
          <label>From Name</label>
          <input type="text" name="from_name" class="input" value="<?= e($settings['from_name'] ?? 'Asaan Capital Ltd') ?>" required>
        </div>

        <div class="dash-form-actions" style="border-top:none;padding-top:0;">
          <button type="submit" class="btn btn-primary">Save Identity</button>
        </div>
      </form>
    </div>

    <div class="dash-panel dash-panel-pad dash-form">
      <h3 style="font-size:15px;font-weight:700;color:#1a1a1a;margin:0 0 20px;">Send Test Email</h3>
      <form method="post">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="test">

        <div class="input-group">
          <label>Test Email Address</label>
          <input type="email" name="test_email" class="input" value="<?= e($user['email'] ?? '') ?>" required>
        </div>
        <p style="font-size:12px;color:#5A5A5A;margin:-8px 0 16px;">A test email will be sent to this address using the current SMTP settings.</p>
        <div class="dash-form-actions" style="border-top:none;padding-top:0;">
          <button type="submit" class="btn btn-outline">Send Test</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
