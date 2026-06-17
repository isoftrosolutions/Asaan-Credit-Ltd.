<?php
function current_user(): ?array {
    if (!isset($_SESSION['user'])) return null;
    if (!array_key_exists('is_premium', $_SESSION['user'])) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([(int)$_SESSION['user']['id']]);
        $fresh = $stmt->fetch();
        if ($fresh) $_SESSION['user'] = $fresh;
    }
    return $_SESSION['user'];
}

function require_login(): void {
    if (!current_user()) {
        $_SESSION['_flash_error'] = 'Please log in to continue.';
        redirect('/login');
    }
}

function require_role(string|array $role): void {
    $user = current_user();
    $roles = is_array($role) ? $role : [$role];
    if (!$user || !in_array($user['role'], $roles, true)) {
        http_response_code(403);
        e('Forbidden: you do not have access to this area.');
        exit;
    }
}

function require_admin(): void {
    $user = current_user();
    if (!$user || empty($user['is_admin'])) {
        http_response_code(403);
        e('Forbidden: admin access required.');
        exit;
    }
}

function require_verified(): void {
    $user = current_user();
    if (!$user || $user['verification_status'] !== 'verified') {
        $_SESSION['_flash_error'] = 'Your account must be verified to perform this action.';
        redirect('/dashboard');
    }
}
