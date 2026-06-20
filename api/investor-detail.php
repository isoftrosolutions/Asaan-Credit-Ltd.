<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$investorId = (int)($_GET['id'] ?? 0);
if ($investorId < 1) {
    json_error('Investor id is required.', 400);
}

$stmt = db()->prepare('
    SELECT u.*, ip.*
    FROM users u
    LEFT JOIN investor_profiles ip ON ip.user_id = u.id
    WHERE u.id = ? AND u.role = ?
');
$stmt->execute([$investorId, ROLE_INVESTOR]);
$profile = $stmt->fetch();

if (!$profile) {
    json_error('Investor not found.', 404);
}

$preferredSectors = json_decode($profile['preferred_sectors'] ?? '[]', true) ?: [];
$preferredStages = json_decode($profile['preferred_stages'] ?? '[]', true) ?: [];
$preferredGeography = json_decode($profile['preferred_geography'] ?? '[]', true) ?: [];

$user = _api_auth_user();
$userId = $user ? (int)$user['id'] : 0;
$isOwner = $userId === $investorId;
$showContact = $isOwner;

if (!$showContact && $userId) {
    $matchStmt = db()->prepare('SELECT COUNT(*) FROM matches WHERE (user_a_id = ? AND user_b_id = ?) OR (user_a_id = ? AND user_b_id = ?)');
    $matchStmt->execute([$userId, $investorId, $investorId, $userId]);
    $showContact = (int)$matchStmt->fetchColumn() > 0;
}

$isSaved = false;
if ($userId) {
    $sStmt = db()->prepare("SELECT id FROM saved_listings WHERE user_id = ? AND listing_type = 'investor' AND listing_id = ?");
    $sStmt->execute([$userId, $investorId]);
    $isSaved = (bool)$sStmt->fetch();
}

$safeFields = ['id', 'name', 'email', 'role', 'account_type', 'phone', 'province', 'district', 'profile_photo', 'verification_status', 'company_name', 'created_at'];
$safeProfile = array_intersect_key($profile, array_flip($safeFields));
$profileFields = array_diff_key($profile, array_flip($safeFields));

json_success([
    'profile' => $safeProfile,
    'investor_profile' => $profileFields,
    'preferred_sectors' => $preferredSectors,
    'preferred_stages' => $preferredStages,
    'preferred_geography' => $preferredGeography,
    'show_contact' => $showContact,
    'is_saved' => $isSaved,
]);
