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

function extractFirstImage($html) {
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $html, $m)) return $m[1];
    return null;
}

function readingTime($html) {
    $text = strip_tags($html);
    $words = str_word_count($text);
    return max(1, (int)ceil($words / 200));
}

$img = extractFirstImage($post['body']);
$mins = readingTime($post['body']);
$pageTitle = $post['title'] . ' — ' . APP_NAME;
$pageDescription = $post['excerpt'] ?: mb_substr(strip_tags($post['body']), 0, 150);
$forcePublicHeader = true;

$relatedPosts = [];
try {
    $stmt = db()->prepare("SELECT title, slug, excerpt, published_at FROM blog_posts WHERE status='published' AND id != ? ORDER BY published_at DESC LIMIT 3");
    $stmt->execute([$post['id']]);
    $relatedPosts = $stmt->fetchAll();
} catch (\Throwable $e) {}

$shareUrl = APP_URL . '/blog/' . $post['slug'];
$shareText = rawurlencode($post['title'] . ' — Asaan Capital');

$articleSchema = '<script type="application/ld+json">' . json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $post['title'],
    'description' => $pageDescription,
    'image' => $img ?: APP_URL . '/assets/asaan-capital-logo-header.png',
    'author' => ['@type' => 'Organization', 'name' => $post['author']],
    'datePublished' => $post['published_at'] ? date('c', strtotime($post['published_at'])) : null,
    'mainEntityOfPage' => $shareUrl,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';

