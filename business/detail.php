<?php
require __DIR__ . '/../config/bootstrap.php';

$db = db();
$businessId = (int)($_GET['id'] ?? 0);
$slug = $_GET['slug'] ?? '';

if ($businessId < 1 && $slug === '') {
    http_response_code(404);
    require __DIR__ . '/../pages/404.php';
    exit;
}

$user = current_user();
$userId = $user ? (int)$user['id'] : 0;

if ($slug) {
    $stmt = $db->prepare('SELECT b.*, s.name AS sector_name, u.name AS owner_name, u.company_name, u.phone AS owner_phone, u.email AS owner_email, u.verification_status, u.id AS owner_id, u.profile_photo FROM businesses b LEFT JOIN sectors s ON s.id = b.sector_id JOIN users u ON u.id = b.user_id WHERE b.slug = ?');
    $stmt->execute([$slug]);
} else {
    $stmt = $db->prepare('SELECT b.*, s.name AS sector_name, u.name AS owner_name, u.company_name, u.phone AS owner_phone, u.email AS owner_email, u.verification_status, u.id AS owner_id, u.profile_photo FROM businesses b LEFT JOIN sectors s ON s.id = b.sector_id JOIN users u ON u.id = b.user_id WHERE b.id = ?');
    $stmt->execute([$businessId]);
}
$business = $stmt->fetch();

if (!$business) {
    http_response_code(404);
    require __DIR__ . '/../pages/404.php';
    exit;
}

$businessId = (int)$business['id'];

if ($business['status'] !== 'approved' && (!$user || $userId !== (int)$business['owner_id'])) {
    http_response_code(404);
    require __DIR__ . '/../pages/404.php';
    exit;
}

$db->prepare('UPDATE businesses SET views = views + 1 WHERE id = ?')->execute([$businessId]);

$mediaS = $db->prepare('SELECT * FROM business_media WHERE business_id = ? ORDER BY sort_order');
$mediaS->execute([$businessId]);
$mediaItems = $mediaS->fetchAll();

$assetS = $db->prepare('SELECT * FROM business_assets WHERE business_id = ? ORDER BY id');
$assetS->execute([$businessId]);
$assetItems = $assetS->fetchAll();

$finS = $db->prepare('SELECT * FROM business_financials WHERE business_id = ? ORDER BY fiscal_year ASC');
$finS->execute([$businessId]);
$financialItems = $finS->fetchAll();

$verS = $db->prepare('SELECT * FROM business_verifications WHERE business_id = ?');
$verS->execute([$businessId]);
$verification = $verS->fetch();

// Count distinct months in financials to determine if we show YTD
$finYears = array_map(fn($f) => (int)$f['fiscal_year'], $financialItems);
$isCurrentYear = !empty($finYears) && max($finYears) >= (int)date('Y');

$inqS = $db->prepare('SELECT COUNT(*) FROM business_inquiries WHERE business_id = ?');
$inqS->execute([$businessId]);
$inquiryCount = (int)$inqS->fetchColumn();

$ndaSigned = false;
if ($userId) {
    $ndaS = $db->prepare('SELECT signed FROM nda_requests WHERE business_id = ? AND investor_id = ? AND signed = 1 LIMIT 1');
    $ndaS->execute([$businessId, $userId]);
    $ndaSigned = (bool)$ndaS->fetchColumn();
}

$hasInquired = false;
if ($userId) {
    $inqC = $db->prepare('SELECT id FROM business_inquiries WHERE business_id = ? AND user_id = ? LIMIT 1');
    $inqC->execute([$businessId, $userId]);
    $hasInquired = (bool)$inqC->fetchColumn();
}

$hasMatch = false;
$ownerUserId = (int)$business['owner_id'];
if ($user) {
    $matchS = $db->prepare("SELECT id FROM matches WHERE context_type = 'business' AND context_id = ? AND ((user_a_id = ? AND user_b_id = ?) OR (user_a_id = ? AND user_b_id = ?)) AND closed_status = 'open' LIMIT 1");
    $matchS->execute([$businessId, $userId, $ownerUserId, $ownerUserId, $userId]);
    $hasMatch = (bool)$matchS->fetch();
}

$typeLabels = [
    'business_sale' => 'Business for Sale', 'investment' => 'Investment Opportunity',
    'partial_stake' => 'Partial Stake Sale', 'loan' => 'Business Loan',
    'asset_sale' => 'Asset Sale', 'franchise' => 'Franchise Opportunity',
    'partner' => 'Looking for Partner',
];
$typeLabel = $typeLabels[$business['listing_type']] ?? ucfirst($business['listing_type']);

$location = '';
if ($business['city_id']) {
    $cS = $db->prepare('SELECT ci.name AS city, s.name AS state FROM cities ci JOIN states s ON ci.state_id = s.id WHERE ci.id = ?');
    $cS->execute([$business['city_id']]);
    $loc = $cS->fetch();
    if ($loc) $location = e($loc['city']) . ', ' . e($loc['state']) . ', Nepal';
} elseif ($business['province']) {
    $location = e($business['province']) . ($business['district'] ? ', ' . e($business['district']) : '');
}
if (!$location) $location = 'Nepal';

