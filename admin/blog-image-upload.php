<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    csrf_check();
} catch (Throwable $e) {
    http_response_code(419);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

$file = $_FILES['file'] ?? null;
if (!$file) {
    http_response_code(422);
    echo json_encode(['error' => 'No image uploaded']);
    exit;
}

$filename = handle_upload($file, ['image/jpeg', 'image/png', 'image/webp'], 3145728, upload_path('blog-images'));
if (!$filename) {
    http_response_code(422);
    echo json_encode(['error' => 'Upload must be JPG, PNG, or WebP under 3MB']);
    exit;
}

$path = 'blog-images/' . $filename;
echo json_encode([
    'url' => upload_url($path),
    'path' => $path,
]);

