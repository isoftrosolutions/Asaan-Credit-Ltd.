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

$featured_biz = db()->query("SELECT * FROM businesses WHERE is_published=1 AND is_featured=1 ORDER BY rating DESC LIMIT 6")->fetchAll();
$featured_pitches = db()->query("SELECT p.*, s.name as sector_name FROM pitches p LEFT JOIN sectors s ON p.sector_id = s.id WHERE p.is_published=1 AND p.is_featured=1 ORDER BY p.id DESC LIMIT 6")->fetchAll();
$faqs = db()->query("SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order LIMIT 4")->fetchAll();

$pageTitle = APP_NAME;
$forcePublicHeader = true; // home keeps the public marketing nav even when logged in
require __DIR__ . '/../includes/header.php';
?>
<style>
@media (min-width:768px) {
  .hp-hero { min-height:600px; }
  .hp-hero-inner { padding-top:80px; padding-bottom:80px; }
  .hp-hero-title { font-size:48px; line-height:56px; }
  .hp-hide-mobile { display:block; }
  .hp-show-mobile { display:none; }
  .hp-stats-row { flex-direction:row; }
  .hp-grid-2 { grid-template-columns:repeat(2,1fr); }
  .hp-grid-3 { grid-template-columns:repeat(3,1fr); }
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
  .hp-gap-48 { gap:24px; }
  .hp-stats-divider { display:none; }
}
@media (max-width:639px) {
  .hp-hero-actions { flex-direction:column; }
  .hp-hero-actions a { width:100%; text-align:center; }
}
@keyframes hpFadeUp {
  from { opacity:0; transform:translateY(16px); }
  to { opacity:1; transform:translateY(0); }
}
.hp-animate-in { opacity:0; transform:translateY(16px); transition:opacity 0.7s ease, transform 0.7s ease; }
.hp-animate-in.visible { opacity:1; transform:translateY(0); }
</style>
<main>
<!-- Hero Section -->
<section class="hp-hero" style="position:relative;overflow:hidden;display:flex;align-items:center;">
  <div style="position:absolute;inset:0;z-index:0;background-image:url('/assets/hero.jpeg');background-size:cover;background-position:center;"></div>
  <div style="position:absolute;inset:0;z-index:1;background:linear-gradient(135deg, #00263f 0%, rgba(0,38,63,0.85) 50%, rgba(0,38,63,0.3) 100%);"></div>
  <div class="hp-hero-inner" style="max-width:1200px;margin:0 auto;padding-left:24px;padding-right:24px;width:100%;position:relative;z-index:10;">
    <div style="max-width:580px;">
      <div style="margin-bottom:24px;">
        <h1 class="hp-hero-title" style="font-weight:800;letter-spacing:-0.02em;color:white;font-family:Montserrat,sans-serif;line-height:1.2;">
          <?= $hero_title ?>
        </h1>
      </div>
      <p style="font-size:18px;line-height:28px;color:white;opacity:0.9;font-family:Inter,sans-serif;margin-bottom:24px;">
        <?= e($hero_subtitle) ?>
      </p>
      <div class="hp-hero-actions" style="display:flex;gap:16px;padding-top:16px;">
        <a href="<?= APP_URL ?>/signup" style="display:inline-block;background:#98202A;color:white;padding:12px 32px;border-radius:8px;font-weight:600;font-size:16px;line-height:24px;font-family:Inter,sans-serif;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);transition:all 0.2s;text-decoration:none;" onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'" onmousedown="this.style.transform='scale(0.95)'" onmouseup="this.style.transform='scale(1)'">
          I'm an Investor
        </a>
        <a href="<?= APP_URL ?>/signup" style="display:inline-block;border:1.5px solid rgba(255,255,255,0.4);color:white;padding:12px 32px;border-radius:8px;font-weight:600;font-size:16px;line-height:24px;font-family:Inter,sans-serif;transition:all 0.2s;text-decoration:none;backdrop-filter:blur(4px);" onmouseover="this.style.background='rgba(255,255,255,0.1)';this.style.borderColor='rgba(255,255,255,0.7)'" onmouseout="this.style.background='transparent';this.style.borderColor='rgba(255,255,255,0.4)'" onmousedown="this.style.transform='scale(0.95)'" onmouseup="this.style.transform='scale(1)'">
          I'm an Entrepreneur
        </a>
      </div>
    </div>
  </div>
