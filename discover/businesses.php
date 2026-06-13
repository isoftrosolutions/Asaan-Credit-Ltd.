<?php
require __DIR__ . '/../config/bootstrap.php';

$pageTitle = 'Browse Businesses — ' . APP_NAME;
$pageDescription = 'Browse businesses for sale and investment opportunities in Nepal. Find vetted businesses across 40+ industries. Updated daily.';

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Businesses","item":"'.APP_URL.'/browse/businesses"}
  ]
}</script>';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;

$sectorId = $_GET['sector_id'] ?? '';
$province = $_GET['province'] ?? '';
$listingType = $_GET['listing_type'] ?? '';
$priceMin = $_GET['price_min'] ?? '';
$priceMax = $_GET['price_max'] ?? '';
$keyword = $_GET['keyword'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$where = ["b.status = 'approved'", 'b.is_hidden = 0'];
$params = [];

if ($sectorId !== '') {
    $where[] = 'b.sector_id = ?';
    $params[] = (int)$sectorId;
}
if ($province !== '') {
    $where[] = 'b.province = ?';
    $params[] = $province;
}
if ($listingType !== '') {
    $where[] = 'b.listing_type = ?';
    $params[] = $listingType;
}
if ($priceMin !== '') {
    $where[] = 'b.asking_price >= ?';
    $params[] = (float)$priceMin;
}
if ($priceMax !== '') {
    $where[] = 'b.asking_price <= ?';
    $params[] = (float)$priceMax;
}
if ($keyword !== '') {
    $where[] = '(b.business_name LIKE ? OR b.description LIKE ?)';
    $kw = '%' . $keyword . '%';
    $params[] = $kw;
    $params[] = $kw;
}

$whereClause = implode(' AND ', $where);

switch ($sort) {
    case 'rating': $orderBy = 'b.rating DESC'; break;
    case 'price_low': $orderBy = 'b.asking_price ASC'; break;
    case 'price_high': $orderBy = 'b.asking_price DESC'; break;
    default: $orderBy = 'b.created_at DESC';
}

$countSql = "SELECT COUNT(*) FROM businesses b WHERE $whereClause";
$countStmt = db()->prepare($countSql);
$countStmt->execute($params);

$p = paginate($countStmt, $page, $perPage);

$sql = "SELECT b.*, s.name as sector_name
        FROM businesses b
        LEFT JOIN sectors s ON b.sector_id = s.id
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT {$p['perPage']} OFFSET {$p['offset']}";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$businesses = $stmt->fetchAll();

$user = current_user();
$savedIds = [];
if ($user) {
    $sStmt = db()->prepare("SELECT listing_id FROM saved_listings WHERE user_id = ? AND listing_type = 'business'");
    $sStmt->execute([$user['id']]);
    $savedIds = array_column($sStmt->fetchAll(), 'listing_id');
}

$sectors = db()->query("SELECT id, name FROM sectors WHERE is_active = 1 ORDER BY name")->fetchAll();

$activeFilters = 0;
if ($listingType !== '') $activeFilters++;
if ($sectorId !== '') $activeFilters++;
if ($province !== '') $activeFilters++;
if ($priceMin !== '' || $priceMax !== '') $activeFilters++;

$baseUrl = '/discover/businesses.php';
$queryParams = $_GET;
unset($queryParams['page']);
if ($queryParams) {
    $baseUrl .= '?' . http_build_query($queryParams);
}

$badgeMap = [
    'business_sale' => 'Business for Sale',
    'investment' => 'Investment Opportunity',
    'partial_stake' => 'Partial Stake Sale',
    'loan' => 'Business Loan',
    'asset_sale' => 'Asset Sale',
    'franchise' => 'Franchise Opportunity',
    'partner' => 'Looking for Partner',
];
$badgeClass = [
    'business_sale' => 'tx-badge-sale',
    'investment' => 'tx-badge-partial',
    'partial_stake' => 'tx-badge-partial',
    'loan' => 'tx-badge-loan',
    'asset_sale' => 'tx-badge-asset',
    'franchise' => 'tx-badge-sale',
    'partner' => 'tx-badge-partial',
];
$listingTypeLabels = [
    'business_sale' => 'For Sale',
    'investment' => 'Investment',
    'partial_stake' => 'Partial Stake',
    'loan' => 'Loan',
    'asset_sale' => 'Asset Sale',
    'franchise' => 'Franchise',
    'partner' => 'Partner',
];
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?= $breadcrumbSchema ?>
<div class="breadcrumbs container">
  <a href="<?= APP_URL ?>">Home</a> <span>/</span>
  <span>Businesses</span>
</div>

<div class="container" style="padding-bottom:var(--space-8);">
  <div class="browse-title-bar">
    <h2>Businesses for Sale &amp; Investment</h2>
    <span class="result-pill"><?= number_format($p['total']) ?> listing<?= $p['total'] !== 1 ? 's' : '' ?></span>
  </div>

  <button type="button" class="filter-toggle-mobile" onclick="document.getElementById('filter-sidebar').classList.toggle('open')" aria-label="Toggle filters">
    <i class="fas fa-search"></i>
    Filters<?= $activeFilters > 0 ? ' (' . $activeFilters . ')' : '' ?>
  </button>

  <form class="filter-layout" method="GET" action="">
    <div class="filter-sidebar" id="filter-sidebar">
      <h5>Transaction Type</h5>
      <div class="filter-group">
        <label><input type="radio" name="listing_type" value="" <?= $listingType === '' ? 'checked' : '' ?> onchange="this.form.submit()"> All</label>
        <label><input type="radio" name="listing_type" value="sale" <?= $listingType === 'sale' ? 'checked' : '' ?> onchange="this.form.submit()"> Businesses For Sale</label>
        <label><input type="radio" name="listing_type" value="partial_stake" <?= $listingType === 'partial_stake' ? 'checked' : '' ?> onchange="this.form.submit()"> Partial Stake Sale</label>
        <label><input type="radio" name="listing_type" value="loan" <?= $listingType === 'loan' ? 'checked' : '' ?> onchange="this.form.submit()"> Business Loan</label>
        <label><input type="radio" name="listing_type" value="asset_sale" <?= $listingType === 'asset_sale' ? 'checked' : '' ?> onchange="this.form.submit()"> Assets For Sale</label>
      </div>

      <h5>Industry</h5>
      <div class="filter-group">
        <select name="sector_id" onchange="this.form.submit()">
          <option value="">All Industries</option>
          <?php foreach ($sectors as $s): ?>
            <option value="<?= $s['id'] ?>" <?= (string)$s['id'] === $sectorId ? 'selected' : '' ?>><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <h5>Location</h5>
      <div class="filter-group">
        <select name="province" onchange="this.form.submit()">
          <option value="">All Locations</option>
          <option value="Bagmati" <?= $province === 'Bagmati' ? 'selected' : '' ?>>Bagmati</option>
          <option value="Gandaki" <?= $province === 'Gandaki' ? 'selected' : '' ?>>Gandaki</option>
          <option value="Province 1" <?= $province === 'Province 1' ? 'selected' : '' ?>>Province 1</option>
          <option value="Province 2" <?= $province === 'Province 2' ? 'selected' : '' ?>>Province 2</option>
          <option value="Lumbini" <?= $province === 'Lumbini' ? 'selected' : '' ?>>Lumbini</option>
          <option value="Karnali" <?= $province === 'Karnali' ? 'selected' : '' ?>>Karnali</option>
          <option value="Sudurpashchim" <?= $province === 'Sudurpashchim' ? 'selected' : '' ?>>Sudurpashchim</option>
        </select>
      </div>

      <h5>Price Range</h5>
      <div class="filter-group" style="display:flex;gap:0.5rem;">
        <input type="number" name="price_min" class="input" placeholder="Min" value="<?= e($priceMin) ?>" style="width:48%;">
        <input type="number" name="price_max" class="input" placeholder="Max" value="<?= e($priceMax) ?>" style="width:48%;">
      </div>

      <button class="btn btn-primary btn-sm" style="width:100%;">Apply Filters</button>
      <a href="<?= APP_URL ?>/browse/businesses" class="btn btn-ghost btn-sm" style="width:100%;display:block;text-align:center;">Reset</a>
    </div>

    <div>
      <?php if ($activeFilters > 0): ?>
      <div class="filter-chips">
        <?php if ($listingType !== ''): ?>
          <a href="<?= remove_query_param($baseUrl, 'listing_type') ?>" class="filter-chip">
            <?= $listingTypeLabels[$listingType] ?? e($listingType) ?>
            <span class="filter-chip-remove"><i class="fas fa-times"></i></span>
          </a>
        <?php endif; ?>
        <?php if ($sectorId !== ''): $selSector = current(array_filter($sectors, fn($s) => (string)$s['id'] === $sectorId)); ?>
          <a href="<?= remove_query_param($baseUrl, 'sector_id') ?>" class="filter-chip">
            <?= e($selSector['name'] ?? $sectorId) ?>
            <span class="filter-chip-remove"><i class="fas fa-times"></i></span>
          </a>
        <?php endif; ?>
        <?php if ($province !== ''): ?>
          <a href="<?= remove_query_param($baseUrl, 'province') ?>" class="filter-chip">
            <?= e($province) ?>
            <span class="filter-chip-remove"><i class="fas fa-times"></i></span>
          </a>
        <?php endif; ?>
        <?php if ($priceMin !== '' || $priceMax !== ''): ?>
          <a href="<?= remove_query_param(remove_query_param($baseUrl, 'price_min'), 'price_max') ?>" class="filter-chip">
            <?= $priceMin !== '' ? 'NPR ' . number_format((float)$priceMin) : 'NPR 0' ?> &ndash; <?= $priceMax !== '' ? 'NPR ' . number_format((float)$priceMax) : 'Any' ?>
            <span class="filter-chip-remove"><i class="fas fa-times"></i></span>
          </a>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/browse/businesses" class="filter-chip" style="background:transparent;color:var(--color-text-muted);font-weight:500;">Clear all</a>
      </div>
      <?php endif; ?>

      <div class="sort-bar">
        <span class="result-count">
          Showing <strong><?= $p['total'] > 0 ? ($p['offset'] + 1) . '&ndash;' . min($p['offset'] + $perPage, $p['total']) : 0 ?></strong> of <strong><?= number_format($p['total']) ?></strong>
        </span>
        <div class="sort-controls">
          <span class="meta-label">Sort by:</span>
          <select name="sort" onchange="this.form.submit()">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Recently Listed</option>
            <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Rating (Highest)</option>
            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price (Lowest)</option>
            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price (Highest)</option>
          </select>
        </div>
      </div>

      <?php if (empty($businesses)): ?>
        <div class="empty-state-browse">
          <i class="fas fa-chart-bar" style="font-size:48px;color:var(--color-text-muted);margin-bottom:1rem;"></i>
          <h3>No businesses found</h3>
          <p>Try adjusting your filters or search criteria to find more results.</p>
          <a href="<?= APP_URL ?>/browse/businesses" class="btn btn-primary btn-sm">Clear All Filters</a>
        </div>
      <?php else: ?>
        <div class="listing-grid">
          <?php foreach ($businesses as $b): ?>
            <div class="browse-card" onclick="location.href='<?= APP_URL ?>/business/<?= (int)$b['id'] ?>'">
              <div class="card-header-row">
                <span>
                  <span class="tx-badge <?= $badgeClass[$b['listing_type']] ?? '' ?>"><?= $badgeMap[$b['listing_type']] ?? e($b['listing_type']) ?></span>
                  <?php if (!empty($b['is_featured'])): ?>
                    <span class="premium-ribbon" style="margin-left:4px;">PREMIUM</span>
                  <?php endif; ?>
                </span>
                <div class="card-rating">
                  <i class="fas fa-star"></i>
                  <span><?= e($b['rating']) ?></span>
                </div>
              </div>
              <h3 class="card-title"><?= e($b['business_name']) ?></h3>
              <div class="card-body">
                <div class="card-body-left">
                  <p class="card-desc"><?= e(mb_substr($b['description'] ?? '', 0, 150)) ?><?= mb_strlen($b['description'] ?? '') > 150 ? '...' : '' ?></p>
                </div>
                <?php
                  $thumbSrc = '';
                  if (!empty($b['thumbnail_url'])) {
                      $thumbSrc = (str_starts_with($b['thumbnail_url'], 'http') || str_starts_with($b['thumbnail_url'], '/')) ? $b['thumbnail_url'] : '/public/uploads/business-thumbnails/' . $b['thumbnail_url'];
                  }
                ?>
                <?php if (!empty($thumbSrc)): ?>
                  <img class="card-thumb" src="<?= e($thumbSrc) ?>" alt="<?= e($b['business_name']) ?>" loading="lazy">
                <?php else: ?>
                  <div class="card-thumb-placeholder">
                    <i class="fas fa-building" style="font-size:22px;"></i>
                  </div>
                <?php endif; ?>
              </div>
              <div class="card-location">
                <i class="fas fa-map-marker-alt" style="font-size:15px;"></i>
                <?= e($b['district'] ? $b['district'] . ', ' : '') ?><?= e($b['province'] ?? 'Nepal') ?>
              </div>
              <div class="card-data-grid">
                <div class="card-data-item">
                  <span class="card-data-label">Run Rate Sales</span>
                  <span class="card-data-value"><?= money($b['annual_revenue'] ?? 0) ?></span>
                </div>
                <div class="card-data-item">
                  <span class="card-data-label">EBITDA Margin</span>
                  <span class="card-data-value"><?= e($b['ebitda_pct'] ?? '—') ?>%</span>
                </div>
              </div>
              <div class="card-footer">
                <div class="card-footer-left">
                  <span class="card-footer-label"><?= $listingTypeLabels[$b['listing_type']] ?? e($b['listing_type']) ?></span>
                  <span class="card-footer-price"><?= money($b['asking_price'] ?? 0) ?></span>
                </div>
                <button type="button" class="btn-view-details" onclick="event.stopPropagation();location.href='<?= APP_URL ?>/business/<?= (int)$b['id'] ?>'">View Details</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?= render_pagination($p['page'], $p['lastPage'], $baseUrl) ?>
    </div>
  </form>
</div>

<?php if (!empty($businesses)): ?>
<script type="application/ld+json"><?= json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'ItemList',
  'name' => 'Businesses for Sale in Nepal',
  'itemListElement' => array_map(function($b, $i) {
    return [
      '@type' => 'ListItem',
      'position' => $i + 1,
      'url' => APP_URL . '/business/' . (int)$b['id'],
    ];
  }, $businesses, array_keys($businesses)),
], JSON_UNESCAPED_SLASHES) ?></script>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
