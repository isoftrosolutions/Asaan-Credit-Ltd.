<?php
/**
 * End-to-End Test: Business & Pitch Visibility + Image Upload
 *
 * Run: php tests/e2e-test.php
 */

declare(strict_types=1);

$passed = 0;
$failed = 0;
$errors = [];

function test(string $label, callable $fn): void {
    global $passed, $failed, $errors;
    try { $fn(); echo "  \e[32mPASS\e[0m  $label\n"; $passed++; }
    catch (\Throwable $e) { echo "  \e[31mFAIL\e[0m  $label\n        \e[33m" . $e->getMessage() . "\e[0m\n"; $failed++; $errors[] = "FAIL: $label — " . $e->getMessage(); }
}

function assert_true($val, string $msg): void { if ($val !== true) throw new \RuntimeException("Expected true — $msg"); }
function assert_contains(string $h, string $n, string $msg): void { if (strpos($h, $n) === false) throw new \RuntimeException("Expected to find '$n' — $msg"); }

/* Bootstrap */
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Asaan Capital Test');
    define('APP_URL', 'http://localhost/assan');
    define('DEBUG_MODE', true);
    define('DB_HOST', '127.0.0.1'); define('DB_PORT', '3306');
    define('DB_NAME', 'invest_match'); define('DB_USER', 'root'); define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4'); define('SESSION_LIFETIME', 1800);
    define('CSRF_TOKEN_NAME', '_csrf'); define('UPLOAD_MAX_BYTES', 10485760);
    define('UPLOAD_MAX_BYTES_PHOTO', 2097152);
    define('UPLOAD_ALLOWED_MIME', 'application/pdf,image/jpeg,image/png,image/webp');
    define('MAIL_DRIVER', 'log'); define('MAIL_FROM', 't@t.com'); define('MAIL_FROM_NAME', 'T');
    define('SMTP_HOST', ''); define('SMTP_PORT', 0); define('SMTP_ENCRYPTION', '');
    define('SMTP_USER', ''); define('SMTP_PASS', '');
    define('STORAGE_PATH', __DIR__ . '/../storage');
    define('PUBLIC_UPLOADS_PATH', __DIR__ . '/../public/uploads');
    define('BOOTSTRAP_LOADED', true);
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

// Auth stubs for dashboard includes
if (!function_exists('current_user')) {
    function current_user(): ?array { return $_SESSION['user'] ?? null; }
    function require_login(): void { if (!current_user()) throw new \RuntimeException('Not logged in'); }
    function require_admin(): void { $u = current_user(); if (!$u || empty($u['is_admin'])) throw new \RuntimeException('Admin required'); }
    function require_role(string|array $role): void { $u = current_user(); $roles = is_array($role) ? $role : [$role]; if (!$u || !in_array($u['role'], $roles, true)) throw new \RuntimeException('Role required'); }
}
if (!function_exists('flash_set')) { function flash_set(string $k, string $v): void {} }
if (!function_exists('flash_get')): function flash_get(string $k): ?string { return null; } endif;
if (!function_exists('csrf_check')) { function csrf_check(string $n = '_csrf'): void {} }
if (!function_exists('csrf_token')) { function csrf_token(string $n = '_csrf'): string { return 'test-token'; } }
if (!function_exists('redirect_back')) { function redirect_back(): void { throw new \RuntimeException('redirect_back'); } }
if (!function_exists('redirect')) { function redirect(string $p): void { throw new \RuntimeException('redirect:' . $p); } }
if (!function_exists('admin_log')) { function admin_log(string $a, string $t, int $i): void {} }
if (!function_exists('flash_render')) { function flash_render(): void {} }

$pdo = db();

/* ── HTTP fetch ── */
function fetch(string $url): string {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10, CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => ['User-Agent: E2ETest/1.0'],
    ]);
    $html = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200) throw new \RuntimeException("HTTP $code for $url");
    return $html;
}

/* ── Dashboard renderer ── */
function renderDashboard(string $file, array $userData): string {
    while (ob_get_level()) ob_end_clean();
    $_SESSION = ['user' => $userData];
    ob_start();
    try { require $file; return ob_get_clean(); }
    catch (\Throwable $e) { ob_end_clean(); throw $e; }
}

