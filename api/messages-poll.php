<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

header('Content-Type: application/json');

$user = current_user();
$userId = (int)$user['id'];
$db = db();

$conversationId = (int)($_GET['conversation_id'] ?? 0);
$since = $_GET['since'] ?? '';

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

// If no since param, reverse so oldest first
if (!$since) {
    $messages = array_reverse($messages);
}

echo json_encode(['success' => true, 'messages' => $messages]);
