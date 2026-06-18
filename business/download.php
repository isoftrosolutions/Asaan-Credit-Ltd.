<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

$user = current_user();
$userId = (int)$user['id'];

$id = (int)($_GET['id'] ?? 0);
$mid = (int)($_GET['mid'] ?? 0);

if ($id < 1 && $mid < 1) {
    http_response_code(400);
    echo 'Invalid request.';
    exit;
}

$db = db();

// Determine business_id and file details
$filePath = null;
$businessId = 0;

if ($id > 0) {
    $stmt = $db->prepare('SELECT business_id, original_name, file_path FROM business_documents WHERE id = ?');
    $stmt->execute([$id]);
    $doc = $stmt->fetch();
    if (!$doc) {
        http_response_code(404);
        echo 'Document not found.';
        exit;
    }
    $businessId = (int)$doc['business_id'];
    $fileName = $doc['original_name'];
    $filePath = $doc['file_path'];
} else {
    $stmt = $db->prepare("SELECT business_id, file_url FROM business_media WHERE id = ? AND media_type = 'document'");
    $stmt->execute([$mid]);
    $doc = $stmt->fetch();
    if (!$doc) {
        http_response_code(404);
        echo 'Document not found.';
        exit;
    }
    $businessId = (int)$doc['business_id'];
    $fileName = basename($doc['file_url']);
    $filePath = $doc['file_url'];
}

// Verify permission: owner, premium, or admin
$canDownload = false;

// Check if viewer is the listing owner
if ($businessId > 0) {
    $ownerStmt = $db->prepare('SELECT user_id FROM businesses WHERE id = ?');
    $ownerStmt->execute([$businessId]);
    $ownerId = (int)$ownerStmt->fetchColumn();
    if ($ownerId === $userId) {
        $canDownload = true;
    }
}

// Check admin
if (!$canDownload && !empty($user['is_admin'])) {
    $canDownload = true;
}

// Check premium
if (!$canDownload) {
    $userIsPremium = !empty($user['is_premium']) || !empty($user['premium_until']) && strtotime($user['premium_until']) > time();
    if ($userIsPremium) {
        $canDownload = true;
    }
}

if (!$canDownload) {
    http_response_code(403);
    echo 'You do not have permission to download this document.';
    exit;
}

// Resolve absolute file path
$absPath = PUBLIC_UPLOADS_PATH . '/' . $filePath;

if (!file_exists($absPath)) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}

// Increment download count (only for business_documents, not legacy media)
if ($id > 0) {
    $db->prepare('UPDATE business_documents SET download_count = download_count + 1 WHERE id = ?')->execute([$id]);
}

// Force download
$fileSize = filesize($absPath);
$mimeType = mime_content_type($absPath) ?: 'application/octet-stream';

header('Content-Description: File Transfer');
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . $fileSize);

ob_clean();
flush();
readfile($absPath);
exit;
