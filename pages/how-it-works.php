<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'How To Guides — ' . APP_NAME;
require __DIR__ . '/../includes/header.php';
?>
<main class="main-content" style="padding-top:0;">

<div class="breadcrumbs container" style="padding-top:1rem;padding-bottom:1rem;font-size:0.85rem;color:var(--secondary-text);">
  <a href="<?= APP_URL ?>">Home</a> <span style="margin:0 0.5rem;">/</span>
  <span>How To Guides</span>
</div>

<div class="container" style="padding-bottom:4rem;">
  <div style="max-width:720px; margin:0 auto 2rem; text-align:center;">
    <h1>How <?= APP_NAME ?> Works</h1>
    <p style="font-size:1.1rem;color:var(--secondary-text);">Select your path below to get started. Every journey follows the same trusted process — tailored to your goal.</p>
  </div>

  <!-- Persona selector -->
  <div class="persona-tabs" id="personaTabs" style="display:flex;gap:0.5rem;flex-wrap:wrap;justify-content:center;margin-bottom:2rem;">
    <button class="persona-tab active" onclick="switchPersona('sell', this)" style="padding:10px 20px;border-radius:999px;font-weight:600;cursor:pointer;font-size:0.9rem;border:1.5px solid var(--surface-container-high);background:var(--brand-red);color:#fff;font-family:inherit;transition:all 0.2s;">Sell Your Business</button>
    <button class="persona-tab" onclick="switchPersona('buy', this)" style="padding:10px 20px;border-radius:999px;font-weight:600;cursor:pointer;font-size:0.9rem;border:1.5px solid var(--surface-container-high);background:var(--surface);color:var(--dark);font-family:inherit;transition:all 0.2s;">Buy a Business</button>
    <button class="persona-tab" onclick="switchPersona('invest', this)" style="padding:10px 20px;border-radius:999px;font-weight:600;cursor:pointer;font-size:0.9rem;border:1.5px solid var(--surface-container-high);background:var(--surface);color:var(--dark);font-family:inherit;transition:all 0.2s;">Invest in a Business</button>
    <button class="persona-tab" onclick="switchPersona('franchise', this)" style="padding:10px 20px;border-radius:999px;font-weight:600;cursor:pointer;font-size:0.9rem;border:1.5px solid var(--surface-container-high);background:var(--surface);color:var(--dark);font-family:inherit;transition:all 0.2s;">Franchise Your Business</button>
    <button class="persona-tab" onclick="switchPersona('advisor', this)" style="padding:10px 20px;border-radius:999px;font-weight:600;cursor:pointer;font-size:0.9rem;border:1.5px solid var(--surface-container-high);background:var(--surface);color:var(--dark);font-family:inherit;transition:all 0.2s;">Register as an Advisor</button>
  </div>

  <!-- Sell path (default) -->
  <div class="persona-content" id="content-sell">
    <h2>Sell Your Business</h2>
    <p style="font-size:1.05rem;color:var(--secondary-text);">Thousands of pre-verified investors and buyers actively searching for businesses like yours.</p>

    <div style="display:flex;flex-direction:column;gap:1.25rem;margin-top:1.5rem;">
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:var(--brand-red);margin-bottom:0.25rem;">Step 1 — Today</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Create Business Profile</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Register and complete your business profile with financials, documents, and description. Our team reviews within 2 business days.</div>
      </div>
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:var(--brand-red);margin-bottom:0.25rem;">Step 2 — By Week 1</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Profile Activation &amp; Matching</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Once approved, your profile is live. Our algorithm matches you with relevant buyers and investors based on industry, size, and location.</div>
      </div>
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:var(--brand-red);margin-bottom:0.25rem;">Step 3 — By Week 2-3</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Buyer Introductions</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Receive and review interest from verified buyers. Accept proposals that fit your criteria.</div>
      </div>
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:var(--brand-red);margin-bottom:0.25rem;">Step 4 — By Week 3-4</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Share Documents</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Share confidential documents with serious buyers after mutual NDA. Conduct management discussions.</div>
      </div>
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:var(--brand-red);margin-bottom:0.25rem;">Step 5 — By Month 3</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Due Diligence &amp; Closure</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Complete financial and legal due diligence. Finalize deal terms and close with your advisors.</div>
      </div>
    </div>

    <div style="margin-top:2rem; display:flex; gap:1rem; flex-wrap:wrap;">
      <a href="<?= APP_URL ?>/signup" class="btn btn-primary">Create Business Profile</a>
      <a href="<?= APP_URL ?>/business-valuation" class="btn btn-secondary">Value My Business First</a>
    </div>
  </div>

  <!-- Buy path -->
  <div class="persona-content" id="content-buy" style="display:none;">
    <h2>Buy a Business</h2>
    <p style="font-size:1.05rem;color:var(--secondary-text);">Browse thousands of pre-screened businesses for sale across 900+ industries and 170+ countries.</p>

    <div style="display:flex;flex-direction:column;gap:1.25rem;margin-top:1.5rem;">
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:var(--brand-blue);margin-bottom:0.25rem;">Step 1 — Today</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Create Investor Profile</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Register and define your acquisition criteria: industry, location, size, and budget. Get verified.</div>
      </div>
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:var(--brand-blue);margin-bottom:0.25rem;">Step 2 — By Week 1</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Browse &amp; Get Matched</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Search businesses for sale or receive smart matches based on your mandate. Save favorites.</div>
      </div>
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:var(--brand-blue);margin-bottom:0.25rem;">Step 3 — By Week 2-3</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Express Interest</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Send proposals to businesses you're interested in. Sellers review your verified profile.</div>
      </div>
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:var(--brand-blue);margin-bottom:0.25rem;">Step 4 — By Week 3-4</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">NDA &amp; Documents</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Sign NDA through platform. Access confidential financial documents and data room.</div>
      </div>
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:var(--brand-blue);margin-bottom:0.25rem;">Step 5 — By Month 3</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Due Diligence &amp; Close</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Conduct site visits, financial audits, legal review. Finalize acquisition with advisors.</div>
      </div>
    </div>

    <div style="margin-top:2rem;">
      <a href="<?= APP_URL ?>/browse/businesses" class="btn btn-primary">Browse Businesses for Sale</a>
      <a href="<?= APP_URL ?>/signup" class="btn btn-secondary" style="margin-left:0.5rem;">Create Investor Profile</a>
    </div>
  </div>

  <!-- Invest path -->
  <div class="persona-content" id="content-invest" style="display:none;">
    <h2>Invest in a Business</h2>
    <p style="font-size:1.05rem;color:var(--secondary-text);">Discover vetted investment opportunities — from startups to established businesses seeking growth capital.</p>

    <div style="display:flex;flex-direction:column;gap:1.25rem;margin-top:1.5rem;">
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:var(--success);margin-bottom:0.25rem;">Step 1</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Register as Investor</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Create your investor profile, define ticket size, sector preference, and investment stage.</div>
      </div>
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:var(--success);margin-bottom:0.25rem;">Step 2</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Discover Opportunities</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Browse investment opportunities or get weekly smart-match recommendations via email.</div>
      </div>
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:var(--success);margin-bottom:0.25rem;">Step 3</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Connect &amp; Evaluate</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Express interest, review documents, attend management presentations.</div>
      </div>
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:var(--success);margin-bottom:0.25rem;">Step 4</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Invest &amp; Monitor</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Complete investment, receive post-investment updates through the platform.</div>
      </div>
    </div>

    <div style="margin-top:2rem;">
      <a href="<?= APP_URL ?>/signup" class="btn btn-primary">Register as Investor</a>
      <a href="<?= APP_URL ?>/browse/businesses?type=investment" class="btn btn-secondary" style="margin-left:0.5rem;">View Investment Opportunities</a>
    </div>
  </div>

  <!-- Franchise path -->
  <div class="persona-content" id="content-franchise" style="display:none;">
    <h2>Franchise Your Business</h2>
    <p style="font-size:1.05rem;color:var(--secondary-text);">Expand your brand through franchising. Connect with qualified franchisees looking for opportunities.</p>

    <div style="display:flex;flex-direction:column;gap:1.25rem;margin-top:1.5rem;">
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:#B45309;margin-bottom:0.25rem;">Step 1</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Create Franchise Profile</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">List your brand with franchise details — investment required, space, training, and support.</div>
      </div>
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:#B45309;margin-bottom:0.25rem;">Step 2</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Get Discovered</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Your brand appears in franchise searches and gets recommended to franchise investors.</div>
      </div>
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:#B45309;margin-bottom:0.25rem;">Step 3</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Screen Candidates</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Review franchise applications, evaluate candidates' background and financial capacity.</div>
      </div>
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:#B45309;margin-bottom:0.25rem;">Step 4</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Expand Your Network</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Sign franchise agreements and grow your brand presence across new locations.</div>
      </div>
    </div>

    <div style="margin-top:2rem;">
      <a href="<?= APP_URL ?>/signup" class="btn btn-primary">Create Franchise Profile</a>
      <a href="<?= APP_URL ?>/browse/franchises" class="btn btn-secondary" style="margin-left:0.5rem;">View Franchise Opportunities</a>
    </div>
  </div>

  <!-- Advisor path -->
  <div class="persona-content" id="content-advisor" style="display:none;">
    <h2>Register as an Advisor</h2>
    <p style="font-size:1.05rem;color:var(--secondary-text);">M&A advisors, business brokers, financial consultants, accountants, and law firms — help businesses close deals.</p>

    <div style="display:flex;flex-direction:column;gap:1.25rem;margin-top:1.5rem;">
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:var(--brand-blue);margin-bottom:0.25rem;">Step 1</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Create Advisor Profile</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Register with your credentials, specialization (M&A, valuation, legal, accounting), and track record.</div>
      </div>
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:var(--brand-blue);margin-bottom:0.25rem;">Step 2</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Get Discovered</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Business owners and investors search for advisors by expertise, industry, and location.</div>
      </div>
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:var(--brand-blue);margin-bottom:0.25rem;">Step 3</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Engage &amp; Advise</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Receive mandates, provide valuation, due diligence, deal structuring, and negotiation support.</div>
      </div>
      <div style="background:var(--surface);border-radius:16px;padding:1.25rem;border:1px solid var(--surface-container-high);">
        <div style="font-size:0.8rem;font-weight:600;color:var(--brand-blue);margin-bottom:0.25rem;">Step 4</div>
        <div style="font-weight:700;margin-bottom:0.35rem;">Close Deals</div>
        <div style="font-size:0.88rem;color:var(--secondary-text);">Guide transactions to successful closure. Build your reputation through client ratings.</div>
      </div>
    </div>

    <div style="margin-top:2rem;">
      <a href="<?= APP_URL ?>/signup" class="btn btn-primary">Register as Advisor</a>
    </div>
  </div>

  <!-- Service tiers -->
  <div style="margin-top:3rem; padding:2rem; background:var(--surface-container); border-radius:2rem;">
    <h3 style="text-align:center;">Select Your Service Level</h3>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:1rem; margin-top:1.5rem;">
      <div class="card" style="text-align:center;">
        <h4>Basic</h4>
        <div style="font-size:2rem;font-weight:700;">Free</div>
        <ul style="text-align:left;font-size:0.85rem;list-style:disc;padding-left:1.25rem;">
          <li>Profile creation</li>
          <li>Browse listings</li>
          <li>Basic search</li>
        </ul>
      </div>
      <div class="card" style="text-align:center;border-color:var(--brand-red);">
        <h4>Premium</h4>
        <div style="font-size:2rem;font-weight:700;">NPR 25,500</div>
        <ul style="text-align:left;font-size:0.85rem;list-style:disc;padding-left:1.25rem;">
          <li>Priority visibility</li>
          <li>Unlimited connections</li>
          <li>Analytics dashboard</li>
          <li>1% finder's fee post close</li>
        </ul>
      </div>
      <div class="card" style="text-align:center;">
        <h4>Enterprise</h4>
        <div style="font-size:2rem;font-weight:700;">NPR 2.55 L</div>
        <ul style="text-align:left;font-size:0.85rem;list-style:disc;padding-left:1.25rem;">
          <li>Dedicated relationship manager</li>
          <li>CIM &amp; valuation reports</li>
          <li>Priority support</li>
          <li>1% finder's fee post close</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
function switchPersona(id, btn) {
  document.querySelectorAll('.persona-content').forEach(function(el) { el.style.display = 'none'; });
  document.querySelectorAll('.persona-tab').forEach(function(el) {
    el.style.background = 'var(--surface)';
    el.style.color = 'var(--dark)';
  });
  document.getElementById('content-' + id).style.display = 'block';
  btn.style.background = 'var(--brand-red)';
  btn.style.color = '#fff';
}
var hash = window.location.hash.replace('#', '');
if (hash && ['sell','buy','invest','franchise','advisor'].indexOf(hash) !== -1) {
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
