<?php
require __DIR__ . '/../config/bootstrap.php';

$slug = $_GET['slug'] ?? '';
$post = null;
try {
    $stmt = db()->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status='published' LIMIT 1");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();
} catch (\Throwable $e) { $post = null; }

if (!$post) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    return;
}

$pageTitle = $post['title'] . ' — ' . APP_NAME;
$pageDescription = $post['excerpt'] ?: mb_substr(strip_tags($post['body']), 0, 150);
$forcePublicHeader = true;

$articleSchema = '<script type="application/ld+json">' . json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $post['title'],
    'description' => $pageDescription,
    'author' => ['@type' => 'Organization', 'name' => $post['author']],
    'datePublished' => $post['published_at'] ? date('c', strtotime($post['published_at'])) : null,
    'mainEntityOfPage' => APP_URL . '/blog/' . $post['slug'],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';

require __DIR__ . '/../includes/header.php';
?>
<?= $articleSchema ?>
<main>
<article style="padding:48px 0 56px;background:#ffffff;">
  <div style="max-width:720px;margin:0 auto;padding:0 24px;">
    <div class="breadcrumbs" style="font-size:13px;color:#887271;font-family:Inter,sans-serif;margin-bottom:16px;">
      <a href="<?= APP_URL ?>/" style="color:#887271;text-decoration:none;">Home</a> <span style="margin:0 6px;">/</span>
      <a href="<?= APP_URL ?>/blog" style="color:#887271;text-decoration:none;">Blog</a> <span style="margin:0 6px;">/</span>
      <span style="color:#554242;"><?= e($post['title']) ?></span>
    </div>

    <h1 style="font-size:34px;line-height:42px;font-weight:800;letter-spacing:-0.02em;color:#6B1D22;font-family:Montserrat,sans-serif;margin:0 0 12px;"><?= e($post['title']) ?></h1>
    <div style="font-size:14px;color:#887271;font-family:Inter,sans-serif;margin-bottom:28px;">
      By <?= e($post['author']) ?><?= $post['published_at'] ? ' &middot; ' . e(date('M j, Y', strtotime($post['published_at']))) : '' ?>
    </div>

    <div style="font-size:17px;line-height:29px;color:#1c1b1b;font-family:Inter,sans-serif;">
      <?php foreach (preg_split('/\n\s*\n/', trim($post['body'])) as $para): ?>
        <?php if (trim($para) !== ''): ?>
        <p style="margin:0 0 20px;"><?= nl2br(e(trim($para))) ?></p>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <div style="margin-top:32px;padding-top:24px;border-top:1px solid #dbc0bf4d;">
      <a href="<?= APP_URL ?>/blog" style="font-size:14px;font-weight:600;color:#6B1D22;text-decoration:none;">&larr; Back to all articles</a>
    </div>
  </div>
</article>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
