<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
csrf_check();

header('Content-Type: application/json');

$user = current_user();
$userId = (int)$user['id'];
$db = db();

$conversationId = (int)($_POST['conversation_id'] ?? 0);

if ($conversationId < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'conversation_id required']);
    exit;
}

$stmt = $db->prepare('UPDATE conversation_participants SET last_read_at = NOW() WHERE conversation_id = ? AND user_id = ?');
$stmt->execute([$conversationId, $userId]);

echo json_encode(['success' => true]);
