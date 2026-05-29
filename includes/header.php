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
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script>
  tailwind.config = {
    darkMode: "class",
    theme: {
      extend: {
        colors: {
          "on-tertiary-fixed-variant": "#0a4a72",
          "background": "#fcf9f8",
          "primary-container": "#6b1d22",
          "on-tertiary": "#ffffff",
          "tertiary": "#00263f",
          "on-background": "#1c1b1b",
          "on-secondary-fixed": "#001e31",
          "secondary-fixed-dim": "#a4cbef",
          "inverse-primary": "#ffb3b2",
          "surface-bright": "#fcf9f8",
          "on-primary-fixed": "#410008",
          "on-tertiary-fixed": "#001d32",
          "error": "#ba1a1a",
          "on-error-container": "#93000a",
          "on-error": "#ffffff",
          "primary": "#4d060f",
          "surface-tint": "#9b4144",
          "surface-container-high": "#eae7e7",
          "on-primary-fixed-variant": "#7d2a2e",
          "error-container": "#ffdad6",
          "surface-container-low": "#f6f3f2",
          "tertiary-fixed": "#cde5ff",
          "surface-container-highest": "#e5e2e1",
          "on-surface": "#1c1b1b",
          "on-tertiary-container": "#76a8d5",
          "secondary": "#3b6281",
          "surface-dim": "#dcd9d9",
          "outline-variant": "#dbc0bf",
          "inverse-surface": "#313030",
          "surface-container": "#f0eded",
          "outline": "#887271",
          "on-secondary": "#ffffff",
          "on-surface-variant": "#554242",
          "secondary-container": "#b1d9fd",
          "primary-fixed": "#ffdad9",
          "tertiary-fixed-dim": "#9accfa",
          "surface": "#fcf9f8",
          "on-primary-container": "#f08484",
          "on-secondary-container": "#385f7e",
          "on-secondary-fixed-variant": "#214a68",
          "surface-variant": "#e5e2e1",
          "tertiary-container": "#003d60",
          "on-primary": "#ffffff",
          "inverse-on-surface": "#f3f0ef",
          "primary-fixed-dim": "#ffb3b2",
          "surface-container-lowest": "#ffffff",
          "secondary-fixed": "#cce5ff"
        }
      }
    }
  }
  </script>
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
