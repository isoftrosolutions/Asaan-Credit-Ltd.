# Asaan Marketplace — Execution Plan

> Single source of truth for building the platform. Read this before touching any code.
> Stack: **Core PHP 8.2+, PDO, MySQL 8.4** on Apache (Windows dev → Hostinger VPS prod).

---

## 0. Document Status

| Field | Value |
|---|---|
| Project root | `C:\Apache24\htdocs\assan` |
| DB name | `invest_match` (already exists on local MySQL) |
| MySQL client | `C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe` |
| PDO DSN | `mysql:host=127.0.0.1;dbname=invest_match;charset=utf8mb4` user=`root` pass=`(empty)` |
| Source of truth (UI) | `C:\Apache24\htdocs\assan\screens\*.html` (30 files) |
| Source of truth (req) | `C:\Apache24\htdocs\assan\PRD.md` v1.0 |
| Roles in v1 | investor, business_owner, franchisor, advisor + admin |

---

## 1. What Exists Right Now

### 1.1 Files on disk (`C:\Apache24\htdocs\assan`)
- `PRD.md` — full v1.0 product spec
- `README.md` — README confirms Core PHP + PDO + MySQL
- `logo.jpeg` — brand asset
- `screens/` — **30 HTML mockups** (the design target)

### 1.2 Screens inventory (30 files)
| # | File | Role | Page kind |
|---|---|---|---|
| 1 | `index.html` | public | Homepage |
| 2 | `about.html` | public | About |
| 3 | `how-it-works.html` | public | Marketing |
| 4 | `support.html` | public | FAQ + contact |
| 5 | `legal.html` | public | Terms / Privacy |
| 6 | `sign-up.html` | public | 3-step signup w/ role select |
| 7 | `login.html` | public | Login |
| 8 | `business-valuation.html` | public | Free valuation tool |
| 9 | `browse-businesses.html` | discovery | Listings + filters |
| 10 | `browse-investors.html` | discovery | Listings + filters |
| 11 | `browse-entrepreneurs.html` | discovery | Listings + filters |
| 12 | `browse-franchises.html` | discovery | Listings + filters |
| 13 | `search-results.html` | discovery | Keyword search |
| 14 | `business-detail.html` | detail | Business for-sale page |
| 15 | `public-pitch-detail.html` | detail | Entrepreneur pitch page |
| 16 | `public-investor-profile.html` | detail | Investor public profile |
| 17 | `investor-dashboard.html` | dashboard | Investor home |
| 18 | `business-owner-dashboard.html` | dashboard | Business owner home |
| 19 | `entrepreneur-dashboard.html` | dashboard | Entrepreneur home |
| 20 | `create-investor-profile.html` | onboarding | Build investor profile |
| 21 | `create-business-profile.html` | onboarding | List a business |
| 22 | `create-franchise-profile.html` | onboarding | Build franchise profile |
| 23 | `create-advisor-profile.html` | onboarding | Build advisor profile |
| 24 | `profile-edit.html` | settings | Edit profile (investor variant) |
| 25 | `pitch-edit.html` | settings | Edit entrepreneur pitch |
| 26 | `investor-profile-edit-investment-preferences.html` | settings | Edit prefs |
| 27 | `investor-profile-edit-verification-documents.html` | settings | Re-upload docs |
| 28 | `my-connections.html` | connections | Matched-with users |
| 29 | `send-interest-request-modal.html` | connections | Express-interest modal |
| 30 | `notifications-settings.html` | settings | Notification prefs |
| 31 | `admin.html` | admin | Admin panel (all modules) |

### 1.3 Missing shared assets (referenced by every screen, but don't exist)
- `styles.css` — design system (colors, typography, buttons, cards, forms)
- `header.css` — sticky header styles
- `icons.js` — SVG icon helpers
- `header.js` — `injectHeader('public' | 'auth' | 'admin')` function
- `components.js` — `injectFooter()` and shared widgets

→ Must be **built from scratch** by extracting tokens from inline styles already present in screens.

