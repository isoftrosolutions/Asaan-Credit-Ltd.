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

      $adminStmt = db()->query("SELECT id, email FROM users WHERE is_admin = 1");
      $admins = $adminStmt->fetchAll();

      $notifBody = $user['name'] . ' (' . $user['email'] . ') requested a premium upgrade. Plan: ' . $plan['label'] . ', Amount: NPR ' . number_format($plan['amount']);
      foreach ($admins as $admin) {
          $nStmt = db()->prepare("INSERT INTO notifications (user_id, type, title, body, action_url, is_read, created_at) VALUES (?, 'upgrade', 'Premium Upgrade Request', ?, '/admin/premium-verify', 0, NOW())");
          $nStmt->execute([$admin['id'], $notifBody]);
      }

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

$useStitchHeader = true;
require __DIR__ . '/../includes/header.php';
?>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
tailwind.config = {
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        "primary": "#4d060f",
        "on-primary": "#ffffff",
        "primary-container": "#6b1d22",
        "on-primary-container": "#f08484",
        "surface": "#fcf9f8",
        "surface-dim": "#dcd9d9",
        "surface-bright": "#fcf9f8",
        "surface-container-lowest": "#ffffff",
        "surface-container-low": "#f6f3f2",
        "surface-container": "#f0eded",
        "surface-container-high": "#eae7e7",
        "surface-container-highest": "#e5e2e1",
        "on-surface": "#1c1b1b",
        "on-surface-variant": "#554242",
        "outline": "#887271",
        "outline-variant": "#dbc0bf",
        "secondary": "#3b6281",
        "on-secondary": "#ffffff",
        "secondary-container": "#b1d9fd",
        "tertiary": "#00263f",
        "tertiary-container": "#003d60",
        "error": "#ba1a1a",
        "error-container": "#ffdad6",
        "gold": "#D4AF37",
        "gold-light": "#F9F6E5"
      },
      borderRadius: {
        DEFAULT: "0.25rem",
        lg: "0.5rem",
        xl: "0.75rem",
        full: "9999px"
      },
      spacing: {
        xxl: "48px",
        sm: "8px",
        xs: "4px",
        xl: "32px",
        md: "16px",
        lg: "24px",
        gutter: "24px"
      },
      fontFamily: {
        "headline": ["Montserrat"],
        "body": ["Inter"],
      },
      fontSize: {
        "headline-xl": ["40px", { lineHeight: "48px", letterSpacing: "-0.02em", fontWeight: "800" }],
        "headline-lg": ["32px", { lineHeight: "40px", letterSpacing: "-0.01em", fontWeight: "700" }],
        "headline-md": ["24px", { lineHeight: "32px", fontWeight: "600" }],
        "headline-sm": ["20px", { lineHeight: "28px", fontWeight: "600" }],
        "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }],
        "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }],
        "body-md-bold": ["16px", { lineHeight: "24px", fontWeight: "600" }],
        "body-sm": ["14px", { lineHeight: "20px", fontWeight: "400" }],
        "label-md": ["12px", { lineHeight: "16px", letterSpacing: "0.05em", fontWeight: "600" }],
      }
    },
  },
}
</script>

<style>
.gold-gradient-text {
  background: linear-gradient(135deg, #BF953F, #FCF6BA, #B38728, #FBF5B7, #AA771C);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.card-lift {
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
}
.card-lift:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 24px rgba(0,0,0,0.12);
}
.most-popular-glow {
  box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
  border: 2px solid #D4AF37;
}
@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
  100% { transform: translateY(0px); }
}
.animate-float {
  animation: float 4s ease-in-out infinite;
}
</style>

<main class="bg-surface text-on-surface font-body antialiased min-h-screen">
<div class="max-w-[1100px] mx-auto px-margin">

