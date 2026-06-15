<?php
require __DIR__ . '/../config/bootstrap.php';

$homepage = [];
$stmt = db()->query("SELECT `key`, `value` FROM homepage_contents");
while ($row = $stmt->fetch()) {
    $homepage[$row['key']] = $row['value'];
}

$hero_title = $homepage['hero_title'] ?? 'Connect with <span class="highlight">Investors</span>.<br>Sell or Grow Your Business <span class="highlight">Faster</span>.';
$hero_subtitle = $homepage['hero_subtitle'] ?? 'The premium marketplace where verified business owners meet qualified investors, buyers, and franchise partners. Close deals with confidence.';
$stats_businesses = $homepage['stats_businesses'] ?? '67,500+';
$stats_investors = $homepage['stats_investors'] ?? '44,000+';
$stats_matches = $homepage['stats_matches'] ?? '12,800+';
$stats_deal_value = $homepage['stats_deal_value'] ?? 'NPR 850 Cr+';

$featured_biz = db()->query("SELECT * FROM businesses WHERE status='approved' AND is_featured=1 ORDER BY rating DESC LIMIT 6")->fetchAll();
$recent_biz = db()->query("SELECT * FROM businesses WHERE status='approved' ORDER BY created_at DESC LIMIT 6")->fetchAll();
$featured_pitches = db()->query("SELECT p.*, s.name as sector_name FROM pitches p LEFT JOIN sectors s ON p.sector_id = s.id WHERE p.is_published=1 AND p.is_hidden=0 ORDER BY p.id DESC LIMIT 6")->fetchAll();
$recent_pitches = db()->query("SELECT p.*, s.name as sector_name FROM pitches p LEFT JOIN sectors s ON p.sector_id = s.id WHERE p.is_published=1 AND p.is_hidden=0 ORDER BY p.created_at DESC LIMIT 6")->fetchAll();
$faqs = db()->query("SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order LIMIT 4")->fetchAll();

$pageTitle = APP_NAME_LONG;
$forcePublicHeader = true; // home keeps the public marketing nav even when logged in
require __DIR__ . '/../includes/header.php';
?>
<style>
:root {
  --ease-out-strong: cubic-bezier(0.23,1,0.32,1);
  --ease-in-out-strong: cubic-bezier(0.77,0,0.175,1);
}
@media (min-width:768px) {
  .hp-hero { min-height:600px; }
  .hp-hero-inner { padding-top:80px; padding-bottom:80px; }
  .hp-hero-title { font-size:48px; line-height:56px; }
  .hp-hide-mobile { display:block; }
  .hp-show-mobile { display:none; }
  .hp-stats-row { flex-direction:row; }
  .hp-grid-2 { grid-template-columns:repeat(2,1fr); }
  .hp-grid-3 { grid-template-columns:repeat(3,1fr); }
  .hp-grid-4 { grid-template-columns:repeat(4,1fr); }
  .hp-biz-split { grid-template-columns:1.7fr 1fr; }
  .hp-biz-cards { grid-template-columns:1fr 1fr; }
  .hp-gap-48 { gap:48px; }
}
@media (max-width:767px) {
  .hp-hero { min-height:500px; }
  .hp-hero-inner { padding-top:48px; padding-bottom:48px; }
  .hp-hero-title { font-size:32px; line-height:38px; }
  .hp-hide-mobile { display:none; }
  .hp-show-mobile { display:block; }
  .hp-stats-row { flex-direction:column; }
  .hp-grid-2 { grid-template-columns:1fr; }
  .hp-grid-3 { grid-template-columns:1fr; }
  .hp-grid-4 { grid-template-columns:repeat(2,1fr); }
  .hp-biz-split { grid-template-columns:1fr; }
  .hp-biz-cards { grid-template-columns:1fr; }
  .hp-gap-48 { gap:24px; }
  .hp-stats-divider { display:none; }
}
@media (max-width:479px) {
  .hp-grid-4 { grid-template-columns:1fr; }
}
@media (max-width:639px) {
  .hp-hero-actions { flex-direction:column; }
  .hp-hero-actions a { width:100%; text-align:center; }
}

/* ── Hero entrance stagger ── */
.hp-hero-content { animation: hpFadeSlide 600ms var(--ease-out-strong) both; }
.hp-hero-visual { animation: hpFadeSlide 600ms var(--ease-out-strong) 150ms both; }

.hp-hero-title { opacity:0; animation: hpFadeSlide 500ms var(--ease-out-strong) 100ms both; }
.hp-hero-sub { opacity:0; animation: hpFadeSlide 500ms var(--ease-out-strong) 200ms both; }
.hp-hero-actions { opacity:0; animation: hpFadeSlide 500ms var(--ease-out-strong) 300ms both; }

@keyframes hpFadeSlide {
  from { opacity:0; transform:translateY(12px); }
  to { opacity:1; transform:translateY(0); }
}

/* ── Stagger card entry ── */
.hp-card-stagger { opacity:0; transform:translateY(8px); }
.hp-card-stagger:nth-child(1) { animation: hpFadeSlide 400ms var(--ease-out-strong) 50ms both; }
.hp-card-stagger:nth-child(2) { animation: hpFadeSlide 400ms var(--ease-out-strong) 100ms both; }
.hp-card-stagger:nth-child(3) { animation: hpFadeSlide 400ms var(--ease-out-strong) 150ms both; }
.hp-card-stagger:nth-child(4) { animation: hpFadeSlide 400ms var(--ease-out-strong) 200ms both; }
.hp-card-stagger:nth-child(5) { animation: hpFadeSlide 400ms var(--ease-out-strong) 250ms both; }
.hp-card-stagger:nth-child(6) { animation: hpFadeSlide 400ms var(--ease-out-strong) 300ms both; }

