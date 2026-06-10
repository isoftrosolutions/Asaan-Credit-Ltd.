-- ============================================================
-- Pages CMS — Table + Seed Data for Asaan Capital Ltd
-- Safe to re-run (refreshes seed pages on duplicate slug)
-- ============================================================
-- Usage: mysql -u root invest_match < database/seed_pages.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS `pages` (
  `id`               bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug`             varchar(120) NOT NULL,
  `title`            varchar(255) NOT NULL,
  `content_html`     mediumtext NOT NULL,
  `meta_description` varchar(320) DEFAULT NULL,
  `is_active`        tinyint(1) NOT NULL DEFAULT 1,
  `created_at`       timestamp NULL DEFAULT NULL,
  `updated_at`       timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Seed data — uses REPLACE to stay idempotent
-- -----------------------------------------------------------
REPLACE INTO `pages` (`slug`, `title`, `content_html`, `meta_description`, `is_active`, `created_at`, `updated_at`) VALUES
('about', 'Our Story', '
<div class="pub-wrap-narrow" style="padding-top:var(--space-6);padding-bottom:var(--space-8);">
  <div class="breadcrumbs pub-text" style="margin-bottom:var(--space-5);">
    <a href="/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a>
    <span style="margin:0 0.5rem;">/</span>
    <span>Our Story</span>
  </div>

  <h1 class="pub-h1" style="margin-bottom:var(--space-2);">Our Story</h1>
  <p class="pub-text-lead" style="font-size:1.2rem;line-height:1.7;color:var(--dash-ink-soft);margin-bottom:var(--space-6);">
    Nepal\'s trusted marketplace connecting business owners with investors, buyers, and advisors.
  </p>

  <div class="pub-prose">
    <h2>Who We Are</h2>
    <p>Asaan Capital Ltd is a Kathmandu-based financial services company operating Nepal\'s first dedicated online marketplace for business matching, M&A, and fundraising. We bridge the gap between capital seekers and capital providers — making business transactions <em>asaan</em> (easy).</p>

    <h2>Our Promise</h2>
    <p>Every listing on our platform is manually verified. Contact details stay private until both parties express mutual interest. We do not facilitate payments or execute transactions — we connect, you close.</p>

    <h2>Our Reach</h2>
    <p>We serve all seven provinces of Nepal with a growing network of entrepreneurs, investors, franchisors, and advisors across sectors including agriculture, technology, manufacturing, tourism, and services.</p>

    <h2>Our Journey</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:var(--space-4);margin:var(--space-6) 0;">
      <div style="background:var(--dash-card);border:1px solid var(--dash-border);border-radius:var(--radius-lg);padding:var(--space-5);text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:var(--dash-primary);">2024</div>
        <div style="font-size:0.9rem;color:var(--dash-ink-soft);">Platform conceived &amp; built</div>
      </div>
      <div style="background:var(--dash-card);border:1px solid var(--dash-border);border-radius:var(--radius-lg);padding:var(--space-5);text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:var(--dash-primary);">2025</div>
        <div style="font-size:0.9rem;color:var(--dash-ink-soft);">Soft launch + first matches</div>
      </div>
      <div style="background:var(--dash-card);border:1px solid var(--dash-border);border-radius:var(--radius-lg);padding:var(--space-5);text-align:center;">
        <div style="font-size:2rem;font-weight:700;color:var(--dash-primary);">2026</div>
        <div style="font-size:0.9rem;color:var(--dash-ink-soft);">Full platform + nationwide presence</div>
      </div>
    </div>

    <h2>Our Team</h2>
    <p>We are a small, dedicated team of finance professionals, technologists, and relationship managers based in Bhaktapur, Nepal. We understand the Nepali market because we are part of it.</p>
  </div>
</div>
', 'Learn the story behind Asaan Capital Ltd. Nepal\'s trusted marketplace for business matching, M&A, and fundraising.', 1, NOW(), NOW()),

-- -----------------------------------------------------------
-- Seed: Contact Us
-- -----------------------------------------------------------
('contact', 'Contact Us', '
<div class="pub-wrap-narrow" style="padding-top:var(--space-6);padding-bottom:var(--space-8);">
  <div class="breadcrumbs pub-text" style="margin-bottom:var(--space-5);">
    <a href="/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a>
    <span style="margin:0 0.5rem;">/</span>
    <span>Contact Us</span>
  </div>

  <h1 class="pub-h1" style="margin-bottom:var(--space-6);">Contact Us</h1>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-6);">
    <div>
      <form method="post" action="/contact">
        <input type="hidden" name="_csrf" value="{{CSRF_TOKEN}}">
        <div style="display:none;"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
        <div class="input-group" style="margin-bottom:var(--space-4);">
          <label>Your Name</label>
          <input type="text" name="name" class="input" required>
        </div>
        <div class="input-group" style="margin-bottom:var(--space-4);">
          <label>Email</label>
          <input type="email" name="email" class="input" required>
        </div>
        <div class="input-group" style="margin-bottom:var(--space-4);">
          <label>Subject</label>
          <input type="text" name="subject" class="input" required>
        </div>
        <div class="input-group" style="margin-bottom:var(--space-4);">
          <label>Message</label>
          <textarea name="message" class="input" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary" style="font-size:1rem;padding:0.75rem 2rem;">Send Message</button>
      </form>
    </div>
    <div>
      <div style="background:var(--dash-card);border:1px solid var(--dash-border);border-radius:var(--radius-lg);padding:var(--space-5);margin-bottom:var(--space-4);">
        <h3 style="margin:0 0 var(--space-2);font-size:1rem;">Office</h3>
        <p style="margin:0;color:var(--dash-ink-soft);font-size:0.95rem;">Madhyapur Thimi Municipality-9<br>Bhaktapur, Nepal</p>
      </div>
      <div style="background:var(--dash-card);border:1px solid var(--dash-border);border-radius:var(--radius-lg);padding:var(--space-5);margin-bottom:var(--space-4);">
        <h3 style="margin:0 0 var(--space-2);font-size:1rem;">Phone</h3>
        <p style="margin:0;color:var(--dash-ink-soft);font-size:0.95rem;">+977-9848714990<br>+977-982000470</p>
      </div>
      <div style="background:var(--dash-card);border:1px solid var(--dash-border);border-radius:var(--radius-lg);padding:var(--space-5);margin-bottom:var(--space-4);">
        <h3 style="margin:0 0 var(--space-2);font-size:1rem;">Email</h3>
        <p style="margin:0;color:var(--dash-ink-soft);font-size:0.95rem;"><a href="mailto:info@asaancapital.com" style="color:var(--dash-primary);">info@asaancapital.com</a></p>
      </div>
      <div style="background:var(--dash-card);border:1px solid var(--dash-border);border-radius:var(--radius-lg);padding:var(--space-5);">
        <h3 style="margin:0 0 var(--space-2);font-size:1rem;">Hours</h3>
        <p style="margin:0;color:var(--dash-ink-soft);font-size:0.95rem;">Sunday – Friday<br>9:00 AM – 5:00 PM NPT</p>
      </div>
    </div>
  </div>
</div>
', 'Contact Asaan Capital Ltd. Get in touch with Nepal\'s trusted business marketplace.', 1, NOW(), NOW()),

-- -----------------------------------------------------------
-- Seed: Terms of Service
-- -----------------------------------------------------------
('terms', 'Terms of Service', '
<div class="pub-wrap-narrow" style="padding-top:var(--space-6);padding-bottom:var(--space-8);">
  <div class="breadcrumbs pub-text" style="margin-bottom:var(--space-5);">
    <a href="/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a>
    <span style="margin:0 0.5rem;">/</span>
    <span>Terms of Service</span>
  </div>

  <h1 class="pub-h1" style="margin-bottom:var(--space-2);">Terms of Service</h1>
  <p class="pub-text" style="color:var(--dash-ink-soft);margin-bottom:var(--space-6);">Last updated: June 10, 2026</p>

  <div class="pub-prose">
    <h3>1. Platform Role</h3>
    <p>Asaan Capital Ltd is a discovery and matching platform. We connect business owners, investors, buyers, lenders, franchisors, and advisors. We do not facilitate payments, execute transactions, or provide investment advice. All deals are conducted directly between parties.</p>

    <h3>2. Eligibility</h3>
    <p>Users must be at least 18 years old. Businesses must be legally registered entities in Nepal. All users must pass manual verification before their profiles go live. We reserve the right to reject any registration without explanation.</p>

    <h3>3. Verification &amp; Data</h3>
    <p>Verification documents (citizenship, PAN, company registration, VAT/GST certificate) are stored securely and accessible only to platform administrators. Documents are never shared publicly. Standard security measures including SSL encryption are employed throughout the platform.</p>

    <h3>4. Profile Rules</h3>
    <p>Each user may maintain one active business profile at a time. Profiles must be accurate, current, and not misleading. We prohibit scraping, automated data collection, unsolicited messaging, and any form of spam or solicitation outside the platform\'s intended use.</p>

    <h3>5. Confidentiality</h3>
    <p>Contact information is revealed only after mutual interest is established through the platform. Users may not share or misuse contact details obtained through the platform. Violation may result in permanent account suspension without refund.</p>

    <h3>6. Refund Policy</h3>
    <p>Profile activation typically completes within 2 business days. CIM and Valuation Model services have a 25 business day SLA. A 5% processing fee applies in eligible refund cases. No refund for change-of-mind or non-use. Refund requests must be submitted within 3 months via email. Credits process within 5–15 business days.</p>

    <h3>7. Finder\'s Fee</h3>
    <p>Paid subscription plans include a 1% finder\'s fee payable upon successful transaction completion between matched parties. This fee is due only when a deal closes.</p>

    <h3>8. Governing Law</h3>
    <p>These terms are governed by the laws of Nepal. Disputes shall be subject to the exclusive jurisdiction of courts in Kathmandu, Nepal.</p>

    <h3>9. Indemnification</h3>
    <p>Users agree to indemnify and hold Asaan Capital Ltd harmless from any claims arising from their use of the platform, including but not limited to transaction disputes, misrepresentation, or breach of these terms.</p>
  </div>
</div>
', 'Terms of Service for Asaan Capital Ltd — Nepal\'s business matching marketplace.', 1, NOW(), NOW()),

-- -----------------------------------------------------------
-- Seed: Privacy Policy
-- -----------------------------------------------------------
('privacy', 'Privacy Policy', '
<div class="pub-wrap-narrow" style="padding-top:var(--space-6);padding-bottom:var(--space-8);">
  <div class="breadcrumbs pub-text" style="margin-bottom:var(--space-5);">
    <a href="/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a>
    <span style="margin:0 0.5rem;">/</span>
    <span>Privacy Policy</span>
  </div>

  <h1 class="pub-h1" style="margin-bottom:var(--space-2);">Privacy Policy</h1>
  <p class="pub-text" style="color:var(--dash-ink-soft);margin-bottom:var(--space-6);">Last updated: June 10, 2026</p>

  <div class="pub-prose">
    <h3>1. Information We Collect</h3>
    <p>We collect the following information when you register and use our platform:</p>
    <ul>
      <li><strong>Identity data:</strong> full name, email address, phone number, profile photo</li>
      <li><strong>Business data:</strong> company name, registration documents, financial information, business descriptions, PAN/VAT details</li>
      <li><strong>Verification data:</strong> citizenship certificate, company registration certificate, tax clearance, and other verification documents</li>
      <li><strong>Usage data:</strong> pages visited, searches performed, interests expressed, time spent on platform</li>
      <li><strong>Technical data:</strong> IP address, browser type, device information, session data</li>
    </ul>

    <h3>2. How We Use Your Data</h3>
    <p>Your data is used exclusively for:</p>
    <ul>
      <li>Platform operation and account management</li>
      <li>Matching algorithms that suggest relevant connections</li>
      <li>Identity verification and fraud prevention</li>
      <li>Platform communications (interest requests, matches, notifications)</li>
      <li>Analytics and platform improvement</li>
      <li>Compliance with legal and regulatory obligations</li>
    </ul>

    <h3>3. Data Sharing</h3>
    <p>We do not sell, rent, or trade your personal data. Limited data is shared only when you express mutual interest with another user — and only to the extent necessary to facilitate the connection. Verification documents are never shared with other users.</p>

    <h3>4. Data Retention</h3>
    <p>We retain your data for as long as your account is active. After account deletion, we retain backup copies for 90 days for legal compliance, after which all data is permanently deleted.</p>

    <h3>5. Your Rights</h3>
    <p>You may request access to, correction of, or deletion of your personal data at any time by contacting us at info@asaancapital.com. We will respond within 15 business days.</p>

    <h3>6. Security</h3>
    <p>We implement SSL/TLS encryption, password hashing (bcrypt), session security measures, and regular security audits. However, no online platform can guarantee absolute security. Users are responsible for maintaining strong, unique passwords.</p>

    <h3>7. Cookies</h3>
    <p>We use essential session cookies for platform operation. No tracking, advertising, or third-party cookies are used. You may disable cookies in your browser, but some platform features may not function correctly.</p>

    <h3>8. Contact</h3>
    <p>For privacy-related inquiries, contact:<br>
    Asaan Capital Ltd<br>
    Madhyapur Thimi Municipality-9, Bhaktapur, Nepal<br>
    Email: <a href="mailto:info@asaancapital.com" style="color:var(--dash-primary);">info@asaancapital.com</a></p>
  </div>
</div>
', 'Privacy Policy for Asaan Capital Ltd. How we collect, use, and protect your data.', 1, NOW(), NOW()),

-- -----------------------------------------------------------
-- Seed: FAQ
-- -----------------------------------------------------------
('faq', 'Frequently Asked Questions', '
<div class="pub-wrap-narrow" style="padding-top:var(--space-6);padding-bottom:var(--space-8);">
  <div class="breadcrumbs pub-text" style="margin-bottom:var(--space-5);">
    <a href="/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a>
    <span style="margin:0 0.5rem;">/</span>
    <span>FAQ</span>
  </div>

  <h1 class="pub-h1" style="margin-bottom:var(--space-6);">Frequently Asked Questions</h1>

  <div style="display:grid;gap:var(--space-3);">
    <div style="background:white;border-radius:var(--radius-lg);padding:var(--space-5);border:1px solid var(--dash-border);">
      <h3 style="margin:0 0 var(--space-2);font-size:1rem;">What is Asaan Capital?</h3>
      <p style="margin:0;color:var(--dash-ink-soft);font-size:0.95rem;">Asaan Capital Ltd is Nepal\'s first online marketplace connecting business owners with investors, buyers, franchisors, and advisors. We make business matching <em>asaan</em> (easy).</p>
    </div>
    <div style="background:white;border-radius:var(--radius-lg);padding:var(--space-5);border:1px solid var(--dash-border);">
      <h3 style="margin:0 0 var(--space-2);font-size:1rem;">Is Asaan Capital free to join?</h3>
      <p style="margin:0;color:var(--dash-ink-soft);font-size:0.95rem;">Registration and basic profile creation are free. Premium features such as CIM preparation, business valuation, and priority listing may require subscription or one-time fees.</p>
    </div>
    <div style="background:white;border-radius:var(--radius-lg);padding:var(--space-5);border:1px solid var(--dash-border);">
      <h3 style="margin:0 0 var(--space-2);font-size:1rem;">How does verification work?</h3>
      <p style="margin:0;color:var(--dash-ink-soft);font-size:0.95rem;">After registration, you submit verification documents (citizenship, PAN, company registration). Our admin team reviews and approves within 1–2 business days. Your profile goes live only after verification.</p>
    </div>
    <div style="background:white;border-radius:var(--radius-lg);padding:var(--space-5);border:1px solid var(--dash-border);">
      <h3 style="margin:0 0 var(--space-2);font-size:1rem;">When is my contact information shared?</h3>
      <p style="margin:0;color:var(--dash-ink-soft);font-size:0.95rem;">Your contact details remain private until you express mutual interest with another user. Only then are both parties\' contact details revealed to facilitate direct communication.</p>
    </div>
    <div style="background:white;border-radius:var(--radius-lg);padding:var(--space-5);border:1px solid var(--dash-border);">
      <h3 style="margin:0 0 var(--space-2);font-size:1rem;">What types of businesses can list?</h3>
      <p style="margin:0;color:var(--dash-ink-soft);font-size:0.95rem;">Any legally registered business in Nepal can list, including sole proprietorships, partnerships, private limited companies, and public limited companies. We cover sectors from agriculture and manufacturing to technology and services.</p>
    </div>
    <div style="background:white;border-radius:var(--radius-lg);padding:var(--space-5);border:1px solid var(--dash-border);">
      <h3 style="margin:0 0 var(--space-2);font-size:1rem;">Do you facilitate payments or transactions?</h3>
      <p style="margin:0;color:var(--dash-ink-soft);font-size:0.95rem;">No. We are a discovery and matching platform only. All transactions, negotiations, due diligence, and payments are conducted directly between the involved parties outside our platform.</p>
    </div>
    <div style="background:white;border-radius:var(--radius-lg);padding:var(--space-5);border:1px solid var(--dash-border);">
      <h3 style="margin:0 0 var(--space-2);font-size:1rem;">How do I delete my account?</h3>
      <p style="margin:0;color:var(--dash-ink-soft);font-size:0.95rem;">Email us at info@asaancapital.com with your account details. We will process deletion within 15 business days. Backups are retained for 90 days per legal requirements.</p>
    </div>
  </div>
</div>
', 'Frequently Asked Questions about Asaan Capital Ltd — Nepal\'s business matching marketplace.', 1, NOW(), NOW());
