<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
csrf_check();

header('Content-Type: application/json');

$user = current_user();
$userId = (int)$user['id'];

try {
    $db = db();

    $id = (int)($_POST['id'] ?? 0);
    $markAll = !empty($_POST['all']);

    if ($markAll) {
        $db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([$userId]);
    } elseif ($id > 0) {
        $stmt = $db->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Notification not found.']);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid parameters.']);
        exit;
    }

    echo json_encode(['success' => true]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Something went wrong.']);
    if (DEBUG_MODE) {
        error_log('mark-read error: ' . $e->getMessage());
    }
}
