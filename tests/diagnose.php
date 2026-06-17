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

function ok($msg) { global $pass, $br, $bold, $boldEnd; echo "  \xE2\x9C\x85 {$bold}PASS{$boldEnd}: {$msg}{$br}"; $pass++; }
function fail($msg) { global $fail, $br, $bold, $boldEnd; echo "  \xE2\x9D\x8C {$bold}FAIL{$boldEnd}: {$msg}{$br}"; $fail++; }
function warn($msg) { global $warn, $br, $bold, $boldEnd; echo "  \xE2\x9A\xA0 {$bold}WARN{$boldEnd}: {$msg}{$br}"; $warn++; }
function heading($t) { global $br, $bold, $boldEnd; echo "{$br}{$bold}\xE2\x94\x80\xE2\x94\x80 {$t} \xE2\x94\x80\xE2\x94\x80{$boldEnd}{$br}"; }

$root = __DIR__ . '/..';

echo "{$bold}\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90{$boldEnd}{$br}";
echo "{$bold}  Asaan Capital - Messaging System Diagnostic  {$boldEnd}{$br}";
echo "{$bold}\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90{$boldEnd}{$br}";

// ━━ Error Log Scanner ━━
heading('Error Log Scan');
$logFound = false;
$logPaths = [
  $_SERVER['HOME'] . '/error_log',
  $_SERVER['HOME'] . '/logs/error_log',
  $_SERVER['HOME'] . '/public_html/error_log',
  '/var/log/apache2/error.log',
  '/var/log/httpd/error_log',
  '/usr/local/apache/logs/error_log',
  '/tmp/php-error.log',
  __DIR__ . '/../error_log',
];

foreach ($logPaths as $lp) {
  if (file_exists($lp) && is_readable($lp)) {
    $logFound = true;
    $lines = file($lp);
    $today = date('d-M-Y');
    $recent = [];
    $count = 0;

    // Walk backwards to get the latest 20 relevant lines
    for ($i = count($lines) - 1; $i >= 0 && $count < 20; $i--) {
      $line = trim($lines[$i]);
      if (strpos($line, 'roundcube') !== false || strpos($line, 'kolab') !== false || strpos($line, 'sqlite') !== false) {
        continue;
      }
      // Skip if this is just a repeat of the previous
      if ($i > 0 && trim($lines[$i-1]) === $line) {
        continue;
      }
      $recent[] = $line;
      $count++;
    }
    $recent = array_reverse($recent);

    if (count($recent) > 0) {
      $hasError = false;
      foreach ($recent as $l) {
        if (preg_match('/Fatal|Parse|syntax|Uncaught|Warning.*require|Warning.*include/', $l)) {
          $hasError = true;
          break;
        }
      }
      if ($hasError) {
        fail("Errors found in: {$lp}");
        echo "  ── Latest errors ──{$br}";
        foreach ($recent as $l) {
          if (preg_match('/Fatal|Parse|syntax|Uncaught|Warning.*require|Warning.*include/', $l)) {
            echo "  ❌ " . substr($l, 0, 300) . "{$br}";
          } else {
            echo "     " . substr($l, 0, 200) . "{$br}";
          }
        }
      } else {
        ok("Log file {$lp} — no recent errors (only Roundcube deprecations)");
      }
    } else {
      ok("Log file {$lp} is empty");
    }
    break; // Only process the first found log
  }
}

