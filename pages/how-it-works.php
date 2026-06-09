<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'How To Guides — ' . APP_NAME;
$pageDescription = 'Step-by-step guides for selling a business, buying a business, investing, franchising, or registering as an advisor on Asaan Capital Ltd.';

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"How To Guides","item":"'.APP_URL.'/how-it-works"}
  ]
}</script>';
$forcePublicHeader = true;
require __DIR__ . '/../includes/header.php';
?>
<main class="pub-page" style="padding-top:0;">

<?= $breadcrumbSchema ?>

<!-- Hero -->
<section class="pub-hero">
  <div class="pub-wrap">
    <div class="pub-section-head">
      <div class="pub-breadcrumbs" style="font-size:0.82rem;color:var(--dash-ink-soft);margin-bottom:16px;">
        <a href="<?= APP_URL ?>" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a>
        <span style="margin:0 6px;">/</span>
        <span>How To Guides</span>
      </div>
      <span class="pub-eyebrow">Step-by-Step Guides</span>
      <h1 class="pub-h1" style="margin-top:8px;">How <?= APP_NAME_LONG ?> Works</h1>
      <p class="pub-lead">Select your path below to get started. Every journey follows the same trusted process — tailored to your goal.</p>
    </div>

    <!-- Persona selector -->
    <div class="persona-tabs" id="personaTabs" style="display:flex;gap:0.5rem;flex-wrap:wrap;justify-content:center;margin-top:var(--space-5);">
      <button class="persona-tab btn btn-primary btn-sm" onclick="switchPersona('sell', this)">Sell Your Business</button>
      <button class="persona-tab btn btn-outline btn-sm" onclick="switchPersona('buy', this)">Buy a Business</button>
      <button class="persona-tab btn btn-outline btn-sm" onclick="switchPersona('invest', this)">Invest in a Business</button>
      <button class="persona-tab btn btn-outline btn-sm" onclick="switchPersona('franchise', this)">Franchise Your Business</button>
      <button class="persona-tab btn btn-outline btn-sm" onclick="switchPersona('advisor', this)">Register as an Advisor</button>
    </div>
  </div>
</section>

<!-- Sell path (default) -->
<section class="pub-section surface" id="content-sell">
  <div class="pub-wrap">
    <div class="pub-section-head left">
      <span class="pub-eyebrow">Seller Journey</span>
      <h2 class="pub-h2">Sell Your Business</h2>
      <p class="pub-lead">Thousands of pre-verified investors and buyers actively searching for businesses like yours.</p>
    </div>
    <div class="pub-grid cols-3" style="margin-top:var(--space-5);">
      <div class="pub-card">
        <span class="pub-badge" style="margin-bottom:var(--space-3);">Step 1 — Today</span>
        <div class="pub-card-title">Create Business Profile</div>
        <p class="pub-card-text">Register and complete your business profile with financials, documents, and description. Our team reviews within 2 business days.</p>
      </div>
      <div class="pub-card">
        <span class="pub-badge" style="margin-bottom:var(--space-3);">Step 2 — By Week 1</span>
        <div class="pub-card-title">Profile Activation &amp; Matching</div>
        <p class="pub-card-text">Once approved, your profile is live. Our algorithm matches you with relevant buyers and investors based on industry, size, and location.</p>
      </div>
      <div class="pub-card">
        <span class="pub-badge" style="margin-bottom:var(--space-3);">Step 3 — By Week 2–3</span>
        <div class="pub-card-title">Buyer Introductions</div>
        <p class="pub-card-text">Receive and review interest from verified buyers. Accept proposals that fit your criteria.</p>
      </div>
      <div class="pub-card">
        <span class="pub-badge" style="margin-bottom:var(--space-3);">Step 4 — By Week 3–4</span>
        <div class="pub-card-title">Share Documents</div>
        <p class="pub-card-text">Share confidential documents with serious buyers after mutual NDA. Conduct management discussions.</p>
      </div>
      <div class="pub-card">
        <span class="pub-badge" style="margin-bottom:var(--space-3);">Step 5 — By Month 3</span>
        <div class="pub-card-title">Due Diligence &amp; Closure</div>
        <p class="pub-card-text">Complete financial and legal due diligence. Finalize deal terms and close with your advisors.</p>
      </div>
    </div>
    <div class="pub-cta-actions" style="margin-top:var(--space-6);justify-content:flex-start;">
      <a href="<?= APP_URL ?>/onboarding" class="btn btn-primary">Create Business Profile</a>
      <a href="<?= APP_URL ?>/business-valuation" class="btn btn-outline">Value My Business First</a>
    </div>
  </div>
