<?php
require __DIR__ . '/../config/bootstrap.php';

// Fetch homepage contents
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

// Featured businesses
$featured_biz = db()->query("SELECT * FROM businesses WHERE is_published=1 AND is_featured=1 ORDER BY rating DESC LIMIT 6")->fetchAll();

// Featured pitches
$featured_pitches = db()->query("SELECT p.*, s.name as sector_name FROM pitches p LEFT JOIN sectors s ON p.sector_id = s.id WHERE p.is_published=1 AND p.is_featured=1 ORDER BY p.id DESC LIMIT 6")->fetchAll();

$pageTitle = APP_NAME . ' — Connect with Investors. Sell or Grow Your Business Faster.';
require __DIR__ . '/../includes/header.php';
?>
<main class="main-content" style="padding-top:0;">

<!-- ===== HERO SECTION ===== -->
<header class="hero-premium">
  <div class="gradient-blob" style="top:-20%;right:-10%;width:600px;height:600px;background:radial-gradient(circle,rgba(196,30,58,0.08),transparent 70%);"></div>
  <div class="gradient-blob" style="bottom:-30%;left:-5%;width:500px;height:500px;background:radial-gradient(circle,rgba(30,58,138,0.05),transparent 70%);"></div>
  <div class="container" style="position:relative;z-index:2;">
    <div style="display:flex;align-items:center;gap:4rem;justify-content:space-between;">

      <!-- Left Content -->
      <div class="hero-content-left animate-fade-up">
        <div class="hero-trust-badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
          Trusted by <?= e($stats_businesses) ?> business owners, investors &amp; advisors
        </div>

        <h1><?= $hero_title ?></h1>

        <p class="hero-subtitle"><?= e($hero_subtitle) ?></p>

        <div class="hero-actions">
          <a href="<?= APP_URL ?>/signup" class="btn btn-primary btn-lg">Get Started Free</a>
          <a href="<?= APP_URL ?>/browse/businesses" class="btn btn-outline btn-lg">
            Browse Opportunities
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
        </div>

        <div class="hero-features">
          <div class="hero-feature">
            <span class="check-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>
            Verified investors &amp; buyers
          </div>
          <div class="hero-feature">
            <span class="check-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>
            Pre-screened business listings
          </div>
          <div class="hero-feature">
            <span class="check-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>
            Expert advisory support
          </div>
        </div>
      </div>

      <!-- Right: Dashboard Mockup -->
      <div class="dashboard-mock-premium">
        <div class="dashboard-mock-inner">
          <div class="dashboard-mock-sidebar">
            <div class="dashboard-mock-sidebar-item active">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
              Dashboard
            </div>
            <div class="dashboard-mock-sidebar-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              Discover
            </div>
            <div class="dashboard-mock-sidebar-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
              Messages
            </div>
            <div class="dashboard-mock-sidebar-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2 3 3 0 003 3 3 3 0 003-3 2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/></svg>
              Matches
            </div>
            <div style="flex:1;"></div>
            <div class="dashboard-mock-sidebar-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              Settings
            </div>
          </div>

          <div class="dashboard-mock-main">
            <div class="dashboard-mock-topbar">
              <div class="dashboard-mock-topbar-title">Deal Pipeline</div>
              <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:0.65rem;color:#16a34a;font-weight:600;display:flex;align-items:center;gap:4px;">
                  <span style="width:6px;height:6px;border-radius:50%;background:#16a34a;display:inline-block;"></span>
                  Live
                </span>
                <div class="dashboard-mock-avatar">AK</div>
              </div>
            </div>

            <div class="dashboard-mock-grid">
              <div class="dashboard-mock-card dashboard-mock-card-accent">
                <div class="dashboard-mock-card-label">Match Score</div>
                <div class="dashboard-mock-card-value">94%</div>
                <div class="dashboard-mock-card-change">↑ 12% this week</div>
              </div>
              <div class="dashboard-mock-card">
                <div class="dashboard-mock-card-label">Active Deals</div>
                <div class="dashboard-mock-card-value">14</div>
                <div class="dashboard-mock-card-change">3 new this week</div>
              </div>
            </div>

            <div class="dashboard-mock-list-title">Recommended Matches</div>
            <div class="dashboard-mock-list">
              <div class="dashboard-mock-list-item">
                <div class="dashboard-mock-list-item-left"><span class="dashboard-mock-list-dot" style="background:#16a34a;"></span><span class="dashboard-mock-list-name">Enterprise Software Co.</span></div>
                <span class="dashboard-mock-list-price">NPR 12 Cr</span>
              </div>
              <div class="dashboard-mock-list-item">
                <div class="dashboard-mock-list-item-left"><span class="dashboard-mock-list-dot" style="background:#ca8a04;"></span><span class="dashboard-mock-list-name">Hotel Equity Stake</span></div>
                <span class="dashboard-mock-list-price">NPR 3 Cr</span>
              </div>
              <div class="dashboard-mock-list-item">
                <div class="dashboard-mock-list-item-left"><span class="dashboard-mock-list-dot" style="background:#2563eb;"></span><span class="dashboard-mock-list-name">Retail Pharmacy Chain</span></div>
                <span class="dashboard-mock-list-price">NPR 2.5 Cr</span>
              </div>
            </div>

            <div class="dashboard-mock-footer">
              <div class="dashboard-mock-footer-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                142 views this week
              </div>
              <div class="dashboard-mock-footer-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                12 proposals
              </div>
              <div class="dashboard-mock-footer-item">
                <svg viewBox="0 0 24 24" fill="currentColor" width="12" height="12"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                9.3 rating
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</header>

