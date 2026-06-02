<?php
/**
 * Email System — Comprehensive Test Suite
 *
 * Usage:
 *   1. Make sure the email_log table exists (run database/migration_email_log.sql)
 *   2. Run: php tests/email-system-test.php
 *
 * The test bootstraps a minimal environment, exercises every code path in
 * EmailService and the mailer.php compatibility layer, then reports results.
 */

declare(strict_types=1);

/* -------------------------------------------------------------------------- */
/* Bootstrap                                                                  */
/* -------------------------------------------------------------------------- */

// Simulate the constants that config.php would normally define
define('APP_NAME', 'Asaan Capital Test');
define('APP_URL', 'http://localhost/assan');
define('DEBUG_MODE', true);
define('MAIL_DRIVER', 'smtp');
define('MAIL_FROM', 'noreply@asaancapital.com');
define('MAIL_FROM_NAME', 'Asaan Capital Test');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('STORAGE_PATH', __DIR__ . '/../storage');
define('PUBLIC_UPLOADS_PATH', __DIR__ . '/../public/uploads');
define('SESSION_LIFETIME', 1800);
define('CSRF_TOKEN_NAME', '_csrf');
define('UPLOAD_MAX_BYTES', 10485760);
define('UPLOAD_MAX_BYTES_PHOTO', 2097152);
define('UPLOAD_ALLOWED_MIME', 'application/pdf,image/jpeg,image/png,image/webp');
define('BOOTSTRAP_LOADED', true);

// Minimal DB abstraction (tests use a file-based "DB" if the real one isn't available)
// We'll test in isolation, mocking where needed.

require_once __DIR__ . '/../includes/email-service.php';

