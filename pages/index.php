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
$hero_video_id = $homepage['hero_video_id'] ?? '';

$featured_biz = db()->query("SELECT * FROM businesses WHERE status='approved' AND is_featured=1 ORDER BY rating DESC LIMIT 6")->fetchAll();
$recent_biz = db()->query("SELECT * FROM businesses WHERE status='approved' ORDER BY created_at DESC LIMIT 6")->fetchAll();
$featured_pitches = db()->query("SELECT p.*, s.name as sector_name FROM pitches p LEFT JOIN sectors s ON p.sector_id = s.id WHERE p.is_published=1 AND p.is_hidden=0 ORDER BY p.id DESC LIMIT 6")->fetchAll();
$recent_pitches = db()->query("SELECT p.*, s.name as sector_name FROM pitches p LEFT JOIN sectors s ON p.sector_id = s.id WHERE p.is_published=1 AND p.is_hidden=0 ORDER BY p.created_at DESC LIMIT 6")->fetchAll();
$faqs = db()->query("SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order LIMIT 4")->fetchAll();

$featured_investors = db()->query("SELECT u.id, u.name, u.company_name, u.province, u.district, u.profile_photo, u.bio, u.account_type, u.is_premium, u.created_at, ip.total_capital_deployed, ip.past_investments, ip.preferred_sectors, ip.ticket_min, ip.ticket_max FROM users u JOIN investor_profiles ip ON ip.user_id = u.id WHERE u.role = 'investor' AND u.verification_status = 'verified' ORDER BY u.is_premium DESC, u.last_login_at DESC LIMIT 8")->fetchAll();

$pageTitle = APP_NAME_LONG;
$pageDescription = 'Nepal\'s #1 marketplace for buying, selling, and investing in businesses. Connect with 44,000+ verified investors and 67,500+ business owners.';
$forcePublicHeader = true; // home keeps the public marketing nav even when logged in
require __DIR__ . '/../includes/header.php';
?>
<style>
:root {
  --ease-out-strong: cubic-bezier(0.23,1,0.32,1);
  --ease-in-out-strong: cubic-bezier(0.77,0,0.175,1);
}
@media (min-width:768px) {
  .hp-hero { min-height:85vh; }
  .hp-hero-inner { padding:120px 0 100px; }
  .hp-hero-title { font-size:52px; line-height:60px; }
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
  .hp-hero { min-height:90svh; }
  .hp-hero-inner { padding:100px 0 60px; }
  .hp-hero-title { font-size:30px; line-height:36px; }
  .hp-hero-sub { max-width:100%!important; }
  .hp-hide-mobile { display:none; }
  .hp-show-mobile { display:block; }
  .hp-stats-row { flex-direction:column; gap:12px; }
  .hp-grid-2 { grid-template-columns:1fr; }
  .hp-grid-3 { grid-template-columns:1fr; }
  .hp-grid-4 { grid-template-columns:repeat(2,1fr); }
  .hp-biz-split { grid-template-columns:1fr; }
  .hp-biz-cards { grid-template-columns:1fr; }
  .hp-gap-48 { gap:24px; }
  .hp-stats-divider { display:none; }
  .pub-section { padding:40px 0; }
  section[style*="padding:64px"] { padding:40px 0!important; }
  .pub-section-head { margin-bottom:32px; }
}
@media (max-width:479px) {
  .hp-hero { min-height:85svh; }
  .hp-hero-title { font-size:26px; line-height:32px; }
  .hp-hero-inner { padding:80px 0 48px; }
  .hp-grid-4 { grid-template-columns:1fr; }
}
@media (max-width:639px) {
  .hp-hero-actions { flex-direction:column; }
  .hp-hero-actions a { width:100%; text-align:center; }
}

/* ── Hero background overlay ── */
.hp-hero {
  position:relative;
  overflow:hidden;
  display:flex;
  align-items:center;
  background:#0f172a;
}
.hp-hero-bg {
  position:absolute;
  inset:0;
  background-image:url('<?= APP_URL ?>/public/uploads/hero-bg.jpg');
  background-size:cover;
  background-position:center;
  background-repeat:no-repeat;
}
.hp-hero-overlay {
  position:absolute;
  inset:0;
  background:linear-gradient(135deg, rgba(15,23,42,0.82) 0%, rgba(15,23,42,0.55) 50%, rgba(15,23,42,0.3) 100%);
}
@media (max-width:767px) {
  .hp-hero-overlay {
    background:linear-gradient(180deg, rgba(15,23,42,0.85) 0%, rgba(15,23,42,0.6) 100%);
  }
}
.hp-hero-inner {
  position:relative;
  z-index:2;
  width:100%;
}
.hp-hero-content {
  max-width:640px;
}
.hp-hero-content h1 {
  color:#fff!important;
}
.hp-hero-content .highlight {
  color:#fbbf24;
}
.hp-hero-content .pub-lead {
  color:rgba(255,255,255,0.8)!important;
}
.hp-hero-actions .btn-primary {
  background:#fbbf24;
  color:#0f172a;
  border:none;
  font-weight:700;
  padding:14px 36px;
  font-size:1.05rem;
  border-radius:10px;
  box-shadow:0 4px 20px rgba(251,191,36,0.35);
  transition:transform .15s, box-shadow .15s;
}
.hp-hero-actions .btn-primary:hover {
  transform:translateY(-2px);
  box-shadow:0 8px 30px rgba(251,191,36,0.45);
}
.hp-hero-actions .btn-outline {
  border:2px solid rgba(255,255,255,0.5);
  color:#fff!important;
  background:transparent;
  padding:14px 36px;
  font-size:1.05rem;
  font-weight:600;
  border-radius:10px;
  transition:background .15s, border-color .15s;
}
.hp-hero-actions .btn-outline:hover {
  background:rgba(255,255,255,0.1);
  border-color:#fff;
}