<!-- ===== TRUST STRIP ===== -->
<div class="trust-strip">
  <div class="container">
    <div class="trust-strip-inner">
      <div class="trust-strip-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
        Pre-approved profiles only
      </div>
      <div class="trust-strip-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        Confidential until mutual match
      </div>
      <div class="trust-strip-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Free valuation tools
      </div>
      <div class="trust-strip-item" style="font-weight:600;color:var(--dark);">
        Featured in Economic Times, YourStory, Business Today
      </div>
    </div>
  </div>
</div>

<!-- ===== FEATURE CARDS ===== -->
<section class="section-premium">
  <div class="container">
    <div class="section-premium-header">
      <h2>Three ways to get started</h2>
      <p>Choose your path — we'll match you with the right opportunities.</p>
    </div>
    <div class="feature-cards-grid">
      <a href="<?= APP_URL ?>/signup" class="feature-card-premium">
        <div class="feature-card-icon" style="background:rgba(196,30,58,0.1);color:var(--brand-red);">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        </div>
        <h3>Sell Your Business</h3>
        <p>Get matched with verified investors &amp; buyers. List in 10 minutes with our guided intake process.</p>
        <span class="feature-card-link">
          List your business
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </span>
      </a>
      <a href="<?= APP_URL ?>/browse/businesses" class="feature-card-premium">
        <div class="feature-card-icon" style="background:rgba(30,58,138,0.1);color:var(--brand-blue);">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <h3>Find Investment Opportunities</h3>
        <p>Browse 23,000+ vetted businesses. Smart matches based on your investment criteria and preferences.</p>
        <span class="feature-card-link">
          Browse opportunities
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </span>
      </a>
      <a href="<?= APP_URL ?>/browse/franchises" class="feature-card-premium">
        <div class="feature-card-icon" style="background:rgba(245,158,11,0.1);color:#B45309;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
        <h3>Start a Franchise</h3>
        <p>Expand your brand through franchising. Connect with qualified franchisees across multiple regions.</p>
        <span class="feature-card-link">
          Explore franchises
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </span>
      </a>
    </div>
  </div>
</section>

<hr class="divider-subtle">

<!-- ===== STATS SECTION ===== -->
<section class="section-premium">
  <div class="container">
    <div class="stats-premium">
      <div>
        <div class="stat-premium-value"><?= e($stats_businesses) ?></div>
        <div class="stat-premium-label">Businesses Listed</div>
      </div>
      <div>
        <div class="stat-premium-value"><?= e($stats_investors) ?></div>
        <div class="stat-premium-label">Verified Investors</div>
      </div>
      <div>
        <div class="stat-premium-value"><?= e($stats_matches) ?></div>
        <div class="stat-premium-label">Successful Matches</div>
      </div>
      <div>
        <div class="stat-premium-value"><?= e($stats_deal_value) ?></div>
        <div class="stat-premium-label">Deal Value Closed</div>
      </div>
    </div>
  </div>
</section>

<hr class="divider-subtle">

