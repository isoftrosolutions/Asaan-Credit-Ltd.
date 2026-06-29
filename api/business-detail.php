<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$id = (int)($_GET['id'] ?? 0);

if ($id < 1) {
    json_error('Business id is required.', 400);
}

$user = _api_auth_user();
$userId = $user ? (int)$user['id'] : 0;

$db = db();

$stmt = $db->prepare('SELECT b.*, s.name AS sector_name, u.name AS owner_name, u.company_name, u.phone AS owner_phone, u.email AS owner_email, u.verification_status, u.id AS owner_id, u.profile_photo FROM businesses b LEFT JOIN sectors s ON s.id = b.sector_id JOIN users u ON u.id = b.user_id WHERE b.id = ?');
$stmt->execute([$id]);
$business = $stmt->fetch();

if (!$business) {
    json_error('Business not found.', 404);
}

$businessId = (int)$business['id'];

if ($business['status'] !== 'approved' && (!$user || $userId !== (int)$business['owner_id'])) {
    json_error('Business not found.', 404);
}

$db->prepare('UPDATE businesses SET views = views + 1 WHERE id = ?')->execute([$businessId]);

$mediaS = $db->prepare('SELECT * FROM business_media WHERE business_id = ? ORDER BY sort_order');
$mediaS->execute([$businessId]);
$mediaItems = $mediaS->fetchAll();
foreach ($mediaItems as &$mediaItem) {
    if (!empty($mediaItem['file_url'])) {
        $mediaItem['url'] = upload_url($mediaItem['file_url']);
    }
}
unset($mediaItem);

$assetS = $db->prepare('SELECT * FROM business_assets WHERE business_id = ? ORDER BY id');
$assetS->execute([$businessId]);
$assetItems = $assetS->fetchAll();

$finS = $db->prepare('SELECT * FROM business_financials WHERE business_id = ? ORDER BY fiscal_year ASC');
$finS->execute([$businessId]);
$financialItems = $finS->fetchAll();

$docS = $db->prepare('SELECT * FROM business_documents WHERE business_id = ? ORDER BY sort_order');
$docS->execute([$businessId]);
$documents = $docS->fetchAll();

json_success([
    'business' => $business,
    'media' => $mediaItems,
    'assets' => $assetItems,
    'financials' => $financialItems,
    'documents' => $documents,
]);
