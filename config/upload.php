<?php
function handle_upload(array $file, array $allowedMime, int $maxBytes, string $destDir): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowedMime, true)) {
        return null;
    }
    if ($file['size'] > $maxBytes) {
        return null;
    }
    if (!is_dir($destDir)) {
        mkdir($destDir, 0777, true);
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = (current_user()['id'] ?? 0) . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destPath = $destDir . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $destPath)) {
        if (in_array($mime, ['image/jpeg', 'image/png'], true)) {
            strip_exif($destPath);
        }
        return $filename;
    }
    return null;
}

function strip_exif(string $path): void {
    try {
        $img = imagecreatefromstring(file_get_contents($path));
        if ($img) {
            imagejpeg($img, $path, 85);
            imagedestroy($img);
        }
    } catch (\Throwable $e) {
    }
}

function upload_path(string $category): string {
    return PUBLIC_UPLOADS_PATH . '/' . $category;
}
