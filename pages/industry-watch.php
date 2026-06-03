<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'Industry Watch — ' . APP_NAME;
$pageDescription = 'Sector trends, typical valuation multiples, and deal activity across Nepal\'s key industries.';
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

// Indicative EV/EBITDA trading multiple + a sample momentum signal per sector.
$sectors = [
  ['memory', 'Technology', '11x', 'Hot', '#1E7A4D'],
  ['account_balance', 'FinTech', '12x', 'Hot', '#1E7A4D'],
  ['agriculture', 'Agriculture', '5.5x', 'Steady', '#3b6281'],
  ['hotel', 'Hotels & Resorts', '8x', 'Recovering', '#C77A12'],
  ['factory', 'Manufacturing', '7x', 'Steady', '#3b6281'],
  ['health_and_safety', 'Healthcare', '10x', 'Hot', '#1E7A4D'],
  ['storefront', 'Retail', '6x', 'Steady', '#3b6281'],
  ['restaurant', 'Food & Beverage', '7x', 'Steady', '#3b6281'],
];
?>
<?= $breadcrumbSchema ?>
<style>
@media (min-width:768px){ .iw-grid{ grid-template-columns:repeat(3,1fr); } }
@media (max-width:767px){ .iw-grid{ grid-template-columns:repeat(2,1fr); } }
@media (max-width:479px){ .iw-grid{ grid-template-columns:1fr; } }
</style>
<main class="pub-page">

<section class="pub-section tight">
  <div class="pub-wrap">
    <div class="breadcrumbs pub-text" style="margin-bottom:var(--space-4);">
      <a href="<?= APP_URL ?>/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a> <span style="margin:0 6px;">/</span>
      <span>Industry Watch</span>
    </div>
    <h1 class="pub-h1">Industry Watch</h1>
    <p class="pub-lead" style="max-width:680px;">Sector trends and indicative valuation multiples across Nepal's key industries. Figures below are typical EV/EBITDA ranges used as a starting point for valuation — detailed quarterly reports are on the way.</p>
  </div>
</section>

<section class="pub-section tight" style="padding-top:0;">
  <div class="pub-wrap">
    <div class="iw-grid" style="display:grid;gap:var(--space-4);">
      <?php foreach ($sectors as $s): ?>
      <div class="pub-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
          <span style="color:var(--dash-primary);font-family:'Material Symbols Outlined';font-size:26px;font-variation-settings:'FILL' 1;"><?= $s[0] ?></span>
          <span style="font-size:11px;font-weight:700;color:<?= $s[4] ?>;background:<?= $s[4] ?>1a;padding:3px 9px;border-radius:999px;font-family:var(--font-body);"><?= e($s[3]) ?></span>
        </div>
        <h3 class="pub-h3" style="margin:var(--space-3) 0 var(--space-1);"><?= e($s[1]) ?></h3>
        <div class="pub-text">Typical multiple: <strong style="color:var(--dash-primary);"><?= e($s[2]) ?> EV/EBITDA</strong></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="pub-section tight">
  <div class="pub-wrap">
    <div class="pub-cta">
      <h2>What's your business worth today?</h2>
      <p>Apply your sector's multiple to your own numbers with our free calculator.</p>
      <div class="pub-cta-actions">
        <a href="<?= APP_URL ?>/business-valuation" class="btn btn-lg" style="background:#fff;color:var(--color-primary);font-weight:700;">Value My Business</a>
      </div>
    </div>
  </div>
</section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
