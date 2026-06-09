<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

csrf_check();

$businessId = (int)($_POST['business_id'] ?? 0);
$message = trim($_POST['message'] ?? '');
$userId = (int)current_user()['id'];

if ($businessId < 1) {
    flash_set('error', 'Invalid business.');
    redirect_back();
}

$db = db();

// Check if business exists
$stmt = $db->prepare('SELECT id, user_id, business_name FROM businesses WHERE id = ? AND status = "approved"');
$stmt->execute([$businessId]);
$business = $stmt->fetch();

if (!$business) {
    flash_set('error', 'Business not found.');
    redirect_back();
}

// Check for duplicate inquiry
$check = $db->prepare('SELECT id FROM business_inquiries WHERE business_id = ? AND user_id = ?');
$check->execute([$businessId, $userId]);
if ($check->fetch()) {
    flash_set('info', 'You have already contacted this seller.');
    redirect('/business/' . ($business['slug'] ?: $business['id']));
}

// Save inquiry
$stmt = $db->prepare('INSERT INTO business_inquiries (business_id, user_id, message, status, created_at, updated_at) VALUES (?, ?, ?, "new", NOW(), NOW())');
$stmt->execute([$businessId, $userId, $message]);

// Create notification for business owner
$buyer = current_user();
$notifyStmt = $db->prepare('INSERT INTO notifications (user_id, type, title, body, action_url, is_read, created_at) VALUES (?, "interest", ?, ?, ?, 0, NOW())');
$notifyStmt->execute([
    $business['user_id'],
    'New Inquiry',
    $buyer['name'] . ' is interested in ' . $business['business_name'],
    '/business/' . ($business['slug'] ?: $business['id'])
]);

flash_set('success', 'Your inquiry has been sent to the seller.');
redirect('/business/' . ($business['slug'] ?: $business['id']));
