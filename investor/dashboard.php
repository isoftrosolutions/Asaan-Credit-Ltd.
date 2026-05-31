<?php
if (!function_exists('db')) {
    require __DIR__ . '/../config/bootstrap.php';
}
require_login();
require_role(ROLE_INVESTOR);

$user = current_user();
$userId = $user['id'];

$stmt = db()->prepare('SELECT COUNT(*) FROM interest_requests WHERE sender_id = ?');
$stmt->execute([$userId]);
$interestSent = (int)$stmt->fetchColumn();

$stmt = db()->prepare('SELECT COUNT(*) FROM matches WHERE user_a_id = ? OR user_b_id = ?');
$stmt->execute([$userId, $userId]);
$matchesCount = (int)$stmt->fetchColumn();

$stmt = db()->prepare('SELECT COUNT(*) FROM saved_listings WHERE user_id = ?');
$stmt->execute([$userId]);
$savedCount = (int)$stmt->fetchColumn();

$stmt = db()->prepare("
    SELECT ssc.*, b.business_name, b.listing_type, b.annual_revenue, b.asking_price, b.ebitda_pct, b.province, b.id AS biz_id, s.name AS sector_name
    FROM smart_suggestion_cache ssc
    LEFT JOIN businesses b ON ssc.target_type = 'business' AND ssc.target_id = b.id
    LEFT JOIN sectors s ON b.sector_id = s.id
    WHERE ssc.user_id = ? AND (ssc.cached_until IS NULL OR ssc.cached_until > NOW())
    ORDER BY ssc.match_score DESC
    LIMIT 6
");
$stmt->execute([$userId]);
$suggestions = $stmt->fetchAll();

$stmt = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 8');
$stmt->execute([$userId]);
$recentNotifications = $stmt->fetchAll();

$greeting = 'Good evening';
$hour = (int)date('H');
if ($hour < 12) $greeting = 'Good morning';
elseif ($hour < 17) $greeting = 'Good afternoon';

$pageTitle = 'Investor Dashboard';
require __DIR__ . '/../includes/layout-dashboard.php';
?>
<div class="dashboard-heading">
  <div>
    <h2><?= e($greeting) ?>, <?= e($user['name']) ?></h2>
    <div class="greeting-sub">You have <strong><?= $matchesCount ?> match<?= $matchesCount !== 1 ? 'es' : '' ?></strong> on the platform</div>
  </div>
  <a href="<?= APP_URL ?>/browse/businesses" class="btn btn-accent btn-sm">Browse all businesses →</a>
</div>

<div class="dashboard-stats">
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(30,122,77,0.1);color:var(--color-success);">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
    </div>
    <div class="stat-card-content">
      <div class="stat-value"><?= $interestSent ?></div>
      <div class="stat-label">Interest requests sent</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(30,72,102,0.1);color:var(--color-secondary);">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
    </div>
    <div class="stat-card-content">
      <div class="stat-value"><?= $matchesCount ?></div>
      <div class="stat-label">Matches made</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(199,122,18,0.1);color:var(--color-warning);">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
    </div>
    <div class="stat-card-content">
      <div class="stat-value"><?= $savedCount ?></div>
      <div class="stat-label">Saved listings</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(152,32,42,0.1);color:var(--color-primary-vivid);">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
    </div>
    <div class="stat-card-content">
      <div class="stat-value"><?= $interestSent + $matchesCount ?></div>
      <div class="stat-label">Total engagements</div>
    </div>
  </div>
</div>

<?php if (!empty($suggestions)): ?>
<div class="section-header">
  <h3>Smart Matches for You</h3>
  <a href="<?= APP_URL ?>/browse/businesses" class="btn btn-ghost btn-sm">View all</a>
</div>
<div class="match-grid">
  <?php foreach ($suggestions as $s): ?>
  <div class="match-card" onclick="location.href='<?= APP_URL ?>/business/<?= (int)$s['biz_id'] ?>'">
    <div class="match-header">
      <span class="tx-badge tx-badge-<?= e($s['listing_type'] === 'sale' ? 'sale' : 'investment') ?>"><?= e($s['listing_type'] === 'sale' ? 'For Sale' : 'Investment') ?></span>
      <?php if ($s['province']): ?>
        <span style="font-size:0.75rem;color:var(--color-text-muted);"><?= e($s['province']) ?></span>
      <?php endif; ?>
    </div>
    <div class="match-title"><?= e($s['business_name'] ?? 'Untitled') ?></div>
    <div class="match-meta"><?= e($s['sector_name'] ?? 'General') ?><?php if ($s['annual_revenue']): ?> &bull; NPR <?= e(number_format((float)$s['annual_revenue'], 0)) ?> revenue<?php endif; ?></div>
    <div class="match-details">
      <?php if ($s['asking_price']): ?>
      <div class="match-detail">
        <span class="match-detail-label">Asking</span>
        <span class="match-detail-value">NPR <?= e(number_format((float)$s['asking_price'], 0)) ?></span>
      </div>
      <?php endif; ?>
      <?php if ($s['ebitda_pct']): ?>
      <div class="match-detail">
        <span class="match-detail-label">EBITDA</span>
        <span class="match-detail-value"><?= e($s['ebitda_pct']) ?>%</span>
      </div>
      <?php endif; ?>
    </div>
    <div class="match-footer">
      <div class="match-score-bar">
        <span class="match-score-label"><?= e(number_format($s['match_score'], 0)) ?>%</span>
        <div class="match-score-track">
          <div class="match-score-fill" style="width:<?= e(number_format($s['match_score'], 0)) ?>%;background:<?= $s['match_score'] >= 80 ? 'var(--color-success)' : ($s['match_score'] >= 60 ? 'var(--color-warning)' : 'var(--color-text-muted)') ?>;"></div>
        </div>
      </div>
      <span style="font-size:0.8rem;color:var(--color-primary);font-weight:600;">View &rarr;</span>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="activity-feed">
  <div class="section-header">
    <h3>Recent Activity</h3>
    <?php if (!empty($recentNotifications)): ?>
    <a href="<?= APP_URL ?>/notifications" class="btn btn-ghost btn-sm">View all</a>
    <?php endif; ?>
  </div>
  <div class="activity-card">
    <?php if (empty($recentNotifications)): ?>
      <div class="activity-empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-4"/></svg>
        <div>No recent activity yet.</div>
      </div>
    <?php else: ?>
      <?php foreach ($recentNotifications as $n): ?>
      <div class="activity-item">
        <div class="activity-icon" style="background:<?php if ($n['type'] === 'match'): ?>rgba(30,122,77,0.1)<?php elseif ($n['type'] === 'interest'): ?>rgba(30,72,102,0.1)<?php elseif ($n['type'] === 'connection'): ?>rgba(199,122,18,0.1)<?php else: ?>rgba(152,32,42,0.1)<?php endif; ?>">
          <?php if ($n['type'] === 'match'): ?>🤝<?php elseif ($n['type'] === 'interest'): ?>📩<?php elseif ($n['type'] === 'connection'): ?>🔗<?php else: ?>📌<?php endif; ?>
        </div>
        <div class="activity-content">
          <div class="activity-title"><?= e($n['title']) ?></div>
          <?php if ($n['body']): ?>
          <div class="activity-body"><?= e(mb_substr($n['body'], 0, 80)) ?></div>
          <?php endif; ?>
        </div>
        <div class="activity-time"><?= date_human($n['created_at']) ?></div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
