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
<main>
<section style="padding:48px 0 24px;background:#ffffff;">
  <div style="max-width:980px;margin:0 auto;padding:0 24px;">
    <div class="breadcrumbs" style="font-size:13px;color:#887271;font-family:Inter,sans-serif;margin-bottom:16px;">
      <a href="<?= APP_URL ?>/" style="color:#887271;text-decoration:none;">Home</a> <span style="margin:0 6px;">/</span>
      <span style="color:#554242;">Testimonials</span>
    </div>
    <h1 style="font-size:36px;line-height:44px;font-weight:800;letter-spacing:-0.02em;color:#6B1D22;font-family:Montserrat,sans-serif;margin:0 0 14px;">Trusted by business owners &amp; investors</h1>
    <p style="font-size:17px;line-height:27px;color:#554242;font-family:Inter,sans-serif;margin:0;">Real stories from real users who found their perfect match.</p>
  </div>
</section>

<section style="padding:8px 0 16px;background:#ffffff;">
  <div style="max-width:980px;margin:0 auto;padding:0 24px;">
    <div style="display:flex;flex-wrap:wrap;gap:24px;padding:24px;background:#fcf9f8;border-radius:16px;border:1px solid #dbc0bf4d;">
      <?php foreach ($stats as $s): ?>
      <div style="flex:1;min-width:140px;text-align:center;">
        <div style="font-size:28px;font-weight:800;color:#6B1D22;font-family:Montserrat,sans-serif;"><?= e($s[0]) ?></div>
        <div style="font-size:13px;color:#554242;font-family:Inter,sans-serif;"><?= e($s[1]) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section style="padding:24px 0 56px;background:#ffffff;">
  <div style="max-width:980px;margin:0 auto;padding:0 24px;">
    <div class="tm-grid" style="display:grid;gap:20px;">
      <?php foreach ($testimonials as $t): ?>
      <div style="background:#fff;border:1px solid #dbc0bf4d;border-radius:16px;padding:24px;box-shadow:0 1px 2px rgba(0,0,0,0.04);">
        <p style="font-size:16px;line-height:26px;color:#1c1b1b;font-family:Inter,sans-serif;margin:0 0 16px;">&ldquo;<?= e($t[4]) ?>&rdquo;</p>
        <div style="display:flex;align-items:center;gap:12px;">
          <div style="width:44px;height:44px;border-radius:50%;background:<?= $t[1] ?>;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:15px;flex-shrink:0;font-family:Montserrat,sans-serif;"><?= e($t[0]) ?></div>
          <div style="flex:1;">
            <div style="font-weight:700;font-size:15px;color:#1c1b1b;font-family:Montserrat,sans-serif;"><?= e($t[2]) ?></div>
            <div style="font-size:13px;color:#554242;font-family:Inter,sans-serif;"><?= e($t[3]) ?></div>
          </div>
          <span style="font-size:12px;font-weight:700;color:#1E7A4D;background:rgba(30,122,77,0.1);padding:4px 10px;border-radius:999px;font-family:Inter,sans-serif;white-space:nowrap;"><?= e($t[5]) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div style="margin-top:32px;text-align:center;">
      <a href="<?= APP_URL ?>/signup" style="display:inline-block;padding:12px 32px;border-radius:8px;font-weight:600;font-size:16px;color:#fff;background:#6B1D22;font-family:Inter,sans-serif;text-decoration:none;">Join them — get started free</a>
    </div>
  </div>
</section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
