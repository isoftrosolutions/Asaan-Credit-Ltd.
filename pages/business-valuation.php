<?php
require __DIR__ . '/../config/bootstrap.php';

$pageTitle = 'Business Valuation Calculator — ' . APP_NAME;
$pageDescription = 'Free online business valuation calculator. Estimate the value of your SME in Nepal using trading and transaction comparables.';

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"How To","item":"'.APP_URL.'/how-it-works"},
    {"@type": "ListItem","position":3,"name":"Business Valuation Calculator","item":"'.APP_URL.'/business-valuation"}
  ]
}</script>';
$forcePublicHeader = true;
require __DIR__ . '/../includes/header.php';
?>
<?= $breadcrumbSchema ?>
<style>
@media (min-width:768px) {
  .bv-grid-2 { grid-template-columns:repeat(2,1fr); }
  .bv-results-cards { grid-template-columns:repeat(2,1fr); }
  .bv-methods { grid-template-columns:repeat(3,1fr); }
}
@media (max-width:767px) {
  .bv-grid-2 { grid-template-columns:1fr; }
  .bv-results-cards { grid-template-columns:1fr; }
  .bv-methods { grid-template-columns:1fr; }
}
.bv-input { width:100%; padding:10px 12px; border:1px solid var(--dash-border); border-radius:8px; font-size:15px; font-family:var(--font-body); color:var(--dash-ink); background:#fff; box-sizing:border-box; }
.bv-input:focus { outline:none; border-color:var(--dash-primary); box-shadow:0 0 0 3px var(--dash-primary-soft); }
.bv-label { display:block; font-size:13px; font-weight:600; color:var(--dash-ink-soft); margin-bottom:6px; font-family:var(--font-body); }
</style>
<main class="pub-page">

<!-- Intro -->
<section class="pub-section surface" style="padding-bottom:0;">
  <div class="pub-wrap-narrow">
    <div class="pub-breadcrumbs" style="font-size:0.82rem;color:var(--dash-ink-soft);margin-bottom:16px;">
      <a href="<?= APP_URL ?>/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a> <span style="margin:0 6px;">/</span>
      <a href="<?= APP_URL ?>/how-it-works" style="color:var(--dash-ink-soft);text-decoration:none;">How To</a> <span style="margin:0 6px;">/</span>
      <span>Business Valuation Calculator</span>
    </div>
    <div class="pub-section-head left" style="margin-bottom:var(--space-5);">
      <span class="pub-eyebrow">Free Tool</span>
      <h1 class="pub-h1">Business Valuation Calculator</h1>
      <p class="pub-lead">
        At <?= APP_NAME ?>, we define Business Valuation as a technique used to capture the true value of a business based on similar comparable companies. Our comparable data includes publicly traded companies across global stock exchanges and private transactions of thousands of small businesses, which makes our valuation estimate as precise as it can get. Curious to know the valuation of your business? Try our simple, yet one of the most effective, online valuation calculators out there &mdash; for free.
      </p>
    </div>
  </div>
</section>

<!-- Calculator -->
<section class="pub-section surface" style="padding-top:var(--space-5);">
  <div class="pub-wrap-narrow">
    <div class="pub-card" style="border-top:4px solid var(--dash-primary);">
      <h2 class="pub-h2" style="margin-bottom:var(--space-5);font-size:1.2rem;">Business Details</h2>
      <div class="bv-grid-2" style="display:grid;gap:20px;">
        <div>
          <label class="bv-label" for="bv-industry">Industry</label>
          <select class="bv-input" id="bv-industry">
            <option value="Technology">Technology</option>
            <option value="FinTech">FinTech</option>
            <option value="Healthcare">Healthcare</option>
            <option value="Education">Education</option>
            <option value="E-commerce">E-commerce</option>
            <option value="Manufacturing">Manufacturing</option>
            <option value="Food & Beverage">Food &amp; Beverage</option>
            <option value="Hotels & Resorts">Hotels &amp; Resorts</option>
            <option value="Retail">Retail</option>
            <option value="Construction">Construction</option>
            <option value="Real Estate">Real Estate</option>
            <option value="Transportation">Transportation</option>
            <option value="Agriculture">Agriculture</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div>
          <label class="bv-label" for="bv-revenue">Annual Revenue</label>
          <div style="display:flex;gap:8px;">
            <input type="number" min="0" step="0.01" class="bv-input" id="bv-revenue" placeholder="e.g., 1.6" style="flex:1;" value="1.6">
            <select class="bv-input" id="bv-unit" style="width:110px;flex:none;">
              <option value="10000000">Crore</option>
              <option value="100000">Lakh</option>
            </select>
          </div>
        </div>
        <div>
          <label class="bv-label" for="bv-ebitda">EBITDA Margin (%)</label>
          <input type="number" min="0" step="0.1" class="bv-input" id="bv-ebitda" placeholder="e.g., 20" value="20">
        </div>
        <div>
          <label class="bv-label" for="bv-growth">Growth Rate (% YoY)</label>
          <input type="number" step="0.1" class="bv-input" id="bv-growth" placeholder="e.g., 15" value="15">
        </div>
        <div>
          <label class="bv-label" for="bv-year">Year Established</label>
          <input type="number" min="1900" class="bv-input" id="bv-year" placeholder="e.g., 2016" value="2016">
        </div>
      </div>
      <button type="button" onclick="bvCalculate()" class="btn btn-primary btn-lg" style="margin-top:var(--space-5);display:inline-flex;align-items:center;gap:8px;">
        <span style="font-family:'Material Symbols Outlined';font-size:20px;">calculate</span>
        Calculate Value
      </button>
      <p id="bv-error" style="display:none;margin:16px 0 0;font-size:14px;color:var(--color-error);"></p>
    </div>
  </div>
</section>

<!-- Results -->
<section id="bv-results" style="display:none;" class="pub-section surface" style="padding-top:0;">
  <div class="pub-wrap-narrow">
    <div style="background:linear-gradient(135deg,var(--dash-primary),#4A1317);border-radius:16px;padding:40px 24px;text-align:center;">
      <div style="font-size:13px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:rgba(255,255,255,0.7);margin-bottom:8px;">Estimated Valuation Range</div>
      <div id="bv-range" style="font-size:40px;line-height:48px;font-weight:800;color:#fff;font-family:var(--font-heading);">&mdash;</div>
      <div style="margin-top:8px;font-size:14px;color:rgba(255,255,255,0.8);">Midpoint estimate: <span id="bv-mid" style="font-weight:700;">&mdash;</span></div>
    </div>

    <div class="bv-results-cards" style="display:grid;gap:16px;margin-top:16px;">
      <div class="pub-card" style="text-align:center;">
        <div style="font-size:13px;font-weight:600;color:var(--dash-ink-soft);margin-bottom:6px;">Trading Comparables</div>
        <div id="bv-trading" style="font-size:26px;font-weight:700;color:var(--dash-info);font-family:var(--font-heading);">&mdash;</div>
        <div style="font-size:12px;color:var(--dash-ink-soft);margin-top:4px;">Public company EV/EBITDA multiples</div>
      </div>
      <div class="pub-card" style="text-align:center;">
        <div style="font-size:13px;font-weight:600;color:var(--dash-ink-soft);margin-bottom:6px;">Transaction Comparables</div>
        <div id="bv-transaction" style="font-size:26px;font-weight:700;color:var(--dash-primary);font-family:var(--font-heading);">&mdash;</div>
        <div style="font-size:12px;color:var(--dash-ink-soft);margin-top:4px;">Private M&amp;A deal multiples</div>
      </div>
    </div>

    <div style="margin-top:16px;padding:16px;background:rgba(245,158,11,0.08);border-radius:12px;font-size:13px;line-height:20px;color:var(--dash-warning);">
      <strong>Disclaimer:</strong> This is an indicative estimate for informational purposes only and does not constitute a professional valuation. Actual value depends on many factors. Consult a qualified M&amp;A advisor for an accurate assessment.
    </div>

    <div style="margin-top:24px;text-align:center;">
      <p class="pub-text" style="font-size:15px;color:var(--dash-ink-soft);margin:0 0 12px;">Ready to take the next step with your business?</p>
      <a href="<?= APP_URL ?>/onboarding" class="btn btn-primary">List Your Business</a>
    </div>
  </div>
</section>

<!-- Methodology -->
<section class="pub-section tint">
  <div class="pub-wrap-narrow">
    <div class="pub-section-head left">
      <h2 class="pub-h2">How We Value a Business</h2>
      <p class="pub-lead">Three common approaches are used to value a business for sale or investment.</p>
    </div>
    <div class="bv-methods" style="display:grid;gap:16px;margin-top:var(--space-5);">
      <div class="pub-card">
        <span style="color:var(--dash-primary);font-family:'Material Symbols Outlined';font-size:28px;font-variation-settings:'FILL' 1;">trending_up</span>
        <h3 class="pub-card-title" style="margin:10px 0 6px;">DCF Method</h3>
        <p class="pub-card-text">Discounted Cash Flow projects future cash flows and discounts them to present value using a rate that reflects risk.</p>
      </div>
      <div class="pub-card">
        <span style="color:var(--dash-info);font-family:'Material Symbols Outlined';font-size:28px;font-variation-settings:'FILL' 1;">show_chart</span>
        <h3 class="pub-card-title" style="margin:10px 0 6px;">Trading Comparables</h3>
        <p class="pub-card-text">Compares your business to publicly traded companies in similar industries using multiples like EV/EBITDA and P/E.</p>
      </div>
      <div class="pub-card">
        <span style="color:var(--dash-primary);font-family:'Material Symbols Outlined';font-size:28px;font-variation-settings:'FILL' 1;">handshake</span>
        <h3 class="pub-card-title" style="margin:10px 0 6px;">Transaction Comparables</h3>
        <p class="pub-card-text">Uses data from actual M&amp;A transactions of similar businesses to derive valuation multiples.</p>
      </div>
    </div>
  </div>
</section>

</main>

<script>
// Industry EV/EBITDA multiples: [trading (public), transaction (private M&A)]
var BV_MULTIPLES = {
  'Technology':      [11.0, 8.5],
  'FinTech':         [12.0, 9.0],
  'Healthcare':      [10.0, 7.5],
  'Education':       [10.0, 7.5],
  'E-commerce':      [9.0,  7.0],
  'Manufacturing':   [7.0,  5.5],
  'Food & Beverage': [7.0,  5.5],
  'Hotels & Resorts':[8.0,  6.0],
  'Retail':          [6.0,  4.5],
  'Construction':    [6.5,  5.0],
  'Real Estate':     [8.0,  6.0],
  'Transportation':  [6.5,  5.0],
  'Agriculture':     [5.5,  4.0],
  'Other':           [7.0,  5.5]
};

function bvFormat(val) {
  val = Math.round(val);
  if (val >= 10000000) return 'NPR ' + (val / 10000000).toFixed(2).replace(/\.00$/, '') + ' Cr';
  if (val >= 100000)   return 'NPR ' + (val / 100000).toFixed(2).replace(/\.00$/, '') + ' L';
  return 'NPR ' + val.toLocaleString('en-IN');
}

function bvCalculate() {
  var errEl = document.getElementById('bv-error');
  errEl.style.display = 'none';

  var industry = document.getElementById('bv-industry').value;
  var unit = parseFloat(document.getElementById('bv-unit').value) || 10000000;
  var revenue = (parseFloat(document.getElementById('bv-revenue').value) || 0) * unit;
  var margin = parseFloat(document.getElementById('bv-ebitda').value) || 0;
  var growth = parseFloat(document.getElementById('bv-growth').value) || 0;
  var year = parseInt(document.getElementById('bv-year').value, 10) || new Date().getFullYear();

  var ebitda = revenue * (margin / 100);
  if (ebitda <= 0) {
    errEl.textContent = 'Please enter a positive annual revenue and EBITDA margin to estimate a valuation.';
    errEl.style.display = 'block';
    return;
  }

  var mult = BV_MULTIPLES[industry] || BV_MULTIPLES['Other'];

  // Growth premium: clamp growth to [-50, 100], half-weighted
  var g = Math.max(-50, Math.min(100, growth));
  var growthAdj = 1 + (g / 100) * 0.5;

  // Track-record premium: up to +20% for 20+ years operating
  var yearsOperating = Math.max(0, new Date().getFullYear() - year);
  var yearsAdj = 1 + Math.min(yearsOperating, 20) * 0.01;

  var trading = ebitda * mult[0] * growthAdj * yearsAdj;
  var transaction = ebitda * mult[1] * growthAdj * yearsAdj;

  var low = Math.min(trading, transaction);
  var high = Math.max(trading, transaction);
  var mid = (low + high) / 2;

  document.getElementById('bv-trading').textContent = bvFormat(trading);
  document.getElementById('bv-transaction').textContent = bvFormat(transaction);
  document.getElementById('bv-range').innerHTML = bvFormat(low) + ' &ndash; ' + bvFormat(high);
  document.getElementById('bv-mid').textContent = bvFormat(mid);

  var results = document.getElementById('bv-results');
  results.style.display = 'block';
  results.scrollIntoView({ behavior: 'smooth' });
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