/* ========================================================================== */
/* Setup                                                                      */
/* ========================================================================== */
echo "\n\e[36m=== Setup Test Data ===\e[0m\n";

$suffix = date('Ymd_His');
$testData = ['suffix' => $suffix];

// Sector
$st = $pdo->query("SELECT COALESCE(MAX(id), 0) + 1 FROM sectors LIMIT 1");
$nextSectorId = (int)$st->fetchColumn();
$pdo->prepare("INSERT INTO sectors (id, name, slug) VALUES (?, ?, ?)")
    ->execute([$nextSectorId, 'E2E Sector ' . $suffix, 'e2e-sector-' . $suffix]);
$testData['sector_id'] = $nextSectorId;

// Users
function mkUser(PDO $pdo, string $role, string $suffix): array {
    $email = "e2e_{$role}_{$suffix}@t.asaancapital.com";
    $st = $pdo->prepare("SELECT id, email, role FROM users WHERE email = ?"); $st->execute([$email]);
    if ($r = $st->fetch()) return $r;
    $now = date('Y-m-d H:i:s');
    $pdo->prepare("INSERT INTO users (email, password, name, role, is_admin, verification_status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'verified', ?, ?)")
        ->execute([$email, password_hash('test', PASSWORD_DEFAULT), 'E2E ' . ucfirst($role) . ' ' . $suffix, $role, $role === 'admin' ? 1 : 0, $now, $now]);
    $id = (int)$pdo->lastInsertId();
    return ['id' => $id, 'email' => $email, 'role' => $role, 'is_admin' => $role === 'admin' ? 1 : 0, 'name' => 'E2E ' . ucfirst($role) . ' ' . $suffix, 'verification_status' => 'verified'];
}
$testData['owner'] = mkUser($pdo, 'business_owner', $suffix);
$testData['entrepreneur'] = mkUser($pdo, 'entrepreneur', $suffix);
$testData['admin'] = mkUser($pdo, 'admin', $suffix);
$pdo->prepare("UPDATE users SET is_admin = 1, role = 'admin' WHERE id = ?")->execute([$testData['admin']['id']]);
$testData['admin']['is_admin'] = 1;

echo "  Owner#{$testData['owner']['id']}, Founder#{$testData['entrepreneur']['id']}\n";

// Images (minimal JPEG)
function mkJpeg(string $path): void {
    $dir = dirname($path); if (!is_dir($dir)) mkdir($dir, 0775, true);
    file_put_contents($path, "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00\xFF\xDB\x00\x43\x00\x08\x06\x06\x07\x06\x05\x08\x07\x07\x07\x09\x09\x08\x0A\x0C\x14\x0D\x0C\x0B\x0B\x0C\x19\x12\x13\x0F\x14\x1D\x1A\x1F\x1E\x1D\x1A\x1C\x1C\x20\x24\x2E\x27\x20\x22\x2C\x23\x1C\x1C\x28\x37\x29\x2C\x30\x31\x34\x34\x34\x1F\x27\x39\x3D\x38\x32\x3C\x2E\x33\x34\x32\xFF\xC0\x00\x0B\x08\x00\x01\x00\x01\x01\x01\x11\x00\xFF\xC4\x00\x1F\x00\x00\x01\x05\x01\x01\x01\x01\x01\x01\x00\x00\x00\x00\x00\x00\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\xFF\xC4\x00\xB5\x10\x00\x02\x01\x03\x03\x02\x04\x03\x05\x05\x04\x04\x00\x00\x00\x00\x00\x00\x00\x01\x02\x03\x11\x04\x12\x21\x31\x41\x05\x13\x51\x61\x06\x22\x71\x81\x14\x32\x91\xA1\x07\x15\x23\x42\xB1\xC1\xD1\x09\x52\xF0\x33\x62\x72\x82\x09\x0A\x16\x17\x18\x19\x1A\x25\x26\x27\x28\x29\x2A\x34\x35\x36\x37\x38\x39\x3A\x43\x44\x45\x46\x47\x48\x49\x4A\x53\x54\x55\x56\x57\x58\x59\x5A\x63\x64\x65\x66\x67\x68\x69\x6A\x73\x74\x75\x76\x77\x78\x79\x7A\x83\x84\x85\x86\x87\x88\x89\x8A\x92\x93\x94\x95\x96\x97\x98\x99\x9A\xA2\xA3\xA4\xA5\xA6\xA7\xA8\xA9\xAA\xB2\xB3\xB4\xB5\xB6\xB7\xB8\xB9\xBA\xC2\xC3\xC4\xC5\xC6\xC7\xC8\xC9\xCA\xD2\xD3\xD4\xD5\xD6\xD7\xD8\xD9\xDA\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xF1\xF2\xF3\xF4\xF5\xF6\xF7\xF8\xF9\xFA\xFF\xC0\x00\x14\x08\x00\x01\x00\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xFF\xDA\x00\x08\x01\x01\x00\x00\x3F\x00\x14\x3F\x00\xFF\xD9");
}