</section>

<!-- Stats Bar -->
<section style="padding:16px 0;border-bottom:1px solid #dbc0bf;background:#f6f3f2;">
  <div class="hp-stats-row" style="max-width:1200px;margin:0 auto;padding:0 24px;display:flex;justify-content:space-between;align-items:center;text-align:center;gap:16px;">
    <div style="display:flex;align-items:center;gap:8px;">
      <span style="color:#3b6281;font-family:'Material Symbols Outlined';font-size:24px;font-variation-settings:'FILL' 1;">verified_user</span>
      <p style="font-size:20px;line-height:28px;font-weight:600;color:#3b6281;font-family:Montserrat,sans-serif;margin:0;"><?= e($stats_investors) ?> <span style="font-size:16px;line-height:24px;font-weight:400;color:#554242;font-family:Inter,sans-serif;">Verified Investors</span></p>
    </div>
    <div class="hp-stats-divider" style="height:32px;width:1px;background:#dbc0bf;"></div>
    <div style="display:flex;align-items:center;gap:8px;">
      <span style="color:#3b6281;font-family:'Material Symbols Outlined';font-size:24px;font-variation-settings:'FILL' 1;">rocket_launch</span>
      <p style="font-size:20px;line-height:28px;font-weight:600;color:#3b6281;font-family:Montserrat,sans-serif;margin:0;"><?= e($stats_businesses) ?> <span style="font-size:16px;line-height:24px;font-weight:400;color:#554242;font-family:Inter,sans-serif;">Active Pitches</span></p>
    </div>
    <div class="hp-stats-divider" style="height:32px;width:1px;background:#dbc0bf;"></div>
    <div style="display:flex;align-items:center;gap:8px;">
      <span style="color:#3b6281;font-family:'Material Symbols Outlined';font-size:24px;font-variation-settings:'FILL' 1;">handshake</span>
      <p style="font-size:20px;line-height:28px;font-weight:600;color:#3b6281;font-family:Montserrat,sans-serif;margin:0;"><?= e($stats_matches) ?> <span style="font-size:16px;line-height:24px;font-weight:400;color:#554242;font-family:Inter,sans-serif;">Successful Matches</span></p>
    </div>
  </div>
</section>

<!-- Wave Divider -->
<div style="background:linear-gradient(90deg,#6B1D22 0%,#1E4866 100%);height:4px;width:100%;"></div>