<?php if ($success): ?>

  <div class="flex items-center justify-center py-xxl min-h-[80vh]">
    <div class="w-full max-w-2xl bg-surface-container-lowest shadow-lg rounded-xl p-xl md:p-xxl text-center border border-outline-variant">
      <div class="mb-lg inline-flex items-center justify-center w-24 h-24 rounded-full bg-green-50 text-green-600 animate-float">
        <span class="material-symbols-outlined text-[64px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
      </div>
      <h1 class="font-headline text-headline-xl gold-gradient-text mb-md drop-shadow-sm">Payment Submitted!</h1>
      <p class="font-body text-body-lg text-on-surface-variant mb-lg">Your payment receipt has been uploaded successfully.</p>
      <div class="inline-flex items-center gap-xs px-md py-sm bg-yellow-100 text-yellow-800 rounded-full font-body text-label-md border border-yellow-200 mb-xl">
        <span class="material-symbols-outlined text-[16px]">pending</span>
        Verification Pending
      </div>
      <div class="p-md bg-surface-container-low rounded-lg border-l-4 border-secondary mb-xl">
        <p class="font-body text-body-sm text-on-surface-variant italic">Our admin team will verify your payment within 24 hours. You will receive an email notification once the process is complete.</p>
      </div>
      <a href="<?= APP_URL ?>/dashboard" class="inline-block w-full sm:w-auto px-xl py-md bg-primary-container text-on-primary font-body text-body-md-bold rounded-lg shadow-sm hover:opacity-90 active:scale-95 transition-all text-center" style="text-decoration:none;">
        Back to Dashboard
      </a>
    </div>
  </div>

