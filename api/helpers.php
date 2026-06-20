<?php

function cors_headers(): void {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 86400');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function json_response(mixed $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error(string $message, int $status = 400): void {
    json_response(['success' => false, 'error' => $message], $status);
}

function json_success(mixed $data = null, ?array $meta = null): void {
    $res = ['success' => true];
    if ($data !== null) $res['data'] = $data;
    if ($meta !== null) $res['meta'] = $meta;
    json_response($res);
}

function get_json_input(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function require_api_auth(): array {
    $user = _api_auth_user();
    if (!$user) {
        json_error('Authentication required. Provide a valid Bearer token or session cookie.', 401);
    }
    return $user;
}

function require_api_role(string|array $role): array {
    $user = require_api_auth();
    $roles = is_array($role) ? $role : [$role];
    if (!in_array($user['role'], $roles, true)) {
        json_error('Forbidden: insufficient permissions.', 403);
    }
    return $user;
}

function api_paginate(PDOStatement $countStmt, int $page, int $perPage = 12): array {
    $total = (int)$countStmt->fetchColumn();
    $lastPage = max(1, (int)ceil($total / $perPage));
    $page = max(1, min($page, $lastPage));
    $offset = ($page - 1) * $perPage;
    return [
        'page' => $page,
        'per_page' => $perPage,
        'offset' => $offset,
        'total' => $total,
        'last_page' => $lastPage,
    ];
}

function _api_auth_user(): ?array {
    if (isset($_SESSION['user'])) {
        return current_user();
    }
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if ($header === '' && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }
    if (preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
        $token = $m[1];
        $stmt = db()->prepare('
            SELECT u.* FROM users u
            JOIN user_api_tokens t ON t.user_id = u.id
            WHERE t.token = ? AND (t.expires_at IS NULL OR t.expires_at > NOW())
            LIMIT 1
        ');
        $stmt->execute([hash('sha256', $token)]);
        $user = $stmt->fetch();
        if ($user) {
            db()->prepare('UPDATE user_api_tokens SET last_used_at = NOW() WHERE token = ?')
               ->execute([hash('sha256', $token)]);
            return $user;
        }
    }
    return null;
}

function api_token_exists(int $userId, ?string $name = null): bool {
    $sql = 'SELECT COUNT(*) FROM user_api_tokens WHERE user_id = ?';
    $params = [$userId];
    if ($name !== null) {
        $sql .= ' AND name = ?';
        $params[] = $name;
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn() > 0;
}

function generate_api_token(): string {
    return bin2hex(random_bytes(32));
}
