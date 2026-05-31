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
<main>
<section style="padding:48px 0 24px;background:#ffffff;">
  <div style="max-width:980px;margin:0 auto;padding:0 24px;">
    <div class="breadcrumbs" style="font-size:13px;color:#887271;font-family:Inter,sans-serif;margin-bottom:16px;">
      <a href="<?= APP_URL ?>/" style="color:#887271;text-decoration:none;">Home</a> <span style="margin:0 6px;">/</span>
      <span style="color:#554242;">Industry Watch</span>
    </div>
    <h1 style="font-size:36px;line-height:44px;font-weight:800;letter-spacing:-0.02em;color:#6B1D22;font-family:Montserrat,sans-serif;margin:0 0 14px;">Industry Watch</h1>
    <p style="font-size:17px;line-height:27px;color:#554242;font-family:Inter,sans-serif;margin:0;max-width:680px;">Sector trends and indicative valuation multiples across Nepal's key industries. Figures below are typical EV/EBITDA ranges used as a starting point for valuation — detailed quarterly reports are on the way.</p>
  </div>
</section>

<section style="padding:24px 0;background:#ffffff;">
  <div style="max-width:980px;margin:0 auto;padding:0 24px;">
    <div class="iw-grid" style="display:grid;gap:16px;">
      <?php foreach ($sectors as $s): ?>
      <div style="background:#fff;border:1px solid #dbc0bf4d;border-radius:12px;padding:20px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
          <span style="color:#6B1D22;font-family:'Material Symbols Outlined';font-size:26px;font-variation-settings:'FILL' 1;"><?= $s[0] ?></span>
          <span style="font-size:11px;font-weight:700;color:<?= $s[4] ?>;background:<?= $s[4] ?>1a;padding:3px 9px;border-radius:999px;font-family:Inter,sans-serif;"><?= e($s[3]) ?></span>
        </div>
        <h3 style="font-size:16px;font-weight:700;margin:12px 0 4px;color:#1c1b1b;font-family:Montserrat,sans-serif;"><?= e($s[1]) ?></h3>
        <div style="font-size:13px;color:#554242;font-family:Inter,sans-serif;">Typical multiple: <strong style="color:#6B1D22;"><?= e($s[2]) ?> EV/EBITDA</strong></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section style="padding:24px 0 56px;background:#ffffff;">
  <div style="max-width:980px;margin:0 auto;padding:0 24px;">
    <div style="padding:32px;background:#fcf9f8;border-radius:16px;border:1px solid #dbc0bf4d;text-align:center;">
      <h3 style="font-size:20px;font-weight:700;color:#6B1D22;font-family:Montserrat,sans-serif;margin:0 0 8px;">What's your business worth today?</h3>
      <p style="font-size:15px;line-height:23px;color:#554242;font-family:Inter,sans-serif;margin:0 0 20px;">Apply your sector's multiple to your own numbers with our free calculator.</p>
      <a href="<?= APP_URL ?>/business-valuation" style="display:inline-block;padding:12px 32px;border-radius:8px;font-weight:600;font-size:16px;color:#fff;background:#6B1D22;font-family:Inter,sans-serif;text-decoration:none;">Value My Business</a>
    </div>
  </div>
</section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