/* ── Clickable cards: active press ── */
.pub-card, .pub-feature, .faq-item, [class*="hp-card-stagger"] {
  transition: transform 160ms var(--ease-out-strong), box-shadow 220ms var(--ease-out-strong), border-color 220ms var(--ease-out-strong);
}
@media (hover:hover) and (pointer:fine) {
  .pub-card:hover, .faq-item:hover {
    box-shadow: var(--shadow-md);
  }
  .pub-feature:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
  }
}
.pub-card:active, .faq-item:active {
  transform: scale(0.98);
}
.pub-feature:active {
  transform: scale(0.97);
}

/* ── FAQ accordion animation ── */
.faq-answer {
  display:grid;
  grid-template-rows:0fr;
  transition:grid-template-rows 280ms var(--ease-out-strong), opacity 200ms var(--ease-out-strong);
  opacity:0;
}
.faq-item.open .faq-answer {
  grid-template-rows:1fr;
  opacity:1;
}
.faq-answer-inner { overflow:hidden; }
.faq-icon {
  display:inline-flex;align-items:center;justify-content:center;
  transition:transform 220ms var(--ease-out-strong);
  font-size:20px;font-weight:300;color:var(--color-primary);width:28px;height:28px;
  flex-shrink:0;
}
.faq-item.open .faq-icon { transform:rotate(45deg); }
.faq-header { gap:12px; user-select:none; }
.faq-header:hover { color:var(--color-primary); }

/* ── Marquee carousel ── */
.hp-marquee { position:relative; }
.hp-marquee-track {
  display:flex; gap:12px; overflow-x:auto;
  scroll-snap-type:x mandatory; -webkit-overflow-scrolling:touch;
  scroll-behavior:smooth; padding:4px 0;
  -ms-overflow-style:none; scrollbar-width:none;
}
.hp-marquee-track::-webkit-scrollbar { display:none; }
.hp-marquee-track > * { scroll-snap-align:start; flex-shrink:0; flex:0 0 calc(50% - 6px); }

.hp-marquee-arrow {
  position:absolute; top:50%; transform:translateY(-50%);
  z-index:5; width:40px; height:40px; border:none; border-radius:50%;
  background:#fff; color:var(--dash-ink); cursor:pointer;
  box-shadow:0 2px 12px rgba(0,0,0,0.12);
  display:flex; align-items:center; justify-content:center;
  opacity:0; transition:opacity 220ms var(--ease-out-strong);
  font-size:18px; line-height:1;
}
.hp-marquee:hover .hp-marquee-arrow,
.hp-marquee-arrow:focus-visible { opacity:1; }
.hp-marquee-arrow-left  { left:-16px; }
.hp-marquee-arrow-right { right:-16px; }
.hp-marquee-arrow:active { transform:translateY(-50%) scale(0.92); }
@media (max-width:767px) {
  .hp-marquee-track > * { flex:0 0 calc(50% - 6px); }
  .hp-marquee-arrow { display:none; }
}

.pitch-scroll { scroll-snap-type:x mandatory; -webkit-overflow-scrolling:touch; }
.pitch-scroll > * { scroll-snap-align:start; }

/* ── Featured Businesses showcase ── */
.showcase-header {
  text-align:center; margin-bottom:40px;
}
.showcase-header .showcase-eyebrow {
  display:inline-block;
  font-size:0.72rem; font-weight:700; text-transform:uppercase;
  letter-spacing:0.12em; color:var(--color-primary);
  margin-bottom:8px;
}
.showcase-header h2 {
  font-family:var(--font-heading); font-size:1.75rem;
  font-weight:800; color:var(--dash-ink); margin:0 0 6px;
}
.showcase-header p {
  font-size:0.9rem; color:var(--dash-ink-soft);
  margin:0; max-width:520px; margin-left:auto; margin-right:auto;
}
.fb-row {
  display:grid; grid-template-columns:1.9fr 1fr;
  gap:48px; align-items:start;
}
.fb-cards-wrap { position:relative; }
.fb-track {
  display:flex; gap:16px; overflow-x:auto;
  scroll-snap-type:x mandatory; scroll-behavior:smooth;
  padding:4px 0;
  -ms-overflow-style:none; scrollbar-width:none;
}
.fb-track::-webkit-scrollbar { display:none; }
.fb-track > * { scroll-snap-align:start; flex-shrink:0; width:calc(50% - 8px); }

.fb-card {
  background:var(--dash-card); border-radius:var(--dash-radius-card);
  border:1px solid var(--dash-border); padding:0; cursor:pointer;
  box-shadow:var(--dash-shadow); overflow:hidden; position:relative;
  transition:box-shadow var(--motion-base) var(--ease-standard),
             transform var(--motion-base) var(--ease-standard),
             border-color var(--motion-base) var(--ease-standard);
}
.fb-card:hover {
  box-shadow:var(--dash-shadow-hover); transform:translateY(-3px);
  border-color:var(--color-primary);
}
.fb-card-inner { padding:20px; }

