<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

$user = current_user();
$userId = (int)$user['id'];

$pageTitle = 'Upgrade to Premium — ' . APP_NAME;
$pageDescription = 'Unlock premium features on Asaan Capital.';

$submitted = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $message = mb_substr(trim($_POST['message'] ?? ''), 0, 2000);

    try {
        $admins = db()->query('SELECT id, name, email FROM users WHERE is_admin = 1')->fetchAll();

        foreach ($admins as $admin) {
            $stmt = db()->prepare('INSERT INTO notifications (user_id, type, title, body, action_url, is_read, created_at) VALUES (?, "upgrade", ?, ?, ?, 0, NOW())');
            $stmt->execute([
                (int)$admin['id'],
                'Premium Upgrade Request',
                $user['name'] . ' (' . $user['email'] . ') requested a premium upgrade.' . ($message ? ' Message: ' . $message : ''),
                '/admin/users'
            ]);

            $mailBody = '<div style="font-family:sans-serif;max-width:600px;margin:20px auto;padding:32px;border:1px solid #eef2f6;border-radius:16px;">
                <h2 style="color:#6B1D22;margin:0 0 16px;">Premium Upgrade Request</h2>
                <table style="width:100%;border-collapse:collapse;margin-bottom:16px;">
                    <tr><td style="padding:8px 12px;border-bottom:1px solid #eee;font-weight:600;color:#555;">Name</td><td style="padding:8px 12px;border-bottom:1px solid #eee;">' . e($user['name']) . '</td></tr>
                    <tr><td style="padding:8px 12px;border-bottom:1px solid #eee;font-weight:600;color:#555;">Email</td><td style="padding:8px 12px;border-bottom:1px solid #eee;">' . e($user['email']) . '</td></tr>
                    <tr><td style="padding:8px 12px;border-bottom:1px solid #eee;font-weight:600;color:#555;">Phone</td><td style="padding:8px 12px;border-bottom:1px solid #eee;">' . e($user['phone'] ?? '—') . '</td></tr>
                    <tr><td style="padding:8px 12px;border-bottom:1px solid #eee;font-weight:600;color:#555;">Role</td><td style="padding:8px 12px;border-bottom:1px solid #eee;">' . e(ucfirst(str_replace('_', ' ', $user['role'] ?? ''))) . '</td></tr>
                    <tr><td style="padding:8px 12px;border-bottom:1px solid #eee;font-weight:600;color:#555;">Current Premium</td><td style="padding:8px 12px;border-bottom:1px solid #eee;">' . (!empty($user['is_premium']) ? 'Yes' : 'No') . '</td></tr>
                </table>
                ' . ($message ? '<p style="color:#333;"><strong>Message:</strong><br>' . nl2br(e($message)) . '</p>' : '') . '
                <p style="color:#888;font-size:13px;margin-top:20px;">Sent from Asaan Capital upgrade request page.</p>
            </div>';
            $sent = EmailService::getInstance()->sendCustomEmail(
                $admin['email'],
                'Premium Upgrade Request — ' . $user['name'],
                $mailBody
            );
        }

        $submitted = true;
    } catch (\Throwable $e) {
        $error = 'Something went wrong. Please try again.';
        if (DEBUG_MODE) error_log('upgrade request error: ' . $e->getMessage());
    }
}

