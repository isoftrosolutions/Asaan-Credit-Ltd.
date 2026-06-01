<?php
require __DIR__ . '/../config/bootstrap.php';

$investorId = $_GET['id'] ?? null;
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

$loggedIn = current_user();
$isMatch = false;
$showContact = false;

if ($loggedIn) {
    $stmt = db()->prepare('
        SELECT COUNT(*) FROM matches
        WHERE (user_a_id = ? AND user_b_id = ?) OR (user_a_id = ? AND user_b_id = ?)
    ');
    $stmt->execute([$loggedIn['id'], $investorId, $investorId, $loggedIn['id']]);
    $isMatch = (int)$stmt->fetchColumn() > 0;
    $showContact = $isMatch || $loggedIn['id'] == $investorId;
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

$investorTypeLabel = $investorTypeLabels[$profile['investor_type']] ?? $profile['investor_type'];

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
foreach ($profileFields as $val) {
    if ($val) $filledCount++;
}
$totalFields = count($profileFields);
$strengthPercent = min(100, round(($filledCount / $totalFields) * 100));

if ($strengthPercent >= 80) {
    $strengthLabel = 'Strong';
    $strengthColor = 'var(--color-success)';
} elseif ($strengthPercent >= 50) {
    $strengthLabel = 'Moderate';
    $strengthColor = 'var(--color-warning)';
} else {
    $strengthLabel = 'Basic';
    $strengthColor = 'var(--color-text-muted)';
}

$pageTitle = e($profile['name'] ?? 'Investor') . ' — Investor Profile';
require __DIR__ . '/../includes/layout-public.php';
?>
<div class="breadcrumbs container">
  <a href="<?= APP_URL ?>">Home</a> <span>/</span>
  <a href="<?= APP_URL ?>/browse/investors">Investors &amp; Buyers</a> <span>/</span>
  <span><?= e($profile['name']) ?></span>
</div>

<div class="container" style="padding-bottom:3rem;">

  <?php if ($loggedIn && (int)$loggedIn['id'] === (int)$investorId): ?>
  <div style="display:flex;justify-content:flex-end;margin-bottom:1rem;">
    <a href="<?= APP_URL ?>/investor/profile-edit.php" class="btn btn-primary btn-sm">&#9998; Edit Profile</a>
  </div>
  <?php endif; ?>

  <div class="detail-grid" style="display:grid; grid-template-columns: 1fr 320px; gap:2rem;">

    <div>

      <div style="background:linear-gradient(135deg, var(--color-secondary), #0a1e2f);border-radius:var(--radius-lg);padding:2rem;margin-bottom:1.5rem;color:var(--color-text-inverse);">
        <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;">
          <div class="avatar" style="width:80px;height:80px;font-size:1.8rem;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.2);color:#fff;border-radius:50%;font-weight:700;flex-shrink:0;"><?= e($initials) ?></div>
          <div style="flex:1;min-width:200px;">
            <h1 style="margin:0 0 0.25rem;color:#fff;font-size:1.6rem;"><?= e($profile['name']) ?></h1>
            <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
              <?php if ($profile['company_name']): ?>
              <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;font-size:0.75rem;font-weight:600;background:rgba(255,255,255,0.15);border-radius:var(--radius-pill);color:#fff;"><?= e($profile['company_name']) ?></span>
              <?php endif; ?>
              <?php if ($profile['investor_type']): ?>
              <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;font-size:0.75rem;font-weight:600;background:rgba(255,255,255,0.15);border-radius:var(--radius-pill);color:#fff;"><?= e($investorTypeLabel) ?></span>
              <?php endif; ?>
              <?php if ($profile['province']): ?>
              <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;font-size:0.75rem;font-weight:600;background:rgba(255,255,255,0.15);border-radius:var(--radius-pill);color:#fff;"><?= e($profile['province']) ?></span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;">
        <div class="stat-card" style="flex:1;min-width:160px;">
          <div class="stat-card-content">
            <div class="stat-value"><?= (int)$profile['past_investments'] ?>+</div>
            <div class="stat-label">Connected Businesses</div>
          </div>
        </div>
        <div class="stat-card" style="flex:1;min-width:160px;">
          <div class="stat-card-content">
            <div class="stat-value" style="display:flex;align-items:center;gap:6px;">
              <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:<?= $strengthColor ?>;"></span>
              <?= $strengthPercent ?>%
            </div>
            <div class="stat-label">Profile Strength (<?= $strengthLabel ?>)</div>
          </div>
        </div>
        <div class="stat-card" style="flex:1;min-width:160px;">
          <div class="stat-card-content">
            <div class="stat-value"><?= date('Y', strtotime($profile['created_at'])) ?></div>
            <div class="stat-label">Member Since</div>
          </div>
        </div>
      </div>

      <div class="verify-row" style="margin-bottom:1.5rem;">
        <span class="trust-badge" style="background:rgba(30,122,77,0.1);color:var(--color-success);">&#10003; Email <?= $profile['email_verified_at'] ? 'Verified' : 'Unverified' ?></span>
        <?php if ($profile['phone']): ?><span class="trust-badge" style="background:rgba(30,122,77,0.1);color:var(--color-success);">&#10003; Phone Provided</span><?php endif; ?>
        <?php if ($profile['verification_status'] === 'verified'): ?><span class="trust-badge" style="background:rgba(30,122,77,0.1);color:var(--color-success);">&#10003; Identity Verified</span><?php endif; ?>
      </div>

      <?php if ($profile['bio']): ?>
      <div class="card" style="margin-bottom:1.5rem;">
        <h3 style="margin-top:0;">About</h3>
        <p style="margin-bottom:0;line-height:1.7;"><?= nl2br(e($profile['bio'])) ?></p>
      </div>
      <?php endif; ?>

      <div class="card" style="margin-bottom:1.5rem;">
        <h3 style="margin-top:0;">Investment Preferences</h3>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
          <div>
            <span class="meta-label">Preferred Sectors</span>
            <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:0.35rem;">
              <?php foreach ($preferredSectors as $sector): ?>
              <span class="tag" style="background:rgba(30,122,77,0.08);color:var(--color-success);font-weight:600;"><?= e($sector) ?></span>
              <?php endforeach; ?>
              <?php if (empty($preferredSectors)): ?><span style="color:var(--color-text-muted);font-size:0.9rem;">Not specified</span><?php endif; ?>
            </div>
          </div>
          <div>
            <span class="meta-label">Investment Size</span>
            <div style="font-weight:600;margin-top:0.35rem;">
              <?php if ($profile['ticket_min'] || $profile['ticket_max']): ?>
                NPR <?= e(number_format((float)$profile['ticket_min'])) ?> – <?= e(number_format((float)$profile['ticket_max'])) ?>
              <?php else: ?>
                <span style="color:var(--color-text-muted);font-size:0.9rem;">Not specified</span>
              <?php endif; ?>
            </div>
          </div>
          <div>
            <span class="meta-label">Preferred Stage</span>
            <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:0.35rem;">
              <?php foreach ($preferredStages as $stage): ?>
              <span class="tag" style="background:rgba(30,72,102,0.08);color:var(--color-secondary);font-weight:600;"><?= e($stage) ?></span>
              <?php endforeach; ?>
              <?php if (empty($preferredStages)): ?><span style="color:var(--color-text-muted);font-size:0.9rem;">Not specified</span><?php endif; ?>
            </div>
          </div>
          <div>
            <span class="meta-label">Preferred Locations</span>
            <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:0.35rem;">
              <?php foreach ($preferredGeography as $geo): ?>
              <span class="tag" style="background:rgba(199,122,18,0.08);color:var(--color-warning);font-weight:600;"><?= e($geo) ?></span>
              <?php endforeach; ?>
              <?php if (empty($preferredGeography)): ?><span style="color:var(--color-text-muted);font-size:0.9rem;">Not specified</span><?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <?php if ($profile['total_capital_deployed']): ?>
      <div class="card" style="margin-bottom:1.5rem;">
        <h3 style="margin-top:0;">Track Record</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
          <div>
            <span class="meta-label">Total Capital Deployed</span>
            <div style="font-weight:700;font-size:1.1rem;">NPR <?= e(number_format((float)$profile['total_capital_deployed'])) ?></div>
          </div>
          <div>
            <span class="meta-label">Past Investments</span>
            <div style="font-weight:700;font-size:1.1rem;"><?= (int)$profile['past_investments'] ?> deals</div>
          </div>
        </div>
        <?php if ($profile['portfolio_companies']): ?>
        <div style="margin-top:0.75rem;">
          <span class="meta-label">Portfolio Companies</span>
          <p style="margin:0.25rem 0 0;"><?= nl2br(e($profile['portfolio_companies'])) ?></p>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if ($profile['references']): ?>
      <div class="card" style="margin-bottom:1.5rem;">
        <h3 style="margin-top:0;">References</h3>
        <p style="margin-bottom:0;"><?= nl2br(e($profile['references'])) ?></p>
      </div>
      <?php endif; ?>

      <?php if ($profile['linkedin_url'] && $showContact): ?>
      <div style="margin-bottom:1.5rem;">
        <a href="<?= e($profile['linkedin_url']) ?>" target="_blank" rel="noopener" class="btn btn-secondary" style="display:inline-flex;align-items:center;gap:0.5rem;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
          View LinkedIn Profile
        </a>
      </div>
      <?php endif; ?>
    </div>

    <div>
      <div class="card" style="position:sticky;top:1rem;">
        <div style="text-align:center;margin-bottom:1rem;">
          <div class="avatar" style="width:80px;height:80px;font-size:1.8rem;margin:0 auto 0.75rem;display:flex;align-items:center;justify-content:center;background:var(--color-primary-vivid);color:var(--color-text-inverse);border-radius:50%;font-weight:600;"><?= e($initials) ?></div>
          <h4 style="margin:0;"><?= e($profile['name']) ?></h4>
          <?php if ($profile['company_name']): ?><div style="font-size:0.85rem;color:var(--color-text-muted);"><?= e($profile['company_name']) ?></div><?php endif; ?>
        </div>

        <div class="info-row" style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--color-border);">
          <span class="label" style="color:var(--color-text-muted);">Member Since</span>
          <span class="value"><?= $profile['created_at'] ? date('F Y', strtotime($profile['created_at'])) : '—' ?></span>
        </div>
        <div class="info-row" style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--color-border);">
          <span class="label" style="color:var(--color-text-muted);">Last Active</span>
          <span class="value"><?= date_human($profile['last_login_at'] ?? $profile['updated_at']) ?></span>
        </div>
        <?php if ($profile['total_capital_deployed']): ?>
        <div class="info-row" style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--color-border);">
          <span class="label" style="color:var(--color-text-muted);">Capital Deployed</span>
          <span class="value">NPR <?= e(number_format((float)$profile['total_capital_deployed'])) ?></span>
        </div>
        <?php endif; ?>
        <div class="info-row" style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--color-border);">
          <span class="label" style="color:var(--color-text-muted);">Past Investments</span>
          <span class="value"><?= (int)$profile['past_investments'] ?></span>
        </div>
        <?php if ($profile['investor_type']): ?>
        <div class="info-row" style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--color-border);">
          <span class="label" style="color:var(--color-text-muted);">Investor Type</span>
          <span class="value" style="font-size:0.8rem;"><?= e($investorTypeLabel) ?></span>
        </div>
        <?php endif; ?>
        <div class="info-row" style="display:flex;justify-content:space-between;padding:6px 0;">
          <span class="label" style="color:var(--color-text-muted);">Profile Strength</span>
          <span class="value" style="display:flex;align-items:center;gap:4px;font-size:0.8rem;">
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= $strengthColor ?>;"></span>
            <?= $strengthPercent ?>%
          </span>
        </div>

        <?php if ($loggedIn): ?>
          <a href="<?= APP_URL ?>/connections/send-interest?receiver_id=<?= (int)$investorId ?>" class="btn btn-accent" style="width:100%;margin-top:1rem;display:block;text-align:center;">Send Proposal</a>
        <?php else: ?>
          <a href="<?= APP_URL ?>/login" class="btn btn-accent" style="width:100%;margin-top:1rem;display:block;text-align:center;">Login to Connect</a>
        <?php endif; ?>

        <div style="margin-top:0.75rem;font-size:0.75rem;color:var(--color-text-muted);text-align:center;">
          <?php if ($showContact && $profile['email']): ?>
            <div>&#128231; <?= e($profile['email']) ?></div>
          <?php else: ?>
            <span>Available after match: Email, Phone</span>
          <?php endif; ?>
        </div>

        <?php if (!$showContact): ?>
        <div style="margin-top:0.75rem;padding:0.75rem;background:rgba(199,122,18,0.1);border-radius:0.75rem;font-size:0.75rem;color:var(--color-warning);">
          <strong>&#8505;&#65039; Disclaimer:</strong> Profile reviewed by InvestMatch analysts. Connect to access contact details.
        </div>
        <?php endif; ?>

        <button class="btn btn-ghost btn-sm" style="width:100%;margin-top:0.75rem;font-size:0.75rem;color:var(--color-text-muted);" onclick="document.getElementById('report-modal').classList.add('open')">Report listing</button>
      </div>
    </div>

    <div id="report-modal" class="modal" onclick="if(event.target===this)this.classList.remove('open')">
      <div class="modal-content" onclick="event.stopImmediatePropagation()">
        <div class="modal-header">
          <h3>Report Listing</h3>
          <button class="close-btn" onclick="document.getElementById('report-modal').classList.remove('open')">&times;</button>
        </div>
        <form method="POST" action="/connections/send-interest" onsubmit="event.preventDefault();const f=this;fetch('/api/report.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams(new FormData(f))}).then(r=>r.json()).then(d=>{if(d.ok){alert('Report submitted. Thank you.');f.closest('.modal').classList.remove('open')}else{alert('Error submitting report.')}}).catch(()=>{alert('Error submitting report.')})">
          <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="target_type" value="investor">
          <input type="hidden" name="target_id" value="<?= $investorId ?>">
          <div class="input-group">
            <label>Reason</label>
            <select name="reason" class="input" required>
              <option value="">Select a reason...</option>
              <option value="inaccurate_info">Inaccurate information</option>
              <option value="suspicious">Suspicious or fraudulent</option>
              <option value="duplicate">Duplicate listing</option>
              <option value="inappropriate">Inappropriate content</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="input-group">
            <label>Details (optional)</label>
            <textarea name="details" class="input" rows="3" placeholder="Provide additional context..."></textarea>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%;">Submit Report</button>
        </form>
      </div>
    </div>

  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