<!-- ===== FEATURED BUSINESSES ===== -->
<?php if (!empty($featured_biz)): ?>
<section class="section-premium">
  <div class="container">
    <div class="section-header-action">
      <div class="section-premium-header">
        <h2>Featured Businesses</h2>
        <p>Hand-picked opportunities from verified business owners.</p>
      </div>
      <a href="<?= APP_URL ?>/browse/businesses" class="btn btn-outline" style="flex-shrink:0;">
        View All
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>

    <div class="carousel-premium">
      <?php foreach ($featured_biz as $biz): ?>
      <div class="card-premium" onclick="location.href='<?= APP_URL ?>/business/<?= (int)$biz['id'] ?>'" style="flex:0 0 300px;">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:0.75rem;">
          <span class="badge-premium badge-sale">Business for Sale</span>
          <?php if (!empty($biz['rating'])): ?>
          <span class="rating-badge" style="font-size:0.7rem;padding:2px 8px;"><?= e($biz['rating']) ?></span>
          <?php endif; ?>
        </div>
        <h4 style="margin:0 0 0.35rem;font-size:1rem;font-weight:700;color:var(--dark);"><?= e($biz['business_name']) ?></h4>
        <p style="font-size:0.82rem;color:var(--secondary-text);margin:0 0 0.75rem;line-height:1.5;"><?= e(mb_substr($biz['description'] ?? '', 0, 120)) ?></p>
        <div style="display:flex;gap:4px;margin-bottom:0.75rem;">
          <span class="badge" style="background:var(--bg-subtle);color:var(--secondary-text);padding:2px 8px;font-size:0.68rem;font-weight:600;">
            <svg viewBox="0 0 24 24" fill="none" stroke="#166534" stroke-width="2.5" width="10" height="10"><path d="M5 13l4 4L19 7"/></svg>
            Email
          </span>
          <span class="badge" style="background:var(--bg-subtle);color:var(--secondary-text);padding:2px 8px;font-size:0.68rem;font-weight:600;">
            <svg viewBox="0 0 24 24" fill="none" stroke="#166534" stroke-width="2.5" width="10" height="10"><path d="M5 13l4 4L19 7"/></svg>
            Phone
          </span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;padding-top:0.75rem;border-top:1px solid var(--surface-container-high);">
          <div><span style="font-size:0.68rem;font-weight:600;color:var(--secondary-text);display:block;">Run Rate</span><span style="font-size:0.85rem;font-weight:700;color:var(--dark);"><?= money($biz['annual_revenue']) ?></span></div>
          <?php if (!empty($biz['ebitda_pct'])): ?>
          <div><span style="font-size:0.68rem;font-weight:600;color:var(--secondary-text);display:block;">EBITDA</span><span style="font-size:0.85rem;font-weight:700;color:var(--dark);"><?= e($biz['ebitda_pct']) ?>%</span></div>
          <?php endif; ?>
          <?php if (!empty($biz['asking_price'])): ?>
          <div style="width:100%;padding-top:0.5rem;"><strong style="font-size:1rem;color:var(--brand-red);">Asking <?= money($biz['asking_price']) ?></strong></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== FEATURED PITCHES ===== -->
<?php if (!empty($featured_pitches)): ?>
<section class="section-premium" style="background:var(--bg-page);">
  <div class="container">
    <div class="section-header-action">
      <div class="section-premium-header">
        <h2>Featured Investment Opportunities</h2>
        <p>Pre-verified entrepreneurs seeking capital for growth.</p>
      </div>
      <a href="<?= APP_URL ?>/browse/entrepreneurs" class="btn btn-outline" style="flex-shrink:0;">
        View All
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>

    <div class="carousel-premium">
      <?php foreach ($featured_pitches as $p): ?>
      <div class="card-premium" onclick="location.href='<?= APP_URL ?>/pitch/<?= (int)$p['id'] ?>'" style="flex:0 0 300px;">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:0.75rem;">
          <span class="badge-premium badge-investment">Seeking Investment</span>
        </div>
        <h4 style="margin:0 0 0.35rem;font-size:1rem;font-weight:700;color:var(--dark);"><?= e($p['tagline']) ?></h4>
        <p style="font-size:0.82rem;color:var(--secondary-text);margin:0 0 0.75rem;line-height:1.5;"><?= e(mb_substr($p['short_summary'] ?? $p['problem_statement'] ?? '', 0, 120)) ?></p>
        <div style="display:flex;gap:4px;margin-bottom:0.5rem;flex-wrap:wrap;">
          <?php if (!empty($p['sector_name'])): ?>
          <span class="badge" style="background:var(--bg-subtle);color:var(--secondary-text);padding:2px 8px;font-size:0.68rem;font-weight:600;"><?= e($p['sector_name']) ?></span>
          <?php endif; ?>
          <?php if (!empty($p['stage'])): ?>
          <span class="badge" style="background:var(--bg-subtle);color:var(--secondary-text);padding:2px 8px;font-size:0.68rem;font-weight:600;"><?= e(ucfirst($p['stage'])) ?></span>
          <?php endif; ?>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;padding-top:0.75rem;border-top:1px solid var(--surface-container-high);">
          <div><span style="font-size:0.68rem;font-weight:600;color:var(--secondary-text);display:block;">Funding</span><span style="font-size:0.85rem;font-weight:700;color:var(--dark);"><?= money($p['funding_amount']) ?></span></div>
          <?php if (!empty($p['equity_offered'])): ?>
          <div><span style="font-size:0.68rem;font-weight:600;color:var(--secondary-text);display:block;">Equity</span><span style="font-size:0.85rem;font-weight:700;color:var(--dark);"><?= e($p['equity_offered']) ?>%</span></div>
          <?php endif; ?>
          <div style="width:100%;padding-top:0.5rem;"><strong style="font-size:1rem;color:var(--brand-red);">Valued at <?= money($p['valuation'] ?? 0) ?></strong></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== HOW IT WORKS ===== -->
