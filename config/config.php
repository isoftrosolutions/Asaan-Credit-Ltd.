<?php
if (defined('APP_NAME')) return;
define('APP_NAME', 'Asaan Capital Ltd - Financial & Investment Services');

// Environment-aware base URL. On localhost the app lives under /assan, so assets
// and APP_URL-prefixed links resolve to the dev server; everything else uses prod.
$__host = $_SERVER['HTTP_HOST'] ?? '';
if ($__host === 'localhost' || $__host === '127.0.0.1' || str_starts_with($__host, 'localhost:') || str_starts_with($__host, '127.0.0.1:')) {
    define('APP_URL', 'http://' . $__host . '/assan');
} else {
    define('APP_URL', 'https://asaancapital.com');
}
unset($__host);

define('DEBUG_MODE', false);

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');

// --- Local dev DB (uncomment for local dev) ---
// define('DB_NAME', 'invest_match');
// define('DB_USER', 'root');
// define('DB_PASS', '');

// --- Production DB ---
define('DB_NAME', 'asaancapital_assan_capital');
define('DB_USER', 'asaancapital_asaancapital');
define('DB_PASS', 'J3ssEl.*}@OrYzmy');
define('DB_CHARSET', 'utf8mb4');

define('SESSION_LIFETIME', 1800);
define('CSRF_TOKEN_NAME', '_csrf');

define('UPLOAD_MAX_BYTES', 10485760);
define('UPLOAD_MAX_BYTES_PHOTO', 2097152);
define('UPLOAD_ALLOWED_MIME', 'application/pdf,image/jpeg,image/png,image/webp');

// MAIL_DRIVER: 'smtp' to send via Gmail/SMTP, 'log' to write emails to storage/mail/.
// When 'smtp' is selected but SMTP_USER/SMTP_PASS are blank, the mailer falls
// back to the log driver automatically so the app never errors during local dev.
define('MAIL_DRIVER', 'smtp');
define('MAIL_FROM', 'noreply@asaancapital.com');
define('MAIL_FROM_NAME', 'Asaan Capital Ltd');

// --- SMTP (Gmail) ---
// SMTP_USER = your full Gmail address, SMTP_PASS = a Gmail "App Password"
// (Google Account → Security → 2-Step Verification → App passwords).
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');   // 'tls' (port 587) or 'ssl' (port 465)
define('SMTP_USER', '');
define('SMTP_PASS', '');

define('STORAGE_PATH', __DIR__ . '/../storage');
define('PUBLIC_UPLOADS_PATH', __DIR__ . '/../public/uploads');
