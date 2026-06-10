<?php
require __DIR__ . '/../config/bootstrap.php';

$pageTitle = 'Franchise Opportunities — ' . APP_NAME;
$pageDescription = 'Explore franchise opportunities in Nepal. Find verified franchise brands looking for qualified franchisees across all regions.';

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Franchises","item":"'.APP_URL.'/discover/franchises.php"}
  ]
}</script>';

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

$activeFilters = 0;
if ($sectorId !== '') $activeFilters++;
if ($invMin !== '' || $invMax !== '') $activeFilters++;

$baseUrl = '/discover/franchises.php';
$queryParams = $_GET;
unset($queryParams['page']);
if ($queryParams) {
    $baseUrl .= '?' . http_build_query($queryParams);
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?= $breadcrumbSchema ?>
<div class="breadcrumbs container">
  <a href="<?= APP_URL ?>">Home</a> <span>/</span>
  <span>Franchise Opportunities</span>
</div>

<div class="container" style="padding-bottom:var(--space-8);">
  <div class="browse-title-bar">
    <h2>Franchise &amp; Brand Opportunities</h2>
    <span class="result-pill"><?= number_format($p['total']) ?> opportunit<?= $p['total'] !== 1 ? 'ies' : 'y' ?></span>
  </div>

  <button type="button" class="filter-toggle-mobile" onclick="document.getElementById('filter-sidebar').classList.toggle('open')" aria-label="Toggle filters">
    <i class="fas fa-search"></i>
    Filters<?= $activeFilters > 0 ? ' (' . $activeFilters . ')' : '' ?>
  </button>

  <form class="filter-layout" method="GET" action="">
    <div class="filter-sidebar" id="filter-sidebar">
      <h5>Industry</h5>
      <div class="filter-group">
        <select name="sector_id" onchange="this.form.submit()">
          <option value="">All Industries</option>
          <?php foreach ($sectors as $s): ?>
            <option value="<?= $s['id'] ?>" <?= (string)$s['id'] === $sectorId ? 'selected' : '' ?>><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <h5>Investment Range</h5>
      <div class="filter-group" style="display:flex;gap:0.5rem;">
        <input type="number" name="inv_min" class="input" placeholder="Min" value="<?= e($invMin) ?>" style="width:48%;">
        <input type="number" name="inv_max" class="input" placeholder="Max" value="<?= e($invMax) ?>" style="width:48%;">
      </div>

      <button class="btn btn-primary btn-sm" style="width:100%;">Apply Filters</button>
      <a href="<?= APP_URL ?>/discover/franchises.php" class="btn btn-ghost btn-sm" style="width:100%;display:block;text-align:center;">Reset</a>
    </div>

    <div>
      <?php if ($activeFilters > 0): ?>
      <div class="filter-chips">
        <?php if ($sectorId !== ''): $selSector = current(array_filter($sectors, fn($s) => (string)$s['id'] === $sectorId)); ?>
          <a href="<?= remove_query_param($baseUrl, 'sector_id') ?>" class="filter-chip">
            <?= e($selSector['name'] ?? $sectorId) ?>
            <span class="filter-chip-remove"><i class="fas fa-times"></i></span>
          </a>
        <?php endif; ?>
        <?php if ($invMin !== '' || $invMax !== ''): ?>
          <a href="<?= remove_query_param(remove_query_param($baseUrl, 'inv_min'), 'inv_max') ?>" class="filter-chip">
            NPR <?= $invMin !== '' ? number_format((float)$invMin) : '0' ?> &ndash; NPR <?= $invMax !== '' ? number_format((float)$invMax) : 'Any' ?>
            <span class="filter-chip-remove"><i class="fas fa-times"></i></span>
          </a>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/discover/franchises.php" class="filter-chip" style="background:transparent;color:var(--color-text-muted);font-weight:500;">Clear all</a>
      </div>
      <?php endif; ?>

      <div class="sort-bar">
        <span class="result-count">
          Showing <strong><?= $p['total'] > 0 ? ($p['offset'] + 1) . '&ndash;' . min($p['offset'] + $perPage, $p['total']) : 0 ?></strong> of <strong><?= number_format($p['total']) ?></strong>
        </span>
        <div class="sort-controls">
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
        <div class="empty-state-browse">
          <i class="fas fa-sliders-h" style="font-size:48px;color:var(--color-text-muted);margin-bottom:1rem;"></i>
          <h3>No franchise opportunities found</h3>
          <p>Try adjusting your filters to discover more franchise brands.</p>
          <a href="<?= APP_URL ?>/discover/franchises.php" class="btn btn-primary btn-sm">Clear All Filters</a>
        </div>
      <?php else: ?>
        <div class="listing-grid">
          <?php foreach ($franchises as $f): ?>
            <div class="franchise-card" onclick="location.href='<?= APP_URL ?>/franchise/<?= $f['id'] ?>'">
              <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                  <div class="card-title" style="margin:0;"><?= e($f['brand_name']) ?></div>
                  <div class="card-meta" style="margin-top:2px;">
                    <?= (int)$f['existing_units'] ?> Franchisee<?= (int)$f['existing_units'] !== 1 ? 's' : '' ?>
                    &bull; Est'd <?= e($f['established_year'] ?? 'N/A') ?>
                    <?php if ($f['countries_present']): ?>&bull; <?= e($f['countries_present']) ?><?php endif; ?>
                  </div>
                </div>
                <span class="rating-badge"><?= e($f['rating']) ?></span>
              </div>
              <div class="card-desc" style="margin:0.5rem 0 0;"><?= e(mb_substr($f['description'] ?? '', 0, 150)) ?></div>
              <div class="franchise-stats">
                <div>
                  <div class="franchise-stat-label">Franchise Fee</div>
                  <div class="franchise-stat-value"><?= money($f['franchise_fee'] ?? 0) ?></div>
                </div>
                <div>
                  <div class="franchise-stat-label">Royalty</div>
                  <div class="franchise-stat-value"><?= e($f['royalty_pct'] ?? 0) ?>%</div>
                </div>
                <div>
                  <div class="franchise-stat-label">Investment</div>
                  <div class="franchise-stat-value"><?= money($f['total_investment_min'] ?? 0) ?> &ndash; <?= money($f['total_investment_max'] ?? 0) ?></div>
                </div>
              </div>
              <span class="tx-badge tx-badge-franchise">Franchise Opportunity</span>
              <div class="card-footer" style="margin-top:auto;">
                <button type="button" class="btn btn-accent btn-sm" onclick="event.stopPropagation();location.href='<?= APP_URL ?>/franchise/<?= $f['id'] ?>'">View Details</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?= render_pagination($p['page'], $p['lastPage'], $baseUrl) ?>
    </div>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
