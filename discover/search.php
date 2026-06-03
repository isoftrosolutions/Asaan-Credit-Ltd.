<?php
require __DIR__ . '/../config/bootstrap.php';

$pageTitle = 'Search Results — ' . APP_NAME;
$pageDescription = 'Search results for businesses, investors, franchises, and investment opportunities on Asaan Capital Ltd.';

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Search","item":"'.APP_URL.'/discover/search.php"}
  ]
}</script>';

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$user = current_user();

if ($q === '') {
    include __DIR__ . '/../includes/header.php';
    ?>
    <div class="container" style="padding:3rem 0; text-align:center; max-width:620px; margin:0 auto;">
      <h2 style="margin-bottom:0.5rem;">Enter a search term</h2>
      <form method="GET" action="">
        <input type="text" name="q" class="input" placeholder="Search businesses, pitches, franchises..." style="width:100%;padding:0.75rem;font-size:1rem;">
        <button class="btn btn-primary" style="margin-top:1rem;">Search</button>
      </form>
    </div>
    <?php
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$kw = '%' . $q . '%';

$countSql = "SELECT COUNT(*) FROM (
    SELECT id, 'business' as type FROM businesses WHERE is_published = 1 AND is_hidden = 0 AND (business_name LIKE ? OR description LIKE ?)
    UNION ALL
    SELECT id, 'pitch' as type FROM pitches WHERE is_published = 1 AND is_hidden = 0 AND (short_summary LIKE ? OR problem_statement LIKE ? OR solution LIKE ?)
    UNION ALL
    SELECT id, 'franchise' as type FROM franchises WHERE is_published = 1 AND is_hidden = 0 AND (brand_name LIKE ? OR description LIKE ?)
) t";
$countStmt = db()->prepare($countSql);
$countStmt->execute([$kw, $kw, $kw, $kw, $kw, $kw, $kw]);

$p = paginate($countStmt, $page, $perPage);

$sql = "SELECT id, business_name as title, description, 'business' as type, created_at, asking_price as price_field, sector_id FROM businesses WHERE is_published = 1 AND is_hidden = 0 AND (business_name LIKE ? OR description LIKE ?)
        UNION ALL
        SELECT id, short_summary as title, CONCAT(problem_statement, ' ', solution) as description, 'pitch' as type, created_at, funding_amount as price_field, sector_id FROM pitches WHERE is_published = 1 AND is_hidden = 0 AND (short_summary LIKE ? OR problem_statement LIKE ? OR solution LIKE ?)
        UNION ALL
        SELECT id, brand_name as title, description, 'franchise' as type, created_at, total_investment_min as price_field, sector_id FROM franchises WHERE is_published = 1 AND is_hidden = 0 AND (brand_name LIKE ? OR description LIKE ?)
        ORDER BY created_at DESC
        LIMIT {$p['perPage']} OFFSET {$p['offset']}";
$stmt = db()->prepare($sql);
$stmt->execute([$kw, $kw, $kw, $kw, $kw, $kw, $kw]);
$results = $stmt->fetchAll();

$typeLabels = [
    'business' => ['label' => 'Business', 'url' => '/business/detail.php?id='],
    'pitch' => ['label' => 'Pitch', 'url' => '/entrepreneur/pitch/'],
    'franchise' => ['label' => 'Franchise', 'url' => '/franchise/detail/'],
];

$baseUrl = '/discover/search.php?q=' . urlencode($q);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?= $breadcrumbSchema ?>
<div class="breadcrumbs container">
  <a href="<?= APP_URL ?>">Home</a> <span>/</span>
  <a href="<?= APP_URL ?>/discover/search.php">Search</a> <span>/</span>
  <span><?= e($q) ?></span>
</div>

<div class="container" style="padding-bottom:var(--space-8);">
  <form method="GET" action="" style="margin-bottom:2rem;">
    <div style="display:flex;gap:0.75rem;max-width:600px;">
      <input type="text" name="q" class="input" value="<?= e($q) ?>" placeholder="Search businesses, pitches, franchises..." style="flex:1;padding:0.75rem;font-size:1rem;">
      <button class="btn btn-primary">Search</button>
    </div>
  </form>

  <?php if ($p['total'] === 0): ?>
    <div class="empty-state-browse">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/><path d="M8 11h6"/><path d="M11 8v6"/></svg>
      <h3>No results found for &ldquo;<?= e($q) ?>&rdquo;</h3>
      <p>Try broadening your search or browse by category below.</p>
      <div style="display:flex;flex-wrap:wrap;gap:0.5rem;justify-content:center;">
        <a href="<?= APP_URL ?>/discover/businesses.php" class="btn btn-primary btn-sm">Browse Businesses</a>
        <a href="<?= APP_URL ?>/discover/entrepreneurs.php" class="btn btn-ghost btn-sm">Browse Pitches</a>
        <a href="<?= APP_URL ?>/discover/investors.php" class="btn btn-ghost btn-sm">Browse Investors</a>
      </div>
      <?php if (!$user): ?>
        <p style="margin-top:2rem;font-size:0.85rem;">Tip: Smart suggestions are only shown to verified users. Complete verification to unlock personalized matches.</p>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <p class="text-muted" style="margin-bottom:1.5rem;"><?= $p['total'] ?> result<?= $p['total'] !== 1 ? 's' : '' ?> found for &ldquo;<?= e($q) ?>&rdquo;</p>

    <div style="display:flex;flex-direction:column;gap:1rem;">
      <?php foreach ($results as $r): ?>
        <?php $info = $typeLabels[$r['type']] ?? ['label' => 'Listing', 'url' => '#'] ?>
        <div class="card search-result-card" onclick="location.href='<?= APP_URL ?><?= $info['url'] ?><?= $r['id'] ?>'">
          <div style="display:flex;justify-content:space-between;align-items:start;">
            <div style="flex:1;">
              <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.25rem;">
                <span class="tx-badge <?= $r['type'] === 'business' ? 'tx-badge-sale' : ($r['type'] === 'pitch' ? 'tx-badge-partial' : 'tx-badge-franchise') ?>"><?= $info['label'] ?></span>
                <?php if ($r['price_field']): ?>
                  <span style="font-weight:600;"><?= money($r['price_field']) ?></span>
                <?php endif; ?>
              </div>
              <h4 style="margin:0.25rem 0;"><?= e(mb_substr($r['title'] ?? '', 0, 120)) ?></h4>
              <p style="font-size:0.85rem;margin:0.25rem 0 0;color:var(--color-text-muted);">
                <?= e(mb_substr(strip_tags($r['description'] ?? ''), 0, 250)) ?>
              </p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div style="text-align:center;margin-top:2rem;">
      <?= render_pagination($p['page'], $p['lastPage'], $baseUrl) ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
