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
?>
<h2 style="margin-bottom:0.25rem;">Advisor Dashboard</h2>
<p style="color:var(--color-text-muted);">Manage your professional profile and track your engagement.</p>

<?php if (!$advisor): ?>
<div class="card" style="text-align:center;padding:3rem 2rem;margin-top:1.5rem;">
  <h3 style="margin-bottom:0.5rem;">No advisor profile yet</h3>
  <p style="color:var(--color-text-muted);margin-bottom:1rem;">Create your advisor profile to start connecting with clients.</p>
  <a href="create.php" class="btn btn-primary">Create Advisor Profile</a>
</div>
<?php else:
$specialties = json_decode($advisor['specialties'] ?? '[]', true) ?: [];
$specialtyNames = array_map(fn($s) => $specialtyLabels[$s] ?? $s, $specialties);
?>
<div style="display:flex;justify-content:space-between;align-items:end;margin-bottom:2rem;flex-wrap:wrap;gap:0.5rem;">
  <div>
    <h3 style="margin-bottom:0.25rem;"><?= e($advisor['firm_name']) ?></h3>
    <div style="color:var(--color-text-muted);display:flex;gap:1rem;flex-wrap:wrap;">
      <?php if ($advisor['is_published']): ?><span style="color:var(--color-success);font-weight:600;">Published</span><?php endif; ?>
      <?php if ($advisor['rating']): ?><span>⭐ Rating <?= e($advisor['rating']) ?></span><?php endif; ?>
      <?php if ($advisor['years_experience']): ?><span><?= e($advisor['years_experience']) ?> yrs experience</span><?php endif; ?>
      <?php if ($advisor['past_deals_count']): ?><span><?= e($advisor['past_deals_count']) ?> deals closed</span><?php endif; ?>
    </div>
  </div>
  <a href="edit.php" class="btn btn-secondary">Edit Profile</a>
</div>

<div class="stats-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem;">
  <div class="stat-card"><div class="stat-value"><?= e($advisor['years_experience']) ?: '—' ?></div><div class="stat-label">Years Experience</div></div>
  <div class="stat-card"><div class="stat-value"><?= e($advisor['past_deals_count']) ?: '—' ?></div><div class="stat-label">Deals Closed</div></div>
  <div class="stat-card"><div class="stat-value"><?= $advisor['total_deal_value'] ? money($advisor['total_deal_value']) : '—' ?></div><div class="stat-label">Total Deal Value</div></div>
  <div class="stat-card"><div class="stat-value"><?= $advisor['rating'] ?: '—' ?></div><div class="stat-label">Rating</div></div>
</div>

<div class="card" style="padding:1.5rem;">
  <h3 style="margin-bottom:0.75rem;">Profile Details</h3>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
    <div><span class="meta-label">Specialties</span><div class="meta-value"><?= count($specialtyNames) ? implode(', ', $specialtyNames) : '—' ?></div></div>
    <div><span class="meta-label">Fee Structure</span><div class="meta-value"><?= e(ucwords(str_replace('_', ' ', $advisor['service_fee_structure']))) ?: '—' ?></div></div>
    <div><span class="meta-label">Fee Range</span><div class="meta-value"><?= $advisor['fee_min'] ? money($advisor['fee_min']) . ' – ' . money($advisor['fee_max']) : '—' ?></div></div>
    <div><span class="meta-label">Bar Council ID</span><div class="meta-value"><?= e($advisor['bar_council_id']) ?: '—' ?></div></div>
  </div>
  <?php if ($advisor['credentials']): ?>
  <div style="margin-top:1rem;">
    <span class="meta-label">Credentials</span>
    <p style="margin-top:0.25rem;"><?= nl2br(e($advisor['credentials'])) ?></p>
  </div>
  <?php endif; ?>
  <?php if ($advisor['description']): ?>
  <div style="margin-top:1rem;">
    <span class="meta-label">About</span>
    <p style="margin-top:0.25rem;"><?= nl2br(e($advisor['description'])) ?></p>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
</div></div>
