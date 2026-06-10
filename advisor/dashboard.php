<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role(ROLE_ADVISOR);

$user = current_user();
$userId = (int)$user['id'];

$stmt = db()->prepare('SELECT * FROM advisors WHERE user_id = ?');
$stmt->execute([$userId]);
$advisor = $stmt->fetch();

$specialtyLabels = [
    'm_and_a' => 'M&A Advisory',
    'brokerage' => 'Business Brokerage',
    'legal' => 'Legal',
    'consulting' => 'Consulting',
    'due_diligence' => 'Due Diligence',
];

$pageTitle = 'Advisor Dashboard';
require __DIR__ . '/../includes/layout-dashboard.php';

ui_page_header(
    'Advisor Dashboard',
    'Manage your professional profile and track your engagement.',
    $advisor ? '<a href="' . APP_URL . '/advisor/edit" class="btn btn-primary btn-sm">' . ui_icon_str('settings') . ' Edit profile</a>' : ''
);
?>

<?php if (!$advisor): ?>
  <div class="dash-panel">
    <?php ui_empty_state(['icon' => 'user', 'title' => 'No advisor profile yet', 'text' => 'Create your advisor profile to start connecting with clients.', 'ctaHref' => APP_URL . '/advisor/create', 'ctaLabel' => 'Create advisor profile']); ?>
  </div>
<?php else:
$specialties = json_decode($advisor['specialties'] ?? '[]', true) ?: [];
$specialtyNames = array_map(fn($s) => $specialtyLabels[$s] ?? $s, $specialties);
?>

<div class="dash-panel dash-panel-pad" style="margin-bottom:var(--space-6);display:flex;align-items:center;justify-content:space-between;gap:var(--space-4);flex-wrap:wrap;">
  <div>
    <div class="dash-section-title" style="margin-bottom:6px;"><?= e($advisor['firm_name']) ?></div>
    <div style="display:flex;gap:var(--space-3);flex-wrap:wrap;align-items:center;">
      <?php if ($advisor['is_published']): ?><span class="dash-pill published">Published</span><?php endif; ?>
      <?php if ($advisor['rating']): ?><span class="t-muted" style="display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-star" style="font-size:13px;color:var(--dash-warning);"></i>Rating <?= e($advisor['rating']) ?></span><?php endif; ?>
      <?php if ($advisor['years_experience']): ?><span class="t-muted"><?= e($advisor['years_experience']) ?> yrs experience</span><?php endif; ?>
      <?php if ($advisor['past_deals_count']): ?><span class="t-muted"><?= e($advisor['past_deals_count']) ?> deals closed</span><?php endif; ?>
    </div>
  </div>
</div>

<div class="dash-stats">
  <?php
    ui_stat_card(['label' => 'Years experience', 'value' => $advisor['years_experience'] ?: '—', 'icon' => 'clock', 'tone' => 'info']);
    ui_stat_card(['label' => 'Deals closed', 'value' => $advisor['past_deals_count'] ?: '—', 'icon' => 'check', 'tone' => 'success']);
    ui_stat_card(['label' => 'Total deal value', 'value' => $advisor['total_deal_value'] ? money($advisor['total_deal_value']) : '—', 'icon' => 'tag', 'tone' => 'primary']);
    ui_stat_card(['label' => 'Rating', 'value' => $advisor['rating'] ?: '—', 'icon' => 'chart', 'tone' => 'warning']);
  ?>
</div>

<?php ui_section_header('Profile details'); ?>
<div class="dash-panel dash-panel-pad">
  <div class="dash-deflist">
    <div><div class="dash-def-label">Specialties</div><div class="dash-def-value"><?= count($specialtyNames) ? e(implode(', ', $specialtyNames)) : '—' ?></div></div>
    <div><div class="dash-def-label">Fee structure</div><div class="dash-def-value"><?= e(ucwords(str_replace('_', ' ', $advisor['service_fee_structure']))) ?: '—' ?></div></div>
    <div><div class="dash-def-label">Fee range</div><div class="dash-def-value"><?= $advisor['fee_min'] ? money($advisor['fee_min']) . ' – ' . money($advisor['fee_max']) : '—' ?></div></div>
    <div><div class="dash-def-label">Bar council ID</div><div class="dash-def-value"><?= e($advisor['bar_council_id']) ?: '—' ?></div></div>
  </div>
  <?php if ($advisor['credentials']): ?>
  <div style="margin-top:var(--space-5);">
    <div class="dash-def-label">Credentials</div>
    <p class="dash-prose" style="margin-top:6px;"><?= nl2br(e($advisor['credentials'])) ?></p>
  </div>
  <?php endif; ?>
  <?php if ($advisor['description']): ?>
  <div style="margin-top:var(--space-5);">
    <div class="dash-def-label">About</div>
    <p class="dash-prose" style="margin-top:6px;"><?= nl2br(e($advisor['description'])) ?></p>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
