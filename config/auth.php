<?php

function refresh_user_session(int $userId): void {
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $fresh = $stmt->fetch();
    if ($fresh) {
        $_SESSION['user'] = $fresh;
    }
}

function current_user(): ?array {
    if (!isset($_SESSION['user'])) return null;
    refresh_user_session((int)$_SESSION['user']['id']);
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
            refresh_user_session((int)$_SESSION['user']['id']);
            $_SESSION['user']['is_premium'] = 0;
        }
    }
    return $_SESSION['user'];
}

function isPremium(?array $u = null): bool {
    $u ??= current_user();
    return $u && !empty($u['is_premium']);
}

function hasActiveSubscription(?int $userId = null): bool {
    $userId ??= (int)(current_user()['id'] ?? 0);
    if (!$userId) return false;
    $stmt = db()->prepare("SELECT COUNT(*) FROM premium_subscriptions WHERE user_id = ? AND status = 'active' AND (expiry_date IS NULL OR expiry_date >= CURDATE())");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn() > 0;
}

function canCreateBusiness(?array $u = null): bool {
    $u ??= current_user();
    if (!$u) return false;
    $userId = (int)$u['id'];
    $stmt = db()->prepare('SELECT COUNT(*) FROM businesses WHERE user_id = ?');
    $stmt->execute([$userId]);
    $count = (int)$stmt->fetchColumn();
    $maxAllowed = isPremium($u) ? 10 : 3;
    return $count < $maxAllowed;
}

function canAccessPremiumFeature(?array $u = null): bool {
    return isPremium($u);
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
        echo 'Forbidden: you do not have access to this area.';
        exit;
    }
}

function require_admin(): void {
    $user = current_user();
    if (!$user || empty($user['is_admin'])) {
        http_response_code(403);
        echo 'Forbidden: admin access required.';
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

function require_premium(): void {
    if (!isPremium()) {
        $_SESSION['_flash_error'] = 'This feature requires a premium subscription.';
        redirect('/upgrade');
    }
}
