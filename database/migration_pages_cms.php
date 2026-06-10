<?php
/**
 * Migration: create `pages` table for admin-editable CMS content.
 * Run once: php database/migration_pages_cms.php
 */

$db = new PDO(
    'mysql:host=localhost;dbname=invest_match;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$db->exec("
CREATE TABLE IF NOT EXISTS `pages` (
  `id`          bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug`        varchar(120) NOT NULL,
  `title`       varchar(255) NOT NULL,
  `content_html` mediumtext NOT NULL,
  `meta_description` varchar(320) DEFAULT NULL,
  `is_active`   tinyint(1) NOT NULL DEFAULT 1,
  `created_at`  timestamp NULL DEFAULT NULL,
  `updated_at`  timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$count = $db->query("SELECT COUNT(*) FROM pages")->fetchColumn();
if ($count > 0) {
    echo "Pages table already has $count rows. Skipping seed.\n";
    exit;
}

$now = date('Y-m-d H:i:s');
$pages = [
    [
        'slug' => 'about',
        'title' => 'Our Story',
        'content_html' => '<section class="pub-hero">
  <div class="pub-wrap-narrow">
    <div class="pub-breadcrumbs" style="font-size:0.82rem;color:var(--dash-ink-soft);margin-bottom:16px;">
      <a href="/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a>
      <span style="margin:0 6px;">/</span>
      <span>Our Story</span>
    </div>
    <div class="pub-section-head left" style="margin-bottom:0;">
      <h1 class="pub-h1">Nepal\'s trusted marketplace.<br>Built for business growth.</h1>
    </div>
  </div>
</section>

<section class="pub-section surface tight">
  <div class="pub-wrap-narrow">
    <div class="pub-prose">
      <p>For too long, Nepali business owners and investors have relied on fragmented networks, Facebook groups, and word-of-mouth to find the right opportunities. Asaan Capital Ltd changes that — a single, trusted platform where verified business owners, investors, franchise brands, and advisors connect and close deals with confidence.</p>
    </div>
  </div>
</section>

<section class="pub-section tint">
  <div class="pub-wrap-narrow">
    <div class="pub-grid cols-3">
      <div class="pub-card">
        <div class="pub-card-title">Why we exist</div>
        <p class="pub-card-text">Nepal\'s SME sector is full of potential — but fragmented. There is no centralized infrastructure for business matching, M&amp;A, or fundraising. Asaan Capital Ltd fills that gap with a secure, verified marketplace designed for Nepal.</p>
      </div>
      <div class="pub-card">
        <div class="pub-card-title">Our promise</div>
        <p class="pub-card-text">Every profile — business, investor, advisor, or franchise — is manually verified by our analysts. No bots. No fake listings. No time wasters. Just real opportunities from real people.</p>
      </div>
      <div class="pub-card">
        <div class="pub-card-title">Our reach</div>
        <p class="pub-card-text">Thousands of pre-screened businesses and verified investors across all 7 provinces of Nepal. Investment sizes from NPR 20 lakh to NPR 100 crore across 40+ industries.</p>
      </div>
    </div>
  </div>
</section>

<section class="pub-section surface tight">
  <div class="pub-wrap-narrow">
    <div class="pub-section-head left">
      <h2 class="pub-h2">Who Uses Asaan Capital</h2>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
      <span class="pub-badge neutral">Businesses For Sale</span>
      <span class="pub-badge neutral">Companies Seeking Capital</span>
      <span class="pub-badge neutral">Franchise Brands</span>
      <span class="pub-badge neutral">M&amp;A Advisors</span>
      <span class="pub-badge neutral">Business Brokers</span>
      <span class="pub-badge neutral">Private Investors</span>
      <span class="pub-badge neutral">Corporate Acquirers</span>
      <span class="pub-badge neutral">Lenders</span>
      <span class="pub-badge neutral">PE / VC Firms</span>
      <span class="pub-badge neutral">Family Offices</span>
      <span class="pub-badge neutral">Deal Professionals</span>
    </div>
  </div>
</section>

<section class="pub-section tint">
  <div class="pub-wrap-narrow">
    <div class="pub-section-head left">
      <h2 class="pub-h2">Our Journey</h2>
    </div>
    <ul style="list-style:none;padding:0;margin:0;">
      <li style="padding:0.75rem 0;border-bottom:1px solid var(--dash-border);display:flex;gap:1rem;align-items:flex-start;">
        <span style="font-weight:700;color:var(--dash-primary);min-width:60px;">2024</span>
        <span style="color:var(--dash-ink-soft);">Asaan Credit Ltd established with a vision to transform business matching in Nepal</span>
      </li>
      <li style="padding:0.75rem 0;border-bottom:1px solid var(--dash-border);display:flex;gap:1rem;align-items:flex-start;">
        <span style="font-weight:700;color:var(--dash-primary);min-width:60px;">2024</span>
        <span style="color:var(--dash-ink-soft);">Platform development begins in partnership with iSoftro Solutions</span>
      </li>
      <li style="padding:0.75rem 0;border-bottom:1px solid var(--dash-border);display:flex;gap:1rem;align-items:flex-start;">
        <span style="font-weight:700;color:var(--dash-primary);min-width:60px;">2025</span>
        <span style="color:var(--dash-ink-soft);">Beta launch — onboarded first 200 businesses and 50 investors</span>
      </li>
      <li style="padding:0.75rem 0;border-bottom:1px solid var(--dash-border);display:flex;gap:1rem;align-items:flex-start;">
        <span style="font-weight:700;color:var(--dash-primary);min-width:60px;">2025</span>
        <span style="color:var(--dash-ink-soft);">Official public launch with verified profiles across all 7 provinces</span>
      </li>
      <li style="padding:0.75rem 0;border-bottom:1px solid var(--dash-border);display:flex;gap:1rem;align-items:flex-start;">
        <span style="font-weight:700;color:var(--dash-primary);min-width:60px;">2026</span>
        <span style="color:var(--dash-ink-soft);">Expanded to 2,500+ listings, franchise vertical launched, advisor network introduced</span>
      </li>
    </ul>
  </div>
</section>

<section class="pub-section surface tight">
  <div class="pub-wrap-narrow">
    <div class="pub-section-head left">
      <h2 class="pub-h2">The Team</h2>
    </div>
    <div class="pub-prose">
      <p>Asaan Capital Ltd is developed and operated by <strong>Asaan Credit Ltd</strong>, built in partnership with <strong>iSoftro Solutions</strong>, in close collaboration with Nepal\'s startup ecosystem leaders, legal experts, and active investors.</p>
    </div>
  </div>
</section>',
        'meta_description' => 'Learn the story behind Asaan Capital Ltd. Nepal\'s trusted marketplace for business matching, M&A, and fundraising.',
    ],
    [
        'slug' => 'contact',
        'title' => 'Contact Us',
        'content_html' => '<section class="pub-hero">
  <div class="pub-wrap-narrow">
    <div class="pub-breadcrumbs" style="font-size:0.82rem;color:var(--dash-ink-soft);margin-bottom:16px;">
      <a href="/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a>
      <span style="margin:0 6px;">/</span>
      <span>Contact Us</span>
    </div>
    <div class="pub-section-head left" style="margin-bottom:0;">
      <h1 class="pub-h1">Get in touch</h1>
      <p class="pub-lead">Have a question or want to learn more? We\'d love to hear from you.</p>
    </div>
  </div>
</section>

<section class="pub-section tint">
  <div class="pub-wrap-narrow">
    <div class="pub-grid cols-2" style="gap:var(--space-6);">
      <div>
        <h2 class="pub-h3">Send us a message</h2>
        <form id="contact-form" method="post" action="/contact">
          <input type="hidden" name="_csrf" value="{{CSRF_TOKEN}}">
          <div style="position:absolute;left:-9999px;" aria-hidden="true">
            <input type="text" name="website" tabindex="-1" autocomplete="off">
          </div>
          <div class="input-group">
            <label for="cf_name">Your name</label>
            <input type="text" id="cf_name" name="name" class="input" required>
          </div>
          <div class="input-group">
            <label for="cf_email">Email</label>
            <input type="email" id="cf_email" name="email" class="input" required>
          </div>
          <div class="input-group">
            <label for="cf_subject">Subject</label>
            <input type="text" id="cf_subject" name="subject" class="input" required>
          </div>
          <div class="input-group">
            <label for="cf_message">Message</label>
            <textarea id="cf_message" name="message" class="input" rows="5" required></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
      </div>
      <div>
        <h2 class="pub-h3">Contact information</h2>
        <div style="display:flex;flex-direction:column;gap:var(--space-4);margin-top:var(--space-4);">
          <div class="pub-card">
            <strong>Office</strong><br>
            <span style="color:var(--dash-ink-soft);">Madhyapur Thimi Municipality-9</span><br>
            <span style="color:var(--dash-ink-soft);">Bhaktapur, Nepal</span>
          </div>
          <div class="pub-card">
            <strong>Phone</strong><br>
            <a href="tel:+9779848714990" style="color:var(--dash-primary);">+977-9848714990</a><br>
            <a href="tel:+977982000470" style="color:var(--dash-primary);">+977-982000470</a>
          </div>
          <div class="pub-card">
            <strong>Email</strong><br>
            <a href="mailto:info@asaancapital.com" style="color:var(--dash-primary);">info@asaancapital.com</a>
          </div>
          <div class="pub-card">
            <strong>Hours</strong><br>
            <span style="color:var(--dash-ink-soft);">Sunday — Friday, 9:00 AM — 5:00 PM (NPT)</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>',
        'meta_description' => 'Contact Asaan Capital Ltd. Get in touch with Nepal\'s trusted business marketplace.',
    ],
];

$stmt = $db->prepare('INSERT INTO pages (slug, title, content_html, meta_description, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, 1, ?, ?)');
foreach ($pages as $p) {
    $stmt->execute([$p['slug'], $p['title'], $p['content_html'], $p['meta_description'], $now, $now]);
    echo "  Created page: {$p['slug']}\n";
}

echo "Done. {$count} rows seeded.\n";
