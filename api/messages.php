<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

header('Content-Type: application/json');

$user = current_user();
$userId = (int)$user['id'];
$db = db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $conversationId = (int)($_GET['conversation_id'] ?? 0);
    $before = (int)($_GET['before'] ?? 0);

    if ($conversationId < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'conversation_id required']);
        exit;
    }

    // Verify user is a participant
    $stmt = $db->prepare('SELECT id FROM conversation_participants WHERE conversation_id = ? AND user_id = ?');
    $stmt->execute([$conversationId, $userId]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Not a participant']);
        exit;
    }

    if ($before > 0) {
        $stmt = $db->prepare('
            SELECT m.id, m.message, m.sender_id, m.created_at, u.name AS sender_name
            FROM messages m
            JOIN users u ON u.id = m.sender_id
            WHERE m.conversation_id = ? AND m.id < ?
            ORDER BY m.id DESC
            LIMIT 30
        ');
        $stmt->execute([$conversationId, $before]);
    } else {
        $stmt = $db->prepare('
            SELECT m.id, m.message, m.sender_id, m.created_at, u.name AS sender_name
            FROM messages m
            JOIN users u ON u.id = m.sender_id
            WHERE m.conversation_id = ?
            ORDER BY m.id DESC
            LIMIT 30
        ');
        $stmt->execute([$conversationId]);
    }

    $messages = $stmt->fetchAll();

    echo json_encode(['success' => true, 'messages' => $messages]);
    exit;
}

if ($method === 'POST') {
    csrf_check();

    $conversationId = (int)($_POST['conversation_id'] ?? 0);
    $message = mb_substr(trim($_POST['message'] ?? ''), 0, 5000);

    if ($conversationId < 1 || $message === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'conversation_id and message required']);
        exit;
    }

    // Verify user is a participant
    $stmt = $db->prepare('
        SELECT cp.conversation_id, cp2.user_id AS recipient_id, u.name AS recipient_name
        FROM conversation_participants cp
        JOIN conversation_participants cp2 ON cp2.conversation_id = cp.conversation_id AND cp2.user_id != ?
        JOIN users u ON u.id = cp2.user_id
        WHERE cp.conversation_id = ? AND cp.user_id = ?
    ');
    $stmt->execute([$userId, $conversationId, $userId]);
    $conv = $stmt->fetch();

    if (!$conv) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Not a participant']);
        exit;
    }

    $recipientId = (int)$conv['recipient_id'];
    $recipientName = $conv['recipient_name'];

    try {
        $stmt = $db->prepare('INSERT INTO messages (conversation_id, sender_id, message, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$conversationId, $userId, $message]);
        $messageId = (int)$db->lastInsertId();

        // Update conversation timestamp
        $db->prepare('UPDATE conversations SET updated_at = NOW() WHERE id = ?')->execute([$conversationId]);

        echo json_encode([
            'success' => true,
            'message_id' => $messageId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to send message']);
        if (DEBUG_MODE) error_log('messages send error: ' . $e->getMessage());
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);