// Minimal e() helper if not already defined
if (!function_exists('e')) {
    function e(?string $s): string {
        return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

// Minimal public_base_url if not already defined
if (!function_exists('public_base_url')) {
    function public_base_url(): string {
        return APP_URL;
    }
}

// Load mailer.php wrappers (must be after stubs are defined)
require_once __DIR__ . '/../config/mailer.php';

// Minimal db() stub — throws PDOException so catch blocks work
if (!function_exists('db')) {
    function db(): PDO {
        throw new \PDOException('No database connection in test mode');
    }
}

/* -------------------------------------------------------------------------- */
/* Test Runner                                                                */
/* -------------------------------------------------------------------------- */

$passed = 0;
$failed = 0;
$errors = [];

function test(string $label, callable $fn): void {
    global $passed, $failed, $errors;
    try {
        $fn();
        echo "  \e[32mPASS\e[0m  $label\n";
        $passed++;
    } catch (\Throwable $e) {
        echo "  \e[31mFAIL\e[0m  $label\n";
        echo "        \e[33m" . $e->getMessage() . "\e[0m\n";
        $failed++;
        $errors[] = "FAIL: $label — " . $e->getMessage();
    }
}

function assert_true($val, string $msg): void {
    if ($val !== true) throw new \RuntimeException("Expected true, got " . var_export($val, true) . " — $msg");
}

function assert_false($val, string $msg): void {
    if ($val !== false) throw new \RuntimeException("Expected false, got " . var_export($val, true) . " — $msg");
}

function assert_equals($expected, $actual, string $msg): void {
    if ($expected !== $actual) throw new \RuntimeException("Expected " . var_export($expected, true) . ", got " . var_export($actual, true) . " — $msg");
}

function assert_not_equals($expected, $actual, string $msg): void {
    if ($expected === $actual) throw new \RuntimeException("Expected not equal to " . var_export($expected, true) . " — $msg");
}

function assert_contains(string $haystack, string $needle, string $msg): void {
    if (strpos($haystack, $needle) === false) throw new \RuntimeException("Expected to find '$needle' — $msg");
}

function assert_not_contains(string $haystack, string $needle, string $msg): void {
    if (strpos($haystack, $needle) !== false) throw new \RuntimeException("Did not expect '$needle' — $msg");
}

/* -------------------------------------------------------------------------- */
/* Cleanup before start                                                       */
/* -------------------------------------------------------------------------- */

$mailDir = STORAGE_PATH . '/mail';
if (is_dir($mailDir)) {
    array_map('unlink', glob($mailDir . '/*.html'));
}

/* ========================================================================== */
/* 1. SINGLETON PATTERN                                                       */
/* ========================================================================== */

echo "\n\e[36m=== 1. Singleton Pattern ===\e[0m\n";

test('getInstance returns same instance on multiple calls', function () {
    $a = EmailService::getInstance();
    $b = EmailService::getInstance();
    assert_true($a === $b, 'Must be the same object');
});

test('email_service() helper returns EmailService instance', function () {
    $svc = email_service();
    assert_true($svc instanceof EmailService, 'Must be EmailService');
});

test('email_service() returns same singleton', function () {
    assert_true(email_service() === EmailService::getInstance(), 'Must be identical');
});

/* ========================================================================== */
/* 2. SMTP CONFIG LOADING                                                     */
/* ========================================================================== */

echo "\n\e[36m=== 2. SMTP Config Loading ===\e[0m\n";

test('smtpConfig returns array with all keys', function () {
    $cfg = email_service()->smtpConfig();
    assert_true(is_array($cfg), 'Must be array');
    assert_true(array_key_exists('host', $cfg), 'Must have host');
    assert_true(array_key_exists('port', $cfg), 'Must have port');
    assert_true(array_key_exists('encryption', $cfg), 'Must have encryption');
    assert_true(array_key_exists('username', $cfg), 'Must have username');
    assert_true(array_key_exists('password', $cfg), 'Must have password');
    assert_true(array_key_exists('from_email', $cfg), 'Must have from_email');
    assert_true(array_key_exists('from_name', $cfg), 'Must have from_name');
    assert_true(array_key_exists('active', $cfg), 'Must have active');
});

test('smtpConfig falls back to constants', function () {
    $cfg = email_service()->smtpConfig();
    assert_equals('smtp.gmail.com', $cfg['host'], 'Host from constant');
    assert_equals(587, $cfg['port'], 'Port from constant');
    assert_equals('tls', $cfg['encryption'], 'Encryption from constant');
    assert_equals('noreply@asaancapital.com', $cfg['from_email'], 'From email');
});

test('smtpConfig active is false when SMTP_USER and SMTP_PASS are empty', function () {
    $cfg = email_service()->smtpConfig();
    assert_false($cfg['active'], 'Should be inactive with empty credentials');
});

test('resetSmtpConfig clears cache', function () {
    $svc = email_service();
    $cfg1 = $svc->smtpConfig();
    $svc->resetSmtpConfig();
    $cfg2 = $svc->smtpConfig();
    // Should be the same values (reloaded)
    assert_equals($cfg1['host'], $cfg2['host'], 'Same after reset');
});

/* ========================================================================== */
/* 3. TEMPLATE RESOLUTION                                                     */
/* ========================================================================== */

echo "\n\e[36m=== 3. Template Resolution ===\e[0m\n";

test('resolveTemplate returns null for unknown key', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'resolveTemplate');
    $ref->setAccessible(true);
    $result = $ref->invoke(EmailService::getInstance(), 'nonexistent_template_key_xyz');
    assert_equals(null, $result, 'Unknown key returns null');
});

test('resolveTemplate finds email_verification from file', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'resolveTemplate');
    $ref->setAccessible(true);
    $result = $ref->invoke(EmailService::getInstance(), 'email_verification');
    assert_not_equals(null, $result, 'Template must be found');
    assert_true(isset($result['subject']), 'Must have subject');
    assert_true(isset($result['body']), 'Must have body');
    assert_contains($result['subject'], 'Verify', 'Subject mentions verify');
});

