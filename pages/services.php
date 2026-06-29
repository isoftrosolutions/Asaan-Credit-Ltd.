<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'Services — ' . APP_NAME_LONG;
$pageDescription = 'Explore Asaan Capital Ltd\'s comprehensive financial services: Investment Advisory, Business Valuation, Due Diligence, Capital Raising, M&A, Project Finance, and more.';
$forcePublicHeader = true;
$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Services","item":"'.APP_URL.'/services"}
  ]
}</script>';
require __DIR__ . '/../includes/header.php';
?>
<?= $breadcrumbSchema ?>
<style>
.svc-header{ background:linear-gradient(135deg, var(--color-primary) 0%, #4A1317 100%); color:#fff; padding:64px 0; }
.svc-grid{ display:grid; gap:24px; }
@media (min-width:768px){ .svc-grid{ grid-template-columns:repeat(3,1fr); } }
@media (max-width:767px){ .svc-grid{ grid-template-columns:1fr; } }
.svc-card{ background:var(--dash-card); border:1px solid var(--dash-border); border-radius:var(--dash-radius-card); padding:var(--space-5); transition:box-shadow var(--motion-base), transform var(--motion-base), border-color var(--motion-base); }
.svc-card:hover{ box-shadow:var(--dash-shadow-hover); transform:translateY(-3px); border-color:var(--dash-primary); }
.svc-icon{ display:inline-flex; align-items:center; justify-content:center; width:56px; height:56px; border-radius:16px; background:rgba(107,29,34,.1); color:var(--color-primary); margin-bottom:var(--space-3); }
.svc-icon .material-symbols-outlined{ font-size:28px; }
.svc-title{ font-family:var(--font-heading); font-weight:700; font-size:1.05rem; color:var(--dash-ink); margin-bottom:6px; }
.svc-text{ font-size:.9rem; line-height:1.6; color:var(--dash-ink-soft); }
.svc-cta{ background:var(--dash-bg); border-radius:20px; padding:48px 32px; text-align:center; }
.svc-cta h2{ font-family:var(--font-heading); font-weight:800; font-size:clamp(1.4rem,2.5vw,1.8rem); color:var(--dash-ink); margin-bottom:10px; }
.svc-cta p{ color:var(--dash-ink-soft); margin-bottom:var(--space-4); }
</style>
<main class="pub-page">

<section class="svc-header">
  <div class="pub-wrap">
    <div style="max-width:720px;">
      <span style="display:inline-block;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.7);margin-bottom:8px;">Our Services</span>
      <h1 class="pub-h1" style="color:#fff;margin:0 0 14px;">Comprehensive Financial Solutions</h1>
      <p style="font-size:1.05rem;line-height:1.7;opacity:.88;">From investment advisory to capital raising — Asaan Capital Ltd delivers end-to-end financial services tailored to businesses, investors, and institutions across Nepal.</p>
    </div>
  </div>
</section>

<section class="pub-section">
  <div class="pub-wrap">
    <div class="svc-grid">
      <?php $services = [
        ['trending_up', 'Investment Advisory', 'Strategic investment guidance and portfolio management for individuals, families, and institutions. We analyze market opportunities, assess risk, and build customized investment strategies that align with your financial goals.'],
        ['account_balance', 'Business Valuation', 'Accurate, data-driven business valuations for M&A, fundraising, tax planning, and strategic decision-making. Our team uses multiple methodologies including DCF, market comparables, and asset-based approaches.'],
        ['search_insights', 'Due Diligence', 'Comprehensive financial, legal, and operational due diligence services. We help buyers, sellers, and investors identify risks, verify assumptions, and make informed transaction decisions.'],
        ['finance', 'Financial Consulting', 'Expert financial analysis, planning, and strategic guidance. We help businesses optimize their financial structure, improve profitability, and plan for sustainable growth.'],
        ['payments', 'Capital Raising', 'Equity, debt, and hybrid capital raising solutions. Through our extensive network of investors, financial institutions, and funding partners, we connect businesses with the right capital sources.'],
        ['merge_type', 'M&A Advisory', 'End-to-end merger and acquisition advisory services including target identification, valuation, negotiation support, deal structuring, and post-merger integration planning.'],
        ['savings', 'Project Finance', 'Structured finance solutions for infrastructure, energy, manufacturing, and development projects. We arrange financing through a combination of debt, equity, and mezzanine instruments.'],
        ['account_balance', 'Loan Syndication', 'Arranging and structuring syndicated loans for large-scale business requirements. We coordinate with multiple lenders to create optimal financing packages for our clients.'],
        ['description', 'Business Planning', 'Professional business plans, financial models, and investor pitch decks. We help entrepreneurs and businesses present their vision with clarity and impact to attract funding.'],
        ['business_center', 'Corporate Advisory', 'Strategic corporate finance advice including capital structure optimization, restructuring, growth planning, and exit strategy development for businesses at every stage.'],
      ]; foreach ($services as $s): ?>
      <div class="svc-card">
        <div class="svc-icon"><span class="material-symbols-outlined"><?= $s[0] ?></span></div>
        <div class="svc-title"><?= $s[1] ?></div>
        <div class="svc-text"><?= $s[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="pub-section tint">
  <div class="pub-wrap">
    <div class="svc-cta">
      <h2>Need a Custom Solution?</h2>
      <p style="max-width:500px;margin:0 auto var(--space-4);">Every business is unique. Contact us for a free consultation to discuss your specific financial services needs.</p>
      <a href="<?= APP_URL ?>/contact" class="btn btn-primary" style="padding:12px 28px;background:var(--color-primary);color:#fff;border-radius:10px;text-decoration:none;font-weight:600;font-size:1rem;">Get in Touch</a>
    </div>
  </div>
</section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
