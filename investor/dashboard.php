<?php
if (!function_exists('db')) {
    require __DIR__ . '/../config/bootstrap.php';
}
require_login();
require_role([ROLE_INVESTOR, 'individual_investor']);

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

// Fallback: if no smart suggestions, show recent listings
$recentBiz = [];
$recentPitches = [];
if (empty($suggestions)) {
    $recentBiz = db()->query("SELECT id, business_name, listing_type, annual_revenue, asking_price, ebitda_pct, province FROM businesses WHERE status='approved' AND is_hidden=0 ORDER BY created_at DESC LIMIT 6")->fetchAll();
    $recentPitches = db()->query("SELECT p.id, p.tagline, p.funding_amount, p.equity_offered, p.stage, p.pitch_image, s.name as sector_name FROM pitches p LEFT JOIN sectors s ON p.sector_id = s.id WHERE p.is_published=1 AND p.is_hidden=0 ORDER BY p.created_at DESC LIMIT 6")->fetchAll();
}

$stmt = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 8');
$stmt->execute([$userId]);
$recentNotifications = $stmt->fetchAll();

$greeting = 'Good evening';
$hour = (int)date('H');
if ($hour < 12) $greeting = 'Good morning';
elseif ($hour < 17) $greeting = 'Good afternoon';

$pageTitle = 'Investor Dashboard';
require __DIR__ . '/../includes/layout-dashboard.php';

ui_page_header(
    "$greeting, " . e($user['name']),
    'You have <strong>' . $matchesCount . ' match' . ($matchesCount !== 1 ? 'es' : '') . '</strong> on the platform right now.',
    '<a href="' . APP_URL . '/browse/businesses" class="btn btn-primary btn-sm">Browse businesses ' . ui_icon_str('arrowRight') . '</a>'
);
?>

<div class="dash-stats">
  <?php
    ui_stat_card(['label' => 'Interest requests sent', 'value' => $interestSent, 'icon' => 'share', 'tone' => 'success']);
    ui_stat_card(['label' => 'Matches made', 'value' => $matchesCount, 'icon' => 'matches', 'tone' => 'info']);
    ui_stat_card(['label' => 'Saved listings', 'value' => $savedCount, 'icon' => 'heart', 'tone' => 'warning']);
    ui_stat_card(['label' => 'Total engagements', 'value' => $interestSent + $matchesCount, 'icon' => 'trending', 'tone' => 'primary']);
  ?>
</div>

<?php ui_section_header('Quick actions'); ?>
<div class="dash-qa-grid">
  <?php
    ui_quick_action(['title' => 'Browse businesses', 'desc' => 'Discover new opportunities', 'icon' => 'briefcase', 'href' => APP_URL . '/browse/businesses', 'tone' => 'info']);
    ui_quick_action(['title' => 'My connections', 'desc' => 'Manage your conversations', 'icon' => 'matches', 'href' => APP_URL . '/connections', 'tone' => 'success']);
    ui_quick_action(['title' => 'Investment preferences', 'desc' => 'Sharpen your matches', 'icon' => 'settings', 'href' => APP_URL . '/investor/preferences-edit', 'tone' => 'warning']);
    ui_quick_action(['title' => 'My documents', 'desc' => 'Verification & files', 'icon' => 'document', 'href' => APP_URL . '/investor/documents-edit', 'tone' => 'primary']);
  ?>
</div>

<?php if (!empty($suggestions)): ?>
  <?php ui_section_header('Smart matches for you', APP_URL . '/browse/businesses', 'View all'); ?>
  <div class="dash-rec-grid">
    <?php foreach ($suggestions as $s):
      $isSale = ($s['listing_type'] === 'sale');
      $score = (float)$s['match_score'];
      $scoreColor = $score >= 80 ? 'var(--dash-success)' : ($score >= 60 ? 'var(--dash-warning)' : 'var(--dash-ink-soft)');
    ?>
    <a class="dash-rec" href="<?= APP_URL ?>/business/<?= (int)$s['biz_id'] ?>">
      <div class="dash-rec-top">
        <span class="dash-rec-badge <?= $isSale ? 'sale' : 'investment' ?>"><?= $isSale ? 'For Sale' : 'Investment' ?></span>
        <?php if ($s['province']): ?>
          <span class="dash-rec-loc"><?php ui_icon('mapPin'); ?><?= e($s['province']) ?></span>
        <?php endif; ?>
      </div>
      <div>
        <div class="dash-rec-title"><?= e($s['business_name'] ?? 'Untitled') ?></div>
        <div class="dash-rec-meta"><?= e($s['sector_name'] ?? 'General') ?><?php if ($s['annual_revenue']): ?> &bull; NPR <?= e(number_format((float)$s['annual_revenue'], 0)) ?> revenue<?php endif; ?></div>
      </div>
      <?php if ($s['asking_price'] || $s['ebitda_pct']): ?>
      <div class="dash-rec-details">
        <?php if ($s['asking_price']): ?>
        <div>
          <div class="dash-rec-detail-label">Asking</div>
          <div class="dash-rec-detail-value">NPR <?= e(number_format((float)$s['asking_price'], 0)) ?></div>
        </div>
        <?php endif; ?>
        <?php if ($s['ebitda_pct']): ?>
        <div>
          <div class="dash-rec-detail-label">EBITDA</div>
          <div class="dash-rec-detail-value"><?= e($s['ebitda_pct']) ?>%</div>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <div class="dash-rec-foot">
        <div class="dash-rec-score">
          <div class="dash-rec-score-top"><span>Match score</span><span class="dash-rec-score-num"><?= e(number_format($score, 0)) ?>%</span></div>
          <div class="dash-rec-score-track"><div class="dash-rec-score-fill" style="width:<?= e(number_format($score, 0)) ?>%;background:<?= $scoreColor ?>;"></div></div>
        </div>
        <span class="dash-rec-cta">View <?php ui_icon('arrowRight'); ?></span>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