/* ── card badge (listing type) ── */
.fb-card-badge {
  display:inline-block;
  font-size:0.65rem; font-weight:700; text-transform:uppercase;
  letter-spacing:0.06em;
  padding:3px 10px; border-radius:var(--radius-pill);
  background:rgba(107,29,34,0.08); color:var(--color-primary);
}
.fb-card-badge.is-investment {
  background:rgba(16,185,129,0.1); color:var(--dash-success);
}
.fb-card-badge.is-loan {
  background:rgba(59,130,246,0.1); color:var(--dash-info);
}

/* ── rating pill ── */
.fb-card-rating {
  position:absolute; top:12px; right:12px; z-index:2;
  display:inline-flex; align-items:center; gap:3px;
  font-size:0.72rem; font-weight:700;
  padding:2px 8px; border-radius:var(--radius-pill);
  background:rgba(255,255,255,0.92); color:#b45309;
  box-shadow:0 1px 4px rgba(0,0,0,0.06);
}

/* ── card image ── */
.fb-card-img {
  width:100%; height:110px; object-fit:cover; display:block;
  background:var(--color-bg-soft);
}

/* ── card title ── */
.fb-card-title {
  margin:10px 0 3px; font-size:0.95rem; font-weight:700;
  color:var(--dash-ink); line-height:1.35;
}

/* ── card description ── */
.fb-card-desc {
  margin:0 0 8px; font-size:0.78rem; line-height:1.55;
  color:var(--dash-ink-soft);
  display:-webkit-box; -webkit-line-clamp:2;
  -webkit-box-orient:vertical; overflow:hidden;
}

/* ── location meta ── */
.fb-card-meta {
  display:flex; gap:14px; font-size:0.75rem;
  color:var(--dash-ink-soft); margin-bottom:10px; flex-wrap:wrap;
}
.fb-card-meta span { display:inline-flex; align-items:center; gap:4px; }

/* ── financial grid ── */
.fb-card-fin {
  background:var(--color-bg-soft); border-radius:var(--radius-md);
  padding:8px 10px; margin-bottom:10px;
  display:grid; grid-template-columns:1fr 1fr; gap:3px 16px;
  font-size:0.76rem;
}
.fb-card-fin .lbl {
  color:var(--dash-ink-soft); font-size:0.65rem;
  text-transform:uppercase; letter-spacing:0.04em;
}
.fb-card-fin .val {
  font-weight:600; color:var(--dash-ink);
}

/* ── footer ── */
.fb-card-ftr {
  display:flex; justify-content:space-between;
  align-items:center; gap:8px;
}
.fb-card-price-wrap { display:flex; flex-direction:column; }
.fb-card-price-label {
  font-size:0.62rem; font-weight:600; text-transform:uppercase;
  letter-spacing:0.06em; color:var(--dash-ink-soft); line-height:1;
}
.fb-card-price {
  font-weight:800; font-size:1rem;
  color:var(--color-primary-vivid); white-space:nowrap; margin-top:2px;
}
.fb-card-btn {
  flex-shrink:0;
  background:rgba(177,217,253,0.35); color:#1a4a6e;
  border:none; border-radius:var(--radius-md); padding:5px 14px;
  font-size:0.72rem; font-weight:700; cursor:pointer;
  text-decoration:none; white-space:nowrap;
  transition:background 160ms ease-out, transform 160ms ease-out;
}
.fb-card-btn:hover {
  background:rgba(177,217,253,0.6);
}
.fb-card-btn:active { transform:scale(0.97); }

/* ── carousel arrows ── */
.fb-arrow {
  position:absolute; top:50%; transform:translateY(-50%);
  z-index:5; width:34px; height:34px; border:none; border-radius:50%;
  background:#fff; color:var(--dash-ink); cursor:pointer;
  box-shadow:0 2px 8px rgba(0,0,0,0.10);
  display:flex; align-items:center; justify-content:center;
  font-size:16px; line-height:1; opacity:0;
  transition:opacity 200ms ease-out, transform 200ms ease-out;
}
.fb-cards-wrap:hover .fb-arrow { opacity:1; }
.fb-arrow-left { left:-14px; }
.fb-arrow-right { right:-14px; }
.fb-arrow:active { transform:translateY(-50%) scale(0.92); }

/* ── right content column ── */
.fb-content { padding-top:4px; }
.fb-content .fb-eyebrow {
  display:inline-block; font-size:0.72rem; font-weight:700;
  text-transform:uppercase; letter-spacing:0.08em;
  color:var(--color-primary); margin-bottom:10px;
}
.fb-content h2 {
  font-family:var(--font-heading);
  font-size:1.5rem; line-height:1.35;
  color:var(--color-primary); margin:0 0 8px;
}
.fb-content h3 {
  font-family:var(--font-heading);
  font-size:1rem; line-height:1.4; font-weight:600;
  color:var(--color-secondary); margin:0 0 14px;
}
.fb-content p {
  font-size:0.88rem; line-height:1.7;
  color:var(--dash-ink-soft); margin:0 0 28px;
}
.fb-content .fb-cta {
  display:inline-block;
  background:rgba(107,29,34,0.12); color:var(--color-primary);
  padding:14px 32px; border-radius:var(--radius-md);
  font-size:0.9rem; font-weight:700; text-decoration:none;
  transition:background 160ms ease-out, transform 160ms ease-out;
}
.fb-content .fb-cta:hover {
  background:rgba(107,29,34,0.2);
}
.fb-content .fb-cta:active { transform:scale(0.97); }

