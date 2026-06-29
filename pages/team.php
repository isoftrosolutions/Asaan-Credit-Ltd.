<?php
require __DIR__ . '/../config/bootstrap.php';

$pageTitle = 'Team — ' . APP_NAME_LONG;
$pageDescription = 'Meet the leadership team at Asaan Capital Ltd — experienced professionals dedicated to investment advisory, business valuation, and financial services in Nepal.';
$forcePublicHeader = true;

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Team","item":"'.APP_URL.'/team"}
  ]
}</script>';
require __DIR__ . '/../includes/header.php';
?>
<?= $breadcrumbSchema ?>
<style>
.tm-grid{ display:grid; gap:32px; }
@media (min-width:768px){ .tm-grid{ grid-template-columns:repeat(3,1fr); } }
@media (max-width:767px){ .tm-grid{ grid-template-columns:1fr; } }
.tm-card{ text-align:center; background:var(--dash-card); border:1px solid var(--dash-border); border-radius:var(--dash-radius-card); padding:var(--space-6) var(--space-4); transition:box-shadow var(--motion-base), transform var(--motion-base); }
.tm-card:hover{ box-shadow:var(--dash-shadow-hover); transform:translateY(-4px); }
.tm-avatar-wrap{ width:120px; height:120px; border-radius:50%; margin:0 auto var(--space-4); background:var(--dash-bg); border:3px solid var(--dash-border); overflow:hidden; display:flex; align-items:center; justify-content:center; }
.tm-avatar-wrap img{ width:100%; height:100%; object-fit:cover; }
.tm-avatar-placeholder{ width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-family:var(--font-heading); font-weight:800; font-size:2.5rem; color:var(--color-primary); background:rgba(107,29,34,.06); }
.tm-name{ font-family:var(--font-heading); font-weight:700; font-size:1.1rem; color:var(--dash-ink); margin-bottom:2px; }
.tm-role{ display:inline-block; font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:var(--dash-primary); margin-bottom:var(--space-3); }
.tm-bio{ font-size:.88rem; line-height:1.6; color:var(--dash-ink-soft); max-width:300px; margin:0 auto; }
.tm-phone{ font-size:.82rem; color:var(--dash-ink-soft); margin-top:var(--space-2); }
.tm-phone a{ color:var(--dash-primary); text-decoration:none; }
</style>
<main class="pub-page">

<section style="background:linear-gradient(135deg, var(--color-primary) 0%, #4A1317 100%);color:#fff;padding:64px 0;">
  <div class="pub-wrap">
    <div style="max-width:680px;">
      <span style="display:inline-block;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.7);margin-bottom:8px;">Our Team</span>
      <h1 class="pub-h1" style="color:#fff;margin:0 0 14px;">Meet the Professionals Behind Asaan Capital</h1>
      <p style="font-size:1.05rem;line-height:1.7;opacity:.88;">Our team brings decades of combined experience in investment advisory, business valuation, M&A, and corporate finance — dedicated to delivering exceptional results for our clients.</p>
    </div>
  </div>
</section>

<section class="pub-section">
  <div class="pub-wrap">
    <div class="tm-grid">
      <?php $team = [
        ['Shyam Sundar Yadav', 'CEO & Founder', 'Visionary leader with extensive experience in business development, M&A advisory, and strategic financial consulting. Driving Asaan Capital\'s mission to transform Nepal\'s investment landscape.', '9848714991', null],
        ['Devbarat Patel', 'Managing Partner', '8+ years in investment advisory, business valuation, and client relationship management. Committed to delivering transparent and innovative financial solutions.', '9848714990', null],
        ['Rabin Thapa', 'Head of Advisory', '12+ years in corporate finance, project finance, and due diligence. Previously served at leading financial institutions in Nepal.', '9848714992', null],
      ]; foreach ($team as $t): ?>
      <div class="tm-card">
        <div class="tm-avatar-wrap">
          <?php if ($t[4]): ?>
            <img src="<?= e($t[4]) ?>" alt="<?= e($t[0]) ?>">
          <?php else: ?>
            <?php $initial = mb_strtoupper(mb_substr($t[0], 0, 1)); ?>
            <div class="tm-avatar-placeholder"><?= $initial ?></div>
          <?php endif; ?>
        </div>
        <div class="tm-name"><?= e($t[0]) ?></div>
        <div class="tm-role"><?= e($t[1]) ?></div>
        <div class="tm-bio"><?= e($t[2]) ?></div>
        <?php if ($t[3]): ?>
        <div class="tm-phone"><a href="tel:<?= e($t[3]) ?>"><i class="fas fa-phone"></i> <?= e($t[3]) ?></a></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="pub-section tint" style="text-align:center;">
  <div class="pub-wrap">
    <div style="max-width:600px;margin:0 auto;">
      <span class="material-symbols-outlined" style="font-size:40px;color:var(--color-primary);margin-bottom:var(--space-3);">handshake</span>
      <h2 class="pub-h2" style="margin-bottom:var(--space-2);">Want to Join Our Team?</h2>
      <p class="pub-text" style="margin-bottom:var(--space-4);">We're always looking for talented professionals to join our growing team in Nepal.</p>
      <a href="<?= APP_URL ?>/careers" class="btn btn-primary" style="padding:12px 28px;background:var(--color-primary);color:#fff;border-radius:10px;text-decoration:none;font-weight:600;">View Open Positions</a>
    </div>
  </div>
</section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
