<?php
if (defined('APP_NAME')) return;
define('APP_NAME', 'Asaan Capital Ltd');
define('APP_NAME_LONG', 'Asaan Capital Ltd - Financial & Investment Services');

// Environment-aware base URL. On localhost the app lives under /assan, so assets
// and APP_URL-prefixed links resolve to the dev server; everything else uses prod.
$__host = $_SERVER['HTTP_HOST'] ?? '';
if ($__host === 'localhost' || $__host === '127.0.0.1' || str_starts_with($__host, 'localhost:') || str_starts_with($__host, '127.0.0.1:')) {
    define('APP_URL', 'http://' . $__host . '/assan');
} else {
    define('APP_URL', 'https://asaancapital.com');
}
unset($__host);

define('DEBUG_MODE', true);

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');

// --- Production DB (comment out for local dev) ---
// define('DB_NAME', 'asaancapital_assan_capital');
// define('DB_USER', 'asaancapital_asaancapital');
// define('DB_PASS', 'J3ssEl.*}@OrYzmy');

// --- Local dev DB ---
define('DB_NAME', 'asaancapital_assan_capital');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('SESSION_LIFETIME', 1800);
define('CSRF_TOKEN_NAME', '_csrf');

define('UPLOAD_MAX_BYTES', 10485760);
define('UPLOAD_MAX_BYTES_PHOTO', 2097152);
define('UPLOAD_ALLOWED_MIME', 'application/pdf,image/jpeg,image/png,image/webp');

// MAIL_DRIVER: 'smtp' to send via SMTP server, 'log' to write emails to storage/mail/.
// When 'smtp' is selected but no SMTP credentials are found, the mailer falls
// back to the log driver automatically so the app never errors during local dev.
// Live SMTP credentials are loaded from the `email_settings` DB table (admin UI).
// The constants below are fallback defaults only.
define('MAIL_DRIVER', 'smtp');
define('MAIL_FROM', 'noreply@asaancapital.com');
define('MAIL_FROM_NAME', 'Asaan Capital Ltd');
define('MAIL_DOMAIN', 'asaancapital.com');

// --- SMTP fallback (used only when email_settings DB table is empty) ---
define('SMTP_HOST', 'mail.asaancapital.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');   // 'tls' (port 587) or 'ssl' (port 465)
define('SMTP_USER', '');
define('SMTP_PASS', '');

define('STORAGE_PATH', __DIR__ . '/../storage');
define('PUBLIC_UPLOADS_PATH', __DIR__ . '/../public/uploads');
