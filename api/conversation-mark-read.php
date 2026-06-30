<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$user = require_api_auth();
$userId = (int)$user['id'];
$db = db();

$input = get_json_input();
$conversationId = (int)($input['conversation_id'] ?? 0);

if ($conversationId < 1) {
    json_error('conversation_id required');
}

$stmt = $db->prepare('UPDATE conversation_participants SET last_read_at = NOW() WHERE conversation_id = ? AND user_id = ?');
$stmt->execute([$conversationId, $userId]);

json_success(null);
