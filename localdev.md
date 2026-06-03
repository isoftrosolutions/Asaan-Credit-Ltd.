# Local Development Setup

App runs under `http://localhost/assan` via Apache.

## Prerequisites

- Apache 2.4+ with `mod_rewrite` enabled
- PHP 8.1+
- MySQL 8.0 (service name: `MySQL80`)
- Browser with hard-refresh (Ctrl+F5)

## Database

| Setting     | Value           |
|-------------|-----------------|
| Host        | 127.0.0.1       |
| Port        | 3306            |
| Database    | `invest_match`  |
| User        | `root`          |
| Password    | *(empty)*       |

The `invest_match` database already exists with all tables. Schema dump: `database/final.sql`.

## Configuration changes

### `config/config.php`

| Change | Before | After |
|--------|--------|-------|
| DB credentials | Production DB (commented-out local) | Local DB (`root`, no password, `invest_match`) |
| Debug mode | `false` | `true` |

On localhost, `APP_URL` auto-resolves to `http://localhost/assan`. On production it uses `https://asaancapital.com`.

### `includes/header.php` (line 82)

Added `<script>window.BASE_URL = '<?= APP_URL ?>';</script>` before loading `header.js`. This lets client-side JavaScript resolve asset paths correctly regardless of environment.

### `assets/header.js` (lines 168, 173, 278, 295)

Changed logo paths from hard-coded `/logo.png` to dynamic:

```js
// Before (broken on localhost):
<img src="/logo.png" ...>

// After (works everywhere):
<img src="${window.BASE_URL || ''}/logo.png" ...>
```

App runs under `/assan/` on localhost, so `/logo.png` resolves to `http://localhost/logo.png` (404). The `BASE_URL` prefix makes it resolve to `http://localhost/assan/logo.png` (200).

## Running

Start Apache and MySQL services, then open `http://localhost/assan` in a browser. Hard-refresh (Ctrl+F5) after code changes.
