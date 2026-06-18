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
<main class="pub-page">
<article class="pub-section">
  <div class="pub-wrap-narrow">
    <div class="breadcrumbs pub-text" style="margin-bottom:var(--space-4);">
      <a href="<?= APP_URL ?>/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a> <span style="margin:0 6px;">/</span>
      <a href="<?= APP_URL ?>/blog" style="color:var(--dash-ink-soft);text-decoration:none;">Blog</a> <span style="margin:0 6px;">/</span>
      <span><?= e($post['title']) ?></span>
    </div>

    <h1 class="pub-h1" style="margin-bottom:var(--space-2);"><?= e($post['title']) ?></h1>
    <div class="pub-text" style="margin-bottom:var(--space-6);">
      By <?= e($post['author']) ?><?= $post['published_at'] ? ' &middot; ' . e(date('M j, Y', strtotime($post['published_at']))) : '' ?>
    </div>

    <div class="pub-prose trix-content">
      <?php
      $body = $post['body'];
      if ($body !== strip_tags($body)) {
          echo $body;
      } else {
          foreach (preg_split('/\n\s*\n/', trim($body)) as $para) {
              if (trim($para) !== '') echo '<p>' . nl2br(e(trim($para))) . '</p>';
          }
      }
      ?>
    </div>

    <div style="margin-top:var(--space-8);padding-top:var(--space-5);border-top:1px solid var(--dash-border);">
      <a href="<?= APP_URL ?>/blog" class="pub-text" style="font-weight:600;color:var(--dash-primary);text-decoration:none;">&larr; Back to all articles</a>
    </div>
  </div>
</article>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
