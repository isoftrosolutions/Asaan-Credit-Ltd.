<?php
function session_init(): void {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_samesite', 'Lax');
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            ini_set('session.cookie_secure', 1);
        }
        session_start();
    }
    if (isset($_SESSION['_last_activity']) && (time() - $_SESSION['_last_activity']) > SESSION_LIFETIME) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['_last_activity'] = time();
    if (!isset($_SESSION['_created'])) {
        session_regenerate_id(true);
        $_SESSION['_created'] = true;
    }
}