$vC = $verification ? ((($verification['email_verified'] ?? false) ? 1 : 0) + (($verification['phone_verified'] ?? false) ? 1 : 0) + (($verification['identity_verified'] ?? false) ? 1 : 0) + (($verification['company_verified'] ?? false) ? 1 : 0)) : 0;

$revGrowth = null;
$profitGrowth = null;
if (count($financialItems) >= 2) {
    $last = $financialItems[count($financialItems) - 1];
    $prev = $financialItems[count($financialItems) - 2];
    if ((float)$prev['revenue'] > 0) $revGrowth = round((( (float)$last['revenue'] - (float)$prev['revenue']) / (float)$prev['revenue']) * 100, 1);
    if ((float)$prev['profit'] > 0) $profitGrowth = round((( (float)$last['profit'] - (float)$prev['profit']) / (float)$prev['profit']) * 100, 1);
}
$maxRevenue = empty($financialItems) ? 0 : max(array_map(fn($f) => (float)$f['revenue'], $financialItems));

$pageTitle = e($business['business_name']) . ' — ' . APP_NAME;
$pageDescription = mb_substr(strip_tags($business['description'] ?: $business['overview'] ?: ''), 0, 160);
$canonicalUrl = APP_URL . '/business/' . ($business['slug'] ?: $business['id']);

$images = array_values(array_filter($mediaItems, fn($m) => $m['media_type'] === 'image'));

$businessSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $business['business_name'],
    'description' => mb_substr(strip_tags($business['description'] ?: $business['overview'] ?: ''), 0, 200),
    'url' => $canonicalUrl,
    'category' => $business['sector_name'] ?? 'Business',
    'offers' => [
        '@type' => 'Offer',
        'price' => $business['asking_price'] ?? '0',
        'priceCurrency' => 'NPR',
    ],
];
if ($business['annual_revenue']) {
    $businessSchema['brand'] = [
        '@type' => 'Brand',
        'description' => 'Annual Revenue: ' . money($business['annual_revenue']),
    ];
}
$extraSchema = '<script type="application/ld+json">' . json_encode($businessSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
$firstImg = $images[0] ?? null;
$hasDocs = !empty(array_filter($mediaItems, fn($m) => $m['media_type'] === 'document'));

require __DIR__ . '/../includes/layout-public.php';
?>

<div class="stitch-detail">

<!-- ════ Breadcrumb ════ -->
<nav class="stitch-breadcrumb">
  <a href="<?= APP_URL ?>/">Home</a>
  <span class="sep">›</span>
  <a href="<?= APP_URL ?>/browse/businesses">Businesses for Sale</a>
  <span class="sep">›</span>
  <span><?= e($business['business_name']) ?></span>
</nav>

<!-- ════════════════════════════════════════════════════════════
     HERO
     ════════════════════════════════════════════════════════════ -->
<section class="stitch-hero">
  <div class="stitch-hero-inner">
    <div class="stitch-hero-left">
      <div class="stitch-hero-badges">
        <span class="stitch-badge stitch-badge-sale"><?= e($typeLabel) ?></span>
        <?php if (!empty($business['is_featured'])): ?>
        <span class="stitch-badge stitch-badge-premium">Premium</span>
        <?php endif; ?>
        <?php if ($vC >= 2): ?>
        <span class="stitch-badge stitch-badge-verified">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5z"/><polyline points="9 12 11 14 15 10" fill="none" stroke="currentColor" stroke-width="2"/></svg>
          Verified
        </span>
        <?php endif; ?>
        <span class="stitch-badge stitch-badge-industry"><?= e($business['sector_name'] ?? '') ?></span>
      </div>

      <h1 class="stitch-hero-title"><?= e($business['business_name']) ?></h1>

      <div class="stitch-hero-location">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <span><?= $location ?></span>
      </div>

      <p class="stitch-hero-desc"><?= e(mb_substr(strip_tags($business['overview'] ?: $business['description'] ?: ''), 0, 160)) ?></p>

      <div class="stitch-hero-metrics">
        <div class="stitch-metric-card">
          <span class="label">Asking Price</span>
          <span class="value"><?= money($business['asking_price'] ?? 0) ?></span>
        </div>
        <div class="stitch-metric-card">
          <span class="label">Annual Revenue</span>
          <span class="value"><?= money($business['annual_revenue'] ?? 0) ?></span>
        </div>
        <div class="stitch-metric-card">
          <span class="label">EBITDA Margin</span>
          <span class="value"><?= e($business['ebitda_pct'] ?? '—') ?>%</span>
        </div>
        <div class="stitch-metric-card">
          <span class="label">Growth (3Y)</span>
          <span class="value"><?= $revGrowth !== null ? ($revGrowth >= 0 ? '+' : '') . $revGrowth . '%' : '—' ?></span>
        </div>
      </div>

      <div class="stitch-hero-actions">
        <button class="stitch-btn stitch-btn-primary" onclick="document.getElementById('interest-modal').classList.add('open')">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          Contact Seller
        </button>
        <button class="stitch-btn stitch-btn-secondary" id="saveBtn" data-id="<?= $businessId ?>" data-type="business">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <span>Save</span>
        </button>
        <button class="stitch-btn stitch-btn-secondary" onclick="navigator.share? navigator.share({title:'<?= e($business['business_name']) ?>',url:window.location.href}) : navigator.clipboard.writeText(window.location.href)">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
          Share
        </button>
      </div>
    </div>

    <!-- Gallery -->
    <div class="stitch-gallery">
      <?php if ($firstImg): ?>
      <div class="stitch-gallery-main-wrap">
        <img src="<?= APP_URL . $firstImg['file_url'] ?>" alt="<?= e($business['business_name']) ?>" class="stitch-gallery-main" id="heroMainImage">
        <?php if (count($images) > 1): ?>
        <button class="stitch-gallery-btn" onclick="document.getElementById('gallery-modal').classList.add('open')">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><circle cx="12" cy="12" r="3"/><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/></svg>
          View all photos (<?= count($images) ?>)
        </button>
        <?php endif; ?>
      </div>
      <?php if (count($images) > 1): ?>
      <div class="stitch-gallery-thumbs">
        <?php foreach (array_slice($images, 1, 3) as $i => $img): ?>
        <img src="<?= APP_URL . $img['file_url'] ?>" alt="" class="stitch-gallery-thumb" onclick="document.getElementById('heroMainImage').src='<?= APP_URL . $img['file_url'] ?>'" loading="lazy">
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <?php elseif (!empty($business['thumbnail_url'])): ?>
      <div class="stitch-gallery-main-wrap">
        <img src="<?= e($business['thumbnail_url']) ?>" alt="<?= e($business['business_name']) ?>" class="stitch-gallery-main">
      </div>
      <?php else: ?>
      <div class="stitch-gallery-fallback">
        <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="10" y="10" width="44" height="44" rx="4"/><circle cx="32" cy="28" r="8"/><path d="M22 46c0-6 4-10 10-10s10 4 10 10"/></svg>
        <span>Gallery</span>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════════
     LAYOUT: Content + Sidebar
     ════════════════════════════════════════════════════════════ -->
<div class="stitch-layout">

  <!-- ─── Main Content ─── -->
  <div class="stitch-content">

    <!-- ── Investment Snapshot ── -->
    <section class="stitch-section">
      <h2 class="stitch-section-title">Investment Snapshot</h2>
      <div class="stitch-snapshot-grid">
        <div class="stitch-snapshot-item">
          <div class="icon-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div>
          <span class="stitch-snapshot-label">Asking Price</span>
          <span class="stitch-snapshot-value accent"><?= money($business['asking_price'] ?? 0) ?></span>
        </div>
        <div class="stitch-snapshot-item">
          <div class="icon-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 16l4-8 4 4 4-6"/></svg></div>
          <span class="stitch-snapshot-label">Annual Revenue</span>
          <span class="stitch-snapshot-value"><?= money($business['annual_revenue'] ?? 0) ?></span>
        </div>
        <div class="stitch-snapshot-item">
          <div class="icon-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path d="M12 6v6l4 2"/></svg></div>
          <span class="stitch-snapshot-label">EBITDA Margin</span>
          <span class="stitch-snapshot-value"><?= e($business['ebitda_pct'] ?? '—') ?>%</span>
        </div>
        <div class="stitch-snapshot-item">
          <div class="icon-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg></div>
          <span class="stitch-snapshot-label">Employees</span>
          <span class="stitch-snapshot-value"><?= (int)($business['employee_count'] ?? 0) ?></span>
        </div>

        <div class="stitch-snapshot-divider"></div>

        <?php if (!empty($business['established_year'])): ?>
        <div class="stitch-snapshot-item sm">
          <span class="stitch-snapshot-label">Established</span>
          <span class="stitch-snapshot-value"><?= e($business['established_year']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($business['legal_entity_type']): ?>
        <div class="stitch-snapshot-item sm">
          <span class="stitch-snapshot-label">Legal Entity</span>
          <span class="stitch-snapshot-value"><?= e($business['legal_entity_type']) ?></span>
        </div>
        <?php endif; ?>
        <div class="stitch-snapshot-item sm">
          <span class="stitch-snapshot-label">Industry</span>
          <span class="stitch-snapshot-value"><?= e($business['sector_name'] ?? '—') ?></span>
        </div>
      </div>

      <!-- Bottom highlight row -->
      <?php if ($business['monthly_revenue'] || !empty($business['gross_margin_pct']) || !empty($business['retention_rate']) || !empty($business['customer_count'])): ?>
      <div class="stitch-snapshot-highlights">
        <?php if ($business['monthly_revenue']): ?>
        <div class="stitch-highlight-pill">
          <span class="icon" style="background:rgba(11,58,94,.1);color:var(--color-secondary)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></span>
          <div><span class="l">Monthly Revenue</span><span class="v"><?= money($business['monthly_revenue']) ?></span></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($business['gross_margin_pct'])): ?>
        <div class="stitch-highlight-pill">
          <span class="icon" style="background:rgba(22,163,74,.1);color:var(--color-success)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></span>
          <div><span class="l">Gross Margin</span><span class="v"><?= (float)($business['gross_margin_pct'] ?? 0) ?>%</span></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($business['retention_rate'])): ?>
        <div class="stitch-highlight-pill">
          <span class="icon" style="background:rgba(245,158,11,.1);color:var(--color-warning)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></span>
          <div><span class="l">Retention Rate</span><span class="v"><?= (float)($business['retention_rate'] ?? 0) ?>%</span></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($business['customer_count'])): ?>
        <div class="stitch-highlight-pill">
          <span class="icon" style="background:rgba(11,58,94,.1);color:var(--color-secondary)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l9 4.5v7c0 5.5-9 9.5-9 9.5s-9-4-9-9.5v-7z"/></svg></span>
          <div><span class="l">Countries</span><span class="v"><?= number_format((int)($business['customer_count'] ?? 0)) ?></span></div>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </section>

    <!-- ── Business Overview ── -->
    <?php if ($business['overview'] || $business['description']): ?>
    <section class="stitch-section">
      <h2 class="stitch-section-title">Business Overview</h2>
      <div class="stitch-overview-grid">
        <div class="stitch-overview-text">
          <?= nl2br(e($business['overview'] ?: $business['description'])) ?>
        </div>
        <div class="stitch-overview-facts">
          <div class="fact-row"><span class="label">Industry</span><span class="value"><?= e($business['sector_name'] ?? '—') ?></span></div>
          <div class="fact-row"><span class="label">Location</span><span class="value"><?= $location ?></span></div>
          <?php if (!empty($business['established_year'])): ?>
          <div class="fact-row"><span class="label">Year Founded</span><span class="value"><?= e($business['established_year']) ?></span></div>
          <?php endif; ?>
          <div class="fact-row"><span class="label">Team Size</span><span class="value"><?= (int)($business['employee_count'] ?? 0) ?> Full-time</span></div>
          <?php if ($business['legal_entity_type']): ?>
          <div class="fact-row"><span class="label">Entity Type</span><span class="value"><?= e($business['legal_entity_type']) ?></span></div>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- ── Financial Performance ── -->
    <?php if (!empty($financialItems) && $maxRevenue > 0): ?>
    <section class="stitch-section">
      <h2 class="stitch-section-title">Financial Performance</h2>
      <div class="stitch-financial-card">

        <div class="stitch-chart-legend">
          <span><span class="dot" style="background:var(--color-secondary)"></span> Revenue (NPR)</span>
          <span><span class="dot" style="background:var(--color-success)"></span> Profit (NPR)</span>
        </div>

        <div class="stitch-chart">
          <?php foreach ($financialItems as $f):
            $rh = max(4, ((float)$f['revenue'] / $maxRevenue) * 180);
            $pp = (float)$f['profit'];
            $ph = $maxRevenue > 0 ? max(4, ($pp / $maxRevenue) * 180) : 4;
            $isLatest = $f['fiscal_year'] == max($finYears);
            $opacity = $isLatest ? '100' : ($f['fiscal_year'] == max($finYears) - 1 ? '70' : ($f['fiscal_year'] == max($finYears) - 2 ? '40' : '20'));
          ?>
          <div class="stitch-chart-group">
            <div class="stitch-chart-bars">
              <div class="stitch-bar revenue" style="height:<?= $rh ?>px;opacity:<?= $opacity === '100' ? '1' : '0.' . $opacity ?>"></div>
              <?php if ($pp > 0): ?>
              <div class="stitch-bar profit" style="height:<?= $ph ?>px;opacity:<?= $opacity === '100' ? '1' : '0.' . $opacity ?>"></div>
              <?php endif; ?>
            </div>
            <span class="stitch-chart-label <?= $isLatest ? 'current' : '' ?>"><?= $isLatest ? (int)$f['fiscal_year'] . ' (YTD)' : (int)$f['fiscal_year'] ?></span>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if ($revGrowth !== null): ?>
        <div class="stitch-growth-note">
          Revenue <strong class="<?= $revGrowth >= 0 ? 'up' : 'down' ?>"><?= ($revGrowth >= 0 ? 'grew' : 'declined') ?> <?= abs($revGrowth) ?>%</strong> year-over-year
          <?php if ($profitGrowth !== null): ?>
          &middot; Profit <strong class="<?= $profitGrowth >= 0 ? 'up' : 'down' ?>"><?= ($profitGrowth >= 0 ? 'grew' : 'declined') ?> <?= abs($profitGrowth) ?>%</strong>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="stitch-fin-table-wrap">
          <table class="stitch-fin-table">
            <thead><tr>
              <th>Metric</th>
              <?php foreach ($financialItems as $f): ?>
              <th><?= (int)$f['fiscal_year'] ?><?= (int)$f['fiscal_year'] === max($finYears) ? ' YTD' : '' ?></th>
              <?php endforeach; ?>
            </tr></thead>
            <tbody>
              <tr>
                <td class="name">Revenue</td>
                <?php foreach ($financialItems as $f): ?>
                <td class="<?= (int)$f['fiscal_year'] === max($finYears) ? 'current' : '' ?>"><?= money($f['revenue'] ?? 0) ?></td>
                <?php endforeach; ?>
              </tr>
              <tr>
                <td class="name">Gross Profit</td>
                <?php foreach ($financialItems as $f): ?>
                <td><?= money($f['gross_profit'] ?? $f['profit'] ?? 0) ?></td>
                <?php endforeach; ?>
              </tr>
              <tr>
                <td class="name">EBITDA</td>
                <?php foreach ($financialItems as $f): ?>
                <td class="highlight"><?= money($f['ebitda'] ?? 0) ?></td>
                <?php endforeach; ?>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- ── Key Highlights ── -->
    <section class="stitch-section">
      <h2 class="stitch-section-title">Key Highlights</h2>
      <div class="stitch-highlights-grid">
        <?php $hasHighlight = false; ?>
        <?php if (!empty($business['customer_count'])): $hasHighlight = true; ?>
        <div class="stitch-highlight-item">
          <span class="icon" style="background:rgba(22,163,74,.1);color:var(--color-success)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></span>
          <strong>Strong Recurring Revenue</strong>
          <p><?= number_format((int)($business['customer_count'] ?? 0)) ?>+ active customers with long-term contracts.</p>
        </div>
        <?php endif; ?>
        <?php if (!empty($business['ebitda_pct']) && (float)$business['ebitda_pct'] > 15): $hasHighlight = true; ?>
        <div class="stitch-highlight-item">
          <span class="icon" style="background:rgba(11,58,94,.1);color:var(--color-secondary)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></span>
          <strong>Healthy Margins</strong>
          <p><?= e($business['ebitda_pct']) ?>% EBITDA margin with strong operational efficiency.</p>
        </div>
        <?php endif; ?>
        <?php if (!empty($business['employee_count'])): $hasHighlight = true; ?>
        <div class="stitch-highlight-item">
          <span class="icon" style="background:rgba(24,22,233,.1);color:var(--color-secondary)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></span>
          <strong>Experienced Team</strong>
          <p><?= (int)$business['employee_count'] ?> skilled professionals with deep industry expertise.</p>
        </div>
        <?php endif; ?>
        <?php if (!empty($business['established_year'])): $age = date('Y') - (int)$business['established_year']; if ($age > 3): $hasHighlight = true; ?>
        <div class="stitch-highlight-item">
          <span class="icon" style="background:rgba(245,158,11,.1);color:var(--color-warning)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>
          <strong>Established Business</strong>
          <p>Operating successfully for <?= $age ?>+ years with proven track record.</p>
        </div>
        <?php endif; endif; ?>
        <?php if (!$hasHighlight): ?>
        <div class="stitch-highlight-item">
          <span class="icon" style="background:rgba(90,90,90,.1);color:var(--color-grey-mid)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg></span>
          <strong>More Details Pending</strong>
          <p>Contact the seller for detailed highlights and performance metrics.</p>
        </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- ── Products & Services ── -->
    <?php if ($business['products_services']): ?>
    <section class="stitch-section">
      <h2 class="stitch-section-title">Products &amp; Services</h2>
      <div class="stitch-overview-text"><?= nl2br(e($business['products_services'])) ?></div>
    </section>
    <?php endif; ?>

    <!-- ── Assets ── -->
    <?php if (!empty($assetItems)): ?>
    <section class="stitch-section">
      <h2 class="stitch-section-title">Assets Included</h2>
      <div class="stitch-fin-table-wrap">
        <table class="stitch-fin-table">
          <thead><tr><th>Asset</th><th>Type</th><th>Value</th></tr></thead>
          <tbody>
          <?php foreach ($assetItems as $a): ?>
          <tr>
            <td><strong><?= e($a['asset_name']) ?></strong><br><span class="sub"><?= e($a['description']) ?></span></td>
            <td><?= e(ucfirst(str_replace('_', ' ', $a['asset_type'] ?? ''))) ?></td>
            <td><?= money($a['estimated_value'] ?? 0) ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
    <?php endif; ?>

    <!-- ── Documents & NDA ── -->
    <section class="stitch-section">
      <h2 class="stitch-section-title">Documents &amp; NDA</h2>
      <div class="stitch-nda-card">
        <div class="stitch-nda-text">
          <p>Sign a Non-Disclosure Agreement to access detailed documents and financial information.</p>
          <div class="stitch-doc-list">
            <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg> Financial Statements</span>
            <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/></svg> Customer Contracts</span>
            <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/></svg> Business Plan</span>
          </div>
        </div>
        <?php if ($userId): ?>
          <?php if ($ndaSigned): ?>
          <span class="stitch-nda-btn signed">NDA Signed</span>
          <?php else: ?>
          <button class="stitch-nda-btn" onclick="signNda(<?= $businessId ?>, this)">Sign NDA to Unlock</button>
          <?php endif; ?>
        <?php else: ?>
        <a href="<?= APP_URL ?>/login" class="stitch-nda-btn">Sign NDA to Unlock</a>
        <?php endif; ?>
      </div>

      <?php if ($hasDocs): ?>
      <div class="stitch-doc-links">
        <?php foreach ($mediaItems as $m): if ($m['media_type'] !== 'document') continue; ?>
        <a href="<?= APP_URL . $m['file_url'] ?>" target="_blank">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          <?= e($m['original_name'] ?: 'Document') ?>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </section>

    <!-- ── Reason for Sale ── -->
    <?php if ($business['reason_for_sale']): ?>
    <section class="stitch-section">
      <h2 class="stitch-section-title">Reason for Sale</h2>
      <div class="stitch-reason-card">
        <p><?= nl2br(e($business['reason_for_sale'])) ?></p>
      </div>
    </section>
    <?php endif; ?>

    <!-- ── Verification ── -->
    <section class="stitch-section">
      <h2 class="stitch-section-title">Seller Verification</h2>
      <div class="stitch-verification-row">
        <div class="stitch-verif-item <?= ($verification['email_verified'] ?? false) ? 'done' : '' ?>">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Email <?= ($verification['email_verified'] ?? false) ? 'Verified' : 'Unverified' ?>
        </div>
        <div class="stitch-verif-item <?= ($verification['phone_verified'] ?? false) ? 'done' : '' ?>">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Phone <?= ($verification['phone_verified'] ?? false) ? 'Verified' : 'Unverified' ?>
        </div>
        <div class="stitch-verif-item <?= ($verification['identity_verified'] ?? false) ? 'done' : '' ?>">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Identity <?= ($verification['identity_verified'] ?? false) ? 'Verified' : 'Unverified' ?>
        </div>
        <div class="stitch-verif-item <?= ($verification['company_verified'] ?? false) ? 'done' : '' ?>">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Company <?= ($verification['company_verified'] ?? false) ? 'Verified' : 'Unverified' ?>
        </div>
        <?php if ($vC >= 4): ?>
        <span class="stitch-verif-all">All verifications completed</span>
        <?php endif; ?>
      </div>
    </section>

    <!-- ── Related Opportunities ── -->
    <?php
    $relS = $db->prepare('SELECT id, business_name, slug, thumbnail_url, asking_price, annual_revenue, ebitda_pct, province FROM businesses WHERE sector_id = ? AND id != ? AND status = "approved" ORDER BY RAND() LIMIT 3');
    $relS->execute([$business['sector_id'], $businessId]);
    $related = $relS->fetchAll();
    if (!empty($related)): ?>
    <section class="stitch-section">
      <div class="stitch-section-header-row">
        <h2 class="stitch-section-title" style="margin:0">Related Opportunities</h2>
        <a href="<?= APP_URL ?>/browse/businesses" class="stitch-view-all">View all</a>
      </div>
      <div class="stitch-related-grid">
        <?php foreach ($related as $r): ?>
        <div class="stitch-related-card" onclick="location.href='<?= APP_URL ?>/business/<?= e($r['slug'] ?: $r['id']) ?>'" tabindex="0" role="link">
          <div class="stitch-related-img">
            <?php if ($r['thumbnail_url']): ?>
            <img src="<?= e($r['thumbnail_url']) ?>" alt="" loading="lazy">
            <?php else: ?>
            <div class="fallback"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg></div>
            <?php endif; ?>
            <?php if (!empty($r['is_featured'])): ?>
            <span class="stitch-premium-badge">PREMIUM</span>
            <?php endif; ?>
          </div>
          <div class="stitch-related-body">
            <h4><?= e($r['business_name']) ?></h4>
            <div class="loc">📍 <?= e($r['province'] ?: 'Nepal') ?></div>
            <div class="stats">
              <div><span class="l">Revenue</span><span class="v"><?= money($r['annual_revenue'] ?? 0) ?></span></div>
              <div><span class="l">EBITDA</span><span class="v"><?= e($r['ebitda_pct'] ?? '—') ?>%</span></div>
            </div>
            <div class="price"><?= money($r['asking_price'] ?? 0) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

  </div>

  <!-- ─── Sidebar ─── -->
  <aside class="stitch-sidebar">
    <div class="stitch-sidebar-sticky">

      <!-- Price + CTA -->
      <div class="stitch-sidebar-card">
        <div class="stitch-sidebar-price">
          <span class="label">Asking Price</span>
          <span class="value"><?= $business['asking_price'] ? money($business['asking_price']) : 'Negotiable' ?></span>
        </div>

        <?php if ($userId && $userId === $ownerUserId): ?>
        <a href="<?= APP_URL ?>/business/edit.php?id=<?= $businessId ?>" class="stitch-sidebar-cta">Edit Listing</a>
        <?php elseif ($hasInquired || $hasMatch): ?>
        <button class="stitch-sidebar-cta" onclick="alert('Contact: <?= e($business['owner_name']) ?> — <?= e($business['owner_email']) ?>')">View Contact Details</button>
        <?php else: ?>
        <button class="stitch-sidebar-cta" onclick="document.getElementById('interest-modal').classList.add('open')">Contact Seller</button>
        <?php endif; ?>

        <?php if ($inquiryCount > 0): ?>
        <div class="stitch-sidebar-interest">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
          <strong><?= $inquiryCount ?> investor<?= $inquiryCount !== 1 ? 's' : '' ?></strong> interested this month
        </div>
        <?php endif; ?>

        <!-- Auth butons for guests -->
        <?php if (!$userId): ?>
        <div class="stitch-sidebar-auth">
          <button class="stitch-auth-btn" onclick="location.href='<?= APP_URL ?>/login'">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/></svg>
            Continue with Google
          </button>
          <button class="stitch-auth-btn" onclick="location.href='<?= APP_URL ?>/login'">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.5 2h-17A1.5 1.5 0 002 3.5v17A1.5 1.5 0 003.5 22h17a1.5 1.5 0 001.5-1.5v-17A1.5 1.5 0 0020.5 2zM8 19H5v-9h3zM6.5 8.25A1.75 1.75 0 118.3 6.5a1.78 1.78 0 01-1.8 1.75zM19 19h-3v-4.74c0-1.42-.6-1.93-1.38-1.93A1.74 1.74 0 0013 14.19a.66.66 0 000 .14V19h-3v-9h2.9v1.3a3.11 3.11 0 012.7-1.4c1.55 0 3.36.86 3.36 3.67z"/></svg>
            Continue with LinkedIn
          </button>
        </div>
        <?php endif; ?>

        <!-- Listing Meta -->
        <div class="stitch-sidebar-meta">
          <div><span class="l">Listing ID</span><span class="v">#AC-<?= str_pad($businessId, 4, '0', STR_PAD_LEFT) ?></span></div>
          <div><span class="l">Listed</span><span class="v"><?= date('M d, Y', strtotime($business['created_at'])) ?></span></div>
          <div><span class="l">Views</span><span class="v"><?= number_format((int)$business['views']) ?></span></div>
          <div><span class="l">Inquiries</span><span class="v"><?= $inquiryCount ?></span></div>
        </div>

        <!-- Disclaimer -->
        <div class="stitch-sidebar-disclaimer">
          <p>Asaan Capital Ltd is a discovery platform. We verify data independently.</p>
        </div>

        <button class="stitch-report-btn" onclick="document.getElementById('report-modal').classList.add('open')">Report listing</button>
      </div>

      <!-- Trust & Verification -->
      <?php if ($vC > 0): ?>
      <div class="stitch-sidebar-card">
        <div class="stitch-card-header">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          Trust &amp; Verification
        </div>
        <div class="stitch-card-body">
          <div class="stitch-verif-row <?= ($verification['email_verified'] ?? false) ? 'done' : '' ?>">
            <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Email</span>
            <?php if ($verification['email_verified'] ?? false): ?>
            <span class="check"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></span>
            <?php endif; ?>
          </div>
          <div class="stitch-verif-row <?= ($verification['phone_verified'] ?? false) ? 'done' : '' ?>">
            <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg> Phone</span>
            <?php if ($verification['phone_verified'] ?? false): ?>
            <span class="check"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></span>
            <?php endif; ?>
          </div>
          <div class="stitch-verif-row <?= ($verification['identity_verified'] ?? false) ? 'done' : '' ?>">
            <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Identity</span>
            <?php if ($verification['identity_verified'] ?? false): ?>
            <span class="check"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></span>
            <?php endif; ?>
          </div>
          <div class="stitch-verif-row <?= ($verification['company_verified'] ?? false) ? 'done' : '' ?>">
            <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg> Company</span>
            <?php if ($verification['company_verified'] ?? false): ?>
            <span class="check"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></span>
            <?php endif; ?>
          </div>
        </div>
        <div class="stitch-card-footer verified">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          Verified Business
        </div>
      </div>
      <?php endif; ?>

      <!-- Recent Activity -->
      <div class="stitch-sidebar-card">
        <div class="stitch-card-header">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Recent Activity
        </div>
        <div class="stitch-card-body activity">
          <div class="stitch-activity-item">
            <span class="dot"></span>
            <div><span class="t">Listed on marketplace</span><span class="ts"><?= date_human($business['created_at']) ?></span></div>
          </div>
          <?php if ($inquiryCount > 0): ?>
          <div class="stitch-activity-item">
            <span class="dot"></span>
            <div><span class="t"><?= $inquiryCount ?> inquiry<?= $inquiryCount !== 1 ? 'ies' : 'y' ?></span><span class="ts">Total received</span></div>
          </div>
          <?php endif; ?>
          <?php if ((int)$business['views'] > 0): ?>
          <div class="stitch-activity-item">
            <span class="dot"></span>
            <div><span class="t"><?= number_format((int)$business['views']) ?> profile views</span><span class="ts">Total views</span></div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Security & Confidential -->
      <div class="stitch-sidebar-card security">
        <div class="stitch-card-header">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
          Secure &amp; Confidential
        </div>
        <div class="stitch-card-body">
          <ul>
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> NDA protection</li>
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Secure data room</li>
            <li><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Confidential inquiries</li>
          </ul>
        </div>
      </div>

    </div>
  </aside>

