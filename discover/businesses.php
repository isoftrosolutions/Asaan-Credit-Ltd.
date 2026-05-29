<?php
require __DIR__ . '/../config/bootstrap.php';

$pageTitle = 'Browse Businesses — Asaan Marketplace';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;

$sectorId = $_GET['sector_id'] ?? '';
$province = $_GET['province'] ?? '';
$listingType = $_GET['listing_type'] ?? '';
$priceMin = $_GET['price_min'] ?? '';
$priceMax = $_GET['price_max'] ?? '';
$keyword = $_GET['keyword'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$where = ['b.is_published = 1', 'b.is_hidden = 0'];
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

$baseUrl = '/discover/businesses.php';
$queryParams = $_GET;
unset($queryParams['page']);
if ($queryParams) {
    $baseUrl .= '?' . http_build_query($queryParams);
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="breadcrumbs container">
  <a href="<?= APP_URL ?>">Home</a> <span>/</span>
  <span>Businesses</span>
</div>

<div class="container" style="padding-bottom:3rem;">
  <h2 style="margin-bottom:0.25rem;">Businesses for Sale and Investment</h2>
  <p style="margin-top:0;font-size:0.9rem;">Showing <?= $p['total'] ?: 0 ?> businesses. Buy or invest in a business.</p>

  <form class="filter-layout" style="margin-top:1.5rem;" method="GET" action="">
    <div class="filter-sidebar">
      <h5 style="margin-top:0;">Transaction Type</h5>
      <div class="filter-group">
        <label><input type="radio" name="listing_type" value="" <?= $listingType === '' ? 'checked' : '' ?> onchange="this.form.submit()"> All</label>
        <label><input type="radio" name="listing_type" value="sale" <?= $listingType === 'sale' ? 'checked' : '' ?> onchange="this.form.submit()"> Businesses For Sale</label>
        <label><input type="radio" name="listing_type" value="partial_stake" <?= $listingType === 'partial_stake' ? 'checked' : '' ?> onchange="this.form.submit()"> Partial Stake Sale</label>
        <label><input type="radio" name="listing_type" value="loan" <?= $listingType === 'loan' ? 'checked' : '' ?> onchange="this.form.submit()"> Business Loan</label>
        <label><input type="radio" name="listing_type" value="asset_sale" <?= $listingType === 'asset_sale' ? 'checked' : '' ?> onchange="this.form.submit()"> Assets For Sale</label>
      </div>

      <h5>Industry</h5>
      <div class="filter-group">
        <select name="sector_id" class="input" style="border-bottom:1px solid #ccc;padding:0.5rem 0;font-size:0.85rem;" onchange="this.form.submit()">
          <option value="">All Industries</option>
          <?php foreach ($sectors as $s): ?>
            <option value="<?= $s['id'] ?>" <?= (string)$s['id'] === $sectorId ? 'selected' : '' ?>><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <h5>Location</h5>
      <div class="filter-group">
        <select name="province" class="input" style="border-bottom:1px solid #ccc;padding:0.5rem 0;font-size:0.85rem;" onchange="this.form.submit()">
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
        <input type="number" name="price_min" placeholder="Min" value="<?= e($priceMin) ?>" class="input" style="padding:0.4rem;font-size:0.85rem;width:48%;">
        <input type="number" name="price_max" placeholder="Max" value="<?= e($priceMax) ?>" class="input" style="padding:0.4rem;font-size:0.85rem;width:48%;">
      </div>

      <button class="btn btn-primary btn-sm" style="width:100%;margin-top:0.5rem;">Apply Filters</button>
      <a href="<?= APP_URL ?>/discover/businesses.php" class="btn btn-ghost btn-sm" style="width:100%;display:block;text-align:center;">Reset</a>
    </div>

    <div>
      <div class="sort-bar">
        <span>Showing <?= ($p['offset'] + 1) ?> &ndash; <?= min($p['offset'] + $perPage, $p['total']) ?> of <?= $p['total'] ?></span>
        <div style="display:flex;align-items:center;gap:0.5rem;">
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
        <p style="text-align:center;padding:3rem 0;color:#888;">No businesses found matching your criteria.</p>
      <?php else: ?>
        <div class="listing-grid">
          <?php foreach ($businesses as $b): ?>
            <div class="card business-card" onclick="location.href='<?= APP_URL ?>/business/detail.php?id=<?= $b['id'] ?>'">
              <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:0.5rem;">
                <span>
                  <?php
                  $badgeMap = [
                    'sale' => 'Business for Sale',
                    'partial_stake' => 'Partial Stake Sale',
                    'loan' => 'Business Loan',
                    'asset_sale' => 'Asset Sale',
                  ];
                  $badgeClass = [
                    'sale' => 'tx-badge-sale',
                    'partial_stake' => 'tx-badge-partial',
                    'loan' => 'tx-badge-loan',
                    'asset_sale' => 'tx-badge-asset',
                  ];
                  $label = $badgeMap[$b['listing_type']] ?? e($b['listing_type']);
                  $cls = $badgeClass[$b['listing_type']] ?? '';
                  ?>
                  <span class="tx-badge <?= $cls ?>"><?= $label ?></span>
                  <?php if (!empty($b['is_featured'])): ?>
                    <span class="premium-ribbon" style="margin-left:4px;">PREMIUM</span>
                  <?php endif; ?>
                </span>
                <span class="rating-badge"><?= e($b['rating']) ?> <?= e($b['province'] ?? '') ?></span>
              </div>
              <h4 style="margin:0.5rem 0 0.25rem;"><?= e($b['business_name']) ?></h4>
              <p style="font-size:0.85rem;margin:0 0 0.5rem;"><?= e(mb_substr($b['description'] ?? '', 0, 120)) ?><?= mb_strlen($b['description'] ?? '') > 120 ? '...' : '' ?></p>
              <?php if (!empty($b['sector_name'])): ?>
                <div style="font-size:0.8rem;color:var(--on-surface-variant);margin-bottom:0.5rem;">Sector: <?= e($b['sector_name']) ?></div>
              <?php endif; ?>
              <div style="margin-top:0.75rem;padding-top:0.75rem;border-top:1px solid var(--surface-container-high);display:flex;justify-content:space-between;align-items:center;">
                <strong style="font-size:1.05rem;"><?= money($b['asking_price']) ?></strong>
                <div style="display:flex;gap:0.5rem;align-items:center;">
                  <?php if ($user): ?>
                    <button class="btn btn-sm <?= in_array($b['id'], $savedIds) ? 'btn-primary' : 'btn-ghost' ?>" onclick="event.stopPropagation();fetch('<?= APP_URL ?>/api/toggle-save.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'listing_type=business&listing_id=<?= $b['id'] ?>&_csrf=<?= csrf_token() ?>'}).then(r=>r.json()).then(d=>{if(d.saved){this.classList.remove('btn-ghost');this.classList.add('btn-primary')}else{this.classList.remove('btn-primary');this.classList.add('btn-ghost')}})">
                      <?= in_array($b['id'], $savedIds) ? 'Saved' : 'Save' ?>
                    </button>
                  <?php endif; ?>
                  <button class="btn btn-accent btn-sm" onclick="event.stopPropagation();location.href='<?= APP_URL ?>/business/detail.php?id=<?= $b['id'] ?>'">View Details</button>
                </div>
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