/* ── responsive ── */
@media (max-width:1024px) {
  .fb-row { gap:32px; }
  .fb-track > * { width:calc(50% - 8px); }
}
@media (max-width:900px) {
  .fb-row { grid-template-columns:1fr; gap:28px; }
  .fb-content { order:-1; }
  .fb-content h2 { font-size:1.35rem; }
  .fb-track > * { width:calc(50% - 8px); }
}
@media (max-width:768px) {
  .fb-card-fin { grid-template-columns:1fr; gap:4px; }
  .fb-card-fin div { grid-column:1!important; }
  .fb-card-meta { gap:10px; font-size:0.72rem; }
  .fb-card-desc { -webkit-line-clamp:1; }
  .fb-card-img { height:90px; }
  .fb-card-rating { top:8px; right:8px; }
  .showcase-header { margin-bottom:28px; }
  .showcase-header h2 { font-size:1.35rem; }
}
@media (max-width:640px) {
  .fb-track > * { width:100%; }
  .fb-arrow { display:none; }
  .fb-card-ftr { flex-direction:column; align-items:stretch; gap:8px; }
  .fb-card-btn { text-align:center; padding:8px 12px; font-size:0.8rem; }
  .fb-card-price-wrap { align-items:center; }
  .fb-card-price { text-align:center; }
  .fb-card-inner { padding:14px; }
  .fb-content h2 { font-size:1.2rem; }
  .fb-content p { font-size:0.85rem; }
}

/* ── Mobile bottom nav ── */
.hp-bottom-nav {
  display:none;
  position:fixed; bottom:0; left:0; right:0;
  z-index:55; height:64px;
  background:#fff; border-top:1px solid var(--dash-border);
  box-shadow:0 -2px 16px rgba(0,0,0,0.06);
}
@media (max-width:768px) {
  .hp-bottom-nav { display: flex; }
  .pub-page { padding-bottom: 64px; }
}
.hp-bottom-nav-inner {
  display:flex; align-items:center; justify-content:space-around;
  height:100%; max-width:600px; margin:0 auto;
}
.hp-bottom-nav-item {
  display:flex; flex-direction:column; align-items:center; gap:2px;
  text-decoration:none; color:var(--dash-ink-soft); font-size:10px; font-weight:600;
  padding:4px 12px; border-radius:8px; transition:color 150ms;
  white-space:nowrap; min-width:0;
}
.hp-bottom-nav-item.active { color:var(--color-primary-vivid); }
.hp-bottom-nav-item svg { width:22px; height:22px; display:block; }

/* ── Reduced motion ── */
@media (prefers-reduced-motion:reduce) {
  *, *::before, *::after {
    animation-duration:0.01ms !important;
    animation-iteration-count:1 !important;
    transition-duration:0.01ms !important;
  }
  .hp-card-stagger { opacity:1; transform:none; }
}
</style>
<main class="pub-page">
<!-- Hero Section -->
<section class="hp-hero" style="position:relative;overflow:hidden;display:flex;align-items:center;background:#fff;">
  <div class="hp-hero-inner pub-wrap" style="width:100%;position:relative;z-index:10;padding-top:60px;padding-bottom:60px;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center;">
      <div class="hp-hero-content">
        <h1 class="hp-hero-title pub-h1" style="color:var(--dash-ink);margin-bottom:20px;">
          <?= $hero_title ?>
        </h1>
        <p class="hp-hero-sub pub-lead" style="color:var(--dash-ink-soft);max-width:520px;">
          <?= e($hero_subtitle) ?>
        </p>
        <div class="hp-hero-actions pub-cta-actions" style="justify-content:flex-start;margin-top:28px;">
          <a href="<?= APP_URL ?>/onboarding" class="btn btn-primary">Get Started</a>
          <a href="<?= APP_URL ?>/browse/businesses" class="btn btn-outline" style="border-color:var(--dash-border);color:var(--dash-ink);background:transparent;">Browse Businesses</a>
        </div>
      </div>
      <div class="hp-hero-visual" style="border-radius:var(--radius-lg);overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
        <img src="<?= APP_URL ?>/public/uploads/hero-bg.jpg" alt="" style="width:100%;height:auto;display:block;aspect-ratio:16/10;object-fit:cover;">
      </div>
    </div>
  </div>
</section>

