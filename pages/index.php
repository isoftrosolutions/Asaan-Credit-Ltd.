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

$featured_biz = db()->query("SELECT * FROM businesses WHERE is_published=1 AND is_featured=1 ORDER BY rating DESC LIMIT 6")->fetchAll();
$featured_pitches = db()->query("SELECT p.*, s.name as sector_name FROM pitches p LEFT JOIN sectors s ON p.sector_id = s.id WHERE p.is_published=1 AND p.is_featured=1 ORDER BY p.id DESC LIMIT 6")->fetchAll();
$faqs = db()->query("SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order LIMIT 4")->fetchAll();

$pageTitle = APP_NAME;
require __DIR__ . '/../includes/header.php';
?>
<main>
<!-- Hero Section -->
<section class="relative overflow-hidden min-h-[500px] md:min-h-[600px] flex items-center">
  <div class="absolute inset-0 z-0" style="background-image:url('/assets/hero.jpeg');background-size:cover;background-position:center;"></div>
  <div class="absolute inset-0 z-[1]" style="background:linear-gradient(135deg, #00263f 0%, rgba(0,38,63,0.85) 50%, rgba(0,38,63,0.3) 100%);"></div>
  <div class="max-w-[1200px] mx-auto px-[24px] w-full relative z-10 py-[48px] md:py-[80px]">
    <div class="max-w-[580px] space-y-[24px]">
      <h1 class="text-[32px] md:text-[48px] leading-[38px] md:leading-[56px] font-[800] tracking-[-0.02em] text-white" style="font-family:Montserrat,sans-serif;">
        <?= $hero_title ?>
      </h1>
      <p class="text-[18px] leading-[28px] text-white opacity-90" style="font-family:Inter,sans-serif;">
        <?= e($hero_subtitle) ?>
      </p>
      <div class="flex flex-col sm:flex-row gap-[16px] pt-[16px]">
        <a href="<?= APP_URL ?>/signup" class="inline-block bg-[#98202A] text-white px-[32px] py-[12px] rounded-lg font-[600] text-[16px] leading-[24px] hover:brightness-110 transition-all shadow-md active:scale-95 text-center" style="font-family:Inter,sans-serif;">
          I'm an Investor
        </a>
        <a href="<?= APP_URL ?>/signup" class="inline-block border border-white/40 text-white px-[32px] py-[12px] rounded-lg font-[600] text-[16px] leading-[24px] hover:bg-white/10 hover:border-white/70 transition-all active:scale-95 text-center backdrop-blur-sm" style="font-family:Inter,sans-serif;">
          I'm an Entrepreneur
        </a>
      </div>
    </div>
  </div>
</section>

<!-- Stats Bar -->
<section class="py-[16px] border-b" style="background:#f6f3f2;border-color:#dbc0bf;">
  <div class="max-w-[1200px] mx-auto px-[24px] flex flex-col md:flex-row justify-between items-center text-center gap-[16px]">
    <div class="flex items-center gap-[8px]">
      <span class="material-symbols-outlined" style="color:#3b6281;font-variation-settings:'FILL' 1;">verified_user</span>
      <p class="text-[20px] leading-[28px] font-[600]" style="color:#3b6281;font-family:Montserrat,sans-serif;"><?= e($stats_investors) ?> <span class="text-[16px] leading-[24px] font-[400]" style="color:#554242;font-family:Inter,sans-serif;">Verified Investors</span></p>
    </div>
    <div class="hidden md:block h-8 w-px" style="background:#dbc0bf;"></div>
    <div class="flex items-center gap-[8px]">
      <span class="material-symbols-outlined" style="color:#3b6281;font-variation-settings:'FILL' 1;">rocket_launch</span>
      <p class="text-[20px] leading-[28px] font-[600]" style="color:#3b6281;font-family:Montserrat,sans-serif;"><?= e($stats_businesses) ?> <span class="text-[16px] leading-[24px] font-[400]" style="color:#554242;font-family:Inter,sans-serif;">Active Pitches</span></p>
    </div>
    <div class="hidden md:block h-8 w-px" style="background:#dbc0bf;"></div>
    <div class="flex items-center gap-[8px]">
      <span class="material-symbols-outlined" style="color:#3b6281;font-variation-settings:'FILL' 1;">handshake</span>
      <p class="text-[20px] leading-[28px] font-[600]" style="color:#3b6281;font-family:Montserrat,sans-serif;"><?= e($stats_matches) ?> <span class="text-[16px] leading-[24px] font-[400]" style="color:#554242;font-family:Inter,sans-serif;">Successful Matches</span></p>
    </div>
  </div>