<!-- How It Works -->
<section style="padding:48px 0;background:#fcf9f8;">
  <div style="max-width:1200px;margin:0 auto;padding:0 24px;">
    <div style="text-align:center;margin-bottom:48px;">
      <h2 style="font-size:32px;line-height:40px;font-weight:700;letter-spacing:-0.01em;margin-bottom:16px;color:#6B1D22;font-family:Montserrat,sans-serif;">How It Works</h2>
      <p style="font-size:16px;line-height:24px;color:#554242;font-family:Inter,sans-serif;margin:0;">A streamlined three-step journey to your next big opportunity.</p>
    </div>
    <div class="hp-grid-3 hp-gap-48" style="display:grid;">
      <div style="padding:24px;background:white;border-radius:12px;border:1px solid #dbc0bf4d;text-align:center;transition:box-shadow 0.3s;cursor:default;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)'">
        <div style="width:64px;height:64px;margin:0 auto 24px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#f0eded;transition:transform 0.3s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
          <span style="color:#6B1D22;font-family:'Material Symbols Outlined';font-size:32px;">person_add</span>
        </div>
        <h3 style="font-size:20px;line-height:28px;font-weight:600;margin-bottom:16px;color:#00263f;font-family:Montserrat,sans-serif;">Step 1</h3>
        <p style="font-weight:600;font-size:16px;line-height:24px;margin-bottom:8px;color:#1c1b1b;font-family:Inter,sans-serif;">Create your profile</p>
        <p style="font-size:14px;line-height:20px;color:#554242;font-family:Inter,sans-serif;margin:0;">Detailed background to ensure quality and intent.</p>
      </div>
      <div style="padding:24px;background:white;border-radius:12px;border:1px solid #dbc0bf4d;text-align:center;transition:box-shadow 0.3s;cursor:default;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)'">
        <div style="width:64px;height:64px;margin:0 auto 24px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#f0eded;transition:transform 0.3s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
          <span style="color:#3b6281;font-family:'Material Symbols Outlined';font-size:32px;">verified</span>
        </div>
        <h3 style="font-size:20px;line-height:28px;font-weight:600;margin-bottom:16px;color:#00263f;font-family:Montserrat,sans-serif;">Step 2</h3>
        <p style="font-weight:600;font-size:16px;line-height:24px;margin-bottom:8px;color:#1c1b1b;font-family:Inter,sans-serif;">Get verified by our team</p>
        <p style="font-size:14px;line-height:20px;color:#554242;font-family:Inter,sans-serif;margin:0;">Strict compliance checks for maximum security.</p>
      </div>
      <div style="padding:24px;background:white;border-radius:12px;border:1px solid #dbc0bf4d;text-align:center;transition:box-shadow 0.3s;cursor:default;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)'">
        <div style="width:64px;height:64px;margin:0 auto 24px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#f0eded;transition:transform 0.3s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
          <span style="color:#6B1D22;font-family:'Material Symbols Outlined';font-size:32px;">handshake</span>
        </div>
        <h3 style="font-size:20px;line-height:28px;font-weight:600;margin-bottom:16px;color:#00263f;font-family:Montserrat,sans-serif;">Step 3</h3>
        <p style="font-weight:600;font-size:16px;line-height:24px;margin-bottom:8px;color:#1c1b1b;font-family:Inter,sans-serif;">Connect with the right match</p>
        <p style="font-size:14px;line-height:20px;color:#554242;font-family:Inter,sans-serif;margin:0;">Direct messaging and deal-flow management.</p>
      </div>
    </div>
  </div>
</section>

