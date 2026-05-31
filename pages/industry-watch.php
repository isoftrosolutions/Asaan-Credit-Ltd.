<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'Industry Watch — ' . APP_NAME;
$pageDescription = 'Sector trends, valuation multiples, and deal activity across Nepal\'s key industries.';
$forcePublicHeader = true;
$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Industry Watch","item":"'.APP_URL.'/industry-watch"}
  ]
}</script>';
require __DIR__ . '/../includes/header.php';

$sectors = ['Technology', 'Agriculture', 'Hotels & Resorts', 'Manufacturing', 'Healthcare', 'Retail', 'Food & Beverage', 'Education'];
?>
<main class="main-content" style="padding-top:0;">
<?= $breadcrumbSchema ?>
<div class="breadcrumbs container" style="padding-top:1rem;padding-bottom:1rem;font-size:0.85rem;color:var(--secondary-text);">
  <a href="<?= APP_URL ?>">Home</a> <span style="margin:0 0.5rem;">/</span>
  <span>Industry Watch</span>
</div>

<div class="container" style="max-width:760px;padding-bottom:4rem;">
  <h1 style="font-size:2.5rem;margin-bottom:0.5rem;">Industry Watch</h1>
  <p style="font-size:1.1rem;line-height:1.8;color:var(--secondary-text);">Sector trends, typical valuation multiples, and deal activity across Nepal's key industries. Detailed reports are on the way.</p>

  <h3 style="margin-top:2rem;">Sectors we track</h3>
  <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:2rem;">
    <?php foreach ($sectors as $s): ?>
    <span style="background:var(--surface);border:1px solid var(--surface-container-high);border-radius:999px;padding:8px 16px;font-size:0.85rem;font-weight:500;"><?= e($s) ?></span>
    <?php endforeach; ?>
  </div>

  <div style="padding:2rem;background:var(--surface-container);border-radius:1.5rem;text-align:center;">
    <p style="margin:0 0 1rem;color:var(--secondary-text);">Want an estimate for your sector today? Try our free valuation calculator.</p>
    <a href="<?= APP_URL ?>/business-valuation" class="btn btn-primary">Value My Business</a>
  </div>
</div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
