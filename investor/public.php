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

$pageTitle = e($profile['name'] ?? 'Investor') . ' — Investor Profile';
require __DIR__ . '/../includes/layout-public.php';
?>
<div class="breadcrumbs container">
  <a href="<?= APP_URL ?>">Home</a> <span>/</span>
  <a href="<?= APP_URL ?>/browse/investors">Investors &amp; Buyers</a> <span>/</span>
  <span><?= e($profile['name']) ?></span>
</div>

<div class="container" style="padding-bottom:3rem;">
  <div class="detail-grid" style="display:grid; grid-template-columns: 1fr 320px; gap:2rem;">

    <div>
      <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;">
        <div class="avatar" style="width:72px;height:72px;font-size:1.6rem;display:flex;align-items:center;justify-content:center;background:var(--color-primary-vivid);color:var(--color-text-inverse);border-radius:50%;font-weight:600;"><?= e($initials) ?></div>
        <div>
          <h1 style="margin:0 0 0.1rem;"><?= e($profile['name']) ?></h1>
          <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
            <?php if ($profile['company_name']): ?><span class="tag tag-accent"><?= e($profile['company_name']) ?></span><?php endif; ?>
            <?php if ($profile['province']): ?><span class="tag"><?= e($profile['province']) ?></span><?php endif; ?>
          </div>
        </div>
      </div>

      <div class="verify-row" style="margin-bottom:1.5rem;">
        <span class="trust-badge" style="background:rgba(30,122,77,0.1);color:var(--color-success);">✓ Email <?= $profile['email_verified_at'] ? 'Verified' : 'Unverified' ?></span>
        <?php if ($profile['phone']): ?><span class="trust-badge" style="background:rgba(30,122,77,0.1);color:var(--color-success);">✓ Phone Provided</span><?php endif; ?>
        <?php if ($profile['verification_status'] === 'verified'): ?><span class="trust-badge" style="background:rgba(30,122,77,0.1);color:var(--color-success);">✓ Identity Verified</span><?php endif; ?>
      </div>

      <div class="social-proof" style="margin-bottom:1.5rem;">
        Connected with <?= (int)$profile['past_investments'] ?>+ businesses on InvestMatch
      </div>

      <?php if ($profile['bio']): ?>
      <h3>About</h3>
      <p><?= nl2br(e($profile['bio'])) ?></p>
      <?php endif; ?>

      <h3>Investment Preferences</h3>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
        <div>
          <span class="meta-label">Preferred Sectors</span>
          <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:0.25rem;">
            <?php foreach ($preferredSectors as $sector): ?>
            <span class="tag"><?= e($sector) ?></span>
            <?php endforeach; ?>
            <?php if (empty($preferredSectors)): ?><span style="color:var(--color-text-muted);">Not specified</span><?php endif; ?>
          </div>
        </div>
        <div>
          <span class="meta-label">Investment Size</span>
          <div style="font-weight:600;">
            <?php if ($profile['ticket_min'] || $profile['ticket_max']): ?>
              NPR <?= e(number_format((float)$profile['ticket_min'])) ?> – <?= e(number_format((float)$profile['ticket_max'])) ?>
            <?php else: ?>
              <span style="color:var(--color-text-muted);">Not specified</span>
            <?php endif; ?>
          </div>
        </div>
        <div>
          <span class="meta-label">Preferred Stage</span>
          <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:0.25rem;">
            <?php foreach ($preferredStages as $stage): ?>
            <span class="tag"><?= e($stage) ?></span>
            <?php endforeach; ?>
            <?php if (empty($preferredStages)): ?><span style="color:var(--color-text-muted);">Not specified</span><?php endif; ?>
          </div>
        </div>
        <div>
          <span class="meta-label">Preferred Locations</span>
          <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:0.25rem;">
            <?php foreach ($preferredGeography as $geo): ?>
            <span class="tag"><?= e($geo) ?></span>
            <?php endforeach; ?>
            <?php if (empty($preferredGeography)): ?><span style="color:var(--color-text-muted);">Not specified</span><?php endif; ?>
          </div>
        </div>
      </div>

      <?php if ($profile['total_capital_deployed']): ?>
      <h3 style="margin-top:2rem;">Track Record</h3>
      <p>
        <strong>Total Capital Deployed:</strong> NPR <?= e(number_format((float)$profile['total_capital_deployed'])) ?><br>
        <strong>Past Investments:</strong> <?= (int)$profile['past_investments'] ?> deals<br>
        <?php if ($profile['portfolio_companies']): ?><strong>Portfolio Companies:</strong> <?= nl2br(e($profile['portfolio_companies'])) ?><?php endif; ?>
      </p>
      <?php endif; ?>

      <?php if ($profile['references']): ?>
      <h3 style="margin-top:2rem;">References</h3>
      <p><?= nl2br(e($profile['references'])) ?></p>
      <?php endif; ?>

      <?php if ($profile['linkedin_url'] && $showContact): ?>
      <div style="margin-top:2rem;">
        <a href="<?= e($profile['linkedin_url']) ?>" target="_blank" rel="noopener" class="btn btn-secondary">View LinkedIn Profile →</a>
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
          <span class="value"><?= $profile['created_at'] ? date('Y', strtotime($profile['created_at'])) : '—' ?></span>
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
        <div class="info-row" style="display:flex;justify-content:space-between;padding:6px 0;">
          <span class="label" style="color:var(--color-text-muted);">Past Investments</span>
          <span class="value"><?= (int)$profile['past_investments'] ?></span>
        </div>

        <?php if ($loggedIn): ?>
          <a href="<?= APP_URL ?>/connections/send-interest?receiver_id=<?= (int)$investorId ?>" class="btn btn-accent" style="width:100%;margin-top:1rem;display:block;text-align:center;">Send Proposal</a>
        <?php else: ?>
          <a href="<?= APP_URL ?>/login" class="btn btn-accent" style="width:100%;margin-top:1rem;display:block;text-align:center;">Login to Connect</a>
        <?php endif; ?>

        <div style="margin-top:0.75rem;font-size:0.75rem;color:var(--color-text-muted);text-align:center;">
          <?php if ($showContact && $profile['email']): ?>
            <div>📧 <?= e($profile['email']) ?></div>
          <?php else: ?>
            <span>Available after match: Email, Phone</span>
          <?php endif; ?>
        </div>

        <?php if (!$showContact): ?>
        <div style="margin-top:0.75rem;padding:0.75rem;background:rgba(199,122,18,0.1);border-radius:0.75rem;font-size:0.75rem;color:var(--color-warning);">
          <strong>ℹ Disclaimer:</strong> Profile reviewed by InvestMatch analysts. Connect to access contact details.
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
