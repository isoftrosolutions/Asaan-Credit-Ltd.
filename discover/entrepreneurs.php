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

$sql = "SELECT p.*, s.name as sector_name, u.name as user_name, u.profile_photo
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

$stages = ['idea', 'seed', 'early', 'growth', 'expansion', 'pre_ipo'];

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

<style>
.pitch-card {
  background: rgba(255,255,255,0.04);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: var(--radius-lg);
  padding: 1.25rem;
  cursor: pointer;
  transition: transform 220ms var(--ease-out), box-shadow 220ms var(--ease-out), border-color 220ms var(--ease-out);
  display: flex;
  flex-direction: column;
}
.pitch-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.1);
  border-color: var(--color-primary-vivid);
}

.pitch-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.pitch-avatar-initials {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--color-primary), var(--color-primary-vivid));
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.pitch-contact-row {
  display: flex;
  gap: 6px;
  margin-bottom: 0.75rem;
}
.pitch-contact-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--dash-ink-soft);
  font-size: 0.85rem;
  transition: background 200ms ease, color 200ms ease;
}
.pitch-contact-icon:hover {
  background: var(--color-primary-vivid);
  color: #fff;
}

.pitch-name {
  font-weight: 700;
  font-size: 1rem;
  color: var(--dash-ink);
}
.pitch-role {
  font-size: 0.8rem;
  color: var(--dash-ink-soft);
  margin-top: 2px;
}

.pitch-section-label {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--dash-ink-soft);
  margin-bottom: 4px;
}
.pitch-section-text {
  font-size: 0.85rem;
  color: var(--dash-ink);
  line-height: 1.5;
}

.pitch-location {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.8rem;
  color: var(--dash-ink-soft);
  margin: 0.75rem 0;
}
.pitch-rating {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.8rem;
  color: var(--color-warning);
  margin-bottom: 0.75rem;
}

.pitch-data-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem;
  padding: 0.75rem 0;
  border-top: 1px solid var(--dash-border);
  border-bottom: 1px solid var(--dash-border);
  margin-bottom: 0.75rem;
}
.pitch-data-label {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--dash-ink-soft);
}
.pitch-data-value {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--dash-ink);
}

