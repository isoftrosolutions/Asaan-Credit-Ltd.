<?php
function current_user(): ?array {
    if (!isset($_SESSION['user'])) return null;
    $needsRefresh = !array_key_exists('is_premium', $_SESSION['user']);
    if ($needsRefresh) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([(int)$_SESSION['user']['id']]);
        $fresh = $stmt->fetch();
        if ($fresh) $_SESSION['user'] = $fresh;
    }
    if (!empty($_SESSION['user']['is_premium'])) {
        $expStmt = db()->prepare("
            SELECT id, status, expiry_date FROM premium_subscriptions
            WHERE user_id = ? AND status = 'active' AND expiry_date <= CURDATE()
            ORDER BY id DESC LIMIT 1
        ");
        $expStmt->execute([(int)$_SESSION['user']['id']]);
        $expired = $expStmt->fetch();
        if ($expired) {
            db()->prepare("UPDATE premium_subscriptions SET status = 'expired' WHERE id = ?")->execute([$expired['id']]);
            db()->prepare("UPDATE users SET is_premium = 0 WHERE id = ?")->execute([(int)$_SESSION['user']['id']]);
            $fresh = db()->prepare("SELECT * FROM users WHERE id = ?");
            $fresh->execute([(int)$_SESSION['user']['id']]);
            $_SESSION['user'] = $fresh->fetch() ?: $_SESSION['user'];
            $_SESSION['user']['is_premium'] = 0;
        }
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