</div>

<!-- Mobile CTA -->
<div class="stitch-mobile-cta" id="mobileCta">
  <button onclick="document.getElementById('interest-modal').classList.add('open')">Contact Seller</button>
</div>

<!-- ════════════════════════════════════════════════════════════
     FOOTER
     ════════════════════════════════════════════════════════════ -->
<footer class="stitch-footer">
  <div class="stitch-footer-inner">
    <div class="stitch-footer-brand">
      <strong><?= APP_NAME ?></strong>
      <p>The leading institutional-grade business marketplace in South Asia, connecting serious entrepreneurs with global capital.</p>
    </div>
    <div class="stitch-footer-links">
      <div>
        <span class="h">Company</span>
        <a href="#">About Us</a>
        <a href="<?= APP_URL ?>/contact">Contact</a>
        <a href="#">FAQ</a>
      </div>
      <div>
        <span class="h">Resources</span>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Cookie Policy</a>
      </div>
      <div>
        <span class="h">Connect</span>
        <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
      </div>
    </div>
  </div>
</footer>

</div>
<!-- / .stitch-detail -->

<!-- ════════════════════════════════════════════════════════════
     MODALS
     ════════════════════════════════════════════════════════════ -->

<!-- Gallery Modal -->
<div id="gallery-modal" class="stitch-overlay" onclick="if(event.target===this)this.classList.remove('open')" role="dialog" aria-modal="true">
  <div class="stitch-overlay-content" onclick="event.stopImmediatePropagation()">
    <div class="stitch-overlay-header">
      <h3>Gallery (<?= count($images) ?>)</h3>
      <button class="stitch-overlay-close" onclick="document.getElementById('gallery-modal').classList.remove('open')">&times;</button>
    </div>
    <div class="stitch-gallery-modal-grid">
      <?php foreach ($images as $img): ?>
      <img src="<?= APP_URL . $img['file_url'] ?>" alt="" loading="lazy">
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Report -->
<div id="report-modal" class="stitch-overlay" onclick="if(event.target===this)this.classList.remove('open')" role="dialog" aria-modal="true">
  <div class="stitch-overlay-content" onclick="event.stopImmediatePropagation()">
    <div class="stitch-overlay-header">
      <h3>Report Listing</h3>
      <button class="stitch-overlay-close" onclick="document.getElementById('report-modal').classList.remove('open')">&times;</button>
    </div>
    <form method="POST" onsubmit="event.preventDefault();const f=this;fetch('/api/report.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams(new FormData(f))}).then(r=>r.json()).then(d=>{if(d.ok){alert('Report submitted.');f.closest('.stitch-overlay').classList.remove('open')}}).catch(()=>{alert('Error')})">
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="target_type" value="business">
      <input type="hidden" name="target_id" value="<?= $businessId ?>">
      <div class="stitch-field">
        <label>Reason</label>
        <select name="reason" required>
          <option value="">Select...</option>
          <option value="inaccurate_info">Inaccurate information</option>
          <option value="suspicious">Suspicious</option>
          <option value="duplicate">Duplicate</option>
          <option value="inappropriate">Inappropriate</option>
        </select>
      </div>
      <div class="stitch-field">
        <label>Details</label>
        <textarea name="details" rows="3"></textarea>
      </div>
      <button type="submit" class="stitch-btn-primary" style="width:100%">Submit</button>
    </form>
  </div>