</section>

<!-- Wave Divider -->
<div style="background:linear-gradient(90deg,#6B1D22 0%,#1E4866 100%);height:4px;width:100%;"></div>

<!-- How It Works -->
<section class="py-[48px]" style="background:#fcf9f8;">
  <div class="max-w-[1200px] mx-auto px-[24px]">
    <div class="text-center mb-[48px]">
      <h2 class="text-[32px] leading-[40px] font-[700] tracking-[-0.01em] mb-[16px]" style="color:#6B1D22;font-family:Montserrat,sans-serif;">How It Works</h2>
      <p class="text-[16px] leading-[24px]" style="color:#554242;font-family:Inter,sans-serif;">A streamlined three-step journey to your next big opportunity.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-[24px]">
      <div class="group p-[24px] bg-white rounded-xl shadow-sm border transition-all duration-300 text-center hover:shadow-md" style="border-color:#dbc0bf4d;">
        <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-[24px] group-hover:scale-110 transition-transform" style="background:#f0eded;">
          <span class="material-symbols-outlined text-3xl" style="color:#6B1D22;">person_add</span>
        </div>
        <h3 class="text-[20px] leading-[28px] font-[600] mb-[16px]" style="color:#00263f;font-family:Montserrat,sans-serif;">Step 1</h3>
        <p class="font-[600] text-[16px] leading-[24px] mb-[8px]" style="color:#1c1b1b;font-family:Inter,sans-serif;">Create your profile</p>
        <p class="text-[14px] leading-[20px]" style="color:#554242;font-family:Inter,sans-serif;">Detailed background to ensure quality and intent.</p>
      </div>
      <div class="group p-[24px] bg-white rounded-xl shadow-sm border transition-all duration-300 text-center hover:shadow-md" style="border-color:#dbc0bf4d;">
        <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-[24px] group-hover:scale-110 transition-transform" style="background:#f0eded;">
          <span class="material-symbols-outlined text-3xl" style="color:#3b6281;">verified</span>
        </div>
        <h3 class="text-[20px] leading-[28px] font-[600] mb-[16px]" style="color:#00263f;font-family:Montserrat,sans-serif;">Step 2</h3>
        <p class="font-[600] text-[16px] leading-[24px] mb-[8px]" style="color:#1c1b1b;font-family:Inter,sans-serif;">Get verified by our team</p>
        <p class="text-[14px] leading-[20px]" style="color:#554242;font-family:Inter,sans-serif;">Strict compliance checks for maximum security.</p>
      </div>
      <div class="group p-[24px] bg-white rounded-xl shadow-sm border transition-all duration-300 text-center hover:shadow-md" style="border-color:#dbc0bf4d;">
        <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-[24px] group-hover:scale-110 transition-transform" style="background:#f0eded;">
          <span class="material-symbols-outlined text-3xl" style="color:#6B1D22;">handshake</span>
        </div>
        <h3 class="text-[20px] leading-[28px] font-[600] mb-[16px]" style="color:#00263f;font-family:Montserrat,sans-serif;">Step 3</h3>
        <p class="font-[600] text-[16px] leading-[24px] mb-[8px]" style="color:#1c1b1b;font-family:Inter,sans-serif;">Connect with the right match</p>
        <p class="text-[14px] leading-[20px]" style="color:#554242;font-family:Inter,sans-serif;">Direct messaging and deal-flow management.</p>
      </div>
    </div>
  </div>
</section>

