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

ui_page_header(
    'Franchise Dashboard',
    'Manage your franchise listings and track performance.',
    empty($listings) ? '' : '<a href="' . APP_URL . '/franchise/create" class="btn btn-primary btn-sm">' . ui_icon_str('plus') . ' New franchise</a>'
);
?>

<?php if (empty($listings)): ?>
  <div class="dash-panel">
    <?php ui_empty_state(['icon' => 'briefcase', 'title' => 'No franchise listings yet', 'text' => 'Create your first franchise profile to start connecting with franchisees.', 'ctaHref' => APP_URL . '/franchise/create', 'ctaLabel' => 'Create franchise profile']); ?>
  </div>
<?php else: ?>

<div class="dash-stats">
  <?php
    ui_stat_card(['label' => 'Total listings', 'value' => $totalListings, 'icon' => 'briefcase', 'tone' => 'primary']);
    ui_stat_card(['label' => 'Published', 'value' => $published, 'icon' => 'check', 'tone' => 'success']);
    ui_stat_card(['label' => 'Total views', 'value' => number_format($totalViews), 'icon' => 'eye', 'tone' => 'info']);
    ui_stat_card(['label' => 'Avg rating', 'value' => $avgRating, 'icon' => 'chart', 'tone' => 'warning']);
  ?>
</div>

<?php ui_section_header('Your franchise listings'); ?>
<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>Brand</th><th>Franchise fee</th><th>Investment</th>
        <th class="ta-center">Status</th><th class="ta-center">Views</th><th class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($listings as $f): ?>
        <tr>
          <td>
            <span class="t-strong"><?= e($f['brand_name']) ?></span>
            <?php if ($f['is_featured']): ?> <span class="dash-pill featured">Featured</span><?php endif; ?>
          </td>
          <td><?= $f['franchise_fee'] ? money($f['franchise_fee']) : '—' ?></td>
          <td><?= $f['total_investment_min'] ? money($f['total_investment_min']) . ' – ' . money($f['total_investment_max']) : '—' ?></td>
          <td class="ta-center"><span class="dash-pill <?= $f['is_published'] ? 'published' : 'draft' ?>"><?= $f['is_published'] ? 'Published' : 'Draft' ?></span></td>
          <td class="ta-center"><?= (int)$f['views'] ?></td>
          <td class="ta-right">
            <span class="dash-table-actions">
              <a href="<?= APP_URL ?>/franchise/<?= $f['id'] ?>" class="btn btn-sm btn-outline">View</a>
              <a href="<?= APP_URL ?>/franchise/edit?id=<?= $f['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
            </span>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
