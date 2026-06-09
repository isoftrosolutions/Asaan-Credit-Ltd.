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
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M2 14h4"/><path d="M10 8h4"/><path d="M18 16h4"/></svg>
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
            <span class="filter-chip-remove"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg></span>
          </a>
        <?php endif; ?>
        <?php if ($sectorId !== ''): $selSector = current(array_filter($sectors, fn($s) => (string)$s['id'] === $sectorId)); ?>
          <a href="<?= remove_query_param($baseUrl, 'sector_id') ?>" class="filter-chip">
            <?= e($selSector['name'] ?? $sectorId) ?>
            <span class="filter-chip-remove"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg></span>
          </a>
        <?php endif; ?>
        <?php if ($province !== ''): ?>
          <a href="<?= remove_query_param($baseUrl, 'province') ?>" class="filter-chip">
            <?= e($province) ?>
            <span class="filter-chip-remove"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg></span>
          </a>
        <?php endif; ?>
        <?php if ($priceMin !== '' || $priceMax !== ''): ?>
          <a href="<?= remove_query_param(remove_query_param($baseUrl, 'price_min'), 'price_max') ?>" class="filter-chip">
            <?= $priceMin !== '' ? 'NPR ' . number_format((float)$priceMin) : 'NPR 0' ?> &ndash; <?= $priceMax !== '' ? 'NPR ' . number_format((float)$priceMax) : 'Any' ?>
            <span class="filter-chip-remove"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg></span>
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
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/><path d="M8 11h6"/><path d="M11 8v6"/></svg>
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
                  <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                  <span><?= e($b['rating']) ?></span>
                </div>
              </div>
              <h3 class="card-title"><?= e($b['business_name']) ?></h3>
              <div class="card-body">
                <div class="card-body-left">
                  <p class="card-desc"><?= e(mb_substr($b['description'] ?? '', 0, 150)) ?><?= mb_strlen($b['description'] ?? '') > 150 ? '...' : '' ?></p>
                </div>
                <?php if (!empty($b['thumbnail_url'])): ?>
                  <img class="card-thumb" src="<?= e($b['thumbnail_url']) ?>" alt="<?= e($b['business_name']) ?>" loading="lazy">
                <?php else: ?>
                  <div class="card-thumb-placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="24" height="24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                  </div>
                <?php endif; ?>
              </div>
              <div class="card-location">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
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
                <button class="btn-view-details" onclick="event.stopPropagation();location.href='<?= APP_URL ?>/business/<?= (int)$b['id'] ?>'">View Details</button>
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