<!-- Stats Bar -->
<section class="pub-section surface tight" style="border-bottom:1px solid var(--dash-border);">
  <div class="pub-wrap">
    <div class="hp-stats-row pub-statstrip">
      <div class="pub-statstrip-item" style="display:flex;align-items:center;gap:10px;">
        <i class="fas fa-user-check" style="color:var(--color-primary-vivid);font-size:24px;"></i>
        <p style="margin:0;"><span class="pub-statstrip-num" style="display:inline;font-size:1.4rem;color:var(--color-primary-vivid);"><?= e($stats_investors) ?></span> <span class="pub-statstrip-label" style="display:inline;">Verified Investors</span></p>
      </div>
      <div class="hp-stats-divider" style="height:32px;width:1px;background:var(--dash-border);"></div>
      <div class="pub-statstrip-item" style="display:flex;align-items:center;gap:10px;">
        <i class="fas fa-rocket" style="color:var(--dash-primary);font-size:24px;"></i>
        <p style="margin:0;"><span class="pub-statstrip-num" style="display:inline;font-size:1.4rem;"><?= e($stats_businesses) ?></span> <span class="pub-statstrip-label" style="display:inline;">Active Pitches</span></p>
      </div>
      <div class="hp-stats-divider" style="height:32px;width:1px;background:var(--dash-border);"></div>
      <div class="pub-statstrip-item" style="display:flex;align-items:center;gap:10px;">
        <i class="fas fa-handshake" style="color:var(--color-primary-vivid);font-size:24px;"></i>
        <p style="margin:0;"><span class="pub-statstrip-num" style="display:inline;font-size:1.4rem;color:var(--color-primary-vivid);"><?= e($stats_matches) ?></span> <span class="pub-statstrip-label" style="display:inline;">Successful Matches</span></p>
      </div>
    </div>
  </div>
</section>

<!-- Trust Pillars -->
<section class="pub-section surface">
  <div class="pub-wrap">
    <div class="pub-grid cols-4">
      <div class="pub-feature hp-card-stagger">
        <span class="pub-feature-ico"><i class="fas fa-check-circle"></i></span>
        <h3 class="pub-feature-title">Pre-approved</h3>
        <p class="pub-feature-text">Every business, investor and advisor profile is pre-screened by our analysts.</p>
      </div>
      <div class="pub-feature hp-card-stagger">
        <span class="pub-feature-ico"><i class="fas fa-lock"></i></span>
        <h3 class="pub-feature-title">Confidential</h3>
        <p class="pub-feature-text">Your contact details stay private until there is a mutual match.</p>
      </div>
      <div class="pub-feature hp-card-stagger">
        <span class="pub-feature-ico"><i class="fas fa-chart-line"></i></span>
        <h3 class="pub-feature-title">Fair Valuation</h3>
        <p class="pub-feature-text">Benchmark your business against comparable private companies in Nepal.</p>
      </div>
      <div class="pub-feature hp-card-stagger">
        <span class="pub-feature-ico"><i class="fas fa-globe"></i></span>
        <h3 class="pub-feature-title">Global Network</h3>
        <p class="pub-feature-text">Connect with investors, buyers and partners across Nepal and beyond.</p>
      </div>
    </div>
  </div>
</section>

<!-- Featured Businesses Showcase -->
<?php
$allBiz = [];
$seen = [];
foreach ([$featured_biz, $recent_biz] as $list) {
    foreach ($list as $b) {
        if (!isset($seen[$b['id']])) {
            $seen[$b['id']] = true;
            $allBiz[] = $b;
            if (count($allBiz) >= 4) break 2;
        }
    }
}
$ltLabels = ['full_sale'=>'Business for Sale', 'partial_sale'=>'Stake Sale', 'seeking_investment'=>'Seeking Investment', 'seeking_loan'=>'Seeking Loan', 'franchise'=>'Franchise'];
$ltCSS = ['full_sale'=>'', 'partial_sale'=>'', 'seeking_investment'=>'is-investment', 'seeking_loan'=>'is-loan', 'franchise'=>''];
?>
<?php if (!empty($allBiz)): ?>
<section class="pub-section tint">
  <div class="pub-wrap">
    <div class="showcase-header">
      <span class="showcase-eyebrow">Featured Businesses</span>
      <h2>Businesses for Sale in Nepal</h2>
      <p>Pre-screened businesses for sale across Nepal — verified by our analysts.</p>
    </div>
    <div class="fb-row">
      <div class="fb-cards-wrap" id="bizMarquee">
        <button class="fb-arrow fb-arrow-left" type="button" aria-label="Previous">&#8249;</button>
        <div class="fb-track" id="bizMarqueeTrack">
          <?php foreach ($allBiz as $biz):
            $loc = array_filter([$biz['district'] ?? '', $biz['province'] ?? '']);
            $locStr = !empty($loc) ? implode(', ', $loc) : 'Nepal';
            $lt = $ltLabels[$biz['listing_type']] ?? 'Business for Sale';
            $ltClass = $ltCSS[$biz['listing_type']] ?? '';
            $img = '';
            if (!empty($biz['thumbnail_url'])) {
                $img = (str_starts_with($biz['thumbnail_url'], 'http') || str_starts_with($biz['thumbnail_url'], '/'))
                    ? $biz['thumbnail_url']
                    : '/public/uploads/business-thumbnails/' . $biz['thumbnail_url'];
            }
            $hasStake = !empty($biz['stake_offered_pct']);
          ?>
          <div class="fb-card" onclick="location.href='<?= APP_URL ?>/business/<?= (int)$biz['id'] ?>'">
            <?php if ($img): ?>
            <img src="<?= e($img) ?>" alt="" class="fb-card-img" loading="lazy">
            <?php endif; ?>
            <div class="fb-card-inner">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <span class="fb-card-badge <?= e($ltClass) ?>"><?= e($lt) ?></span>
                <?php if (!empty($biz['rating'])): ?>
                <span class="fb-card-rating">
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="#f59e0b"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                  <?= e($biz['rating']) ?>
                </span>
                <?php endif; ?>
              </div>
              <h3 class="fb-card-title"><?= e($biz['business_name']) ?></h3>
              <p class="fb-card-desc"><?= e(mb_substr($biz['description'] ?? '', 0, 120)) ?></p>
              <div class="fb-card-meta">
                <span>
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                  <?= e($locStr) ?>
                </span>
              </div>
              <div class="fb-card-fin">
                <div><span class="lbl">Run Rate Sales</span><span class="val"><?php $rv = $biz['annual_revenue'] ?? 0; echo $rv > 0 ? money($rv) : 'Not disclosed'; ?></span></div>
                <div><span class="lbl">EBITDA</span><span class="val"><?= !empty($biz['ebitda_pct']) ? e($biz['ebitda_pct']).'%' : '—' ?></span></div>
                <div><span class="lbl">Sale Type</span><span class="val"><?= e($lt) ?></span></div>
                <div><span class="lbl"><?= $hasStake ? 'Stake' : 'Asking Price' ?></span><span class="val"><?= $hasStake ? e($biz['stake_offered_pct']).'%' : ((int)($biz['asking_price'] ?? 0) > 0 ? money((int)$biz['asking_price']) : 'Contact') ?></span></div>
              </div>
              <div class="fb-card-ftr">
                <div class="fb-card-price-wrap">
                  <span class="fb-card-price-label">Asking Price</span>
                  <span class="fb-card-price"><?php $ap = (int)($biz['asking_price'] ?? 0); if ($ap > 0) { echo $ap >= 10000000 ? 'रू ' . number_format($ap / 10000000, 1) . 'Cr' : ($ap >= 100000 ? 'रू ' . number_format($ap / 100000, 1) . 'L' : money($ap)); } else { echo 'Contact for price'; } ?></span>
                </div>
                <a href="<?= APP_URL ?>/business/<?= (int)$biz['id'] ?>" class="fb-card-btn" onclick="event.stopPropagation()">Contact Business</a>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <button class="fb-arrow fb-arrow-right" type="button" aria-label="Next">&#8250;</button>
      </div>
      <div class="fb-content">
        <span class="fb-eyebrow">Why Asaan Capital</span>
        <h2>Businesses for Sale in Nepal</h2>
        <h3>Pre-screened businesses for sale across Nepal.</h3>
        <p>Explore pre-screened businesses for sale across Nepal. Find verified businesses looking for full sale, partial stake sale, investment, or business loans. Asaan Capital helps investors, buyers, and entrepreneurs discover trusted opportunities with confidence.</p>
        <a href="<?= APP_URL ?>/browse/businesses" class="fb-cta">View All Businesses</a>
      </div>
    </div>
  </div>
