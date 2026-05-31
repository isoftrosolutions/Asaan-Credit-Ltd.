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

$values = [
  ['rocket_launch', 'Ownership', 'We hire people who take problems end-to-end and care about the outcome, not just the task.'],
  ['groups', 'Trust first', 'Verification and confidentiality are our promise to users — and how we treat each other.'],
  ['insights', 'Clarity', 'Simple beats clever. We explain hard things plainly, to users and to ourselves.'],
  ['favorite', 'Care', 'Real businesses and livelihoods are on the platform. We sweat the details for them.'],
];
$perks = ['Flexible, outcome-driven work', 'Direct impact on a growing product', 'Learning budget', 'A small, senior team', 'Work that matters for Nepal\'s SMEs'];
?>
<?= $breadcrumbSchema ?>
<style>
@media (min-width:768px){ .cr-values{ grid-template-columns:repeat(2,1fr); } }
@media (max-width:767px){ .cr-values{ grid-template-columns:1fr; } }
</style>
<main>
<section style="padding:48px 0 24px;background:#ffffff;">
  <div style="max-width:900px;margin:0 auto;padding:0 24px;">
    <div class="breadcrumbs" style="font-size:13px;color:#887271;font-family:Inter,sans-serif;margin-bottom:16px;">
      <a href="<?= APP_URL ?>/" style="color:#887271;text-decoration:none;">Home</a> <span style="margin:0 6px;">/</span>
      <span style="color:#554242;">Careers</span>
    </div>
    <h1 style="font-size:36px;line-height:44px;font-weight:800;letter-spacing:-0.02em;color:#6B1D22;font-family:Montserrat,sans-serif;margin:0 0 14px;">Build the future of Nepali business with us</h1>
    <p style="font-size:17px;line-height:27px;color:#554242;font-family:Inter,sans-serif;margin:0;">We're a small, ambitious team on a mission to give every Nepali business owner and investor a trusted place to connect. We hire for curiosity, ownership, and care.</p>
  </div>
</section>

<section style="padding:24px 0;background:#fcf9f8;">
  <div style="max-width:900px;margin:0 auto;padding:0 24px;">
    <h2 style="font-size:24px;font-weight:700;color:#6B1D22;font-family:Montserrat,sans-serif;margin:0 0 20px;">What we value</h2>
    <div class="cr-values" style="display:grid;gap:16px;">
      <?php foreach ($values as $v): ?>
      <div style="background:#fff;border-radius:12px;border:1px solid #dbc0bf4d;padding:20px;">
        <span style="color:#6B1D22;font-family:'Material Symbols Outlined';font-size:28px;font-variation-settings:'FILL' 1;"><?= $v[0] ?></span>
        <h3 style="font-size:16px;font-weight:700;margin:10px 0 6px;color:#1c1b1b;font-family:Montserrat,sans-serif;"><?= e($v[1]) ?></h3>
        <p style="font-size:14px;line-height:21px;color:#554242;font-family:Inter,sans-serif;margin:0;"><?= e($v[2]) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section style="padding:40px 0;background:#ffffff;">
  <div style="max-width:900px;margin:0 auto;padding:0 24px;">
    <h2 style="font-size:24px;font-weight:700;color:#6B1D22;font-family:Montserrat,sans-serif;margin:0 0 16px;">Why join</h2>
    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:32px;">
      <?php foreach ($perks as $p): ?>
      <span style="display:inline-flex;align-items:center;gap:6px;background:#fcf9f8;border:1px solid #dbc0bf4d;border-radius:999px;padding:8px 16px;font-size:14px;font-weight:500;color:#554242;font-family:Inter,sans-serif;">
        <span style="color:#1E7A4D;font-family:'Material Symbols Outlined';font-size:16px;font-variation-settings:'FILL' 1;">check_circle</span><?= e($p) ?>
      </span>
      <?php endforeach; ?>
    </div>

    <div style="padding:32px;background:linear-gradient(135deg,#6B1D22,#4A1317);border-radius:16px;text-align:center;">
      <h3 style="font-size:22px;font-weight:700;color:#fff;font-family:Montserrat,sans-serif;margin:0 0 8px;">No open roles right now</h3>
      <p style="font-size:15px;line-height:23px;color:rgba(255,255,255,0.85);font-family:Inter,sans-serif;margin:0 0 20px;">We're not actively hiring, but we're always glad to meet talented people. Send your CV and a note about how you'd like to contribute.</p>
      <a href="mailto:careers@asaancapital.com" style="display:inline-block;padding:12px 32px;border-radius:8px;font-weight:600;font-size:16px;color:#6B1D22;background:#fff;font-family:Inter,sans-serif;text-decoration:none;">careers@asaancapital.com</a>
    </div>
  </div>
</section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
