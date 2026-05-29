<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_verified();
csrf_check();

$user = current_user();
$requestId = (int)($_POST['request_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($requestId < 1 || !in_array($action, ['accept', 'reject'], true)) {
    flash_set('error', 'Invalid request.');
    redirect_back();
}

try {
    $db = db();

    $stmt = $db->prepare('SELECT ir.*, u.name AS sender_name, u.email AS sender_email, ru.name AS receiver_name, ru.email AS receiver_email FROM interest_requests ir JOIN users u ON u.id = ir.sender_id JOIN users ru ON ru.id = ir.receiver_id WHERE ir.id = ? AND ir.receiver_id = ? AND ir.status = \'pending\' LIMIT 1');
    $stmt->execute([$requestId, $user['id']]);
    $request = $stmt->fetch();

    if (!$request) {
        flash_set('error', 'Request not found or already responded to.');
        redirect_back();
    }

    $senderId = (int)$request['sender_id'];
    $receiverId = (int)$request['receiver_id'];

    $db->beginTransaction();

    if ($action === 'accept') {
        $db->prepare('UPDATE interest_requests SET status = \'accepted\', responded_at = NOW() WHERE id = ?')->execute([$requestId]);

        $contextType = null;
        $contextId = null;
        if (!empty($request['pitch_id'])) {
            $contextType = 'pitch';
            $contextId = (int)$request['pitch_id'];
        } elseif (!empty($request['business_id'])) {
            $contextType = 'business';
            $contextId = (int)$request['business_id'];
        }

        $insertMatch = $db->prepare('INSERT INTO matches (interest_request_id, user_a_id, user_b_id, context_type, context_id, matched_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $insertMatch->execute([$requestId, $senderId, $receiverId, $contextType, $contextId]);

        $notif = $db->prepare('INSERT INTO notifications (user_id, type, title, body, action_url, created_at) VALUES (?, \'match\', \'Interest Accepted\', ?, \'/connections\', NOW())');
        $notif->execute([$senderId, e($request['receiver_name']) . ' has accepted your interest request. You are now connected!']);
        $notif->execute([$receiverId, 'You accepted an interest request from ' . e($request['sender_name']) . '. You are now connected!']);

        $bodyBoth = '<p>You are now connected on ' . APP_NAME . '!</p>';
        $bodyBoth .= '<p>Contact details have been revealed. You can now communicate directly.</p>';
        $bodyBoth .= '<p><a href="' . APP_URL . '/connections">View your Connections</a></p>';

        send_mail($request['sender_email'], 'Interest Accepted — You\'re Now Connected!', $bodyBoth);
        send_mail($request['receiver_email'], 'Interest Accepted — You\'re Now Connected!', $bodyBoth);
    } else {
        $db->prepare('UPDATE interest_requests SET status = \'rejected\', responded_at = NOW(), rejected_until = DATE_ADD(NOW(), INTERVAL 60 DAY) WHERE id = ?')->execute([$requestId]);

        send_mail($request['sender_email'], 'Update on Your Interest Request',
            '<p>Thank you for your interest. The recipient has reviewed your request and decided not to proceed at this time.</p>' .
            '<p>We encourage you to explore other opportunities on ' . APP_NAME . '.</p>');
    }

    $db->commit();
    flash_set('success', $action === 'accept' ? 'Interest request accepted. You are now connected!' : 'Interest request declined.');
} catch (\Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    flash_set('error', 'Something went wrong. Please try again.');
    if (DEBUG_MODE) {
        error_log('respond error: ' . $e->getMessage());
    }
}

redirect_back();