</section>
<?php else: ?>
<section class="pub-section tint">
  <div class="pub-wrap">
    <div style="text-align:center;padding:var(--space-6) 0;">
      <p style="color:var(--dash-ink-soft);margin:0;">No featured businesses available right now.</p>
    </div>
  </div>
</section>
<?php endif; ?><!-- END FEATURED BUSINESSES -->

<!-- Dual Path Cards -->
<section class="pub-section surface">
  <div class="pub-wrap">
    <div class="hp-grid-2 hp-gap-48" style="display:grid;">
      <div style="position:relative;overflow:hidden;border-radius:12px;padding:24px;background:#ffdad933;border:1px solid #ffb3b2;">
        <div style="position:relative;z-index:10;">
          <h3 class="pub-h2" style="margin-bottom:16px;color:var(--color-primary);">For Investors</h3>
          <p class="pub-text" style="margin-bottom:24px;max-width:400px;">
            Access vetted startups from Nepal across diverse sectors including Agriculture, SaaS, and Energy. View pitch decks and financial reports instantly.
          </p>
          <ul style="list-style:none;padding:0;">
            <li style="display:flex;align-items:center;gap:4px;font-size:12px;line-height:16px;font-weight:600;letter-spacing:0.05em;color:var(--dash-ink);font-family:var(--font-body);margin-bottom:8px;">
              <span style="color:var(--color-primary);font-family:'Material Symbols Outlined';font-size:14px;font-variation-settings:'FILL' 1;">check_circle</span>
              Pre-vetted Opportunities
            </li>
            <li style="display:flex;align-items:center;gap:4px;font-size:12px;line-height:16px;font-weight:600;letter-spacing:0.05em;color:var(--dash-ink);font-family:var(--font-body);">
              <span style="color:var(--color-primary);font-family:'Material Symbols Outlined';font-size:14px;font-variation-settings:'FILL' 1;">check_circle</span>
              Direct Entrepreneur Access
            </li>
          </ul>
        </div>
      </div>
      <div style="position:relative;overflow:hidden;border-radius:12px;padding:24px;background:#f5e6e6;border:1px solid #e0c5c5;">
        <div style="position:relative;z-index:10;">
          <h3 class="pub-h2" style="margin-bottom:16px;color:var(--color-primary);">For Entrepreneurs</h3>
          <p class="pub-text" style="margin-bottom:24px;max-width:400px;">
            List your venture and get matched with professional investors who understand the Nepalese market. Secure funding to scale your vision.
          </p>
          <ul style="list-style:none;padding:0;">
            <li style="display:flex;align-items:center;gap:4px;font-size:12px;line-height:16px;font-weight:600;letter-spacing:0.05em;color:var(--dash-ink);font-family:var(--font-body);margin-bottom:8px;">
              <span style="color:var(--color-primary);font-family:'Material Symbols Outlined';font-size:14px;font-variation-settings:'FILL' 1;">check_circle</span>
              Visibility to HNIs
            </li>
            <li style="display:flex;align-items:center;gap:4px;font-size:12px;line-height:16px;font-weight:600;letter-spacing:0.05em;color:var(--dash-ink);font-family:var(--font-body);">
              <span style="color:var(--color-primary);font-family:'Material Symbols Outlined';font-size:14px;font-variation-settings:'FILL' 1;">check_circle</span>
              Fundraising Assistance
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Featured Pitches -->
<?php
$displayPitches = !empty($featured_pitches) ? $featured_pitches : (!empty($recent_pitches) ? $recent_pitches : []);
$pitchSectionTitle = !empty($featured_pitches) ? 'Featured Investment Opportunities' : 'Latest Investment Opportunities';
?>
<?php if (!empty($displayPitches)): ?>
<section class="pub-section tint">
  <div class="pub-wrap">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:24px;gap:16px;flex-wrap:wrap;">
      <div>
        <h2 class="pub-h2" style="color:var(--color-primary);margin:0 0 8px 0;"><?= $pitchSectionTitle ?></h2>
        <p class="pub-text" style="margin:0;">Pre-verified entrepreneurs seeking capital for growth.</p>
      </div>
      <a href="<?= APP_URL ?>/browse/entrepreneurs" class="btn btn-ghost btn-sm hp-hide-mobile" style="flex-shrink:0;">View All</a>
    </div>
    <div class="hp-marquee" id="pitchMarquee">
      <button class="hp-marquee-arrow hp-marquee-arrow-left" type="button" aria-label="Previous">&#8249;</button>
      <div class="pitch-scroll hp-marquee-track" id="pitchMarqueeTrack" style="display:flex;gap:12px;overflow-x:auto;padding-bottom:8px;">
      <?php foreach ($displayPitches as $p): ?>
      <div class="pub-card card-accent-bar-navy" style="flex-shrink:0;flex:0 0 calc(50% - 6px);cursor:pointer;" onclick="location.href='<?= APP_URL ?>/pitch/<?= (int)$p['id'] ?>'">
        <div style="margin-bottom:12px;">
          <span class="pub-badge info">Seeking Investment</span>
        </div>
        <h4 class="pub-card-title" style="margin:0 0 6px;"><?= e($p['tagline']) ?></h4>
        <p class="pub-text" style="margin:0 0 12px;"><?= e(mb_substr($p['short_summary'] ?? $p['problem_statement'] ?? '', 0, 120)) ?></p>
        <div style="display:flex;gap:4px;margin-bottom:8px;flex-wrap:wrap;">
          <?php if (!empty($p['sector_name'])): ?>
          <span class="pub-badge neutral"><?= e($p['sector_name']) ?></span>
          <?php endif; ?>
          <?php if (!empty($p['stage'])): ?>
          <span class="pub-badge neutral"><?= e(ucfirst($p['stage'])) ?></span>
          <?php endif; ?>
        </div>
        <div style="display:flex;gap:8px;justify-content:space-between;align-items:center;flex-wrap:wrap;padding-top:12px;border-top:1px solid var(--dash-border);">
          <div><span class="pub-stat-label" style="display:block;">Funding</span><span class="pub-stat-value"><?= money($p['funding_amount']) ?></span></div>
          <?php if (!empty($p['equity_offered'])): ?>
          <div><span class="pub-stat-label" style="display:block;">Equity</span><span class="pub-stat-value"><?= e($p['equity_offered']) ?>%</span></div>
          <?php endif; ?>
          <div style="width:100%;padding-top:8px;"><strong style="font-size:16px;color:var(--color-primary-vivid);">Valued at <?= money($p['valuation'] ?? 0) ?></strong></div>
        </div>
      </div>
      <?php endforeach; ?>
      </div>
      <button class="hp-marquee-arrow hp-marquee-arrow-right" type="button" aria-label="Next">&#8250;</button>
    </div>
    <div class="hp-show-mobile" style="text-align:center;margin-top:16px;">
      <a href="<?= APP_URL ?>/browse/entrepreneurs" class="btn btn-ghost btn-sm">View All</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- FAQ -->
