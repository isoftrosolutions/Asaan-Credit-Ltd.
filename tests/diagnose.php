<?php
/**
 * Messaging System - Production Diagnostic & Fix
 * Run: php tests/diagnose.php
 * Or via web: https://asaancapital.com/tests/diagnose.php
 */

$isCli = php_sapi_name() === 'cli';
$br = $isCli ? "\n" : "<br>\n";
$bold = $isCli ? "**" : "<strong>";
$boldEnd = $isCli ? "**" : "</strong>";
$pass = 0;
$fail = 0;
$warn = 0;

function ok($msg) { global $pass, $br, $bold, $boldEnd; echo "  ✅ {$bold}PASS{$boldEnd}: {$msg}{$br}"; $pass++; }
function fail($msg) { global $fail, $br, $bold, $boldEnd; echo "  ❌ {$bold}FAIL{$boldEnd}: {$msg}{$br}"; $fail++; }
function warn($msg) { global $warn, $br, $bold, $boldEnd; echo "  ⚠️  {$bold}WARN{$boldEnd}: {$msg}{$br}"; $warn++; }

$root = __DIR__ . '/..';

echo "{$bold}═══════════════════════════════════════════════{$boldEnd}{$br}";
echo "{$bold}  Asaan Capital - Messaging System Diagnostic  {$boldEnd}{$br}";
echo "{$bold}═══════════════════════════════════════════════{$boldEnd}{$br}{$br}";

// ── 1. PHP Version ──
echo "{$bold}── PHP Environment ──{$boldEnd}{$br}";
$phpVer = phpversion();
if (version_compare($phpVer, '8.0', '>=')) ok("PHP version {$phpVer}");
else fail("PHP version {$phpVer} (need 8.0+)");

if (extension_loaded('pdo_mysql')) ok("PDO MySQL extension loaded");
else fail("PDO MySQL extension missing");

if (extension_loaded('curl')) ok("cURL extension loaded");
else warn("cURL not loaded (message notifications will silently fail)");

if (extension_loaded('mbstring')) ok("mbstring extension loaded");
else fail("mbstring extension missing");

echo "{$br}";

// ── 2. Database Connection ──
echo "{$bold}── Database ──{$boldEnd}{$br}";
$configFile = "{$root}/config/config.php";
$dbOk = false;
if (file_exists($configFile)) {
    ok("config.php exists");
    $config = file_get_contents($configFile);
    preg_match('/define\(\'DB_NAME\',\s*\'([^\']+)\'\)/', $config, $m);
    $dbName = $m[1] ?? 'unknown';
    preg_match('/define\(\'DB_USER\',\s*\'([^\']+)\'\)/', $config, $m);
    $dbUser = $m[1] ?? 'unknown';
    echo "  DB: {$dbName} as {$dbUser}{$br}";

    try {
        $pdo = new PDO("mysql:host=localhost;port=3306;dbname={$dbName};charset=utf8mb4", $dbUser, 'J3ssEl.*}@OrYzmy', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3,
        ]);
        ok("Database connection successful");
        $dbOk = true;
    } catch (\Exception $e) {
        fail("Database connection failed: " . $e->getMessage());
    }
} else {
    fail("config.php not found at {$configFile}");
}

if ($dbOk) {
    $tables = ['conversations', 'conversation_participants', 'messages'];
    foreach ($tables as $tbl) {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$tbl}'");
        if ($stmt->fetch()) {
            ok("Table `{$tbl}` exists");
        } else {
            fail("Table `{$tbl}` is MISSING — run: mysql ... < database/migration_messages.sql");
        }
    }
}
echo "{$br}";

// ── 3. File Checks ──
echo "{$bold}── Files ──{$boldEnd}{$br}";
$files = [
    'pages/messages.php',
    'assets/chat.js',
    'assets/styles.css',
    'api/conversations.php',
    'api/messages.php',
    'api/messages-poll.php',
    'api/conversation-mark-read.php',
    'api/conversation-unread.php',
    'api/users.php',
    'index.php',
    'includes/ui.php',
    'config/bootstrap.php',
];
foreach ($files as $f) {
    $path = "{$root}/{$f}";
    if (file_exists($path)) {
        ok("{$f} exists");
        // Check syntax
        $out = shell_exec("php -l " . escapeshellarg($path) . " 2>&1");
        if (strpos($out, 'No syntax errors') !== false) {
            // ok implicitly
        } else {
            fail("{$f} has syntax errors: {$out}");
        }
    } else {
        fail("{$f} is MISSING");
    }
}
echo "{$br}";