require __DIR__ . '/../includes/header.php';
?>
<?= $articleSchema ?>
<style>
.post-hero{ position:relative; overflow:hidden; }
.post-hero-img{ width:100%; aspect-ratio:21/9; object-fit:cover; display:block; }
.post-hero-overlay{ position:absolute; inset:0; background:linear-gradient(180deg, rgba(0,0,0,.05) 0%, rgba(0,0,0,.5) 100%); }
.post-hero-content{ position:absolute; bottom:0; left:0; right:0; padding:40px 24px; color:#fff; }
.post-hero-content .pub-wrap-narrow{ max-width:760px; margin:0 auto; }
.post-hero-content .breadcrumbs a{ color:rgba(255,255,255,.7); }
.post-hero-content .breadcrumbs span{ color:rgba(255,255,255,.9); }
.post-share{ display:flex; gap:10px; }
.post-share a{ display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; border-radius:12px; background:var(--dash-bg); border:1px solid var(--dash-border); color:var(--dash-ink-soft); text-decoration:none; transition:all var(--motion-fast); font-size:1.1rem; }
.post-share a:hover{ background:var(--color-primary); color:#fff; border-color:var(--color-primary); }
.related-grid{ display:grid; gap:var(--space-5); }
@media (min-width:768px){ .related-grid{ grid-template-columns:repeat(3,1fr); } }
@media (max-width:767px){ .related-grid{ grid-template-columns:1fr; } }
.author-box{ display:flex; gap:16px; align-items:flex-start; background:var(--dash-bg); border:1px solid var(--dash-border); border-radius:var(--dash-radius-card); padding:var(--space-5); }
.author-avatar{ display:inline-flex; align-items:center; justify-content:center; width:56px; height:56px; border-radius:50%; background:var(--color-primary); color:#fff; font-size:1.2rem; font-weight:700; flex-shrink:0; }
</style>
<main class="pub-page">

<?php if ($img): ?>
<div class="post-hero">
  <img src="<?= e($img) ?>" alt="" class="post-hero-img">
  <div class="post-hero-overlay"></div>
  <div class="post-hero-content">
    <div class="pub-wrap-narrow">
      <div class="breadcrumbs" style="margin-bottom:var(--space-3);font-size:.85rem;">
        <a href="<?= APP_URL ?>/" style="color:rgba(255,255,255,.7);text-decoration:none;">Home</a> <span style="margin:0 6px;color:rgba(255,255,255,.5);">/</span>
        <a href="<?= APP_URL ?>/blog" style="color:rgba(255,255,255,.7);text-decoration:none;">News & Insights</a> <span style="margin:0 6px;color:rgba(255,255,255,.5);">/</span>
        <span style="color:rgba(255,255,255,.9);"><?= e($post['title']) ?></span>
      </div>
      <h1 style="font-family:var(--font-heading);font-weight:800;font-size:clamp(1.4rem,3vw,2.2rem);line-height:1.15;letter-spacing:-.01em;color:#fff;margin:0 0 12px;"><?= e($post['title']) ?></h1>
      <div style="display:flex;flex-wrap:wrap;gap:12px;font-size:.85rem;opacity:.85;">
        <span>By <?= e($post['author']) ?></span>
        <span>&middot;</span>
        <span><?= $post['published_at'] ? date('M j, Y', strtotime($post['published_at'])) : '' ?></span>
        <span>&middot;</span>
        <span><?= $mins ?> min read</span>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<article class="pub-section">
  <div class="pub-wrap-narrow">
    <?php if (!$img): ?>
    <div class="breadcrumbs pub-text" style="margin-bottom:var(--space-4);">
      <a href="<?= APP_URL ?>/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a> <span style="margin:0 6px;">/</span>
      <a href="<?= APP_URL ?>/blog" style="color:var(--dash-ink-soft);text-decoration:none;">News & Insights</a> <span style="margin:0 6px;">/</span>
      <span><?= e($post['title']) ?></span>
    </div>
    <h1 class="pub-h1" style="margin-bottom:var(--space-2);"><?= e($post['title']) ?></h1>
    <div class="pub-text" style="margin-bottom:var(--space-6);display:flex;flex-wrap:wrap;gap:8px;">
      <span>By <?= e($post['author']) ?></span>
      <span>&middot;</span>
      <span><?= $post['published_at'] ? date('M j, Y', strtotime($post['published_at'])) : '' ?></span>
      <span>&middot;</span>
      <span><?= $mins ?> min read</span>
    </div>
    <?php endif; ?>

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

    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:var(--space-4);margin-top:var(--space-8);padding-top:var(--space-5);border-top:1px solid var(--dash-border);">
      <a href="<?= APP_URL ?>/blog" class="pub-text" style="font-weight:600;color:var(--dash-primary);text-decoration:none;">&larr; Back to all articles</a>
      <div class="post-share">
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>" target="_blank" rel="noopener" aria-label="Share on Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="https://twitter.com/intent/tweet?text=<?= $shareText ?>&url=<?= urlencode($shareUrl) ?>" target="_blank" rel="noopener" aria-label="Share on Twitter"><i class="fab fa-twitter"></i></a>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($shareUrl) ?>" target="_blank" rel="noopener" aria-label="Share on LinkedIn"><i class="fab fa-linkedin-in"></i></a>
        <a href="https://api.whatsapp.com/send?text=<?= $shareText . '%20' . urlencode($shareUrl) ?>" target="_blank" rel="noopener" aria-label="Share on WhatsApp"><i class="fab fa-whatsapp"></i></a>
      </div>
    </div>

    <div style="margin-top:var(--space-6);">
      <div class="author-box">
        <?php $initial = mb_strtoupper(mb_substr($post['author'], 0, 1)); ?>
        <span class="author-avatar"><?= $initial ?></span>
        <div>
          <h4 style="font-family:var(--font-heading);font-weight:700;font-size:1rem;color:var(--dash-ink);margin:0 0 4px;"><?= e($post['author']) ?></h4>
          <p class="pub-text" style="font-size:.85rem;margin:0;">Asaan Capital Ltd — Nepal's trusted financial advisory and investment company. We empower businesses and investors through transparent, innovative financial solutions.</p>
        </div>
      </div>
    </div>

    <?php if (!empty($relatedPosts)): ?>
    <div style="margin-top:var(--space-8);">
      <h2 class="pub-h3" style="margin-bottom:var(--space-4);">Related Articles</h2>
      <div class="related-grid">
        <?php foreach ($relatedPosts as $r): ?>
        <a href="<?= APP_URL ?>/blog/<?= e($r['slug']) ?>" class="pub-card hover" style="display:block;text-decoration:none;color:inherit;">
          <div class="pub-card-body" style="padding:var(--space-4);">
            <div class="pub-text" style="font-size:.78rem;margin-bottom:var(--space-1);"><?= $r['published_at'] ? date('M j, Y', strtotime($r['published_at'])) : '' ?></div>
            <h3 class="pub-card-title" style="font-size:.95rem;margin-bottom:var(--space-1);"><?= e($r['title']) ?></h3>
            <p class="pub-card-text" style="font-size:.82rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?= e($r['excerpt']) ?></p>
            <span style="font-size:.8rem;font-weight:600;color:var(--dash-primary);">Read more &rarr;</span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</article>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