<section class="pub-section surface">
  <div class="pub-wrap-narrow">
    <div class="pub-section-head">
      <h2 class="pub-h2" style="margin-bottom:16px;">Frequently Asked Questions</h2>
      <p class="pub-text">Everything you need to know about <?= APP_NAME_LONG ?>.</p>
    </div>
    <?php $first = true; foreach ($faqs as $faq): ?>
    <div class="faq-item <?= $first ? 'open' : '' ?>" style="background:var(--dash-card);border-radius:12px;padding:16px;margin-bottom:8px;border:1px solid var(--dash-border);<?= $first ? 'border-left:4px solid var(--color-primary-vivid);' : '' ?>">
      <div class="faq-header" style="display:flex;justify-content:space-between;align-items:center;cursor:pointer;font-weight:600;font-size:16px;line-height:24px;color:var(--dash-ink);font-family:var(--font-body);">
        <span><?= e($faq['question']) ?></span>
        <span class="faq-icon">+</span>
      </div>
      <div class="faq-answer">
        <div class="faq-answer-inner"><?= e($faq['answer']) ?></div>
      </div>
    </div>
    <?php $first = false; endforeach; ?>
    <div style="text-align:center;margin-top:24px;">
      <a href="<?= APP_URL ?>/support" class="btn btn-ghost btn-sm">View all FAQs</a>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="pub-section surface">
  <div class="pub-wrap">
    <div class="pub-cta">
      <h2>Ready to grow your business?</h2>
      <p>Join <?= e($stats_businesses) ?> business owners and <?= e($stats_investors) ?> investors already on the platform.</p>
      <div class="pub-cta-actions">
        <a href="<?= APP_URL ?>/onboarding" class="btn btn-primary btn-lg">Get Started</a>
        <a href="<?= APP_URL ?>/browse/businesses" class="btn btn-outline btn-lg" style="border-color:rgba(255,255,255,0.3);color:rgba(255,255,255,0.9);">Browse Businesses</a>
      </div>
    </div>
  </div>
