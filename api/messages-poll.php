<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$user = require_api_auth();
$userId = (int)$user['id'];
$db = db();

$conversationId = (int)($_GET['conversation_id'] ?? 0);
$since = $_GET['since'] ?? '';

if ($conversationId < 1) {
    json_error('conversation_id required');
}

$stmt = $db->prepare('SELECT id FROM conversation_participants WHERE conversation_id = ? AND user_id = ?');
$stmt->execute([$conversationId, $userId]);
if (!$stmt->fetch()) {
    json_error('Not a participant', 403);
}

if ($since) {
    $stmt = $db->prepare('
        SELECT m.id, m.message, m.sender_id, m.created_at, u.name AS sender_name
        FROM messages m
        JOIN users u ON u.id = m.sender_id
        WHERE m.conversation_id = ? AND m.created_at > ?
        ORDER BY m.id ASC
    ');
    $stmt->execute([$conversationId, $since]);
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

if (!$since) {
    $messages = array_reverse($messages);
}

json_success(['messages' => $messages]);