<!-- Dual Path Cards -->
<section style="padding:48px 0;background:#ffffff;">
  <div style="max-width:1200px;margin:0 auto;padding:0 24px;">
    <div class="hp-grid-2 hp-gap-48" style="display:grid;">
      <div style="position:relative;overflow:hidden;border-radius:12px;padding:24px;background:#ffdad933;border:1px solid #ffb3b2;">
        <div style="position:absolute;right:-40px;top:-40px;opacity:0.1;transition:transform 0.7s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
          <span style="font-size:160px;font-family:'Material Symbols Outlined';">finance_chip</span>
        </div>
        <div style="position:relative;z-index:10;">
          <h3 style="font-size:32px;line-height:40px;font-weight:700;letter-spacing:-0.01em;margin-bottom:16px;color:#6B1D22;font-family:Montserrat,sans-serif;">For Investors</h3>
          <p style="font-size:16px;line-height:24px;margin-bottom:24px;max-width:400px;color:#554242;font-family:Inter,sans-serif;">
            Access vetted startups from Nepal across diverse sectors including Agriculture, SaaS, and Energy. View pitch decks and financial reports instantly.
          </p>
          <ul style="list-style:none;padding:0;margin-bottom:24px;">
            <li style="display:flex;align-items:center;gap:4px;font-size:12px;line-height:16px;font-weight:600;letter-spacing:0.05em;color:#1c1b1b;font-family:Inter,sans-serif;margin-bottom:8px;">
              <span style="color:#6B1D22;font-family:'Material Symbols Outlined';font-size:14px;font-variation-settings:'FILL' 1;">check_circle</span>
              Pre-vetted Opportunities
            </li>
            <li style="display:flex;align-items:center;gap:4px;font-size:12px;line-height:16px;font-weight:600;letter-spacing:0.05em;color:#1c1b1b;font-family:Inter,sans-serif;">
              <span style="color:#6B1D22;font-family:'Material Symbols Outlined';font-size:14px;font-variation-settings:'FILL' 1;">check_circle</span>
              Direct Entrepreneur Access
            </li>
          </ul>
          <a href="<?= APP_URL ?>/signup" style="display:inline-block;padding:12px 24px;border-radius:8px;font-weight:600;font-size:16px;line-height:24px;color:white;background:#6B1D22;font-family:Inter,sans-serif;text-decoration:none;transition:filter 0.2s;" onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'">Start Investing</a>
        </div>
      </div>
      <div style="position:relative;overflow:hidden;border-radius:12px;padding:24px;background:#cce5ff4d;border:1px solid #a4cbef;">
        <div style="position:absolute;right:-40px;top:-40px;opacity:0.1;transition:transform 0.7s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
          <span style="font-size:160px;font-family:'Material Symbols Outlined';">rocket</span>
        </div>
        <div style="position:relative;z-index:10;">
          <h3 style="font-size:32px;line-height:40px;font-weight:700;letter-spacing:-0.01em;margin-bottom:16px;color:#3b6281;font-family:Montserrat,sans-serif;">For Entrepreneurs</h3>
          <p style="font-size:16px;line-height:24px;margin-bottom:24px;max-width:400px;color:#554242;font-family:Inter,sans-serif;">
            List your venture and get matched with professional investors who understand the Nepalese market. Secure funding to scale your vision.
          </p>
          <ul style="list-style:none;padding:0;margin-bottom:24px;">
            <li style="display:flex;align-items:center;gap:4px;font-size:12px;line-height:16px;font-weight:600;letter-spacing:0.05em;color:#1c1b1b;font-family:Inter,sans-serif;margin-bottom:8px;">
              <span style="color:#3b6281;font-family:'Material Symbols Outlined';font-size:14px;font-variation-settings:'FILL' 1;">check_circle</span>
              Visibility to HNIs
            </li>
            <li style="display:flex;align-items:center;gap:4px;font-size:12px;line-height:16px;font-weight:600;letter-spacing:0.05em;color:#1c1b1b;font-family:Inter,sans-serif;">
              <span style="color:#3b6281;font-family:'Material Symbols Outlined';font-size:14px;font-variation-settings:'FILL' 1;">check_circle</span>
              Fundraising Assistance
            </li>
          </ul>
          <a href="<?= APP_URL ?>/signup" style="display:inline-block;padding:12px 24px;border-radius:8px;font-weight:600;font-size:16px;line-height:24px;color:white;background:#3b6281;font-family:Inter,sans-serif;text-decoration:none;transition:filter 0.2s;" onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'">Pitch Your Idea</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Featured Businesses -->
