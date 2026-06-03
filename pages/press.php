<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'Press — ' . APP_NAME;
$pageDescription = 'Press and media resources for ' . APP_NAME . '. Coverage, brand assets, and media contact.';
$forcePublicHeader = true;
$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Press","item":"'.APP_URL.'/press"}
  ]
}</script>';
require __DIR__ . '/../includes/header.php';

$coverage = [
  ['Nepali Times', 'Apr 2026', 'New online marketplace connects Nepali businesses with local investors'],
  ['TechKhabar', 'Mar 2026', APP_NAME . ' aims to digitize Nepal\'s SME investment landscape'],
  ['Business 360', 'Feb 2026', 'How a Kathmandu startup is bringing M&A infrastructure to Nepal'],
];
$facts = [
  ['2,500+', 'Listings on the platform'],
  ['7', 'Provinces covered'],
  ['40+', 'Industries represented'],
];
?>
<?= $breadcrumbSchema ?>
<main class="pub-page">

<!-- Hero -->
<section class="pub-hero">
  <div class="pub-wrap">
    <div class="pub-breadcrumbs" style="font-size:0.82rem;color:var(--dash-ink-soft);margin-bottom:16px;">
      <a href="<?= APP_URL ?>/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a> <span style="margin:0 6px;">/</span>
      <span>Press</span>
    </div>
    <div class="pub-section-head left" style="margin-bottom:var(--space-5);">
      <span class="pub-eyebrow">Media</span>
      <h1 class="pub-h1">Press &amp; Media</h1>
      <p class="pub-lead">Writing about Nepal's SME and investment landscape? We're happy to help with data, interviews, and brand assets.</p>
    </div>
    <!-- Stats band -->
    <div class="pub-grid cols-3" style="max-width:560px;">
      <?php foreach ($facts as $f): ?>
      <div style="text-align:center;">
        <div style="font-size:2rem;font-weight:800;color:var(--dash-primary);font-family:var(--font-heading);"><?= e($f[0]) ?></div>
        <div style="font-size:0.82rem;color:var(--dash-ink-soft);"><?= e($f[1]) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- In the news -->
<section class="pub-section surface">
  <div class="pub-wrap">
    <div class="pub-section-head left">
      <h2 class="pub-h2">In the news</h2>
    </div>
    <div style="display:grid;gap:var(--space-3);">
      <?php foreach ($coverage as $c): ?>
      <div class="pub-card" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
        <div>
          <div class="pub-card-title"><?= e($c[0]) ?></div>
          <p class="pub-card-text" style="margin-top:4px;"><?= e($c[2]) ?></p>
        </div>
        <span class="pub-badge neutral"><?= e($c[1]) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Brand assets / media contact -->
<section class="pub-section tint tight">
  <div class="pub-wrap">
    <div class="pub-card">
      <div class="pub-section-head left" style="margin-bottom:var(--space-3);">
        <h2 class="pub-h2" style="font-size:1.2rem;">Brand assets &amp; media enquiries</h2>
      </div>
      <p class="pub-lead" style="margin-top:0;margin-bottom:var(--space-5);">For logos, executive bios, fact sheets, or interview requests, reach our media team.</p>
      <a href="mailto:press@asaancapital.com" class="btn btn-primary">press@asaancapital.com</a>
    </div>
  </div>
</section>

</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
