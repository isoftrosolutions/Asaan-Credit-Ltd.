<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

$user = current_user();
$userId = (int)$user['id'];

$pageTitle = 'Premium Plans — ' . APP_NAME;
$pageDescription = 'Choose a premium plan and unlock exclusive features.';

$qrCode = site_setting('payment_qr_code');
$paymentPhone = site_setting('payment_phone');
$paymentInstructions = site_setting('payment_instructions');
$siteTagline = site_setting('site_tagline', 'Connect. Grow. Invest.');

$plans = [
  'starter' => [
    'label' => 'Starter',
    'months' => 3,
    'amount' => 1500,
    'icon' => 'fa-rocket',
    'color' => '#4A6CF7',
    'popular' => false,
    'features' => [
      'Premium Startup Profile',
      'Business Details & Pitch',
      'Investor Visibility',
      'Startup Marketplace Access',
      'Basic Support',
      'Profile Highlight Badge',
    ],
  ],
  'growth' => [
    'label' => 'Growth',
    'months' => 6,
    'amount' => 3000,
    'icon' => 'fa-chart-line',
    'color' => '#F59E0B',
    'popular' => true,
    'features' => [
      'Everything in Starter',
      'Featured Listing',
      'Higher Investor Reach',
      'Priority Profile Ranking',
      'Investor Contact Access',
      'Business Analytics',
    ],
  ],
  'pro' => [
    'label' => 'Pro',
    'months' => 12,
    'amount' => 5000,
    'icon' => 'fa-crown',
    'color' => '#8B5CF6',
    'popular' => false,
    'features' => [
      'Everything in Growth',
      'Top Featured Placement',
      'Maximum Investor Exposure',
      'Unlimited Updates',
      'Advanced Insights',
      'Priority Customer Support',
    ],
  ],
];

$selectedPlan = $_GET['plan'] ?? '';
if ($selectedPlan && !isset($plans[$selectedPlan])) {
  $selectedPlan = '';
}

