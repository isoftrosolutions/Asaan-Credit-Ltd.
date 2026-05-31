<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'Contact Us — ' . APP_NAME;
$pageDescription = 'Get in touch with ' . APP_NAME . '. Reach our team for support, partnerships, and media enquiries.';
$forcePublicHeader = true;
$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Contact Us","item":"'.APP_URL.'/contact"}
  ]
}</script>';
require __DIR__ . '/../includes/header.php';
?>
<main class="main-content" style="padding-top:0;">
<?= $breadcrumbSchema ?>
<div class="breadcrumbs container" style="padding-top:1rem;padding-bottom:1rem;font-size:0.85rem;color:var(--secondary-text);">
  <a href="<?= APP_URL ?>">Home</a> <span style="margin:0 0.5rem;">/</span>
  <span>Contact Us</span>
</div>

<div class="container" style="max-width:760px;padding-bottom:4rem;">
  <h1 style="font-size:2.5rem;margin-bottom:0.5rem;">Contact Us</h1>
  <p style="font-size:1.1rem;line-height:1.8;color:var(--secondary-text);">Have a question, partnership idea, or need a hand getting started? We'd love to hear from you.</p>

  <div style="margin:2rem 0;display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.5rem;">
    <div class="card">
      <h4 style="margin-bottom:0.5rem;">General &amp; Support</h4>
      <p style="margin:0;color:var(--secondary-text);">For help with your account, listings, or connections.</p>
      <p style="margin:0.75rem 0 0;"><a href="mailto:hello@asaancapital.com" style="color:var(--brand-red);font-weight:600;">hello@asaancapital.com</a></p>
    </div>
    <div class="card">
      <h4 style="margin-bottom:0.5rem;">Partnerships &amp; Media</h4>
      <p style="margin:0;color:var(--secondary-text);">For press, advisors, and business development.</p>
      <p style="margin:0.75rem 0 0;"><a href="mailto:partnerships@asaancapital.com" style="color:var(--brand-red);font-weight:600;">partnerships@asaancapital.com</a></p>
    </div>
  </div>

  <div style="padding:2rem;background:var(--surface-container);border-radius:1.5rem;">
    <strong>Asaan Credit Ltd</strong><br>
    Kathmandu, Nepal<br>
    Email: <a href="mailto:hello@asaancapital.com" style="color:var(--brand-red);">hello@asaancapital.com</a>
  </div>
</div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