<?php elseif (!empty($user['is_premium'])): ?>

  <div class="flex items-center justify-center py-xxl min-h-[80vh]">
    <div class="w-full max-w-2xl bg-surface-container-lowest shadow-lg rounded-xl p-xl md:p-xxl text-center border border-outline-variant">
      <div class="w-20 h-20 bg-primary-container/10 rounded-full flex items-center justify-center mx-auto mb-lg">
        <span class="material-symbols-outlined text-4xl text-[#D4AF37]" style="font-variation-settings: 'FILL' 1;">crown</span>
      </div>
      <h1 class="font-headline text-headline-lg text-on-surface mb-sm">You're Already Premium</h1>
      <div class="w-20 h-[4px] bg-gradient-to-r from-[#6B1D22] to-[#1E4866] rounded-full mx-auto mb-lg"></div>
      <p class="font-body text-body-md text-on-surface-variant max-w-md mx-auto mb-xl">Welcome back to your elite investment dashboard. Your current status grants you full access to exclusive market insights and premium startup pitches.</p>
      <?php
        $sub = db()->prepare("SELECT plan_label, status, created_at, expiry_date FROM premium_subscriptions WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $sub->execute([$userId]);
        $subRow = $sub->fetch();
      ?>
      <?php if ($subRow): ?>
      <div class="grid grid-cols-3 gap-md mb-xl">
        <div class="p-md bg-surface-container-low rounded-lg border border-outline-variant/30 flex flex-col items-center">
          <span class="font-body text-label-md text-on-surface-variant mb-xs">Plan</span>
          <span class="font-body text-body-md-bold text-on-surface"><?= e($subRow['plan_label']) ?></span>
        </div>
        <div class="p-md bg-surface-container-low rounded-lg border border-outline-variant/30 flex flex-col items-center">
          <span class="font-body text-label-md text-on-surface-variant mb-xs">Since</span>
          <span class="font-body text-body-md-bold text-on-surface"><?= date('M j, Y', strtotime($subRow['created_at'])) ?></span>
        </div>
        <?php if ($subRow['expiry_date']): ?>
        <div class="p-md bg-surface-container-low rounded-lg border border-outline-variant/30 flex flex-col items-center">
          <span class="font-body text-label-md text-on-surface-variant mb-xs">Expires</span>
          <span class="font-body text-body-md-bold text-on-surface"><?= date('M j, Y', strtotime($subRow['expiry_date'])) ?></span>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <a href="<?= APP_URL ?>/dashboard" class="inline-block w-full px-xl py-md bg-[#6B1D22] text-white rounded-lg font-body text-body-md-bold hover:bg-[#4d060f] transition-all active:scale-95 shadow-sm" style="text-decoration:none;">
        Go to Dashboard
      </a>
    </div>
  </div>

<?php elseif ($showForm): ?>

  <?php $plan = $plans[$selectedPlan]; ?>
  <div class="py-xxl">
    <div class="flex items-center gap-md mb-lg">
      <a href="<?= APP_URL ?>/upgrade" class="p-xs rounded-full hover:bg-surface-container transition-colors flex items-center justify-center" style="text-decoration:none;">
        <span class="material-symbols-outlined text-primary">arrow_back</span>
      </a>
      <h1 class="font-headline text-headline-md text-on-surface" style="margin:0;">Complete Payment</h1>
    </div>

    <div class="bg-surface-container-low rounded-xl p-lg flex flex-wrap items-center justify-between shadow-sm border border-outline-variant mb-lg">
      <div class="flex items-center gap-md">
        <div class="bg-primary-container p-sm rounded-lg flex items-center justify-center">
          <span class="material-symbols-outlined text-white">star_rate</span>
        </div>
        <div>
          <p class="font-body text-body-md-bold text-primary" style="margin:0;"><?= $plan['label'] ?> Plan</p>
          <p class="font-body text-body-sm text-on-surface-variant" style="margin:0;"><?= $plan['months'] ?> Months Subscription</p>
        </div>
      </div>
      <div class="text-right">
        <p class="font-body text-label-md text-on-surface-variant" style="margin:0;">TOTAL AMOUNT</p>
        <p class="font-headline text-headline-sm text-primary" style="margin:0;">NPR <?= number_format($plan['amount']) ?></p>
      </div>
    </div>

    <div class="bg-surface-container-lowest rounded-xl p-lg shadow-sm border border-outline-variant grid grid-cols-1 md:grid-cols-2 gap-xl items-center mb-lg">
      <div class="flex flex-col items-center justify-center space-y-sm">
        <div class="w-[160px] h-[160px] bg-white border-4 border-primary-container p-sm rounded-lg flex items-center justify-center shadow-md">
          <?php if ($qrCode): ?>
            <img src="<?= e(upload_url($qrCode)) ?>" alt="Payment QR" class="w-full h-full object-contain">
          <?php else: ?>
            <div class="flex flex-col items-center justify-center text-on-surface-variant">
              <span class="material-symbols-outlined text-[64px]">qrcode</span>
            </div>
          <?php endif; ?>
        </div>
        <p class="font-body text-label-md text-primary" style="margin:0;">SCAN TO PAY</p>
        <?php if ($paymentPhone): ?>
        <p class="font-body text-body-sm text-on-surface-variant" style="margin:0;">
          <span class="material-symbols-outlined text-[16px] align-middle">phone</span>
          Or send to: <strong class="text-on-surface"><?= e($paymentPhone) ?></strong>
        </p>
        <?php endif; ?>
      </div>
      <div class="space-y-md">
        <h3 class="font-headline text-headline-sm" style="margin:0;">Scan with any app</h3>
        <div class="flex gap-md">
          <div class="flex flex-col items-center gap-xs">
            <div class="w-12 h-12 bg-surface-container-high rounded-full flex items-center justify-center">
              <span class="material-symbols-outlined text-secondary">account_balance</span>
            </div>
            <span class="text-[10px] font-bold">Mobile Banking</span>
          </div>
          <div class="flex flex-col items-center gap-xs">
            <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center border border-green-200">
              <span class="font-extrabold text-green-700 text-xs">eSewa</span>
            </div>
            <span class="text-[10px] font-bold">eSewa</span>
          </div>
          <div class="flex flex-col items-center gap-xs">
            <div class="w-12 h-12 bg-purple-50 rounded-full flex items-center justify-center border border-purple-200">
              <span class="font-extrabold text-purple-700 text-xs">Khalti</span>
            </div>
            <span class="text-[10px] font-bold">Khalti</span>
          </div>
          <div class="flex flex-col items-center gap-xs">
            <div class="w-12 h-12 bg-surface-container-high rounded-full flex items-center justify-center">
              <span class="material-symbols-outlined text-secondary">account_balance</span>
            </div>
            <span class="text-[10px] font-bold">Bank App</span>
          </div>
        </div>
        <div class="bg-surface-container-low rounded-lg p-md">
          <strong class="text-body-sm">Instructions:</strong>
          <ol class="space-y-sm font-body text-body-sm text-on-surface-variant mt-sm" style="padding-left:20px;">
            <?php if ($paymentInstructions): $lines = explode("\n", $paymentInstructions); ?>
              <?php foreach ($lines as $line): $line = trim($line); if ($line): ?>
                <li><?= e(preg_replace('/^\d+\.\s*/', '', $line)) ?></li>
              <?php endif; endforeach; ?>
            <?php else: ?>
              <li>Scan QR from your preferred app</li>
              <li>Pay exactly NPR <?= number_format($plan['amount']) ?></li>
              <li>Take a screenshot of the confirmation</li>
              <li>Upload screenshot in the form below</li>
            <?php endif; ?>
          </ol>
        </div>
      </div>
    </div>

    <?php if ($error): ?>
    <div class="bg-error-container border border-error/25 text-on-error-container p-md rounded-lg mb-lg font-body text-body-sm flex items-center gap-sm">
      <span class="material-symbols-outlined text-[20px]">error</span>
      <?= e($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-surface-container-lowest rounded-xl p-lg shadow-sm border border-outline-variant space-y-xl">
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="plan" value="<?= e($selectedPlan) ?>">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
        <div class="space-y-xs">
          <label class="font-body text-label-md text-on-surface-variant">FULL NAME</label>
          <input type="text" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-md py-sm font-body text-body-md text-on-surface-variant cursor-not-allowed outline-none" value="<?= e($user['name'] ?? '') ?>" readonly disabled>
        </div>
        <div class="space-y-xs">
          <label class="font-body text-label-md text-on-surface-variant">EMAIL ADDRESS</label>
          <input type="email" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-md py-sm font-body text-body-md text-on-surface-variant cursor-not-allowed outline-none" value="<?= e($user['email'] ?? '') ?>" readonly disabled>
        </div>
        <div class="space-y-xs">
          <label class="font-body text-label-md text-on-surface-variant">PHONE NUMBER</label>
          <input type="text" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-md py-sm font-body text-body-md text-on-surface-variant cursor-not-allowed outline-none" value="<?= e($user['phone'] ?? '') ?>" readonly disabled>
        </div>
        <div class="space-y-xs">
          <label class="font-body text-label-md text-on-surface-variant">COMPANY / ORGANIZATION</label>
          <input type="text" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-md py-sm font-body text-body-md text-on-surface-variant cursor-not-allowed outline-none" value="<?= e($user['company'] ?? $user['organization'] ?? '—') ?>" readonly disabled>
        </div>
        <div class="space-y-xs">
          <label class="font-body text-label-md text-primary">TRANSACTION ID <span class="text-error">*</span></label>
          <input type="text" id="transaction_id" name="transaction_id" class="w-full border border-outline-variant focus:border-secondary focus:ring-2 focus:ring-secondary/10 rounded-lg px-md py-sm font-body text-body-md outline-none transition-all" placeholder="Enter Transaction Reference" required>
        </div>
        <div class="space-y-xs">
          <label class="font-body text-label-md text-primary">PAYMENT DATE <span class="text-error">*</span></label>
          <input type="date" id="payment_date" name="payment_date" class="w-full border border-outline-variant focus:border-secondary focus:ring-2 focus:ring-secondary/10 rounded-lg px-md py-sm font-body text-body-md outline-none transition-all" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>

      <div class="space-y-xs">
        <label class="font-body text-label-md text-primary">UPLOAD RECEIPT <span class="text-error">*</span></label>
        <div class="border-2 border-dashed border-outline-variant rounded-xl p-xl flex flex-col items-center justify-center gap-sm bg-surface-container-lowest hover:bg-surface-container-low transition-all cursor-pointer group" id="dropzone">
          <span class="material-symbols-outlined text-primary text-[48px] group-hover:scale-110 transition-transform">cloud_upload</span>
          <div class="text-center">
            <p class="font-body text-body-md-bold">Drag & drop or <span class="text-secondary underline">browse</span></p>
            <p class="font-body text-body-sm text-on-surface-variant">JPG, PNG, WebP, PDF (max 5MB)</p>
          </div>
          <input type="file" id="receipt" name="receipt" accept="image/jpeg,image/png,image/webp,application/pdf" class="hidden" required>
        </div>
        <div class="hidden mt-sm p-sm bg-surface-container rounded-lg flex items-center justify-between" id="filePreview">
          <div class="flex items-center gap-sm">
            <span class="material-symbols-outlined text-secondary">image</span>
            <span class="font-body text-body-sm text-on-surface" id="fileName">No file chosen</span>
          </div>
          <button class="text-error hover:scale-110 transition-all" id="removeFile" type="button">
            <span class="material-symbols-outlined">delete</span>
          </button>
        </div>
      </div>

      <div class="space-y-xs">
        <label class="font-body text-label-md text-on-surface-variant">ADDITIONAL NOTES (OPTIONAL)</label>
        <textarea id="notes" name="notes" class="w-full border border-outline-variant focus:border-secondary focus:ring-2 focus:ring-secondary/10 rounded-lg px-md py-sm font-body text-body-md outline-none transition-all resize-none" rows="3" placeholder="Any specific details you want to mention about this transaction..."></textarea>
      </div>

      <button type="submit" class="w-full bg-primary-container text-on-primary font-headline text-headline-sm py-lg rounded-xl shadow-md hover:brightness-110 active:scale-[0.98] transition-all flex items-center justify-center gap-md" style="border:none;cursor:pointer;">
        Submit for Verification
        <span class="material-symbols-outlined">send</span>
      </button>
    </form>
  </div>

<?php else: ?>

  <div class="text-center py-xxl">
    <h1 class="font-headline text-headline-xl gold-gradient-text mb-sm">Upgrade to Premium</h1>
    <p class="font-body text-body-lg text-on-surface-variant max-w-2xl mx-auto"><?= e($siteTagline) ?> — Unlock premium features and get better visibility for your startup or investment opportunities.</p>
  </div>

  <?php if ($error): ?>
  <div class="bg-error-container border border-error/25 text-on-error-container p-md rounded-lg mb-lg font-body text-body-sm flex items-center gap-sm">
    <span class="material-symbols-outlined text-[20px]">error</span>
    <?= e($error) ?>
  </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-lg mb-xxl">
    <?php foreach ($plans as $key => $plan): ?>
    <div class="card-lift bg-surface-container-lowest p-xl rounded-xl shadow-sm flex flex-col h-full border <?= $plan['popular'] ? 'most-popular-glow relative transform scale-105 z-10' : 'border-outline-variant' ?>">
      <?php if ($plan['popular']): ?>
      <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-gold text-white font-body text-label-md px-lg py-1 rounded-full uppercase tracking-widest shadow-md">Most Popular</div>
      <?php endif; ?>
      <div class="mb-lg <?= $plan['popular'] ? 'pt-2' : '' ?>">
        <div class="w-14 h-14 rounded-lg bg-primary-container/15 text-primary flex items-center justify-center text-2xl mb-md">
          <i class="fas <?= $plan['icon'] ?>"></i>
        </div>
        <h3 class="font-headline text-headline-sm text-on-surface mb-xs" style="margin:0 0 4px;"><?= $plan['label'] ?></h3>
        <div class="flex items-baseline gap-xs">
          <span class="font-body text-label-md text-on-surface-variant">NPR</span>
          <span class="text-[32px] font-bold text-on-surface"><?= number_format($plan['amount']) ?></span>
        </div>
        <p class="font-body text-label-md text-on-surface-variant mt-xs" style="margin:4px 0 0;"><?= $plan['months'] ?> months duration</p>
      </div>
      <div class="flex-grow space-y-md mb-xl">
        <?php foreach ($plan['features'] as $feature): ?>
        <div class="flex items-center gap-sm">
          <span class="material-symbols-outlined text-secondary text-[20px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
          <span class="font-body text-body-sm"><?= e($feature) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <div>
        <a href="?plan=<?= e($key) ?>" class="block w-full py-md rounded-lg text-center font-bold transition-all" style="text-decoration:none;<?= $plan['popular'] ? 'background:#6B1D22;color:#fff;' : 'border:2px solid #6B1D22;color:#6B1D22;' ?>">
          <i class="fas fa-arrow-right" style="margin-right:6px;"></i>
          <?= $plan['popular'] ? 'Choose Growth' : ($key === 'starter' ? 'Activate Starter' : 'Go Pro') ?>
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-xl mb-xxl">
    <div class="bg-primary-container text-white p-xl rounded-xl overflow-hidden relative group">
      <div class="absolute top-0 right-0 p-lg opacity-10 group-hover:scale-110 transition-transform">
        <span class="material-symbols-outlined text-[80px]">rocket</span>
      </div>
      <h3 class="font-headline text-headline-md mb-lg" style="margin:0 0 16px;">For Startups</h3>
      <ul class="space-y-md relative z-10" style="list-style:none;padding:0;">
        <li class="flex items-center gap-md"><span class="material-symbols-outlined text-gold">verified_user</span> <span class="font-body text-body-md">Attract potential investors</span></li>
        <li class="flex items-center gap-md"><span class="material-symbols-outlined text-gold">person_search</span> <span class="font-body text-body-md">Showcase your business professionally</span></li>
        <li class="flex items-center gap-md"><span class="material-symbols-outlined text-gold">payments</span> <span class="font-body text-body-md">Increase funding opportunities</span></li>
      </ul>
    </div>
    <div class="bg-tertiary-container text-white p-xl rounded-xl overflow-hidden relative group">
      <div class="absolute top-0 right-0 p-lg opacity-10 group-hover:scale-110 transition-transform">
        <span class="material-symbols-outlined text-[80px]">account_balance</span>
      </div>
      <h3 class="font-headline text-headline-md mb-lg" style="margin:0 0 16px;">For Investors</h3>
      <ul class="space-y-md relative z-10" style="list-style:none;padding:0;">
        <li class="flex items-center gap-md"><span class="material-symbols-outlined text-gold">filter_alt</span> <span class="font-body text-body-md">Discover verified startups</span></li>
        <li class="flex items-center gap-md"><span class="material-symbols-outlined text-gold">analytics</span> <span class="font-body text-body-md">Find new investment opportunities</span></li>
        <li class="flex items-center gap-md"><span class="material-symbols-outlined text-gold">chat</span> <span class="font-body text-body-md">Connect directly with founders</span></li>
      </ul>
    </div>
  </div>

  <div class="bg-surface-container-low rounded-xl py-xl px-lg mb-xxl">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-lg text-center">
      <div class="flex flex-col items-center gap-sm">
        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm text-primary mb-xs">
          <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">security</span>
        </div>
        <p class="font-body text-body-md-bold">Safe & Secure Payment</p>
      </div>
      <div class="flex flex-col items-center gap-sm">
        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm text-primary mb-xs">
          <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">bolt</span>
        </div>
        <p class="font-body text-body-md-bold">Instant Activation</p>
      </div>
      <div class="flex flex-col items-center gap-sm">
        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm text-primary mb-xs">
          <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">trending_up</span>
        </div>
        <p class="font-body text-body-md-bold">Grow Your Business Faster</p>
      </div>
    </div>
  </div>

  <div class="text-center py-xxl mb-xxl rounded-2xl bg-surface-container overflow-hidden relative">
    <div class="relative z-10">
      <h2 class="font-headline text-headline-lg text-on-surface mb-md">Ready to grow your business?</h2>
      <p class="font-body text-body-md text-on-surface-variant mb-lg">Join thousands of entrepreneurs and investors today.</p>
      <a href="<?= APP_URL ?>/signup" class="inline-block bg-primary-container text-white px-xxl py-md rounded-full font-headline text-headline-sm hover:scale-105 transition-transform shadow-lg" style="text-decoration:none;">Get Started Free</a>
    </div>
  </div>

<?php endif; ?>

</div>
</main>

<script>
const dropzone = document.getElementById('dropzone');
const fileInput = document.getElementById('receipt');
const filePreview = document.getElementById('filePreview');
const fileName = document.getElementById('fileName');
const removeFile = document.getElementById('removeFile');

if (dropzone && fileInput) {
  dropzone.addEventListener('click', () => fileInput.click());
  dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('border-primary-container', 'bg-surface-container-low'); });
  dropzone.addEventListener('dragleave', () => { dropzone.classList.remove('border-primary-container', 'bg-surface-container-low'); });
  dropzone.addEventListener('drop', (e) => { e.preventDefault(); dropzone.classList.remove('border-primary-container', 'bg-surface-container-low'); if (e.dataTransfer.files.length > 0) handleFile(e.dataTransfer.files[0]); });
  fileInput.addEventListener('change', (e) => { if (e.target.files.length > 0) handleFile(e.target.files[0]); });

  if (removeFile) {
    removeFile.addEventListener('click', () => {
      fileInput.value = '';
      filePreview.classList.add('hidden');
      dropzone.classList.remove('hidden');
    });
  }
}

function handleFile(file) {
  if (fileName) fileName.textContent = file.name;
  if (filePreview) filePreview.classList.remove('hidden');
  if (dropzone) dropzone.classList.add('hidden');
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
