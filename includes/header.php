<?php
// Self-sufficient: guarantees bootstrap (current_user(), db(), …) is loaded
// even when this header is rendered by a page reached directly rather than
// through index.php (e.g. a direct hit on pages/404.php). bootstrap.php has
// its own re-entry guard, so this is a no-op on the normal routed path.
require_once __DIR__ . '/../config/bootstrap.php';

$user = current_user();
$isAdmin = $user && !empty($user['is_admin']);
$unreadCount = 0;
if ($user) {
    try {
        $stmt = db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$user['id']]);
        $unreadCount = (int)$stmt->fetchColumn();
    } catch (\Throwable $e) {}
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'Asaan Capital Ltd') ?></title>
  <link rel="canonical" href="<?= e($canonicalUrl ?? (APP_URL . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) ?>">
  <meta name="description" content="<?= e($pageDescription ?? 'Asaan Capital Ltd - Financial & Investment Services. The premium marketplace for buying, selling, franchising, and funding SMEs.') ?>">
  <meta property="og:title" content="<?= e($pageTitle ?? APP_NAME) ?>">
  <meta property="og:description" content="<?= e($pageDescription ?? 'Asaan Capital Ltd - Financial & Investment Services. The premium marketplace for buying, selling, franchising, and funding SMEs.') ?>">
  <meta property="og:url" content="<?= APP_URL . $_SERVER['REQUEST_URI'] ?>">
  <meta property="og:type" content="website">
  <meta property="og:image" content="<?= APP_URL ?>/og-image.png">
  <meta name="twitter:card" content="summary_large_image">
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "Asaan Capital Ltd",
    "description": "Financial & Investment Services",
    "url": "https://asaancapital.com",
    "logo": "https://asaancapital.com/logo.png",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "Madhyapur Thimi Municipality-9",
      "addressLocality": "Bhaktapur",
      "addressCountry": "NP"
    },
    "contactPoint": {
      "@type": "ContactPoint",
      "email": "info@asaancapital.com",
      "telephone": "+977-9848714990",
      "contactType": "customer service"
    },
    "sameAs": [
      "https://facebook.com/asaancapital",
      "https://instagram.com/asaancapital",
      "https://x.com/asaancapital",
      "https://youtube.com/@asaancapital",
      "https://tiktok.com/@asaancapital",
      "https://linkedin.com/company/asaancapital",
      "https://threads.net/@asaancapital"
    ]
  }
  </script>
  <?php if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/' && !isset($extraSchema)): ?>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "Asaan Capital Ltd",
    "url": "https://asaancapital.com",
    "potentialAction": {
      "@type": "SearchAction",
      "target": {
        "@type": "EntryPoint",
        "urlTemplate": "https://asaancapital.com/search?q={search_term_string}"
      },
      "query-input": "required name=search_term_string"
    }
  }
  </script>
  <?php endif; ?>
  <?= $extraSchema ?? '' ?>
  <link rel="icon" type="image/png" href="<?= APP_URL ?>/favicon.png">
  <link rel="shortcut icon" href="<?= APP_URL ?>/favicon.png">
  <style>html.scroll-smooth { scroll-behavior: smooth; }</style>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Montserrat:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
  <style>
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
  </style>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/styles.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/listings.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/header.css">
</head>
<body>
<a href="#main-content" class="skip-link">Skip to main content</a>
<noscript>
  <header class="site-header pub-header" style="border-bottom:1px solid var(--dash-border);background:var(--color-bg);">
    <div class="pub-header-inner" style="max-width:1200px;margin:0 auto;padding:0 24px;display:flex;align-items:center;height:64px;gap:32px;">
      <a href="/" class="header-logo"><img src="/logo.png" width="140" height="35" alt="Asaan Capital Ltd" style="display:block;"></a>
      <nav style="display:flex;gap:24px;">
        <a href="/" style="color:var(--dash-ink);text-decoration:none;font-weight:500;">Home</a>
        <a href="/browse/businesses" style="color:var(--dash-ink);text-decoration:none;font-weight:500;">Opportunities</a>
        <a href="/about" style="color:var(--dash-ink);text-decoration:none;font-weight:500;">About</a>
        <a href="/blog" style="color:var(--dash-ink);text-decoration:none;font-weight:500;">Blog</a>
      </nav>
      <div style="margin-left:auto;display:flex;gap:8px;">
        <a href="/login" style="padding:8px 16px;border:1px solid var(--dash-border);border-radius:8px;text-decoration:none;color:var(--dash-ink);font-weight:500;">Log in</a>
        <a href="/onboarding" style="padding:8px 16px;background:var(--color-primary-vivid);color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">Sign up</a>
      </div>
    </div>
  </header>
</noscript>
<div id="header-root"></div>
<script src="<?= APP_URL ?>/assets/icons.js?v=<?= filemtime(__DIR__ . '/../assets/icons.js') ?>"></script>
<script src="<?= APP_URL ?>/assets/header.js?v=<?= filemtime(__DIR__ . '/../assets/header.js') ?>" defer></script>
<script src="<?= APP_URL ?>/assets/components.js?v=<?= filemtime(__DIR__ . '/../assets/components.js') ?>" defer></script>
<script>
const UNREAD_COUNT = <?= $unreadCount ?>;
const CURRENT_USER = <?= json_encode($user, JSON_INVALID_UTF8_SUBSTITUTE) ?: 'null' ?>;
const CSRF_TOKEN = '<?= csrf_token() ?>';
document.addEventListener('DOMContentLoaded', function () {
<?php
$headerActions = '';
if ($user) {
    $initial = mb_strtoupper(mb_substr($user['name'] ?? 'U', 0, 1));
    $bellLabel = $unreadCount > 0 ? "Notifications ({$unreadCount} unread)" : 'Notifications';
    $badgeStyle = $unreadCount > 0 ? '' : ' style="display:none;"';
    $badgeText = $unreadCount > 9 ? '9+' : (string)$unreadCount;
    $userName = $user['name'] ?? 'User';
    $headerActions = '<a href="/notifications" class="notification-bell" aria-label="' . e($bellLabel) . '">'
        . '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>'
        . '<span class="notification-badge" aria-hidden="true"' . $badgeStyle . '>' . $badgeText . '</span></a>'
        . '<a href="/dashboard" class="header-user" aria-label="' . e($userName) . ' — go to dashboard">'
        . '<div class="avatar avatar-sm" aria-hidden="true">' . e($initial) . '</div>'
        . '<span class="header-user-name">' . e($userName) . '</span></a>';
} elseif (!empty($onboardingPage)) {
    $headerActions = '<a href="/login" class="btn btn-sm btn-outline">Log in</a>';
} else {
    $headerActions = '<a href="/login" class="btn btn-sm btn-outline">Log in</a>'
        . '<a href="/onboarding" class="btn btn-sm btn-primary">Sign up</a>';
}
?>
<?php if (!empty($dashChrome)): ?>
/* Dashboard chrome is rendered in PHP (see layout-dashboard.php); skip JS injection. */
<?php elseif ($user && empty($forcePublicHeader)): ?>
injectHeader('<?= $isAdmin ? 'admin' : 'dashboard' ?>', <?= json_encode($headerActions) ?>);
<?php else: ?>
injectHeader('public', <?= json_encode($headerActions) ?>);
<?php endif; ?>
});
</script>
<?php flash_render(); ?>
