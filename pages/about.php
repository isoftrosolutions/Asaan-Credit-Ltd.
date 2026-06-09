<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'Our Story — ' . APP_NAME_LONG;
$pageDescription = 'Learn the story behind ' . APP_NAME_LONG . '. Nepal\'s trusted marketplace for business matching, M&A, and fundraising.';

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
<main class="pub-page">

<?= $breadcrumbSchema ?>

<!-- Hero -->
<section class="pub-hero">
  <div class="pub-wrap-narrow">
    <div class="pub-breadcrumbs" style="font-size:0.82rem;color:var(--dash-ink-soft);margin-bottom:16px;">
      <a href="<?= APP_URL ?>" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a>
      <span style="margin:0 6px;">/</span>
      <span>Our Story</span>
    </div>
    <div class="pub-section-head left" style="margin-bottom:0;">
      <h1 class="pub-h1">Nepal's trusted marketplace.<br>Built for business growth.</h1>
    </div>
  </div>
</section>

<!-- Intro image + lead -->
<section class="pub-section surface tight">
  <div class="pub-wrap-narrow">
    <img src="/assets/about-team.jpg" alt="The Asaan Capital team at our Kathmandu office" loading="lazy" style="width:100%;height:auto;border-radius:1.25rem;margin-bottom:var(--space-6);box-shadow:0 8px 28px rgba(0,0,0,0.10);object-fit:cover;">
    <div class="pub-prose">
      <p>For too long, Nepali business owners and investors have relied on fragmented networks, Facebook groups, and word-of-mouth to find the right opportunities. <?= APP_NAME_LONG ?> changes that — a single, trusted platform where verified business owners, investors, franchise brands, and advisors connect and close deals with confidence.</p>
    </div>
  </div>
</section>

<!-- Why / Promise / Reach -->
<section class="pub-section tint">
  <div class="pub-wrap-narrow">
    <div class="pub-grid cols-3">
      <div class="pub-card">
        <div class="pub-card-title">Why we exist</div>
        <p class="pub-card-text">Nepal's SME sector is full of potential — but fragmented. There is no centralized infrastructure for business matching, M&amp;A, or fundraising. <?= APP_NAME_LONG ?> fills that gap with a secure, verified marketplace designed for Nepal.</p>
      </div>
      <div class="pub-card">
        <div class="pub-card-title">Our promise</div>
        <p class="pub-card-text">Every profile — business, investor, advisor, or franchise — is manually verified by our analysts. No bots. No fake listings. No time wasters. Just real opportunities from real people.</p>
      </div>
      <div class="pub-card">
        <div class="pub-card-title">Our reach</div>
        <p class="pub-card-text">Thousands of pre-screened businesses and verified investors across all 7 provinces of Nepal. Investment sizes from NPR 20 lakh to NPR 100 crore across 40+ industries.</p>
      </div>
    </div>
  </div>
</section>

<!-- Who Uses -->
<section class="pub-section surface tight">
  <div class="pub-wrap-narrow">
    <div class="pub-section-head left">
      <h2 class="pub-h2">Who Uses <?= APP_NAME_LONG ?></h2>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
      <?php $personas = ['Businesses For Sale', 'Companies Seeking Capital', 'Franchise Brands', 'M&amp;A Advisors', 'Business Brokers', 'Private Investors', 'Corporate Acquirers', 'Lenders', 'PE / VC Firms', 'Family Offices', 'Deal Professionals']; ?>
      <?php foreach ($personas as $p): ?>
      <span class="pub-badge neutral"><?= $p ?></span>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Journey timeline -->
<section class="pub-section tint">
  <div class="pub-wrap-narrow">
    <div class="pub-section-head left">
      <h2 class="pub-h2">Our Journey</h2>
    </div>
    <ul style="list-style:none;padding:0;margin:0;">
      <?php $milestones = [['2024', 'Asaan Credit Ltd established with a vision to transform business matching in Nepal'], ['2024', 'Platform development begins in partnership with iSoftro Solutions'], ['2025', 'Beta launch — onboarded first 200 businesses and 50 investors'], ['2025', 'Official public launch with verified profiles across all 7 provinces'], ['2026', 'Expanded to 2,500+ listings, franchise vertical launched, advisor network introduced']]; ?>
      <?php foreach ($milestones as $m): ?>
      <li style="padding:0.75rem 0;border-bottom:1px solid var(--dash-border);display:flex;gap:1rem;align-items:flex-start;">
        <span style="font-weight:700;color:var(--dash-primary);min-width:60px;"><?= e($m[0]) ?></span>
        <span style="color:var(--dash-ink-soft);"><?= $m[1] ?></span>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- Press -->
<section class="pub-section surface tight">
  <div class="pub-wrap-narrow">
    <div class="pub-section-head left">
      <h2 class="pub-h2">Press &amp; Media</h2>
    </div>
    <div style="display:grid;gap:0.75rem;">
      <div class="pub-card" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
        <div>
          <div class="pub-card-title">Nepali Times</div>
          <p class="pub-card-text" style="margin-top:4px;">New online marketplace connects Nepali businesses with local investors</p>
        </div>
        <span class="pub-badge neutral">Apr 2026</span>
      </div>
      <div class="pub-card" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
        <div>
          <div class="pub-card-title">TechKhabar</div>
          <p class="pub-card-text" style="margin-top:4px;">Asaan Capital Ltd aims to digitize Nepal's SME investment landscape</p>
        </div>
        <span class="pub-badge neutral">Mar 2026</span>
      </div>
    </div>
  </div>
</section>

<!-- Team + Contact -->
<section class="pub-section tint tight">
  <div class="pub-wrap-narrow">
    <div class="pub-section-head left">
      <h2 class="pub-h2">The Team</h2>
    </div>
    <div class="pub-prose">
      <p><?= APP_NAME_LONG ?> is developed and operated by <strong>Asaan Credit Ltd</strong>, built in partnership with <strong>iSoftro Solutions</strong>, in close collaboration with Nepal's startup ecosystem leaders, legal experts, and active investors.</p>
    </div>
    <div class="pub-card" style="margin-top:var(--space-5);">
      <strong>Contact</strong><br>
      Asaan Credit Ltd<br>
      Kathmandu, Nepal<br>
      Email: <a href="mailto:info@asaancapital.com" style="color:var(--dash-primary);">info@asaancapital.com</a>
    </div>
  </div>
</section>

</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
