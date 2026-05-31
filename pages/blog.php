<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'Blog — ' . APP_NAME;
$pageDescription = 'Insights on buying, selling, valuing, and funding businesses in Nepal from the ' . APP_NAME . ' team.';
$forcePublicHeader = true;
$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Blog","item":"'.APP_URL.'/blog"}
  ]
}</script>';
require __DIR__ . '/../includes/header.php';
?>
<main class="main-content" style="padding-top:0;">
<?= $breadcrumbSchema ?>
<div class="breadcrumbs container" style="padding-top:1rem;padding-bottom:1rem;font-size:0.85rem;color:var(--secondary-text);">
  <a href="<?= APP_URL ?>">Home</a> <span style="margin:0 0.5rem;">/</span>
  <span>Blog</span>
</div>

<div class="container" style="max-width:760px;padding-bottom:4rem;">
  <h1 style="font-size:2.5rem;margin-bottom:0.5rem;">Blog</h1>
  <p style="font-size:1.1rem;line-height:1.8;color:var(--secondary-text);">Practical guides on buying, selling, valuing, and funding businesses in Nepal.</p>

  <div style="margin:2rem 0;padding:2rem;background:var(--surface-container);border-radius:1.5rem;text-align:center;">
    <h4 style="margin:0 0 0.5rem;">Articles coming soon</h4>
    <p style="margin:0 0 1rem;color:var(--secondary-text);">We're preparing our first set of guides. In the meantime, see how the platform works or value your business.</p>
    <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;">
      <a href="<?= APP_URL ?>/how-it-works" class="btn btn-primary">How It Works</a>
      <a href="<?= APP_URL ?>/business-valuation" class="btn btn-outline">Value My Business</a>
    </div>
  </div>
</div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
