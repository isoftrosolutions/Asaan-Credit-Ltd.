<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

$input = get_json_input();
$inputPost = $_POST ?: [];

$businessId = (int)($input['business_id'] ?? $inputPost['business_id'] ?? 0);
$message = mb_substr(trim($input['message'] ?? $inputPost['message'] ?? ''), 0, 1000);

$user = require_api_auth();
$buyer = $user;
$userId = (int)$buyer['id'];

if ($businessId < 1) {
    json_error('Invalid business.');
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
    json_error('Business not found.', 404);
}

if ((int)$business['user_id'] === $userId) {
    json_error('You cannot send an inquiry to your own business.');
}

try {
    $db->beginTransaction();

    // Check for duplicate pending request.
    $pendingCheck = $db->prepare('SELECT id FROM interest_requests WHERE business_id = ? AND sender_id = ? AND receiver_id = ? AND status = "pending" LIMIT 1');
    $pendingCheck->execute([$businessId, $userId, (int)$business['user_id']]);
    if ($pendingCheck->fetch()) {
        $db->rollBack();
        json_error('You already have a pending request for this business.', 409);
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
        'Proposal Sent',
        'Your proposal for ' . $business['business_name'] . ' has been sent. Check Messages for the conversation.',
        '/messages'
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

    // Create or reuse a conversation and insert the first message.
    $ownerId = (int)$business['user_id'];
    $convStmt = $db->prepare('
        SELECT c.id FROM conversations c
        JOIN conversation_participants cp1 ON cp1.conversation_id = c.id AND cp1.user_id = ?
        JOIN conversation_participants cp2 ON cp2.conversation_id = c.id AND cp2.user_id = ?
        LIMIT 1
    ');
    $convStmt->execute([$userId, $ownerId]);
    $existingConv = $convStmt->fetch();

    if ($existingConv) {
        $conversationId = (int)$existingConv['id'];
        $msgStmt = $db->prepare('INSERT INTO messages (conversation_id, sender_id, message, created_at) VALUES (?, ?, ?, NOW())');
        $msgStmt->execute([$conversationId, $userId, $message]);
        $db->prepare('UPDATE conversations SET updated_at = NOW() WHERE id = ?')->execute([$conversationId]);
    } else {
        $convInsert = $db->prepare('INSERT INTO conversations () VALUES ()');
        $convInsert->execute();
        $conversationId = (int)$db->lastInsertId();

        $partStmt = $db->prepare('INSERT INTO conversation_participants (conversation_id, user_id, last_read_at) VALUES (?, ?, NOW()), (?, ?, NULL)');
        $partStmt->execute([$conversationId, $userId, $conversationId, $ownerId]);

        $msgStmt = $db->prepare('INSERT INTO messages (conversation_id, sender_id, message, created_at) VALUES (?, ?, ?, NOW())');
        $msgStmt->execute([$conversationId, $userId, $message]);
    }

    // Notify the owner about the new message.
    $notifyStmt->execute([
        $ownerId,
        'New Message',
        ($buyer['name'] ?? 'A user') . ' sent you a proposal about ' . $business['business_name'],
        '/messages'
    ]);

    $db->commit();
} catch (\Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    if (DEBUG_MODE) error_log('send-inquiry error: ' . $e->getMessage());
    json_error('Something went wrong. Please try again.', 500);
}

json_success(['message' => 'Your proposal has been sent.', 'conversation_id' => $conversationId ?? null]);
