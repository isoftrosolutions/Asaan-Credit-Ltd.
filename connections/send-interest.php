<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_verified();
csrf_check();

$user = current_user();
$senderId = (int)$user['id'];

$listingType = $_POST['listing_type'] ?? '';
$listingId = !empty($_POST['listing_id']) ? (int)$_POST['listing_id'] : null;
$pitchId = !empty($_POST['pitch_id']) ? (int)$_POST['pitch_id'] : null;
$receiverId = (int)($_POST['receiver_id'] ?? 0);
$contextType = preg_replace('/[^a-z_]/', '', $_POST['context_type'] ?? '');
$contextId = !empty($_POST['context_id']) ? (int)$_POST['context_id'] : null;
$businessId = !empty($_POST['business_id']) ? (int)$_POST['business_id'] : null;
$message = mb_substr(trim($_POST['message'] ?? ''), 0, 250);

if ($listingType === 'business' && $listingId && $receiverId < 1) {
    $bStmt = db()->prepare('SELECT user_id FROM businesses WHERE id = ?');
    $bStmt->execute([$listingId]);
    $businessOwner = $bStmt->fetch();
    if ($businessOwner) {
        $receiverId = (int)$businessOwner['user_id'];
        $businessId = $listingId;
    }
}

if ($receiverId < 1 || $senderId === $receiverId) {
    flash_set('error', 'Invalid recipient.');
    redirect_back();
}

if ($pitchId && !$contextType) {
    $contextType = 'pitch';
    $contextId = $pitchId;
}
if ($businessId && !$contextType) {
    $contextType = 'business';
    $contextId = $businessId;
}

if ($contextType && !in_array($contextType, ['pitch', 'business', 'franchise', 'advisor'], true)) {
    flash_set('error', 'Invalid context type.');
    redirect_back();
}

try {
    $db = db();

    $check = $db->prepare('SELECT id FROM interest_requests WHERE sender_id = ? AND receiver_id = ? AND status = ? AND ((pitch_id IS NOT NULL AND pitch_id = ?) OR (business_id IS NOT NULL AND business_id = ?)) LIMIT 1');
    $check->execute([$senderId, $receiverId, 'pending', $pitchId, $businessId]);
    if ($check->fetch()) {
        flash_set('error', 'You already have a pending request for this context.');
        redirect_back();
    }

    $cooldown = $db->prepare('SELECT id FROM interest_requests WHERE sender_id = ? AND receiver_id = ? AND rejected_until > NOW() LIMIT 1');
    $cooldown->execute([$senderId, $receiverId]);
    if ($cooldown->fetch()) {
        flash_set('error', 'You cannot send another request to this user yet. Please wait until the cooldown period ends.');
        redirect_back();
    }

    $db->beginTransaction();

    $increment = $db->prepare('UPDATE users SET daily_request_count = daily_request_count + 1 WHERE daily_request_count < 10 AND id = ?');
    $increment->execute([$senderId]);
    if ($increment->rowCount() === 0) {
        $db->rollBack();
        flash_set('error', 'You have reached your daily limit of 10 interest requests.');
        redirect_back();
    }

    $insert = $db->prepare('INSERT INTO interest_requests (sender_id, receiver_id, pitch_id, business_id, message, status, created_at) VALUES (?, ?, ?, ?, ?, \'pending\', NOW())');
    $insert->execute([$senderId, $receiverId, $pitchId, $businessId, $message]);
    $requestId = (int)$db->lastInsertId();

    $receiver = $db->prepare('SELECT name, email FROM users WHERE id = ?');
    $receiver->execute([$receiverId]);
    $receiverData = $receiver->fetch();

    if ($receiverData) {
        $notif = $db->prepare('INSERT INTO notifications (user_id, type, title, body, action_url, created_at) VALUES (?, \'interest\', \'New Interest Request\', ?, \'/connections\', NOW())');
        $notif->execute([$receiverId, e($user['name']) . ' has expressed interest in your listing.']);

        $mailBody = '<p>' . e($user['name']) . ' has expressed interest in your listing on ' . APP_NAME . '.</p>';
        $mailBody .= '<p>Message: ' . e($message ?: '—') . '</p>';
        $mailBody .= '<p><a href="' . APP_URL . '/connections">View in your Connections</a></p>';
        send_mail($receiverData['email'], 'New Interest Request', $mailBody);
    }

    $db->commit();
    flash_set('success', 'Interest request sent successfully.');
} catch (\Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    flash_set('error', 'Something went wrong. Please try again.');
    if (DEBUG_MODE) {
        error_log('send-interest error: ' . $e->getMessage());
    }
}

redirect_back();
