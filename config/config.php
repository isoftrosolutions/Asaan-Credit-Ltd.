<?php
if (defined('APP_NAME')) return;
define('APP_NAME', 'Asaan Marketplace');
define('APP_URL', 'https://asaancapital.com');
define('DEBUG_MODE', false);

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'asaancapital_assan_capital');
define('DB_USER', 'asaancapital_asaancapital');
define('DB_PASS', 'J3ssEl.*}@OrYzmy');
define('DB_CHARSET', 'utf8mb4');

define('SESSION_LIFETIME', 1800);
define('CSRF_TOKEN_NAME', '_csrf');

define('UPLOAD_MAX_BYTES', 10485760);
define('UPLOAD_MAX_BYTES_PHOTO', 2097152);
define('UPLOAD_ALLOWED_MIME', 'application/pdf,image/jpeg,image/png,image/webp');

define('MAIL_DRIVER', 'log');
define('MAIL_FROM', 'noreply@investmatch.com');
define('MAIL_FROM_NAME', 'Asaan Marketplace');

define('STORAGE_PATH', __DIR__ . '/../storage');
define('PUBLIC_UPLOADS_PATH', __DIR__ . '/../public/uploads');