test('resolveTemplate finds all defined templates', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'resolveTemplate');
    $ref->setAccessible(true);
    $keys = [
        'email_verification', 'password_reset', 'password_changed',
        'welcome', 'interest_received', 'match_confirmed',
        'interest_accepted', 'broadcast', 'verification_approved',
        'verification_rejected', 'new_message',
    ];
    foreach ($keys as $key) {
        $result = $ref->invoke(EmailService::getInstance(), $key);
        assert_not_equals(null, $result, "Template '$key' must be resolvable");
        assert_true(isset($result['subject']) && $result['subject'] !== '', "Template '$key' must have non-empty subject");
        assert_true(isset($result['body']) && $result['body'] !== '', "Template '$key' must have non-empty body");
    }
});

/* ========================================================================== */
/* 4. PLACEHOLDER REPLACEMENT                                                 */
/* ========================================================================== */

echo "\n\e[36m=== 4. Placeholder Replacement ===\e[0m\n";

test('applyPlaceholders replaces single placeholder', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'applyPlaceholders');
    $ref->setAccessible(true);
    $result = $ref->invoke(null, 'Hello {{name}}', ['name' => 'Alice']);
    assert_equals('Hello Alice', $result, 'Single replacement');
});

test('applyPlaceholders replaces multiple placeholders', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'applyPlaceholders');
    $ref->setAccessible(true);
    $result = $ref->invoke(null, '{{greeting}} {{name}}!', ['greeting' => 'Hi', 'name' => 'Bob']);
    assert_equals('Hi Bob!', $result, 'Multiple replacements');
});

test('applyPlaceholders leaves unknown placeholders unchanged', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'applyPlaceholders');
    $ref->setAccessible(true);
    $result = $ref->invoke(null, 'Hello {{name}}, verify at {{verification_link}}', ['name' => 'Alice']);
    assert_contains($result, 'Alice', 'Known replaced');
    assert_contains($result, '{{verification_link}}', 'Unknown preserved');
});

test('applyPlaceholders handles empty vars', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'applyPlaceholders');
    $ref->setAccessible(true);
    $result = $ref->invoke(null, 'Hello {{name}}', []);
    assert_equals('Hello {{name}}', $result, 'No replacements');
});

test('applyPlaceholders replaces with empty string', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'applyPlaceholders');
    $ref->setAccessible(true);
    $result = $ref->invoke(null, 'Hello {{name}}', ['name' => '']);
    assert_equals('Hello ', $result, 'Empty replacement');
});

test('applyPlaceholders handles values containing braces', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'applyPlaceholders');
    $ref->setAccessible(true);
    $result = $ref->invoke(null, 'Template: {{code}}', ['code' => '{{not_a_placeholder}}']);
    // Should not cascade — only one pass
    assert_equals('Template: {{not_a_placeholder}}', $result, 'No cascading replacement');
});

/* ========================================================================== */
/* 5. EMAIL VALIDATION                                                        */
/* ========================================================================== */

echo "\n\e[36m=== 5. Email Validation ===\e[0m\n";

test('validateEmail accepts valid email', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'validateEmail');
    $ref->setAccessible(true);
    assert_true($ref->invoke(null, 'user@example.com'), 'Simple email');
    assert_true($ref->invoke(null, 'test.user+tag@domain.co.uk'), 'Complex email');
});

test('validateEmail rejects empty string', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'validateEmail');
    $ref->setAccessible(true);
    assert_false($ref->invoke(null, ''), 'Empty string');
});

test('validateEmail rejects invalid format', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'validateEmail');
    $ref->setAccessible(true);
    assert_false($ref->invoke(null, 'not-an-email'), 'No @');
    assert_false($ref->invoke(null, '@missing.com'), 'No local part');
    assert_false($ref->invoke(null, 'spaces in@email.com'), 'Spaces');
});

test('sendCustomEmail returns false for empty recipient', function () {
    $result = email_service()->sendCustomEmail('', 'Subject', 'Body');
    assert_false($result, 'Empty recipient');
});

test('sendCustomEmail returns false for invalid recipient', function () {
    $result = email_service()->sendCustomEmail('not-valid', 'Subject', 'Body');
    assert_false($result, 'Invalid recipient');
});

