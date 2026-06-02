<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

$user = current_user();
$userId = (int)$user['id'];

$db = db();

$matches = $db->prepare('
    SELECT m.*,
           CASE WHEN m.user_a_id = ? THEN mu.name ELSE mu2.name END AS connected_name,
           CASE WHEN m.user_a_id = ? THEN mu.role ELSE mu2.role END AS connected_role,
           CASE WHEN m.user_a_id = ? THEN mu.email ELSE mu2.email END AS connected_email,
           CASE WHEN m.user_a_id = ? THEN mu.phone ELSE mu2.phone END AS connected_phone,
           CASE
               WHEN m.context_type = \'pitch\' THEN (SELECT tagline FROM pitches WHERE id = m.context_id)
               WHEN m.context_type = \'business\' THEN (SELECT business_name FROM businesses WHERE id = m.context_id)
               WHEN m.context_type = \'franchise\' THEN (SELECT brand_name FROM franchises WHERE id = m.context_id)
               WHEN m.context_type = \'advisor\' THEN (SELECT firm_name FROM advisors WHERE id = m.context_id)
               ELSE NULL
           END AS context_name,
           ir.message AS interest_message
    FROM matches m
    LEFT JOIN interest_requests ir ON ir.id = m.interest_request_id
    JOIN users mu ON mu.id = m.user_a_id
    JOIN users mu2 ON mu2.id = m.user_b_id
    WHERE m.user_a_id = ? OR m.user_b_id = ?
    ORDER BY m.matched_at DESC
');

$matches->execute([$userId, $userId, $userId, $userId, $userId, $userId]);
$allMatches = $matches->fetchAll();

$recentActivity = $db->prepare('
    SELECT \'match\' AS event_type, m.matched_at AS event_at,
           CASE WHEN m.user_a_id = ? THEN mu.name ELSE mu2.name END AS other_name
    FROM matches m
    JOIN users mu ON mu.id = m.user_a_id
    JOIN users mu2 ON mu2.id = m.user_b_id
    WHERE m.user_a_id = ? OR m.user_b_id = ?
    UNION ALL
    SELECT \'interest\' AS event_type, ir.responded_at AS event_at, u.name AS other_name
    FROM interest_requests ir
    JOIN users u ON u.id = ir.sender_id
    WHERE ir.receiver_id = ? AND ir.responded_at IS NOT NULL
    ORDER BY event_at DESC
    LIMIT 10
');
$recentActivity->execute([$userId, $userId, $userId, $userId]);
$activities = $recentActivity->fetchAll();

$pageTitle = 'My Connections';
require __DIR__ . '/../includes/layout-dashboard.php';

ui_page_header(
    'My Connections',
    'Mutual matches where contact details have been revealed &mdash; <strong>' . count($allMatches) . '</strong> total.'
);
?>

<?php if (empty($allMatches)): ?>
  <div class="dash-panel">
    <?php ui_empty_state(['icon' => 'matches', 'title' => 'No connections yet', 'text' => 'Browse listings and send interest requests to get started.', 'ctaHref' => APP_URL . '/browse/businesses', 'ctaLabel' => 'Browse businesses']); ?>
  </div>
<?php else: ?>
  <div class="dash-panel dash-list">
    <?php foreach ($allMatches as $m): ?>
      <div class="dash-listrow" style="flex-wrap:wrap;">
        <div class="dash-avatar"><?= e(strtoupper(substr($m['connected_name'], 0, 2))) ?></div>
        <div class="dash-listrow-main">
          <div class="dash-listrow-title"><?= e($m['connected_name']) ?></div>
          <div class="dash-listrow-sub"><?= e(ucfirst(str_replace('_', ' ', $m['connected_role'] ?? ''))) ?><?php if ($m['context_name']): ?> &middot; <?= e($m['context_name']) ?><?php endif; ?></div>
        </div>
        <div class="dash-listrow-actions">
          <button type="button" class="btn btn-sm btn-primary" onclick="alert('Email: <?= e($m['connected_email']) ?>\nPhone: <?= e($m['connected_phone'] ?? '—') ?>')">View contact</button>
        </div>
        <?php if ($m['interest_message']): ?>
          <div class="dash-listrow-quote" style="flex-basis:100%;">&ldquo;<?= e($m['interest_message']) ?>&rdquo;</div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!empty($activities)): ?>
  <?php ui_section_header('Recent activity'); ?>
  <div class="dash-panel">
    <div class="dash-timeline">
      <?php foreach ($activities as $act): ?>
        <?php $isMatch = $act['event_type'] === 'match'; ?>
        <div class="dash-tl-item">
          <span class="dash-tl-ico tone-<?= $isMatch ? 'success' : 'info' ?>"><?php ui_icon($isMatch ? 'matches' : 'share'); ?></span>
          <div class="dash-tl-body">
            <div class="dash-tl-title"><?= $isMatch ? 'Connected with' : 'Interest request from' ?> <?= e($act['other_name']) ?></div>
          </div>
          <span class="dash-tl-time"><?= date_human($act['event_at']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<p class="t-muted" style="margin-top:var(--space-5);font-size:.85rem;color:var(--dash-ink-soft);">Connections are permanent. You can use the revealed contact details to communicate directly.</p>

<?php require __DIR__ . '/../includes/footer.php'; ?>
