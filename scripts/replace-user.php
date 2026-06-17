<?php
/**
 * Replace User Script
 *
 * Finds a user by email, deletes them (with all cascading data),
 * and inserts fresh records with auto-resolved IDs.
 *
 * Usage:
 *   php scripts/replace-user.php rkblockudhyog@gmail.com
 *
 * Dry run (preview only, no changes):
 *   php scripts/replace-user.php rkblockudhyog@gmail.com --dry-run
 */

if (PHP_SAPI !== 'cli') {
    die("CLI only.\n");
}

// Bootstrap the app
$_SERVER['HTTP_HOST'] = 'localhost';
require_once __DIR__ . '/../config/bootstrap.php';

$email = $argv[1] ?? '';
$dryRun = in_array('--dry-run', $argv, true);

if (!$email) {
    echo "Usage: php scripts/replace-user.php <email> [--dry-run]\n";
    exit(1);
}

$db = db();

// Find user
$userStmt = $db->prepare('SELECT * FROM users WHERE email = ?');
$userStmt->execute([$email]);
$user = $userStmt->fetch();

if (!$user) {
    echo "User with email '$email' not found.\n";
    exit(1);
}

echo "Found user: ID={$user['id']}, Name={$user['name']}, Email={$user['email']}\n";

// Find their businesses
$busStmt = $db->prepare('SELECT id, business_name FROM businesses WHERE user_id = ?');
$busStmt->execute([$user['id']]);
$businesses = $busStmt->fetchAll();

if (!empty($businesses)) {
    echo "Businesses to delete:\n";
    foreach ($businesses as $b) {
        echo "  - ID={$b['id']}: {$b['business_name']}\n";
    }
} else {
    echo "No businesses found for this user.\n";
}

// Auto-resolve next available IDs
$nextUserId = (int)$db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM users')->fetchColumn();
$nextBusId = (int)$db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM businesses')->fetchColumn();
$nextNotifId = (int)$db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM notifications')->fetchColumn();
$nextAuditId = (int)$db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM admin_audit_log')->fetchColumn();
$nextEmailLogId = (int)$db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM email_log')->fetchColumn();
$nextBusVerId = (int)$db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM business_verifications')->fetchColumn();

echo "\nResolved IDs:\n";
echo "  users:               $nextUserId\n";
echo "  businesses:          $nextBusId\n";
echo "  notifications:       $nextNotifId\n";
echo "  admin_audit_log:     $nextAuditId\n";
echo "  email_log:           $nextEmailLogId\n";
echo "  business_verifications: $nextBusVerId\n";

if ($dryRun) {
    echo "\n--- DRY RUN — no changes made ---\n";
    exit(0);
}

// Confirm
echo "\nProceed with deletion and re-insertion? (yes/no): ";
$handle = fopen('php://stdin', 'r');
$confirm = trim(fgets($handle));
fclose($handle);

if ($confirm !== 'yes') {
    echo "Aborted.\n";
    exit(0);
}

$db->beginTransaction();
try {
    // Delete the user — cascades to all businesses, notifications,
    // interest_requests, matches, saved_listings, etc.
    $delStmt = $db->prepare('DELETE FROM users WHERE id = ?');
    $delStmt->execute([$user['id']]);
    echo "Deleted user ID {$user['id']} and all cascaded data.\n";

    // --- Insert fresh data ---

    // User
    $db->prepare('INSERT INTO users (id, name, email, role, account_type, company_name, verification_status, verified_at, password, created_at, updated_at, company_size, usage_goal, notifications) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, NOW(), NOW(), ?, ?, ?)')
       ->execute([$nextUserId, 'R K Block Udhyog', $email, 'owner', 'company', 'R K Block Udhyog', 'verified', '$2y$10$IWrxMNb9NYCB5hNvZK/kRez0PefjbPa8/ut1yX.EtczrtZRiMcZZa', '1-10', 'sell', 'email']);
    echo "Inserted user ID $nextUserId\n";

    // Business
    $db->prepare('INSERT INTO businesses (id, user_id, business_name, slug, listing_type, sector_id, province, district, legal_entity_type, description, is_published, status, views, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, \'approved\', 0, NOW(), NOW())')
       ->execute([$nextBusId, $nextUserId, 'R K Block Udhyog', 'r-k-block-udhyog', 'investment', 7, 'Bagmati', 'Kathmandu', 'Sole Proprietorship', 'Block manufacturing and construction materials supplier. Looking for investment to expand production capacity and purchase new machinery.']);
    echo "Inserted business ID $nextBusId\n";

    // Business verification
    $db->prepare('INSERT INTO business_verifications (id, business_id, email_verified, phone_verified, identity_verified, company_verified, verified_at, created_at, updated_at) VALUES (?, ?, 1, 1, 1, 1, NOW(), NOW(), NOW())')
       ->execute([$nextBusVerId, $nextBusId]);
    echo "Inserted business_verification ID $nextBusVerId\n";

    // Admin audit log
    $db->prepare('INSERT INTO admin_audit_log (id, admin_id, action, target_type, target_id, details, ip_address, created_at) VALUES (?, 1, \'approve_verification\', \'user\', ?, ?, \'127.0.0.1\', NOW())')
       ->execute([$nextAuditId, $nextUserId, '{"email":"' . $email . '"}']);
    echo "Inserted admin_audit_log ID $nextAuditId\n";

    // Notifications
    $db->prepare('INSERT INTO notifications (id, user_id, type, title, body, action_url, is_read, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())')
       ->execute([$nextNotifId, $nextUserId, 'verification', 'Verification Approved', 'Your account has been verified. You now have full access to the platform.', '/dashboard']);
    $db->prepare('INSERT INTO notifications (id, user_id, type, title, body, action_url, is_read, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())')
       ->execute([$nextNotifId + 1, $nextUserId, 'interest', 'New Inquiry', 'Asaan Credit Ltd is interested in R K Block Udhyog', '/business/' . $nextBusId]);
    echo "Inserted notifications IDs $nextNotifId, " . ($nextNotifId + 1) . "\n";

    // Email log
    $db->prepare('INSERT INTO email_log (id, recipient, subject, template_key, status, sent_at) VALUES (?, ?, ?, \'verification_approved\', \'sent\', NOW())')
       ->execute([$nextEmailLogId, $email, 'Your account has been verified — Asaan Capital']);
    echo "Inserted email_log ID $nextEmailLogId\n";

    $db->commit();
    echo "\nDone! User re-inserted with:\n";
    echo "  User ID:     $nextUserId\n";
    echo "  Business ID: $nextBusId\n";
    echo "  Email:       $email\n";
    echo "  Password:    Use 'Forgot Password' to reset\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