/* ── Hero play button ── */
.hp-hero-play {
  display:inline-flex; align-items:center; gap:10px;
  margin-top:24px; padding:12px 28px;
  background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2);
  border-radius:999px; color:#fff; cursor:pointer;
  font-size:15px; font-weight:600; font-family:inherit;
  -webkit-backdrop-filter:blur(4px); backdrop-filter:blur(4px);
  transition:background .15s, border-color .15s, transform .15s;
}
.hp-hero-play:hover { background:rgba(255,0,0,0.85); border-color:#ff0000; transform:scale(1.03); }
.hp-hero-play svg { color:#ff0000; transition:color .15s; }
.hp-hero-play:hover svg { color:#fff; }
@media (max-width:639px) {
  .hp-hero-play { width:100%; justify-content:center; }
}

/* ── Video modal ── */
.hp-video-modal {
  display:none; position:fixed; inset:0; z-index:9999;
  background:rgba(0,0,0,0.85);
  align-items:center; justify-content:center;
  padding:24px;
  -webkit-backdrop-filter:blur(8px); backdrop-filter:blur(8px);
}
.hp-video-modal-inner {
  position:relative; width:100%; max-width:900px;
  background:#000; border-radius:16px; overflow:hidden;
  box-shadow:0 20px 60px rgba(0,0,0,0.5);
}
.hp-video-wrap {
  position:relative; width:100%; padding-bottom:56.25%;
}
.hp-video-wrap iframe {
  position:absolute; inset:0; width:100%; height:100%;
}
.hp-video-close {
  position:absolute; top:12px; right:16px; z-index:10;
  background:rgba(0,0,0,0.5); border:none; color:#fff;
  font-size:28px; line-height:1; width:36px; height:36px;
  border-radius:50%; cursor:pointer;
  display:flex; align-items:center; justify-content:center;
  transition:background .15s;
}
.hp-video-close:hover { background:rgba(255,0,0,0.8); }

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

/* ── Why Asaan Capital cards ── */
.hp-why-card {
  display:flex;
  gap:16px;
  align-items:flex-start;
}
.hp-why-icon {
  width:48px;
  height:48px;
  border-radius:12px;
  background:rgba(107,29,34,0.08);
  display:flex;
  align-items:center;
  justify-content:center;
  flex-shrink:0;
}
.hp-why-card h3 {
  font-size:16px;
  font-weight:700;
  color:var(--dash-ink);
  margin:0 0 4px;
}
.hp-why-card p {
  font-size:14px;
  line-height:1.6;
  color:var(--dash-ink-soft);
  margin:0;
}
@media (max-width:480px) {
  .hp-why-card { flex-direction:column; }
  .hp-why-icon { width:40px; height:40px; }
}
@media (max-width:767px) {
  .hp-why-card h3 { font-size:15px; }
  .hp-why-card p { font-size:13px; }
}

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
/* two-column layout */
@media (min-width:901px) {
  .fb-ref-row { flex-direction:row!important; }
  .fb-ref-left { width:65%!important; }
  .fb-ref-right { width:35%!important; }
}

/* card */
.fb-ref-card {
  background:#fff; border-radius:8px;
  border:1px solid var(--dash-border);
  box-shadow:var(--dash-shadow); padding:20px;
  display:flex; flex-direction:column; cursor:pointer;
  transition:box-shadow .2s ease, transform .2s ease, border-color .2s ease;
  scroll-snap-align:start; flex-shrink:0;
}
.fb-ref-card:hover {
  box-shadow:0 10px 30px rgba(0,0,0,0.08);
  transform:translateY(-3px);
  border-color:var(--color-primary);
}
.fb-ref-card .fb-ref-title { transition:color .2s ease; }
.fb-ref-card:hover .fb-ref-title { color:var(--color-primary); }

/* carousel track — always flex for auto-scroll */
.fb-track {
  display:flex; gap:16px; overflow-x:auto;
  scroll-snap-type:x mandatory; scroll-behavior:smooth;
  padding:4px 0;
  -ms-overflow-style:none; scrollbar-width:none;
}
.fb-track::-webkit-scrollbar { display:none; }
.fb-track > * { scroll-snap-align:start; flex-shrink:0; width:calc(50% - 8px); }
@media (max-width:640px) {
  .fb-track > * { width:100%; }
}

/* contact button */
.fb-ref-btn {
  flex-shrink:0;
  background:rgba(177,217,253,0.35); color:#1a4a6e;
  border:none; border-radius:8px; padding:6px 16px;
  font-size:13px; font-weight:600; cursor:pointer;
  text-decoration:none; white-space:nowrap;
  transition:background .16s ease-out, transform .16s;
}
.fb-ref-btn:hover { background:rgba(177,217,253,0.6); }
.fb-ref-btn:active { transform:scale(0.97); }

/* View All CTA */
.fb-ref-cta:hover { background:rgba(107,29,34,0.2)!important; }
.fb-ref-cta:active { transform:scale(0.97); }

/* carousel arrows */
.fb-arrow { opacity:0; transition:opacity .2s ease, background .2s; }
.fb-ref-left:hover .fb-arrow { opacity:1; }
.fb-arrow:hover { background:var(--color-bg-soft)!important; }
.fb-arrow:active { transform:translateY(-50%) scale(0.92)!important; }
@media (max-width:900px) {
  .fb-arrow { display:none!important; }
  .fb-ref-arrows { display:none; }
  .fb-ref-row { gap:24px!important; }
}

/* right column responsive */
@media (max-width:900px) {
  .fb-ref-right { order:-1; margin-bottom:8px; }
}

/* ── Mobile bottom nav ── */
.hp-bottom-nav {
  display:none;
  position:fixed; bottom:0; left:0; right:0;
  z-index:55; height:64px;
  padding-bottom:env(safe-area-inset-bottom,0);
  background:#fff; border-top:1px solid var(--dash-border);
  box-shadow:0 -2px 16px rgba(0,0,0,0.06);
  -webkit-backdrop-filter:blur(12px);
  backdrop-filter:blur(12px);
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
  padding:4px 12px; border-radius:8px; transition:color 150ms, background 150ms;
  white-space:nowrap; min-width:0;
  -webkit-tap-highlight-color:transparent;
}
.hp-bottom-nav-item:active { background:var(--color-bg-soft); }
.hp-bottom-nav-item.active { color:var(--color-primary-vivid); }
.hp-bottom-nav-item.active svg { stroke:var(--color-primary-vivid); }
.hp-bottom-nav-item svg { width:22px; height:22px; display:block; transition:stroke 150ms; }

/* ── Mobile refinements ── */
@media (max-width:767px) {
  .hp-hero-title .highlight { display:inline; }
  .pub-h2 { font-size:1.5rem!important; line-height:1.3!important; }
  .pub-lead { font-size:0.95rem!important; }
  .pub-statstrip { gap:8px!important; }
  .pub-statstrip-item p { font-size:0.85rem!important; }
  .pub-statstrip-num { font-size:1.15rem!important; }
  .pub-statstrip-label { font-size:0.75rem!important; }
  .fb-ref-card { padding:16px!important; }
  .fb-ref-card h3 { font-size:16px!important; }
  .fb-ref-right h2 { font-size:22px!important; }
  .fb-ref-right h3 { font-size:15px!important; }
  .fb-ref-right p { font-size:14px!important; }
  .hp-dual-card { padding:20px!important; }
  .hp-dual-card h3 { font-size:22px!important; }
  .hp-dual-card p { font-size:14px!important; }
  .pub-cta h2 { font-size:22px!important; }
  .pub-cta p { font-size:14px!important; }
  .pub-cta-actions { flex-direction:column; gap:12px; }
  .pub-cta-actions a { width:100%; text-align:center; }
  .pub-wrap { padding:0 16px; }
  .faq-item { padding:12px!important; }
  .faq-header { font-size:14px!important; }
}

@media (max-width:480px) {
  .hp-hero-title { font-size:24px!important; line-height:30px!important; }
  .hp-grid-4 { grid-template-columns:1fr; }
  .pub-feature { padding:20px!important; }
}

/* ── Inline container padding on mobile ── */
@media (max-width:767px) {
  div[style*="padding:0 24px"] { padding-left:16px!important; padding-right:16px!important; }
}

/* ── Tap target boost ── */
.hp-bottom-nav-item { min-height:44px; }
.btn, .pub-cta-actions a, .fb-ref-btn, [class*="hp-bottom-nav"] a {
  min-height:44px;
}

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
<section class="hp-hero">
  <div class="hp-hero-bg"></div>
  <div class="hp-hero-overlay">
    <div style="position:absolute;inset:0;background:radial-gradient(ellipse 600px 400px at 30% 60%, rgba(251,191,36,0.08) 0%, transparent 70%);"></div>
  </div>
  <div class="hp-hero-inner pub-wrap">
    <div class="hp-hero-content">
      <h1 class="hp-hero-title pub-h1" style="margin-bottom:20px;">
        <?= $hero_title ?>
      </h1>
      <p class="hp-hero-sub pub-lead" style="max-width:560px;">
        <?= e($hero_subtitle) ?>
      </p>
      <div class="hp-hero-actions pub-cta-actions" style="justify-content:flex-start;margin-top:32px;">
        <?php if (current_user()): ?>
        <a href="<?= APP_URL ?>/dashboard" class="btn btn-primary">Go to Dashboard</a>
        <a href="<?= APP_URL ?>/browse/businesses" class="btn btn-outline">Browse Businesses</a>
        <?php else: ?>
        <a href="<?= APP_URL ?>/onboarding" class="btn btn-primary">Get Started Now</a>
        <a href="<?= APP_URL ?>/browse/businesses" class="btn btn-outline">Explore Businesses</a>
        <?php endif; ?>
      </div>
      <?php if ($hero_video_id): ?>
      <button type="button" class="hp-hero-play" aria-label="Watch video" onclick="openHeroVideo()">
        <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0C.488 3.45.029 5.804 0 12c.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0C23.512 20.55 23.971 18.196 24 12c-.029-6.185-.484-8.549-4.385-8.816zM9 16V8l8 4-8 4z"/></svg>
        <span>Watch Video</span>
      </button>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Video Modal -->
<div class="hp-video-modal" id="heroVideoModal" onclick="closeHeroVideo(event)">
  <div class="hp-video-modal-inner">
    <button type="button" class="hp-video-close" onclick="closeHeroVideo()" aria-label="Close video">&times;</button>
    <div class="hp-video-wrap">
      <iframe id="heroVideoIframe" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    </div>
  </div>
</div>

<script>
function openHeroVideo() {
  var modal = document.getElementById('heroVideoModal');
  var iframe = document.getElementById('heroVideoIframe');
  iframe.src = 'https://www.youtube-nocookie.com/embed/<?= e($hero_video_id) ?>?rel=0&autoplay=1';
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeHeroVideo(e) {
  if (e && e.target !== e.currentTarget) return;
  var modal = document.getElementById('heroVideoModal');
  var iframe = document.getElementById('heroVideoIframe');
  iframe.src = '';
  modal.style.display = '';
  document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeHeroVideo();
});
</script>

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

<!-- Why Asaan Capital -->
<section class="pub-section surface" style="background:#fff;">
  <div class="pub-wrap" style="max-width:960px;">
    <div class="pub-section-head" style="margin-bottom:48px;">
      <span style="display:inline-block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-primary-vivid);margin-bottom:8px;">Why Asaan Capital</span>
      <h2 class="pub-h2" style="margin-bottom:12px;">Nepal's most trusted business matching platform</h2>
      <p class="pub-text" style="max-width:640px;margin:0 auto;color:var(--dash-ink-soft);">We make it safe, simple, and smart to find the right business partner — whether you're buying, selling, investing, or raising capital.</p>
    </div>
    <div class="hp-grid-2" style="display:grid;gap:32px;">
      <div class="hp-why-card">
        <div class="hp-why-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
          <h3>Every profile is verified</h3>
          <p>All businesses, investors, and advisors are manually screened before they go live. No bots, no fake listings.</p>
        </div>
      </div>
      <div class="hp-why-card">
        <div class="hp-why-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        </div>
        <div>
          <h3>Your privacy is protected</h3>
          <p>Contact details stay hidden until both sides express mutual interest. You control who sees your information.</p>
        </div>
      </div>
      <div class="hp-why-card">
        <div class="hp-why-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </div>
        <div>
          <h3>Fair & transparent process</h3>
          <p>No hidden fees. Clear pricing, straightforward matching, and a 1% finder's fee only when a deal closes.</p>
        </div>
      </div>
      <div class="hp-why-card">
        <div class="hp-why-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <div>
          <h3>Active, engaged network</h3>
          <p>Thousands of verified investors, business owners, and advisors across all seven provinces of Nepal.</p>
        </div>
      </div>
      <div class="hp-why-card">
        <div class="hp-why-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <div>
          <h3>Trusted by industry leaders</h3>
          <p>Partnered with leading business organizations, financial advisors, and industry associations across Nepal.</p>
        </div>
      </div>
      <div class="hp-why-card">
        <div class="hp-why-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0112 2a8 8 0 018 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
        <div>
          <h3>Built for Nepal</h3>
          <p>Designed specifically for the Nepali market. Local knowledge, local regulations, and local support.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Trust Pillars -->
<section class="pub-section surface" style="background:var(--color-bg);">
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
?>
<?php if (!empty($allBiz)): ?>
<section style="background:var(--color-bg);padding:64px 0;">
  <div style="max-width:1200px;margin:0 auto;padding:0 24px;">
    <div style="display:flex;gap:48px;align-items:flex-start;flex-direction:column;" class="fb-ref-row">
      <!-- Left Column -->
      <div style="width:100%;position:relative;" class="fb-ref-left" id="bizMarquee">
        <!-- Carousel Arrows -->
        <div class="fb-ref-arrows">
          <button class="fb-arrow fb-arrow-left" type="button" aria-label="Previous" style="position:absolute;left:-20px;top:50%;transform:translateY(-50%);z-index:10;width:40px;height:40px;border-radius:50%;border:1px solid var(--dash-border);background:#fff;display:flex;align-items:center;justify-content:center;color:var(--color-primary);box-shadow:var(--dash-shadow);cursor:pointer;transition:background .2s;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
          </button>
          <button class="fb-arrow fb-arrow-right" type="button" aria-label="Next" style="position:absolute;right:-20px;top:50%;transform:translateY(-50%);z-index:10;width:40px;height:40px;border-radius:50%;border:1px solid var(--dash-border);background:#fff;display:flex;align-items:center;justify-content:center;color:var(--color-primary);box-shadow:var(--dash-shadow);cursor:pointer;transition:background .2s;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
          </button>
        </div>
        <!-- Cards Track -->
        <div class="fb-track" id="bizMarqueeTrack">
          <?php foreach ($allBiz as $biz):
            $loc = array_filter([$biz['district'] ?? '', $biz['province'] ?? '']);
            $locStr = !empty($loc) ? implode(', ', $loc) : 'Nepal';
            $lt = $ltLabels[$biz['listing_type']] ?? 'Business for Sale';
            $hasStake = !empty($biz['stake_offered_pct']);
            $ap = (int)($biz['asking_price'] ?? 0);
          ?>
          <div class="fb-ref-card" onclick="location.href='<?= APP_URL ?>/business/<?= (int)$biz['id'] ?>'">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
              <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;padding:2px 8px;border-radius:999px;background:rgba(107,29,34,0.08);color:var(--color-primary);"><?= e($lt) ?></span>
              <?php if (!empty($biz['rating'])): ?>
              <div style="display:flex;align-items:center;gap:3px;background:var(--color-bg-soft);padding:2px 6px;border-radius:999px;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="#f59e0b"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <span style="font-size:12px;font-weight:600;color:var(--dash-ink);"><?= e($biz['rating']) ?></span>
              </div>
              <?php endif; ?>
            </div>
            <h3 style="font-family:var(--font-heading);font-size:18px;font-weight:700;color:var(--dash-ink);margin:0 0 4px;" class="fb-ref-title"><?= e($biz['business_name']) ?></h3>
            <p style="font-size:14px;line-height:1.5;color:var(--dash-ink-soft);margin:0 0 12px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
              <?= e(mb_substr($biz['description'] ?? '', 0, 130)) ?>
            </p>
            <div style="display:flex;align-items:center;gap:4px;margin-bottom:12px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
              <span style="font-size:12px;font-weight:500;color:var(--dash-ink-soft);"><?= e($locStr) ?></span>
            </div>
            <div style="background:var(--color-bg-soft);border-radius:8px;padding:12px;margin-bottom:12px;">
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div>
                  <p style="font-size:10px;color:var(--dash-ink-soft);text-transform:uppercase;letter-spacing:0.04em;margin:0 0 1px;">Run Rate Sales</p>
                  <p style="font-size:14px;font-weight:600;color:var(--dash-ink);margin:0;"><?php $rv = $biz['annual_revenue'] ?? 0; echo $rv > 0 ? money($rv) : 'Not disclosed'; ?></p>
                </div>
                <div>
                  <p style="font-size:10px;color:var(--dash-ink-soft);text-transform:uppercase;letter-spacing:0.04em;margin:0 0 1px;">EBITDA</p>
                  <p style="font-size:14px;font-weight:600;color:var(--dash-ink);margin:0;"><?= !empty($biz['ebitda_pct']) ? e($biz['ebitda_pct']).'%' : '—' ?></p>
                </div>
                <div>
                  <p style="font-size:10px;color:var(--dash-ink-soft);text-transform:uppercase;letter-spacing:0.04em;margin:0 0 1px;">Sale Type</p>
                  <p style="font-size:14px;font-weight:600;color:var(--dash-ink);margin:0;"><?= e($lt) ?></p>
                </div>
                <div>
                  <p style="font-size:10px;color:var(--dash-ink-soft);text-transform:uppercase;letter-spacing:0.04em;margin:0 0 1px;"><?= $hasStake ? 'Stake' : 'Asking Price' ?></p>
                  <p style="font-size:14px;font-weight:600;color:var(--dash-ink);margin:0;"><?= $hasStake ? e($biz['stake_offered_pct']).'%' : ($ap > 0 ? money($ap) : 'Contact') ?></p>
                </div>
              </div>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:auto;">
              <div style="display:flex;flex-direction:column;">
                <span style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--dash-ink-soft);">Asking Price</span>
                <span style="font-size:18px;font-weight:800;color:var(--color-primary-vivid);"><?php if ($ap > 0) { echo $ap >= 10000000 ? 'रू '.number_format($ap/10000000,1).'Cr' : ($ap >= 100000 ? 'रू '.number_format($ap/100000,1).'L' : money($ap)); } else { echo 'Contact for price'; } ?></span>
              </div>
              <a href="<?= APP_URL ?>/business/<?= (int)$biz['id'] ?>" class="fb-ref-btn" onclick="event.stopPropagation()">Contact Business</a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <!-- Right Column -->
      <div style="width:100%;display:flex;flex-direction:column;justify-content:center;" class="fb-ref-right">
        <h2 style="font-family:var(--font-heading);font-size:28px;font-weight:700;color:var(--color-primary);margin:0 0 4px;">Businesses for Sale in Nepal</h2>
        <h3 style="font-family:var(--font-heading);font-size:18px;font-weight:600;color:var(--color-secondary);margin:0 0 12px;">Pre-screened businesses for sale across Nepal.</h3>
        <p style="font-size:16px;line-height:1.7;color:var(--dash-ink-soft);margin:0 0 28px;">
          Explore pre-screened businesses for sale across Nepal. Find verified businesses looking for full sale, partial stake sale, investment, or business loans. Asaan Capital helps investors, buyers, and entrepreneurs discover trusted opportunities with confidence.
        </p>
        <a href="<?= APP_URL ?>/browse/businesses" style="display:inline-block;background:rgba(107,29,34,0.12);color:var(--color-primary);padding:14px 32px;border-radius:8px;font-size:16px;font-weight:700;text-decoration:none;transition:background .2s,transform .15s;align-self:flex-start;" class="fb-ref-cta">View All Businesses</a>
      </div>
    </div>
  </div>
</section>
<?php else: ?>
<section style="background:var(--dash-bg);padding:64px 0;">
  <div style="max-width:1200px;margin:0 auto;padding:0 24px;">
    <div style="text-align:center;padding:48px 0;">
      <p style="color:var(--dash-ink-soft);margin:0;font-size:16px;">No featured businesses available right now.</p>
    </div>
  </div>
</section>
<?php endif; ?><!-- END FEATURED BUSINESSES -->

<!-- Dual Path Cards -->
<section class="pub-section surface">
  <div class="pub-wrap">
    <div class="hp-grid-2 hp-gap-48" style="display:grid;">
      <div class="hp-dual-card" style="position:relative;overflow:hidden;border-radius:12px;padding:20px;background:#ffdad933;border:1px solid #ffb3b2;">
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
      <div class="hp-dual-card" style="position:relative;overflow:hidden;border-radius:12px;padding:20px;background:#f5e6e6;border:1px solid #e0c5c5;">
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
$pitchStages = ['idea'=>'Idea', 'prototype'=>'Prototype', 'early_traction'=>'Early Traction', 'growth'=>'Growth', 'scaling'=>'Scaling'];
?>
<?php if (!empty($displayPitches)): ?>
<section style="background:var(--dash-bg);padding:64px 0;">
  <div style="max-width:1200px;margin:0 auto;padding:0 24px;">
    <div style="display:flex;gap:48px;align-items:flex-start;flex-direction:column;" class="fb-ref-row">
      <!-- Left Column -->
      <div style="width:100%;position:relative;" class="fb-ref-left" id="pitchMarquee">
        <!-- Carousel Arrows -->
        <div class="fb-ref-arrows">
          <button class="hp-marquee-arrow hp-marquee-arrow-left fb-arrow" type="button" aria-label="Previous" style="position:absolute;left:-20px;top:50%;transform:translateY(-50%);z-index:10;width:40px;height:40px;border-radius:50%;border:1px solid var(--dash-border);background:#fff;display:flex;align-items:center;justify-content:center;color:var(--color-primary);box-shadow:var(--dash-shadow);cursor:pointer;transition:background .2s,opacity .2s;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
          </button>
          <button class="hp-marquee-arrow hp-marquee-arrow-right fb-arrow" type="button" aria-label="Next" style="position:absolute;right:-20px;top:50%;transform:translateY(-50%);z-index:10;width:40px;height:40px;border-radius:50%;border:1px solid var(--dash-border);background:#fff;display:flex;align-items:center;justify-content:center;color:var(--color-primary);box-shadow:var(--dash-shadow);cursor:pointer;transition:background .2s,opacity .2s;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
          </button>
        </div>
        <!-- Cards Track -->
        <div class="pitch-scroll" id="pitchMarqueeTrack" style="display:flex;gap:16px;overflow-x:auto;scroll-snap-type:x mandatory;scroll-behavior:smooth;padding:4px 0;-ms-overflow-style:none;scrollbar-width:none;">
          <style>.pitch-scroll::-webkit-scrollbar { display:none; } .pitch-scroll > * { scroll-snap-align:start; flex-shrink:0; width:calc(50% - 8px); } @media (max-width:640px) { .pitch-scroll > * { width:100%; } }</style>
          <?php foreach ($displayPitches as $p):
            $stage = $pitchStages[$p['stage']] ?? '';
          ?>
          <div style="background:#fff;border-radius:8px;border:1px solid var(--dash-border);box-shadow:var(--dash-shadow);padding:20px;display:flex;flex-direction:column;cursor:pointer;transition:box-shadow .2s ease,transform .2s ease,border-color .2s ease;" onclick="location.href='<?= APP_URL ?>/pitch/<?= (int)$p['id'] ?>'" onmouseover="this.style.boxShadow='0 10px 30px rgba(0,0,0,0.08)';this.style.transform='translateY(-3px)';this.style.borderColor='var(--color-secondary)'" onmouseout="this.style.boxShadow='';this.style.transform='';this.style.borderColor=''">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
              <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;padding:2px 8px;border-radius:999px;background:rgba(30,72,102,0.08);color:var(--color-secondary);">Seeking Investment</span>
              <?php if (!empty($p['sector_name'])): ?>
              <span style="font-size:10px;font-weight:600;color:var(--dash-ink-soft);background:var(--color-bg-soft);padding:2px 6px;border-radius:999px;"><?= e($p['sector_name']) ?></span>
              <?php endif; ?>
            </div>
            <h3 style="font-family:var(--font-heading);font-size:18px;font-weight:700;color:var(--dash-ink);margin:0 0 4px;"><?= e($p['tagline']) ?></h3>
            <p style="font-size:14px;line-height:1.5;color:var(--dash-ink-soft);margin:0 0 12px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
              <?= e(mb_substr($p['short_summary'] ?? $p['problem_statement'] ?? '', 0, 130)) ?>
            </p>
            <?php if ($stage): ?>
            <div style="display:flex;align-items:center;gap:4px;margin-bottom:12px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <span style="font-size:12px;font-weight:500;color:var(--dash-ink-soft);"><?= e($stage) ?> stage</span>
            </div>
            <?php endif; ?>
            <div style="background:var(--color-bg-soft);border-radius:8px;padding:12px;margin-bottom:12px;">
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div>
                  <p style="font-size:10px;color:var(--dash-ink-soft);text-transform:uppercase;letter-spacing:0.04em;margin:0 0 1px;">Funding Required</p>
                  <p style="font-size:14px;font-weight:600;color:var(--dash-ink);margin:0;"><?= money($p['funding_amount']) ?></p>
                </div>
                <div>
                  <p style="font-size:10px;color:var(--dash-ink-soft);text-transform:uppercase;letter-spacing:0.04em;margin:0 0 1px;">Equity Offered</p>
                  <p style="font-size:14px;font-weight:600;color:var(--dash-ink);margin:0;"><?= !empty($p['equity_offered']) ? e($p['equity_offered']).'%' : '—' ?></p>
                </div>
                <div>
                  <p style="font-size:10px;color:var(--dash-ink-soft);text-transform:uppercase;letter-spacing:0.04em;margin:0 0 1px;">Valuation</p>
                  <p style="font-size:14px;font-weight:600;color:var(--dash-ink);margin:0;"><?= money($p['valuation'] ?? 0) ?></p>
                </div>
                <div>
                  <p style="font-size:10px;color:var(--dash-ink-soft);text-transform:uppercase;letter-spacing:0.04em;margin:0 0 1px;">Investors</p>
                  <p style="font-size:14px;font-weight:600;color:var(--dash-ink);margin:0;"><?= (int)($p['investor_count'] ?? 0) ?> interested</p>
                </div>
              </div>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:auto;">
              <div style="display:flex;flex-direction:column;">
                <span style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--dash-ink-soft);">Minimum Investment</span>
                <span style="font-size:18px;font-weight:800;color:var(--color-secondary);"><?php $minInv = (int)($p['min_investment'] ?? $p['funding_amount'] ?? 0); echo $minInv > 0 ? money($minInv) : 'Contact'; ?></span>
              </div>
              <a href="<?= APP_URL ?>/pitch/<?= (int)$p['id'] ?>" style="flex-shrink:0;background:rgba(177,217,253,0.35);color:#1a4a6e;border:none;border-radius:8px;padding:6px 16px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;white-space:nowrap;transition:background .16s,transform .16s;" onclick="event.stopPropagation()" onmouseover="this.style.background='rgba(177,217,253,0.6)'" onmouseout="this.style.background='rgba(177,217,253,0.35)'">View Pitch</a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <!-- Right Column -->
      <div style="width:100%;display:flex;flex-direction:column;justify-content:center;" class="fb-ref-right">
        <h2 style="font-family:var(--font-heading);font-size:28px;font-weight:700;color:var(--color-secondary);margin:0 0 4px;"><?= $pitchSectionTitle ?></h2>
        <h3 style="font-family:var(--font-heading);font-size:18px;font-weight:600;color:var(--color-primary);margin:0 0 12px;">Pre-verified entrepreneurs seeking capital for growth.</h3>
        <p style="font-size:16px;line-height:1.7;color:var(--dash-ink-soft);margin:0 0 28px;">
          Discover pre-verified entrepreneurs and startups from Nepal seeking investment. Each pitch is reviewed by our analysts. Connect directly with founders building the next generation of Nepali businesses.
        </p>
        <a href="<?= APP_URL ?>/browse/entrepreneurs" style="display:inline-block;background:rgba(30,72,102,0.12);color:var(--color-secondary);padding:14px 32px;border-radius:8px;font-size:16px;font-weight:700;text-decoration:none;transition:background .2s,transform .15s;align-self:flex-start;" onmouseover="this.style.background='rgba(30,72,102,0.2)'" onmouseout="this.style.background='rgba(30,72,102,0.12)'">View All Pitches</a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Featured Investors -->
<?php if (!empty($featured_investors)): ?>
<section style="background:var(--dash-card);padding:64px 0;">
  <div style="max-width:1200px;margin:0 auto;padding:0 24px;">
    <div style="display:flex;gap:48px;align-items:flex-start;flex-direction:column;" class="fb-ref-row">
      <!-- Left Column -->
      <div style="width:100%;position:relative;" class="fb-ref-left" id="investorMarquee">
        <div class="fb-ref-arrows">
          <button class="fb-arrow fb-arrow-left" type="button" aria-label="Previous" style="position:absolute;left:-20px;top:50%;transform:translateY(-50%);z-index:10;width:40px;height:40px;border-radius:50%;border:1px solid var(--dash-border);background:#fff;display:flex;align-items:center;justify-content:center;color:var(--color-primary);box-shadow:var(--dash-shadow);cursor:pointer;transition:background .2s;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
          </button>
          <button class="fb-arrow fb-arrow-right" type="button" aria-label="Next" style="position:absolute;right:-20px;top:50%;transform:translateY(-50%);z-index:10;width:40px;height:40px;border-radius:50%;border:1px solid var(--dash-border);background:#fff;display:flex;align-items:center;justify-content:center;color:var(--color-primary);box-shadow:var(--dash-shadow);cursor:pointer;transition:background .2s;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
          </button>
        </div>
        <div class="fb-track" id="investorMarqueeTrack">
          <?php foreach ($featured_investors as $inv):
            $sectors = json_decode($inv['preferred_sectors'] ?? '[]', true) ?: [];
            $invTypeLabels = ['individual'=>'Individual','angel'=>'Angel','venture_capital'=>'VC','private_equity'=>'PE','family_office'=>'Family Office','corporate'=>'Corporate','lender'=>'Lender','advisor'=>'Advisor'];
            $typeLbl = $invTypeLabels[$inv['account_type']] ?? 'Investor';
            $init = '';
            $np = explode(' ', $inv['name'] ?? 'U');
            foreach ($np as $p) $init .= mb_strtoupper(mb_substr($p, 0, 1));
            $init = mb_substr($init, 0, 2);
            $loc = array_filter([$inv['district'] ?? '', $inv['province'] ?? '']);
            $locStr = !empty($loc) ? implode(', ', $loc) : 'Nepal';
          ?>
          <div class="fb-ref-card" onclick="location.href='<?= APP_URL ?>/investor/<?= (int)$inv['id'] ?>'">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
              <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;padding:2px 8px;border-radius:999px;background:rgba(30,72,102,0.08);color:var(--color-secondary);"><?= e($typeLbl) ?></span>
              <?php if (!empty($inv['is_premium'])): ?>
              <span style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:999px;background:rgba(199,122,18,0.12);color:var(--color-warning);">Premium</span>
              <?php endif; ?>
            </div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
              <?php if ($inv['profile_photo']): ?>
              <img src="<?= upload_url($inv['profile_photo']) ?>" alt="" style="width:44px;height:44px;border-radius:50%;object-fit:cover;">
              <?php else: ?>
              <div style="width:44px;height:44px;border-radius:50%;background:var(--color-bg-soft);display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:var(--dash-ink-soft);"><?= e($init) ?></div>
              <?php endif; ?>
              <div>
                <h3 style="font-size:16px;font-weight:700;color:var(--dash-ink);margin:0;"><?= e($inv['name']) ?></h3>
                <?php if ($inv['company_name']): ?>
                <p style="font-size:12px;color:var(--dash-ink-soft);margin:0;"><?= e($inv['company_name']) ?></p>
                <?php endif; ?>
              </div>
            </div>
            <p style="font-size:13px;line-height:1.5;color:var(--dash-ink-soft);margin:0 0 10px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
              <?= e(mb_substr($inv['bio'] ?? '', 0, 120)) ?: 'Experienced investor looking for the right opportunity.' ?>
            </p>
            <div style="display:flex;align-items:center;gap:4px;margin-bottom:10px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
              <span style="font-size:12px;font-weight:500;color:var(--dash-ink-soft);"><?= e($locStr) ?></span>
            </div>
            <div style="background:var(--color-bg-soft);border-radius:8px;padding:10px;margin-bottom:10px;">
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                <div>
                  <p style="font-size:10px;color:var(--dash-ink-soft);text-transform:uppercase;letter-spacing:0.04em;margin:0 0 1px;">Capital Deployed</p>
                  <p style="font-size:13px;font-weight:600;color:var(--dash-ink);margin:0;"><?= $inv['total_capital_deployed'] ? money($inv['total_capital_deployed']) : '—' ?></p>
                </div>
                <div>
                  <p style="font-size:10px;color:var(--dash-ink-soft);text-transform:uppercase;letter-spacing:0.04em;margin:0 0 1px;">Past Investments</p>
                  <p style="font-size:13px;font-weight:600;color:var(--dash-ink);margin:0;"><?= (int)$inv['past_investments'] ?> deals</p>
                </div>
                <div style="grid-column:1/-1;">
                  <p style="font-size:10px;color:var(--dash-ink-soft);text-transform:uppercase;letter-spacing:0.04em;margin:0 0 1px;">Preferred Sectors</p>
                  <p style="font-size:13px;font-weight:600;color:var(--dash-ink);margin:0;"><?= !empty($sectors) ? e(implode(', ', array_slice($sectors, 0, 3))) . (count($sectors) > 3 ? ' +' . (count($sectors)-3) : '') : 'All sectors' ?></p>
                </div>
              </div>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:auto;">
              <div style="display:flex;flex-direction:column;">
                <span style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--dash-ink-soft);">Ticket Range</span>
                <span style="font-size:14px;font-weight:800;color:var(--color-primary-vivid);"><?= $inv['ticket_min'] || $inv['ticket_max'] ? 'NPR ' . number_format((float)$inv['ticket_min']) . ' – ' . number_format((float)$inv['ticket_max']) : 'Flexible' ?></span>
              </div>
              <a href="<?= APP_URL ?>/investor/<?= (int)$inv['id'] ?>" class="fb-ref-btn" onclick="event.stopPropagation()">View Profile</a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div style="width:100%;display:flex;flex-direction:column;justify-content:center;" class="fb-ref-right">
        <h2 style="font-family:var(--font-heading);font-size:28px;font-weight:700;color:var(--color-secondary);margin:0 0 4px;">Qualified Investors &amp; Buyers</h2>
        <h3 style="font-family:var(--font-heading);font-size:18px;font-weight:600;color:var(--color-primary);margin:0 0 12px;">Verified investors actively looking for opportunities in Nepal.</h3>
        <p style="font-size:16px;line-height:1.7;color:var(--dash-ink-soft);margin:0 0 28px;">
          Browse our network of verified investors, from individual angel investors to venture capital firms and private equity groups. Each investor has been reviewed and has a track record of deploying capital in Nepali businesses.
        </p>
        <a href="<?= APP_URL ?>/browse/investors" style="display:inline-block;background:rgba(30,72,102,0.12);color:var(--color-secondary);padding:14px 32px;border-radius:8px;font-size:16px;font-weight:700;text-decoration:none;transition:background .2s,transform .15s;align-self:flex-start;" onmouseover="this.style.background='rgba(30,72,102,0.2)'" onmouseout="this.style.background='rgba(30,72,102,0.12)'">View All Investors</a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($faqs)): ?>
