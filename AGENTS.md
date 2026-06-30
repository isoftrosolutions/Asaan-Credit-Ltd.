# Asaan Capital Ltd — Marketplace

## Stack

- **PHP 8.2+** (no framework), **MySQL 8.4**, **Apache mod_rewrite**
- Vanilla JS + CSS (no build step, no npm/composer, no webpack/vite)
- **No linter, no static analysis, no CI/CD** — no `composer.json`, no `phpunit.xml`, no CI workflows
- PHP lint: `php -l path/to/file.php`

## Dev setup

- App runs at `http://localhost/assan` (auto-detected via `HTTP_HOST` in `config/config.php`)
- Local DB: `asaancapital_assan_capital`, root user, no password. Uncomment local creds + set `DEBUG_MODE=true`
- `config/config.php` is in `.gitignore` — local changes never committed
- Create upload directories: `php setup.php` (creates `public/uploads/business-documents/`, `public/uploads/team/`, `public/uploads/resumes/`). Safe to re-run.
- No live-reload; hard-refresh (Ctrl+F5) after changes
- **README is stale** — references `invest_match` DB + non-existent `database/schema-extensions.sql`/`seed.sql`. Full schema is in the root SQL dump file. Migrations are in `database/migration_NNN_*.sql`
- **Lint**: `php -l path/to/file.php` for PHP files. Mobile app has its own lint/typecheck (`npm run lint`, `npm run ts:check` in `mobile-app/`)

## Routing

- `.htaccess` rewrites all non-file requests to `index.php?_path=...`
- Flat `$routes` array in `index.php:16-141` + regex patterns for `/browse/*`, `/blog/*`, `/investor/N`, `/business/{id|slug}`, `/pitch/N`, `/franchise/N` (lines 143-200)
- Business detail routing at `index.php:180-188`: accepts numeric `id` or slug, excludes literal paths `create|edit|download`
- CMS-driven slugs (e.g. `/terms`, `/privacy`) that match `[a-z0-9-]+` fall through to `pages/page-cms.php` (line 162)
- Pages accessed directly (not routed) load `bootstrap.php` themselves with the re-entry guard `BOOTSTRAP_LOADED` — e.g. `pages/index.php`, `pages/404.php`

## Bootstrap & conventions