<?php if (!empty($featured_biz)): ?>
<section style="padding:48px 0;background:#fcf9f8;">
  <div style="max-width:1200px;margin:0 auto;padding:0 24px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:24px;gap:16px;flex-wrap:wrap;">
      <div>
        <h2 style="font-size:32px;line-height:40px;font-weight:700;letter-spacing:-0.01em;color:#6B1D22;font-family:Montserrat,sans-serif;margin:0 0 8px 0;">Featured Businesses</h2>
        <p style="font-size:16px;line-height:24px;color:#554242;font-family:Inter,sans-serif;margin:0;">Hand-picked opportunities from verified business owners.</p>
      </div>
      <a href="<?= APP_URL ?>/browse/businesses" class="hp-hide-mobile" style="display:inline-block;padding:8px 24px;border-radius:8px;font-weight:600;font-size:14px;border:1.5px solid #887271;color:#1c1b1b;font-family:Inter,sans-serif;text-decoration:none;transition:filter 0.2s;flex-shrink:0;" onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'">View All</a>
    </div>
    <div style="display:flex;gap:16px;overflow-x:auto;padding-bottom:8px;">
      <?php foreach ($featured_biz as $biz): ?>
      <div style="flex-shrink:0;width:300px;background:white;border-radius:12px;border:1px solid #dbc0bf4d;border-left:4px solid #6B1D22;padding:16px;cursor:pointer;transition:box-shadow 0.3s;" onclick="location.href='<?= APP_URL ?>/business/<?= (int)$biz['id'] ?>'" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)'">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
          <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.02em;border-radius:999px;background:rgba(30,122,77,0.1);color:#1E7A4D;">Business for Sale</span>
          <?php if (!empty($biz['rating'])): ?>
          <span style="display:inline-flex;align-items:center;gap:3px;padding:3px 8px;font-size:12px;font-weight:700;border-radius:999px;background:rgba(199,122,18,0.12);color:#C77A12;"><?= e($biz['rating']) ?></span>
          <?php endif; ?>
        </div>
        <h4 style="font-size:16px;font-weight:700;margin:0 0 6px;color:#1c1b1b;font-family:Montserrat,sans-serif;"><?= e($biz['business_name']) ?></h4>
        <p style="font-size:13px;line-height:1.5;margin:0 0 12px;color:#554242;font-family:Inter,sans-serif;"><?= e(mb_substr($biz['description'] ?? '', 0, 120)) ?></p>
        <div style="display:flex;gap:8px;justify-content:space-between;align-items:center;flex-wrap:wrap;padding-top:12px;border-top:1px solid #dbc0bf4d;">
          <div><span style="font-size:11px;font-weight:600;color:#554242;display:block;">Run Rate</span><span style="font-size:14px;font-weight:700;color:#1c1b1b;"><?= money($biz['annual_revenue']) ?></span></div>
          <?php if (!empty($biz['ebitda_pct'])): ?>
          <div><span style="font-size:11px;font-weight:600;color:#554242;display:block;">EBITDA</span><span style="font-size:14px;font-weight:700;color:#1c1b1b;"><?= e($biz['ebitda_pct']) ?>%</span></div>
          <?php endif; ?>
          <?php if (!empty($biz['asking_price'])): ?>
          <div style="width:100%;padding-top:8px;"><strong style="font-size:16px;color:#7d2a2e;">Asking <?= money($biz['asking_price']) ?></strong></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="hp-show-mobile" style="text-align:center;margin-top:16px;">
      <a href="<?= APP_URL ?>/browse/businesses" style="display:inline-block;padding:8px 24px;border-radius:8px;font-weight:600;font-size:14px;border:1.5px solid #887271;color:#1c1b1b;font-family:Inter,sans-serif;text-decoration:none;">View All</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Featured Pitches -->