### 1.4 Existing MySQL database (`invest_match`)
**Product tables (PRD-aligned, 2-role model):**

| Table | Purpose | Notable cols |
|---|---|---|
| `users` | Auth + identity | role (default 'entrepreneur'), is_admin, verification_status, verified_at, is_suspended, daily_request_count, daily_request_date, password (bcrypt), email_verified_at |
| `investor_profiles` | Investor preferences | past_investments, portfolio_companies, total_capital_deployed, preferred_sectors JSON, preferred_stages JSON, ticket_min/max, preferred_geography JSON, references |
| `pitches` | Entrepreneur pitch (50+ cols) | tagline, problem, solution, market_size, funding_amount, equity_offered, valuation, pitch_deck, pitch_video_url, sector_id FK, is_active/hidden/featured, completeness_score, is_published |
| `pitch_media` | Photos/decks | pitch_id FK, file_path, file_type, sort_order |
| `pitch_team_members` | Co-founders | pitch_id FK, name, role, linkedin_url |
| `interest_requests` | Connection flow | sender_id, receiver_id, pitch_id, message, status (pending/accepted/rejected), responded_at, rejected_until |
| `notifications` | In-app feed | user_id, type, title, body, action_url, is_read |
| `verification_documents` | KYC uploads | user_id, document_type, file_path, status, rejection_reason, reviewed_by, reviewed_at |
| `sectors` | Tag list | name, slug, is_active (15 seeded: AgriTech, CleanTech, HealthTech, FinTech, EdTech, Logistics, Manufacturing, Retail, Hospitality, RealEstate, Technology, Food & Beverage, E-commerce, Construction, Education) |
| `faqs` | Support content | question, answer, sort_order, is_active (5 seeded) |
| `homepage_contents` | Editable hero/stats | key-value: hero_title, hero_subtitle, stats_businesses, stats_investors, stats_matches, stats_deal_value |
| `password_reset_tokens` | Auth | email, token, created_at |

**Laravel infrastructure tables (ignore — keep but unused):**
- `cache`, `cache_locks`, `sessions`, `jobs`, `job_batches`, `failed_jobs`, `migrations`

**Seeded users:**
| id | name | email | role | is_admin | verification_status |
|---|---|---|---|---|---|
| 1 | Admin User | admin@investmatch.com | entrepreneur | 1 | verified |
| 2 | Ramesh Thapa | investor@nepal.com | investor | 0 | verified |
| 3 | Anjali K.C. | anjali@aarohan.com | entrepreneur | 0 | verified |
| 4 | Sunita Sharma | sunita@vc.com | investor | 0 | verified |

> ⚠️ Existing password hashes are unknown. **Reset all 4 seeded users to a known dev password** as part of foundation (e.g. `Demo@2026`).

### 1.5 Gap vs. 4-role screen scope
DB has zero tables for:
- `businesses` — "Business for Sale" listings (separate from pitches)
- `franchises` — franchise brand profiles
- `advisors` — advisor/broker profiles
- `matches` — accepted-request match records (currently inferred from `interest_requests.status='accepted'`)
- `reports` — user-flagged content
- `broadcasts` — admin announcements

Must be added in Phase 1.

---

## 2. Decisions Locked

| Question | Decision |
|---|---|
| Where to build | `C:\Apache24\htdocs\assan` root (overwrite/extend in place) |
| Roles to support | **All 4**: investor, business_owner, franchisor, advisor (matches screens, exceeds PRD v1.0) |
| Shared CSS/JS | Build from scratch by extracting inline styles |
| DB strategy | **Extend only** — additive ALTER/CREATE, no drops. Preserve seeded data. |
| DB credentials | `root` / empty pass (dev) → swap in `config.php` for prod |
| Email | SMTP stub via PHPMailer-style wrapper; dev mode writes to `storage/mail/` |
| File uploads | Local FS: `storage/verification-docs/` (private), `public/uploads/` (CDN-able) |
| Routing | `.htaccess` mod_rewrite → friendly URLs (`/login`, `/dashboard`, etc.) |
| Sessions | PHP native sessions, secure cookie, 30-min idle timeout |
| CSRF | Token per session, validated on every POST |
| Password hash | `password_hash()` with `PASSWORD_BCRYPT` |
| Frontend JS | Vanilla — no frameworks, no build step |

