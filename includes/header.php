<?php
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
  <title><?= e($pageTitle ?? APP_NAME) ?></title>
  <link rel="canonical" href="<?= APP_URL ?><?= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?>">
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
    "contactPoint": {
      "@type": "ContactPoint",
      "email": "hello@asaancapital.com",
      "contactType": "customer service"
    }
  }
  </script>
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
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/header.css">
</head>
<body>
<div id="header-root"></div>
<script src="<?= APP_URL ?>/assets/icons.js"></script>
<script src="<?= APP_URL ?>/assets/header.js"></script>
<script src="<?= APP_URL ?>/assets/components.js"></script>
<script>
const UNREAD_COUNT = <?= $unreadCount ?>;
const CURRENT_USER = <?= json_encode($user) ?>;
<?php if ($user): ?>
injectHeader('<?= $isAdmin ? 'admin' : 'dashboard' ?>');
<?php else: ?>
injectHeader('public');
<?php endif; ?>
</script>
<?php flash_render(); ?>