<!-- Dual Path Cards -->
<section class="py-[48px]" style="background:#ffffff;">
  <div class="max-w-[1200px] mx-auto px-[24px]">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-[48px]">
      <div class="relative overflow-hidden rounded-xl p-[24px] border group" style="background:#ffdad933;border-color:#ffb3b2;">
        <div class="absolute -right-10 -top-10 opacity-10 group-hover:scale-110 transition-transform duration-700">
          <span class="material-symbols-outlined text-[160px]">finance_chip</span>
        </div>
        <div class="relative z-10">
          <h3 class="text-[32px] leading-[40px] font-[700] tracking-[-0.01em] mb-[16px]" style="color:#6B1D22;font-family:Montserrat,sans-serif;">For Investors</h3>
          <p class="text-[16px] leading-[24px] mb-[24px] max-w-[400px]" style="color:#554242;font-family:Inter,sans-serif;">
            Access vetted startups from Nepal across diverse sectors including Agriculture, SaaS, and Energy. View pitch decks and financial reports instantly.
          </p>
          <ul class="space-y-[8px] mb-[24px]">
            <li class="flex items-center gap-[4px] text-[12px] leading-[16px] font-[600] tracking-[0.05em]" style="color:#1c1b1b;font-family:Inter,sans-serif;">
              <span class="material-symbols-outlined text-sm" style="color:#6B1D22;font-variation-settings:'FILL' 1;">check_circle</span>
              Pre-vetted Opportunities
            </li>
            <li class="flex items-center gap-[4px] text-[12px] leading-[16px] font-[600] tracking-[0.05em]" style="color:#1c1b1b;font-family:Inter,sans-serif;">
              <span class="material-symbols-outlined text-sm" style="color:#6B1D22;font-variation-settings:'FILL' 1;">check_circle</span>
              Direct Entrepreneur Access
            </li>
          </ul>
          <a href="<?= APP_URL ?>/signup" class="inline-block px-[24px] py-[12px] rounded-lg font-[600] text-[16px] leading-[24px] text-white hover:brightness-110 transition-all" style="background:#6B1D22;font-family:Inter,sans-serif;">Start Investing</a>
        </div>
      </div>
      <div class="relative overflow-hidden rounded-xl p-[24px] border group" style="background:#cce5ff4d;border-color:#a4cbef;">
        <div class="absolute -right-10 -top-10 opacity-10 group-hover:scale-110 transition-transform duration-700">
          <span class="material-symbols-outlined text-[160px]">rocket</span>
        </div>
        <div class="relative z-10">
          <h3 class="text-[32px] leading-[40px] font-[700] tracking-[-0.01em] mb-[16px]" style="color:#3b6281;font-family:Montserrat,sans-serif;">For Entrepreneurs</h3>
          <p class="text-[16px] leading-[24px] mb-[24px] max-w-[400px]" style="color:#554242;font-family:Inter,sans-serif;">
            List your venture and get matched with professional investors who understand the Nepalese market. Secure funding to scale your vision.
          </p>
          <ul class="space-y-[8px] mb-[24px]">
            <li class="flex items-center gap-[4px] text-[12px] leading-[16px] font-[600] tracking-[0.05em]" style="color:#1c1b1b;font-family:Inter,sans-serif;">
              <span class="material-symbols-outlined text-sm" style="color:#3b6281;font-variation-settings:'FILL' 1;">check_circle</span>
              Visibility to HNIs
            </li>
            <li class="flex items-center gap-[4px] text-[12px] leading-[16px] font-[600] tracking-[0.05em]" style="color:#1c1b1b;font-family:Inter,sans-serif;">
              <span class="material-symbols-outlined text-sm" style="color:#3b6281;font-variation-settings:'FILL' 1;">check_circle</span>
              Fundraising Assistance
            </li>
          </ul>
          <a href="<?= APP_URL ?>/signup" class="inline-block px-[24px] py-[12px] rounded-lg font-[600] text-[16px] leading-[24px] text-white hover:brightness-110 transition-all" style="background:#3b6281;font-family:Inter,sans-serif;">Pitch Your Idea</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Featured Businesses -->
