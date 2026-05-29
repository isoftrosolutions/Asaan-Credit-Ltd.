<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role(ROLE_FRANCHISOR);

$user = current_user();
$userId = (int)$user['id'];

$sql = "SELECT id, brand_name, sector_id, established_year, existing_units, franchise_fee, total_investment_min, total_investment_max, is_published, is_hidden, is_featured, views, rating, created_at, updated_at
        FROM franchises WHERE user_id = ? ORDER BY created_at DESC";
$stmt = db()->prepare($sql);
$stmt->execute([$userId]);
$listings = $stmt->fetchAll();

$totalListings = count($listings);
$published = 0;
$totalViews = 0;
$ratings = [];
foreach ($listings as $f) {
    if ($f['is_published']) $published++;
    $totalViews += (int)$f['views'];
    if ($f['rating'] > 0) $ratings[] = (float)$f['rating'];
}
$avgRating = count($ratings) ? round(array_sum($ratings) / count($ratings), 1) : '—';

$pageTitle = 'Franchisor Dashboard';
require __DIR__ . '/../includes/layout-dashboard.php';
?>
<h2 style="margin-bottom:0.25rem;">Franchise Dashboard</h2>
<p style="color:#666;">Manage your franchise listings and track performance.</p>

<?php if (empty($listings)): ?>
<div class="card" style="text-align:center;padding:3rem 2rem;margin-top:1.5rem;">
  <h3 style="margin-bottom:0.5rem;">No franchise listings yet</h3>
  <p style="color:#666;margin-bottom:1rem;">Create your first franchise profile to start connecting with franchisees.</p>
  <a href="create.php" class="btn btn-primary">Create Franchise Profile</a>
</div>
<?php else: ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
  <div></div>
  <a href="create.php" class="btn btn-primary">+ New Franchise</a>
</div>

<div class="stats-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem;">
  <div class="stat-card"><div class="stat-value"><?= $totalListings ?></div><div class="stat-label">Total listings</div></div>
  <div class="stat-card"><div class="stat-value"><?= $published ?></div><div class="stat-label">Published</div></div>
  <div class="stat-card"><div class="stat-value"><?= $totalViews ?></div><div class="stat-label">Total views</div></div>
  <div class="stat-card"><div class="stat-value"><?= $avgRating ?></div><div class="stat-label">Avg Rating</div></div>
</div>

<h3>Your Franchise Listings</h3>
<div class="card" style="padding:0;">
  <table style="width:100%;border-collapse:collapse;">
    <tr style="border-bottom:1px solid #eae8e6;background:#faf8f6;">
      <th style="text-align:left;padding:14px 18px;font-weight:600;">Brand</th>
      <th style="text-align:left;padding:14px 18px;font-weight:600;">Franchise Fee</th>
      <th style="text-align:left;padding:14px 18px;font-weight:600;">Investment</th>
      <th style="text-align:center;padding:14px 18px;font-weight:600;">Status</th>
      <th style="text-align:center;padding:14px 18px;font-weight:600;">Views</th>
      <th style="text-align:right;padding:14px 18px;font-weight:600;"></th>
    </tr>
    <?php foreach ($listings as $f): ?>
    <tr style="border-bottom:1px solid #eae8e6;">
      <td style="padding:14px 18px;">
        <strong><?= e($f['brand_name']) ?></strong>
        <?php if ($f['is_featured']): ?><span class="tx-badge tx-badge-franchise" style="font-size:0.7rem;margin-left:0.5rem;">Featured</span><?php endif; ?>
      </td>
      <td style="padding:14px 18px;"><?= $f['franchise_fee'] ? money($f['franchise_fee']) : '—' ?></td>
      <td style="padding:14px 18px;"><?= $f['total_investment_min'] ? money($f['total_investment_min']) . ' – ' . money($f['total_investment_max']) : '—' ?></td>
      <td style="padding:14px 18px;text-align:center;">
        <?php if ($f['is_published']): ?><span style="color:#166534;font-weight:600;">Published</span>
        <?php else: ?><span style="color:#b91c1c;font-weight:600;">Draft</span>
        <?php endif; ?>
      </td>
      <td style="padding:14px 18px;text-align:center;"><?= (int)$f['views'] ?></td>
      <td style="padding:14px 18px;text-align:right;">
        <a href="<?= APP_URL ?>/franchise/<?= $f['id'] ?>" class="btn btn-sm btn-secondary" style="text-decoration:none;">View</a>
        <a href="edit.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-accent" style="text-decoration:none;">Edit</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
</div></div>
