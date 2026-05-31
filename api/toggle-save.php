<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
csrf_check();

$user = current_user();
$userId = (int)$user['id'];
$listingType = $_POST['listing_type'] ?? '';
$listingId = (int)($_POST['listing_id'] ?? 0);

$validTypes = ['business', 'pitch', 'franchise'];
if (!in_array($listingType, $validTypes, true)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid listing type.']);
    exit;
}

if ($listingId < 1) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid listing ID.']);
    exit;
}

header('Content-Type: application/json');

$db = db();
$stmt = $db->prepare('SELECT id FROM saved_listings WHERE user_id = ? AND listing_type = ? AND listing_id = ?');
$stmt->execute([$userId, $listingType, $listingId]);
$existing = $stmt->fetch();

if ($existing) {
    $db->prepare('DELETE FROM saved_listings WHERE id = ?')->execute([$existing['id']]);
    echo json_encode(['saved' => false]);
} else {
    $db->prepare('INSERT INTO saved_listings (user_id, listing_type, listing_id, created_at) VALUES (?, ?, ?, NOW())')
       ->execute([$userId, $listingType, $listingId]);
    echo json_encode(['saved' => true]);
}
