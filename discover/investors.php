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
  <h2 style="margin-bottom:0.25rem;">Investors &amp; Buyers</h2>
  <p style="margin-top:0;font-size:0.9rem;">Pre-verified investors, buyers, lenders, and advisors actively looking for opportunities.</p>

  <form class="filter-layout" style="margin-top:1.5rem;" method="GET" action="">
    <div class="filter-sidebar">
      <h5 style="margin-top:0;">Investor Type</h5>
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
        <select name="sector" class="input" style="border-bottom:1px solid var(--color-border);padding:0.5rem 0;font-size:0.85rem;" onchange="this.form.submit()">
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
        <input type="text" name="location" placeholder="Search location..." value="<?= e($location) ?>" class="input" style="border-bottom:1px solid var(--color-border);padding:0.5rem 0;font-size:0.85rem;">
      </div>

      <button class="btn btn-primary btn-sm" style="width:100%;margin-top:0.5rem;">Apply Filters</button>
      <a href="<?= APP_URL ?>/discover/investors.php" class="btn btn-ghost btn-sm" style="width:100%;display:block;text-align:center;">Reset</a>
    </div>

    <div>
      <div class="sort-bar">
        <span>Showing <?= ($p['offset'] + 1) ?> &ndash; <?= min($p['offset'] + $perPage, $p['total']) ?> of <?= $p['total'] ?></span>
        <div style="display:flex;align-items:center;gap:0.5rem;">
          <span class="meta-label">Sort by:</span>
          <select name="sort" onchange="this.form.submit()">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Recently Joined</option>
            <option value="ticket_desc" <?= $sort === 'ticket_desc' ? 'selected' : '' ?>>Investment Size (Highest)</option>
            <option value="ticket_asc" <?= $sort === 'ticket_asc' ? 'selected' : '' ?>>Investment Size (Lowest)</option>
          </select>
        </div>
      </div>

      <?php if (empty($investors)): ?>
        <p style="text-align:center;padding:3rem 0;color:var(--color-text-muted);">No investors found matching your criteria.</p>
      <?php else: ?>
        <div class="listing-grid">
          <?php foreach ($investors as $inv): ?>
            <div class="card" onclick="location.href='<?= APP_URL ?>/investor/public.php?id=<?= $inv['id'] ?>'">
              <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.5rem;">
                <div class="avatar avatar-sm"><?= e(mb_substr($inv['name'], 0, 2)) ?></div>
                <div style="flex:1;">
                  <strong><?= e($inv['name']) ?></strong>
                  <div style="font-size:0.8rem;color:var(--color-text-muted);">
                    <?= e($inv['company_name'] ?? '') ?>
                    <?php if ($inv['account_type']): ?>
                      <span class="tag" style="font-size:0.7rem;"><?= e(ucfirst($inv['account_type'])) ?> in <?= e($inv['province'] ?? '') ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <p style="font-size:0.85rem;margin:0.5rem 0;">
                <strong>Interests:</strong> <?= e(implode(', ', json_decode($inv['preferred_sectors'] ?? '[]', true) ?: [])) ?>.
                <?= e(mb_substr($inv['bio'] ?? '', 0, 150)) ?>
              </p>
              <div style="display:flex;justify-content:space-between;font-size:0.85rem;flex-wrap:wrap;">
                <span class="meta-label">Location: <?= e($inv['province'] ?? '') ?><?= $inv['district'] ? ', ' . e($inv['district']) : '' ?></span>
                <span class="meta-label">Investment: <?= money($inv['ticket_min'] ?? 0) ?> &ndash; <?= money($inv['ticket_max'] ?? 0) ?></span>
              </div>
              <div style="margin-top:0.75rem;">
                <button class="btn btn-accent btn-sm" style="width:100%;" onclick="event.stopPropagation();location.href='<?= APP_URL ?>/investor/public.php?id=<?= $inv['id'] ?>'">View Profile</button>
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
