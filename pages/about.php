<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'Our Story — ' . APP_NAME;
$pageDescription = 'Learn the story behind Asaan Capital Ltd - Financial & Investment Services. Nepal\'s trusted marketplace for business matching, M&A, and fundraising.';

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Our Story","item":"'.APP_URL.'/about"}
  ]
}</script>';
$forcePublicHeader = true;
require __DIR__ . '/../includes/header.php';
?>
<main class="main-content" style="padding-top:0;">

<?= $breadcrumbSchema ?>
<div class="breadcrumbs container" style="padding-top:1rem;padding-bottom:1rem;font-size:0.85rem;color:var(--secondary-text);">
  <a href="<?= APP_URL ?>">Home</a> <span style="margin:0 0.5rem;">/</span>
  <span>Our Story</span>
</div>

<div class="container" style="max-width:900px; padding-bottom:4rem;">
  <h1 style="font-size:3rem; margin-bottom:0.5rem;">Nepal's trusted marketplace.<br>Built for business growth.</h1>

  <p style="font-size:1.1rem;line-height:1.8;color:var(--secondary-text);">For too long, Nepali business owners and investors have relied on fragmented networks, Facebook groups, and word-of-mouth to find the right opportunities. <?= APP_NAME ?> changes that — a single, trusted platform where verified business owners, investors, franchise brands, and advisors connect and close deals with confidence.

  <div style="margin:3rem 0; display:grid; grid-template-columns:repeat(auto-fit, minmax(280px,1fr)); gap:2rem;">
    <div class="card">
      <h4 style="margin-bottom:0.5rem;">Why we exist</h4>
      <p style="margin:0;color:var(--secondary-text);">Nepal's SME sector is full of potential — but fragmented. There is no centralized infrastructure for business matching, M&A, or fundraising. <?= APP_NAME ?> fills that gap with a secure, verified marketplace designed for Nepal.</p>
    </div>
    <div class="card">
      <h4 style="margin-bottom:0.5rem;">Our promise</h4>
      <p style="margin:0;color:var(--secondary-text);">Every profile — business, investor, advisor, or franchise — is manually verified by our analysts. No bots. No fake listings. No time wasters. Just real opportunities from real people.</p>
    </div>
    <div class="card">
      <h4 style="margin-bottom:0.5rem;">Our reach</h4>
      <p style="margin:0;color:var(--secondary-text);">Thousands of pre-screened businesses and verified investors across all 7 provinces of Nepal. Investment sizes from NPR 20 lakh to NPR 100 crore across 40+ industries.</p>
    </div>
  </div>

  <h3>Who Uses <?= APP_NAME ?></h3>
  <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:3rem;">
    <?php $personas = ['Businesses For Sale', 'Companies Seeking Capital', 'Franchise Brands', 'M&amp;A Advisors', 'Business Brokers', 'Private Investors', 'Corporate Acquirers', 'Lenders', 'PE / VC Firms', 'Family Offices', 'Deal Professionals']; ?>
    <?php foreach ($personas as $p): ?>
    <span style="background:var(--surface);border:1px solid var(--surface-container-high);border-radius:999px;padding:8px 16px;font-size:0.85rem;font-weight:500;"><?= $p ?></span>
    <?php endforeach; ?>
  </div>

  <h3>Our Journey</h3>
  <ul style="list-style:none;padding:0;margin-bottom:2rem;">
    <?php $milestones = [['2024', 'Asaan Credit Ltd established with a vision to transform business matching in Nepal'], ['2024', 'Platform development begins in partnership with iSoftro Solutions'], ['2025', 'Beta launch — onboarded first 200 businesses and 50 investors'], ['2025', 'Official public launch with verified profiles across all 7 provinces'], ['2026', 'Expanded to 2,500+ listings, franchise vertical launched, advisor network introduced']]; ?>
    <?php foreach ($milestones as $m): ?>
    <li style="padding:0.5rem 0;border-bottom:1px solid var(--surface-container-high);display:flex;gap:1rem;">
      <span style="font-weight:700;color:var(--brand-red);min-width:60px;"><?= e($m[0]) ?></span>
      <span style="color:var(--secondary-text);"><?= $m[1] ?></span>
    </li>
    <?php endforeach; ?>
  </ul>

  <h3>Press &amp; Media</h3>
  <div style="display:grid;gap:0.75rem;margin-bottom:3rem;">
    <div class="card card-compact" style="background:var(--surface);border-radius:16px;padding:1rem;border:1px solid var(--surface-container-high);">
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
          <strong>Nepali Times</strong>
          <div style="font-size:0.85rem;color:var(--secondary-text);">New online marketplace connects Nepali businesses with local investors</div>
        </div>
        <span style="font-size:0.75rem;color:var(--secondary-text);flex-shrink:0;margin-left:1rem;">Apr 2026</span>
      </div>
    </div>
    <div class="card card-compact" style="background:var(--surface);border-radius:16px;padding:1rem;border:1px solid var(--surface-container-high);">
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
          <strong>TechKhabar</strong>
          <div style="font-size:0.85rem;color:var(--secondary-text);">Asaan Capital Ltd aims to digitize Nepal's SME investment landscape</div>
        </div>
        <span style="font-size:0.75rem;color:var(--secondary-text);flex-shrink:0;margin-left:1rem;">Mar 2026</span>
      </div>
    </div>
  </div>

  <h3>The Team</h3>
  <p style="color:var(--secondary-text);"><?= APP_NAME ?> is developed and operated by <strong>Asaan Credit Ltd</strong>, built in partnership with <strong>iSoftro Solutions</strong>, in close collaboration with Nepal's startup ecosystem leaders, legal experts, and active investors.</p>

  <div style="margin-top:2rem; padding:2rem; background:var(--surface-container); border-radius:2rem;">
    <strong>Contact</strong><br>
    Asaan Credit Ltd<br>
    Kathmandu, Nepal<br>
    Email: <a href="mailto:hello@asaancapital.com" style="color:var(--brand-red);">hello@asaancapital.com</a>
  </div>
</div>

</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