<section class="section-premium">
  <div class="container">
    <div class="section-premium-header">
      <h2>How It Works</h2>
      <p>From discovery to deal closure — a seamless four-step process.</p>
    </div>
    <div class="timeline-premium" style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem;">
      <div class="timeline-step-premium" style="text-align:center;">
        <div style="width:48px;height:48px;border-radius:50%;background:var(--brand-red);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.2rem;margin:0 auto 1rem;">1</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Create Your Profile</div>
        <div style="font-size:0.85rem;color:var(--secondary-text);line-height:1.6;">Sign up in minutes. Tell us about your business or investment goals.</div>
      </div>
      <div class="timeline-step-premium" style="text-align:center;">
        <div style="width:48px;height:48px;border-radius:50%;background:var(--brand-red);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.2rem;margin:0 auto 1rem;">2</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Get Matched</div>
        <div style="font-size:0.85rem;color:var(--secondary-text);line-height:1.6;">Our AI matches you with the right opportunities or potential buyers.</div>
      </div>
      <div class="timeline-step-premium" style="text-align:center;">
        <div style="width:48px;height:48px;border-radius:50%;background:var(--brand-red);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.2rem;margin:0 auto 1rem;">3</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Connect Securely</div>
        <div style="font-size:0.85rem;color:var(--secondary-text);line-height:1.6;">Mutual interest unlocks contact details. Confidential until you're ready.</div>
      </div>
      <div class="timeline-step-premium" style="text-align:center;">
        <div style="width:48px;height:48px;border-radius:50%;background:var(--brand-red);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.2rem;margin:0 auto 1rem;">4</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Close the Deal</div>
        <div style="font-size:0.85rem;color:var(--secondary-text);line-height:1.6;">Expert advisors guide you through due diligence to successful closing.</div>
      </div>
    </div>
  </div>
</section>

<hr class="divider-subtle">

<!-- ===== TESTIMONIAL ===== -->
<section class="section-premium">
  <div class="container">
    <div class="section-premium-header">
      <h2>Trusted by business owners &amp; investors</h2>
      <p>Real stories from real users who found their perfect match.</p>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;max-width:800px;margin:0 auto;">
      <div class="testimonial-premium" style="background:var(--surface);border-radius:20px;padding:1.5rem;border:1px solid var(--surface-container-high);">
        <div style="font-weight:700;">Rajesh Sharma</div>
        <div style="font-size:0.85rem;color:var(--secondary-text);">Business Owner, Kathmandu</div>
        <div style="margin-top:0.75rem;color:var(--dark);font-style:italic;">"Sold my manufacturing business to the 4th buyer introduced. Time taken: 3 months. The platform made it seamless."</div>
        <div style="margin-top:0.75rem;">
          <span class="badge-premium badge-sale">Deal Closed: NPR 8.5 Cr</span>
        </div>
      </div>
      <div class="testimonial-premium" style="background:var(--surface);border-radius:20px;padding:1.5rem;border:1px solid var(--surface-container-high);">
        <div style="font-weight:700;">Anita Gurung</div>
        <div style="font-size:0.85rem;color:var(--secondary-text);">Angel Investor, Pokhara</div>
        <div style="margin-top:0.75rem;color:var(--dark);font-style:italic;">"Found two promising startups within my first month. The verification process gave me confidence to invest."</div>
        <div style="margin-top:0.75rem;">
          <span class="badge-premium badge-investment">2 Deals Closed</span>
        </div>
      </div>
    </div>
  </div>
</section>

<hr class="divider-subtle">