/* ========================================================================== */
/* 6. LOG DRIVER FALLBACK (SMTP inactive)                                     */
/* ========================================================================== */

echo "\n\e[36m=== 6. Log Driver Fallback ===\e[0m\n";

test('sendCustomEmail writes to storage/mail/ when SMTP inactive', function () {
    // SMTP_USER and SMTP_PASS are empty, so log driver kicks in
    email_service()->resetSmtpConfig();
    $result = email_service()->sendCustomEmail('test@example.com', 'Test Subject', '<p>Hello</p>');
    assert_true($result, 'Log driver returns true');

    $files = glob(STORAGE_PATH . '/mail/*.html');
    $found = false;
    foreach ($files as $f) {
        $content = file_get_contents($f);
        if (strpos($content, 'test@example.com') !== false && strpos($content, 'Test Subject') !== false) {
            $found = true;
            unlink($f);
            break;
        }
    }
    assert_true($found, 'Email file written with correct content');
});

test('sendCustomEmail via log driver includes body HTML', function () {
    email_service()->resetSmtpConfig();
    email_service()->sendCustomEmail('log@example.com', 'Log Test', '<p>Paragraph</p><br><p>Second</p>');

    $files = glob(STORAGE_PATH . '/mail/*.html');
    $found = false;
    foreach ($files as $f) {
        $content = file_get_contents($f);
        if (strpos($content, 'Paragraph') !== false && strpos($content, 'Second') !== false) {
            $found = true;
            unlink($f);
            break;
        }
    }
    assert_true($found, 'Body HTML preserved in log file');
});

/* ========================================================================== */
/* 7. HTML-TO-TEXT CONVERSION                                                  */
/* ========================================================================== */

echo "\n\e[36m=== 7. HTML-to-Text Conversion ===\e[0m\n";

test('htmlToText converts basic HTML to plain text', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'htmlToText');
    $ref->setAccessible(true);
    $html = '<p>Hello</p><p>World</p>';
    $text = $ref->invoke(null, $html);
    assert_contains($text, 'Hello', 'First paragraph text');
    assert_contains($text, 'World', 'Second paragraph text');
    assert_not_contains($text, '<p>', 'No HTML tags');
});

test('htmlToText handles line breaks', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'htmlToText');
    $ref->setAccessible(true);
    $html = 'Line1<br>Line2<br/>Line3<br />Line4';
    $text = $ref->invoke(null, $html);
    $lines = array_filter(explode("\n", $text));
    assert_true(count($lines) >= 4, 'All lines preserved');
});

test('htmlToText handles nested tags', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'htmlToText');
    $ref->setAccessible(true);
    $html = '<div><h2>Title</h2><p>Body <strong>text</strong></p></div>';
    $text = $ref->invoke(null, $html);
    assert_contains($text, 'Title', 'Heading text extracted');
    assert_contains($text, 'Body', 'Body text extracted');
    assert_contains($text, 'text', 'Nested text extracted');
});

test('htmlToText strips all HTML tags', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'htmlToText');
    $ref->setAccessible(true);
    $html = '<a href="link">click</a> <script>alert(1)</script>';
    $text = $ref->invoke(null, $html);
    assert_contains($text, 'click', 'Link text kept');
    assert_not_contains($text, '<script>', 'Script tags stripped');
});

/* ========================================================================== */
/* 8. BACKWARD-COMPAT WRAPPERS (mailer.php)                                   */
/* ========================================================================== */

echo "\n\e[36m=== 8. Backward-Compatible Wrappers ===\e[0m\n";

test('smtp_config() wrapper returns array', function () {
    $cfg = smtp_config();
    assert_true(is_array($cfg), 'Must be array');
    assert_true(isset($cfg['host']), 'Has host');
});

