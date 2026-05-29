<?php
require __DIR__ . '/../config/bootstrap.php';

$result = null;

$industry_multipliers = [
    'Technology' => 3.5,
    'Manufacturing' => 2.5,
    'Food & Beverage' => 2.0,
    'Retail' => 1.8,
    'Healthcare' => 3.0,
    'Hotels & Resorts' => 2.2,
    'Agriculture' => 1.5,
    'Transportation' => 2.0,
    'Construction' => 2.3,
    'Education' => 2.8,
    'E-commerce' => 3.2,
    'Real Estate' => 2.5,
    'FinTech' => 4.0,
    'EdTech' => 3.8,
    'CleanTech' => 3.0,
    'Other' => 2.0,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'valuate') {
    $business_name = trim($_POST['business_name'] ?? '');
    $industry = $_POST['industry'] ?? 'Other';
    $annual_revenue = (float)($_POST['annual_revenue'] ?? 0);
    $profit_margin = (float)($_POST['profit_margin'] ?? 0);
    $growth_rate = (float)($_POST['growth_rate'] ?? 0);
    $employee_count = (int)($_POST['employee_count'] ?? 0);
    $year_established = (int)($_POST['year_established'] ?? date('Y'));
    $revenue_unit = $_POST['revenue_unit'] ?? 'crore';

    $unit_multiplier = match ($revenue_unit) {
        'lakh' => 100000,
        'thousand' => 1000,
        default => 10000000, // crore
    };

    $revenue_in_npr = $annual_revenue * $unit_multiplier;
    $multiplier = $industry_multipliers[$industry] ?? 2.0;

    $valuation = $revenue_in_npr * (1 + $growth_rate / 100) * $multiplier;
    $years_operating = date('Y') - $year_established;
    if ($years_operating > 0) {
        $valuation *= (1 + ($years_operating * 0.02));
    }

    $result = [
        'business_name' => $business_name,
        'industry' => $industry,
        'annual_revenue' => $revenue_in_npr,
        'multiplier' => $multiplier,
        'valuation' => round($valuation),
        'years_operating' => max(0, $years_operating),
    ];
}

$pageTitle = 'Business Valuation Calculator — ' . APP_NAME;
$pageDescription = 'Free business valuation calculator. Estimate the value of your SME business in Nepal with our easy-to-use valuation tool.';

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"How To","item":"'.APP_URL.'/how-it-works"},
    {"@type": "ListItem","position":3,"name":"Business Valuation Calculator","item":"'.APP_URL.'/business-valuation"}
  ]
}</script>';
require __DIR__ . '/../includes/header.php';
?>
<main class="main-content" style="padding-top:0;">

<?= $breadcrumbSchema ?>
<div class="breadcrumbs container" style="padding-top:1rem;padding-bottom:1rem;font-size:0.85rem;color:var(--secondary-text);">
  <a href="<?= APP_URL ?>">Home</a> <span style="margin:0 0.5rem;">/</span>
  <a href="<?= APP_URL ?>/how-it-works">How To</a> <span style="margin:0 0.5rem;">/</span>
  <span>Business Valuation Calculator</span>
</div>

