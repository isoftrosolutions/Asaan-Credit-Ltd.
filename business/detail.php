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

$viewerIsPremium = $user && !empty($user['is_premium']);
$isSaved = false;
if ($user) {
    $sS = $db->prepare("SELECT id FROM saved_listings WHERE user_id = ? AND listing_type = 'business' AND listing_id = ?");
    $sS->execute([$userId, $businessId]);
    $isSaved = (bool)$sS->fetch();
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
$maxFinancialValue = 0;
foreach ($financialItems as $f) {
    $maxFinancialValue = max(
        $maxFinancialValue,
        max(0, (float)($f['revenue'] ?? 0)),
        max(0, (float)($f['profit'] ?? 0)),
        max(0, (float)($f['ebitda'] ?? 0))
    );
}
$latestFin = !empty($financialItems) ? $financialItems[count($financialItems) - 1] : null;

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
          <i class="fas fa-shield-alt" style="font-size:11px;"></i>
          Verified
        </span>
        <?php endif; ?>
        <span class="stitch-badge stitch-badge-industry"><?= e($business['sector_name'] ?? '') ?></span>
      </div>

      <h1 class="stitch-hero-title"><?= e($business['business_name']) ?></h1>

      <div class="stitch-hero-location">
        <i class="fas fa-map-marker-alt" style="font-size:15px;"></i>
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
        <button class="stitch-btn stitch-btn-primary" onclick="<?php if ($viewerIsPremium || $userId === $ownerUserId): ?>document.getElementById('interest-modal').classList.add('open')<?php else: ?>location.href='<?= APP_URL ?>/upgrade'<?php endif; ?>">
          <i class="fas fa-envelope" style="font-size:16px;"></i>
          Contact Seller
        </button>
        <button class="stitch-btn stitch-btn-secondary card-save-btn-detail <?= $isSaved ? 'saved' : '' ?>" id="saveBtn" onclick="toggleSave('business',<?= (int)$businessId ?>,this)" data-id="<?= $businessId ?>" data-type="business">
          <i class="fas fa-heart" style="font-size:15px;"></i>
          <span><?= $isSaved ? 'Saved' : 'Save' ?></span>
        </button>
        <button class="stitch-btn stitch-btn-secondary" onclick="navigator.share? navigator.share({title:'<?= e($business['business_name']) ?>',url:window.location.href}) : navigator.clipboard.writeText(window.location.href)">
          <i class="fas fa-external-link-alt" style="font-size:13px;"></i>
          Share
        </button>
      </div>
    </div>

    <!-- Gallery -->
    <div class="stitch-gallery">
      <?php if ($firstImg): ?>
      <div class="stitch-gallery-main-wrap">
        <img src="<?= upload_url($firstImg['file_url']) ?>" alt="<?= e($business['business_name']) ?>" class="stitch-gallery-main" id="heroMainImage">
        <?php if (count($images) > 1): ?>
        <button class="stitch-gallery-btn" onclick="document.getElementById('gallery-modal').classList.add('open')">
          <i class="fas fa-eye" style="font-size:15px;"></i>
          View all photos (<?= count($images) ?>)
        </button>
        <?php endif; ?>
      </div>
      <?php if (count($images) > 1): ?>
      <div class="stitch-gallery-thumbs">
        <?php foreach (array_slice($images, 1, 3) as $i => $img): ?>
        <img src="<?= upload_url($img['file_url']) ?>" alt="" class="stitch-gallery-thumb" onclick="document.getElementById('heroMainImage').src='<?= upload_url($img['file_url']) ?>'" loading="lazy">
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <?php else: ?>
      <div class="stitch-gallery-fallback">
        <i class="fas fa-image" style="font-size:40px;opacity:0.4;"></i>
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
          <div class="icon-wrap"><i class="fas fa-dollar-sign" style="font-size:20px;"></i></div>
          <span class="stitch-snapshot-label">Asking Price</span>
          <span class="stitch-snapshot-value accent"><?= money($business['asking_price'] ?? 0) ?></span>
        </div>
        <div class="stitch-snapshot-item">
          <div class="icon-wrap"><i class="fas fa-chart-bar" style="font-size:20px;"></i></div>
          <span class="stitch-snapshot-label">Annual Revenue</span>
          <span class="stitch-snapshot-value"><?= money($business['annual_revenue'] ?? 0) ?></span>
        </div>
        <div class="stitch-snapshot-item">
          <div class="icon-wrap"><i class="fas fa-hourglass-half" style="font-size:20px;"></i></div>
          <span class="stitch-snapshot-label">EBITDA Margin</span>
          <span class="stitch-snapshot-value"><?= e($business['ebitda_pct'] ?? '—') ?>%</span>
        </div>
        <div class="stitch-snapshot-item">
          <div class="icon-wrap"><i class="fas fa-users" style="font-size:20px;"></i></div>
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
          <span class="icon" style="background:rgba(11,58,94,.1);color:var(--color-secondary)"><i class="fas fa-dollar-sign" style="font-size:20px;"></i></span>
          <div><span class="l">Monthly Revenue</span><span class="v"><?= money($business['monthly_revenue']) ?></span></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($business['gross_margin_pct'])): ?>
        <div class="stitch-highlight-pill">
          <span class="icon" style="background:rgba(22,163,74,.1);color:var(--color-success)"><i class="fas fa-chart-line" style="font-size:20px;"></i></span>
          <div><span class="l">Gross Margin</span><span class="v"><?= (float)($business['gross_margin_pct'] ?? 0) ?>%</span></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($business['retention_rate'])): ?>
        <div class="stitch-highlight-pill">
          <span class="icon" style="background:rgba(245,158,11,.1);color:var(--color-warning)"><i class="fas fa-users" style="font-size:20px;"></i></span>
          <div><span class="l">Retention Rate</span><span class="v"><?= (float)($business['retention_rate'] ?? 0) ?>%</span></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($business['customer_count'])): ?>
        <div class="stitch-highlight-pill">
          <span class="icon" style="background:rgba(11,58,94,.1);color:var(--color-secondary)"><i class="fas fa-bullseye" style="font-size:20px;"></i></span>
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
    <?php if (!empty($financialItems) && $maxFinancialValue > 0): ?>
    <section class="stitch-section">
      <div class="stitch-section-header-row">
        <h2 class="stitch-section-title" style="margin:0">Financial Performance</h2>
        <span class="stitch-fin-period"><?= count($financialItems) ?> fiscal year<?= count($financialItems) === 1 ? '' : 's' ?></span>
      </div>
      <div class="stitch-financial-card">

        <div class="stitch-fin-summary">
          <div>
            <span class="label">Latest Revenue</span>
            <strong><?= money($latestFin['revenue'] ?? 0) ?></strong>
          </div>
          <div>
            <span class="label">Latest Profit</span>
            <strong><?= money($latestFin['profit'] ?? 0) ?></strong>
          </div>
          <div>
            <span class="label">Latest EBITDA</span>
            <strong><?= money($latestFin['ebitda'] ?? 0) ?></strong>
          </div>
        </div>

        <div class="stitch-chart-head">
          <div class="stitch-chart-title">
            <span>Revenue and profit trend</span>
            <small>Scaled to highest reported financial value</small>
          </div>
          <div class="stitch-chart-legend">
            <span><span class="dot revenue"></span> Revenue</span>
            <span><span class="dot profit"></span> Profit</span>
          </div>
        </div>

        <div class="stitch-chart">
          <?php foreach ($financialItems as $f):
            $revenue = max(0, (float)($f['revenue'] ?? 0));
            $profit = max(0, (float)($f['profit'] ?? 0));
            $rh = $maxFinancialValue > 0 ? max(2, min(100, ($revenue / $maxFinancialValue) * 100)) : 0;
            $ph = $maxFinancialValue > 0 ? max(2, min(100, ($profit / $maxFinancialValue) * 100)) : 0;
            $isLatest = $f['fiscal_year'] == max($finYears);
            $opacity = $isLatest ? '100' : ($f['fiscal_year'] == max($finYears) - 1 ? '70' : ($f['fiscal_year'] == max($finYears) - 2 ? '40' : '20'));
          ?>
          <div class="stitch-chart-group">
            <div class="stitch-chart-bars">
              <?php if ($revenue > 0): ?>
              <div class="stitch-bar revenue" style="--bar-height:<?= round($rh, 2) ?>%;--bar-opacity:<?= $opacity === '100' ? '1' : '0.' . $opacity ?>" title="Revenue: <?= e(money($revenue)) ?>"></div>
              <?php else: ?>
              <div class="stitch-bar is-empty" title="Revenue not reported"></div>
              <?php endif; ?>
              <?php if ($profit > 0): ?>
              <div class="stitch-bar profit" style="--bar-height:<?= round($ph, 2) ?>%;--bar-opacity:<?= $opacity === '100' ? '1' : '0.' . $opacity ?>" title="Profit: <?= e(money($profit)) ?>"></div>
              <?php else: ?>
              <div class="stitch-bar is-empty" title="Profit not reported"></div>
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
          <span class="icon" style="background:rgba(22,163,74,.1);color:var(--color-success)"><i class="fas fa-chart-line" style="font-size:20px;"></i></span>
          <strong>Strong Recurring Revenue</strong>
          <p><?= number_format((int)($business['customer_count'] ?? 0)) ?>+ active customers with long-term contracts.</p>
        </div>
        <?php endif; ?>
        <?php if (!empty($business['ebitda_pct']) && (float)$business['ebitda_pct'] > 15): $hasHighlight = true; ?>
        <div class="stitch-highlight-item">
          <span class="icon" style="background:rgba(11,58,94,.1);color:var(--color-secondary)"><i class="fas fa-hourglass-half" style="font-size:20px;"></i></span>
          <strong>Healthy Margins</strong>
          <p><?= e($business['ebitda_pct']) ?>% EBITDA margin with strong operational efficiency.</p>
        </div>
        <?php endif; ?>
        <?php if (!empty($business['employee_count'])): $hasHighlight = true; ?>
        <div class="stitch-highlight-item">
          <span class="icon" style="background:rgba(24,22,233,.1);color:var(--color-secondary)"><i class="fas fa-lightbulb" style="font-size:20px;"></i></span>
          <strong>Experienced Team</strong>
          <p><?= (int)$business['employee_count'] ?> skilled professionals with deep industry expertise.</p>
        </div>
        <?php endif; ?>
        <?php if (!empty($business['established_year'])): $age = date('Y') - (int)$business['established_year']; if ($age > 3): $hasHighlight = true; ?>
        <div class="stitch-highlight-item">
          <span class="icon" style="background:rgba(245,158,11,.1);color:var(--color-warning)"><i class="fas fa-thumbs-up" style="font-size:20px;"></i></span>
          <strong>Established Business</strong>
          <p>Operating successfully for <?= $age ?>+ years with proven track record.</p>
        </div>
        <?php endif; endif; ?>
        <?php if (!$hasHighlight): ?>
        <div class="stitch-highlight-item">
          <span class="icon" style="background:rgba(90,90,90,.1);color:var(--color-grey-mid)"><i class="fas fa-check-circle" style="font-size:20px;"></i></span>
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
    <?php if ($userId && $userId === $ownerUserId || $viewerIsPremium): ?>
    <section class="stitch-section">
      <h2 class="stitch-section-title">Documents &amp; NDA</h2>
      <div class="stitch-nda-card">
        <div class="stitch-nda-text">
          <p>Sign a Non-Disclosure Agreement to access detailed documents and financial information.</p>
          <div class="stitch-doc-list">
            <span><i class="fas fa-file-alt" style="font-size:15px;"></i> Financial Statements</span>
            <span><i class="fas fa-file-alt" style="font-size:15px;"></i> Customer Contracts</span>
            <span><i class="fas fa-file-alt" style="font-size:15px;"></i> Business Plan</span>
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
        <a href="<?= upload_url($m['file_url']) ?>" target="_blank">
          <i class="fas fa-file-alt" style="font-size:15px;"></i>
          <?= e($m['original_name'] ?: 'Document') ?>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </section>
    <?php else: ?>
    <section class="stitch-section">
      <div class="stitch-nda-card" style="text-align:center;padding:32px;">
        <i class="fas fa-file-alt" style="font-size:36px;color:var(--color-text-muted);margin-bottom:12px;display:block;"></i>
        <h3 style="margin:0 0 8px;font-size:16px;">Documents are Premium</h3>
        <p style="font-size:13px;color:var(--color-text-muted);margin:0 0 16px;">Upgrade to a premium account to access financial statements, contracts, and reports.</p>
        <a href="<?= APP_URL ?>/upgrade" class="stitch-btn-primary" style="display:inline-block;font-size:13px;padding:8px 20px;text-decoration:none;">Upgrade to Premium</a>
      </div>
    </section>
    <?php endif; ?>

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
          <i class="fas fa-check" style="font-size:15px;"></i>
          Email <?= ($verification['email_verified'] ?? false) ? 'Verified' : 'Unverified' ?>
        </div>
        <div class="stitch-verif-item <?= ($verification['phone_verified'] ?? false) ? 'done' : '' ?>">
          <i class="fas fa-check" style="font-size:15px;"></i>
          Phone <?= ($verification['phone_verified'] ?? false) ? 'Verified' : 'Unverified' ?>
        </div>
        <div class="stitch-verif-item <?= ($verification['identity_verified'] ?? false) ? 'done' : '' ?>">
          <i class="fas fa-check" style="font-size:15px;"></i>
          Identity <?= ($verification['identity_verified'] ?? false) ? 'Verified' : 'Unverified' ?>
        </div>
        <div class="stitch-verif-item <?= ($verification['company_verified'] ?? false) ? 'done' : '' ?>">
          <i class="fas fa-check" style="font-size:15px;"></i>
          Company <?= ($verification['company_verified'] ?? false) ? 'Verified' : 'Unverified' ?>
        </div>
        <?php if ($vC >= 4): ?>
        <span class="stitch-verif-all">All verifications completed</span>
        <?php endif; ?>
      </div>
    </section>

    <!-- ── Related Opportunities ── -->
    <?php
    $relS = $db->prepare('SELECT id, business_name, slug, asking_price, annual_revenue, ebitda_pct, province FROM businesses WHERE sector_id = ? AND id != ? AND status = "approved" ORDER BY RAND() LIMIT 3');
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
            <div class="fallback"><i class="fas fa-image" style="font-size:40px;opacity:0.4;"></i></div>
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
        <?php elseif ($viewerIsPremium || $hasInquired || $hasMatch): ?>
        <button class="stitch-sidebar-cta" onclick="alert('Contact: <?= e($business['owner_name']) ?> — <?= e($business['owner_email']) ?>')">View Contact Details</button>
        <?php else: ?>
        <a href="<?= APP_URL ?>/upgrade" class="stitch-sidebar-cta" style="display:block;text-align:center;">Unlock Contact — Go Premium</a>
        <?php endif; ?>

        <?php if ($inquiryCount > 0): ?>
        <div class="stitch-sidebar-interest">
          <i class="fas fa-users" style="font-size:20px;"></i>
          <strong><?= $inquiryCount ?> investor<?= $inquiryCount !== 1 ? 's' : '' ?></strong> interested this month
        </div>
        <?php endif; ?>

        <!-- Auth butons for guests -->
        <?php if (!$userId): ?>
        <div class="stitch-sidebar-auth">
          <button class="stitch-auth-btn" onclick="location.href='<?= APP_URL ?>/login'">
            <i class="fab fa-google" style="font-size:22px;"></i>
            Continue with Google
          </button>
          <button class="stitch-auth-btn" onclick="location.href='<?= APP_URL ?>/login'">
            <i class="fab fa-linkedin-in" style="font-size:15px;"></i>
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

      <!-- About the Owner -->
      <div class="stitch-sidebar-card">
        <div class="stitch-card-header">
          <i class="fas fa-user-circle" style="font-size:15px;"></i>
          About the Owner
        </div>
        <?php if ($viewerIsPremium): ?>
        <div class="stitch-card-body" style="display:flex;gap:12px;align-items:center;">
          <?php if ($business['profile_photo']): ?>
          <img src="<?= upload_url($business['profile_photo']) ?>" alt="" style="width:44px;height:44px;border-radius:50%;object-fit:cover;">
          <?php else: ?>
          <div style="width:44px;height:44px;border-radius:50%;background:var(--color-bg-soft);display:flex;align-items:center;justify-content:center;font-size:18px;color:var(--color-text-muted);"><i class="fas fa-user"></i></div>
          <?php endif; ?>
          <div>
            <strong style="display:block;font-size:14px;"><?= e($business['owner_name']) ?></strong>
            <?php if ($business['owner_phone']): ?>
            <span style="font-size:13px;color:var(--color-text-muted);"><?= e($business['owner_phone']) ?></span>
            <?php endif; ?>
            <?php if ($business['company_name']): ?>
            <br><span style="font-size:12px;color:var(--color-text-muted);"><?= e($business['company_name']) ?></span>
            <?php endif; ?>
          </div>
        </div>
        <?php else: ?>
        <div class="stitch-card-body" style="text-align:center;padding:16px;">
          <i class="fas fa-lock" style="font-size:24px;color:var(--color-text-muted);margin-bottom:8px;display:block;"></i>
          <p style="font-size:13px;color:var(--color-text-muted);margin:0 0 10px;">Owner details are hidden. Upgrade to Premium to see the owner's name, phone, and company.</p>
          <a href="<?= APP_URL ?>/upgrade" class="stitch-btn-primary" style="display:block;text-align:center;font-size:13px;padding:8px 14px;text-decoration:none;">Upgrade to Premium</a>
        </div>
        <?php endif; ?>
      </div>

      <!-- Trust & Verification -->
      <?php if ($vC > 0): ?>
      <div class="stitch-sidebar-card">
        <div class="stitch-card-header">
          <i class="fas fa-check-circle" style="font-size:15px;"></i>
          Trust &amp; Verification
        </div>
        <div class="stitch-card-body">
          <div class="stitch-verif-row <?= ($verification['email_verified'] ?? false) ? 'done' : '' ?>">
            <span><i class="fas fa-check" style="font-size:13px;"></i> Email</span>
            <?php if ($verification['email_verified'] ?? false): ?>
            <span class="check"><i class="fas fa-check" style="font-size:13px;"></i></span>
            <?php endif; ?>
          </div>
          <div class="stitch-verif-row <?= ($verification['phone_verified'] ?? false) ? 'done' : '' ?>">
            <span><i class="fas fa-phone" style="font-size:13px;"></i> Phone</span>
            <?php if ($verification['phone_verified'] ?? false): ?>
            <span class="check"><i class="fas fa-check" style="font-size:13px;"></i></span>
            <?php endif; ?>
          </div>
          <div class="stitch-verif-row <?= ($verification['identity_verified'] ?? false) ? 'done' : '' ?>">
            <span><i class="fas fa-user" style="font-size:13px;"></i> Identity</span>
            <?php if ($verification['identity_verified'] ?? false): ?>
            <span class="check"><i class="fas fa-check" style="font-size:13px;"></i></span>
            <?php endif; ?>
          </div>
          <div class="stitch-verif-row <?= ($verification['company_verified'] ?? false) ? 'done' : '' ?>">
            <span><i class="fas fa-building" style="font-size:13px;"></i> Company</span>
            <?php if ($verification['company_verified'] ?? false): ?>
            <span class="check"><i class="fas fa-check" style="font-size:13px;"></i></span>
            <?php endif; ?>
          </div>
        </div>
        <div class="stitch-card-footer verified">
          <i class="fas fa-check-circle" style="font-size:15px;"></i>
          Verified Business
        </div>
      </div>
      <?php endif; ?>

      <!-- Recent Activity -->
      <div class="stitch-sidebar-card">
        <div class="stitch-card-header">
          <i class="fas fa-clock" style="font-size:15px;"></i>
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
          <i class="fas fa-lock" style="font-size:15px;"></i>
          Secure &amp; Confidential
        </div>
        <div class="stitch-card-body">
          <ul>
            <li><i class="fas fa-check" style="font-size:13px;"></i> NDA protection</li>
            <li><i class="fas fa-check" style="font-size:13px;"></i> Secure data room</li>
            <li><i class="fas fa-check" style="font-size:13px;"></i> Confidential inquiries</li>
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
      <img src="<?= upload_url($img['file_url']) ?>" alt="" loading="lazy">
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