<?php if (!empty($featured_pitches)): ?>
<section style="padding:48px 0;background:#ffffff;">
  <div style="max-width:1200px;margin:0 auto;padding:0 24px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:24px;gap:16px;flex-wrap:wrap;">
      <div>
        <h2 style="font-size:32px;line-height:40px;font-weight:700;letter-spacing:-0.01em;color:#3b6281;font-family:Montserrat,sans-serif;margin:0 0 8px 0;">Featured Investment Opportunities</h2>
        <p style="font-size:16px;line-height:24px;color:#554242;font-family:Inter,sans-serif;margin:0;">Pre-verified entrepreneurs seeking capital for growth.</p>
      </div>
      <a href="<?= APP_URL ?>/browse/entrepreneurs" class="hp-hide-mobile" style="display:inline-block;padding:8px 24px;border-radius:8px;font-weight:600;font-size:14px;border:1.5px solid #887271;color:#1c1b1b;font-family:Inter,sans-serif;text-decoration:none;transition:filter 0.2s;flex-shrink:0;" onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'">View All</a>
    </div>
    <div style="display:flex;gap:16px;overflow-x:auto;padding-bottom:8px;">
      <?php foreach ($featured_pitches as $p): ?>
      <div style="flex-shrink:0;width:300px;background:white;border-radius:12px;border:1px solid #dbc0bf4d;border-left:4px solid #3b6281;padding:16px;cursor:pointer;transition:box-shadow 0.3s;" onclick="location.href='<?= APP_URL ?>/pitch/<?= (int)$p['id'] ?>'" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)'">
        <div style="margin-bottom:12px;">
          <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.02em;border-radius:999px;background:rgba(30,72,102,0.1);color:#1E4866;">Seeking Investment</span>
        </div>
        <h4 style="font-size:16px;font-weight:700;margin:0 0 6px;color:#1c1b1b;font-family:Montserrat,sans-serif;"><?= e($p['tagline']) ?></h4>
        <p style="font-size:13px;line-height:1.5;margin:0 0 12px;color:#554242;font-family:Inter,sans-serif;"><?= e(mb_substr($p['short_summary'] ?? $p['problem_statement'] ?? '', 0, 120)) ?></p>
        <div style="display:flex;gap:4px;margin-bottom:8px;flex-wrap:wrap;">
          <?php if (!empty($p['sector_name'])): ?>
          <span style="display:inline-flex;padding:2px 6px;font-size:11px;font-weight:600;border-radius:4px;background:#f0eded;color:#554242;"><?= e($p['sector_name']) ?></span>
          <?php endif; ?>
          <?php if (!empty($p['stage'])): ?>
          <span style="display:inline-flex;padding:2px 6px;font-size:11px;font-weight:600;border-radius:4px;background:#f0eded;color:#554242;"><?= e(ucfirst($p['stage'])) ?></span>
          <?php endif; ?>
        </div>
        <div style="display:flex;gap:8px;justify-content:space-between;align-items:center;flex-wrap:wrap;padding-top:12px;border-top:1px solid #dbc0bf4d;">
          <div><span style="font-size:11px;font-weight:600;color:#554242;display:block;">Funding</span><span style="font-size:14px;font-weight:700;color:#1c1b1b;"><?= money($p['funding_amount']) ?></span></div>
          <?php if (!empty($p['equity_offered'])): ?>
          <div><span style="font-size:11px;font-weight:600;color:#554242;display:block;">Equity</span><span style="font-size:14px;font-weight:700;color:#1c1b1b;"><?= e($p['equity_offered']) ?>%</span></div>
          <?php endif; ?>
          <div style="width:100%;padding-top:8px;"><strong style="font-size:16px;color:#7d2a2e;">Valued at <?= money($p['valuation'] ?? 0) ?></strong></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="hp-show-mobile" style="text-align:center;margin-top:16px;">
      <a href="<?= APP_URL ?>/browse/entrepreneurs" style="display:inline-block;padding:8px 24px;border-radius:8px;font-weight:600;font-size:14px;border:1.5px solid #887271;color:#1c1b1b;font-family:Inter,sans-serif;text-decoration:none;">View All</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- FAQ -->