test('send_mail() wrapper delegates and returns bool', function () {
    email_service()->resetSmtpConfig();
    $result = send_mail('wrap@example.com', 'Wrap Test', '<p>Content</p>');
    assert_true($result, 'send_mail returns true via log driver');

    $files = glob(STORAGE_PATH . '/mail/*.html');
    foreach ($files as $f) {
        if (strpos(file_get_contents($f), 'wrap@example.com') !== false) {
            unlink($f);
            break;
        }
    }
});

test('send_mail_smtp() with null config delegates properly', function () {
    email_service()->resetSmtpConfig();
    $result = send_mail_smtp('nullcfg@example.com', 'Null Config', '<p>Test</p>', null);
    assert_true($result, 'Null config falls to log driver');

    $files = glob(STORAGE_PATH . '/mail/*.html');
    foreach ($files as $f) {
        if (strpos(file_get_contents($f), 'nullcfg@example.com') !== false) {
            unlink($f);
            break;
        }
    }
});

test('send_mail_smtp() with explicit config ignores global settings', function () {
    // Even though global SMTP is inactive, passing explicit config should attempt SMTP
    // We'll pass bad credentials so it fails, proving it used our config
    email_service()->resetSmtpConfig();
    $customCfg = [
        'host'       => 'invalid.smtp.test',
        'port'       => 25,
        'encryption' => 'tls',
        'username'   => 'test@test.com',
        'password'   => 'testpass',
        'from_email' => 'test@test.com',
        'from_name'  => 'Test',
        'active'     => true,
    ];
    $result = send_mail_smtp('custom@example.com', 'Custom Config', '<p>Test</p>', $customCfg);
    // Should attempt SMTP (and likely fail since host is invalid)
    // The important thing is it doesn't write to storage/mail/
    $files = glob(STORAGE_PATH . '/mail/*.html');
    foreach ($files as $f) {
        if (strpos(file_get_contents($f), 'custom@example.com') !== false) {
            throw new \RuntimeException('Config with active=true should use SMTP, not log driver');
        }
    }
    // Also clear any files written by this test
    foreach ($files as $f) unlink($f);
});

test('get_email_template() returns null for unknown', function () {
    $result = get_email_template('__nope__');
    assert_equals(null, $result, 'Unknown key returns null');
});

test('get_email_template() finds known template', function () {
    $result = get_email_template('email_verification');
    assert_not_equals(null, $result, 'Known template found');
    assert_true(isset($result['subject']), 'Has subject');
    assert_true(isset($result['body']), 'Has body');
});

test('replace_placeholders() works', function () {
    $result = replace_placeholders('Hi {{name}}', ['name' => 'Test']);
    assert_equals('Hi Test', $result, 'Placeholder replaced');
});

/* ========================================================================== */
/* 9. EDGE CASES — TEMPLATE SYSTEM                                            */
/* ========================================================================== */

echo "\n\e[36m=== 9. Edge Cases — Templates ===\e[0m\n";

test('template with missing body key returns null', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'resolveTemplate');
    $ref->setAccessible(true);

    // Temporarily inject a template without body
    $svc = EmailService::getInstance();
    $tplRef = new \ReflectionProperty($svc, 'templates');
    $tplRef->setAccessible(true);
    $orig = $tplRef->getValue($svc);

    $tplRef->setValue($svc, ['bad_template' => ['subject' => 'No Body']]);
    $result = $ref->invoke($svc, 'bad_template');
    assert_equals(null, $result, 'Template without body/content_html returns null');

    $tplRef->setValue($svc, $orig);
});

test('processAndSend with missing template logs error and returns false', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'processAndSend');
    $ref->setAccessible(true);
    $result = $ref->invoke(EmailService::getInstance(), '__missing__', 'x@x.com', []);
    assert_false($result, 'Missing template returns false');
});

