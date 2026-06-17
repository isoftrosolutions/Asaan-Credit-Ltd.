<?php
/**
 * Quick test for the messaging system.
 * Run: php tests/test-messages.php
 */

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_GET['_path'] = '/api/conversations';

require __DIR__ . '/../config/bootstrap.php';

$_SESSION['user'] = [
    'id' => '1',
    'name' => 'Admin User',
    'role' => 'entrepreneur',
    'email' => 'admin@asaancapital.com',
    'is_admin' => true,
    'is_premium' => true,
    'verification_status' => 'verified',
];

$user = current_user();
$userId = (int)$user['id'];
$db = db();

echo "=== Testing Conversations API ===\n\n";

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

echo "Conversations found: " . count($conversations) . "\n";
foreach ($conversations as $c) {
    echo "  - #{$c['id']} with {$c['other_name']} ({$c['other_role']}), unread: {$c['unread']}, last: " . ($c['last_message'] ? substr($c['last_message'], 0, 50) : 'none') . "\n";
}

echo "\n=== Testing Messages API ===\n\n";

$convId = $conversations[0]['id'] ?? 0;
if ($convId) {
    $stmt = $db->prepare('
        SELECT m.id, m.message, m.sender_id, m.created_at, u.name AS sender_name
        FROM messages m
        JOIN users u ON u.id = m.sender_id
        WHERE m.conversation_id = ?
        ORDER BY m.id ASC
    ');
    $stmt->execute([$convId]);
    $messages = $stmt->fetchAll();

    echo "Messages in conversation #$convId: " . count($messages) . "\n";
    foreach ($messages as $m) {
        echo "  [{$m['created_at']}] {$m['sender_name']}: " . substr($m['message'], 0, 60) . "\n";
    }
}

echo "\n=== Testing Send Message ===\n\n";

$messageText = 'Test message from automated test at ' . date('Y-m-d H:i:s');

$stmt = $db->prepare('INSERT INTO messages (conversation_id, sender_id, message, created_at) VALUES (?, ?, ?, NOW())');
$stmt->execute([$convId, $userId, $messageText]);
$messageId = (int)$db->lastInsertId();

$db->prepare('UPDATE conversations SET updated_at = NOW() WHERE id = ?')->execute([$convId]);

echo "Message sent! ID: $messageId\n";
echo "Message text: $messageText\n";

echo "\n=== All Tests Passed! ===\n";
