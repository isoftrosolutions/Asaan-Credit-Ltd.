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
?>
<main class="main-content" style="padding-top:0;">
<?= $breadcrumbSchema ?>
<div class="breadcrumbs container" style="padding-top:1rem;padding-bottom:1rem;font-size:0.85rem;color:var(--secondary-text);">
  <a href="<?= APP_URL ?>">Home</a> <span style="margin:0 0.5rem;">/</span>
  <span>Press</span>
</div>

<div class="container" style="max-width:760px;padding-bottom:4rem;">
  <h1 style="font-size:2.5rem;margin-bottom:0.5rem;">Press &amp; Media</h1>
  <p style="font-size:1.1rem;line-height:1.8;color:var(--secondary-text);">Writing about Nepal's SME and investment landscape? We're happy to help with data, interviews, and brand assets.</p>

  <h3 style="margin-top:2rem;">In the news</h3>
  <div style="display:grid;gap:0.75rem;">
    <div class="card card-compact" style="background:var(--surface);border-radius:16px;padding:1rem;border:1px solid var(--surface-container-high);">
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <div><strong>Nepali Times</strong><div style="font-size:0.85rem;color:var(--secondary-text);">New online marketplace connects Nepali businesses with local investors</div></div>
        <span style="font-size:0.75rem;color:var(--secondary-text);flex-shrink:0;margin-left:1rem;">Apr 2026</span>
      </div>
    </div>
    <div class="card card-compact" style="background:var(--surface);border-radius:16px;padding:1rem;border:1px solid var(--surface-container-high);">
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <div><strong>TechKhabar</strong><div style="font-size:0.85rem;color:var(--secondary-text);"><?= APP_NAME ?> aims to digitize Nepal's SME investment landscape</div></div>
        <span style="font-size:0.75rem;color:var(--secondary-text);flex-shrink:0;margin-left:1rem;">Mar 2026</span>
      </div>
    </div>
  </div>

  <div style="margin-top:2rem;padding:2rem;background:var(--surface-container);border-radius:1.5rem;">
    <strong>Media enquiries</strong><br>
    <a href="mailto:press@asaancapital.com" style="color:var(--brand-red);">press@asaancapital.com</a>
  </div>
</div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