<!-- Premium Upgrade -->
<div id="premium-modal" class="stitch-overlay" onclick="if(event.target===this)this.classList.remove('open')" role="dialog" aria-modal="true">
  <div class="stitch-overlay-content" onclick="event.stopImmediatePropagation()" style="max-width:440px;">
    <div class="stitch-overlay-header">
      <h3>Upgrade to Premium</h3>
      <button class="stitch-overlay-close" onclick="document.getElementById('premium-modal').classList.remove('open')">&times;</button>
    </div>
    <div style="padding:24px;text-align:center;">
      <i class="fas fa-crown" style="font-size:42px;color:var(--color-primary);margin-bottom:12px;display:block;"></i>
      <h4 style="margin:0 0 8px;font-size:17px;">Unlock Full Access</h4>
      <p style="font-size:13px;color:var(--color-text-muted);margin:0 0 20px;line-height:1.6;">
        Premium members can see owner contact details, financial documents, and reports. 
        Contact the admin to upgrade your account.
      </p>
      <a href="<?= APP_URL ?>/upgrade" class="stitch-btn-primary" style="display:inline-block;padding:10px 28px;text-decoration:none;">Request Upgrade</a>
      <button class="stitch-btn-secondary" style="display:block;width:100%;margin-top:10px;padding:8px;font-size:12px;" onclick="document.getElementById('premium-modal').classList.remove('open')">Maybe later</button>
    </div>
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
  if (saveBtn && !saveBtn.hasAttribute('data-toggled')) {
    saveBtn.setAttribute('data-toggled', '1');
  }
});
</script>

<?php $hidePublicFooter = true; require __DIR__ . '/../includes/footer.php'; ?>