</div>

<!-- Interest / Contact -->
<div id="interest-modal" class="stitch-overlay" onclick="if(event.target===this)this.classList.remove('open')" role="dialog" aria-modal="true">
  <div class="stitch-overlay-content" onclick="event.stopImmediatePropagation()">
    <div class="stitch-overlay-header">
      <h3>Contact Seller</h3>
      <button class="stitch-overlay-close" onclick="document.getElementById('interest-modal').classList.remove('open')">&times;</button>
    </div>
    <?php if ($userId): ?>
    <form method="POST" action="<?= APP_URL ?>/api/send-inquiry">
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="business_id" value="<?= $businessId ?>">
      <div class="stitch-field">
        <label for="inquiry-message">Message</label>
        <textarea id="inquiry-message" name="message" rows="4" placeholder="Introduce yourself and explain your interest in this business..."></textarea>
      </div>
      <button type="submit" class="stitch-btn-primary" style="width:100%">Send Inquiry</button>
    </form>
    <?php else: ?>
    <p style="margin-bottom:16px;color:var(--color-text-muted);font-size:0.875rem;">Please sign in to contact the seller.</p>
    <a href="<?= APP_URL ?>/login" class="stitch-btn-primary" style="display:block;text-align:center;">Sign In</a>
    <?php endif; ?>
  </div>