<div class="container" style="max-width:860px;padding-bottom:4rem;">
  <div style="text-align:center;margin-bottom:2rem;">
    <h1>Business Valuation Calculator</h1>
    <p style="font-size:1.05rem;color:var(--secondary-text);">Get an instant estimate of your business worth based on earnings, growth, and industry benchmarks.</p>
  </div>

  <div class="card" style="margin-bottom:2rem;">
    <h3>Business Details</h3>
    <form method="post" action="<?= APP_URL ?>/business-valuation">
      <input type="hidden" name="action" value="valuate">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
        <div class="input-group">
          <label>Business Name</label>
          <input type="text" name="business_name" class="input" placeholder="e.g., My Business" value="<?= e($_POST['business_name'] ?? '') ?>">
        </div>
        <div class="input-group">
          <label>Industry</label>
          <select name="industry" class="input">
            <?php foreach (array_keys($industry_multipliers) as $ind): ?>
            <option value="<?= e($ind) ?>"<?= ($_POST['industry'] ?? '') === $ind ? ' selected' : '' ?>><?= e($ind) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="input-group">
          <label>Annual Revenue</label>
          <div style="display:flex;gap:0.5rem;">
            <input type="number" step="0.01" name="annual_revenue" class="input" placeholder="e.g., 12" style="flex:1;" value="<?= e($_POST['annual_revenue'] ?? '') ?>">
            <select name="revenue_unit" class="input" style="width:120px;">
              <option value="crore"<?= ($_POST['revenue_unit'] ?? '') === 'crore' ? ' selected' : '' ?>>Crore</option>
              <option value="lakh"<?= ($_POST['revenue_unit'] ?? '') === 'lakh' ? ' selected' : '' ?>>Lakh</option>
              <option value="thousand"<?= ($_POST['revenue_unit'] ?? '') === 'thousand' ? ' selected' : '' ?>>Thousand</option>
            </select>
          </div>
        </div>
        <div class="input-group">
          <label>Profit Margin (%)</label>
          <input type="number" step="0.1" name="profit_margin" class="input" placeholder="e.g., 18" value="<?= e($_POST['profit_margin'] ?? '') ?>">
        </div>
        <div class="input-group">
          <label>Growth Rate (% YoY)</label>
          <input type="number" step="0.1" name="growth_rate" class="input" placeholder="e.g., 15" value="<?= e($_POST['growth_rate'] ?? '') ?>">
        </div>
        <div class="input-group">
          <label>Year Established</label>
          <input type="number" name="year_established" class="input" placeholder="e.g., 2015" value="<?= e($_POST['year_established'] ?? '') ?>">
        </div>
        <div class="input-group">
          <label>Employee Count</label>
          <input type="number" name="employee_count" class="input" placeholder="e.g., 45" value="<?= e($_POST['employee_count'] ?? '') ?>">
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="margin-top:1rem;">Calculate Value</button>
    </form>
  </div>

  <?php if ($result): ?>
  <div id="valuation-results">
    <h3>Estimated Business Value</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:2rem;">
      <div class="card" style="text-align:center;border-color:var(--brand-red);">
        <div class="meta-label">Industry Multiplier</div>
        <div style="font-size:1.8rem;font-weight:700;color:var(--brand-red);"><?= e($result['multiplier']) ?>x</div>
        <div style="font-size:0.8rem;color:var(--secondary-text);"><?= e($result['industry']) ?> sector</div>
      </div>
      <div class="card" style="text-align:center;">
        <div class="meta-label">Annual Revenue</div>
        <div style="font-size:1.8rem;font-weight:700;"><?= money($result['annual_revenue']) ?></div>
        <div style="font-size:0.8rem;color:var(--secondary-text);">Reported revenue</div>
      </div>
      <div class="card" style="text-align:center;">
        <div class="meta-label">Years Operating</div>
        <div style="font-size:1.8rem;font-weight:700;"><?= (int)$result['years_operating'] ?></div>
        <div style="font-size:0.8rem;color:var(--secondary-text);">Established track record</div>
      </div>
    </div>

    <div class="card" style="background:var(--surface-container);text-align:center;">
      <div class="meta-label">Estimated Value</div>
      <div style="font-size:2.5rem;font-weight:700;color:var(--dark);"><?= money($result['valuation']) ?></div>
      <div style="font-size:0.9rem;color:var(--secondary-text);">Based on revenue × industry multiplier × growth adjustment</div>
    </div>

    <div style="margin-top:1rem;padding:1rem;background:rgba(180,83,9,0.08);border-radius:12px;font-size:0.85rem;color:var(--warning);">
      <strong>Disclaimer:</strong> This is a rough estimate for informational purposes only. It does not constitute a professional valuation. Actual business value may vary significantly. Consult a qualified M&A advisor or valuation expert for an accurate assessment.
    </div>

    <div style="margin-top:2rem;text-align:center;">
      <p style="font-size:0.9rem;color:var(--secondary-text);">Want a detailed Confidential Information Memorandum (CIM) for your business?</p>
      <a href="<?= APP_URL ?>/signup" class="btn btn-primary">Get a Professional Valuation Report</a>
    </div>
  </div>
  <?php endif; ?>

  <div style="margin-top:2rem;">
    <h3>How to Value a Business</h3>
    <p style="color:var(--secondary-text);">Three common approaches are used to value a business for sale or investment:</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;">
      <div class="card card-compact">
        <h4>DCF Method</h4>
        <p style="font-size:0.85rem;margin:0;color:var(--secondary-text);">Discounted Cash Flow — projects future cash flows and discounts them to present value using a discount rate that reflects risk.</p>
      </div>
      <div class="card card-compact">
        <h4>Trading Comparables</h4>
        <p style="font-size:0.85rem;margin:0;color:var(--secondary-text);">Compares your business to publicly traded companies in similar industries using multiples like EV/EBITDA and P/E.</p>
      </div>
      <div class="card card-compact">
        <h4>Transaction Comparables</h4>
        <p style="font-size:0.85rem;margin:0;color:var(--secondary-text);">Uses data from actual M&A transactions of similar businesses to derive valuation multiples.</p>
      </div>
    </div>
  </div>
</div>

</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
