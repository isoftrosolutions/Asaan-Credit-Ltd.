<?php
require __DIR__ . '/../config/bootstrap.php';

$investorId = (int)($_GET['id'] ?? 0);
if (!$investorId) {
    http_response_code(404);
    require __DIR__ . '/../pages/404.php';
    exit;
}

$stmt = db()->prepare('
    SELECT u.*, ip.*
    FROM users u
    LEFT JOIN investor_profiles ip ON ip.user_id = u.id
    WHERE u.id = ? AND u.role = ?
');
$stmt->execute([$investorId, ROLE_INVESTOR]);
$profile = $stmt->fetch();

if (!$profile) {
    http_response_code(404);
    require __DIR__ . '/../pages/404.php';
    exit;
}

$user = current_user();
$userId = $user ? (int)$user['id'] : 0;
$ownerUserId = (int)$investorId;

$isMatch = false;
$showContact = false;
$hasInquired = false;
$viewerIsPremium = false;
$isSaved = false;

if ($userId) {
    $viewerIsPremium = !empty($user['is_premium']) || !empty($user['is_admin']) || $userId === $ownerUserId;

    $matchStmt = db()->prepare('SELECT COUNT(*) FROM matches WHERE (user_a_id = ? AND user_b_id = ?) OR (user_a_id = ? AND user_b_id = ?)');
    $matchStmt->execute([$userId, $investorId, $investorId, $userId]);
    $isMatch = (int)$matchStmt->fetchColumn() > 0;
    $showContact = $viewerIsPremium || $isMatch;

    $sStmt = db()->prepare("SELECT id FROM saved_listings WHERE user_id = ? AND listing_type = 'investor' AND listing_id = ?");
    $sStmt->execute([$userId, $investorId]);
    $isSaved = (bool)$sStmt->fetch();
}

$preferredSectors = json_decode($profile['preferred_sectors'] ?? '[]', true) ?: [];
$preferredStages = json_decode($profile['preferred_stages'] ?? '[]', true) ?: [];
$preferredGeography = json_decode($profile['preferred_geography'] ?? '[]', true) ?: [];

$initials = '';
$nameParts = explode(' ', $profile['name'] ?? 'U');
foreach ($nameParts as $part) {
    $initials .= mb_strtoupper(mb_substr($part, 0, 1));
}
$initials = mb_substr($initials, 0, 2);

$investorTypeLabels = [
    'individual' => 'Individual Investor',
    'angel' => 'Angel Investor',
    'venture_capital' => 'Venture Capital',
    'private_equity' => 'Private Equity',
    'family_office' => 'Family Office',
    'corporate' => 'Corporate Acquirer',
    'lender' => 'Lender / NBFC',
    'advisor' => 'M&A Advisor',
];
$investorTypeLabel = $investorTypeLabels[$profile['investor_type']] ?? $profile['investor_type'] ?? 'Investor';
$typeLabel = $investorTypeLabel;

$location = trim(($profile['district'] ?? '') . ', ' . ($profile['province'] ?? ''), ', ') ?: 'Nepal';

// Profile strength
$profileFields = [
    'bio' => $profile['bio'],
    'company_name' => $profile['company_name'],
    'preferred_sectors' => !empty($preferredSectors),
    'preferred_stages' => !empty($preferredStages),
    'preferred_geography' => !empty($preferredGeography),
    'ticket_min' => $profile['ticket_min'],
    'ticket_max' => $profile['ticket_max'],
    'total_capital_deployed' => $profile['total_capital_deployed'],
    'past_investments' => (int)$profile['past_investments'] > 0,
    'linkedin_url' => $profile['linkedin_url'],
    'phone' => $profile['phone'],
];
$filledCount = 0;
foreach ($profileFields as $val) { if ($val) $filledCount++; }
$strengthPercent = min(100, round(($filledCount / count($profileFields)) * 100));

// Verification check count
$vC = 0;
if ($profile['email_verified_at']) $vC++;
if ($profile['phone']) $vC++;
if ($profile['verification_status'] === 'verified') $vC++;

$pageTitle = e($profile['name'] ?? 'Investor') . ' — Investor Profile';
$pageDescription = mb_substr(strip_tags($profile['bio'] ?? ''), 0, 160);
$canonicalUrl = APP_URL . '/investor/' . $investorId;
$ogImage = $profile['profile_photo'] ? upload_url($profile['profile_photo']) : '';

require __DIR__ . '/../includes/layout-public.php';
?>

<div class="stitch-detail">

<!-- ════ Breadcrumb ════ -->
<nav class="stitch-breadcrumb">
  <a href="<?= APP_URL ?>/">Home</a>
  <span class="sep">›</span>
  <a href="<?= APP_URL ?>/browse/investors">Investors &amp; Buyers</a>
  <span class="sep">›</span>
  <span><?= e($profile['name']) ?></span>
</nav>

<!-- ════ HERO ════ -->
<section class="stitch-hero">
  <div class="stitch-hero-inner">
    <div class="stitch-hero-left">
      <div class="stitch-hero-badges">
        <span class="stitch-badge stitch-badge-sale"><?= e($typeLabel) ?></span>
        <span class="stitch-badge stitch-badge-industry"><?= e($profile['province'] ?: 'Nepal') ?></span>
      </div>

      <h1 class="stitch-hero-title"><?= e($profile['name']) ?></h1>

      <?php if ($profile['company_name']): ?>
      <div class="stitch-hero-location">
        <i class="fas fa-building" style="font-size:15px;"></i>
        <span><?= e($profile['company_name']) ?></span>
      </div>
      <?php endif; ?>

      <p class="stitch-hero-desc"><?= e(mb_substr(strip_tags($profile['bio'] ?? ''), 0, 200)) ?: 'Experienced investor looking for the right opportunity.' ?></p>

      <div class="stitch-hero-metrics">
        <div class="stitch-metric-card">
          <span class="label">Capital Deployed</span>
          <span class="value"><?= $profile['total_capital_deployed'] ? money($profile['total_capital_deployed']) : '—' ?></span>
        </div>
        <div class="stitch-metric-card">
          <span class="label">Past Investments</span>
          <span class="value"><?= (int)$profile['past_investments'] ?></span>
        </div>
        <div class="stitch-metric-card">
          <span class="label">Member Since</span>
          <span class="value"><?= $profile['created_at'] ? date('Y', strtotime($profile['created_at'])) : '—' ?></span>
        </div>
      </div>

      <div class="stitch-hero-actions">
        <button class="stitch-btn stitch-btn-primary" onclick="<?php if ($viewerIsPremium ?? false || $userId === $ownerUserId): ?>document.getElementById('interest-modal').classList.add('open')<?php else: ?>location.href='<?= APP_URL ?>/upgrade'<?php endif; ?>">
          <i class="fas fa-envelope" style="font-size:16px;"></i>
          Send Proposal
        </button>
        <button class="stitch-btn stitch-btn-secondary card-save-btn-detail <?= $isSaved ?? false ? 'saved' : '' ?>" id="saveBtn" onclick="toggleSave('investor',<?= (int)$investorId ?>,this)" data-id="<?= $investorId ?>" data-type="investor">
          <i class="fas fa-heart" style="font-size:15px;"></i>
          <span>Save</span>
        </button>
        <button class="stitch-btn stitch-btn-secondary" onclick="navigator.share? navigator.share({title:'<?= e($profile['name']) ?> - Investor Profile',url:window.location.href}) : navigator.clipboard.writeText(window.location.href)">
          <i class="fas fa-external-link-alt" style="font-size:13px;"></i>
          Share
        </button>
      </div>
    </div>

    <!-- Gallery -->
    <div class="stitch-gallery">
      <?php if ($profile['profile_photo']): ?>
      <div class="stitch-gallery-main-wrap">
        <img src="<?= upload_url($profile['profile_photo']) ?>" alt="<?= e($profile['name']) ?>" class="stitch-gallery-main">
      </div>
      <?php else: ?>
      <div class="stitch-gallery-fallback" style="background:linear-gradient(135deg, var(--color-primary), #0a1e2f);min-height:320px;">
        <div style="width:96px;height:96px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:700;color:#fff;"><?= e($initials) ?></div>
        <span style="color:rgba(255,255,255,0.5);font-size:14px;">Investor Profile</span>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ════ LAYOUT: Content + Sidebar ════ -->
<div class="stitch-layout">

  <!-- ─── Main Content ─── -->
  <div>

    <!-- Trust badges -->
    <div class="stitch-verify-row" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;">
      <span class="stitch-verif-item" style="background:rgba(30,122,77,0.1);color:var(--color-success);border:1px solid rgba(30,122,77,0.2);">
        <i class="fas fa-check-circle" style="font-size:12px;"></i> Email <?= $profile['email_verified_at'] ? 'Verified' : 'Unverified' ?>
      </span>
      <?php if ($profile['phone']): ?>
      <span class="stitch-verif-item" style="background:rgba(30,122,77,0.1);color:var(--color-success);border:1px solid rgba(30,122,77,0.2);">
        <i class="fas fa-phone" style="font-size:12px;"></i> Phone Provided
      </span>
      <?php endif; ?>
      <?php if ($profile['verification_status'] === 'verified'): ?>
      <span class="stitch-verif-item" style="background:rgba(30,122,77,0.1);color:var(--color-success);border:1px solid rgba(30,122,77,0.2);">
        <i class="fas fa-shield-alt" style="font-size:12px;"></i> Identity Verified
      </span>
      <?php endif; ?>
    </div>

    <!-- About -->
    <?php if ($profile['bio']): ?>
    <section class="stitch-section">
      <h2 class="stitch-section-title">About</h2>
      <p style="line-height:1.7;color:var(--ds-text-secondary);"><?= nl2br(e($profile['bio'])) ?></p>
    </section>
    <?php endif; ?>

    <!-- Investment Preferences -->
    <section class="stitch-section">
      <h2 class="stitch-section-title">Investment Preferences</h2>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="stitch-snapshot-item">
          <div class="icon-wrap" style="background:rgba(30,122,77,0.1);color:var(--color-success);">
            <i class="fas fa-tag" style="font-size:15px;"></i>
          </div>
          <div>
            <div class="stitch-snapshot-label">Preferred Sectors</div>
            <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:4px;">
              <?php foreach ($preferredSectors as $sector): ?>
              <span class="tag" style="background:rgba(30,122,77,0.08);color:var(--color-success);font-weight:600;font-size:12px;padding:2px 10px;border-radius:999px;"><?= e($sector) ?></span>
              <?php endforeach; ?>
              <?php if (empty($preferredSectors)): ?><span style="color:var(--ds-muted);font-size:13px;">Not specified</span><?php endif; ?>
            </div>
          </div>
        </div>
        <div class="stitch-snapshot-item">
          <div class="icon-wrap" style="background:rgba(30,72,102,0.1);color:var(--color-secondary);">
            <i class="fas fa-chart-line" style="font-size:15px;"></i>
          </div>
          <div>
            <div class="stitch-snapshot-label">Preferred Stage</div>
            <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:4px;">
              <?php foreach ($preferredStages as $stage): ?>
              <span class="tag" style="background:rgba(30,72,102,0.08);color:var(--color-secondary);font-weight:600;font-size:12px;padding:2px 10px;border-radius:999px;"><?= e($stage) ?></span>
              <?php endforeach; ?>
              <?php if (empty($preferredStages)): ?><span style="color:var(--ds-muted);font-size:13px;">Not specified</span><?php endif; ?>
            </div>
          </div>
        </div>
        <div class="stitch-snapshot-item">
          <div class="icon-wrap" style="background:rgba(199,122,18,0.1);color:var(--color-warning);">
            <i class="fas fa-map-marker-alt" style="font-size:15px;"></i>
          </div>
          <div>
            <div class="stitch-snapshot-label">Preferred Locations</div>
            <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:4px;">
              <?php foreach ($preferredGeography as $geo): ?>
              <span class="tag" style="background:rgba(199,122,18,0.08);color:var(--color-warning);font-weight:600;font-size:12px;padding:2px 10px;border-radius:999px;"><?= e($geo) ?></span>
              <?php endforeach; ?>
              <?php if (empty($preferredGeography)): ?><span style="color:var(--ds-muted);font-size:13px;">Not specified</span><?php endif; ?>
            </div>
          </div>
        </div>
        <div class="stitch-snapshot-item">
          <div class="icon-wrap" style="background:rgba(107,29,34,0.1);color:var(--color-primary);">
            <i class="fas fa-money-bill-wave" style="font-size:15px;"></i>
          </div>
          <div>
            <div class="stitch-snapshot-label">Investment Size</div>
            <div style="font-weight:600;margin-top:4px;font-size:14px;">
              <?php if ($profile['ticket_min'] || $profile['ticket_max']): ?>
                NPR <?= number_format((float)$profile['ticket_min']) ?> – <?= number_format((float)$profile['ticket_max']) ?>
              <?php else: ?>
                <span style="color:var(--ds-muted);font-size:13px;">Not specified</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Track Record -->
    <?php if ($profile['total_capital_deployed'] || (int)$profile['past_investments'] > 0): ?>
    <section class="stitch-section">
      <h2 class="stitch-section-title">Track Record</h2>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <?php if ($profile['total_capital_deployed']): ?>
        <div class="stitch-snapshot-item">
          <div class="icon-wrap" style="background:rgba(30,122,77,0.1);color:var(--color-success);">
            <i class="fas fa-coins" style="font-size:15px;"></i>
          </div>
          <div>
            <div class="stitch-snapshot-label">Total Capital Deployed</div>
            <div style="font-weight:700;font-size:16px;margin-top:2px;">NPR <?= number_format((float)$profile['total_capital_deployed']) ?></div>
          </div>
        </div>
        <?php endif; ?>
        <?php if ((int)$profile['past_investments'] > 0): ?>
        <div class="stitch-snapshot-item">
          <div class="icon-wrap" style="background:rgba(30,72,102,0.1);color:var(--color-secondary);">
            <i class="fas fa-handshake" style="font-size:15px;"></i>
          </div>
          <div>
            <div class="stitch-snapshot-label">Past Investments</div>
            <div style="font-weight:700;font-size:16px;margin-top:2px;"><?= (int)$profile['past_investments'] ?> deals</div>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <?php if ($profile['portfolio_companies']): ?>
      <div style="margin-top:16px;padding:16px;background:var(--ds-surface);border-radius:12px;border:1px solid var(--ds-border);">
        <div style="font-size:13px;font-weight:600;color:var(--ds-muted);margin-bottom:6px;">Portfolio Companies</div>
        <p style="margin:0;line-height:1.6;"><?= nl2br(e($profile['portfolio_companies'])) ?></p>
      </div>
      <?php endif; ?>
    </section>
    <?php endif; ?>

    <!-- References -->
    <?php if ($profile['references']): ?>
    <section class="stitch-section">
      <h2 class="stitch-section-title">References</h2>
      <p style="line-height:1.7;color:var(--ds-text-secondary);"><?= nl2br(e($profile['references'])) ?></p>
    </section>
    <?php endif; ?>

    <!-- LinkedIn (premium gated) -->
    <?php if ($profile['linkedin_url']): ?>
    <section class="stitch-section">
      <?php if ($showContact): ?>
      <a href="<?= e($profile['linkedin_url']) ?>" target="_blank" rel="noopener" class="stitch-btn-secondary" style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;text-decoration:none;">
        <i class="fab fa-linkedin" style="font-size:16px;"></i>
        View LinkedIn Profile
      </a>
      <?php else: ?>
      <div class="stitch-nda-card" style="text-align:center;padding:24px;">
        <i class="fab fa-linkedin" style="font-size:36px;color:var(--ds-muted);margin-bottom:8px;display:block;"></i>
        <p style="font-size:13px;color:var(--ds-text-secondary);margin:0 0 12px;">LinkedIn profile is hidden. Connect to unlock.</p>
        <a href="<?= APP_URL ?>/upgrade" class="stitch-btn-primary" style="display:inline-block;font-size:13px;padding:8px 20px;text-decoration:none;">Upgrade to Premium</a>
      </div>
      <?php endif; ?>
    </section>
    <?php endif; ?>

  </div>

  <!-- ─── Sidebar ─── -->
  <div>
    <div class="stitch-sidebar-card">
      <div style="text-align:center;padding:24px 16px 16px;">
        <?php if ($profile['profile_photo']): ?>
        <img src="<?= upload_url($profile['profile_photo']) ?>" alt="" style="width:72px;height:72px;border-radius:50%;object-fit:cover;margin-bottom:8px;">
        <?php else: ?>
        <div style="width:72px;height:72px;border-radius:50%;background:var(--color-bg-soft);display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:var(--color-text-muted);margin:0 auto 8px;">
          <i class="fas fa-user-circle"></i>
        </div>
        <?php endif; ?>
        <h4 style="margin:0 0 2px;font-size:16px;"><?= e($profile['name']) ?></h4>
        <?php if ($profile['company_name']): ?>
        <div style="font-size:13px;color:var(--ds-text-secondary);"><?= e($profile['company_name']) ?></div>
        <?php endif; ?>
      </div>

      <div class="stitch-sidebar-price">
        <span class="label">Investment Range</span>
        <span class="value"><?= $profile['ticket_min'] || $profile['ticket_max'] ? 'NPR ' . number_format((float)$profile['ticket_min']) . ' – ' . number_format((float)$profile['ticket_max']) : 'Negotiable' ?></span>
      </div>

      <?php if ($userId && $userId === $ownerUserId): ?>
      <a href="<?= APP_URL ?>/investor/profile-edit.php" class="stitch-sidebar-cta">Edit Profile</a>
      <?php elseif ($showContact): ?>
      <button class="stitch-sidebar-cta" onclick="document.getElementById('interest-modal').classList.add('open')">Send Proposal</button>
      <?php else: ?>
      <a href="<?= APP_URL ?>/upgrade" class="stitch-sidebar-cta" style="display:block;text-align:center;">Unlock Contact — Go Premium</a>
      <?php endif; ?>

      <div class="stitch-sidebar-meta">
        <div class="stitch-sidebar-meta-row">
          <span>Member Since</span>
          <span><?= $profile['created_at'] ? date('F Y', strtotime($profile['created_at'])) : '—' ?></span>
        </div>
        <div class="stitch-sidebar-meta-row">
          <span>Last Active</span>
          <span><?= date_human($profile['last_login_at'] ?? $profile['updated_at']) ?></span>
        </div>
        <div class="stitch-sidebar-meta-row">
          <span>Profile Strength</span>
          <span style="display:flex;align-items:center;gap:4px;">
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= $strengthPercent >= 80 ? 'var(--color-success)' : ($strengthPercent >= 50 ? 'var(--color-warning)' : 'var(--ds-muted)') ?>;"></span>
            <?= $strengthPercent ?>%
          </span>
        </div>
      </div>

      <div style="padding:12px 16px;font-size:13px;color:var(--ds-muted);">
        <?php if ($showContact && $profile['email']): ?>
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
            <i class="fas fa-envelope" style="font-size:13px;"></i>
            <?= e($profile['email']) ?>
          </div>
        <?php else: ?>
          <span style="font-size:12px;">Available after match: Email, Phone</span>
        <?php endif; ?>
        <?php if ($showContact && $profile['phone']): ?>
          <div style="display:flex;align-items:center;gap:8px;">
            <i class="fas fa-phone" style="font-size:13px;"></i>
            <?= e($profile['phone']) ?>
          </div>
        <?php endif; ?>
      </div>

      <?php if (!$showContact): ?>
      <div class="stitch-sidebar-disclaimer">
        <i class="fas fa-shield-alt"></i>
        Profile reviewed by Asaan Capital analysts. Connect to access contact details.
      </div>
      <?php endif; ?>
    </div>

    <button class="stitch-btn-secondary" style="width:100%;margin-top:12px;padding:8px;font-size:13px;border:1px solid var(--ds-border);border-radius:8px;background:transparent;cursor:pointer;" onclick="document.getElementById('report-modal').classList.add('open')">
      <i class="fas fa-flag" style="font-size:12px;"></i> Report Listing
    </button>
  </div>

</div>
<!-- / .stitch-layout -->

</div>
<!-- / .stitch-detail -->

<!-- ════ MODALS ════ -->

<!-- Interest Modal -->
<div id="interest-modal" class="stitch-overlay" onclick="if(event.target===this)this.classList.remove('open')" role="dialog" aria-modal="true">
  <div class="stitch-overlay-content" onclick="event.stopImmediatePropagation()">
    <div class="stitch-overlay-header">
      <h3>Send Proposal to <?= e($profile['name']) ?></h3>
      <button class="stitch-overlay-close" onclick="document.getElementById('interest-modal').classList.remove('open')">&times;</button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/connections/send-interest" class="stitch-form">
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="receiver_id" value="<?= $investorId ?>">
      <div class="stitch-field">
        <label>Your Message</label>
        <textarea name="message" rows="4" required placeholder="Introduce yourself and explain why you'd like to connect with this investor..." style="width:100%;padding:10px 12px;border:1px solid var(--ds-border);border-radius:8px;font-family:inherit;font-size:14px;"></textarea>
      </div>
      <button type="submit" class="stitch-btn-primary" style="width:100%;padding:10px;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">Send Proposal</button>
    </form>
  </div>
</div>

<!-- Report Modal -->
<div id="report-modal" class="stitch-overlay" onclick="if(event.target===this)this.classList.remove('open')" role="dialog" aria-modal="true">
  <div class="stitch-overlay-content" onclick="event.stopImmediatePropagation()">
    <div class="stitch-overlay-header">
      <h3>Report Listing</h3>
      <button class="stitch-overlay-close" onclick="document.getElementById('report-modal').classList.remove('open')">&times;</button>
    </div>
    <form method="POST" onsubmit="event.preventDefault();const f=this;fetch('/api/report.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams(new FormData(f))}).then(r=>r.json()).then(d=>{if(d.ok){alert('Report submitted.');f.closest('.stitch-overlay').classList.remove('open')}}).catch(()=>{alert('Error')})">
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="target_type" value="investor">
      <input type="hidden" name="target_id" value="<?= $investorId ?>">
      <div class="stitch-field">
        <label>Reason</label>
        <select name="reason" required style="width:100%;padding:10px 12px;border:1px solid var(--ds-border);border-radius:8px;font-family:inherit;font-size:14px;">
          <option value="">Select a reason...</option>
          <option value="inaccurate_info">Inaccurate information</option>
          <option value="suspicious">Suspicious or fraudulent</option>
          <option value="duplicate">Duplicate listing</option>
          <option value="inappropriate">Inappropriate content</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div class="stitch-field">
        <label>Details (optional)</label>
        <textarea name="details" rows="3" placeholder="Provide additional context..." style="width:100%;padding:10px 12px;border:1px solid var(--ds-border);border-radius:8px;font-family:inherit;font-size:14px;"></textarea>
      </div>
      <button type="submit" class="stitch-btn-primary" style="width:100%;padding:10px;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">Submit Report</button>
    </form>
  </div>
</div>



<?php require __DIR__ . '/../includes/footer.php'; ?>
