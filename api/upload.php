<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$user = require_api_auth();

$category = $_POST['category'] ?? '';
$field = $_POST['field'] ?? 'file';

$validCategories = ['avatars', 'photos', 'logos', 'documents'];
if (!in_array($category, $validCategories, true)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid upload category.']);
    exit;
}

if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No file uploaded.']);
    exit;
}

$filename = handle_upload(
    $_FILES[$field],
    explode(',', UPLOAD_ALLOWED_MIME),
    UPLOAD_MAX_BYTES,
    upload_path($category)
);

if ($filename === null) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'File upload failed. Check file type and size limits.']);
    exit;
}

echo json_encode(['filename' => $filename, 'url' => APP_URL . '/public/uploads/' . $category . '/' . $filename]);
