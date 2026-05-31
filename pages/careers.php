<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'Careers — ' . APP_NAME;
$pageDescription = 'Join the team building Nepal\'s trusted marketplace for business matching, M&A, and fundraising.';
$forcePublicHeader = true;
$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Careers","item":"'.APP_URL.'/careers"}
  ]
}</script>';
require __DIR__ . '/../includes/header.php';
?>
<main class="main-content" style="padding-top:0;">
<?= $breadcrumbSchema ?>
<div class="breadcrumbs container" style="padding-top:1rem;padding-bottom:1rem;font-size:0.85rem;color:var(--secondary-text);">
  <a href="<?= APP_URL ?>">Home</a> <span style="margin:0 0.5rem;">/</span>
  <span>Careers</span>
</div>

<div class="container" style="max-width:760px;padding-bottom:4rem;">
  <h1 style="font-size:2.5rem;margin-bottom:0.5rem;">Build the future of Nepali business with us</h1>
  <p style="font-size:1.1rem;line-height:1.8;color:var(--secondary-text);">We're a small, ambitious team on a mission to give every Nepali business owner and investor a trusted place to connect. We hire for curiosity, ownership, and care.</p>

  <div style="margin:2rem 0;padding:2rem;background:var(--surface-container);border-radius:1.5rem;text-align:center;">
    <h4 style="margin:0 0 0.5rem;">No open roles right now</h4>
    <p style="margin:0 0 1rem;color:var(--secondary-text);">We're not actively hiring, but we're always glad to meet talented people. Send us your CV and a note about how you'd like to contribute.</p>
    <a href="mailto:careers@asaancapital.com" class="btn btn-primary">careers@asaancapital.com</a>
  </div>
</div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
