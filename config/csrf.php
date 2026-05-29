<?php
function csrf_token(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function csrf_check(): void {
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (empty($token) || !hash_equals($_SESSION[CSRF_TOKEN_NAME] ?? '', $token)) {
        http_response_code(419);
        e('CSRF validation failed.');
        exit;
    }
}