---

## 3. Target Directory Structure

```
C:\Apache24\htdocs\assan\
├── PRD.md                          (existing, untouched)
├── README.md                       (existing, will refresh)
├── execution.md                    (this file)
├── logo.jpeg                       (existing)
├── screens/                        (existing reference mockups — DO NOT serve)
│
├── .htaccess                       NEW — routes everything through public/
├── index.php                       NEW — bootstraps and includes public/index.php
│
├── config/
│   ├── config.php                  NEW — DB creds, app URL, mail, debug flag
│   ├── db.php                      NEW — PDO singleton (lazy, persistent)
│   ├── session.php                 NEW — start session, regenerate ID, timeout
│   ├── auth.php                    NEW — require_login(), require_role(), current_user()
│   ├── csrf.php                    NEW — csrf_token(), csrf_check()
│   ├── flash.php                   NEW — set/get flash messages
│   ├── mailer.php                  NEW — send_mail() wrapper (PHPMailer or stub)
│   ├── upload.php                  NEW — handle_upload(), validate type/size, MIME sniff
│   └── helpers.php                 NEW — e(), redirect(), money(), date_human(), etc.
│
├── includes/
│   ├── header.php                  NEW — top nav, role-aware menu, notification bell
│   ├── footer.php                  NEW — footer + global JS
│   ├── layout-public.php           NEW — wrapper for public pages
│   ├── layout-dashboard.php        NEW — wrapper for logged-in pages
│   └── layout-admin.php            NEW — admin sidebar wrapper
│
├── assets/
│   ├── styles.css                  NEW — design system (Inter font, navy/red palette)
│   ├── header.css                  NEW — sticky header
│   ├── icons.js                    NEW — SVG icon registry
│   ├── header.js                   NEW — injectHeader('public'|'dashboard'|'admin')
│   └── components.js               NEW — injectFooter(), modal helpers, toasts
│
├── public/                         (Apache DocumentRoot — optional cleaner approach)
│   ├── uploads/                    NEW — public-readable user uploads (photos, logos)
│   │   ├── avatars/
│   │   ├── business-photos/
│   │   ├── pitch-photos/
│   │   └── franchise-logos/
│   └── (entry PHP files mirrored here if we go DocumentRoot=/public)
│
├── storage/                        NEW — non-public (chmod 700 in prod)
│   ├── verification-docs/          KYC docs (citizenship, PAN, company reg)
│   ├── pitch-decks/                PDF pitch decks (signed-URL access)
│   ├── mail/                       Dev-only: dumped emails for inspection
│   └── logs/                       App + error logs
│
├── auth/
│   ├── signup.php                  3-step form, role select
│   ├── login.php                   Login
│   ├── logout.php                  Destroy session
│   ├── verify-email.php            ?token= → mark email_verified_at
│   ├── forgot-password.php         Email reset link
│   └── reset-password.php          ?token= → set new password
│
├── pages/                          (public marketing pages)
│   ├── index.php                   Homepage
│   ├── about.php
│   ├── how-it-works.php
│   ├── support.php                 FAQs from DB
│   ├── legal.php
│   └── business-valuation.php
│
├── investor/                       (logged-in investor area)
│   ├── dashboard.php
│   ├── profile-create.php
│   ├── profile-edit.php
│   ├── preferences-edit.php
│   ├── documents-edit.php
│   └── public/{id}.php             Public investor page
│
├── business/                       (logged-in business_owner area)
│   ├── dashboard.php
│   ├── create.php
│   ├── edit.php
│   ├── detail/{id}.php
│   └── valuation.php
│
├── entrepreneur/                   (logged-in entrepreneur area — overlaps with business)
│   ├── dashboard.php
│   ├── pitch-create.php
│   ├── pitch-edit.php
│   └── pitch/{id}.php              Public pitch detail
│
├── franchise/                      (logged-in franchisor area)
│   ├── create.php
│   ├── edit.php
│   └── detail/{id}.php
│
├── advisor/                        (logged-in advisor area)
│   ├── create.php
│   └── edit.php
│
├── discover/                       (public + logged-in)
│   ├── businesses.php              Browse businesses
│   ├── investors.php               Browse investors
│   ├── entrepreneurs.php           Browse entrepreneurs
│   ├── franchises.php              Browse franchises
│   └── search.php                  Keyword search across everything
│
├── connections/                    (logged-in only)
│   ├── send-interest.php           POST endpoint (modal posts here)
│   ├── respond.php                 Accept/reject
│   └── my-connections.php          List of matches
│
├── notifications/                  (logged-in)
│   ├── index.php                   In-app feed
│   ├── mark-read.php               AJAX POST
│   └── settings.php                Email prefs
│
├── admin/                          (admin role only — middleware enforced)
│   ├── login.php                   Separate admin login (rate-limited)
│   ├── dashboard.php               Analytics
│   ├── users.php                   List + filter + suspend/edit + CSV
│   ├── verification.php            Queue (approve/reject with reason)
│   ├── pitches.php                 Hide/unhide + flag review
│   ├── reports.php                 Resolve/dismiss + take action
│   ├── interest-log.php            Read-only filterable table + CSV
│   ├── content/
│   │   ├── sectors.php             CRUD
│   │   ├── faqs.php                CRUD
│   │   └── homepage.php            Hero + stats KV editor
│   ├── broadcast.php               Compose + audience filter + send
│   └── analytics.php               Charts (Chart.js CDN)
│
├── api/                            (AJAX endpoints — JSON responses)
│   ├── notifications-unread.php    Bell count poller
│   ├── mark-notification-read.php
│   ├── smart-suggestions.php       Cached match feed
│   └── upload.php                  Drag-drop image uploader
│
└── database/
    ├── schema-existing.sql         Dump of current DB (reference)
    ├── schema-extensions.sql       New ALTER/CREATE for 4-role tables
    └── seed.sql                    Demo users + sample content
```

