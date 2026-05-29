<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role(ROLE_BUSINESS_OWNER);

$pageTitle = 'Business Valuation Calculator';
$pageDescription = 'Free business valuation tool for SMEs in Nepal. Calculate your business worth based on financial metrics.';

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Dashboard","item":"'.APP_URL.'/dashboard"},
    {"@type": "ListItem","position":3,"name":"Business Valuation Calculator","item":"'.APP_URL.'/business/valuation.php"}
  ]
}</script>';
require __DIR__ . '/../includes/layout-dashboard.php';
?>
<?= $breadcrumbSchema ?>
<div class="breadcrumbs container">
    <a href="<?= APP_URL ?>/">Home</a> <span>/</span>
    <a href="dashboard.php">Dashboard</a> <span>/</span>
    <span>Business Valuation Calculator</span>
</div>

<div class="container" style="max-width:860px;padding-bottom:4rem;">
    <div style="text-align:center;margin-bottom:2rem;">
        <h1>Business Valuation Calculator</h1>
        <p class="body-lg">Get an instant estimate of your business worth using DCF, Trading Comparables, and Transaction Comparables methods.</p>
    </div>

    <div class="card" style="margin-bottom:2rem;">
        <h3>Business Details</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
            <div class="input-group">
                <label>Industry</label>
                <select class="input" id="industry">
                    <option value="1.5">Technology</option>
                    <option value="1.3">Manufacturing</option>
                    <option value="1.2">Food &amp; Beverage</option>
                    <option value="1.1">Retail</option>
                    <option value="1.4">Healthcare</option>
                    <option value="1.25">Hotels &amp; Resorts</option>
                    <option value="1.0">Agriculture</option>
                    <option value="1.15">Transportation</option>
                </select>
            </div>
            <div class="input-group">
                <label>Annual Revenue (NPR)</label>
                <input type="number" class="input" id="revenue" placeholder="e.g., 12000000" value="12000000">
            </div>
            <div class="input-group">
                <label>EBITDA Margin (%)</label>
                <input type="number" class="input" id="ebitda" placeholder="e.g., 18" value="18">
            </div>
            <div class="input-group">
                <label>Growth Rate (% YoY)</label>
                <input type="number" class="input" id="growth" placeholder="e.g., 15" value="15">
            </div>
            <div class="input-group">
                <label>Years in Business</label>
                <input type="number" class="input" id="years" placeholder="e.g., 8" value="8">
            </div>
        </div>
        <button class="btn btn-primary" style="margin-top:1rem;" onclick="calculateValuation()">Calculate Value</button>
    </div>

    <div id="valuation-results" style="display:none;">
        <h3>Estimated Business Value</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:2rem;">
            <div class="card" style="text-align:center;border-color:var(--accent);">
                <div class="meta-label">DCF Method</div>
                <div style="font-size:1.8rem;font-weight:700;color:var(--accent);" id="dcf-value">NPR 0</div>
                <div style="font-size:0.8rem;color:#666;">Based on projected cash flows</div>
            </div>
            <div class="card" style="text-align:center;">
                <div class="meta-label">Trading Comparables</div>
                <div style="font-size:1.8rem;font-weight:700;" id="trading-value">NPR 0</div>
                <div style="font-size:0.8rem;color:#666;">Based on public company multiples</div>
            </div>
            <div class="card" style="text-align:center;">
                <div class="meta-label">Transaction Comparables</div>
                <div style="font-size:1.8rem;font-weight:700;" id="transaction-value">NPR 0</div>
                <div style="font-size:0.8rem;color:#666;">Based on similar M&A transactions</div>
            </div>
        </div>

        <div class="card" style="background:#f6f3f1;text-align:center;">
            <div class="meta-label">Optimal Value Range</div>
            <div style="font-size:2.5rem;font-weight:700;color:var(--ink);" id="range-value">NPR 0 – 0</div>
            <div style="font-size:0.9rem;color:#666;">Based on weighted average of all three methods</div>
        </div>

        <div style="margin-top:2rem;text-align:center;">
            <p style="font-size:0.9rem;">Want a detailed Confidential Information Memorandum (CIM) for your business?</p>
            <a href="<?= APP_URL ?>/contact" class="btn btn-primary">Request Professional Valuation</a>
        </div>
    </div>

    <div style="margin-top:2rem;">
        <h3>How to Value a Business</h3>
        <p>Three common approaches are used to value a business for sale or investment:</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;">
            <div class="card card-compact">
                <h4>DCF Method</h4>
                <p style="font-size:0.85rem;margin:0;">Discounted Cash Flow &mdash; projects future cash flows and discounts them to present value using a discount rate that reflects risk.</p>
            </div>
            <div class="card card-compact">
                <h4>Trading Comparables</h4>
                <p style="font-size:0.85rem;margin:0;">Compares your business to publicly traded companies in similar industries using multiples like EV/EBITDA and P/E.</p>
            </div>
            <div class="card card-compact">
                <h4>Transaction Comparables</h4>
                <p style="font-size:0.85rem;margin:0;">Uses data from actual M&A transactions of similar businesses to derive valuation multiples.</p>
            </div>
        </div>
    </div>
</div>

<script>
function calculateValuation() {
    const revenue = parseFloat(document.getElementById('revenue').value) || 0;
    const ebitdaPct = parseFloat(document.getElementById('ebitda').value) || 0;
    const growth = parseFloat(document.getElementById('growth').value) || 0;
    const years = parseFloat(document.getElementById('years').value) || 1;
    const multiplier = parseFloat(document.getElementById('industry').value) || 1.0;
    const ebitda = revenue * (ebitdaPct / 100);

    const dcf = ebitda * multiplier * (1 + growth / 100) * (1 - Math.pow(1 + growth / 100, -5)) / (growth / 100) * 0.7;
    const trading = ebitda * (8 + multiplier * 2);
    const transaction = ebitda * (5 + multiplier * 1.5);

    const fmt = (val) => {
        if (val >= 10000000) return 'NPR ' + (val / 10000000).toFixed(1) + ' Cr';
        if (val >= 100000) return 'NPR ' + (val / 100000).toFixed(1) + ' L';
        return 'NPR ' + Math.round(val).toLocaleString();
    };

    document.getElementById('dcf-value').textContent = fmt(dcf);
    document.getElementById('trading-value').textContent = fmt(trading);
    document.getElementById('transaction-value').textContent = fmt(transaction);

    const min = Math.min(dcf, trading, transaction);
    const max = Math.max(dcf, trading, transaction);
    document.getElementById('range-value').textContent = fmt(min) + ' &ndash; ' + fmt(max);

    document.getElementById('valuation-results').style.display = 'block';
    document.getElementById('valuation-results').scrollIntoView({ behavior: 'smooth' });
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
</div></div>