<?php if (!empty($featured_biz)): ?>
<section class="py-[48px]" style="background:#fcf9f8;">
  <div class="max-w-[1200px] mx-auto px-[24px]">
    <div class="flex justify-between items-end mb-[24px] gap-[16px]">
      <div>
        <h2 class="text-[32px] leading-[40px] font-[700] tracking-[-0.01em]" style="color:#6B1D22;font-family:Montserrat,sans-serif;">Featured Businesses</h2>
        <p class="text-[16px] leading-[24px]" style="color:#554242;font-family:Inter,sans-serif;">Hand-picked opportunities from verified business owners.</p>
      </div>
      <a href="<?= APP_URL ?>/browse/businesses" class="hidden sm:inline-block px-[24px] py-[8px] rounded-lg font-[600] text-[14px] border hover:brightness-110 transition-all flex-shrink-0" style="border-color:#887271;color:#1c1b1b;font-family:Inter,sans-serif;">View All</a>
    </div>
    <div class="flex gap-[16px] overflow-x-auto pb-[8px]">
      <?php foreach ($featured_biz as $biz): ?>
      <div class="flex-shrink-0 w-[300px] bg-white rounded-xl shadow-sm border p-[16px] cursor-pointer transition-all duration-300 hover:shadow-md" style="border-color:#dbc0bf4d;border-left:4px solid #6B1D22;" onclick="location.href='<?= APP_URL ?>/business/<?= (int)$biz['id'] ?>'">
        <div class="flex justify-between items-start mb-[12px]">
          <span class="inline-flex items-center gap-[4px] px-[8px] py-[3px] text-[11px] font-[700] uppercase tracking-[0.02em] rounded-full" style="background:rgba(30,122,77,0.1);color:#1E7A4D;">Business for Sale</span>
          <?php if (!empty($biz['rating'])): ?>
          <span class="inline-flex items-center gap-[3px] px-[8px] py-[3px] text-[12px] font-[700] rounded-full" style="background:rgba(199,122,18,0.12);color:#C77A12;"><?= e($biz['rating']) ?></span>
          <?php endif; ?>
        </div>
        <h4 class="text-[16px] font-[700] mb-[6px]" style="color:#1c1b1b;font-family:Montserrat,sans-serif;"><?= e($biz['business_name']) ?></h4>
        <p class="text-[13px] leading-[1.5] mb-[12px]" style="color:#554242;font-family:Inter,sans-serif;"><?= e(mb_substr($biz['description'] ?? '', 0, 120)) ?></p>
        <div class="flex gap-[8px] justify-between items-center flex-wrap pt-[12px]" style="border-top:1px solid #dbc0bf4d;">
          <div><span class="text-[11px] font-[600]" style="color:#554242;display:block;">Run Rate</span><span class="text-[14px] font-[700]" style="color:#1c1b1b;"><?= money($biz['annual_revenue']) ?></span></div>
          <?php if (!empty($biz['ebitda_pct'])): ?>
          <div><span class="text-[11px] font-[600]" style="color:#554242;display:block;">EBITDA</span><span class="text-[14px] font-[700]" style="color:#1c1b1b;"><?= e($biz['ebitda_pct']) ?>%</span></div>
          <?php endif; ?>
          <?php if (!empty($biz['asking_price'])): ?>
          <div class="w-full pt-[8px]"><strong class="text-[16px]" style="color:#7d2a2e;">Asking <?= money($biz['asking_price']) ?></strong></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-[16px] sm:hidden">
      <a href="<?= APP_URL ?>/browse/businesses" class="inline-block px-[24px] py-[8px] rounded-lg font-[600] text-[14px] border" style="border-color:#887271;color:#1c1b1b;font-family:Inter,sans-serif;">View All</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Featured Pitches -->