<section style="padding:48px 0;background:#fcf9f8;">
  <div style="max-width:720px;margin:0 auto;padding:0 24px;">
    <div style="text-align:center;margin-bottom:48px;">
      <h2 style="font-size:32px;line-height:40px;font-weight:700;letter-spacing:-0.01em;margin-bottom:16px;color:#6B1D22;font-family:Montserrat,sans-serif;">Frequently Asked Questions</h2>
      <p style="font-size:16px;line-height:24px;color:#554242;font-family:Inter,sans-serif;margin:0;">Everything you need to know about <?= APP_NAME ?>.</p>
    </div>
    <?php $first = true; foreach ($faqs as $faq): ?>
    <div class="faq-item" style="background:white;border-radius:12px;padding:16px;margin-bottom:8px;border:1px solid #dbc0bf4d;<?= $first ? 'border-left:4px solid #6B1D22;' : '' ?>">
      <div class="faq-header" style="display:flex;justify-content:space-between;align-items:center;cursor:pointer;font-weight:600;font-size:16px;line-height:24px;color:#1c1b1b;font-family:Inter,sans-serif;">
        <span><?= e($faq['question']) ?></span>
        <span class="faq-icon" style="font-size:20px;color:#554242;transition:transform 0.2s;">+</span>
      </div>
      <div class="faq-answer" style="display:<?= $first ? 'block' : 'none' ?>;margin-top:12px;font-size:14px;line-height:1.7;color:#554242;font-family:Inter,sans-serif;">
        <?= e($faq['answer']) ?>
      </div>
    </div>
    <?php $first = false; endforeach; ?>
    <div style="text-align:center;margin-top:24px;">
      <a href="<?= APP_URL ?>/support" style="display:inline-block;padding:10px 24px;border-radius:8px;font-weight:600;font-size:14px;border:1.5px solid #887271;color:#1c1b1b;font-family:Inter,sans-serif;text-decoration:none;transition:filter 0.2s;" onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'">View all FAQs</a>
    </div>
  </div>
</section>

<!-- CTA -->
<section style="padding:48px 0;background:#ffffff;">
  <div style="max-width:1200px;margin:0 auto;padding:0 24px;">
    <div style="border-radius:16px;padding:48px 24px;text-align:center;background:linear-gradient(135deg,#6B1D22,#4A1317);">
      <h2 style="font-size:32px;line-height:40px;font-weight:700;letter-spacing:-0.01em;color:white;margin:0 0 16px;font-family:Montserrat,sans-serif;">Ready to grow your business?</h2>
      <p style="font-size:16px;line-height:24px;color:white;opacity:0.8;margin:0 0 24px;font-family:Inter,sans-serif;">Join <?= e($stats_businesses) ?> business owners and <?= e($stats_investors) ?> investors already on the platform.</p>
      <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
        <a href="<?= APP_URL ?>/signup" style="display:inline-block;padding:12px 32px;border-radius:8px;font-weight:600;font-size:16px;line-height:24px;color:white;background:#98202A;font-family:Inter,sans-serif;text-decoration:none;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);transition:filter 0.2s;" onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'">Get Started Free</a>
        <a href="<?= APP_URL ?>/browse/businesses" style="display:inline-block;padding:12px 32px;border-radius:8px;font-weight:600;font-size:16px;line-height:24px;font-family:Inter,sans-serif;text-decoration:none;border:1.5px solid rgba(255,255,255,0.3);color:rgba(255,255,255,0.9);transition:0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='transparent'">Browse Listings</a>
      </div>
    </div>
  </div>
</section>
</main>

<script>
document.querySelectorAll('.faq-header').forEach(header => {
  header.addEventListener('click', () => {
    const item = header.parentElement;
    const answer = item.querySelector('.faq-answer');
    const icon = item.querySelector('.faq-icon');
    const isOpen = item.style.borderLeftWidth === '4px' && item.style.borderLeftColor !== '#6B1D22';
    item.style.borderLeft = isOpen ? '4px solid #6B1D22' : '1px solid #dbc0bf4d';
    answer.style.display = isOpen ? 'block' : 'none';
    icon.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(45deg)';
  });
});

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
    }
  });
}, { threshold: 0.1 });
document.querySelectorAll('.hp-animate-in').forEach(el => observer.observe(el));
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