<?php
$faqSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [';
$first = true;
foreach ($faqs as $f) {
  if (!$first) $faqSchema .= ',';
  $first = false;
  $question = str_replace('"', '\\"', $f['question']);
  $answer = str_replace('"', '\\"', strip_tags($f['answer']));
  $faqSchema .= '{
    "@type": "Question",
    "name": "' . $question . '",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "' . $answer . '"
    }
  }';
}
$faqSchema .= ']}</script>';
echo $faqSchema;
?>
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
        <div class="faq-answer-inner trix-content"><?php
          $answer = $faq['answer'];
          if ($answer !== strip_tags($answer)) {
              echo $answer;
          } else {
              echo '<p>' . nl2br(e($answer)) . '</p>';
          }
        ?></div>
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

// Marquee auto-scroll with improved touch/swipe
function initMarquee(id) {
  var track = document.getElementById(id + 'Track');
  var marquee = document.getElementById(id);
  if (!track || !marquee) return;

  var leftArrow = marquee.querySelector('.fb-arrow-left, .hp-marquee-arrow-left');
  var rightArrow = marquee.querySelector('.fb-arrow-right, .hp-marquee-arrow-right');
  var interval = null;
  var isPaused = false;

  // Detect if we're on a touch device
  var isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

  function getScrollAmount() {
    var cards = track.querySelectorAll('.pub-card, .fb-card, .fb-ref-card, .pitch-scroll > *, [class*="hp-marquee-track"] > *');
    if (cards.length < 2) return 280;
    var tr = track.getBoundingClientRect();
    for (var i = 0; i < cards.length; i++) {
      var cr = cards[i].getBoundingClientRect();
      if (cr.left > tr.left + 10) {
        return Math.max(cr.left - tr.left, 100);
      }
    }
    var first = cards[0];
    return first ? first.offsetWidth + 16 : 280;
  }

  function scrollTo(left) {
    track.scrollTo({ left: left, behavior: 'smooth' });
  }

  function scrollNext() {
    if (isPaused) return;
    var maxScroll = track.scrollWidth - track.clientWidth;
    var amt = getScrollAmount();
    var next = track.scrollLeft + amt;
    if (next >= maxScroll - 10) next = 0;
    track.scrollTo({ left: next, behavior: 'smooth' });
  }

  function scrollPrev() {
    var prev = track.scrollLeft - getScrollAmount();
    if (prev < 0) prev = 0;
    track.scrollTo({ left: prev, behavior: 'smooth' });
  }

  function startAutoScroll() {
    stopAutoScroll();
    interval = setInterval(scrollNext, 4000);
  }

  function stopAutoScroll() {
    if (interval) { clearInterval(interval); interval = null; }
  }

  // Arrow clicks: scroll one card, restart timer
  if (leftArrow) {
    leftArrow.addEventListener('click', function(e) {
      e.stopPropagation();
      stopAutoScroll();
      scrollPrev();
      setTimeout(startAutoScroll, 5000);
    });
  }
  if (rightArrow) {
    rightArrow.addEventListener('click', function(e) {
      e.stopPropagation();
      stopAutoScroll();
      scrollNext();
      setTimeout(startAutoScroll, 5000);
    });
  }

  // Pause on hover (desktop only)
  if (!isTouchDevice) {
    marquee.addEventListener('mouseenter', function() { isPaused = true; });
    marquee.addEventListener('mouseleave', function() { isPaused = false; });
  }

  // Touch swipe with passive handlers
  var startX = 0, startY = 0, isDragging = false;

  track.addEventListener('touchstart', function(e) {
    startX = e.touches[0].clientX;
    startY = e.touches[0].clientY;
    isDragging = false;
    stopAutoScroll();
  }, { passive: true });

  track.addEventListener('touchmove', function(e) {
    if (!startX) return;
    var dx = Math.abs(e.touches[0].clientX - startX);
    var dy = Math.abs(e.touches[0].clientY - startY);
    // Only activate if horizontal swipe dominates
    if (dx > dy && dx > 10) {
      isDragging = true;
    }
  }, { passive: true });

  track.addEventListener('touchend', function(e) {
    if (!isDragging) { startAutoScroll(); return; }
    isDragging = false;
    var diff = startX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 50) {
      if (diff > 0) scrollNext();
      else scrollPrev();
    }
    startAutoScroll();
  }, { passive: true });

  // Only auto-scroll if overflow
  if (track.scrollWidth > track.clientWidth + 2) {
    startAutoScroll();
  }
}

initMarquee('bizMarquee');
initMarquee('pitchMarquee');
initMarquee('investorMarquee');

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