- `config/bootstrap.php` loads config → session → DB → helpers → CSRF → flash → auth → mailer → upload in order; if path starts with `api/`, also loads `api/helpers.php`
- `db()` singleton (PDO, `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, real prepared statements)
- Escape HTML: `e()` alias for `htmlspecialchars`
- Redirect: `redirect($path)` (prepends `APP_URL`); `redirect_back()` uses `$_SERVER['HTTP_REFERER']`
- Current user: `current_user()` returns `$_SESSION['user']` or null; auto-refreshes session from DB and checks premium expiry on every call
- Auth guards: `require_login()`, `require_role($role)` (accepts string or array), `require_admin()`, `require_verified()`, `require_premium()`
- File uploads: `handle_upload()` in `config/upload.php`, MIME-sniffed, EXIF-stripped via GD
- `money(N)` → `रू 1,000` (NPR); `date_human()` → relative time; `generate_slug()` → URL-safe slug; `public_base_url()` → dynamic request base URL (used in emails)
- `old($key)` → flashed old input value; `site_setting($key)` → DB `site_settings` table value
- Pagination: `paginate()` helper + `render_pagination()` HTML builder; expects a `PDOStatement` for count query

## Layout system

- `includes/header.php` — opens `<html>`, nav. Used by public pages. Guarantees bootstrap is loaded (re-entry safe via `BOOTSTRAP_LOADED`)
- `includes/layout-dashboard.php` — sidebar + topbar shell. Sets `$dashChrome = true`.
- `includes/footer.php` — closes `</main></div>` if `$dashChrome`; injects `injectFooter()` JS if not `$hidePublicFooter`
- Page-specific SEO: set `$pageTitle`, `$pageDescription` before including header
- Two public header rendering modes: default JS-injected via `assets/header.js` (set `$useStitchHeader = false`) vs server-rendered `$useStitchHeader` variant (`includes/stitch-header.php`). Dashboard pages use `$dashChrome` and skip JS injection entirely.
- `$hidePublicFooter = true` suppresses the JS-injected public footer (used on dashboards).
- Dashboard links in JS (`assets/header.js:7-44`) must mirror `includes/ui.php`

## Email system

- `EmailService` singleton in `includes/email-service.php`
- `MAIL_DRIVER` = `'smtp'` or `'log'`. Log driver writes `.html` files to `storage/mail/`
- Falls back to log driver automatically when SMTP credentials are blank (safe local dev default)
- Templates in `config/email_templates.php`, can be overridden via DB `email_templates` table
- Manual PHPMailer in `lib/PHPMailer/` (no Composer)
- Backward-compat wrappers in `config/mailer.php`: `send_mail()`, `send_mail_smtp()`, `send_verification_email()`, etc.

### REST API

- All API endpoints at `/api/*` return JSON with `{success, data?, error?, meta?}` format
- API helpers loaded from `api/helpers.php` automatically when `_path` starts with `api/`
- Auth via Bearer token (mobile) or existing session cookie (web). Token stored in `user_api_tokens` table
- `api/helpers.php` provides `json_response()`, `json_error()`, `json_success()`, `require_api_auth()`, `require_api_role()`, `api_paginate()`, `get_json_input()`, `cors_headers()`
- `has_otp_verified`, `is_premium`, `is_featured`, `is_visible_to_contacts` added to listings when user is premium
- `_api_auth_user()` checks Bearer token from `Authorization` header or falls back to `current_user()` session
- Registering an account via API automatically generates a mobile API token in `user_api_tokens`
- `/api/send-inquiry` converted to JSON (was using flash+redirect)

## Tests

- **One proper automated test**: `tests/email-system-test.php` — custom test runner, no phpunit. Covers singleton, SMTP config, templates, placeholders, validation, log driver, HTML-to-text, backward-compat wrappers.
- Run: `php tests/email-system-test.php`
- Test bootstrap redefines `BOOTSTRAP_LOADED` to bypass live DB; uses log driver for SMTP tests
- Other files in `tests/` are one-off diagnostic scripts (not automated suites): `db-check.php`, `e2e-test.php`, `pitch-check.php`, etc.

## CSRF

- All POST routes must call `csrf_check()` at the top of the handler
- Token: `csrf_token()` → hidden input named `_csrf`
- Expired/invalid tokens get HTTP 419

## .htaccess

- Denies direct access to `storage/` (403), blocks `.sql`, `.md`, `.log` files
- Security headers: `nosniff`, `SAMEORIGIN`, `Referrer-Policy`, `Strict-Transport-Security`, `Permissions-Policy` (geolocation/microphone/camera/payment blocked)
- Canonical redirects: `/discover/businesses.php` → `/browse/businesses`, `/business/detail.php?id=N` → `/business/N`
- `mod_rewrite` must be enabled; `.htaccess` overrides must be allowed

## Design system & assets

- **`assets/styles.css`** is the live CSS source of truth (4.5k+ lines). CSS custom properties define color (`--color-primary: #6B1D22`), typography, spacing, shadows, motion.
- **`design.md`** at root is the authoritative 323-line design system document describing brand colors, typography, components, iconography, and motion.
- `design-system/asaan-capital/MASTER.md` describes an aspirational **different** palette (`--color-primary: #0F172A`, dark-blue) — do not use as source of truth.
- `assets/header.js` — JS-injected nav (dashboard links must mirror `includes/ui.php`)
- `assets/components.js` — shared UI components (footer injection, etc.)

## Database

- No single full-schema SQL file in migrations. Migrations live in `database/migration_NNN_*.sql` files (applied incrementally). Full schema exists only in the root SQL dump file and the live DB.
- ~30+ tables: `users`, `businesses`, `investor_profiles`, `entrepreneur_profiles`, `franchise_profiles`, `advisor_profiles`, `interest_requests`, `matches`, `notifications`, `messages`, `premium_subscriptions`, `email_settings`, `email_templates`, `email_log`, `site_settings`, `admin_audit_log`, `saved_listings`, etc.

## Real-time messaging

- `ws-server/` contains a Node.js WebSocket server (`server.js`). Declared unused on most hosting (per `.gitignore`). The AJAX polling fallback lives in `api/messages-poll.php`. Install deps with `npm install` in `ws-server/` to use.

## OpenCode config

- `.opencode/skills/ui-ux-pro-max/` — custom UI/UX skill file. Agents working on frontend should load it via the skill tool.
- `.opencode/package.json` has `@opencode-ai/plugin` and `@kilocode/plugin` deps (for IDE integration).
- No `opencode.json` at root — config is minimal.

## Project structure

| Directory | Purpose |
|-----------|---------|
| `config/` | Bootstrap, DB, helpers, auth, CSRF, mailer, flash, upload |
| `includes/` | Header, footer, layouts, EmailService, UI kit (`ui.php`) |
| `assets/` | `styles.css`, `header.js`, `components.js`, `icons.js`, `chat.js`, `form-steps.js`, `onboarding.js`, images |
| `auth/` | Login, signup, password reset, email verification |
| `pages/` | Homepage, about, support, blog, valuation, legal, 404, CMS-driven pages |
| `investor/`, `business/`, `entrepreneur/`, `franchise/`, `advisor/` | Role-specific dashboards + profiles |
| `admin/` | User management, verification queue, broadcast, email settings, analytics, content CMS |
| `api/` | JSON REST API + AJAX handlers. Auth (register/login/logout/me/forgot-password/reset-password/verify-email/resend-otp), listings (businesses/investors/pitches/franchises with filters + pagination), details (business/investor/pitch/franchise), search, blog, sectors, send-inquiry, upload, messaging, notifications, saved-listings |
| `discover/` | Browse + search pages with filtered pagination |
| `connections/` | Interest requests (10/day limit, 60-day reject cooldown), match logic |
| `notifications/` | In-app notification feed, settings, saved listings |
| `database/` | Incremental migration SQL files |
| `public/uploads/` | Avatars, photos, logos (subdirs: `business-thumbnails/`, `business-photos/`, `business-documents/`, `payment-receipts/`) |
| `storage/` | Verification docs, pitch decks, mail logs |
| `ws-server/` | Node.js WebSocket server for real-time messaging (optional) |
| `scripts/` | Utility scripts (`replace-user.php`) |
| `mobile-app/` | Expo (React Native) mobile app — SDK 54. Run `npx expo start` from this dir |
| `app/` | **Unrelated** Divya Jyotish desktop app download page — not part of Asaan Capital |
