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

$stmt = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
$stmt->execute([$userId]);
$recentNotifications = $stmt->fetchAll();

$greeting = 'Good evening';
$hour = (int)date('H');
if ($hour < 12) $greeting = 'Good morning';
elseif ($hour < 17) $greeting = 'Good afternoon';

$pageTitle = 'Investor Dashboard';
require __DIR__ . '/../includes/layout-dashboard.php';
?>
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:0.5rem;">
  <div>
    <h2 style="margin:0 0 0.25rem;"><?= e($greeting) ?>, <?= e($user['name']) ?></h2>
    <div style="color:var(--color-text-muted);">You have <strong><?= $matchesCount ?> match<?= $matchesCount !== 1 ? 'es' : '' ?></strong> on the platform</div>
  </div>
  <a href="<?= APP_URL ?>/browse/businesses" class="btn btn-accent">Browse all businesses →</a>
</div>

<div class="stats-grid" style="display:grid; grid-template-columns:repeat(4, 1fr); gap:1rem; margin-bottom:2rem;">
  <div class="stat-card">
    <div class="stat-value"><?= $interestSent ?></div>
    <div class="stat-label">Interest requests sent</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= $matchesCount ?></div>
    <div class="stat-label">Matches made</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= $savedCount ?></div>
    <div class="stat-label">Saved listings</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= $interestSent + $matchesCount ?></div>
    <div class="stat-label">Total engagements</div>
  </div>
</div>

<?php if (!empty($suggestions)): ?>
<h3 style="margin-bottom:1rem;">Smart Matches for You</h3>
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(310px, 1fr)); gap:1rem;">
  <?php foreach ($suggestions as $s): ?>
  <div class="card business-card" onclick="location.href='<?= APP_URL ?>/business/<?= (int)$s['biz_id'] ?>'" style="cursor:pointer;">
    <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:0.5rem;">
      <span class="tx-badge tx-badge-<?= e($s['listing_type'] === 'sale' ? 'sale' : 'investment') ?>"><?= e($s['listing_type'] === 'sale' ? 'Business for Sale' : 'Investment Opportunity') ?></span>
      <?php if ($s['match_score']): ?><span class="rating-badge"><?= e(number_format($s['match_score'], 0)) ?>%</span><?php endif; ?>
    </div>
    <h4 style="margin:0.5rem 0 0.25rem;"><?= e($s['business_name'] ?? 'Untitled') ?></h4>
    <p style="font-size:0.85rem;margin:0 0 0.5rem;">
      <?= e($s['sector_name'] ?? 'General') ?>
      <?php if ($s['annual_revenue']): ?> • NPR <?= e(number_format((float)$s['annual_revenue'], 0)) ?> revenue<?php endif; ?>
      <?php if ($s['match_score']): ?> • <?= e(number_format($s['match_score'], 0)) ?>% match<?php endif; ?>
    </p>
    <div style="display:flex;gap:1rem;font-size:0.85rem;">
      <?php if ($s['asking_price']): ?><span><span class="meta-label">Asking:</span> NPR <?= e(number_format((float)$s['asking_price'], 0)) ?></span><?php endif; ?>
      <?php if ($s['ebitda_pct']): ?><span><span class="meta-label">EBITDA:</span> <?= e($s['ebitda_pct']) ?>%</span><?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div style="margin-top:2.5rem;">
  <h3>Recent Activity</h3>
  <div class="card" style="padding:1rem 1.25rem; font-size:0.9rem;">
    <?php if (empty($recentNotifications)): ?>
      <div style="padding:8px 0; color:var(--color-text-muted);">No recent activity.</div>
    <?php else: ?>
      <?php foreach ($recentNotifications as $i => $n): ?>
      <div style="display:flex; justify-content:space-between; padding:8px 0; <?= $i < count($recentNotifications) - 1 ? 'border-bottom:1px solid var(--color-border);' : '' ?>">
        <div>
          <?php if ($n['type'] === 'match'): ?>✅<?php elseif ($n['type'] === 'interest'): ?>📩<?php elseif ($n['type'] === 'connection'): ?>🔗<?php else: ?>📌<?php endif; ?>
          <strong><?= e($n['title']) ?></strong>
          <?php if ($n['body']): ?> — <?= e(mb_substr($n['body'], 0, 80)) ?><?php endif; ?>
        </div>
        <div style="color:var(--color-text-muted); font-size:0.75rem; white-space:nowrap; margin-left:1rem;"><?= date_human($n['created_at']) ?></div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
