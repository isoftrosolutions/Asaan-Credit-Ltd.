<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

csrf_check();

$businessId = (int)($_POST['business_id'] ?? 0);
$message = mb_substr(trim($_POST['message'] ?? ''), 0, 1000);
$buyer = current_user();
$userId = (int)$buyer['id'];

if ($businessId < 1) {
    flash_set('error', 'Invalid business.');
    redirect_back();
}

$db = db();

// Check if business exists
$stmt = $db->prepare('
    SELECT b.id, b.user_id, b.business_name, b.slug, u.name AS owner_name, u.email AS owner_email
    FROM businesses b
    JOIN users u ON u.id = b.user_id
    WHERE b.id = ? AND b.status = "approved"
');
$stmt->execute([$businessId]);
$business = $stmt->fetch();

if (!$business) {
    flash_set('error', 'Business not found.');
    redirect_back();
}

if ((int)$business['user_id'] === $userId) {
    flash_set('error', 'You cannot send an inquiry to your own business.');
    redirect('/business/' . ($business['slug'] ?: $business['id']));
}

try {
    $db->beginTransaction();

    // Check for duplicate pending request.
    $pendingCheck = $db->prepare('SELECT id FROM interest_requests WHERE business_id = ? AND sender_id = ? AND receiver_id = ? AND status = "pending" LIMIT 1');
    $pendingCheck->execute([$businessId, $userId, (int)$business['user_id']]);
    if ($pendingCheck->fetch()) {
        $db->rollBack();
        flash_set('info', 'You already have a pending request for this business.');
        redirect('/business/' . ($business['slug'] ?: $business['id']));
    }

    // Save archive inquiry for admin/reporting history.
    $inquiryCheck = $db->prepare('SELECT id FROM business_inquiries WHERE business_id = ? AND user_id = ? LIMIT 1');
    $inquiryCheck->execute([$businessId, $userId]);
    if (!$inquiryCheck->fetch()) {
        $stmt = $db->prepare('INSERT INTO business_inquiries (business_id, user_id, message, status, created_at, updated_at) VALUES (?, ?, ?, "new", NOW(), NOW())');
        $stmt->execute([$businessId, $userId, $message]);
    }

    // Save portal-facing request so it appears for both sides and can be accepted/rejected.
    $interestStmt = $db->prepare('INSERT INTO interest_requests (sender_id, receiver_id, pitch_id, business_id, message, status, created_at, updated_at) VALUES (?, ?, NULL, ?, ?, "pending", NOW(), NOW())');
    $interestStmt->execute([$userId, (int)$business['user_id'], $businessId, $message]);

    $notifyStmt = $db->prepare('INSERT INTO notifications (user_id, type, title, body, action_url, is_read, created_at) VALUES (?, "interest", ?, ?, ?, 0, NOW())');
    $notifyStmt->execute([
        (int)$business['user_id'],
        'New Inquiry',
        ($buyer['name'] ?? 'A user') . ' is interested in ' . $business['business_name'],
        '/connections'
    ]);
    $notifyStmt->execute([
        $userId,
        'Inquiry Sent',
        'Your inquiry for ' . $business['business_name'] . ' was sent to the business owner.',
        '/connections'
    ]);

    send_interest_received_email(
        $business['owner_email'],
        $business['owner_name'] ?: 'there',
        $buyer['name'] ?? 'A user',
        ucfirst(str_replace('_', ' ', $buyer['role'] ?? 'user')),
        'business',
        $business['business_name'],
        $message ?: 'No message provided.'
    );

    $db->commit();
} catch (\Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    flash_set('error', 'Something went wrong. Please try again.');
    if (DEBUG_MODE) error_log('send-inquiry error: ' . $e->getMessage());
    redirect('/business/' . ($business['slug'] ?: $business['id']));
}

flash_set('success', 'Your inquiry has been sent to the seller.');
redirect('/business/' . ($business['slug'] ?: $business['id']));