.pitch-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
}
.pitch-funding-label {
  font-size: 0.7rem;
  color: var(--dash-ink-soft);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.pitch-funding {
  font-size: 1rem;
  font-weight: 800;
  color: var(--color-primary-vivid);
}
.btn-send-proposal {
  padding: 8px 18px;
  background: var(--color-primary);
  color: #fff;
  border: none;
  border-radius: var(--radius-md);
  font-weight: 600;
  font-size: 0.8rem;
  cursor: pointer;
  transition: transform 160ms var(--ease-out), background 160ms var(--ease-out);
  white-space: nowrap;
}
.btn-send-proposal:active {
  transform: scale(0.95);
}
.btn-send-proposal:hover {
  background: #4A1317;
}

.filter-sidebar {
  background: rgba(255,255,255,0.03);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: var(--radius-lg);
  padding: 1.25rem;
}

@media (max-width: 768px) {
  .browse-grid { grid-template-columns: 1fr; }
  .pitch-data-grid { grid-template-columns: 1fr; }
  .pitch-footer { flex-direction: column; gap: 0.75rem; align-items: stretch; }
  .btn-send-proposal { width: 100%; text-align: center; }
}
</style>

<div class="container" style="padding-bottom:var(--space-8);">
  <div class="browse-title-bar">
    <h2>Browse Entrepreneurs</h2>
    <span class="result-pill"><?= number_format($p['total']) ?> verified pitche<?= $p['total'] !== 1 ? 's' : '' ?></span>
  </div>

  <button type="button" class="filter-toggle-mobile" onclick="document.getElementById('filter-sidebar').classList.toggle('open')" aria-label="Toggle filters">
    <i class="fas fa-search"></i>
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
            <option value="<?= e($st) ?>" <?= $stage === $st ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $st))) ?></option>
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
            <span class="filter-chip-remove"><i class="fas fa-times"></i></span>
          </a>
        <?php endif; ?>
        <?php if ($stage !== ''): ?>
          <a href="<?= remove_query_param($baseUrl, 'stage') ?>" class="filter-chip">
            <?= e($stage) ?>
            <span class="filter-chip-remove"><i class="fas fa-times"></i></span>
          </a>
        <?php endif; ?>
        <?php if ($fundMin !== '' || $fundMax !== ''): ?>
          <a href="<?= remove_query_param(remove_query_param($baseUrl, 'fund_min'), 'fund_max') ?>" class="filter-chip">
            NPR <?= $fundMin !== '' ? number_format((float)$fundMin) : '0' ?> &ndash; NPR <?= $fundMax !== '' ? number_format((float)$fundMax) : 'Any' ?>
            <span class="filter-chip-remove"><i class="fas fa-times"></i></span>
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
          <i class="fas fa-sliders-h" style="font-size:48px;color:var(--color-text-muted);margin-bottom:1rem;"></i>
          <h3>No pitches found</h3>
          <p>Try adjusting your filters or criteria to discover more entrepreneurs seeking funding.</p>
          <a href="<?= APP_URL ?>/discover/entrepreneurs.php" class="btn btn-primary btn-sm">Clear All Filters</a>
        </div>
      <?php else: ?>
        <div class="browse-grid">
          <?php foreach ($pitches as $pitch): ?>
            <div class="pitch-card" onclick="location.href='<?= APP_URL ?>/pitch/<?= $pitch['id'] ?>'">
              <div class="pitch-header">
                <?php if (!empty($pitch['profile_photo'])): ?>
                  <img class="pitch-avatar" src="<?= e($pitch['profile_photo']) ?>" alt="">
                <?php else: ?>
                  <div class="pitch-avatar-initials"><?= e(mb_substr($pitch['user_name'] ?? 'EN', 0, 2)) ?></div>
                <?php endif; ?>
                <div style="flex:1;min-width:0;">
                  <div class="pitch-name"><?= e($pitch['user_name'] ?? 'Entrepreneur') ?></div>
                  <div class="pitch-role"><?= e(ucfirst(str_replace('_', ' ', $pitch['stage'] ?? ''))) ?> &middot; <?= e($pitch['sector_name'] ?? '') ?></div>
                </div>
              </div>
              <div class="pitch-contact-row">
                <div class="pitch-contact-icon" title="Email">
                  <i class="fas fa-envelope"></i>
                </div>
                <div class="pitch-contact-icon" title="Phone">
                  <i class="fas fa-phone"></i>
                </div>
              </div>
              <div class="pitch-section">
                <div class="pitch-section-label">Background</div>
                <div class="pitch-section-text"><?= e(mb_substr($pitch['short_summary'] ?? $pitch['problem_statement'] ?? '', 0, 200)) ?></div>
              </div>
              <div class="pitch-location">
                <i class="fas fa-map-marker-alt" style="font-size:15px;"></i>
                Nepal
              </div>
              <div class="pitch-rating">
                <i class="fas fa-star"></i>
                <span>Verified</span>
              </div>
              <div class="pitch-data-grid">
                <div class="pitch-data-item">
                  <span class="pitch-data-label">Sector</span>
                  <span class="pitch-data-value"><?= e($pitch['sector_name'] ?? '—') ?></span>
                </div>
                <div class="pitch-data-item">
                  <span class="pitch-data-label">Stage</span>
                  <span class="pitch-data-value"><?= e(ucfirst(str_replace('_', ' ', $pitch['stage'] ?? ''))) ?></span>
                </div>
              </div>
              <div class="pitch-footer">
                <div>
                  <div class="pitch-funding-label">Funding Range</div>
                  <div class="pitch-funding"><?= money($pitch['funding_amount'] ?? 0) ?><?= $pitch['equity_offered'] ? ' for ' . e($pitch['equity_offered']) . '%' : '' ?></div>
                </div>
                <button type="button" class="btn-send-proposal" onclick="event.stopPropagation();location.href='<?= APP_URL ?>/pitch/<?= $pitch['id'] ?>'">Send Proposal</button>
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
