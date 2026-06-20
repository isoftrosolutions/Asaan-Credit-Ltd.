<?php
if (defined('BOOTSTRAP_LOADED')) return;
define('BOOTSTRAP_LOADED', true);
require __DIR__ . '/config.php';
require __DIR__ . '/session.php';
require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/csrf.php';
require __DIR__ . '/flash.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/mailer.php';
require __DIR__ . '/upload.php';
if (isset($_GET['_path']) && str_starts_with($_GET['_path'], 'api/')) {
    require __DIR__ . '/../api/helpers.php';
}
session_init();
