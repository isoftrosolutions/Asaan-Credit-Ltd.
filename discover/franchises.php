<?php
require __DIR__ . '/../config/bootstrap.php';

$pageTitle = 'Franchise Opportunities — ' . APP_NAME;

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;

$sectorId = $_GET['sector_id'] ?? '';
$invMin = $_GET['inv_min'] ?? '';
$invMax = $_GET['inv_max'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$where = ['f.is_published = 1', 'f.is_hidden = 0'];
$params = [];

if ($sectorId !== '') {
    $where[] = 'f.sector_id = ?';
    $params[] = (int)$sectorId;
}
if ($invMin !== '') {
    $where[] = 'f.total_investment_max >= ?';
    $params[] = (float)$invMin;
}
if ($invMax !== '') {
    $where[] = 'f.total_investment_min <= ?';
    $params[] = (float)$invMax;
}

$whereClause = implode(' AND ', $where);

switch ($sort) {
    case 'rating': $orderBy = 'f.rating DESC'; break;
    case 'inv_asc': $orderBy = 'f.total_investment_min ASC'; break;
    case 'inv_desc': $orderBy = 'f.total_investment_max DESC'; break;
    default: $orderBy = 'f.created_at DESC';
}

$countSql = "SELECT COUNT(*) FROM franchises f WHERE $whereClause";
$countStmt = db()->prepare($countSql);
$countStmt->execute($params);

$p = paginate($countStmt, $page, $perPage);

$sql = "SELECT f.*, s.name as sector_name
        FROM franchises f
        LEFT JOIN sectors s ON f.sector_id = s.id
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT {$p['perPage']} OFFSET {$p['offset']}";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$franchises = $stmt->fetchAll();

$sectors = db()->query("SELECT id, name FROM sectors WHERE is_active = 1 ORDER BY name")->fetchAll();

$baseUrl = '/discover/franchises.php';
$queryParams = $_GET;
unset($queryParams['page']);
if ($queryParams) {
    $baseUrl .= '?' . http_build_query($queryParams);
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="breadcrumbs container">
  <a href="<?= APP_URL ?>">Home</a> <span>/</span>
  <span>Franchise Opportunities</span>
</div>

<div class="container" style="padding-bottom:3rem;">
  <h2>Franchise &amp; Brand Opportunities</h2>
  <p style="font-size:0.9rem;margin-top:0;">Showing franchise opportunities from verified brands and franchisors.</p>

  <form class="filter-layout" style="margin-top:1.5rem;" method="GET" action="">
    <div class="filter-sidebar">
      <h5 style="margin-top:0;">Industry</h5>
      <div class="filter-group">
        <select name="sector_id" class="input" style="border-bottom:1px solid #ccc;padding:0.5rem 0;font-size:0.85rem;" onchange="this.form.submit()">
          <option value="">All Industries</option>
          <?php foreach ($sectors as $s): ?>
            <option value="<?= $s['id'] ?>" <?= (string)$s['id'] === $sectorId ? 'selected' : '' ?>><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <h5>Investment Range</h5>
      <div class="filter-group" style="display:flex;gap:0.5rem;">
        <input type="number" name="inv_min" placeholder="Min" value="<?= e($invMin) ?>" class="input" style="padding:0.4rem;font-size:0.85rem;width:48%;">
        <input type="number" name="inv_max" placeholder="Max" value="<?= e($invMax) ?>" class="input" style="padding:0.4rem;font-size:0.85rem;width:48%;">
      </div>

      <button class="btn btn-primary btn-sm" style="width:100%;margin-top:0.5rem;">Apply Filters</button>
      <a href="<?= APP_URL ?>/discover/franchises.php" class="btn btn-ghost btn-sm" style="width:100%;display:block;text-align:center;">Reset</a>
    </div>

    <div>
      <div class="sort-bar">
        <span>Showing <?= ($p['offset'] + 1) ?> &ndash; <?= min($p['offset'] + $perPage, $p['total']) ?> of <?= $p['total'] ?></span>
        <div style="display:flex;align-items:center;gap:0.5rem;">
          <span class="meta-label">Sort by:</span>
          <select name="sort" onchange="this.form.submit()">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
            <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Rating (Highest)</option>
            <option value="inv_asc" <?= $sort === 'inv_asc' ? 'selected' : '' ?>>Investment (Lowest)</option>
            <option value="inv_desc" <?= $sort === 'inv_desc' ? 'selected' : '' ?>>Investment (Highest)</option>
          </select>
        </div>
      </div>

      <?php if (empty($franchises)): ?>
        <p style="text-align:center;padding:3rem 0;color:#888;">No franchise opportunities found matching your criteria.</p>
      <?php else: ?>
        <div class="listing-grid">
          <?php foreach ($franchises as $f): ?>
            <div class="card" onclick="location.href='<?= APP_URL ?>/franchise/detail/<?= $f['id'] ?>'" style="cursor:pointer;">
              <div style="display:flex;justify-content:space-between;align-items:start;">
                <div>
                  <h4 style="margin:0;"><?= e($f['brand_name']) ?></h4>
                  <div style="font-size:0.85rem;color:#666;">
                    <?= (int)$f['existing_units'] ?> Franchisees &bull; Est'd <?= e($f['established_year'] ?? 'N/A') ?>
                    <?php if ($f['countries_present']): ?>&bull; <?= e($f['countries_present']) ?><?php endif; ?>
                    <?php if ($f['sector_name']): ?>&bull; <?= e($f['sector_name']) ?><?php endif; ?>
                  </div>
                </div>
                <span class="rating-badge"><?= e($f['rating']) ?></span>
              </div>
              <p style="font-size:0.9rem;margin:0.5rem 0;"><?= e(mb_substr($f['description'] ?? '', 0, 200)) ?></p>
              <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0.75rem;margin:0.75rem 0;">
                <div><span class="meta-label">Franchise Fee</span><div class="meta-value"><?= money($f['franchise_fee'] ?? 0) ?></div></div>
                <div><span class="meta-label">Royalty</span><div class="meta-value"><?= e($f['royalty_pct'] ?? 0) ?>%</div></div>
                <div><span class="meta-label">Investment</span><div class="meta-value"><?= money($f['total_investment_min'] ?? 0) ?> &ndash; <?= money($f['total_investment_max'] ?? 0) ?></div></div>
              </div>
              <span class="tx-badge tx-badge-franchise">Franchise Opportunity</span>
              <div style="margin-top:0.75rem;">
                <button class="btn btn-accent btn-sm" onclick="event.stopPropagation();location.href='<?= APP_URL ?>/franchise/detail/<?= $f['id'] ?>'">View Details</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div style="text-align:center;margin-top:2rem;">
        <?= render_pagination($p['page'], $p['lastPage'], $baseUrl) ?>
      </div>
    </div>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
