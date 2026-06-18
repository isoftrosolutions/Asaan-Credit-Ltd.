<?php
require __DIR__ . '/../config/bootstrap.php';

$pageTitle = 'Our Partners - ' . APP_NAME;
$pageDescription = 'Meet the clients, companies, and ecosystem partners working with ' . APP_NAME_LONG . '.';
$forcePublicHeader = true;

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Our Partners","item":"'.APP_URL.'/our-partners"}
  ]
}</script>';

require __DIR__ . '/../includes/header.php';

$partners = [
    [
        'name' => 'Himalayan Agro Group',
        'type' => 'Client Company',
        'description' => 'A growing agribusiness group exploring strategic investment and regional expansion opportunities.',
        'logo' => null,
        'initials' => 'HA',
        'accent' => '#1E7A4D',
    ],
    [
        'name' => 'Kathmandu Retail Network',
        'type' => 'SME Partner',
        'description' => 'Retail operators using the marketplace to connect with verified buyers and franchise prospects.',
        'logo' => null,
        'initials' => 'KR',
        'accent' => '#6B1D22',
    ],
    [
        'name' => 'Everest Hospitality',
        'type' => 'Client Company',
        'description' => 'Hospitality entrepreneurs preparing expansion-ready business profiles for investors.',
        'logo' => null,
        'initials' => 'EH',
        'accent' => '#B45309',
    ],
    [
        'name' => 'Nexus Investment Advisors',
        'type' => 'Advisory Partner',
        'description' => 'Independent advisors supporting deal readiness, valuation, and buyer conversations.',
        'logo' => null,
        'initials' => 'NI',
        'accent' => '#12304A',
    ],
    [
        'name' => 'Lumbini Franchise Co.',
        'type' => 'Franchise Partner',
        'description' => 'Franchise teams looking for qualified operators across Nepal.',
        'logo' => null,
        'initials' => 'LF',
        'accent' => '#3b6281',
    ],
    [
        'name' => 'Summit Growth Capital',
        'type' => 'Investor Network',
        'description' => 'Investor groups reviewing curated business and fundraising opportunities.',
        'logo' => null,
        'initials' => 'SG',
        'accent' => '#98202A',
    ],
];

$logoLoop = array_merge($partners, $partners);
?>
<?= $breadcrumbSchema ?>
<style>
.partners-hero-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.1fr) minmax(320px, .9fr);
  gap: var(--space-8);
  align-items: center;
}
.partners-marquee {
  position: relative;
  overflow: hidden;
  border: 1px solid var(--dash-border);
  border-radius: var(--dash-radius-card);
  background: var(--dash-card);
  box-shadow: var(--dash-shadow);
  padding: var(--space-4) 0;
}
.partners-marquee::before,
.partners-marquee::after {
  content: "";
  position: absolute;
  top: 0;
  width: 80px;
  height: 100%;
  z-index: 2;
  pointer-events: none;
}
.partners-marquee::before {
  left: 0;
  background: linear-gradient(90deg, var(--dash-card), rgba(255,255,255,0));
}
.partners-marquee::after {
  right: 0;
  background: linear-gradient(270deg, var(--dash-card), rgba(255,255,255,0));
}
.partners-marquee-track {
  display: flex;
  width: max-content;
  gap: var(--space-4);
  animation: partnersMarquee 28s linear infinite;
}
.partners-marquee:hover .partners-marquee-track {
  animation-play-state: paused;
}
.partner-logo-tile {
  width: 172px;
  min-height: 104px;
  flex: 0 0 auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border: 1px solid var(--dash-border);
  border-radius: 14px;
  background: #fff;
  padding: var(--space-4);
}
.partner-logo-mark {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-family: var(--font-heading);
  font-weight: 800;
  font-size: 1rem;
}
.partner-logo-img {
  max-width: 116px;
  max-height: 52px;
  object-fit: contain;
}
.partner-logo-name {
  max-width: 132px;
  color: var(--dash-ink);
  font-size: .78rem;
  font-weight: 700;
  text-align: center;
  line-height: 1.25;
}
.partners-card-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--space-5);
}
.partners-type {
  display: inline-flex;
  align-items: center;
  min-height: 26px;
  border-radius: 999px;
  padding: 0 10px;
  background: var(--dash-primary-soft);
  color: var(--dash-primary);
  font-size: .76rem;
  font-weight: 700;
}
@keyframes partnersMarquee {
  from { transform: translateX(-50%); }
  to { transform: translateX(0); }
}
@media (prefers-reduced-motion: reduce) {
  .partners-marquee-track { animation: none; transform: translateX(0); flex-wrap: wrap; justify-content: center; }
}
@media (max-width: 900px) {
  .partners-hero-grid,
  .partners-card-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
  .partner-logo-tile { width: 148px; }
  .partners-marquee::before,
  .partners-marquee::after { width: 42px; }
}
</style>