$thumbRelPath = 'business-thumbnails/e2e_biz_' . $suffix . '.jpg';
$thumbAbsPath = PUBLIC_UPLOADS_PATH . '/' . $thumbRelPath;
mkJpeg($thumbAbsPath);

$pitchImgRelPath = 'pitch-images/e2e_pitch_' . $suffix . '.jpg';
$pitchImgAbsPath = PUBLIC_UPLOADS_PATH . '/' . $pitchImgRelPath;
mkJpeg($pitchImgAbsPath);

echo "  Created thumbnail + pitch images\n";

// Business (rating=10 to ensure it's #1 in featured)
$bizName = 'E2E Test Business ' . $suffix;
$now = date('Y-m-d H:i:s');
$pdo->prepare("INSERT INTO businesses (user_id,business_name,slug,listing_type,sector_id,description,overview,thumbnail_url,is_published,is_featured,rating,status,created_at,updated_at) VALUES (?,?,?,'business_sale',?,'desc','overview',?,1,1,10.0,'approved',?,?)")
    ->execute([$testData['owner']['id'], $bizName, 'e2e-test-biz-' . $suffix, $testData['sector_id'], '/' . $thumbRelPath, $now, $now]);
$testData['business_id'] = (int)$pdo->lastInsertId();
echo "  Business#{$testData['business_id']}: $bizName\n";

// Pitch
$pitchTitle = 'E2E Test Pitch ' . $suffix;
$now2 = date('Y-m-d H:i:s', strtotime('+1 second'));
$pdo->prepare("INSERT INTO pitches (user_id,tagline,short_summary,problem_statement,solution,market_size,business_model,pitch_image,is_published,created_at,updated_at) VALUES (?,?,'short','prob','sol','mkt','model',?,1,?,?)")
    ->execute([$testData['entrepreneur']['id'], $pitchTitle, '/' . $pitchImgRelPath, $now2, $now2]);
$testData['pitch_id'] = (int)$pdo->lastInsertId();
echo "  Pitch#{$testData['pitch_id']}: $pitchTitle\n";

/* ========================================================================== */
/* Tests                                                                      */
/* ========================================================================== */

echo "\n\e[36m=== DB & File Tests ===\e[0m\n";
test('Business thumbnail on disk', function () use ($thumbAbsPath) { assert_true(file_exists($thumbAbsPath), 'File not found'); });
test('Pitch image on disk', function () use ($pitchImgAbsPath) { assert_true(file_exists($pitchImgAbsPath), 'File not found'); });
test('Business has thumbnail_url in DB', function () use ($pdo, $testData) {
    $st = $pdo->prepare("SELECT thumbnail_url FROM businesses WHERE id = ?"); $st->execute([$testData['business_id']]);
    assert_true(str_contains($st->fetch()['thumbnail_url'], $testData['suffix']), 'Thumbnail URL');
});
test('Pitch has pitch_image in DB', function () use ($pdo, $testData) {
    $st = $pdo->prepare("SELECT pitch_image FROM pitches WHERE id = ?"); $st->execute([$testData['pitch_id']]);
    assert_true(str_contains($st->fetch()['pitch_image'], $testData['suffix']), 'Pitch image');
});