</section>

<!-- Buy path -->
<section class="pub-section tint" id="content-buy" style="display:none;">
  <div class="pub-wrap">
    <div class="pub-section-head left">
      <span class="pub-eyebrow">Buyer Journey</span>
      <h2 class="pub-h2">Buy a Business</h2>
      <p class="pub-lead">Browse thousands of pre-screened businesses for sale across 900+ industries and 170+ countries.</p>
    </div>
    <div class="pub-grid cols-3" style="margin-top:var(--space-5);">
      <div class="pub-card">
        <span class="pub-badge info" style="margin-bottom:var(--space-3);">Step 1 — Today</span>
        <div class="pub-card-title">Create Investor Profile</div>
        <p class="pub-card-text">Register and define your acquisition criteria: industry, location, size, and budget. Get verified.</p>
      </div>
      <div class="pub-card">
        <span class="pub-badge info" style="margin-bottom:var(--space-3);">Step 2 — By Week 1</span>
        <div class="pub-card-title">Browse &amp; Get Matched</div>
        <p class="pub-card-text">Search businesses for sale or receive smart matches based on your mandate. Save favorites.</p>
      </div>
      <div class="pub-card">
        <span class="pub-badge info" style="margin-bottom:var(--space-3);">Step 3 — By Week 2–3</span>
        <div class="pub-card-title">Express Interest</div>
        <p class="pub-card-text">Send proposals to businesses you're interested in. Sellers review your verified profile.</p>
      </div>
      <div class="pub-card">
        <span class="pub-badge info" style="margin-bottom:var(--space-3);">Step 4 — By Week 3–4</span>
        <div class="pub-card-title">NDA &amp; Documents</div>
        <p class="pub-card-text">Sign NDA through platform. Access confidential financial documents and data room.</p>
      </div>
      <div class="pub-card">
        <span class="pub-badge info" style="margin-bottom:var(--space-3);">Step 5 — By Month 3</span>
        <div class="pub-card-title">Due Diligence &amp; Close</div>
        <p class="pub-card-text">Conduct site visits, financial audits, legal review. Finalize acquisition with advisors.</p>
      </div>
    </div>
    <div class="pub-cta-actions" style="margin-top:var(--space-6);justify-content:flex-start;">
      <a href="<?= APP_URL ?>/browse/businesses" class="btn btn-primary">Browse Businesses for Sale</a>
      <a href="<?= APP_URL ?>/onboarding" class="btn btn-outline">Create Investor Profile</a>
    </div>
  </div>
</section>

<!-- Invest path -->
<section class="pub-section surface" id="content-invest" style="display:none;">
  <div class="pub-wrap">
    <div class="pub-section-head left">
      <span class="pub-eyebrow">Investor Journey</span>
      <h2 class="pub-h2">Invest in a Business</h2>
      <p class="pub-lead">Discover vetted investment opportunities — from startups to established businesses seeking growth capital.</p>
    </div>
    <div class="pub-grid cols-2" style="margin-top:var(--space-5);">
      <div class="pub-card">
        <span class="pub-badge success" style="margin-bottom:var(--space-3);">Step 1</span>
        <div class="pub-card-title">Register as Investor</div>
        <p class="pub-card-text">Create your investor profile, define ticket size, sector preference, and investment stage.</p>
      </div>
      <div class="pub-card">
        <span class="pub-badge success" style="margin-bottom:var(--space-3);">Step 2</span>
        <div class="pub-card-title">Discover Opportunities</div>
        <p class="pub-card-text">Browse investment opportunities or get weekly smart-match recommendations via email.</p>
      </div>
      <div class="pub-card">
        <span class="pub-badge success" style="margin-bottom:var(--space-3);">Step 3</span>
        <div class="pub-card-title">Connect &amp; Evaluate</div>
        <p class="pub-card-text">Express interest, review documents, attend management presentations.</p>
      </div>
      <div class="pub-card">
        <span class="pub-badge success" style="margin-bottom:var(--space-3);">Step 4</span>
        <div class="pub-card-title">Invest &amp; Monitor</div>
        <p class="pub-card-text">Complete investment, receive post-investment updates through the platform.</p>
      </div>
    </div>
    <div class="pub-cta-actions" style="margin-top:var(--space-6);justify-content:flex-start;">
      <a href="<?= APP_URL ?>/onboarding" class="btn btn-primary">Register as Investor</a>
      <a href="<?= APP_URL ?>/browse/businesses?type=investment" class="btn btn-outline">View Investment Opportunities</a>
    </div>
  </div>
