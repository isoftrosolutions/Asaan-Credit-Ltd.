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
$featured_pitches = db()->query("SELECT p.*, s.name as sector_name FROM pitches p LEFT JOIN sectors s ON p.sector_id = s.id WHERE p.is_published=1 ORDER BY p.id DESC LIMIT 6")->fetchAll();
$faqs = db()->query("SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order LIMIT 4")->fetchAll();

$pageTitle = APP_NAME_LONG;
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
</style>
<main class="pub-page">
<!-- Hero Section -->
<section class="hp-hero" style="position:relative;overflow:hidden;display:flex;align-items:center;">
  <div style="position:absolute;inset:0;z-index:0;background:linear-gradient(135deg, #00263f 0%, #013a5e 55%, #00263f 100%);"></div>
  <img src="https://picsum.photos/seed/asaan-capital-nepal/1600/900" alt="" aria-hidden="true" style="position:absolute;inset:0;z-index:1;width:100%;height:100%;object-fit:cover;mix-blend-mode:overlay;opacity:0.15;">
  <div style="position:absolute;inset:0;z-index:2;background:radial-gradient(120% 90% at 85% 15%, rgba(152,32,42,0.22) 0%, rgba(152,32,42,0) 55%);"></div>
  <div class="hp-hero-inner pub-wrap" style="width:100%;position:relative;z-index:10;">
    <div style="max-width:580px;">
      <h1 class="hp-hero-title pub-h1" style="color:#fff;margin-bottom:20px;">
        <?= $hero_title ?>
      </h1>
      <p class="pub-lead" style="color:#fff;opacity:0.92;max-width:560px;">
        <?= e($hero_subtitle) ?>
      </p>
      <div class="hp-hero-actions pub-cta-actions" style="justify-content:flex-start;margin-top:28px;">
        <a href="<?= APP_URL ?>/onboarding" class="btn btn-primary">Get Started</a>
        <a href="<?= APP_URL ?>/browse/businesses" class="btn btn-outline" style="border-color:rgba(255,255,255,0.5);color:#fff;background:transparent;">Browse Businesses</a>
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
      <div class="pub-feature">
        <span class="pub-feature-ico"><i class="fas fa-check-circle"></i></span>
        <h3 class="pub-feature-title">Pre-approved</h3>
        <p class="pub-feature-text">Every business, investor and advisor profile is pre-screened by our analysts.</p>
      </div>
      <div class="pub-feature">
        <span class="pub-feature-ico"><i class="fas fa-lock"></i></span>
        <h3 class="pub-feature-title">Confidential</h3>
        <p class="pub-feature-text">Your contact details stay private until there is a mutual match.</p>
      </div>
      <div class="pub-feature">
        <span class="pub-feature-ico"><i class="fas fa-chart-line"></i></span>
        <h3 class="pub-feature-title">Fair Valuation</h3>
        <p class="pub-feature-text">Benchmark your business against comparable private companies in Nepal.</p>
      </div>
      <div class="pub-feature">
        <span class="pub-feature-ico"><i class="fas fa-globe"></i></span>
        <h3 class="pub-feature-title">Global Network</h3>
        <p class="pub-feature-text">Connect with investors, buyers and partners across Nepal and beyond.</p>
      </div>
    </div>
  </div>
</section>

<!-- Businesses for Sale -->
<?php if (!empty($featured_biz)): ?>
<section class="pub-section tint">
  <div class="pub-wrap">
    <div class="hp-biz-split" style="display:grid;gap:32px;align-items:center;">
      <div class="hp-biz-cards" style="display:grid;gap:16px;">
        <?php foreach (array_slice($featured_biz, 0, 2) as $biz): ?>
        <div class="pub-card card-accent-bar" style="cursor:pointer;" onclick="location.href='<?= APP_URL ?>/business/<?= (int)$biz['id'] ?>'">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
            <span class="pub-badge success">Business for Sale</span>
            <?php if (!empty($biz['rating'])): ?>
            <span class="pub-badge warning"><?= e($biz['rating']) ?></span>
            <?php endif; ?>
          </div>
          <h4 class="pub-card-title" style="margin:0 0 6px;"><?= e($biz['business_name']) ?></h4>
          <p class="pub-text" style="margin:0 0 12px;"><?= e(mb_substr($biz['description'] ?? '', 0, 120)) ?></p>
          <div style="display:flex;gap:8px;justify-content:space-between;align-items:center;flex-wrap:wrap;padding-top:12px;border-top:1px solid var(--dash-border);">
            <div><span class="pub-stat-label" style="display:block;">Run Rate</span><span class="pub-stat-value"><?= money($biz['annual_revenue']) ?></span></div>
            <?php if (!empty($biz['ebitda_pct'])): ?>
            <div><span class="pub-stat-label" style="display:block;">EBITDA</span><span class="pub-stat-value"><?= e($biz['ebitda_pct']) ?>%</span></div>
            <?php endif; ?>
            <?php if (!empty($biz['asking_price'])): ?>
            <div style="width:100%;padding-top:8px;"><strong style="font-size:16px;color:var(--color-primary-vivid);">Asking <?= money($biz['asking_price']) ?></strong></div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div>
        <h2 class="pub-h2" style="color:var(--color-primary);margin:0 0 16px;">Businesses for Sale on <?= APP_NAME_LONG ?></h2>
        <p class="pub-lead" style="margin:0 0 24px;">Explore pre-screened businesses for sale across diverse sectors in Nepal. Find ventures looking for a full sale, raising capital, or seeking a business loan.</p>
        <a href="<?= APP_URL ?>/browse/businesses" class="btn btn-primary btn-lg">Browse Businesses</a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

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
<?php if (!empty($featured_pitches)): ?>
<section class="pub-section tint">
  <div class="pub-wrap">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:24px;gap:16px;flex-wrap:wrap;">
      <div>
        <h2 class="pub-h2" style="color:var(--color-primary);margin:0 0 8px 0;">Featured Investment Opportunities</h2>
        <p class="pub-text" style="margin:0;">Pre-verified entrepreneurs seeking capital for growth.</p>
      </div>
      <a href="<?= APP_URL ?>/browse/entrepreneurs" class="btn btn-ghost btn-sm hp-hide-mobile" style="flex-shrink:0;">View All</a>
    </div>
    <div style="display:flex;gap:16px;overflow-x:auto;padding-bottom:8px;">
      <?php foreach ($featured_pitches as $p): ?>
      <div class="pub-card card-accent-bar-navy" style="flex-shrink:0;width:300px;cursor:pointer;" onclick="location.href='<?= APP_URL ?>/pitch/<?= (int)$p['id'] ?>'">
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
        <?= e($faq['answer']) ?>
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

<script>
document.querySelectorAll('.faq-header').forEach(header => {
  header.addEventListener('click', () => {
    header.parentElement.classList.toggle('open');
  });
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
