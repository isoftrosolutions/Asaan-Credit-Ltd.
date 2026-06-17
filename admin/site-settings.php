<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();

  $keys = ['payment_phone', 'payment_instructions', 'site_tagline', 'premium_contact_email'];
  foreach ($keys as $key) {
    $val = trim($_POST[$key] ?? '');
    db()->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?")->execute([$val, $key]);
  }

  if (!empty($_FILES['payment_qr_code']) && $_FILES['payment_qr_code']['error'] !== UPLOAD_ERR_NO_FILE) {
    $qrDir = PUBLIC_UPLOADS_PATH . '/site';
    $file = handle_upload($_FILES['payment_qr_code'], ['image/jpeg', 'image/png', 'image/webp'], 2097152, $qrDir);
    if ($file) {
      db()->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'payment_qr_code'")->execute(['site/' . $file]);
    } else {
      flash_set('error', 'Invalid QR image. Accepted: JPG, PNG, WebP (max 2MB).');
      redirect('/admin/site-settings');
    }
  }

  if (!empty($_POST['remove_qr'])) {
    db()->prepare("UPDATE site_settings SET setting_value = '' WHERE setting_key = 'payment_qr_code'")->execute();
  }

  admin_log('update_site_settings', 'site_settings', 0, array_keys($_POST));
  flash_set('success', 'Site settings saved.');
  redirect('/admin/site-settings');
}

$rows = db()->query("SELECT setting_key, setting_value, setting_type, description FROM site_settings ORDER BY id")->fetchAll();
$settings = [];
foreach ($rows as $r) {
  $settings[$r['setting_key']] = $r;
}

$pageTitle = 'Site Settings';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('Site Settings', 'Manage global site configuration');
?>

<form method="POST" enctype="multipart/form-data">
  <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

  <div class="dash-panel" style="margin-bottom:24px;">
    <?php ui_section_header('Payment QR Code'); ?>
    <div style="padding:4px 0;">
      <?php $qrVal = $settings['payment_qr_code']['setting_value'] ?? ''; ?>
      <?php if ($qrVal): ?>
        <div style="margin-bottom:16px;">
          <p class="t-muted" style="font-size:13px;margin:0 0 8px;">Current QR Code:</p>
          <img src="<?= e(upload_url($qrVal)) ?>" alt="Payment QR" style="max-width:200px;border:1px solid var(--dash-border);border-radius:10px;display:block;margin-bottom:8px;">
          <label style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--color-error);cursor:pointer;">
            <input type="checkbox" name="remove_qr" value="1"> Remove QR code
          </label>
        </div>
      <?php endif; ?>
      <div class="input">
        <label for="payment_qr_code"><?= $qrVal ? 'Replace' : 'Upload' ?> QR Code</label>
        <input type="file" id="payment_qr_code" name="payment_qr_code" accept="image/jpeg,image/png,image/webp">
        <p class="t-muted" style="font-size:12px;margin:4px 0 0;">PNG, JPG, WebP (max 2MB). Upload the QR code image users scan to pay.</p>
      </div>
    </div>
  </div>

  <div class="dash-panel" style="margin-bottom:24px;">
    <?php ui_section_header('Payment Settings'); ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;padding:4px 0;">
      <div class="input">
        <label for="payment_phone">Payment Phone (eSewa / Khalti)</label>
        <input type="text" id="payment_phone" name="payment_phone" class="input" value="<?= e($settings['payment_phone']['setting_value'] ?? '') ?>" placeholder="e.g. 98XXXXXXXX">
      </div>
      <div class="input">
        <label for="premium_contact_email">Premium Contact Email</label>
        <input type="email" id="premium_contact_email" name="premium_contact_email" class="input" value="<?= e($settings['premium_contact_email']['setting_value'] ?? '') ?>" placeholder="admin@example.com">
      </div>
    </div>
    <div class="input" style="margin-top:16px;">
      <label for="payment_instructions">Payment Instructions</label>
      <textarea id="payment_instructions" name="payment_instructions" class="input" rows="5" style="resize:vertical;"><?= e($settings['payment_instructions']['setting_value'] ?? '') ?></textarea>
      <p class="t-muted" style="font-size:12px;margin:4px 0 0;">Instructions shown on the payment page. Line breaks are preserved.</p>
    </div>
  </div>

  <div class="dash-panel" style="margin-bottom:24px;">
    <?php ui_section_header('General Settings'); ?>
    <div class="input" style="margin-top:4px;">
      <label for="site_tagline">Site Tagline</label>
      <input type="text" id="site_tagline" name="site_tagline" class="input" value="<?= e($settings['site_tagline']['setting_value'] ?? '') ?>" placeholder="Connect. Grow. Invest.">
      <p class="t-muted" style="font-size:12px;margin:4px 0 0;">Shown on the premium upgrade page.</p>
    </div>
  </div>

  <button type="submit" class="btn btn-primary" style="padding:12px 32px;font-size:15px;">
    <i class="fas fa-save" style="margin-right:8px;"></i> Save Settings
  </button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