// ── 4. Route Registration ──
echo "{$bold}── Routes in index.php ──{$boldEnd}{$br}";
$index = file_get_contents("{$root}/index.php");
$routes = [
    '/messages' => 'pages/messages.php',
    '/api/conversations' => 'api/conversations.php',
    '/api/messages' => 'api/messages.php',
    '/api/messages-poll' => 'api/messages-poll.php',
    '/api/conversations/mark-read' => 'api/conversation-mark-read.php',
    '/api/conversation-unread' => 'api/conversation-unread.php',
    '/api/users' => 'api/users.php',
];
foreach ($routes as $route => $file) {
    if (strpos($index, "'{$route}'") !== false) {
        ok("Route {$route} → {$file}");
    } else {
        fail("Route {$route} is MISSING from index.php");
    }
}

// Check the $routes array has the right closing bracket
if (preg_match('/\$routes\s*=\s*\[/', $index)) {
    ok("Routes array found");
} else {
    fail("Routes array not found in index.php");
}
echo "{$br}";

// ── 5. Sidebar Links ──
echo "{$bold}── Sidebar Links in ui.php ──{$boldEnd}{$br}";
$ui = file_get_contents("{$root}/includes/ui.php");
$roles = ['investor', 'business_owner', 'entrepreneur', 'franchisor', 'advisor'];
foreach ($roles as $role) {
    if (preg_match("/'".$role."'\s*=>\s*\[/", $ui)) {
        if (strpos($ui, "['Messages', '/messages', 'mail']") !== false) {
            ok("Messages link in {$role} sidebar");
        } else {
            fail("Messages link MISSING from {$role} sidebar");
        }
    }
}
echo "{$br}";

// ── 6. Bootstrap Load ──
echo "{$bold}── Bootstrap Test ──{$boldEnd}{$br}";
try {
    // Simulate minimal bootstrap
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['HTTP_HOST'] = 'asaancapital.com';
    $_GET['_path'] = '/';
    require_once "{$root}/config/bootstrap.php";
    ok("Bootstrap loads successfully");
} catch (\Exception $e) {
    fail("Bootstrap error: " . $e->getMessage());
}
echo "{$br}";

// ── 7. Fix Attempts ──
echo "{$bold}── Fixes ──{$boldEnd}{$br}";

// Fix: ensure .htaccess allows the new routes
$htaccess = "{$root}/.htaccess";
if (file_exists($htaccess)) {
    $ht = file_get_contents($htaccess);
    if (strpos($ht, 'RewriteEngine On') !== false) {
        ok(".htaccess has RewriteEngine On");
    } else {
        warn(".htaccess missing RewriteEngine On — rewrites won't work");
    }
} else {
    warn(".htaccess not found — mod_rewrite may not be enabled");
}

// Fix: check file permissions (www-data or the right user)
$perms = fileperms("{$root}/index.php");
$permStr = substr(sprintf('%o', $perms), -4);
if (intval($permStr) >= 644) {
    ok("File permissions look OK (index.php: {$permStr})");
} else {
    warn("Tight permissions (index.php: {$permStr}) — may cause issues for new files");
}

// Fix: session directory writable
$sessPath = session_save_path() ?: sys_get_temp_dir();
if (is_writable($sessPath)) {
    ok("Session path writable: {$sessPath}");
} else {
    warn("Session path not writable: {$sessPath}");
}

echo "{$br}";
echo "{$bold}═══════════════════════════════════════════════{$boldEnd}{$br}";
echo "{$bold}  Results: {$pass} passed, {$fail} failed, {$warn} warnings          {$boldEnd}{$br}";
echo "{$bold}═══════════════════════════════════════════════{$boldEnd}{$br}";

if ($fail > 0) {
    echo "{$br}❌ Some checks failed. Fix the issues above and re-run.{$br}";
}
if ($warn > 0) {
    echo "{$br}⚠️  Warnings should be reviewed but may not block functionality.{$br}";
}
if ($fail === 0) {
    echo "{$br}✅ All checks passed! The messaging system should be operational.{$br}";
}
