<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'Testimonials — ' . APP_NAME;
$pageDescription = 'Real stories from business owners and investors who found their match on ' . APP_NAME_LONG . '.';
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

$stats = [
  ['2,500+', 'Businesses listed'],
  ['12,800+', 'Successful matches'],
  ['NPR 850 Cr+', 'Deal value enabled'],
];
$testimonials = [
  ['RS', '#6B1D22', 'Rajesh Sharma', 'Business Owner, Kathmandu', 'Sold my manufacturing business to the 4th buyer introduced. Time taken: 3 months. The platform made it seamless.', 'Deal closed: NPR 8.5 Cr'],
  ['AG', '#3b6281', 'Anita Gurung', 'Angel Investor, Pokhara', 'Found two promising startups within my first month. The verification process gave me confidence to invest.', '2 deals closed'],
  ['BT', '#B45309', 'Bikash Thapa', 'Franchise Owner, Biratnagar', 'Expanded to three new cities by connecting with serious, verified franchise partners.', '3 franchise units'],
  ['SK', '#1E7A4D', 'Sunita Koirala', 'Corporate Buyer, Eastern Nepal', 'Confidentiality until mutual match meant I could explore deals without tipping off competitors.', 'Acquired 1 business'],
];
?>
<?= $breadcrumbSchema ?>
<style>
@media (min-width:768px){ .tm-grid{ grid-template-columns:repeat(2,1fr); } }
@media (max-width:767px){ .tm-grid{ grid-template-columns:1fr; } }
</style>
<main class="pub-page">

<section class="pub-section tight">
  <div class="pub-wrap">
    <div class="breadcrumbs pub-text" style="margin-bottom:var(--space-4);">
      <a href="<?= APP_URL ?>/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a> <span style="margin:0 6px;">/</span>
      <span>Testimonials</span>
    </div>
    <h1 class="pub-h1">Trusted by business owners &amp; investors</h1>
    <p class="pub-lead">Real stories from real users who found their perfect match.</p>
  </div>
</section>

<section class="pub-section tight" style="padding-top:0;">
  <div class="pub-wrap">
    <div class="pub-statstrip" style="padding:var(--space-5);background:var(--dash-card);border-radius:var(--dash-radius-card);border:1px solid var(--dash-border);">
      <?php foreach ($stats as $s): ?>
      <div class="pub-statstrip-item">
        <div class="pub-statstrip-num"><?= e($s[0]) ?></div>
        <div class="pub-statstrip-label"><?= e($s[1]) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="pub-section">
  <div class="pub-wrap">
    <div class="tm-grid" style="display:grid;gap:var(--space-5);">
      <?php foreach ($testimonials as $t): ?>
      <div class="pub-card">
        <p class="pub-text" style="margin:0 0 var(--space-4);">&ldquo;<?= e($t[4]) ?>&rdquo;</p>
        <div style="display:flex;align-items:center;gap:var(--space-3);">
          <div style="width:44px;height:44px;border-radius:50%;background:<?= $t[1] ?>;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:15px;flex-shrink:0;font-family:var(--font-heading);"><?= e($t[0]) ?></div>
          <div style="flex:1;">
            <div class="pub-card-title" style="font-size:.95rem;"><?= e($t[2]) ?></div>
            <div class="pub-text" style="font-size:.82rem;"><?= e($t[3]) ?></div>
          </div>
          <span class="pub-badge success" style="white-space:nowrap;"><?= e($t[5]) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div style="margin-top:var(--space-8);text-align:center;">
      <a href="<?= APP_URL ?>/onboarding" class="btn btn-primary btn-lg">Join them — get started free</a>
    </div>
  </div>
</section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