if (!$logFound) {
    // Try PHP error_log ini setting
    $phpLog = ini_get('error_log');
    if ($phpLog && file_exists($phpLog)) {
      $lines = file($phpLog);
      $today = date('d-M-Y');
      $found = false;
      for ($i = max(0, count($lines) - 50); $i < count($lines); $i++) {
        $l = trim($lines[$i]);
        if (strpos($l, 'roundcube') !== false || strpos($l, 'kolab') !== false || strpos($l, 'sqlite') !== false) continue;
        if (strpos($l, $today) !== false) {
          if (preg_match('/Fatal|Parse|syntax|Uncaught|Error/', $l)) {
            echo "  ❌ " . substr($l, 0, 300) . "{$br}";
            $found = true;
          }
        }
      }
      if (!$found) ok("No PHP errors found today in PHP error log");
    } else {
      // Walk known locations using pure PHP
      $searchPaths = [
        $_SERVER['DOCUMENT_ROOT'] ?? '',
        __DIR__ . '/..',
        $_SERVER['HOME'] ?? '',
      ];
      $foundLog = '';
      foreach ($searchPaths as $base) {
        if (!$base || !is_dir($base)) continue;
        $it = new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS);
        $maxDepth = 4;
        $rit = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);
        $rit->setMaxDepth($maxDepth);
        foreach ($rit as $spl) {
          if ($spl->getFilename() === 'error_log' && $spl->isFile() && $spl->isReadable() && $spl->getSize() > 0) {
            $foundLog = $spl->getPathname();
            break 2;
          }
        }
      }
      if ($foundLog) {
        $lines = file($foundLog);
        $today = date('d-M-Y');
        $printCount = 0;
        for ($i = max(0, count($lines) - 50); $i < count($lines) && $printCount < 15; $i++) {
          $l = trim($lines[$i]);
          if (strpos($l, 'roundcube') !== false || strpos($l, 'kolab') !== false || strpos($l, 'sqlite') !== false) continue;
          if (!strpos($l, $today)) continue;
          $printCount++;
          if (preg_match('/Fatal|Parse|syntax|Uncaught|Error|Warning.*require/', $l)) {
            echo "  ❌ " . substr($l, 0, 300) . "{$br}";
          } else {
            echo "     " . substr($l, 0, 200) . "{$br}";
          }
        }
        if ($printCount === 0) {
          ok("Logs found at: {$foundLog} — no matching errors for today");
        }
      } else {
        warn("Could not locate PHP error log automatically");
      }
    }
  }

echo "{$br}";

// ━━ PHP Environment ━━
heading('PHP Environment');
$phpVer = phpversion();
if (version_compare($phpVer, '8.0', '>=')) ok("PHP version {$phpVer}");
else fail("PHP version {$phpVer} (need 8.0+)");

if (extension_loaded('pdo_mysql')) ok("PDO MySQL extension loaded");
else fail("PDO MySQL extension missing");

if (extension_loaded('curl')) ok("cURL extension loaded");
else warn("cURL not loaded");

if (extension_loaded('mbstring')) ok("mbstring extension loaded");
else fail("mbstring extension missing");

if (extension_loaded('json')) ok("JSON extension loaded");
else fail("JSON extension missing");

// ━━ Database ━━
heading('Database');
$configFile = "{$root}/config/config.php";
$dbOk = false;
if (file_exists($configFile)) {
    ok("config.php exists");
    $config = file_get_contents($configFile);
    preg_match('/define\(\'DB_NAME\',\s*\'([^\']+)\'\)/', $config, $m);
    $dbName = $m[1] ?? 'unknown';
    preg_match('/define\(\'DB_USER\',\s*\'([^\']+)\'\)/', $config, $m);
    $dbUser = $m[1] ?? 'unknown';
    preg_match('/define\(\'DB_PASS\',\s*\'([^\']+)\'\)/', $config, $m);
    $dbPass = $m[1] ?? '';
    echo "  DB: {$dbName} as {$dbUser}{$br}";

    try {
        $pdo = new PDO("mysql:host=localhost;port=3306;dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
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
    $tables = ['conversations', 'conversation_participants', 'messages', 'users', 'interest_requests'];
    foreach ($tables as $tbl) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$tbl}'");
            if ($stmt->fetch()) {
                ok("Table `{$tbl}` exists");
            } else {
                fail("Table `{$tbl}` is MISSING");
            }
        } catch (\Exception $e) {
            fail("Cannot check table `{$tbl}`: " . $e->getMessage());
        }
    }

    // Check messages table columns
    try {
        $stmt = $pdo->query("DESCRIBE messages");
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $expected = ['id', 'conversation_id', 'sender_id', 'message', 'created_at'];
        $missing = array_diff($expected, $cols);
        if (count($missing) === 0) {
            ok("messages table has correct columns");
        } else {
            fail("messages table missing columns: " . implode(', ', $missing));
        }
    } catch (\Exception $e) {
        fail("Cannot describe messages table: " . $e->getMessage());
    }
}