<?php elseif (!empty($recentBiz) || !empty($recentPitches)): ?>
  <?php ui_section_header('Recently added for you', APP_URL . '/browse/businesses', 'View all'); ?>
  <div class="dash-rec-grid">
    <?php foreach ($recentBiz as $rb): ?>
    <a class="dash-rec" href="<?= APP_URL ?>/business/<?= (int)$rb['id'] ?>">
      <div class="dash-rec-top">
        <span class="dash-rec-badge sale">For Sale</span>
        <?php if (!empty($rb['province'])): ?>
          <span class="dash-rec-loc"><?php ui_icon('mapPin'); ?><?= e($rb['province']) ?></span>
        <?php endif; ?>
      </div>
      <div>
        <div class="dash-rec-title"><?= e($rb['business_name'] ?? 'Untitled') ?></div>
        <div class="dash-rec-meta"><?php if ($rb['annual_revenue']): ?>NPR <?= e(number_format((float)$rb['annual_revenue'], 0)) ?> revenue<?php endif; ?></div>
      </div>
      <?php if ($rb['asking_price'] || $rb['ebitda_pct']): ?>
      <div class="dash-rec-details">
        <?php if ($rb['asking_price']): ?>
        <div>
          <div class="dash-rec-detail-label">Asking</div>
          <div class="dash-rec-detail-value">NPR <?= e(number_format((float)$rb['asking_price'], 0)) ?></div>
        </div>
        <?php endif; ?>
        <?php if ($rb['ebitda_pct']): ?>
        <div>
          <div class="dash-rec-detail-label">EBITDA</div>
          <div class="dash-rec-detail-value"><?= e($rb['ebitda_pct']) ?>%</div>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <div class="dash-rec-foot">
        <span class="dash-rec-cta">View <?php ui_icon('arrowRight'); ?></span>
      </div>
    </a>
    <?php endforeach; ?>
    <?php foreach ($recentPitches as $rp): ?>
    <a class="dash-rec" href="<?= APP_URL ?>/pitch/<?= (int)$rp['id'] ?>">
      <div class="dash-rec-top">
        <span class="dash-rec-badge investment">Pitch</span>
        <?php if ($rp['sector_name']): ?>
          <span class="dash-rec-loc"><?= e($rp['sector_name']) ?></span>
        <?php endif; ?>
      </div>
      <div>
        <div class="dash-rec-title"><?= e(mb_substr($rp['tagline'] ?? 'Untitled', 0, 40)) ?></div>
        <div class="dash-rec-meta"><?= e(ucfirst(str_replace('_', ' ', $rp['stage'] ?? ''))) ?></div>
      </div>
      <?php if ($rp['funding_amount']): ?>
      <div class="dash-rec-details">
        <div>
          <div class="dash-rec-detail-label">Funding</div>
          <div class="dash-rec-detail-value">NPR <?= e(number_format((float)$rp['funding_amount'], 0)) ?></div>
        </div>
        <?php if ($rp['equity_offered']): ?>
        <div>
          <div class="dash-rec-detail-label">Equity</div>
          <div class="dash-rec-detail-value"><?= e($rp['equity_offered']) ?>%</div>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <div class="dash-rec-foot">
        <span class="dash-rec-cta">View <?php ui_icon('arrowRight'); ?></span>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="dash-cols" style="margin-top:var(--space-8);">
  <div class="dash-panel">
    <div class="dash-panel-head">
      <span class="dash-panel-title">Recent activity</span>
      <?php if (!empty($recentNotifications)): ?>
        <a href="<?= APP_URL ?>/notifications" class="dash-section-link">View all <?php ui_icon('arrowRight'); ?></a>
      <?php endif; ?>
    </div>
    <?php if (empty($recentNotifications)): ?>
      <?php ui_empty_state(['icon' => 'bell', 'title' => 'No recent activity yet', 'text' => 'When you connect with businesses, updates will show up here.', 'ctaHref' => APP_URL . '/browse/businesses', 'ctaLabel' => 'Browse businesses']); ?>
    <?php else: ?>
      <?php
        $activityTone = ['match' => 'success', 'interest' => 'info', 'connection' => 'warning'];
        $activityIcon = ['match' => 'matches', 'interest' => 'share', 'connection' => 'matches'];
      ?>
      <div class="dash-timeline">
        <?php foreach ($recentNotifications as $n):
          $tone = $activityTone[$n['type']] ?? 'primary';
          $icon = $activityIcon[$n['type']] ?? 'bell';
        ?>
        <div class="dash-tl-item">
          <span class="dash-tl-ico tone-<?= $tone ?>"><?php ui_icon($icon); ?></span>
          <div class="dash-tl-body">
            <div class="dash-tl-title"><?= e($n['title']) ?></div>
            <?php if ($n['body']): ?><div class="dash-tl-text"><?= e(mb_substr($n['body'], 0, 90)) ?></div><?php endif; ?>
          </div>
          <span class="dash-tl-time"><?= date_human($n['created_at']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div style="display:flex;flex-direction:column;gap:var(--space-4);">
    <?php
      ui_pro_tip([
        'tone' => 'info',
        'title' => 'Complete your profile',
        'body' => 'Investors with detailed preferences get <strong>3&times; more relevant matches</strong>. Add your sectors, ticket size and stage to sharpen recommendations.',
      ]);
      ui_pro_tip([
        'tone' => 'success',
        'title' => 'Stay responsive',
        'body' => 'Replying to interest requests within 24 hours keeps conversations warm and improves your match quality over time.',
      ]);
    ?>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