</section>

<!-- Franchise path -->
<section class="pub-section tint" id="content-franchise" style="display:none;">
  <div class="pub-wrap">
    <div class="pub-section-head left">
      <span class="pub-eyebrow">Franchisor Journey</span>
      <h2 class="pub-h2">Franchise Your Business</h2>
      <p class="pub-lead">Expand your brand through franchising. Connect with qualified franchisees looking for opportunities.</p>
    </div>
    <div class="pub-grid cols-2" style="margin-top:var(--space-5);">
      <div class="pub-card">
        <span class="pub-badge warning" style="margin-bottom:var(--space-3);">Step 1</span>
        <div class="pub-card-title">Create Franchise Profile</div>
        <p class="pub-card-text">List your brand with franchise details — investment required, space, training, and support.</p>
      </div>
      <div class="pub-card">
        <span class="pub-badge warning" style="margin-bottom:var(--space-3);">Step 2</span>
        <div class="pub-card-title">Get Discovered</div>
        <p class="pub-card-text">Your brand appears in franchise searches and gets recommended to franchise investors.</p>
      </div>
      <div class="pub-card">
        <span class="pub-badge warning" style="margin-bottom:var(--space-3);">Step 3</span>
        <div class="pub-card-title">Screen Candidates</div>
        <p class="pub-card-text">Review franchise applications, evaluate candidates' background and financial capacity.</p>
      </div>
      <div class="pub-card">
        <span class="pub-badge warning" style="margin-bottom:var(--space-3);">Step 4</span>
        <div class="pub-card-title">Expand Your Network</div>
        <p class="pub-card-text">Sign franchise agreements and grow your brand presence across new locations.</p>
      </div>
    </div>
    <div class="pub-cta-actions" style="margin-top:var(--space-6);justify-content:flex-start;">
      <a href="<?= APP_URL ?>/onboarding" class="btn btn-primary">Create Franchise Profile</a>
      <a href="<?= APP_URL ?>/browse/franchises" class="btn btn-outline">View Franchise Opportunities</a>
    </div>
  </div>
</section>

<!-- Advisor path -->
<section class="pub-section surface" id="content-advisor" style="display:none;">
  <div class="pub-wrap">
    <div class="pub-section-head left">
      <span class="pub-eyebrow">Advisor Journey</span>
      <h2 class="pub-h2">Register as an Advisor</h2>
      <p class="pub-lead">M&amp;A advisors, business brokers, financial consultants, accountants, and law firms — help businesses close deals.</p>
    </div>
    <div class="pub-grid cols-2" style="margin-top:var(--space-5);">
      <div class="pub-card">
        <span class="pub-badge info" style="margin-bottom:var(--space-3);">Step 1</span>
        <div class="pub-card-title">Create Advisor Profile</div>
        <p class="pub-card-text">Register with your credentials, specialization (M&amp;A, valuation, legal, accounting), and track record.</p>
      </div>
      <div class="pub-card">
        <span class="pub-badge info" style="margin-bottom:var(--space-3);">Step 2</span>
        <div class="pub-card-title">Get Discovered</div>
        <p class="pub-card-text">Business owners and investors search for advisors by expertise, industry, and location.</p>
      </div>
      <div class="pub-card">
        <span class="pub-badge info" style="margin-bottom:var(--space-3);">Step 3</span>
        <div class="pub-card-title">Engage &amp; Advise</div>
        <p class="pub-card-text">Receive mandates, provide valuation, due diligence, deal structuring, and negotiation support.</p>
      </div>
      <div class="pub-card">
        <span class="pub-badge info" style="margin-bottom:var(--space-3);">Step 4</span>
        <div class="pub-card-title">Close Deals</div>
        <p class="pub-card-text">Guide transactions to successful closure. Build your reputation through client ratings.</p>
      </div>
    </div>
    <div class="pub-cta-actions" style="margin-top:var(--space-6);justify-content:flex-start;">
      <a href="<?= APP_URL ?>/onboarding" class="btn btn-primary">Register as Advisor</a>
    </div>
  </div>