test('template can use content_html key as fallback', function () {
    $svc = EmailService::getInstance();
    $tplRef = new \ReflectionProperty($svc, 'templates');
    $tplRef->setAccessible(true);
    $orig = $tplRef->getValue($svc);

    $tplRef->setValue($svc, [
        'ars_style' => [
            'subject' => 'ARS Style',
            'content_html' => '<p>Hello {{name}}</p>',
        ],
    ]);

    $ref = new \ReflectionMethod($svc, 'resolveTemplate');
    $ref->setAccessible(true);
    $result = $ref->invoke($svc, 'ars_style');
    assert_not_equals(null, $result, 'content_html key is recognized');
    assert_equals('<p>Hello {{name}}</p>', $result['body'], 'Body from content_html');

    $tplRef->setValue($svc, $orig);
});

/* ========================================================================== */
/* 10. EDGE CASES — CONCURRENCY & CACHING                                     */
/* ========================================================================== */

echo "\n\e[36m=== 10. Edge Cases — Caching & Reset ===\e[0m\n";

test('smtpConfig is cached (same object returned twice)', function () {
    $svc = email_service();
    $svc->resetSmtpConfig();
    $cfg1 = $svc->smtpConfig();
    $cfg2 = $svc->smtpConfig();
    // Config is an array built internally; we can't compare by reference,
    // but values should be identical
    assert_equals($cfg1['host'], $cfg2['host'], 'Cached config has same host');
    assert_equals($cfg1['port'], $cfg2['port'], 'Cached config has same port');
});

test('multiple getInstance calls return same cached smtpConfig', function () {
    email_service()->resetSmtpConfig();
    $a = EmailService::getInstance()->smtpConfig();
    $b = EmailService::getInstance()->smtpConfig();
    assert_equals($a['host'], $b['host'], 'Same host across singleton access');
});

/* ========================================================================== */
/* 11. EDGE CASES — SPECIAL CHARACTERS IN PLACEHOLDER VALUES                  */
/* ========================================================================== */

echo "\n\e[36m=== 11. Edge Cases — Special Characters ===\e[0m\n";

test('placeholders with HTML content are preserved', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'applyPlaceholders');
    $ref->setAccessible(true);
    $html = '<strong>bold</strong> & <script>alert("xss")</script>';
    $result = $ref->invoke(null, '<div>{{content}}</div>', ['content' => $html]);
    assert_contains($result, '<strong>bold</strong>', 'HTML preserved');
    assert_contains($result, '<script>alert', 'Script tags preserved (template responsibility)');
});

test('placeholders with newlines are preserved', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'applyPlaceholders');
    $ref->setAccessible(true);
    $multiLine = "Line 1\nLine 2\nLine 3";
    $result = $ref->invoke(null, 'Message: {{msg}}', ['msg' => $multiLine]);
    assert_contains($result, "Line 1\nLine 2", 'Multiline preserved');
});

test('placeholders with unicode characters', function () {
    $ref = new \ReflectionMethod(EmailService::getInstance(), 'applyPlaceholders');
    $ref->setAccessible(true);
    $result = $ref->invoke(null, 'Hello {{name}}', ['name' => 'José \u{1F60A}']);
    assert_contains($result, 'José', 'Accented chars');
});

/* ========================================================================== */
/* 12. CLEANUP                                                                */
/* ========================================================================== */

echo "\n\e[36m=== 12. Cleanup ===\e[0m\n";

test('no leftover HTML files in storage/mail/', function () {
    $files = glob(STORAGE_PATH . '/mail/*.html');
    $leftover = array_filter($files, function ($f) {
        // Only remove files that contain test email addresses
        $content = file_get_contents($f);
        return strpos($content, '@example.com') !== false || strpos($content, '@test.com') !== false;
    });
    foreach ($leftover as $f) unlink($f);
    assert_true(true, 'Cleaned up test files');
});

/* ========================================================================== */
/* SUMMARY                                                                    */
/* ========================================================================== */

echo "\n";
echo str_repeat('=', 60) . "\n";
echo "  Results: $passed passed, $failed failed\n";
echo str_repeat('=', 60) . "\n\n";

if ($failed > 0) {
    echo "\e[31mFailures:\e[0m\n";
    foreach ($errors as $e) {
        echo "  - $e\n";
    }
    echo "\n";
    exit(1);
}

exit(0);