<main class="pub-page" id="main-content">
  <section class="pub-hero">
    <div class="pub-wrap">
      <div class="partners-hero-grid">
        <div>
          <div class="pub-breadcrumbs" style="font-size:0.82rem;color:var(--dash-ink-soft);margin-bottom:16px;">
            <a href="<?= APP_URL ?>/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a>
            <span style="margin:0 6px;">/</span>
            <span>Our Partners</span>
          </div>
          <span class="pub-eyebrow">Clients &amp; Companies</span>
          <h1 class="pub-h1">Our Partners</h1>
          <p class="pub-lead">A growing network of clients, companies, advisors, franchises, and investor groups using <?= e(APP_NAME_LONG) ?> to build better business connections.</p>
          <div class="hero-actions" style="margin-top:var(--space-6);">
            <a href="<?= APP_URL ?>/contact" class="btn btn-primary">Become a partner</a>
            <a href="<?= APP_URL ?>/browse/businesses" class="btn btn-outline">Explore companies</a>
          </div>
        </div>

        <div class="pub-card" style="padding:var(--space-5);">
          <div class="pub-card-title">Partner network</div>
          <p class="pub-card-text">Showcase client logos, company introductions, and ecosystem partners in one place.</p>
          <div class="pub-statstrip" style="justify-content:flex-start;margin-top:var(--space-5);gap:var(--space-6);">
            <div class="pub-statstrip-item">
              <div class="pub-statstrip-num">30+</div>
              <div class="pub-statstrip-label">Sectors</div>
            </div>
            <div class="pub-statstrip-item">
              <div class="pub-statstrip-num">7</div>
              <div class="pub-statstrip-label">Provinces</div>
            </div>
            <div class="pub-statstrip-item">
              <div class="pub-statstrip-num">100%</div>
              <div class="pub-statstrip-label">Verified focus</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="pub-section surface">
    <div class="pub-wrap">
      <div class="pub-section-head">
        <span class="pub-eyebrow">Logo Wall</span>
        <h2 class="pub-h2">Companies moving with us</h2>
        <p class="pub-lead">Replace these logo tiles with real client and company logos as your partner list grows.</p>
      </div>
    </div>
    <div class="partners-marquee" aria-label="Partner logo marquee">
      <div class="partners-marquee-track">
        <?php foreach ($logoLoop as $partner): ?>
          <div class="partner-logo-tile">
            <?php if (!empty($partner['logo'])): ?>
              <img class="partner-logo-img" src="<?= e(APP_URL . '/' . ltrim($partner['logo'], '/')) ?>" alt="<?= e($partner['name']) ?> logo">
            <?php else: ?>
              <span class="partner-logo-mark" style="background:<?= e($partner['accent']) ?>;"><?= e($partner['initials']) ?></span>
            <?php endif; ?>
            <span class="partner-logo-name"><?= e($partner['name']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="pub-section">
    <div class="pub-wrap">
      <div class="pub-section-head left">
        <span class="pub-eyebrow">Featured Partners</span>
        <h2 class="pub-h2">Clients, companies, and ecosystem allies</h2>
        <p class="pub-lead">Use this section to write short notes about each partner, client, or company profile.</p>
      </div>
      <div class="partners-card-grid">
        <?php foreach ($partners as $partner): ?>
          <article class="pub-card">
            <div style="display:flex;align-items:center;gap:var(--space-3);margin-bottom:var(--space-4);">
              <?php if (!empty($partner['logo'])): ?>
                <img class="partner-logo-img" src="<?= e(APP_URL . '/' . ltrim($partner['logo'], '/')) ?>" alt="<?= e($partner['name']) ?> logo">
              <?php else: ?>
                <span class="partner-logo-mark" style="background:<?= e($partner['accent']) ?>;"><?= e($partner['initials']) ?></span>
              <?php endif; ?>
              <div>
                <h3 class="pub-card-title" style="margin:0;"><?= e($partner['name']) ?></h3>
                <span class="partners-type"><?= e($partner['type']) ?></span>
              </div>
            </div>
            <p class="pub-card-text"><?= e($partner['description']) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
