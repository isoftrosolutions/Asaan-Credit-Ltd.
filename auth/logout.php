<?php
require __DIR__ . '/../config/bootstrap.php';

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

setcookie('remember', '', time() - 42000, '/');

session_destroy();

redirect('/');
