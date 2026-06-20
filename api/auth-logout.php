<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

$header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if ($header === '' && function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    $header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
}

if (preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
    $token = $m[1];
    $stmt = db()->prepare('DELETE FROM user_api_tokens WHERE token = ?');
    $stmt->execute([hash('sha256', $token)]);
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

json_success(['message' => 'Logged out successfully.']);
