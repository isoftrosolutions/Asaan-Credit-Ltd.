<?php
require __DIR__ . '/../config/bootstrap.php';

$pageTitle = 'About Us — ' . APP_NAME_LONG;
$pageDescription = 'Asaan Capital Ltd is Nepal\'s leading financial advisory and investment company. Learn our story, mission, values, and how we empower businesses and investors.';
$forcePublicHeader = true;
require __DIR__ . '/../includes/header.php';
?>
<main class="pub-page">

  <!-- ─── HERO ─── -->
  <section class="pub-hero" style="padding:80px 0;background:linear-gradient(135deg, var(--color-primary) 0%, #4A1317 100%);color:#fff;overflow:hidden;position:relative;">
    <div style="position:absolute;inset:0;opacity:.12;background-image:url('<?= APP_URL ?>/assets/about-team.jpg');background-size:cover;background-position:center;"></div>
    <div class="pub-wrap" style="position:relative;z-index:1;">
      <div style="max-width:760px;">
        <span style="display:inline-block;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.7);margin-bottom:12px;">About Asaan Capital Ltd</span>
        <h1 style="font-family:var(--font-heading);font-weight:800;font-size:clamp(2.2rem,5vw,3.2rem);line-height:1.12;letter-spacing:-.02em;color:#fff;margin:0 0 16px;">Trusted Capital,<br>Secure Future.</h1>
        <p style="font-size:1.1rem;line-height:1.7;opacity:.88;max-width:600px;">Nepal's trusted financial services company — bridging capital seekers with capital providers through integrity, transparency, and professional excellence.</p>
      </div>
    </div>
  </section>

  <!-- ─── COMPANY OVERVIEW ─── -->
  <section class="pub-section">
    <div class="pub-wrap">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center;">
        <div>
          <span class="pub-eyebrow">Company Overview</span>
          <h2 class="pub-h2" style="margin-bottom:16px;">Who We Are</h2>
          <p class="pub-text" style="font-size:1rem;margin-bottom:16px;">
            <strong>Asaan Capital Ltd</strong> is a Birgunj-based financial services company and the operator of Nepal's first dedicated online marketplace for business matching, M&A, and fundraising. We bridge the gap between capital seekers and capital providers — making business transactions <em>asaan</em> (easy).
          </p>
          <p class="pub-text" style="font-size:1rem;margin-bottom:16px;">
            Registered and regulated in Nepal, we serve a growing network of entrepreneurs, investors, franchisors, and advisors across all seven provinces. Our platform provides end-to-end solutions including <strong>Investment Advisory, Business Valuation, Due Diligence, Capital Raising, and Corporate Advisory</strong>.
          </p>
          <p class="pub-text" style="font-size:1rem;">
            Since our founding, we have facilitated financial transactions worth hundreds of crores and advised dozens of businesses across agriculture, technology, manufacturing, tourism, and services sectors.
          </p>
        </div>
        <div style="position:relative;">
          <img src="<?= APP_URL ?>/assets/about-team.jpg" alt="Asaan Capital Team" style="width:100%;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.12);display:block;">
          <div style="position:absolute;bottom:-24px;right:-24px;background:var(--color-primary);color:#fff;padding:20px 28px;border-radius:16px;text-align:center;box-shadow:0 8px 30px rgba(107,29,34,.3);">
            <div style="font-family:var(--font-heading);font-weight:800;font-size:2rem;line-height:1;">2024</div>
            <div style="font-size:.8rem;opacity:.85;">Founded</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ─── MISSION & VISION ─── -->
  <section class="pub-section tint">
    <div class="pub-wrap">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;">
        <div style="background:var(--dash-card);border:1px solid var(--dash-border);border-radius:20px;padding:40px;box-shadow:var(--dash-shadow);">
          <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:16px;background:rgba(107,29,34,.1);color:var(--color-primary);margin-bottom:20px;">
            <span class="material-symbols-outlined" style="font-size:28px;">visibility</span>
          </div>
          <h3 class="pub-h3" style="margin-bottom:10px;">Our Vision</h3>
          <p class="pub-text" style="font-size:.95rem;line-height:1.7;">To become Nepal's most trusted investment and financial advisory company, fostering sustainable economic growth and investment excellence across the nation.</p>
        </div>
        <div style="background:var(--dash-card);border:1px solid var(--dash-border);border-radius:20px;padding:40px;box-shadow:var(--dash-shadow);">
          <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:16px;background:rgba(107,29,34,.1);color:var(--color-primary);margin-bottom:20px;">
            <span class="material-symbols-outlined" style="font-size:28px;">target</span>
          </div>
          <h3 class="pub-h3" style="margin-bottom:10px;">Our Mission</h3>
          <p class="pub-text" style="font-size:.95rem;line-height:1.7;">To empower businesses and investors by providing transparent, innovative, and sustainable financial solutions that create long-term value for all stakeholders.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ─── CORE VALUES ─── -->
  <section class="pub-section">
    <div class="pub-wrap">
      <div class="pub-section-head">
        <span class="pub-eyebrow">Our Core Values</span>
        <h2 class="pub-h2">What Drives Us</h2>
        <p class="pub-lead">Our values shape every decision, partnership, and solution we deliver.</p>
      </div>
      <div class="pub-grid cols-3" style="gap:24px;">
        <?php $values = [
          ['verified', 'Integrity', 'We uphold the highest ethical standards in every transaction and relationship.'],
          ['visibility', 'Transparency', 'Clear communication, honest dealings, and no hidden agendas.'],
          ['workspace_premium', 'Professionalism', 'Excellence in service delivery with a client-first mindset.'],
          ['lightbulb', 'Innovation', 'Leveraging modern technology and creative thinking for better outcomes.'],
          ['diversity_3', 'Client First', 'Your goals are our priority. Every solution is tailored to your needs.'],
          ['award_star', 'Accountability', 'We take ownership and deliver on our commitments, every time.'],
        ]; foreach ($values as $v): ?>
        <div class="pub-feature">
          <div class="pub-feature-ico"><span class="material-symbols-outlined"><?= $v[0] ?></span></div>
          <div class="pub-feature-title"><?= $v[1] ?></div>
          <div class="pub-feature-text"><?= $v[2] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ─── WHAT WE DO ─── -->
  <section class="pub-section tint">
    <div class="pub-wrap">
      <div class="pub-section-head">
        <span class="pub-eyebrow">Our Services</span>
        <h2 class="pub-h2">What We Do</h2>
        <p class="pub-lead">Comprehensive financial solutions tailored to businesses, investors, and institutions.</p>
      </div>
      <div class="pub-grid cols-3" style="gap:20px;">
        <?php $services = [
          ['trending_up', 'Investment Advisory', 'Strategic investment guidance and portfolio solutions for individuals and institutions.'],
          ['account_balance', 'Business Valuation', 'Accurate, data-driven valuations for M&A, fundraising, and strategic planning.'],
          ['search_insights', 'Due Diligence', 'Comprehensive financial, legal, and operational due diligence services.'],
          ['payments', 'Capital Raising', 'Equity, debt, and hybrid capital raising through our extensive investor network.'],
          ['merge_type', 'M&A Advisory', 'End-to-end merger and acquisition advisory from identification to closure.'],
          ['savings', 'Project Finance', 'Structured finance solutions for infrastructure, energy, and development projects.'],
          ['account_balance', 'Loan Syndication', 'Arranging and structuring syndicated loans for large-scale business requirements.'],
          ['description', 'Business Planning', 'Professional business plans, financial models, and pitch decks for fundraising.'],
          ['business_center', 'Corporate Advisory', 'Strategic corporate finance advice including restructuring and growth planning.'],
        ]; foreach ($services as $s): ?>
        <div class="pub-card" style="padding:28px 24px;">
          <div style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:14px;background:rgba(107,29,34,.1);color:var(--color-primary);margin-bottom:16px;">
            <span class="material-symbols-outlined" style="font-size:24px;"><?= $s[0] ?></span>
          </div>
          <div class="pub-card-title" style="margin-bottom:6px;"><?= $s[1] ?></div>
          <div class="pub-card-text"><?= $s[2] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ─── WHY CHOOSE ASAN CAPITAL ─── -->
  <section class="pub-section">
    <div class="pub-wrap">
      <div class="pub-section-head">
        <span class="pub-eyebrow">Why Choose Us</span>
        <h2 class="pub-h2">The Asaan Capital Difference</h2>
        <p class="pub-lead">What sets us apart in Nepal's financial services landscape.</p>
      </div>
      <div class="pub-grid cols-3" style="gap:24px;">
        <?php $reasons = [
          ['groups', 'Experienced Team', 'Our professionals bring decades of combined experience in finance, investment, and advisory services.'],
          ['lock', 'Confidential Service', 'Every engagement is handled with the highest level of discretion and data protection.'],
          ['gavel', 'Ethical Practices', 'We adhere to strict ethical guidelines and regulatory compliance in all operations.'],
          ['hub', 'Strong Network', 'Access to a vast network of investors, financial institutions, and industry experts.'],
          ['design_services', 'Customized Solutions', 'Tailored financial strategies that match your unique business goals and circumstances.'],
          ['rocket_launch', 'Fast Decision Making', 'Agile processes and lean teams ensure quick turnaround without compromising quality.'],
        ]; foreach ($reasons as $r): ?>
        <div class="pub-feature">
          <div class="pub-feature-ico"><span class="material-symbols-outlined"><?= $r[0] ?></span></div>
          <div class="pub-feature-title"><?= $r[1] ?></div>
          <div class="pub-feature-text"><?= $r[2] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ─── OUR JOURNEY ─── -->
  <section class="pub-section tint">
    <div class="pub-wrap">
      <div class="pub-section-head">
        <span class="pub-eyebrow">Our Journey</span>
        <h2 class="pub-h2">How We Got Here</h2>
        <p class="pub-lead">From concept to national presence — our growth story.</p>
      </div>
      <div style="max-width:600px;margin:0 auto;">
        <?php $milestones = [
          ['2024', 'Platform Conceived & Built', 'Asaan Capital Ltd was founded. The marketplace platform was designed and developed to address Nepal\'s business matching gap.'],
          ['2025', 'Soft Launch & First Matches', 'Platform went live. First successful business-investor matches were facilitated. Initial partnerships established across key sectors.'],
          ['2026', 'Full Platform & Nationwide Presence', 'Nationwide expansion across all seven provinces. Comprehensive service suite launched. Hundreds of businesses and investors onboarded.'],
        ]; foreach ($milestones as $i => $m): ?>
        <div style="display:flex;gap:20px;padding-bottom:32px;position:relative;">
          <div style="display:flex;flex-direction:column;align-items:center;">
            <div style="width:48px;height:48px;border-radius:50%;background:var(--color-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.8rem;flex-shrink:0;position:relative;z-index:2;"><?= $m[0] ?></div>
            <?php if ($i < count($milestones) - 1): ?>
            <div style="width:2px;flex:1;background:var(--dash-border);margin:4px 0;"></div>
            <?php endif; ?>
          </div>
          <div style="padding-bottom:<?= $i < count($milestones) - 1 ? '0' : '0' ?>;">
            <h4 style="font-family:var(--font-heading);font-weight:700;font-size:1.05rem;color:var(--dash-ink);margin:6px 0 6px;"><?= $m[1] ?></h4>
            <p class="pub-text" style="font-size:.9rem;margin:0;"><?= $m[2] ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ─── OUR CLIENTS ─── -->
  <section class="pub-section">
    <div class="pub-wrap">
      <div class="pub-section-head">
        <span class="pub-eyebrow">Industries We Serve</span>
        <h2 class="pub-h2">Our Client Base</h2>
        <p class="pub-lead">We serve a diverse range of sectors across Nepal's economy.</p>
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;">
        <?php $industries = ['Agriculture', 'Technology', 'Manufacturing', 'Hydropower', 'Tourism & Hospitality', 'Trading', 'Finance & Banking', 'Real Estate', 'Healthcare', 'Education', 'Transportation', 'Construction']; foreach ($industries as $ind): ?>
        <span class="pub-badge" style="font-size:.82rem;padding:8px 18px;background:var(--dash-card);border:1px solid var(--dash-border);color:var(--dash-ink);text-transform:none;letter-spacing:normal;font-weight:500;"><?= $ind ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ─── ACHIEVEMENTS ─── -->
  <section class="pub-section" style="background:linear-gradient(135deg, var(--color-primary) 0%, #4A1317 100%);color:#fff;">
    <div class="pub-wrap">
      <div class="pub-section-head" style="color:#fff;">
        <span class="pub-eyebrow" style="color:rgba(255,255,255,.7);">Our Achievements</span>
        <h2 class="pub-h2" style="color:#fff;">By the Numbers</h2>
        <p class="pub-lead" style="color:rgba(255,255,255,.7);">Our impact across Nepal's business landscape.</p>
      </div>
      <div class="pub-statstrip">
        <?php $stats = [
          ['500+', 'Clients Served', 'facilitate investments, advisory, and matchmaking'],
          ['₹10B+', 'Transactions', 'facilitated across all service lines since inception'],
          ['100+', 'Projects', 'advisory and due diligence assignments completed'],
          ['20+', 'Partnerships', 'strategic alliances with financial institutions and firms'],
        ]; foreach ($stats as $s): ?>
        <div class="pub-statstrip-item" style="color:#fff;">
          <div class="pub-statstrip-num" style="color:#fff;font-size:2.4rem;"><?= $s[0] ?></div>
          <div class="pub-statstrip-label" style="color:rgba(255,255,255,.75);font-size:.9rem;font-weight:600;"><?= $s[1] ?></div>
          <div class="pub-statstrip-label" style="color:rgba(255,255,255,.55);font-size:.78rem;"><?= $s[2] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ─── CSR & SUSTAINABILITY ─── -->
  <section class="pub-section">
    <div class="pub-wrap">
      <div class="pub-section-head">
        <span class="pub-eyebrow">CSR & Sustainability</span>
        <h2 class="pub-h2">Giving Back</h2>
        <p class="pub-lead">Our commitment to Nepal's social and economic development.</p>
      </div>
      <div class="pub-grid cols-2" style="gap:24px;max-width:800px;margin:0 auto;">
        <?php $csr = [
          ['school', 'Financial Literacy', 'Conducting workshops and training programs to improve financial awareness among SMEs and aspiring entrepreneurs across Nepal.'],
          ['groups', 'SME Development', 'Supporting small and medium enterprises through mentorship, business planning assistance, and access to capital networks.'],
          ['rocket', 'Entrepreneurship', 'Nurturing the next generation of Nepali entrepreneurs through guidance, networking events, and startup support programs.'],
          ['volunteer_activism', 'Community Engagement', 'Active participation in local community development initiatives and responsible business practices.'],
        ]; foreach ($csr as $c): ?>
        <div class="pub-card" style="padding:28px 24px;display:flex;gap:16px;align-items:flex-start;">
          <div style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:14px;background:rgba(107,29,34,.1);color:var(--color-primary);flex-shrink:0;">
            <span class="material-symbols-outlined" style="font-size:24px;"><?= $c[0] ?></span>
          </div>
          <div>
            <div class="pub-card-title" style="margin-bottom:4px;"><?= $c[1] ?></div>
            <div class="pub-card-text"><?= $c[2] ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ─── CTA ─── -->
  <section class="pub-section">
    <div class="pub-wrap">
      <div class="pub-cta">
        <h2>Let's Build Your Financial Future Together</h2>
        <p style="max-width:520px;margin:0 auto 24px;">Contact Asaan Capital Ltd today to explore investment opportunities and strategic financial solutions tailored to your goals.</p>
        <div class="pub-cta-actions">
          <a href="<?= APP_URL ?>/contact" class="btn btn-primary" style="font-size:1rem;padding:14px 32px;border-radius:12px;background:#fff;color:var(--color-primary);font-weight:700;text-decoration:none;">Contact Us</a>
          <a href="<?= APP_URL ?>/onboarding" class="btn" style="font-size:1rem;padding:14px 32px;border-radius:12px;border:2px solid rgba(255,255,255,.4);color:#fff;font-weight:600;text-decoration:none;">Get Started</a>
        </div>
      </div>
    </div>
  </section>

</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