<?php if (!empty($featured_pitches)): ?>
<section class="py-[48px]" style="background:#ffffff;">
  <div class="max-w-[1200px] mx-auto px-[24px]">
    <div class="flex justify-between items-end mb-[24px] gap-[16px]">
      <div>
        <h2 class="text-[32px] leading-[40px] font-[700] tracking-[-0.01em]" style="color:#3b6281;font-family:Montserrat,sans-serif;">Featured Investment Opportunities</h2>
        <p class="text-[16px] leading-[24px]" style="color:#554242;font-family:Inter,sans-serif;">Pre-verified entrepreneurs seeking capital for growth.</p>
      </div>
      <a href="<?= APP_URL ?>/browse/entrepreneurs" class="hidden sm:inline-block px-[24px] py-[8px] rounded-lg font-[600] text-[14px] border hover:brightness-110 transition-all flex-shrink-0" style="border-color:#887271;color:#1c1b1b;font-family:Inter,sans-serif;">View All</a>
    </div>
    <div class="flex gap-[16px] overflow-x-auto pb-[8px]">
      <?php foreach ($featured_pitches as $p): ?>
      <div class="flex-shrink-0 w-[300px] bg-white rounded-xl shadow-sm border p-[16px] cursor-pointer transition-all duration-300 hover:shadow-md" style="border-color:#dbc0bf4d;border-left:4px solid #3b6281;" onclick="location.href='<?= APP_URL ?>/pitch/<?= (int)$p['id'] ?>'">
        <div class="mb-[12px]">
          <span class="inline-flex items-center gap-[4px] px-[8px] py-[3px] text-[11px] font-[700] uppercase tracking-[0.02em] rounded-full" style="background:rgba(30,72,102,0.1);color:#1E4866;">Seeking Investment</span>
        </div>
        <h4 class="text-[16px] font-[700] mb-[6px]" style="color:#1c1b1b;font-family:Montserrat,sans-serif;"><?= e($p['tagline']) ?></h4>
        <p class="text-[13px] leading-[1.5] mb-[12px]" style="color:#554242;font-family:Inter,sans-serif;"><?= e(mb_substr($p['short_summary'] ?? $p['problem_statement'] ?? '', 0, 120)) ?></p>
        <div class="flex gap-[4px] mb-[8px] flex-wrap">
          <?php if (!empty($p['sector_name'])): ?>
          <span class="inline-flex px-[6px] py-[2px] text-[11px] font-[600] rounded" style="background:#f0eded;color:#554242;"><?= e($p['sector_name']) ?></span>
          <?php endif; ?>
          <?php if (!empty($p['stage'])): ?>
          <span class="inline-flex px-[6px] py-[2px] text-[11px] font-[600] rounded" style="background:#f0eded;color:#554242;"><?= e(ucfirst($p['stage'])) ?></span>
          <?php endif; ?>
        </div>
        <div class="flex gap-[8px] justify-between items-center flex-wrap pt-[12px]" style="border-top:1px solid #dbc0bf4d;">
          <div><span class="text-[11px] font-[600]" style="color:#554242;display:block;">Funding</span><span class="text-[14px] font-[700]" style="color:#1c1b1b;"><?= money($p['funding_amount']) ?></span></div>
          <?php if (!empty($p['equity_offered'])): ?>
          <div><span class="text-[11px] font-[600]" style="color:#554242;display:block;">Equity</span><span class="text-[14px] font-[700]" style="color:#1c1b1b;"><?= e($p['equity_offered']) ?>%</span></div>
          <?php endif; ?>
          <div class="w-full pt-[8px]"><strong class="text-[16px]" style="color:#7d2a2e;">Valued at <?= money($p['valuation'] ?? 0) ?></strong></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-[16px] sm:hidden">
      <a href="<?= APP_URL ?>/browse/entrepreneurs" class="inline-block px-[24px] py-[8px] rounded-lg font-[600] text-[14px] border" style="border-color:#887271;color:#1c1b1b;font-family:Inter,sans-serif;">View All</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- FAQ -->