echo "\n\e[36m=== Public Page Visibility (HTTP) ===\e[0m\n";
test('Homepage shows business name', function () use ($bizName) {
    assert_contains(fetch(APP_URL . '/'), $bizName, 'Business on homepage');
});
test('Homepage shows pitch tagline', function () use ($pitchTitle) {
    assert_contains(fetch(APP_URL . '/'), $pitchTitle, 'Pitch on homepage');
});
test('Browse businesses shows business', function () use ($bizName) {
    assert_contains(fetch(APP_URL . '/browse/businesses'), $bizName, 'Business on browse');
});
test('Browse entrepreneurs page loads (2+ pitches)', function () {
    $html = fetch(APP_URL . '/browse/entrepreneurs');
    assert_contains($html, 'verified pitches', 'Pitch count shown');
});
test('Business detail loads', function () use ($testData, $bizName) {
    assert_contains(fetch(APP_URL . '/business/' . $testData['business_id']), $bizName, 'Detail');
});
test('Business detail shows thumbnail ref', function () use ($testData) {
    assert_contains(fetch(APP_URL . '/business/' . $testData['business_id']), $testData['suffix'], 'Thumbnail in detail');
});
test('Pitch detail loads', function () use ($testData, $pitchTitle) {
    assert_contains(fetch(APP_URL . '/pitch/' . $testData['pitch_id']), $pitchTitle, 'Pitch detail');
});

echo "\n\e[36m=== Dashboard Visibility ===\e[0m\n";
test('Owner dashboard shows business', function () use ($testData, $bizName) {
    $html = renderDashboard(__DIR__ . '/../business/dashboard.php', $testData['owner']);
    assert_contains($html, $bizName, 'Owner dash');
    assert_contains($html, 'Published', 'Status badge');
});
test('Entrepreneur dashboard shows pitch', function () use ($testData, $pitchTitle) {
    $html = renderDashboard(__DIR__ . '/../entrepreneur/dashboard.php', $testData['entrepreneur']);
    assert_contains($html, $pitchTitle, 'Entrepreneur dash');
});
test('Admin dashboard loads', function () use ($testData) {
    $html = renderDashboard(__DIR__ . '/../admin/dashboard.php', $testData['admin']);
    assert_contains($html, 'Recent', 'Recent section');
});
test('Admin businesses page loads', function () use ($testData, $bizName) {
    $html = renderDashboard(__DIR__ . '/../admin/businesses.php', $testData['admin']);
    assert_contains($html, $bizName, 'Admin mgmt shows business');
    assert_contains($html, 'Delete', 'Delete action');
});

echo "\n\e[36m=== Cleanup ===\e[0m\n";
test('Cleanup', function () use ($pdo, $testData, $thumbAbsPath, $pitchImgAbsPath) {
    if (!empty($testData['business_id'])) $pdo->prepare("DELETE FROM businesses WHERE id = ?")->execute([$testData['business_id']]);
    if (!empty($testData['pitch_id'])) $pdo->prepare("DELETE FROM pitches WHERE id = ?")->execute([$testData['pitch_id']]);
    if (!empty($testData['suffix'])) $pdo->prepare("DELETE FROM users WHERE email LIKE ?")->execute(['%' . $testData['suffix'] . '@t.asaancapital.com']);
    if (!empty($testData['sector_id'])) $pdo->prepare("DELETE FROM sectors WHERE id = ?")->execute([$testData['sector_id']]);
    if ($thumbAbsPath && file_exists($thumbAbsPath)) unlink($thumbAbsPath);
    if ($pitchImgAbsPath && file_exists($pitchImgAbsPath)) unlink($pitchImgAbsPath);
    assert_true(true, 'Done');
});

/* Summary */
echo "\n" . str_repeat('=', 60) . "\n";
echo "  \e[36mResults: $passed passed, $failed failed\e[0m\n" . str_repeat('=', 60) . "\n\n";
if ($failed > 0) { echo "\e[31mFailures:\e[0m\n"; foreach ($errors as $e) echo "  - $e\n"; echo "\n"; exit(1); }
exit(0);