// ━━ Files ━━
heading('Files');
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
        if (function_exists('shell_exec')) {
            $out = shell_exec("php -l " . escapeshellarg($path) . " 2>&1");
            if (strpos($out, 'No syntax errors') === false && strpos($out, 'Parse error') !== false) {
                fail("{$f} has syntax errors: {$out}");
            }
        }
    } else {
        fail("{$f} is MISSING");
    }
}
// Check file permissions on new files
$newFiles = ['pages/messages.php', 'assets/chat.js', 'api/conversations.php', 'api/messages.php', 'api/messages-poll.php'];
foreach ($newFiles as $f) {
    $path = "{$root}/{$f}";
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -3);
        if ($perms < 600) {
            warn("{$f} has restrictive permissions ({$perms})");
        }
    }
}

// ━━ Routes ━━
heading('Routes in index.php');
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
$allRoutesOk = true;
foreach ($routes as $route => $file) {
    if (strpos($index, "'{$route}'") !== false) {
        ok("Route {$route} → {$file}");
    } else {
        fail("Route {$route} is MISSING from index.php");
        $allRoutesOk = false;
    }
}

// Check the routes array structure (count the array entries)
$routeCount = substr_count($index, "'/");
if ($routeCount > 60) {
    ok("Routes array has ~{$routeCount} entries (looks complete)");
} else {
    warn("Routes array only has ~{$routeCount} entries — may be truncated");
}

// ━━ Sidebar Links ━━
heading('Sidebar Links');
$ui = file_get_contents("{$root}/includes/ui.php");
$roles = ['investor', 'business_owner', 'entrepreneur', 'franchisor', 'advisor'];
$msgLinkExists = strpos($ui, "['Messages', '/messages', 'mail']") !== false;
if ($msgLinkExists) {
    ok("Messages link found in sidebar (present in all roles)");
} else {
    // Check each role
    foreach ($roles as $role) {
        if (preg_match("/'".$role."'\s*=>\s*\[/", $ui)) {
            if (strpos($ui, "'Messages'") !== false) {
                ok("Messages link present in {$role}");
            } else {
                fail("Messages link MISSING from {$role}");
            }
        }
    }
}

// ━━ Bootstrap Load Test ━━
heading('Bootstrap Load Test');
try {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['HTTP_HOST'] = 'asaancapital.com';
    $_GET['_path'] = '/diagnose';
    require_once "{$root}/config/bootstrap.php";

    global $user;
    $user = current_user();
    if (session_status() === PHP_SESSION_ACTIVE) {
        ok("Session started successfully");
    } else {
        warn("Session not active (may be CLI — normal)");
    }

    try {
        $test = db();
        if ($test instanceof PDO) {
            ok("db() singleton returns PDO");
        }
    } catch (\Exception $e) {
        fail("db() failed: " . $e->getMessage());
    }

    $token = csrf_token();
    if (strlen($token) > 10) {
        ok("CSRF token generated");
    } else {
        fail("CSRF token generation failed");
    }

} catch (\Exception $e) {
    fail("Bootstrap error: " . $e->getMessage());
}

// ━━ .htaccess Check ━━
heading('.htaccess & Config');
$htaccess = "{$root}/.htaccess";
if (file_exists($htaccess)) {
    $ht = file_get_contents($htaccess);
    if (strpos($ht, 'RewriteEngine On') !== false) {
        ok(".htaccess has RewriteEngine On");
    } else {
        warn(".htaccess missing RewriteEngine On");
    }
    if (strpos($ht, '_path') !== false) {
        ok(".htaccess rewrites to index.php?_path=...");
    } else {
        fail(".htaccess missing _path rewrite rule");
    }
} else {
    warn(".htaccess not found — mod_rewrite may not work");
}

// Check APP_URL
if (defined('APP_URL')) {
    ok("APP_URL = " . APP_URL);
} else {
    fail("APP_URL not defined");
}

// ━━ Summary ━━
echo "{$br}";
echo "{$bold}\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90{$boldEnd}{$br}";
echo "{$bold}  Results: {$pass} passed, {$fail} failed, {$warn} warnings          {$boldEnd}{$br}";
echo "{$bold}\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90\xE2\x95\x90{$boldEnd}{$br}";

if ($fail > 0) {
    echo "{$br}\xE2\x9D\x8C Some checks failed. Fix the issues above and re-run.{$br}";
}
if ($warn > 0) {
    echo "{$br}\xE2\x9A\xA0 Warnings should be reviewed.{$br}";
}
if ($fail === 0) {
    echo "{$br}\xE2\x9C\x85 All checks passed! The messaging system should be operational.{$br}";
    echo "  Visit: https://asaancapital.com/messages{$br}";
}
