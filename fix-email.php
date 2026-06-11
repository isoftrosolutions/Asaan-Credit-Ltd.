<?php
require __DIR__ . '/config/bootstrap.php';

$testTo = 'pdewbrath@gmail.com';

$db = db();

// 1. Update SMTP config to use hosting mail server
$stmt = $db->prepare("UPDATE email_settings SET
    smtp_host = 'mail.asaancapital.com',
    smtp_port = 587,
    smtp_encryption = 'tls',
    from_email = 'info@asaancapital.com',
    from_name = 'Asaan Capital Ltd',
    is_active = 1,
    updated_at = NOW()
    WHERE id = 1");

$stmt->execute();
echo "✓ email_settings updated\n";

// 2. Reset cached SMTP config so the next send reads fresh from DB
email_service()->resetSmtpConfig();

$cfg = email_service()->smtpConfig();
echo "  Host: " . $cfg['host'] . "\n";
echo "  Port: " . $cfg['port'] . "\n";
echo "  User: " . $cfg['username'] . "\n";
echo "  Active: " . ($cfg['active'] ? 'yes' : 'no') . "\n\n";

// 3. Send test email
$body = '<div style="font-family:sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;">
    <h2 style="color:#1E7A4D;">Test Email Successful!</h2>
    <p>Your SMTP configuration is working correctly via mail.asaancapital.com.</p>
    <p style="color:#888;font-size:12px;">Asaan Capital Ltd — Kathmandu, Nepal</p>
</div>';

$result = send_mail($testTo, 'Test Email from Asaan Capital', $body);

if ($result) {
    echo "✓ Test email sent to $testTo\n";
} else {
    echo "✗ Failed to send test email\n";

    $log = $db->query("SELECT error FROM email_log ORDER BY sent_at DESC LIMIT 1")->fetchColumn();
    if ($log) {
        echo "  Error: $log\n";
    }
}