require __DIR__ . '/../includes/header.php';
?>
<main class="pub-page">
<div class="pub-wrap-narrow" style="padding-top:var(--space-6);padding-bottom:var(--space-8);">

  <div class="breadcrumbs pub-text" style="margin-bottom:var(--space-5);">
    <a href="<?= APP_URL ?>/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a>
    <span style="margin:0 0.5rem;">/</span>
    <span>Upgrade to Premium</span>
  </div>

  <?php if ($submitted): ?>
  <div class="dash-panel" style="text-align:center;padding:var(--space-8);">
    <i class="fas fa-check-circle" style="font-size:48px;color:var(--dash-success);margin-bottom:16px;display:block;"></i>
    <h2 style="margin:0 0 8px;">Request Sent</h2>
    <p style="color:var(--dash-ink-soft);margin:0 0 24px;">Your upgrade request has been sent to the admin. We'll review and get back to you shortly.</p>
    <a href="<?= APP_URL ?>/dashboard" class="btn btn-primary">Back to Dashboard</a>
  </div>

  <?php elseif (!empty($user['is_premium'])): ?>
  <div class="dash-panel" style="text-align:center;padding:var(--space-8);">
    <i class="fas fa-crown" style="font-size:48px;color:var(--color-primary);margin-bottom:16px;display:block;"></i>
    <h2 style="margin:0 0 8px;">You're Already Premium</h2>
    <p style="color:var(--dash-ink-soft);margin:0 0 24px;">You have full access to all premium features.</p>
    <a href="<?= APP_URL ?>/dashboard" class="btn btn-primary">Go to Dashboard</a>
  </div>

  <?php else: ?>

  <?php if ($error): ?>
  <div class="dash-panel" style="background:rgba(239,68,68,.1);color:var(--color-error);border-radius:var(--radius-md);padding:1rem;margin-bottom:1.5rem;"><?= e($error) ?></div>
  <?php endif; ?>

  <div class="dash-panel" style="margin-bottom:1.5rem;">
    <h2 style="margin:0 0 4px;">Upgrade to Premium</h2>
    <p style="color:var(--dash-ink-soft);margin:0 0 24px;">Unlock owner contact details, financial documents, and more.</p>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
      <div style="padding:20px;border:2px solid var(--color-primary);border-radius:12px;text-align:center;">
        <i class="fas fa-user" style="font-size:28px;color:var(--color-primary);margin-bottom:8px;display:block;"></i>
        <h3 style="margin:0 0 4px;font-size:15px;">Current</h3>
        <p style="margin:0;font-size:13px;color:var(--dash-ink-soft);">Basic access</p>
        <ul style="list-style:none;padding:0;margin:12px 0 0;font-size:12px;color:var(--dash-ink-soft);text-align:left;">
          <li style="padding:4px 0;">✓ Browse listings</li>
          <li style="padding:4px 0;">✓ Send inquiries</li>
          <li style="padding:4px 0;opacity:0.4;">✗ Owner contact details</li>
          <li style="padding:4px 0;opacity:0.4;">✗ Financial documents</li>
          <li style="padding:4px 0;opacity:0.4;">✗ PDF reports</li>
        </ul>
      </div>
      <div style="padding:20px;border:2px solid var(--dash-success);border-radius:12px;text-align:center;background:rgba(16,185,129,.04);">
        <i class="fas fa-crown" style="font-size:28px;color:var(--dash-success);margin-bottom:8px;display:block;"></i>
        <h3 style="margin:0 0 4px;font-size:15px;">Premium</h3>
        <p style="margin:0;font-size:13px;color:var(--dash-ink-soft);">Full access</p>
        <ul style="list-style:none;padding:0;margin:12px 0 0;font-size:12px;color:var(--dash-ink-soft);text-align:left;">
          <li style="padding:4px 0;">✓ Browse listings</li>
          <li style="padding:4px 0;">✓ Send inquiries</li>
          <li style="padding:4px 0;">✓ Owner contact details</li>
          <li style="padding:4px 0;">✓ Financial documents</li>
          <li style="padding:4px 0;">✓ PDF reports</li>
        </ul>
      </div>
    </div>

    <div style="padding:20px;background:var(--dash-bg-soft);border-radius:12px;margin-bottom:24px;">
      <h4 style="margin:0 0 12px;font-size:14px;">Your Information</h4>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:13px;">
        <span style="color:var(--dash-ink-soft);">Name:</span>
        <span style="font-weight:600;"><?= e($user['name'] ?? '—') ?></span>
        <span style="color:var(--dash-ink-soft);">Email:</span>
        <span style="font-weight:600;"><?= e($user['email'] ?? '—') ?></span>
        <span style="color:var(--dash-ink-soft);">Phone:</span>
        <span style="font-weight:600;"><?= e($user['phone'] ?? '—') ?></span>
        <span style="color:var(--dash-ink-soft);">Role:</span>
        <span style="font-weight:600;"><?= e(ucfirst(str_replace('_', ' ', $user['role'] ?? ''))) ?></span>
      </div>
    </div>

    <form method="POST">
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
      <div class="input" style="margin-bottom:16px;">
        <label for="upgrade-message">Message <span style="font-weight:400;color:var(--dash-ink-soft);">(optional)</span></label>
        <textarea id="upgrade-message" name="message" rows="4" placeholder="Tell us why you'd like to upgrade..." style="width:100%;padding:10px 14px;border:1px solid var(--dash-border);border-radius:8px;font-size:14px;font-family:inherit;resize:vertical;"></textarea>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;font-size:15px;">
        <i class="fas fa-paper-plane" style="margin-right:6px;"></i> Send Upgrade Request
      </button>
    </form>
  </div>

  <?php endif; ?>

</div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
