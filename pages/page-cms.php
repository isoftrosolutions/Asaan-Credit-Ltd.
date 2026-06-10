<?php
require __DIR__ . '/../config/bootstrap.php';

$slug = $_GET['slug'] ?? '';
if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$stmt = db()->prepare("SELECT * FROM pages WHERE slug = ? AND is_active = 1 LIMIT 1");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$pageTitle = $page['title'] . ' — ' . APP_NAME_LONG;
$pageDescription = $page['meta_description'] ?? $page['title'];
$forcePublicHeader = true;
require __DIR__ . '/../includes/header.php';
?>
<main class="pub-page">
  <?= $page['content_html'] ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
