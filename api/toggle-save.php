<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$user = require_api_auth();
$userId = (int)$user['id'];
$input = get_json_input();

$listingType = $input['listing_type'] ?? '';
$listingId = (int)($input['listing_id'] ?? 0);

$validTypes = ['business', 'pitch', 'franchise'];
if (!in_array($listingType, $validTypes, true)) {
    json_error('Invalid listing type.');
}

if ($listingId < 1) {
    json_error('Invalid listing ID.');
}

$db = db();
$stmt = $db->prepare('SELECT id FROM saved_listings WHERE user_id = ? AND listing_type = ? AND listing_id = ?');
$stmt->execute([$userId, $listingType, $listingId]);
$existing = $stmt->fetch();

if ($existing) {
    $db->prepare('DELETE FROM saved_listings WHERE id = ?')->execute([$existing['id']]);
    json_success(['saved' => false]);
} else {
    $db->prepare('INSERT INTO saved_listings (user_id, listing_type, listing_id, created_at) VALUES (?, ?, ?, NOW())')
       ->execute([$userId, $listingType, $listingId]);
    json_success(['saved' => true]);
}
