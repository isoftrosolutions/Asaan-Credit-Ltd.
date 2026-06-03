<?php
require __DIR__ . '/config/bootstrap.php';

$path = $_GET['_path'] ?? '';
$path = '/' . trim(parse_url($path, PHP_URL_PATH), '/');

$routes = [
    '/'                             => 'pages/index.php',
    '/login'                        => 'auth/login.php',
    '/logout'                       => 'auth/logout.php',
    '/verify-email'                 => 'auth/verify-email.php',
    '/forgot-password'              => 'auth/forgot-password.php',
    '/reset-password'               => 'auth/reset-password.php',
    '/change-password'              => 'auth/change-password.php',
    '/about'                        => 'pages/about.php',
    '/contact'                      => 'pages/contact.php',
    '/careers'                      => 'pages/careers.php',
    '/press'                        => 'pages/press.php',
    '/testimonials'                 => 'pages/testimonials.php',
    '/blog'                         => 'pages/blog.php',
    '/industry-watch'               => 'pages/industry-watch.php',
    '/how-it-works'                 => 'pages/how-it-works.php',
    '/support'                      => 'pages/support.php',
    '/legal'                        => 'pages/legal.php',
    '/advisor/create'               => 'advisor/create.php',
    '/advisor/edit'                 => 'advisor/edit.php',
    '/business/create'              => 'business/create.php',
    '/business/edit'                => 'business/edit.php',
    '/business-valuation'           => 'pages/business-valuation.php',
    '/dashboard'                    => 'pages/dashboard.php',
    '/entrepreneur/pitch-create'    => 'entrepreneur/pitch-create.php',
    '/entrepreneur/pitch-edit'      => 'entrepreneur/pitch-edit.php',
    '/franchise/create'             => 'franchise/create.php',
    '/franchise/edit'               => 'franchise/edit.php',
    '/investor/profile-create'      => 'investor/profile-create.php',
    '/investor/profile-edit'        => 'investor/profile-edit.php',
    '/investor/preferences-edit'    => 'investor/preferences-edit.php',
    '/investor/documents-edit'      => 'investor/documents-edit.php',
    '/connections'                  => 'connections/my-connections.php',
    '/connections/send-interest'    => 'connections/send-interest.php',
    '/connections/respond'          => 'connections/respond.php',
    '/notifications'                => 'notifications/index.php',
    '/notifications/mark-read'      => 'notifications/mark-read.php',
    '/notifications/settings'       => 'notifications/settings.php',
    '/my-saved'                     => 'notifications/saved-listings.php',
    '/onboarding'                   => 'pages/onboarding.php',
    '/admin'                        => 'admin/dashboard.php',
    '/admin/login'                  => 'admin/login.php',
    '/admin/users'                  => 'admin/users.php',
    '/admin/verification'           => 'admin/verification.php',
    '/admin/pitches'                => 'admin/pitches.php',
    '/admin/reports'                => 'admin/reports.php',
    '/admin/interest-log'           => 'admin/interest-log.php',
    '/admin/broadcast'              => 'admin/broadcast.php',

    '/admin/sectors'                => 'admin/content/sectors.php',
    '/admin/faqs'                   => 'admin/content/faqs.php',
    '/admin/blog'                   => 'admin/content/blog.php',
    '/admin/homepage'               => 'admin/content/homepage.php',
    '/admin/email-settings'         => 'admin/email-settings.php',
    '/admin/email-templates'        => 'admin/email-templates.php',
    '/admin/email-log'              => 'admin/email-log.php',
    '/api/notifications-unread'     => 'api/notifications-unread.php',
    '/api/mark-notification-read'   => 'api/mark-notification-read.php',
    '/api/smart-suggestions'        => 'api/smart-suggestions.php',
    '/api/toggle-save'              => 'api/toggle-save.php',
    '/api/upload'                   => 'api/upload.php',
];

if (preg_match('#^/browse/(businesses|investors|entrepreneurs|franchises)$#', $path, $m)) {
    $file = 'discover/' . $m[1] . '.php';
    if (file_exists(__DIR__ . '/' . $file)) {
        require __DIR__ . '/' . $file;
        exit;
    }
}

if (preg_match('#^/search$#', $path)) {
    require __DIR__ . '/discover/search.php';
    exit;
}

if (preg_match('#^/blog/([a-z0-9-]+)$#', $path, $m)) {
    $_GET['slug'] = $m[1];
    require __DIR__ . '/pages/blog-post.php';
    exit;
}

if (preg_match('#^/investor/(\d+)$#', $path, $m)) {
    $_GET['id'] = $m[1];
    require __DIR__ . '/investor/public.php';
    exit;
}

if (preg_match('#^/business/(\d+)$#', $path, $m)) {
    $_GET['id'] = $m[1];
    require __DIR__ . '/business/detail.php';
    exit;
}

if (preg_match('#^/pitch/(\d+)$#', $path, $m)) {
    $_GET['id'] = $m[1];
    require __DIR__ . '/entrepreneur/pitch.php';
    exit;
}

if (preg_match('#^/franchise/(\d+)$#', $path, $m)) {
    $_GET['id'] = $m[1];
    require __DIR__ . '/franchise/detail.php';
    exit;
}

if (isset($routes[$path])) {
    $file = __DIR__ . '/' . $routes[$path];
    if (file_exists($file)) {
        require $file;
        exit;
    }
}

http_response_code(404);
require __DIR__ . '/pages/404.php';