---

## 4. Database Extension Plan (Phase 1)

> Additive only. No drops. Run via `mysql -u root invest_match < database/schema-extensions.sql`.

### 4.1 ALTER existing `users`
- Widen `role` semantics — already `varchar`, no schema change needed. Just accept new values in app logic:
  - `investor`, `entrepreneur`, `business_owner`, `franchisor`, `advisor` (admin via `is_admin=1` flag, regardless of role)
- Add columns:
  - `last_login_at TIMESTAMP NULL`
  - `failed_login_attempts INT DEFAULT 0`
  - `locked_until TIMESTAMP NULL`

### 4.2 NEW `businesses` (for-sale listings — distinct from entrepreneur pitches)
```sql
CREATE TABLE businesses (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  business_name VARCHAR(255) NOT NULL,
  listing_type VARCHAR(50) NOT NULL,        -- 'sale' | 'partial_stake' | 'loan' | 'asset_sale'
  sector_id BIGINT UNSIGNED NULL,
  province VARCHAR(100), district VARCHAR(100),
  established_year SMALLINT,
  employee_count INT,
  annual_revenue DECIMAL(15,2),
  ebitda_pct DECIMAL(5,2),
  asking_price DECIMAL(15,2),
  stake_offered_pct DECIMAL(5,2),           -- for partial
  loan_amount DECIMAL(15,2), loan_interest_pct DECIMAL(5,2),  -- for loan
  description TEXT,
  reason_for_sale TEXT,
  assets_included TEXT,
  is_published TINYINT(1) DEFAULT 0,
  is_hidden TINYINT(1) DEFAULT 0,
  is_featured TINYINT(1) DEFAULT 0,
  views INT DEFAULT 0,
  rating DECIMAL(3,1),
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (sector_id) REFERENCES sectors(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE business_photos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id BIGINT UNSIGNED NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP NULL,
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4.3 NEW `franchises`
```sql
CREATE TABLE franchises (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  brand_name VARCHAR(255) NOT NULL,
  sector_id BIGINT UNSIGNED NULL,
  established_year SMALLINT,
  existing_units INT,
  countries_present VARCHAR(255),
  description TEXT,
  ideal_partner_profile TEXT,
  franchise_fee DECIMAL(15,2),
  royalty_pct DECIMAL(5,2),
  marketing_fee_pct DECIMAL(5,2),
  total_investment_min DECIMAL(15,2),
  total_investment_max DECIMAL(15,2),
  expected_payback_months INT,
  training_provided TINYINT(1) DEFAULT 1,
  territory_protection TINYINT(1) DEFAULT 0,
  logo_path VARCHAR(255),
  is_published TINYINT(1) DEFAULT 0,
  is_hidden TINYINT(1) DEFAULT 0,
  is_featured TINYINT(1) DEFAULT 0,
  views INT DEFAULT 0,
  rating DECIMAL(3,1),
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (sector_id) REFERENCES sectors(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4.4 NEW `advisors`
```sql
CREATE TABLE advisors (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  firm_name VARCHAR(255),
  specialties JSON,                          -- ['m_and_a','brokerage','legal','consulting','due_diligence']
  years_experience INT,
  past_deals_count INT,
  total_deal_value DECIMAL(15,2),
  credentials TEXT,                          -- CA, CFA, JD, MBA, etc.
  bar_council_id VARCHAR(100),               -- legal advisors
  service_fee_structure VARCHAR(100),        -- 'success_fee' | 'retainer' | 'hourly'
  fee_min DECIMAL(15,2), fee_max DECIMAL(15,2),
  description TEXT,
  is_published TINYINT(1) DEFAULT 0,
  is_hidden TINYINT(1) DEFAULT 0,
  rating DECIMAL(3,1),
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4.5 NEW `matches` (denormalized accepted-interest record)
```sql
CREATE TABLE matches (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  interest_request_id BIGINT UNSIGNED NOT NULL,
  user_a_id BIGINT UNSIGNED NOT NULL,
  user_b_id BIGINT UNSIGNED NOT NULL,
  context_type VARCHAR(50),                  -- 'pitch' | 'business' | 'franchise' | 'investor'
  context_id BIGINT UNSIGNED NULL,
  matched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  closed_status VARCHAR(50) DEFAULT 'open',  -- 'open' | 'deal_closed' | 'dropped'
  closed_at TIMESTAMP NULL,
  FOREIGN KEY (interest_request_id) REFERENCES interest_requests(id) ON DELETE CASCADE,
  FOREIGN KEY (user_a_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (user_b_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4.6 NEW `reports` (user-flagged content)
```sql
CREATE TABLE reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reporter_id BIGINT UNSIGNED NOT NULL,
  target_type VARCHAR(50) NOT NULL,          -- 'user' | 'pitch' | 'business' | 'franchise'
  target_id BIGINT UNSIGNED NOT NULL,
  reason VARCHAR(100) NOT NULL,              -- 'fake' | 'spam' | 'inappropriate' | 'misleading' | 'other'
  details TEXT,
  status VARCHAR(50) DEFAULT 'open',         -- 'open' | 'resolved' | 'dismissed'
  resolved_by BIGINT UNSIGNED NULL,
  resolved_at TIMESTAMP NULL,
  action_taken VARCHAR(100),                 -- 'warning' | 'suspension' | 'ban' | 'no_action'
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4.7 NEW `broadcasts` (admin announcements)
```sql
CREATE TABLE broadcasts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sent_by BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  body TEXT NOT NULL,
  audience VARCHAR(50) NOT NULL,             -- 'all' | 'investors' | 'entrepreneurs' | 'business_owners' | 'franchisors' | 'advisors' | 'verified_only'
  delivery VARCHAR(50) NOT NULL,             -- 'in_app' | 'email' | 'both'
  recipients_count INT DEFAULT 0,
  sent_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  FOREIGN KEY (sent_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4.8 NEW `saved_listings` (bookmarks)
```sql
CREATE TABLE saved_listings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  listing_type VARCHAR(50) NOT NULL,         -- 'pitch' | 'business' | 'investor' | 'franchise'
  listing_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  UNIQUE KEY uq_save (user_id, listing_type, listing_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4.9 NEW `admin_audit_log`
```sql
CREATE TABLE admin_audit_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id BIGINT UNSIGNED NOT NULL,
  action VARCHAR(100) NOT NULL,              -- 'approve_verification' | 'suspend_user' | 'hide_pitch' | etc.
  target_type VARCHAR(50),
  target_id BIGINT UNSIGNED,
  details JSON,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4.10 NEW `smart_suggestion_cache` (24h TTL per PRD §9.2)
```sql
CREATE TABLE smart_suggestion_cache (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  target_type VARCHAR(50),
  target_id BIGINT UNSIGNED,
  match_score DECIMAL(5,2),
  score_breakdown JSON,                      -- {"sector":40,"stage":30,"budget":24}
  cached_until TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_until (user_id, cached_until),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 5. Coding Conventions

### 5.1 PDO usage (mandatory)
```php
$pdo = db();                                              // singleton
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
```
- **Never** string-concatenate SQL.
- Always use `PDO::FETCH_ASSOC`.
- Wrap multi-table writes in `$pdo->beginTransaction()` / `commit()` / `rollBack()`.

### 5.2 Page bootstrap pattern
Every page starts with:
```php
<?php
require __DIR__ . '/../config/bootstrap.php';   // loads session, db, helpers, csrf
// ...page logic
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<!-- HTML -->
<?php include __DIR__ . '/../includes/footer.php'; ?>
```

### 5.3 Auth gating
```php
require_login();                                          // 401 → /login
require_role('investor');                                 // 403 → /403
require_verified();                                       // 403 if not verified
require_admin();                                          // 403 if !is_admin
```

### 5.4 Output escaping
- Wrap ALL dynamic output: `<?= e($user['name']) ?>`.
- `e()` calls `htmlspecialchars($s, ENT_QUOTES, 'UTF-8')`.

### 5.5 CSRF
Every form:
```html
<input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
```
Every POST handler starts with `csrf_check();`.

### 5.6 File uploads
Always go through `handle_upload($_FILES['x'], $allowedMime, $maxBytes, $dest)`:
- Whitelist MIME (PDF/JPG/PNG only for docs; JPG/PNG/WebP for photos).
- Re-name to `{user_id}_{timestamp}_{rand}.{ext}`.
- Size cap per PRD (10 MB pitch deck, 2 MB photos).
- Strip EXIF on photos.

### 5.7 URL routing (`.htaccess`)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?_path=$1 [QSA,L]
```
`index.php` then maps `_path` to the right file. Pretty URLs:
| URL | File |
|---|---|
| `/` | `pages/index.php` |
| `/login` | `auth/login.php` |
| `/signup` | `auth/signup.php` |
| `/dashboard` | role-routed dashboard |
| `/browse/businesses` | `discover/businesses.php` |
| `/pitch/{id}` | `entrepreneur/pitch.php` |
| `/business/{id}` | `business/detail.php` |
| `/investor/{id}` | `investor/public.php` |
| `/connections` | `connections/my-connections.php` |
| `/admin` | `admin/dashboard.php` |
| `/admin/users` | `admin/users.php` |

---

## 6. Execution Phases

### Phase 1 — Foundation (sequential, must finish first)
**Owner:** foundation agent
**Deliverables:**
1. Run `database/schema-extensions.sql` against `invest_match`
2. Reset seeded user passwords to `Demo@2026` (bcrypt)
3. Create directory tree (Section 3)
4. Write `config/*` files (config, db, session, auth, csrf, flash, mailer, upload, helpers)
5. Write `includes/header.php`, `footer.php`, `layout-*.php`
6. Write `assets/styles.css`, `header.css`, `icons.js`, `header.js`, `components.js`
7. Write `.htaccess` + root `index.php` router
8. Write seed file `database/seed.sql` (sample pitches, businesses, franchises for demo)

**Acceptance:** `http://localhost/assan/` returns a styled homepage with header/footer, no PHP errors.

### Phase 2 — Parallel modules (8 agents run concurrently after Phase 1)

| Agent | Module | Screens it owns | Tables it writes |
|---|---|---|---|
| 2A | Auth | sign-up, login | users, password_reset_tokens, notifications (welcome) |
| 2B | Public pages | index, about, how-it-works, support, legal | reads-only (faqs, homepage_contents, featured listings) |
| 2C | Investor | create-investor-profile, profile-edit (investor), investor-dashboard, edit-investment-preferences, edit-verification-documents, public-investor-profile | investor_profiles, verification_documents |
| 2D | Business + Entrepreneur | create-business-profile, business-owner-dashboard, entrepreneur-dashboard, pitch-edit, business-detail, public-pitch-detail, business-valuation | businesses, business_photos, pitches, pitch_media, pitch_team_members |
| 2E | Franchise + Advisor | create-franchise-profile, create-advisor-profile | franchises, advisors |
| 2F | Discovery | browse-businesses, browse-investors, browse-entrepreneurs, browse-franchises, search-results | reads + writes smart_suggestion_cache |
| 2G | Connections | my-connections, send-interest-request-modal, notifications-settings | interest_requests, matches, notifications |
| 2H | Admin | admin (all submodules) | admin_audit_log, broadcasts, reports + writes across all tables |

Each agent must:
- Read its assigned HTML screens for visual fidelity
- Reuse foundation helpers (no duplicate PDO/sessions/CSRF code)
- Use shared layout includes (don't re-implement header/footer)
- Match PRD validation rules (word counts, file sizes, limits)
- Wire forms via POST → redirect-after-post pattern
- Add flash messages on success/failure

---

## 7. Smart Suggestion Algorithm (PRD §5.4 + §9.2)

Implemented in `lib/matching.php`:

```
score(investor I, pitch P) =
  40 * sector_overlap(I.preferred_sectors, P.sector_id)        // 0..1
+ 30 * stage_overlap(I.preferred_stages, P.stage)              // 0..1
+ 30 * budget_overlap(I.ticket_min..max, P.funding_amount)     // 0..1
+ geography_tiebreaker(I.preferred_geography, P.province)      // 0 or +0.5
```
- Only verified profiles considered.
- Recomputed when a profile or pitch is created/updated.
- Cached in `smart_suggestion_cache` for 24h (`cached_until = NOW() + INTERVAL 24 HOUR`).
- Returned via `/api/smart-suggestions.php?limit=10`.

---

## 8. Interest Request State Machine (PRD §5.5)

```
[initial: investor browses pitch]
   │
   ├─ both verified? ──no──→ button disabled
   │       yes
   ▼
[POST /connections/send-interest]
   │ validates: sender.daily_request_count < 10
   │            no existing pending request to same receiver
   │            no rejected_until > NOW() for this pair
   │
   ▼ INSERT interest_requests (status='pending')
   │ INSERT notifications for receiver
   │ send_mail(receiver, 'new_interest')
   │ sender.daily_request_count++
   │
   ▼ [receiver responds]
       ├─ ACCEPT
       │   UPDATE interest_requests status='accepted', responded_at=NOW()
       │   INSERT matches (user_a, user_b, context)
       │   send_mail BOTH
       │   reveal contacts on both profiles
       │
       └─ REJECT
           UPDATE status='rejected', responded_at=NOW(), rejected_until=NOW()+60day
           send_mail sender (polite)
           NO notification to sender for rejection (per PRD)
```

---

## 9. Notifications Matrix (PRD §5.7)

| Event | In-App | Email | Trigger code path |
|---|---|---|---|
| Email verification needed | — | ✅ | on signup |
| Profile verified | ✅ | ✅ | admin approval |
| Profile rejected | ✅ | ✅ | admin rejection |
| New interest request | ✅ | ✅ | send-interest |
| Interest accepted | ✅ | ✅ | respond |
| Interest rejected | ✅ | — | respond |
| Admin broadcast | ✅ | ✅ | admin/broadcast |
| Account suspended | ✅ | ✅ | admin/users |

Bell shows unread count (poll every 30s). Feed shows last 30 notifications. `mark-read` AJAX endpoint clears.

---

## 10. Security Checklist

- [ ] All POST handlers call `csrf_check()`.
- [ ] All output goes through `e()`.
- [ ] All SQL uses prepared statements.
- [ ] `password_hash()` everywhere; `password_verify()` on login.
- [ ] Session ID regenerated on login.
- [ ] `Secure`, `HttpOnly`, `SameSite=Lax` cookies in prod (`config.php`).
- [ ] Admin routes gated by `require_admin()`.
- [ ] Admin login rate-limited (5 attempts / 15 min lockout via `users.failed_login_attempts` + `locked_until`).
- [ ] `storage/` not web-accessible (`.htaccess` deny).
- [ ] File uploads MIME-sniffed (`finfo_file`), not just trusted by extension.
- [ ] EXIF stripped from photos.
- [ ] Verification doc access requires admin session + signed temporary URL.
- [ ] Contact info hidden until match (server-side gate, not just CSS).
- [ ] Per-day request limit enforced at SQL level (atomic `UPDATE ... WHERE daily_request_count < 10`).
- [ ] Reject-cooldown enforced (`WHERE rejected_until IS NULL OR rejected_until < NOW()`).

---

## 11. Out of Scope (locked from PRD §8)

iOS, AI matching, Nepali UI, payments, in-app chat, push, SMS, direct video upload, WebSockets, third-party logins, advanced analytics, multi-admin roles, public API, calendar/meeting tools, NDA/signing.

---

## 12. Acceptance Criteria (per phase)

| Phase | Done means... |
|---|---|
| 1 | `/` renders styled with no errors. DB extensions applied. Login as `investor@nepal.com` / `Demo@2026` works (manual password reset done). |
| 2A | Signup creates user + sends verification email (to `storage/mail/`). Login + logout + reset all work end-to-end. |
| 2B | Homepage carousels driven by DB. FAQs render from `faqs` table. About/legal/support/how-it-works render. |
| 2C | Investor can create, edit, save preferences, upload docs. Public profile renders with contacts hidden if no match. |
| 2D | Business owner lists business; entrepreneur creates pitch with deck upload + photos; dashboards show real metrics. |
| 2E | Franchise + Advisor profiles save and appear in browse. |
| 2F | All 4 browse screens filter and paginate. Smart Suggestions tab shows scored matches for logged-in verified users. |
| 2G | Interest request flow works end-to-end including 10/day limit + 60-day reject cooldown. Match record created on accept. Contacts revealed. |
| 2H | Admin can approve/reject verifications with reason, suspend users, hide pitches, run analytics, send broadcasts, manage sectors/FAQs/homepage content. |

---

## 13. Demo Credentials (post-foundation)

| Email | Password | Role |
|---|---|---|
| admin@investmatch.com | Demo@2026 | admin (is_admin=1) |
| investor@nepal.com | Demo@2026 | investor (verified) |
| anjali@aarohan.com | Demo@2026 | entrepreneur (verified) |
| sunita@vc.com | Demo@2026 | investor (verified) |

---

## 14. Open Risks

| Risk | Mitigation |
|---|---|
| Inline-style extraction may miss design tokens used in screens we don't read | Foundation agent must scan all 30 screens before locking the design system |
| 50+ column `pitches` table mixes PRD v1 fields with later additions | Use it as-is; ignore unused cols in v1 forms |
| `users.role` is varchar — typos possible | Centralize role constants in `config/helpers.php` (`define('ROLE_INVESTOR', 'investor')`); use everywhere |
| Existing Laravel `sessions` table conflicts with PHP native sessions | PHP sessions write to filesystem by default, no collision. Leave Laravel `sessions` table dormant. |
| Old seeded password hashes unrecoverable | Foundation explicitly resets to `Demo@2026` via `password_hash()` |

---

*End of execution.md — last edited before code lands.*