</section>
</main>

<?php $navIsLoggedIn = $user ? true : false; ?>
<nav class="hp-bottom-nav" id="bottomNav" aria-label="Mobile navigation">
  <div class="hp-bottom-nav-inner">
    <a href="<?= APP_URL ?>/" class="hp-bottom-nav-item active">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12l9-9 9 9"/><path d="M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10"/></svg>
      <span>Home</span>
    </a>
    <a href="<?= APP_URL ?>/browse/businesses" class="hp-bottom-nav-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
      <span>Browse</span>
    </a>
    <?php if ($navIsLoggedIn): ?>
    <a href="<?= APP_URL ?>/connections" class="hp-bottom-nav-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
      <span>Network</span>
    </a>
    <a href="<?= APP_URL ?>/notifications" class="hp-bottom-nav-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
      <span>Alerts</span>
    </a>
    <a href="<?= APP_URL ?>/dashboard" class="hp-bottom-nav-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <span>Profile</span>
    </a>
    <?php else: ?>
    <a href="<?= APP_URL ?>/login" class="hp-bottom-nav-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
      <span>Login</span>
    </a>
    <a href="<?= APP_URL ?>/onboarding" class="hp-bottom-nav-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
      <span>Join</span>
    </a>
    <?php endif; ?>
  </div>
</nav>

<script>
// FAQ accordion
document.querySelectorAll('.faq-header').forEach(function(header) {
  header.addEventListener('click', function() {
    this.parentElement.classList.toggle('open');
  });
});

// Marquee auto-scroll
function initMarquee(id) {
  var track = document.getElementById(id + 'Track');
  var marquee = document.getElementById(id);
  if (!track || !marquee) return;

  var leftArrow = marquee.querySelector('.fb-arrow-left, .hp-marquee-arrow-left');
  var rightArrow = marquee.querySelector('.fb-arrow-right, .hp-marquee-arrow-right');
  var interval = null;
  var isPaused = false;

  function getScrollAmount() {
    var card = track.querySelector('.pub-card, .fb-card');
    if (!card) return 320;
    return card.offsetWidth + 16;
  }

  function scrollNext() {
    if (isPaused) return;
    var maxScroll = track.scrollWidth - track.clientWidth;
    var next = track.scrollLeft + getScrollAmount();
    if (next >= maxScroll - 10) next = 0;
    track.scrollTo({ left: next, behavior: 'smooth' });
  }

  function scrollPrev() {
    var prev = track.scrollLeft - getScrollAmount();
    if (prev <= 0) prev = 0;
    track.scrollTo({ left: prev, behavior: 'smooth' });
  }

  function startAutoScroll() {
    stopAutoScroll();
    interval = setInterval(scrollNext, 3500);
  }

  function stopAutoScroll() {
    if (interval) { clearInterval(interval); interval = null; }
  }

  if (leftArrow) leftArrow.addEventListener('click', function() { stopAutoScroll(); scrollPrev(); });
  if (rightArrow) rightArrow.addEventListener('click', function() { stopAutoScroll(); scrollNext(); });

  marquee.addEventListener('mouseenter', function() { isPaused = true; });
  marquee.addEventListener('mouseleave', function() { isPaused = false; });

  // Touch swipe
  var startX = 0;
  var isDragging = false;

  track.addEventListener('touchstart', function(e) {
    startX = e.touches[0].clientX;
    isDragging = true;
    stopAutoScroll();
  }, { passive: true });

  track.addEventListener('touchend', function(e) {
    if (!isDragging) return;
    isDragging = false;
    var diff = startX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 40) {
      if (diff > 0) scrollNext();
      else scrollPrev();
    }
    startAutoScroll();
  }, { passive: true });

  startAutoScroll();
}

initMarquee('bizMarquee');
initMarquee('pitchMarquee');

// Prevent card onclick when clicking "Contact Business" link
document.querySelectorAll('.fb-card-btn').forEach(function(btn) {
  btn.addEventListener('click', function(e) { e.stopPropagation(); });
});

// Bottom nav active state
(function() {
  var cur = window.location.pathname;
  if (cur.indexOf('/assan') === 0) cur = cur.replace(/^\/assan/, '') || '/';
  document.querySelectorAll('.hp-bottom-nav-item').forEach(function(a) {
    var href = a.getAttribute('href').replace(/^.*\/\/[^\/]+/, '');
    if (cur === href || (href !== '/' && cur.indexOf(href) === 0)) {
      a.classList.add('active');
    }
  });
})();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