<section class="py-[48px]" style="background:#fcf9f8;">
  <div class="max-w-[720px] mx-auto px-[24px]">
    <div class="text-center mb-[48px]">
      <h2 class="text-[32px] leading-[40px] font-[700] tracking-[-0.01em] mb-[16px]" style="color:#6B1D22;font-family:Montserrat,sans-serif;">Frequently Asked Questions</h2>
      <p class="text-[16px] leading-[24px]" style="color:#554242;font-family:Inter,sans-serif;">Everything you need to know about <?= APP_NAME ?>.</p>
    </div>
    <?php $first = true; foreach ($faqs as $faq): ?>
    <div class="faq-item bg-white rounded-xl p-[16px] mb-[8px] border<?= $first ? ' faq-open' : '' ?>" style="border-color:#dbc0bf4d;<?= $first ? 'border-left:4px solid #6B1D22;' : '' ?>">
      <div class="faq-header flex justify-between items-center cursor-pointer font-[600] text-[16px] leading-[24px]" style="color:#1c1b1b;font-family:Inter,sans-serif;">
        <span><?= e($faq['question']) ?></span>
        <span class="faq-icon text-[20px] transition-transform duration-200" style="color:#554242;">+</span>
      </div>
      <div class="faq-answer mt-[12px] text-[14px] leading-[1.7]" style="display:<?= $first ? 'block' : 'none' ?>;color:#554242;font-family:Inter,sans-serif;">
        <?= e($faq['answer']) ?>
      </div>
    </div>
    <?php $first = false; endforeach; ?>
    <div class="text-center mt-[24px]">
      <a href="<?= APP_URL ?>/support" class="inline-block px-[24px] py-[10px] rounded-lg font-[600] text-[14px] border hover:brightness-110 transition-all" style="border-color:#887271;color:#1c1b1b;font-family:Inter,sans-serif;">View all FAQs</a>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="py-[48px]" style="background:#ffffff;">
  <div class="max-w-[1200px] mx-auto px-[24px]">
    <div class="rounded-2xl py-[48px] px-[24px] text-center" style="background:linear-gradient(135deg,#6B1D22,#4A1317);">
      <h2 class="text-[32px] leading-[40px] font-[700] tracking-[-0.01em] text-white mb-[16px]" style="font-family:Montserrat,sans-serif;">Ready to grow your business?</h2>
      <p class="text-[16px] leading-[24px] text-white opacity-80 mb-[24px]" style="font-family:Inter,sans-serif;">Join <?= e($stats_businesses) ?> business owners and <?= e($stats_investors) ?> investors already on the platform.</p>
      <div class="flex gap-[16px] justify-center flex-wrap">
        <a href="<?= APP_URL ?>/signup" class="inline-block px-[32px] py-[12px] rounded-lg font-[600] text-[16px] leading-[24px] text-white hover:brightness-110 transition-all shadow-md" style="background:#98202A;font-family:Inter,sans-serif;">Get Started Free</a>
        <a href="<?= APP_URL ?>/browse/businesses" class="inline-block px-[32px] py-[12px] rounded-lg font-[600] text-[16px] leading-[24px] transition-all" style="border:1.5px solid rgba(255,255,255,0.3);color:rgba(255,255,255,0.9);font-family:Inter,sans-serif;">Browse Listings</a>
      </div>
    </div>
  </div>
</section>
</main>

<script>
document.querySelectorAll('.faq-header').forEach(header => {
  header.addEventListener('click', () => {
    const item = header.parentElement;
    const answer = item.querySelector('.faq-answer');
    const icon = item.querySelector('.faq-icon');
    const isOpen = item.classList.contains('faq-open');
    item.classList.toggle('faq-open');
    answer.style.display = isOpen ? 'none' : 'block';
    icon.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(45deg)';
  });
});

const observerOptions = { threshold: 0.1 };
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('opacity-100', 'translate-y-0');
      entry.target.classList.remove('opacity-0', 'translate-y-4');
    }
  });
}, observerOptions);
document.querySelectorAll('.group, h2, .faq-item').forEach(el => {
  if (!el.classList.contains('faq-item')) {
    el.classList.add('transition-all', 'duration-700', 'opacity-0', 'translate-y-4');
    observer.observe(el);
  }
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
