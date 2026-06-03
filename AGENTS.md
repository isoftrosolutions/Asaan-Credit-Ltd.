# Asaan Capital Ltd — Marketplace

## Stack

- **PHP 8.2+** (no framework), **MySQL 8.4**, **Apache mod_rewrite**
- Vanilla JS + CSS (no build step, no npm/composer, no webpack/vite)
- **No linter, no static analysis, no CI/CD** — no `composer.json`, no `phpunit.xml`, no CI workflows

## Dev setup

- App runs at `http://localhost/assan` (auto-detected via `HTTP_HOST` in `config/config.php`)
- Local DB: `invest_match`, root user, no password. Uncomment local creds in `config/config.php` + set `DEBUG_MODE` to `true`
- PHP lint: `php -l path/to/file.php`
- No live-reload; hard-refresh (Ctrl+F5) after changes

## Routing

- `.htaccess` rewrites all non-file requests to `index.php?_path=...`
- Routes: flat `$routes` array in `index.php:7-68` + regex patterns for `/browse/*`, `/investor/N`, `/business/N`, `/pitch/N`, `/franchise/N`, `/blog/*`
- Pages accessed directly (not routed) load `bootstrap.php` themselves with the re-entry guard `BOOTSTRAP_LOADED`

## Bootstrap & conventions

- `config/bootstrap.php` loads config, session, DB, helpers, CSRF, flash, auth, mailer, upload in order
- `db()` singleton (PDO, `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, real prepared statements)
- Escape HTML: `e()` alias for `htmlspecialchars`
- Redirect: `redirect($path)` (prepends `APP_URL`)
- Current user: `current_user()` returns `$_SESSION['user']` or null
- Auth guards: `require_login()`, `require_role($role)`, `require_admin()`, `require_verified()`
- File uploads: `handle_upload()` in `config/upload.php`, MIME-sniffed, EXIF-stripped
- `money(N)` → `NPR 1,000`; `date_human()` → relative time; `generate_slug()` → URL-safe slug

## Layout system

- `includes/header.php` — opens `<html>`, nav. Used by public pages.
- `includes/layout-dashboard.php` — sidebar + topbar shell. Sets `$dashChrome = true`.
- `includes/footer.php` — closes `</main></div>` if `$dashChrome`, injects `injectFooter()` JS if not `$hidePublicFooter`
- Page-specific SEO: set `$pageTitle`, `$pageDescription` before including header

## Email system

- `EmailService` singleton in `includes/email-service.php`
- `MAIL_DRIVER` = `'smtp'` or `'log'`. Log driver writes `.html` files to `storage/mail/`
- Falls back to log driver automatically when SMTP credentials are blank (safe local dev default)
- Templates in `config/email_templates.php`, can be overridden via DB `email_templates` table
- Manual PHPMailer dependency in `lib/PHPMailer/` (no Composer)

## Tests

- One test file: `tests/email-system-test.php` — custom test runner, no phpunit
- Run: `php tests/email-system-test.php`
- Bootstrap redefines constants to avoid needing a live DB; uses log driver for SMTP tests

## CSRF

- All POST routes must call `csrf_check()` at the top of the handler
- Token: `csrf_token()` → hidden input named `_csrf`

## .htaccess

- Denies direct access to `storage/` (403), blocks `.sql`, `.md`, `.log` files
- Security headers: `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`

## Design system

- Live CSS variables in `assets/styles.css` (`--color-primary: #6B1D22`, dark-maroon scheme)
- `design-system/asaan-capital/MASTER.md` describes a **different** palette (`--color-primary: #0F172A`, dark-blue)
- The CSS file is the source of truth; the design doc is aspirational reference

## Project structure

| Directory | Purpose |
|-----------|---------|
| `config/` | Bootstrap, DB, helpers, auth, CSRF, mailer, flash, upload |
| `includes/` | Header, footer, layouts, UI helpers, EmailService |
| `assets/` | `styles.css`, `header.js`, `components.js`, `icons.js` |
| `auth/` | Login, signup, password reset, email verification |
| `pages/` | Homepage, about, support, blog, valuation, legal, 404 |
| `investor/`, `business/`, `entrepreneur/`, `franchise/`, `advisor/` | Role-specific dashboards + profiles |
| `admin/` | User management, verification queue, broadcast, email settings, analytics |
| `api/` | AJAX handlers (notifications, smart-suggestions, toggle-save, upload) |
| `discover/` | Browse + search pages |
| `connections/` | Interest requests, match logic |
| `notifications/` | In-app notification feed |
| `database/` | Schema (`final.sql`), seed data, email migrations |
| `public/uploads/` | Avatars, photos, logos |
| `storage/` | Verification docs, pitch decks, mail logs |
