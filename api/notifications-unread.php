<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

header('Content-Type: application/json');

$user = current_user();
$userId = (int)$user['id'];

try {
    $stmt = db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$userId]);
    $count = (int)$stmt->fetchColumn();

    echo json_encode(['count' => $count]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['count' => 0, 'error' => 'Something went wrong.']);
    if (DEBUG_MODE) {
        error_log('notifications-unread error: ' . $e->getMessage());
    }
}
