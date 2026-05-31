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
<main>
<section style="padding:48px 0 24px;background:#ffffff;">
  <div style="max-width:900px;margin:0 auto;padding:0 24px;">
    <div class="breadcrumbs" style="font-size:13px;color:#887271;font-family:Inter,sans-serif;margin-bottom:16px;">
      <a href="<?= APP_URL ?>/" style="color:#887271;text-decoration:none;">Home</a> <span style="margin:0 6px;">/</span>
      <span style="color:#554242;">Press</span>
    </div>
    <h1 style="font-size:36px;line-height:44px;font-weight:800;letter-spacing:-0.02em;color:#6B1D22;font-family:Montserrat,sans-serif;margin:0 0 14px;">Press &amp; Media</h1>
    <p style="font-size:17px;line-height:27px;color:#554242;font-family:Inter,sans-serif;margin:0;">Writing about Nepal's SME and investment landscape? We're happy to help with data, interviews, and brand assets.</p>
  </div>
</section>

<section style="padding:8px 0 16px;background:#ffffff;">
  <div style="max-width:900px;margin:0 auto;padding:0 24px;">
    <div style="display:flex;flex-wrap:wrap;gap:24px;padding:24px;background:#fcf9f8;border-radius:16px;border:1px solid #dbc0bf4d;">
      <?php foreach ($facts as $f): ?>
      <div style="flex:1;min-width:140px;text-align:center;">
        <div style="font-size:30px;font-weight:800;color:#6B1D22;font-family:Montserrat,sans-serif;"><?= e($f[0]) ?></div>
        <div style="font-size:13px;color:#554242;font-family:Inter,sans-serif;"><?= e($f[1]) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section style="padding:32px 0;background:#ffffff;">
  <div style="max-width:900px;margin:0 auto;padding:0 24px;">
    <h2 style="font-size:24px;font-weight:700;color:#6B1D22;font-family:Montserrat,sans-serif;margin:0 0 16px;">In the news</h2>
    <div style="display:grid;gap:12px;">
      <?php foreach ($coverage as $c): ?>
      <div style="background:#fff;border:1px solid #dbc0bf4d;border-radius:12px;padding:18px;display:flex;justify-content:space-between;align-items:center;gap:16px;">
        <div>
          <strong style="font-size:15px;color:#1c1b1b;font-family:Montserrat,sans-serif;"><?= e($c[0]) ?></strong>
          <div style="font-size:14px;color:#554242;font-family:Inter,sans-serif;margin-top:2px;"><?= e($c[2]) ?></div>
        </div>
        <span style="font-size:13px;color:#887271;font-family:Inter,sans-serif;flex-shrink:0;"><?= e($c[1]) ?></span>
      </div>
      <?php endforeach; ?>
    </div>

    <div style="margin-top:28px;display:grid;gap:16px;grid-template-columns:1fr;">
      <div style="padding:24px;background:#fcf9f8;border-radius:16px;border:1px solid #dbc0bf4d;">
        <h3 style="font-size:18px;font-weight:700;color:#1c1b1b;font-family:Montserrat,sans-serif;margin:0 0 8px;">Brand assets &amp; media enquiries</h3>
        <p style="font-size:15px;line-height:23px;color:#554242;font-family:Inter,sans-serif;margin:0 0 16px;">For logos, executive bios, fact sheets, or interview requests, reach our media team.</p>
        <a href="mailto:press@asaancapital.com" style="display:inline-block;padding:11px 28px;border-radius:8px;font-weight:600;font-size:15px;color:#fff;background:#6B1D22;font-family:Inter,sans-serif;text-decoration:none;">press@asaancapital.com</a>
      </div>
    </div>
  </div>
</section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
