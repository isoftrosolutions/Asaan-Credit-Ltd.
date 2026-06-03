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
<main class="pub-page">

<!-- Hero -->
<section class="pub-hero">
  <div class="pub-wrap">
    <div class="pub-breadcrumbs" style="font-size:0.82rem;color:var(--dash-ink-soft);margin-bottom:16px;">
      <a href="<?= APP_URL ?>/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a> <span style="margin:0 6px;">/</span>
      <span>Careers</span>
    </div>
    <div class="pub-section-head left" style="margin-bottom:0;">
      <span class="pub-eyebrow">Join Us</span>
      <h1 class="pub-h1">Build the future of Nepali business with us</h1>
      <p class="pub-lead">We're a small, ambitious team on a mission to give every Nepali business owner and investor a trusted place to connect. We hire for curiosity, ownership, and care.</p>
    </div>
  </div>
</section>

<!-- Values -->
<section class="pub-section tint">
  <div class="pub-wrap">
    <div class="pub-section-head left">
      <h2 class="pub-h2">What we value</h2>
    </div>
    <div class="cr-values" style="display:grid;gap:var(--space-4);">
      <?php foreach ($values as $v): ?>
      <div class="pub-card">
        <span style="color:var(--dash-primary);font-family:'Material Symbols Outlined';font-size:28px;font-variation-settings:'FILL' 1;"><?= $v[0] ?></span>
        <div class="pub-card-title" style="margin:10px 0 6px;"><?= e($v[1]) ?></div>
        <p class="pub-card-text"><?= e($v[2]) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Why join -->
<section class="pub-section surface">
  <div class="pub-wrap">
    <div class="pub-section-head left">
      <h2 class="pub-h2">Why join</h2>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:var(--space-8);">
      <?php foreach ($perks as $p): ?>
      <span class="pub-badge" style="font-size:0.82rem;padding:8px 16px;">
        <span style="color:var(--dash-success);font-family:'Material Symbols Outlined';font-size:16px;font-variation-settings:'FILL' 1;">check_circle</span><?= e($p) ?>
      </span>
      <?php endforeach; ?>
    </div>

    <div class="pub-cta">
      <h2>No open roles right now</h2>
      <p>We're not actively hiring, but we're always glad to meet talented people. Send your CV and a note about how you'd like to contribute.</p>
      <div class="pub-cta-actions">
        <a href="mailto:careers@asaancapital.com" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,0.5);">careers@asaancapital.com</a>
      </div>
    </div>
  </div>
</section>

</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
