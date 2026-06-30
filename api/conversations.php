<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$user = require_api_auth();
$userId = (int)$user['id'];
$db = db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->prepare('
        SELECT
            c.id,
            c.updated_at,
            cp.last_read_at,
            m.message AS last_message,
            m.created_at AS last_message_at,
            m.sender_id AS last_sender_id,
            u.id AS other_id,
            u.name AS other_name,
            u.role AS other_role,
            u.profile_photo AS other_photo,
            (SELECT COUNT(*) FROM messages m2
             WHERE m2.conversation_id = c.id
             AND m2.sender_id != ?
             AND m2.created_at > COALESCE(cp.last_read_at, "0000-00-00")) AS unread
        FROM conversations c
        JOIN conversation_participants cp ON cp.conversation_id = c.id AND cp.user_id = ?
        JOIN conversation_participants cp2 ON cp2.conversation_id = c.id AND cp2.user_id != ?
        JOIN users u ON u.id = cp2.user_id
        LEFT JOIN messages m ON m.id = (
            SELECT MAX(m3.id) FROM messages m3 WHERE m3.conversation_id = c.id
        )
        ORDER BY COALESCE(m.created_at, c.updated_at) DESC
        LIMIT 50
    ');
    $stmt->execute([$userId, $userId, $userId]);
    $conversations = $stmt->fetchAll();

    json_success(['conversations' => $conversations]);
}

if ($method === 'POST') {
    $input = get_json_input();
    $otherId = (int)($input['user_id'] ?? 0);

    if ($otherId < 1 || $otherId === $userId) {
        json_error('Invalid user');
    }

    $stmt = $db->prepare('
        SELECT c.id FROM conversations c
        JOIN conversation_participants cp1 ON cp1.conversation_id = c.id AND cp1.user_id = ?
        JOIN conversation_participants cp2 ON cp2.conversation_id = c.id AND cp2.user_id = ?
        LIMIT 1
    ');
    $stmt->execute([$userId, $otherId]);
    $existing = $stmt->fetch();

    if ($existing) {
        json_success(['conversation_id' => (int)$existing['id'], 'existing' => true]);
    }

    $connCheck = $db->prepare('
        SELECT id FROM interest_requests
        WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
        AND status IN ("pending", "accepted")
        LIMIT 1
    ');
    $connCheck->execute([$userId, $otherId, $otherId, $userId]);
    if (!$connCheck->fetch()) {
        json_error('No connection exists with this user', 403);
    }

    try {
        $db->beginTransaction();

        $stmt = $db->prepare('INSERT INTO conversations () VALUES ()');
        $stmt->execute();
        $conversationId = (int)$db->lastInsertId();

        $stmt = $db->prepare('INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?), (?, ?)');
        $stmt->execute([$conversationId, $userId, $conversationId, $otherId]);

        $db->commit();

        json_success(['conversation_id' => $conversationId, 'existing' => false]);
    } catch (\Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        if (DEBUG_MODE) error_log('conversations create error: ' . $e->getMessage());
        json_error('Failed to create conversation', 500);
    }
}

json_error('Method not allowed', 405);