</section>

<!-- Service tiers -->
<section class="pub-section tint">
  <div class="pub-wrap">
    <div class="pub-section-head">
      <span class="pub-eyebrow">Pricing</span>
      <h2 class="pub-h2">Select Your Service Level</h2>
    </div>
    <div class="pub-grid cols-3" style="margin-top:var(--space-5);">
      <div class="pub-card" style="text-align:center;">
        <div class="pub-card-title" style="font-size:1.1rem;">Basic</div>
        <div style="font-family:var(--font-heading);font-size:2rem;font-weight:800;color:var(--dash-ink);margin:12px 0;">Free</div>
        <ul style="text-align:left;font-size:0.875rem;list-style:disc;padding-left:1.25rem;color:var(--dash-ink-soft);line-height:1.8;">
          <li>Profile creation</li>
          <li>Browse listings</li>
          <li>Basic search</li>
        </ul>
      </div>
      <div class="pub-card" style="text-align:center;border-color:var(--dash-primary);border-top:4px solid var(--dash-primary);">
        <div class="pub-card-title" style="font-size:1.1rem;">Premium</div>
        <div style="font-family:var(--font-heading);font-size:2rem;font-weight:800;color:var(--dash-primary);margin:12px 0;">NPR 25,500</div>
        <ul style="text-align:left;font-size:0.875rem;list-style:disc;padding-left:1.25rem;color:var(--dash-ink-soft);line-height:1.8;">
          <li>Priority visibility</li>
          <li>Unlimited connections</li>
          <li>Analytics dashboard</li>
          <li>1% finder's fee post close</li>
        </ul>
      </div>
      <div class="pub-card" style="text-align:center;">
        <div class="pub-card-title" style="font-size:1.1rem;">Enterprise</div>
        <div style="font-family:var(--font-heading);font-size:2rem;font-weight:800;color:var(--dash-ink);margin:12px 0;">NPR 2.55 L</div>
        <ul style="text-align:left;font-size:0.875rem;list-style:disc;padding-left:1.25rem;color:var(--dash-ink-soft);line-height:1.8;">
          <li>Dedicated relationship manager</li>
          <li>CIM &amp; valuation reports</li>
          <li>Priority support</li>
          <li>1% finder's fee post close</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="pub-section surface tight">
  <div class="pub-wrap">
    <div class="pub-cta">
      <h2>Ready to Get Started?</h2>
      <p>Join thousands of business owners, investors, and advisors on <?= APP_NAME_LONG ?>.</p>
      <div class="pub-cta-actions">
        <a href="<?= APP_URL ?>/onboarding" class="btn btn-primary">Create Free Account</a>
        <a href="<?= APP_URL ?>/contact" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,0.5);">Talk to Us</a>
      </div>
    </div>
  </div>
</section>

<script>
function switchPersona(id, btn) {
  document.querySelectorAll('.persona-content-section').forEach(function(el) { el.style.display = 'none'; });
  document.querySelectorAll('.persona-tab').forEach(function(el) {
    el.classList.remove('btn-primary');
    el.classList.add('btn-outline');
  });
  document.getElementById('content-' + id).style.display = 'block';
  btn.classList.remove('btn-outline');
  btn.classList.add('btn-primary');
}

// Map IDs to sections
var personaSections = ['sell','buy','invest','franchise','advisor'];
personaSections.forEach(function(id) {
  var section = document.getElementById('content-' + id);
  if (section) section.classList.add('persona-content-section');
});

// Handle hash
var hash = window.location.hash.replace('#', '');
if (hash && personaSections.indexOf(hash) !== -1) {
  var btns = document.querySelectorAll('.persona-tab');
  for (var i = 0; i < btns.length; i++) {
    if (btns[i].getAttribute('onclick') && btns[i].getAttribute('onclick').indexOf(hash) !== -1) {
      btns[i].click();
      break;
    }
  }
}
</script>

</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
