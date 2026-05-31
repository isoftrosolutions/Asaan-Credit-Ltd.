<?php
require __DIR__ . '/../config/bootstrap.php';

$pageTitle = 'Browse Investors — ' . APP_NAME;
$pageDescription = 'Browse verified investors and buyers in Nepal. Connect with qualified investors for your business sale or funding needs.';

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Investors","item":"'.APP_URL.'/discover/investors.php"}
  ]
}</script>';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;

$sector = $_GET['sector'] ?? '';
$investorType = $_GET['investor_type'] ?? '';
$location = $_GET['location'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$where = ["u.role = 'investor'", "u.verification_status = 'verified'"];
$params = [];

if ($sector !== '') {
    $where[] = 'ip.preferred_sectors LIKE ?';
    $params[] = '%"' . $sector . '"%';
}
if ($investorType !== '') {
    $where[] = 'u.account_type = ?';
    $params[] = $investorType;
}
if ($location !== '') {
    $where[] = '(u.province LIKE ? OR u.district LIKE ?)';
    $loc = '%' . $location . '%';
    $params[] = $loc;
    $params[] = $loc;
}

$whereClause = implode(' AND ', $where);

switch ($sort) {
    case 'ticket_desc': $orderBy = 'ip.ticket_max DESC'; break;
    case 'ticket_asc': $orderBy = 'ip.ticket_min ASC'; break;
    default: $orderBy = 'u.created_at DESC';
}

$countSql = "SELECT COUNT(*) FROM users u JOIN investor_profiles ip ON u.id = ip.user_id WHERE $whereClause";
$countStmt = db()->prepare($countSql);
$countStmt->execute($params);

$p = paginate($countStmt, $page, $perPage);

$sql = "SELECT u.*, ip.*
        FROM users u
        JOIN investor_profiles ip ON u.id = ip.user_id
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT {$p['perPage']} OFFSET {$p['offset']}";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$investors = $stmt->fetchAll();

$activeFilters = 0;
if ($investorType !== '') $activeFilters++;
if ($sector !== '') $activeFilters++;
if ($location !== '') $activeFilters++;

$investorTypeLabels = [
    'individual' => 'Individual',
    'company' => 'Company',
    'lender' => 'Lender',
    'vc' => 'Venture Capital',
    'pe' => 'Private Equity',
    'family_office' => 'Family Office',
];

$baseUrl = '/discover/investors.php';
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
  <span>Investors &amp; Buyers</span>
</div>

<div class="container" style="padding-bottom:var(--space-8);">
  <div class="browse-title-bar">
    <h2>Investors &amp; Buyers</h2>
    <span class="result-pill"><?= number_format($p['total']) ?> verified investor<?= $p['total'] !== 1 ? 's' : '' ?></span>
  </div>

  <button type="button" class="filter-toggle-mobile" onclick="document.getElementById('filter-sidebar').classList.toggle('open')" aria-label="Toggle filters">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M2 14h4"/><path d="M10 8h4"/><path d="M18 16h4"/></svg>
    Filters<?= $activeFilters > 0 ? ' (' . $activeFilters . ')' : '' ?>
  </button>

  <form class="filter-layout" method="GET" action="">
    <div class="filter-sidebar" id="filter-sidebar">
      <h5>Investor Type</h5>
      <div class="filter-group">
        <label><input type="radio" name="investor_type" value="" <?= $investorType === '' ? 'checked' : '' ?> onchange="this.form.submit()"> All</label>
        <label><input type="radio" name="investor_type" value="individual" <?= $investorType === 'individual' ? 'checked' : '' ?> onchange="this.form.submit()"> Individual Investors</label>
        <label><input type="radio" name="investor_type" value="company" <?= $investorType === 'company' ? 'checked' : '' ?> onchange="this.form.submit()"> Companies</label>
        <label><input type="radio" name="investor_type" value="lender" <?= $investorType === 'lender' ? 'checked' : '' ?> onchange="this.form.submit()"> Lenders</label>
        <label><input type="radio" name="investor_type" value="vc" <?= $investorType === 'vc' ? 'checked' : '' ?> onchange="this.form.submit()"> Venture Capital</label>
        <label><input type="radio" name="investor_type" value="pe" <?= $investorType === 'pe' ? 'checked' : '' ?> onchange="this.form.submit()"> Private Equity</label>
        <label><input type="radio" name="investor_type" value="family_office" <?= $investorType === 'family_office' ? 'checked' : '' ?> onchange="this.form.submit()"> Family Offices</label>
      </div>

      <h5>Interested In Sector</h5>
      <div class="filter-group">
        <select name="sector" onchange="this.form.submit()">
          <option value="">All Sectors</option>
          <?php
          $sectors = db()->query("SELECT name FROM sectors WHERE is_active = 1 ORDER BY name")->fetchAll();
          foreach ($sectors as $s):
          ?>
            <option value="<?= e($s['name']) ?>" <?= $sector === $s['name'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <h5>Location</h5>
      <div class="filter-group">
        <input type="text" name="location" class="input" placeholder="Search location..." value="<?= e($location) ?>">
      </div>

      <button class="btn btn-primary btn-sm" style="width:100%;">Apply Filters</button>
      <a href="<?= APP_URL ?>/discover/investors.php" class="btn btn-ghost btn-sm" style="width:100%;display:block;text-align:center;">Reset</a>
    </div>

    <div>
      <?php if ($activeFilters > 0): ?>
      <div class="filter-chips">
        <?php if ($investorType !== ''): ?>
          <a href="<?= remove_query_param($baseUrl, 'investor_type') ?>" class="filter-chip">
            <?= $investorTypeLabels[$investorType] ?? e($investorType) ?>
            <span class="filter-chip-remove"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg></span>
          </a>
        <?php endif; ?>
        <?php if ($sector !== ''): ?>
          <a href="<?= remove_query_param($baseUrl, 'sector') ?>" class="filter-chip">
            <?= e($sector) ?>
            <span class="filter-chip-remove"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg></span>
          </a>
        <?php endif; ?>
        <?php if ($location !== ''): ?>
          <a href="<?= remove_query_param($baseUrl, 'location') ?>" class="filter-chip">
            <?= e($location) ?>
            <span class="filter-chip-remove"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg></span>
          </a>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/discover/investors.php" class="filter-chip" style="background:transparent;color:var(--color-text-muted);font-weight:500;">Clear all</a>
      </div>
      <?php endif; ?>

      <div class="sort-bar">
        <span class="result-count">
          Showing <strong><?= $p['total'] > 0 ? ($p['offset'] + 1) . '&ndash;' . min($p['offset'] + $perPage, $p['total']) : 0 ?></strong> of <strong><?= number_format($p['total']) ?></strong>
        </span>
        <div class="sort-controls">
          <span class="meta-label">Sort by:</span>
          <select name="sort" onchange="this.form.submit()">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Recently Joined</option>
            <option value="ticket_desc" <?= $sort === 'ticket_desc' ? 'selected' : '' ?>>Investment Size (Highest)</option>
            <option value="ticket_asc" <?= $sort === 'ticket_asc' ? 'selected' : '' ?>>Investment Size (Lowest)</option>
          </select>
        </div>
      </div>

      <?php if (empty($investors)): ?>
        <div class="empty-state-browse">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/><path d="M8 11h6"/><path d="M11 8v6"/></svg>
          <h3>No investors found</h3>
          <p>Try adjusting your filters to find investors that match your criteria.</p>
          <a href="<?= APP_URL ?>/discover/investors.php" class="btn btn-primary btn-sm">Clear All Filters</a>
        </div>
      <?php else: ?>
        <div class="listing-grid">
          <?php foreach ($investors as $inv): ?>
            <div class="browse-card" onclick="location.href='<?= APP_URL ?>/investor/public.php?id=<?= $inv['id'] ?>'">
              <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;">
                <div class="avatar avatar-sm"><?= e(mb_substr($inv['name'], 0, 2)) ?></div>
                <div style="flex:1;min-width:0;">
                  <div class="card-title" style="margin:0;font-size:0.95rem;"><?= e($inv['name']) ?></div>
                  <div class="card-meta" style="margin:0;">
                    <?= e($inv['company_name'] ?? '') ?>
                    <?php if ($inv['account_type']): ?>
                      &bull; <?= e(ucfirst($investorTypeLabels[$inv['account_type']] ?? $inv['account_type'])) ?>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="card-desc">
                <strong>Interests:</strong> <?= e(implode(', ', json_decode($inv['preferred_sectors'] ?? '[]', true) ?: [])) ?>.
                <?= e(mb_substr($inv['bio'] ?? '', 0, 120)) ?>
              </div>
              <div style="display:flex;justify-content:space-between;font-size:0.8rem;flex-wrap:wrap;gap:0.5rem;margin-top:0.5rem;">
                <span class="meta-label"><?= e($inv['province'] ?? '') ?><?= $inv['district'] ? ', ' . e($inv['district']) : '' ?></span>
                <span class="meta-label" style="font-weight:700;color:var(--color-text);"><?= money($inv['ticket_min'] ?? 0) ?> &ndash; <?= money($inv['ticket_max'] ?? 0) ?></span>
              </div>
              <div class="card-footer">
                <button class="btn btn-accent btn-sm" onclick="event.stopPropagation();location.href='<?= APP_URL ?>/investor/public.php?id=<?= $inv['id'] ?>'" style="width:100%;">View Profile</button>
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
