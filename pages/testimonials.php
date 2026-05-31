<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'Testimonials — ' . APP_NAME;
$pageDescription = 'Real stories from business owners and investors who found their match on ' . APP_NAME . '.';
$forcePublicHeader = true;
$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Testimonials","item":"'.APP_URL.'/testimonials"}
  ]
}</script>';
require __DIR__ . '/../includes/header.php';

$testimonials = [
  ['Rajesh Sharma', 'Business Owner, Kathmandu', 'Sold my manufacturing business to the 4th buyer introduced. Time taken: 3 months. The platform made it seamless.'],
  ['Anita Gurung', 'Angel Investor, Pokhara', 'Found two promising startups within my first month. The verification process gave me confidence to invest.'],
  ['Bikash Thapa', 'Franchise Owner, Biratnagar', 'Expanded to three new cities by connecting with serious franchise partners — all verified.'],
  ['Sunita Koirala', 'Corporate Buyer, Eastern Nepal', 'Confidentiality until mutual match meant I could explore deals without tipping off competitors.'],
];
?>
<main class="main-content" style="padding-top:0;">
<?= $breadcrumbSchema ?>
<div class="breadcrumbs container" style="padding-top:1rem;padding-bottom:1rem;font-size:0.85rem;color:var(--secondary-text);">
  <a href="<?= APP_URL ?>">Home</a> <span style="margin:0 0.5rem;">/</span>
  <span>Testimonials</span>
</div>

<div class="container" style="max-width:860px;padding-bottom:4rem;">
  <h1 style="font-size:2.5rem;margin-bottom:0.5rem;">Trusted by business owners &amp; investors</h1>
  <p style="font-size:1.1rem;line-height:1.8;color:var(--secondary-text);">Real stories from real users who found their perfect match.</p>

  <div style="margin-top:2rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1.5rem;">
    <?php foreach ($testimonials as $t): ?>
    <div class="card">
      <p style="font-size:1rem;line-height:1.7;color:var(--ink);margin:0 0 1rem;">&ldquo;<?= e($t[2]) ?>&rdquo;</p>
      <div style="font-weight:700;"><?= e($t[0]) ?></div>
      <div style="font-size:0.85rem;color:var(--secondary-text);"><?= e($t[1]) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
