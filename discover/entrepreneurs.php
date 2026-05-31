<?php
require __DIR__ . '/../config/bootstrap.php';

$pageTitle = 'Browse Entrepreneurs — ' . APP_NAME;
$pageDescription = 'Browse investment opportunities from pre-verified entrepreneurs in Nepal seeking capital for growth across multiple sectors.';

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Entrepreneurs","item":"'.APP_URL.'/discover/entrepreneurs.php"}
  ]
}</script>';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;

$sectorId = $_GET['sector_id'] ?? '';
$stage = $_GET['stage'] ?? '';
$fundMin = $_GET['fund_min'] ?? '';
$fundMax = $_GET['fund_max'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$where = ['p.is_published = 1', 'p.is_hidden = 0'];
$params = [];

if ($sectorId !== '') {
    $where[] = 'p.sector_id = ?';
    $params[] = (int)$sectorId;
}
if ($stage !== '') {
    $where[] = 'p.stage = ?';
    $params[] = $stage;
}
if ($fundMin !== '') {
    $where[] = 'p.funding_amount >= ?';
    $params[] = (float)$fundMin;
}
if ($fundMax !== '') {
    $where[] = 'p.funding_amount <= ?';
    $params[] = (float)$fundMax;
}

$whereClause = implode(' AND ', $where);

switch ($sort) {
    case 'fund_asc': $orderBy = 'p.funding_amount ASC'; break;
    case 'fund_desc': $orderBy = 'p.funding_amount DESC'; break;
    default: $orderBy = 'p.created_at DESC';
}

$countSql = "SELECT COUNT(*) FROM pitches p WHERE $whereClause";
$countStmt = db()->prepare($countSql);
$countStmt->execute($params);

$p = paginate($countStmt, $page, $perPage);

$sql = "SELECT p.*, s.name as sector_name, u.name as user_name
        FROM pitches p
        LEFT JOIN sectors s ON p.sector_id = s.id
        JOIN users u ON p.user_id = u.id
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT {$p['perPage']} OFFSET {$p['offset']}";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$pitches = $stmt->fetchAll();

$user = current_user();
$savedIds = [];
if ($user) {
    $sStmt = db()->prepare("SELECT listing_id FROM saved_listings WHERE user_id = ? AND listing_type = 'pitch'");
    $sStmt->execute([$user['id']]);
    $savedIds = array_column($sStmt->fetchAll(), 'listing_id');
}

$sectors = db()->query("SELECT id, name FROM sectors WHERE is_active = 1 ORDER BY name")->fetchAll();

$stages = ['Idea', 'MVP', 'Early Revenue', 'Growth', 'Seed', 'Series A', 'Series B'];

$activeFilters = 0;
if ($sectorId !== '') $activeFilters++;
if ($stage !== '') $activeFilters++;
if ($fundMin !== '' || $fundMax !== '') $activeFilters++;

$baseUrl = '/discover/entrepreneurs.php';
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
  <span>Entrepreneurs &amp; Pitches</span>
</div>

<div class="container" style="padding-bottom:var(--space-8);">
  <div class="browse-title-bar">
    <h2>Browse Entrepreneurs</h2>
    <span class="result-pill"><?= number_format($p['total']) ?> verified pitche<?= $p['total'] !== 1 ? 's' : '' ?></span>
  </div>

  <button type="button" class="filter-toggle-mobile" onclick="document.getElementById('filter-sidebar').classList.toggle('open')" aria-label="Toggle filters">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M2 14h4"/><path d="M10 8h4"/><path d="M18 16h4"/></svg>
    Filters<?= $activeFilters > 0 ? ' (' . $activeFilters . ')' : '' ?>
  </button>

  <form method="GET" action="" class="filter-layout">
    <div class="filter-sidebar" id="filter-sidebar">
      <h5>Sector</h5>
      <div class="filter-group">
        <select name="sector_id" onchange="this.form.submit()">
          <option value="">All Sectors</option>
          <?php foreach ($sectors as $s): ?>
            <option value="<?= $s['id'] ?>" <?= (string)$s['id'] === $sectorId ? 'selected' : '' ?>><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <h5>Stage</h5>
      <div class="filter-group">
        <select name="stage" onchange="this.form.submit()">
          <option value="">Any Stage</option>
          <?php foreach ($stages as $st): ?>
            <option value="<?= e($st) ?>" <?= $stage === $st ? 'selected' : '' ?>><?= e($st) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <h5>Funding Range</h5>
      <div class="filter-group" style="display:flex;gap:0.5rem;">
        <input type="number" name="fund_min" class="input" placeholder="Min" value="<?= e($fundMin) ?>" style="width:48%;">
        <input type="number" name="fund_max" class="input" placeholder="Max" value="<?= e($fundMax) ?>" style="width:48%;">
      </div>

      <button class="btn btn-primary btn-sm" style="width:100%;">Apply Filters</button>
      <a href="<?= APP_URL ?>/discover/entrepreneurs.php" class="btn btn-ghost btn-sm" style="width:100%;display:block;text-align:center;">Reset</a>
    </div>

    <div>
      <?php if ($activeFilters > 0): ?>
      <div class="filter-chips">
        <?php if ($sectorId !== ''): $selSector = current(array_filter($sectors, fn($s) => (string)$s['id'] === $sectorId)); ?>
          <a href="<?= remove_query_param($baseUrl, 'sector_id') ?>" class="filter-chip">
            <?= e($selSector['name'] ?? $sectorId) ?>
            <span class="filter-chip-remove"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg></span>
          </a>
        <?php endif; ?>
        <?php if ($stage !== ''): ?>
          <a href="<?= remove_query_param($baseUrl, 'stage') ?>" class="filter-chip">
            <?= e($stage) ?>
            <span class="filter-chip-remove"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg></span>
          </a>
        <?php endif; ?>
        <?php if ($fundMin !== '' || $fundMax !== ''): ?>
          <a href="<?= remove_query_param(remove_query_param($baseUrl, 'fund_min'), 'fund_max') ?>" class="filter-chip">
            NPR <?= $fundMin !== '' ? number_format((float)$fundMin) : '0' ?> &ndash; NPR <?= $fundMax !== '' ? number_format((float)$fundMax) : 'Any' ?>
            <span class="filter-chip-remove"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg></span>
          </a>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/discover/entrepreneurs.php" class="filter-chip" style="background:transparent;color:var(--color-text-muted);font-weight:500;">Clear all</a>
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
            <option value="fund_asc" <?= $sort === 'fund_asc' ? 'selected' : '' ?>>Funding (Lowest)</option>
            <option value="fund_desc" <?= $sort === 'fund_desc' ? 'selected' : '' ?>>Funding (Highest)</option>
          </select>
        </div>
      </div>

      <?php if (empty($pitches)): ?>
        <div class="empty-state-browse">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/><path d="M8 11h6"/><path d="M11 8v6"/></svg>
          <h3>No pitches found</h3>
          <p>Try adjusting your filters or criteria to discover more entrepreneurs seeking funding.</p>
          <a href="<?= APP_URL ?>/discover/entrepreneurs.php" class="btn btn-primary btn-sm">Clear All Filters</a>
        </div>
      <?php else: ?>
        <div class="browse-grid">
          <?php foreach ($pitches as $pitch): ?>
            <div class="pitch-card" onclick="location.href='<?= APP_URL ?>/pitch/<?= $pitch['id'] ?>'">
              <div class="pitch-header">
                <div class="avatar avatar-sm"><?= e(mb_substr($pitch['user_name'] ?? '', 0, 2)) ?></div>
                <div style="flex:1;min-width:0;">
                  <strong style="font-size:0.95rem;"><?= e(mb_substr($pitch['tagline'] ?? $pitch['short_summary'] ?? '', 0, 60)) ?></strong>
                  <div class="text-xs"><?= e($pitch['sector_name'] ?? '') ?> &bull; Verified</div>
                </div>
                <?php if ($user): ?>
                  <button class="btn btn-sm <?= in_array($pitch['id'], $savedIds) ? 'btn-primary' : 'btn-ghost' ?>" onclick="event.stopPropagation();fetch('<?= APP_URL ?>/api/toggle-save.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'listing_type=pitch&listing_id=<?= $pitch['id'] ?>&_csrf=<?= csrf_token() ?>'}).then(r=>r.json()).then(d=>{if(d.saved){this.classList.remove('btn-ghost');this.classList.add('btn-primary')}else{this.classList.remove('btn-primary');this.classList.add('btn-ghost')}})">
                    <?= in_array($pitch['id'], $savedIds) ? 'Saved' : 'Save' ?>
                  </button>
                <?php endif; ?>
              </div>
              <div class="pitch-summary"><?= e(mb_substr($pitch['short_summary'] ?? $pitch['problem_statement'] ?? '', 0, 200)) ?></div>
              <div style="display:flex;justify-content:space-between;align-items:center;margin-top:auto;padding-top:0.75rem;border-top:1px solid var(--color-border);">
                <div>
                  <span class="pitch-amount"><?= money($pitch['funding_amount'] ?? 0) ?></span>
                  <?php if ($pitch['equity_offered']): ?>
                    <span class="pitch-equity"> for <?= e($pitch['equity_offered']) ?>%</span>
                  <?php endif; ?>
                </div>
                <span class="pitch-stage"><?= e($pitch['stage'] ?? '') ?></span>
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
