# Asaan Capital Ltd — Marketplace

## Stack

- **PHP 8.2+** (no framework), **MySQL 8.4**, **Apache mod_rewrite**
- Vanilla JS + CSS (no build step, no npm/composer, no webpack/vite)
- **No linter, no static analysis, no CI/CD** — no `composer.json`, no `phpunit.xml`, no CI workflows

## Dev setup

- App runs at `http://localhost/assan` (auto-detected via `HTTP_HOST` in `config/config.php`)
- Local DB: `asaancapital_assan_capital`, root user, no password. Uncomment local creds in `config/config.php` + set `DEBUG_MODE` to `true`
- PHP lint: `php -l path/to/file.php`
- No live-reload; hard-refresh (Ctrl+F5) after changes
- Create upload directories: `php setup.php` (creates `public/uploads/business-documents/`)
- **README is stale** — it mentions `invest_match` DB and `database/schema-extensions.sql`/`seed.sql` files that do not exist. The full schema lives in the root SQL dump file.

## Routing

- `.htaccess` rewrites all non-file requests to `index.php?_path=...`
- Routes: flat `$routes` array in `index.php:7-98` + regex patterns for `/browse/*`, `/investor/N`, `/business/{id|slug}`, `/pitch/N`, `/franchise/N`, `/blog/*`, `/search`, `/terms`, `/privacy`, `/faq`
- Pages accessed directly (not routed) load `bootstrap.php` themselves with the re-entry guard `BOOTSTRAP_LOADED`
- Business detail routing is unique: accepts numeric `id` or slug via regex in `index.php:125-133`

## Bootstrap & conventions

- `config/bootstrap.php` loads config, session, DB, helpers, CSRF, flash, auth, mailer, upload in order
- `db()` singleton (PDO, `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, real prepared statements)
- Escape HTML: `e()` alias for `htmlspecialchars`
- Redirect: `redirect($path)` (prepends `APP_URL`)
- Current user: `current_user()` returns `$_SESSION['user']` or null; auto-refreshes session from DB and checks premium expiry on every call
- Auth guards: `require_login()`, `require_role($role)`, `require_admin()`, `require_verified()`, `require_premium()`
- File uploads: `handle_upload()` in `config/upload.php`, MIME-sniffed, EXIF-stripped via GD
- `money(N)` → `रू 1,000` (NPR); `date_human()` → relative time; `generate_slug()` → URL-safe slug
- Pagination: `paginate()` helper + `render_pagination()` HTML builder; expects a `PDOStatement` for count query

## Layout system

- `includes/header.php` — opens `<html>`, nav. Used by public pages.
- `includes/layout-dashboard.php` — sidebar + topbar shell (Phase 1 redesign, pure PHP chrome). Sets `$dashChrome = true`.
- `includes/footer.php` — closes `</main></div>` if `$dashChrome`, injects `injectFooter()` JS if not `$hidePublicFooter`
- Page-specific SEO: set `$pageTitle`, `$pageDescription` before including header
- Two header rendering modes: JS-injected React-style header (default) vs `$useStitchHeader` server-rendered variant. Dashboard pages use `$dashChrome` and skip JS injection.

## Email system

- `EmailService` singleton in `includes/email-service.php`
- `MAIL_DRIVER` = `'smtp'` or `'log'`. Log driver writes `.html` files to `storage/mail/`
- Falls back to log driver automatically when SMTP credentials are blank (safe local dev default)
- Templates in `config/email_templates.php`, can be overridden via DB `email_templates` table
- Manual PHPMailer dependency in `lib/PHPMailer/` (no Composer)
- Backward-compat wrappers in `config/mailer.php`: `send_mail()`, `send_mail_smtp()`, `send_verification_email()`, etc.

## Tests

- **One proper automated test**: `tests/email-system-test.php` — custom test runner, no phpunit. Covers singleton, SMTP config, templates, placeholder replacement, validation, log driver, HTML-to-text, and backward-compat wrappers.
- Additional files in `tests/` are one-off diagnostic scripts (not automated test suites): `db-check.php`, `e2e-test.php`, `pitch-check.php`, etc.
- Run: `php tests/email-system-test.php`
- Test bootstrap redefines constants to avoid needing a live DB; uses log driver for SMTP tests

## CSRF

- All POST routes must call `csrf_check()` at the top of the handler
- Token: `csrf_token()` → hidden input named `_csrf`
- Expired/invalid tokens get HTTP 419

## .htaccess

- Denies direct access to `storage/` (403), blocks `.sql`, `.md`, `.log` files
- Security headers: `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`
- Canonical redirects: `/discover/businesses.php` → `/browse/businesses`, `business/detail.php?id=N` → `/business/N`

## Design system

- Live CSS variables in `assets/styles.css` (`--color-primary: #6B1D22`, dark-maroon scheme)
- `design-system/asaan-capital/MASTER.md` describes a **different** palette (`--color-primary: #0F172A`, dark-blue)
- The CSS file is the source of truth; the design doc is aspirational reference

## Database

- **No full schema SQL file** — only migration SQL files in `database/` (prefixed `migration_NNN_`). The complete schema exists in the root dump file and the live DB.
- Table count: ~30+ tables (`users`, `businesses`, `investor_profiles`, `entrepreneur_profiles`, `franchise_profiles`, `advisor_profiles`, `interest_requests`, `matches`, `notifications`, `messages`, `premium_subscriptions`, `email_settings`, `email_templates`, `email_log`, `site_settings`, `admin_audit_log`, `saved_listings`, etc.)

## Real-time messaging

- `ws-server/` contains a Node.js WebSocket server for real-time messaging (`server.js`). Declared unused on most hosting (per `.gitignore`). The AJAX polling fallback lives in `api/messages-poll.php`. Install deps with `npm install` in `ws-server/` to use.

## Project structure

| Directory | Purpose |
|-----------|---------|
| `config/` | Bootstrap, DB, helpers, auth, CSRF, mailer, flash, upload |
| `includes/` | Header, footer, layouts, UI dashboard kit, EmailService |
| `assets/` | `styles.css`, `header.js`, `components.js`, `icons.js`, `chat.js`, `form-steps.js`, `onboarding.js`, images |
| `auth/` | Login, signup, password reset, email verification |
| `pages/` | Homepage, about, support, blog, valuation, legal, 404, CMS-driven pages |
| `investor/`, `business/`, `entrepreneur/`, `franchise/`, `advisor/` | Role-specific dashboards + profiles |
| `admin/` | User management, verification queue, broadcast, email settings, analytics, content CMS |
| `api/` | AJAX handlers (notifications, smart-suggestions, toggle-save, upload, messaging, conversations, users) |
| `discover/` | Browse + search pages with filtered pagination |
| `connections/` | Interest requests (10/day limit, 60-day reject cooldown), match logic |
| `notifications/` | In-app notification feed, settings, saved listings |
| `database/` | Incremental migration SQL files (no full schema file) |
| `public/uploads/` | Avatars, photos, logos (subdirs: `business-thumbnails/`, `business-photos/`, `business-documents/`, `payment-receipts/`) |
| `storage/` | Verification docs, pitch decks, mail logs |
| `ws-server/` | Node.js WebSocket server for real-time messaging (optional) |
| `scripts/` | Utility scripts (`replace-user.php`) |
| `app/` | **Unrelated** Divya Jyotish desktop app download page — not part of Asaan Capital |