<!-- ===== FAQ ===== -->
<section class="section-premium">
  <div class="container" style="max-width:720px;">
    <div class="section-premium-header">
      <h2>Frequently Asked Questions</h2>
      <p>Everything you need to know about <?= APP_NAME ?>.</p>
    </div>

    <?php
    $faqs = db()->query("SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order LIMIT 4")->fetchAll();
    $first = true;
    foreach ($faqs as $faq):
    ?>
    <div class="faq-premium<?= $first ? ' open' : '' ?>" style="background:var(--surface);border-radius:16px;padding:1rem 1.25rem;margin-bottom:0.5rem;border:1px solid var(--surface-container-high);">
      <div class="faq-premium-header" onclick="this.parentElement.classList.toggle('open')" style="display:flex;justify-content:space-between;align-items:center;cursor:pointer;font-weight:600;">
        <span><?= e($faq['question']) ?></span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" style="flex-shrink:0;transition:transform 0.2s;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      </div>
      <div class="faq-premium-body" style="display:<?= $first ? 'block' : 'none' ?>;margin-top:0.75rem;font-size:0.9rem;color:var(--secondary-text);line-height:1.7;">
        <?= e($faq['answer']) ?>
      </div>
    </div>
    <?php $first = false; endforeach; ?>

    <div style="text-align:center;margin-top:2rem;">
      <a href="<?= APP_URL ?>/support" class="btn btn-outline">
        View all FAQs
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
  </div>
</section>

<!-- ===== FINAL CTA ===== -->
<section class="section-premium" style="padding:3rem 0;">
  <div class="container">
    <div style="background:linear-gradient(135deg,var(--dark),#162032);border-radius:28px;padding:3rem;text-align:center;">
      <h2 style="color:#fff;margin-bottom:0.5rem;">Ready to grow your business?</h2>
      <p style="color:rgba(255,255,255,0.7);margin-bottom:1.5rem;">Join <?= e($stats_businesses) ?> business owners and <?= e($stats_investors) ?> investors already on the platform.</p>
      <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
        <a href="<?= APP_URL ?>/signup" class="btn btn-primary btn-lg">Get Started Free</a>
        <a href="<?= APP_URL ?>/browse/businesses" class="btn btn-outline btn-lg" style="border-color:rgba(255,255,255,0.3);color:rgba(255,255,255,0.9);">Browse Listings</a>
      </div>
    </div>
  </div>
</section>

<!-- ===== POPULAR CATEGORIES ===== -->
<section class="section-premium" style="padding:2rem 0;background:var(--bg-page);">
  <div class="container">
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;justify-content:center;">
      <span class="tag" style="cursor:pointer;background:var(--surface);border:1px solid var(--surface-container-high);font-size:0.8rem;padding:6px 16px;border-radius:999px;transition:all 0.2s;" onclick="location.href='<?= APP_URL ?>/browse/businesses'">Restaurants for Sale</span>
      <span class="tag" style="cursor:pointer;background:var(--surface);border:1px solid var(--surface-container-high);font-size:0.8rem;padding:6px 16px;border-radius:999px;transition:all 0.2s;" onclick="location.href='<?= APP_URL ?>/browse/businesses'">Tech Companies</span>
      <span class="tag" style="cursor:pointer;background:var(--surface);border:1px solid var(--surface-container-high);font-size:0.8rem;padding:6px 16px;border-radius:999px;transition:all 0.2s;" onclick="location.href='<?= APP_URL ?>/browse/businesses'">Hotels &amp; Resorts</span>
      <span class="tag" style="cursor:pointer;background:var(--surface);border:1px solid var(--surface-container-high);font-size:0.8rem;padding:6px 16px;border-radius:999px;transition:all 0.2s;" onclick="location.href='<?= APP_URL ?>/browse/businesses'">Manufacturing</span>
      <span class="tag" style="cursor:pointer;background:var(--surface);border:1px solid var(--surface-container-high);font-size:0.8rem;padding:6px 16px;border-radius:999px;transition:all 0.2s;" onclick="location.href='<?= APP_URL ?>/browse/businesses'">Retail Stores</span>
      <span class="tag" style="cursor:pointer;background:var(--surface);border:1px solid var(--surface-container-high);font-size:0.8rem;padding:6px 16px;border-radius:999px;transition:all 0.2s;" onclick="location.href='<?= APP_URL ?>/browse/businesses'">Healthcare</span>
      <span class="tag" style="cursor:pointer;background:var(--surface);border:1px solid var(--surface-container-high);font-size:0.8rem;padding:6px 16px;border-radius:999px;transition:all 0.2s;" onclick="location.href='<?= APP_URL ?>/browse/businesses'">Franchise Brands</span>
      <span class="tag" style="cursor:pointer;background:var(--surface);border:1px solid var(--surface-container-high);font-size:0.8rem;padding:6px 16px;border-radius:999px;transition:all 0.2s;" onclick="location.href='<?= APP_URL ?>/browse/businesses'">E-commerce</span>
    </div>
  </div>
</section>

</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