</div>

<script>
function signNda(businessId, btn) {
  fetch('<?= APP_URL ?>/api/sign-nda.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'business_id=' + businessId + '&_csrf=<?= csrf_token() ?>'
  }).then(function(r) { return r.json(); }).then(function(d) {
    if (d.ok) {
      btn.outerHTML = '<span class="stitch-nda-btn signed">NDA Signed</span>';
    } else {
      alert(d.error || 'Failed to sign NDA');
    }
  }).catch(function() { alert('Error signing NDA'); });
}

document.addEventListener('DOMContentLoaded', function() {
  var saveBtn = document.getElementById('saveBtn');
  if (saveBtn) {
    saveBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      var btn = this;
      var params = 'listing_type=' + btn.getAttribute('data-type') + '&listing_id=' + btn.getAttribute('data-id') + '&_csrf=' + '<?= csrf_token() ?>';
      fetch('/api/toggle-save.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: params
      }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.saved) {
          btn.querySelector('span').textContent = 'Saved';
          btn.querySelector('svg').setAttribute('fill', 'currentColor');
        } else {
          btn.querySelector('span').textContent = 'Save';
          btn.querySelector('svg').setAttribute('fill', 'none');
        }
      }).catch(function() {});
    });
  }
});
</script>

<?php $hidePublicFooter = true; require __DIR__ . '/../includes/footer.php'; ?>
