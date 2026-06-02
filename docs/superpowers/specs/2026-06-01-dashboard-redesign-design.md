# Dashboard Redesign — Design Spec

Date: 2026-06-01
Status: Phase 1 approved

## Goal
Transform the dashboard into a modern premium B2B SaaS experience matching the
provided screenshot (SMERGERS / AngelList / PitchBook feel), **without** changing
any business logic, APIs, routes, or data. Stack stays native: PHP server-rendered
HTML + CSS + vanilla JS (no React/Recharts/Framer Motion).

## Decisions (from brainstorming)
- Native PHP + CSS + vanilla JS (reproduce the look, no framework).
- Phased: **Phase 1** = design system + dashboard chrome + investor home. Other
  roles/pages follow in later phases reusing the same system.
- Charts = inline SVG sparklines (vanilla), real data where it exists, flat baseline otherwise.
- `APP_URL` becomes environment-aware so assets/links resolve locally in dev.

## Design tokens
Radius: cards 16 / buttons·inputs 12 / dialogs 20. Shadows: card `0 2px 10px rgba(0,0,0,.04)`,
hover `0 10px 30px rgba(0,0,0,.08)`. 8px spacing scale. Type: Inter — page 32/700,
section 20/600, card 16/600, body 14/400, caption 13/500. Colors: brand red primary,
success #10B981, warning #F59E0B, info #3B82F6, bg #F8FAFC, card #FFF, border #E5E7EB,
text #111827 / #6B7280. Added as `--dash-*` tokens to keep existing vars intact.

## Architecture
- **Chrome** rendered in PHP (not JS) for dashboard pages: fixed topbar (logo · bell ·
  user menu) + fixed 280px sidebar (role links, soft-red active pill + left indicator).
  `includes/layout-dashboard.php` sets `$dashChrome=true`; `header.php` then skips its
  JS `injectHeader`; `footer.php` closes the `<main>` wrapper. Public pages unchanged.
- **Reusable UI** in `includes/ui.php`: `page_header`, `stat_card`, `quick_action_card`,
  `section_header`, `recommendation_card`, `activity_timeline`, `chart_card`, `sparkline`,
  `pro_tip_card`, `empty_state`. Role-agnostic; reused by later phases.
- **Investor home** (`investor/dashboard.php`) recomposed with these partials using the
  existing queries (interest sent, matches, saved, smart suggestions, notifications).
- All new chrome links prefixed with `APP_URL` so they work in dev and prod.

## Out of scope (later phases)
Owner/entrepreneur/franchisor/advisor/admin dashboards, Connections, Notifications,
Profile, Documents, Settings — adopt the same shell + partials per phase.

## Constraints
Keep all routes, queries, CSRF, and role guards. No data migrations. The other role
dashboards inherit the new shell immediately but keep their old content styling until
their phase; verify they still render.
