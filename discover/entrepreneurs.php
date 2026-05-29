<?php
require __DIR__ . '/../config/bootstrap.php';

$pageTitle = 'Browse Entrepreneurs — Asaan Marketplace';

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

$baseUrl = '/discover/entrepreneurs.php';
$queryParams = $_GET;
unset($queryParams['page']);
if ($queryParams) {
    $baseUrl .= '?' . http_build_query($queryParams);
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="breadcrumbs container">
  <a href="<?= APP_URL ?>">Home</a> <span>/</span>
  <span>Entrepreneurs &amp; Pitches</span>
</div>

<div class="container" style="padding-bottom:3rem;">
  <div style="display:flex; gap:1rem; align-items:center; margin-bottom:1.5rem;">
    <h2 style="margin:0;">Browse Entrepreneurs</h2>
    <div style="background:#f0edeb; padding:4px 14px; border-radius:999px; font-size:0.8rem;"><?= $p['total'] ?> verified pitches</div>
  </div>

  <form method="GET" action="" style="display:flex; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap;">
    <select name="sector_id" class="select" style="width:180px;" onchange="this.form.submit()">
      <option value="">All Sectors</option>
      <?php foreach ($sectors as $s): ?>
        <option value="<?= $s['id'] ?>" <?= (string)$s['id'] === $sectorId ? 'selected' : '' ?>><?= e($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="stage" class="select" style="width:160px;" onchange="this.form.submit()">
      <option value="">Any Stage</option>
      <?php foreach ($stages as $st): ?>
        <option value="<?= e($st) ?>" <?= $stage === $st ? 'selected' : '' ?>><?= e($st) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="number" name="fund_min" placeholder="Min funding" value="<?= e($fundMin) ?>" class="input" style="width:130px;border-bottom:1px solid #ccc;">
    <input type="number" name="fund_max" placeholder="Max funding" value="<?= e($fundMax) ?>" class="input" style="width:130px;border-bottom:1px solid #ccc;">
    <button class="btn btn-secondary btn-sm">Apply Filters</button>
    <a href="<?= APP_URL ?>/discover/entrepreneurs.php" class="btn btn-ghost btn-sm">Reset</a>

    <div style="margin-left:auto;display:flex;align-items:center;gap:0.5rem;">
      <span class="meta-label">Sort by:</span>
      <select name="sort" onchange="this.form.submit()">
        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
        <option value="fund_asc" <?= $sort === 'fund_asc' ? 'selected' : '' ?>>Funding (Lowest)</option>
        <option value="fund_desc" <?= $sort === 'fund_desc' ? 'selected' : '' ?>>Funding (Highest)</option>
      </select>
    </div>
  </form>

  <?php if (empty($pitches)): ?>
    <p style="text-align:center;padding:3rem 0;color:#888;">No pitches found matching your criteria.</p>
  <?php else: ?>
    <div class="browse-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(340px, 1fr)); gap:1.25rem;">
      <?php foreach ($pitches as $pitch): ?>
        <div class="card pitch-card" onclick="location.href='<?= APP_URL ?>/entrepreneur/pitch/<?= $pitch['id'] ?>'">
          <div class="header">
            <div class="avatar"><?= e(mb_substr($pitch['user_name'] ?? '', 0, 2)) ?></div>
            <div style="flex:1">
              <strong><?= e(mb_substr($pitch['tagline'] ?? $pitch['short_summary'] ?? '', 0, 60)) ?></strong>
              <div class="text-xs"><?= e($pitch['sector_name'] ?? '') ?> &bull; Verified</div>
            </div>
            <?php if ($user): ?>
              <button class="btn btn-sm <?= in_array($pitch['id'], $savedIds) ? 'btn-primary' : 'btn-ghost' ?>" onclick="event.stopPropagation();fetch('<?= APP_URL ?>/api/toggle-save.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'listing_type=pitch&listing_id=<?= $pitch['id'] ?>&_csrf=<?= csrf_token() ?>'}).then(r=>r.json()).then(d=>{if(d.saved){this.classList.remove('btn-ghost');this.classList.add('btn-primary')}else{this.classList.remove('btn-primary');this.classList.add('btn-ghost')}})">
                <?= in_array($pitch['id'], $savedIds) ? 'Saved' : 'Save' ?>
              </button>
            <?php endif; ?>
          </div>
          <div style="margin:0.75rem 0;"><?= e(mb_substr($pitch['short_summary'] ?? $pitch['problem_statement'] ?? '', 0, 200)) ?></div>
          <div style="display:flex; justify-content:space-between; font-size:0.85rem;">
            <div><strong><?= money($pitch['funding_amount'] ?? 0) ?></strong><?= $pitch['equity_offered'] ? ' for ' . e($pitch['equity_offered']) . '%' : '' ?></div>
            <div style="color:var(--accent); font-weight:700;"><?= e($pitch['stage'] ?? '') ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div style="text-align:center; margin-top:2rem;">
    <?= render_pagination($p['page'], $p['lastPage'], $baseUrl) ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