$error = '';
$success = false;
$showForm = $selectedPlan && isset($plans[$selectedPlan]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $planKey = $_POST['plan'] ?? '';
  $transactionId = trim($_POST['transaction_id'] ?? '');
  $paymentDate = trim($_POST['payment_date'] ?? '');
  $notes = trim($_POST['notes'] ?? '');

  if (!isset($plans[$planKey])) {
    $error = 'Invalid plan selected.';
  } elseif (!$transactionId) {
    $error = 'Transaction ID is required.';
  } elseif (!$paymentDate) {
    $error = 'Payment date is required.';
  }

  if (!$error) {
    $plan = $plans[$planKey];

    $receiptFile = null;
    if (!empty($_FILES['receipt']) && $_FILES['receipt']['error'] !== UPLOAD_ERR_NO_FILE) {
      $receiptDir = PUBLIC_UPLOADS_PATH . '/payment-receipts';
      $receiptFile = handle_upload($_FILES['receipt'], ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'], 5242880, $receiptDir);
      if (!$receiptFile && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
        $error = 'Invalid receipt file. Accepted: JPG, PNG, WebP, PDF (max 5MB).';
      }
    }

    if (!$error) {
      $stmt = db()->prepare("
        INSERT INTO premium_subscriptions
          (user_id, plan_type, plan_label, amount, duration_months, transaction_id, payment_date, receipt_file, status, notes, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
      ");
      $stmt->execute([
        $userId,
        $planKey,
        $plan['label'],
        $plan['amount'],
        $plan['months'],
        $transactionId,
        $paymentDate ?: null,
        $receiptFile,
        $notes,
      ]);

      $adminStmt = db()->query("SELECT id, email FROM users WHERE role = 'admin' OR is_admin = 1");
      $admins = $adminStmt->fetchAll();
      $mailBody = '<div style="font-family:sans-serif;max-width:600px;margin:20px auto;padding:32px;border:1px solid #eef2f6;border-radius:16px;">
        <h2 style="color:#6B1D22;margin:0 0 16px;">Premium Payment Submitted</h2>
        <p style="color:#555;font-size:15px;line-height:1.6;">
          <strong>' . e($user['name']) . '</strong> (' . e($user['email']) . ')
          has submitted a premium upgrade payment.
        </p>
        <table style="width:100%;border-collapse:collapse;margin:16px 0;font-size:14px;">
          <tr><td style="padding:8px 12px;border:1px solid #eef2f6;color:#888;">Plan</td><td style="padding:8px 12px;border:1px solid #eef2f6;font-weight:600;">' . $plan['label'] . ' — ' . $plan['months'] . ' Months</td></tr>
          <tr><td style="padding:8px 12px;border:1px solid #eef2f6;color:#888;">Amount</td><td style="padding:8px 12px;border:1px solid #eef2f6;font-weight:600;">NPR ' . number_format($plan['amount']) . '</td></tr>
          <tr><td style="padding:8px 12px;border:1px solid #eef2f6;color:#888;">Transaction ID</td><td style="padding:8px 12px;border:1px solid #eef2f6;">' . e($transactionId) . '</td></tr>
        </table>
        <a href="' . APP_URL . '/admin/premium-verify" style="display:inline-block;padding:12px 28px;background:#6B1D22;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Review Payment</a>
      </div>';
      foreach ($admins as $admin) {
        EmailService::getInstance()->sendCustomEmail($admin['email'], 'Premium Payment Submitted — ' . $user['name'], $mailBody);
      }

      $success = true;
    }
  }
}

require __DIR__ . '/../includes/header.php';
?>
<main class="pub-page">
<div class="premium-upgrade-wrap">

  <?php if ($success): ?>

  <div class="premium-step-card" style="text-align:center;">
    <div class="premium-success-icon">
      <i class="fas fa-check-circle"></i>
    </div>
    <h2 class="premium-h2 premium-grad-text" style="margin-top:0;">Payment Submitted!</h2>
    <p style="color:rgba(255,255,255,.7);font-size:16px;max-width:480px;margin:0 auto 8px;">
      Your payment receipt has been uploaded successfully.
    </p>
    <div class="premium-pending-badge">
      <span class="premium-status-dot pending"></span> Verification Pending
    </div>
    <p style="color:rgba(255,255,255,.5);font-size:14px;margin-top:16px;">
      Our admin team will verify your payment within 24 hours.
    </p>
    <a href="<?= APP_URL ?>/dashboard" class="premium-btn premium-btn-primary" style="margin-top:24px;display:inline-block;">
      <i class="fas fa-arrow-left" style="margin-right:8px;"></i> Back to Dashboard
    </a>
  </div>

  <?php elseif (!empty($user['is_premium'])): ?>

  <div class="premium-step-card" style="text-align:center;">
    <div style="font-size:56px;color:#F59E0B;margin-bottom:16px;"><i class="fas fa-crown"></i></div>
    <h2 class="premium-h2 premium-grad-text" style="margin-top:0;">You're Already Premium</h2>
    <p style="color:rgba(255,255,255,.7);font-size:15px;max-width:480px;margin:0 auto 24px;">
      You have full access to all premium features and exclusive benefits.
    </p>
    <?php
      $sub = db()->prepare("SELECT plan_label, status, created_at, expiry_date FROM premium_subscriptions WHERE user_id = ? ORDER BY id DESC LIMIT 1");
      $sub->execute([$userId]);
      $subRow = $sub->fetch();
    ?>
    <?php if ($subRow): ?>
    <div style="display:inline-flex;gap:24px;flex-wrap:wrap;justify-content:center;margin-bottom:24px;">
      <div style="background:rgba(255,255,255,.06);border-radius:12px;padding:12px 20px;text-align:center;">
        <div style="font-size:12px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;">Plan</div>
        <div style="font-weight:700;font-size:18px;color:#fff;"><?= e($subRow['plan_label']) ?></div>
      </div>
      <div style="background:rgba(255,255,255,.06);border-radius:12px;padding:12px 20px;text-align:center;">
        <div style="font-size:12px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;">Since</div>
        <div style="font-weight:700;font-size:18px;color:#fff;"><?= date('M j, Y', strtotime($subRow['created_at'])) ?></div>
      </div>
      <?php if ($subRow['expiry_date']): ?>
      <div style="background:rgba(255,255,255,.06);border-radius:12px;padding:12px 20px;text-align:center;">
        <div style="font-size:12px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;">Expires</div>
        <div style="font-weight:700;font-size:18px;color:#fff;"><?= date('M j, Y', strtotime($subRow['expiry_date'])) ?></div>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <a href="<?= APP_URL ?>/dashboard" class="premium-btn premium-btn-primary" style="display:inline-block;">
      <i class="fas fa-tachometer-alt" style="margin-right:8px;"></i> Go to Dashboard
    </a>
  </div>

  <?php elseif ($showForm): ?>

  <?php $plan = $plans[$selectedPlan]; ?>
  <div class="premium-step-card">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
      <a href="<?= APP_URL ?>/upgrade" style="color:rgba(255,255,255,.5);font-size:20px;text-decoration:none;"><i class="fas fa-arrow-left"></i></a>
      <h2 class="premium-h2" style="margin:0;">Complete Payment</h2>
    </div>

    <div class="premium-payment-summary">
      <div class="premium-summary-plan">
        <span class="premium-summary-icon" style="background:<?= $plan['color'] ?>20;color:<?= $plan['color'] ?>;">
          <i class="fas <?= $plan['icon'] ?>"></i>
        </span>
        <div>
          <div class="premium-summary-label"><?= $plan['label'] ?> Plan</div>
          <div class="premium-summary-detail"><?= $plan['months'] ?> Months Access</div>
        </div>
      </div>
      <div class="premium-summary-amount">
        <div class="premium-summary-label">Amount</div>
        <div class="premium-summary-price">NPR <?= number_format($plan['amount']) ?></div>
      </div>
    </div>

    <h3 class="premium-h3" style="margin:32px 0 16px;"><i class="fas fa-qrcode" style="margin-right:8px;"></i> Scan QR & Pay</h3>

    <div class="premium-qr-section">
      <div class="premium-qr-code">
        <?php if ($qrCode): ?>
          <img src="<?= e(upload_url($qrCode)) ?>" alt="Payment QR" class="premium-qr-image">
        <?php else: ?>
          <div class="premium-qr-placeholder">
            <i class="fas fa-qrcode"></i>
            <span>Scan to Pay</span>
          </div>
        <?php endif; ?>
      </div>
      <div class="premium-qr-info">
        <p style="margin:0 0 12px;font-weight:600;color:rgba(255,255,255,.9);">Scan with any app:</p>
        <ul class="premium-qr-apps">
          <li><i class="fas fa-mobile-alt"></i> Mobile Banking</li>
          <li><i class="fas fa-mobile-alt"></i> eSewa</li>
          <li><i class="fas fa-mobile-alt"></i> Khalti</li>
          <li><i class="fas fa-university"></i> Bank App</li>
        </ul>
        <?php if ($paymentPhone): ?>
        <p style="margin:0 0 12px;font-size:14px;color:rgba(255,255,255,.7);">
          <i class="fas fa-phone" style="margin-right:6px;color:#D4A853;"></i>
          Or send to: <strong style="color:#fff;"><?= e($paymentPhone) ?></strong>
        </p>
        <?php endif; ?>
        <div class="premium-qr-steps">
          <strong>Instructions:</strong>
          <ol>
            <?php if ($paymentInstructions): $lines = explode("\n", $paymentInstructions); ?>
              <?php foreach ($lines as $line): $line = trim($line); if ($line): ?>
                <li><?= e(preg_replace('/^\d+\.\s*/', '', $line)) ?></li>
              <?php endif; endforeach; ?>
            <?php else: ?>
              <li>Scan QR code</li>
              <li>Complete payment of <strong>NPR <?= number_format($plan['amount']) ?></strong></li>
              <li>Take a screenshot or download receipt</li>
              <li>Upload proof below</li>
            <?php endif; ?>
          </ol>
        </div>
      </div>
    </div>

    <?php if ($error): ?>
    <div class="premium-error"><?= e($error) ?></div>
    <?php endif; ?>

    <h3 class="premium-h3" style="margin:32px 0 16px;"><i class="fas fa-upload" style="margin-right:8px;"></i> Upload Payment Receipt</h3>

    <form method="POST" enctype="multipart/form-data" class="premium-form">
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="plan" value="<?= e($selectedPlan) ?>">

      <div class="premium-form-grid">
        <div class="premium-form-group">
          <label>Name</label>
          <input type="text" class="premium-input" value="<?= e($user['name'] ?? '') ?>" readonly disabled>
        </div>
        <div class="premium-form-group">
          <label>Email</label>
          <input type="email" class="premium-input" value="<?= e($user['email'] ?? '') ?>" readonly disabled>
        </div>
        <div class="premium-form-group">
          <label>Phone</label>
          <input type="text" class="premium-input" value="<?= e($user['phone'] ?? '') ?>" readonly disabled>
        </div>
        <div class="premium-form-group">
          <label>Company / Organization</label>
          <input type="text" class="premium-input" value="<?= e($user['company'] ?? $user['organization'] ?? '—') ?>" readonly disabled>
        </div>
      </div>

      <div class="premium-form-grid">
        <div class="premium-form-group">
          <label for="transaction_id">Transaction ID <span class="premium-required">*</span></label>
          <input type="text" id="transaction_id" name="transaction_id" class="premium-input" placeholder="e.g. Khalti-123456" required>
        </div>
        <div class="premium-form-group">
          <label for="payment_date">Payment Date <span class="premium-required">*</span></label>
          <input type="date" id="payment_date" name="payment_date" class="premium-input" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>

      <div class="premium-form-group">
        <label for="receipt">Upload Receipt <span class="premium-required">*</span></label>
        <div class="premium-dropzone">
          <input type="file" id="receipt" name="receipt" accept="image/jpeg,image/png,image/webp,application/pdf" required
                 onchange="document.getElementById('receipt-name').textContent = this.files[0]?.name || 'No file chosen';">
          <i class="fas fa-cloud-upload-alt"></i>
          <p>Drag & drop or <span style="color:#D4A853;text-decoration:underline;cursor:pointer;">browse</span></p>
          <p style="font-size:12px;color:rgba(255,255,255,.4);margin:4px 0 0;">JPG, PNG, WebP, PDF (max 5MB)</p>
        </div>
        <div id="receipt-name" style="margin-top:8px;font-size:13px;color:#D4A853;"></div>
      </div>

      <div class="premium-form-group">
        <label for="notes">Notes <span style="font-weight:400;color:#94A3B8;">(optional)</span></label>
        <textarea id="notes" name="notes" class="premium-input" rows="3" placeholder="Any additional information..."></textarea>
      </div>

      <button type="submit" class="premium-btn premium-btn-primary" style="width:100%;padding:14px;font-size:16px;">
        <i class="fas fa-paper-plane" style="margin-right:8px;"></i> Submit for Verification
      </button>
    </form>
  </div>

  <?php else: ?>

  <div class="premium-header">
    <h1 class="premium-h1">Upgrade to Premium</h1>
    <p class="premium-subtitle"><?= e($siteTagline) ?> — Unlock premium features and get better visibility for your startup or investment opportunities.</p>
  </div>

  <?php if ($error): ?>
  <div class="premium-error"><?= e($error) ?></div>
  <?php endif; ?>

  <div class="premium-plans">
    <?php $idx = 0; foreach ($plans as $key => $plan): $idx++; ?>
    <div class="premium-plan-card <?= $plan['popular'] ? 'premium-plan-popular' : '' ?>">
      <?php if ($plan['popular']): ?>
      <div class="premium-plan-badge">Most Popular</div>
      <?php endif; ?>
      <div class="premium-plan-header" style="--plan-color: <?= $plan['color'] ?>;">
        <div class="premium-plan-icon">
          <i class="fas <?= $plan['icon'] ?>"></i>
        </div>
        <h3 class="premium-plan-name"><?= $plan['label'] ?> Plan</h3>
        <div class="premium-plan-duration"><?= $plan['months'] ?> Months</div>
        <div class="premium-plan-price">
          <span class="premium-currency">NPR</span>
          <span class="premium-amount"><?= number_format($plan['amount']) ?></span>
        </div>
      </div>
      <ul class="premium-plan-features">
        <?php foreach ($plan['features'] as $feature): ?>
        <li><i class="fas fa-check-circle"></i> <?= e($feature) ?></li>
        <?php endforeach; ?>
      </ul>
      <div class="premium-plan-action">
        <a href="?plan=<?= e($key) ?>" class="premium-btn premium-btn-<?= $plan['popular'] ? 'primary' : 'outline' ?>">
          <i class="fas fa-arrow-right" style="margin-right:6px;"></i>
          <?= $plan['popular'] ? 'Choose Growth' : ($key === 'starter' ? 'Activate Starter' : 'Go Pro') ?>
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="premium-benefits">
    <div class="premium-benefits-col">
      <h3 class="premium-h3"><i class="fas fa-briefcase" style="margin-right:8px;color:#D4A853;"></i> For Startups</h3>
      <ul>
        <li>Attract potential investors</li>
        <li>Showcase your business professionally</li>
        <li>Increase funding opportunities</li>
      </ul>
    </div>
    <div class="premium-benefits-col">
      <h3 class="premium-h3"><i class="fas fa-handshake" style="margin-right:8px;color:#D4A853;"></i> For Investors</h3>
      <ul>
        <li>Discover verified startups</li>
        <li>Find new investment opportunities</li>
        <li>Connect directly with founders</li>
      </ul>
    </div>
  </div>

  <div class="premium-trust">
    <div class="premium-trust-item"><i class="fas fa-lock"></i> Safe & Secure Payment</div>
    <div class="premium-trust-item"><i class="fas fa-bolt"></i> Instant Activation</div>
    <div class="premium-trust-item"><i class="fas fa-chart-line"></i> Grow Your Business Faster</div>
  </div>

  <div class="premium-cta">
    <h3 class="premium-h3" style="margin:0 0 8px;">Ready to grow your business?</h3>
    <p style="color:rgba(255,255,255,.6);margin:0 0 20px;">Join thousands of entrepreneurs and investors today.</p>
    <a href="<?= APP_URL ?>/signup" class="premium-btn premium-btn-primary">Get Started Free <i class="fas fa-arrow-right" style="margin-left:8px;"></i></a>
  </div>

  <?php endif; ?>

</div>
</main>

<style>
.premium-upgrade-wrap {
  max-width: 1100px;
  margin: 0 auto;
  padding: 48px 24px 80px;
  font-family: var(--font-heading);
  color: #F1F5F9;
}
.premium-h1 {
  font-family: var(--font-heading);
  font-size: 36px; font-weight: 800;
  margin: 0 0 12px;
  background: linear-gradient(135deg, #D4A853, #F5D78E);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text; line-height: 1.2;
}
.premium-h2 {
  font-family: var(--font-heading);
  font-size: 24px; font-weight: 700;
  margin: 0 0 8px; color: #F1F5F9;
}
.premium-h2.premium-grad-text {
  background: linear-gradient(135deg, #D4A853, #F5D78E);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
}
.premium-h3 {
  font-family: var(--font-heading);
  font-size: 18px; font-weight: 600;
  margin: 0 0 12px; color: #F1F5F9;
}
.premium-subtitle { font-size: 16px; color: #94A3B8; margin: 0 0 40px; text-align: center; font-family: var(--font-body); }
.premium-header { text-align: center; margin-bottom: 48px; }

.premium-error {
  background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.25);
  color: #FCA5A5; padding: 12px 16px;
  border-radius: var(--radius-md); margin-bottom: 20px; font-size: 14px;
  font-family: var(--font-body);
}

.premium-plans {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--space-6);
  margin-bottom: 48px;
}
.premium-plan-card {
  background: #141C2E;
  border: 1px solid #1E2A45;
  border-radius: var(--radius-lg);
  overflow: hidden;
  display: flex; flex-direction: column;
  position: relative;
  transition: transform var(--motion-base) var(--ease-standard), box-shadow var(--motion-base) var(--ease-standard);
}
.premium-plan-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
.premium-plan-popular {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 1px var(--color-primary), 0 8px 32px rgba(107,29,34,.2);
}
.premium-plan-badge {
  position: absolute; top: 16px; right: 16px;
  background: var(--color-primary); color: #fff;
  font-size: 11px; font-weight: 700;
  text-transform: uppercase; letter-spacing: .5px;
  padding: 4px 12px;
  border-radius: var(--radius-pill);
}
.premium-plan-header {
  padding: 32px 24px 24px;
  text-align: center;
  border-bottom: 1px solid #1E2A45;
}
.premium-plan-icon {
  width: 56px; height: 56px;
  border-radius: var(--radius-md);
  background: color-mix(in srgb, var(--plan-color) 15%, transparent);
  color: var(--plan-color);
  display: flex; align-items: center; justify-content: center;
  font-size: 24px;
  margin: 0 auto 16px;
}
.premium-plan-name { font-size: 20px; font-weight: 700; color: #F1F5F9; margin: 0 0 4px; }
.premium-plan-duration { font-size: 14px; color: #94A3B8; margin-bottom: 16px; font-family: var(--font-body); }
.premium-plan-price { display: flex; align-items: baseline; justify-content: center; gap: 4px; }
.premium-currency { font-size: 16px; font-weight: 600; color: #94A3B8; }
.premium-amount { font-size: 40px; font-weight: 800; color: #F1F5F9; letter-spacing: -1px; }
.premium-plan-features { list-style: none; padding: 24px; margin: 0; flex: 1; font-family: var(--font-body); }
.premium-plan-features li {
  padding: 8px 0; font-size: 14px; color: #94A3B8;
  display: flex; align-items: center; gap: 10px;
}
.premium-plan-features li i { color: #D4A853; font-size: 14px; flex-shrink: 0; }
.premium-plan-action { padding: 0 24px 24px; }
.premium-btn {
  display: inline-flex; align-items: center; justify-content: center;
  padding: 12px 24px;
  border-radius: var(--radius-md);
  font-size: 15px; font-weight: 600;
  font-family: var(--font-heading);
  text-decoration: none; border: none;
  cursor: pointer;
  transition: all var(--motion-base) var(--ease-standard);
}
.premium-btn-primary {
  background: linear-gradient(135deg, var(--color-primary), var(--color-primary-vivid));
  color: #fff;
}
.premium-btn-primary:hover {
  background: linear-gradient(135deg, var(--color-primary-vivid), #7A222E);
  transform: translateY(-1px);
  box-shadow: 0 4px 16px rgba(107,29,34,.35);
}
.premium-btn-outline {
  background: transparent; border: 1px solid #1E2A45; color: #F1F5F9;
}
.premium-btn-outline:hover { border-color: var(--color-primary); color: #D4A853; }
.premium-btn-sm { padding: 8px 16px; font-size: 13px; }

.premium-benefits {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--space-6);
  margin-bottom: 40px;
}
.premium-benefits-col {
  background: #141C2E;
  border: 1px solid #1E2A45;
  border-radius: var(--radius-lg);
  padding: 28px;
}
.premium-benefits-col ul { list-style: none; padding: 0; margin: 0; }
.premium-benefits-col ul li {
  padding: 6px 0; font-size: 14px; color: #94A3B8;
  display: flex; align-items: center; gap: 8px;
  font-family: var(--font-body);
}
.premium-benefits-col ul li::before { content: '\f00c'; font-family: 'Font Awesome 5 Free'; font-weight: 900; color: #D4A853; font-size: 12px; }

.premium-trust {
  display: flex; justify-content: center; gap: var(--space-6);
  margin-bottom: 48px; flex-wrap: wrap;
}
.premium-trust-item {
  display: flex; align-items: center; gap: 8px;
  color: #94A3B8; font-size: 14px; font-family: var(--font-body);
}
.premium-trust-item i { color: #D4A853; font-size: 16px; }

.premium-cta {
  text-align: center;
  background: #141C2E;
  border: 1px solid #1E2A45;
  border-radius: var(--radius-lg);
  padding: 48px 24px;
}

.premium-step-card {
  background: #141C2E;
  border: 1px solid #1E2A45;
  border-radius: var(--radius-lg);
  padding: 40px; max-width: 720px;
  margin: 0 auto;
}
.premium-success-icon { font-size: 56px; color: #10B981; margin-bottom: 16px; }
.premium-pending-badge {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(212,168,83,.1); border: 1px solid rgba(212,168,83,.2);
  color: #D4A853;
  padding: 8px 20px;
  border-radius: var(--radius-pill);
  font-size: 14px; font-weight: 600;
  margin-top: 16px; font-family: var(--font-body);
}
.premium-status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.premium-status-dot.pending { background: #D4A853; }

.premium-payment-summary {
  display: flex; align-items: center; justify-content: space-between;
  background: rgba(255,255,255,.04);
  border: 1px solid #1E2A45;
  border-radius: var(--radius-md);
  padding: 20px 24px;
}
.premium-summary-plan { display: flex; align-items: center; gap: 14px; }
.premium-summary-icon {
  width: 44px; height: 44px;
  border-radius: var(--radius-sm);
  display: flex; align-items: center; justify-content: center;
  font-size: 20px;
}
.premium-summary-label { font-size: 14px; color: #94A3B8; }
.premium-summary-detail { font-size: 16px; font-weight: 600; color: #F1F5F9; }
.premium-summary-price { font-size: 28px; font-weight: 800; color: #F1F5F9; }

.premium-qr-section {
  display: flex; gap: var(--space-6);
  background: rgba(255,255,255,.03);
  border: 1px solid #1E2A45;
  border-radius: var(--radius-md);
  padding: 24px;
}
.premium-qr-code { flex-shrink: 0; }
.premium-qr-placeholder {
  width: 160px; height: 160px;
  background: #fff;
  border-radius: var(--radius-md);
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  color: #0B1121; font-size: 14px; font-weight: 600; gap: 8px;
}
.premium-qr-placeholder i { font-size: 64px; color: #1E2A45; }
.premium-qr-image {
  width: 160px; height: 160px;
  object-fit: contain;
  border-radius: var(--radius-md);
  background: #fff; padding: 8px;
}
.premium-qr-info { flex: 1; }
.premium-qr-apps { list-style: none; padding: 0; margin: 0 0 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.premium-qr-apps li { display: flex; align-items: center; gap: 8px; font-size: 14px; color: #94A3B8; font-family: var(--font-body); }
.premium-qr-apps li i { color: #D4A853; width: 16px; }
.premium-qr-steps {
  background: rgba(255,255,255,.04);
  border-radius: var(--radius-sm);
  padding: 16px 20px; font-size: 14px; color: #94A3B8;
  font-family: var(--font-body);
}
.premium-qr-steps strong { color: #F1F5F9; }
.premium-qr-steps ol { margin: 8px 0 0; padding-left: 20px; }
.premium-qr-steps ol li { padding: 2px 0; }

.premium-form { margin-top: 16px; font-family: var(--font-body); }
.premium-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
.premium-form-group { margin-bottom: 16px; }
.premium-form-group label {
  display: block; font-size: 13px; font-weight: 600;
  color: #F1F5F9; margin-bottom: 6px;
  text-transform: uppercase; letter-spacing: .3px;
}
.premium-required { color: #EF4444; }
.premium-input {
  width: 100%; padding: 10px 14px;
  background: rgba(255,255,255,.05);
  border: 1px solid #1E2A45;
  border-radius: var(--radius-sm);
  color: #F1F5F9; font-size: 14px;
  font-family: var(--font-body);
  outline: none;
  transition: border-color var(--motion-base) var(--ease-standard);
  box-sizing: border-box;
}
.premium-input:focus { border-color: var(--color-primary); box-shadow: 0 0 0 2px rgba(107,29,34,.25); }
.premium-input:disabled { opacity: .5; cursor: not-allowed; }
.premium-input::placeholder { color: rgba(255,255,255,.2); }
textarea.premium-input { resize: vertical; }

.premium-dropzone {
  border: 2px dashed #1E2A45;
  border-radius: var(--radius-md);
  padding: 32px; text-align: center;
  cursor: pointer;
  transition: border-color var(--motion-base) var(--ease-standard);
  position: relative;
}
.premium-dropzone:hover { border-color: var(--color-primary); }
.premium-dropzone input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.premium-dropzone i { font-size: 40px; color: #D4A853; margin-bottom: 8px; display: block; }
.premium-dropzone p { margin: 0; color: #94A3B8; font-size: 14px; font-family: var(--font-body); }

@media (max-width: 768px) {
  .premium-plans { grid-template-columns: 1fr; }
  .premium-benefits { grid-template-columns: 1fr; }
  .premium-form-grid { grid-template-columns: 1fr; }
  .premium-qr-section { flex-direction: column; align-items: center; text-align: center; }
  .premium-qr-apps { grid-template-columns: 1fr; }
  .premium-payment-summary { flex-direction: column; gap: 16px; text-align: center; }
  .premium-h1 { font-size: 28px; }
  .premium-step-card { padding: 24px; }
}
</style>

<?php require __DIR__ . '/../includes/footer.php'; ?>
