<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'Our Story — ' . APP_NAME;
require __DIR__ . '/../includes/header.php';
?>
<main class="main-content" style="padding-top:0;">

<div class="breadcrumbs container" style="padding-top:1rem;padding-bottom:1rem;font-size:0.85rem;color:var(--secondary-text);">
  <a href="<?= APP_URL ?>">Home</a> <span style="margin:0 0.5rem;">/</span>
  <span>Our Story</span>
</div>

<div class="container" style="max-width:900px; padding-bottom:4rem;">
  <h1 style="font-size:3rem; margin-bottom:0.5rem;">Built for SMEs.<br>Designed for trust.</h1>

  <p style="font-size:1.1rem;line-height:1.8;color:var(--secondary-text);">In the closed community of investment banking, small and medium enterprises have traditionally been left out. <?= APP_NAME ?> is changing that — by building the first dedicated platform that brings together verified business owners, franchise brands, investors, buyers, lenders, and advisors in one trusted marketplace.</p>

  <div style="margin:3rem 0; display:grid; grid-template-columns:repeat(auto-fit, minmax(280px,1fr)); gap:2rem;">
    <div class="card">
      <h4 style="margin-bottom:0.5rem;">Why we exist</h4>
      <p style="margin:0;color:var(--secondary-text);">SMEs are the backbone of the economy — 460 million businesses globally. Yet they lack access to the M&A and fundraising infrastructure that large corporates take for granted. We fix that.</p>
    </div>
    <div class="card">
      <h4 style="margin-bottom:0.5rem;">Our promise</h4>
      <p style="margin:0;color:var(--secondary-text);">Every single profile on this platform — business, investor, advisor, or franchise — has been pre-screened by our analysts. No bots. No fake listings. No time wasters.</p>
    </div>
    <div class="card">
      <h4 style="margin-bottom:0.5rem;">Our reach</h4>
      <p style="margin:0;color:var(--secondary-text);">67,500+ pre-screened businesses, 44,000+ investors, 900+ industries, 170+ countries. Investment range from NPR 20 lakh to NPR 800 crore.</p>
    </div>
  </div>

  <h3>Who Uses <?= APP_NAME ?></h3>
  <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:3rem;">
    <?php $personas = ['Businesses For Sale', 'Companies Seeking Capital', 'Franchise Brands', 'M&amp;A Advisors', 'Business Brokers', 'Private Investors', 'Corporate Acquirers', 'Lenders', 'PE / VC Firms', 'Family Offices', 'Deal Professionals']; ?>
    <?php foreach ($personas as $p): ?>
    <span style="background:var(--surface);border:1px solid var(--surface-container-high);border-radius:999px;padding:8px 16px;font-size:0.85rem;font-weight:500;"><?= $p ?></span>
    <?php endforeach; ?>
  </div>

  <h3>Our Journey</h3>
  <ul style="list-style:none;padding:0;margin-bottom:2rem;">
    <?php $milestones = [['2015', 'Company founded as SMERGERS Online Services Pvt Ltd'], ['2016', 'Platform beta launched with 500 businesses'], ['2018', 'Crossed 10,000 registered members'], ['2020', 'Expanded to 50+ countries, launched franchise vertical'], ['2022', '50,000+ businesses listed, introduced advisor network'], ['2024', '110,000+ total members, 170+ countries'], ['2026', 'Relaunched as ' . APP_NAME . ' with enhanced features']]; ?>
    <?php foreach ($milestones as $m): ?>
    <li style="padding:0.5rem 0;border-bottom:1px solid var(--surface-container-high);display:flex;gap:1rem;">
      <span style="font-weight:700;color:var(--brand-red);min-width:60px;"><?= e($m[0]) ?></span>
      <span style="color:var(--secondary-text);"><?= $m[1] ?></span>
    </li>
    <?php endforeach; ?>
  </ul>

  <h3>In the News</h3>
  <div style="display:grid;gap:0.75rem;margin-bottom:3rem;">
    <?php $articles = [['Economic Times', 'How InvestMatch is democratizing SME M&A in South Asia', 'Mar 2026'], ['YourStory', 'This platform connects SME owners with investors across 170 countries', 'Jan 2026'], ['Business Today', 'The rise of online M&A marketplaces for small businesses', 'Nov 2025']]; ?>
    <?php foreach ($articles as $a): ?>
    <div class="card card-compact" style="cursor:pointer;background:var(--surface);border-radius:16px;padding:1rem;border:1px solid var(--surface-container-high);">
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
          <strong><?= e($a[0]) ?></strong>
          <div style="font-size:0.85rem;color:var(--secondary-text);"><?= e($a[1]) ?></div>
        </div>
        <span style="font-size:0.75rem;color:var(--secondary-text);flex-shrink:0;margin-left:1rem;"><?= e($a[2]) ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <h3>The Team</h3>
  <p style="color:var(--secondary-text);">Developed by <strong>iSoftro Solutions</strong> in close collaboration with Nepal's startup ecosystem leaders, legal experts, and active investors.</p>

  <div style="margin-top:2rem; padding:2rem; background:var(--surface-container); border-radius:2rem;">
    <strong>Contact</strong><br>
    SMERGERS Online Services Pvt Ltd<br>
    Helios Business Park, Bangalore 560103<br>
    CIN: U74900KA2015PTC082128<br>
    Email: <a href="mailto:hello@investmatch.com.np" style="color:var(--brand-red);">hello@investmatch.com.np</a>
  </div>
</div>

</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
