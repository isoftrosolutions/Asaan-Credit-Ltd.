<?php
require __DIR__ . '/config/bootstrap.php';

$path = $_GET['_path'] ?? '';
$path = '/' . trim(parse_url($path, PHP_URL_PATH), '/');

$routes = [
    '/'                             => 'pages/index.php',
    '/login'                        => 'auth/login.php',
    '/logout'                       => 'auth/logout.php',
    '/verify-email'                 => 'auth/verify-email.php',
    '/verify-email-otp'             => 'auth/verify-email-otp.php',
    '/forgot-password'              => 'auth/forgot-password.php',
    '/reset-password'               => 'auth/reset-password.php',
    '/change-password'              => 'auth/change-password.php',
    '/about'                        => 'pages/about.php',
    '/contact'                      => 'pages/contact.php',
    '/careers'                      => 'pages/careers.php',
    '/press'                        => 'pages/press.php',
    '/our-partners'                 => 'pages/our-partners.php',
    '/testimonials'                 => 'pages/testimonials.php',
    '/blog'                         => 'pages/blog.php',
    '/industry-watch'               => 'pages/industry-watch.php',
    '/how-it-works'                 => 'pages/how-it-works.php',
    '/support'                      => 'pages/support.php',
    '/legal'                        => 'pages/legal.php',
    '/advisor/create'               => 'advisor/create.php',
    '/advisor/edit'                 => 'advisor/edit.php',
    '/business/download'            => 'business/download.php',
    '/business/create'              => 'business/create.php',
    '/business/edit'                => 'business/edit.php',
    '/upgrade'                      => 'pages/upgrade.php',
    '/pricing'                      => 'pages/how-it-works.php',
    '/valuation'                    => 'pages/business-valuation.php',
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
    '/messages'                     => 'pages/messages.php',
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
    '/admin/businesses'             => 'admin/businesses.php',
    '/admin/premium'                => 'admin/premium.php',
    '/admin/premium-verify'         => 'admin/premium-verify.php',
    '/admin/reports'                => 'admin/reports.php',
    '/admin/interest-log'           => 'admin/interest-log.php',
    '/admin/inquiries'              => 'admin/inquiries.php',
    '/admin/inquiry-action'         => 'admin/inquiry-action.php',
    '/admin/nda-requests'           => 'admin/nda-requests.php',
    '/admin/business-verifications' => 'admin/business-verifications.php',
    '/admin/broadcast'              => 'admin/broadcast.php',

    '/admin/sectors'                => 'admin/content/sectors.php',
    '/admin/sectors/edit'           => 'admin/content/sector-edit.php',
    '/admin/faqs'                   => 'admin/content/faqs.php',
    '/admin/faqs/edit'              => 'admin/content/faq-edit.php',
    '/admin/pages'                  => 'admin/content/pages.php',
    '/admin/pages/edit'             => 'admin/content/page-edit.php',
    '/admin/blog'                   => 'admin/content/blog.php',
    '/admin/blog/edit'              => 'admin/content/blog-edit.php',
    '/admin/homepage'               => 'admin/content/homepage.php',
    '/admin/email-settings'         => 'admin/email-settings.php',
    '/admin/site-settings'          => 'admin/site-settings.php',
    '/admin/email-templates'        => 'admin/email-templates.php',
    '/admin/email-log'              => 'admin/email-log.php',
    '/terms'                        => 'pages/page-cms.php',
    '/privacy'                      => 'pages/page-cms.php',
    '/faq'                          => 'pages/page-cms.php',
    '/api/notifications-unread'     => 'api/notifications-unread.php',
    '/api/mark-notification-read'   => 'api/mark-notification-read.php',
    '/api/smart-suggestions'        => 'api/smart-suggestions.php',
    '/api/toggle-save'              => 'api/toggle-save.php',
    '/api/get-saved'                => 'api/get-saved.php',
    '/api/upload'                   => 'api/upload.php',
    '/api/send-inquiry'             => 'api/send-inquiry.php',
    '/api/sign-nda'                 => 'api/sign-nda.php',
    '/api/conversations'            => 'api/conversations.php',
    '/api/messages'                 => 'api/messages.php',
    '/api/conversations/mark-read'  => 'api/conversation-mark-read.php',
    '/api/conversation-unread'      => 'api/conversation-unread.php',
    '/api/users'                    => 'api/users.php',
    '/api/messages-poll'            => 'api/messages-poll.php',
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

if (preg_match('#^/business/([a-z0-9-]+)$#', $path, $m) && !in_array($m[1], ['create', 'edit', 'download'], true)) {
    if (ctype_digit($m[1])) {
        $_GET['id'] = $m[1];
    } else {
        $_GET['slug'] = $m[1];
    }
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
        $_GET['slug'] = ltrim($path, '/');
        require $file;
        exit;
    }
}

$slug = ltrim($path, '/');
if ($slug && preg_match('/^[a-z0-9-]+$/', $slug)) {
    $file = __DIR__ . '/pages/page-cms.php';
    if (file_exists($file)) {
        $_GET['slug'] = $slug;
        require $file;
        exit;
    }
}

http_response_code(404);
require __DIR__ . '/pages/404.php';
