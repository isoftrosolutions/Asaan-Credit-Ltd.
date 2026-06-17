<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

header('Content-Type: application/json');

$user = current_user();
$userId = (int)$user['id'];
$db = db();

$stmt = $db->prepare('
    SELECT COALESCE(SUM(sub.unread), 0) AS total
    FROM (
        SELECT
            (SELECT COUNT(*) FROM messages m
             WHERE m.conversation_id = c.id
             AND m.sender_id != ?
             AND m.created_at > COALESCE(cp.last_read_at, "0000-00-00")) AS unread
        FROM conversations c
        JOIN conversation_participants cp ON cp.conversation_id = c.id AND cp.user_id = ?
    ) sub
');
$stmt->execute([$userId, $userId]);
$total = (int)$stmt->fetchColumn();

echo json_encode(['success' => true, 'count' => $total]);
