<?php
require __DIR__ . '/../config/bootstrap.php';

$pageTitle = 'News & Insights — ' . APP_NAME;
$pageDescription = 'Expert insights on investment, business valuation, M&A, and entrepreneurship in Nepal from Asaan Capital Ltd.';
$forcePublicHeader = true;

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$posts = [];
$total = 0;
try {
    $countStmt = db()->query("SELECT COUNT(*) FROM blog_posts WHERE status='published'");
    $total = (int)$countStmt->fetchColumn();

    $stmt = db()->prepare("SELECT title, slug, excerpt, body, author, published_at, created_at FROM blog_posts WHERE status='published' ORDER BY published_at DESC, id DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute();
    $posts = $stmt->fetchAll();
} catch (\Throwable $e) { $posts = []; }

$totalPages = (int)ceil($total / $perPage);

function extractFirstImage($html) {
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $html, $m)) return $m[1];
    return null;
}

function readingTime($html) {
    $text = strip_tags($html);
    $words = str_word_count($text);
    $mins = max(1, (int)ceil($words / 200));
    return $mins;
}

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"News & Insights","item":"'.APP_URL.'/blog"}
  ]
}</script>';
require __DIR__ . '/../includes/header.php';
?>
<?= $breadcrumbSchema ?>
<style>
.bl-grid{ display:grid; gap:var(--space-5); }
@media (min-width:768px){ .bl-grid{ grid-template-columns:repeat(3,1fr); } }
@media (max-width:767px){ .bl-grid{ grid-template-columns:1fr; } }
.bl-card{ display:flex; flex-direction:column; background:var(--dash-card); border:1px solid var(--dash-border); border-radius:var(--dash-radius-card); overflow:hidden; text-decoration:none; color:inherit; transition:box-shadow var(--motion-base), transform var(--motion-base); }
.bl-card:hover{ box-shadow:var(--dash-shadow-hover); transform:translateY(-4px); }
.bl-card-img{ width:100%; aspect-ratio:16/9; object-fit:cover; background:var(--dash-bg); }
.bl-card-body{ padding:var(--space-4); flex:1; display:flex; flex-direction:column; }
.bl-card-meta{ display:flex; align-items:center; gap:12px; font-size:.78rem; color:var(--dash-ink-soft); margin-bottom:var(--space-2); }
.bl-card-avatar{ display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:50%; background:var(--color-primary); color:#fff; font-size:.7rem; font-weight:700; flex-shrink:0; }
.bl-card-title{ font-family:var(--font-heading); font-weight:700; font-size:1rem; color:var(--dash-ink); margin-bottom:var(--space-2); line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.bl-card-text{ font-size:.85rem; line-height:1.6; color:var(--dash-ink-soft); margin-bottom:var(--space-3); display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; flex:1; }
.bl-card-footer{ display:flex; align-items:center; justify-content:space-between; padding-top:var(--space-3); border-top:1px solid var(--dash-border); font-size:.82rem; }
.bl-img-placeholder{ width:100%; aspect-ratio:16/9; background:linear-gradient(135deg, var(--color-primary) 0%, #4A1317 100%); display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,.3); font-size:3rem; }
.pagination{ display:flex; justify-content:center; gap:8px; margin-top:var(--space-8); flex-wrap:wrap; }
.pagination a,.pagination span{ display:inline-flex; align-items:center; justify-content:center; min-width:40px; height:40px; padding:0 12px; border-radius:10px; font-size:.9rem; font-weight:600; text-decoration:none; }
.pagination a{ background:var(--dash-card); border:1px solid var(--dash-border); color:var(--dash-ink); }
.pagination a:hover{ border-color:var(--dash-primary); color:var(--dash-primary); }
.pagination .active{ background:var(--color-primary); color:#fff; border-color:var(--color-primary); }
</style>
<main class="pub-page">

<section class="pub-hero" style="padding:60px 0;background:linear-gradient(135deg, var(--color-primary) 0%, #4A1317 100%);color:#fff;">
  <div class="pub-wrap">
    <div style="max-width:680px;">
      <span style="display:inline-block;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.7);margin-bottom:8px;">News & Insights</span>
      <h1 class="pub-h1" style="color:#fff;margin:0 0 12px;">Latest from Asaan Capital</h1>
      <p style="font-size:1.05rem;line-height:1.7;opacity:.88;">Expert perspectives on investment, business valuation, M&A, and entrepreneurship in Nepal.</p>
    </div>
  </div>
</section>

<section class="pub-section">
  <div class="pub-wrap">
    <?php if (empty($posts)): ?>
      <div class="pub-card" style="padding:var(--space-8) var(--space-5);text-align:center;">
        <span class="material-symbols-outlined" style="font-size:48px;color:var(--dash-ink-soft);margin-bottom:var(--space-3);">article</span>
        <h3 class="pub-h3" style="margin-bottom:var(--space-2);">Articles coming soon</h3>
        <p class="pub-text">We're preparing our first set of guides and insights. Check back shortly.</p>
      </div>
    <?php else: ?>
      <div class="bl-grid">
        <?php foreach ($posts as $p):
          $img = extractFirstImage($p['body']);
          $initial = mb_strtoupper(mb_substr($p['author'], 0, 1));
          $date = $p['published_at'] ? date('M j, Y', strtotime($p['published_at'])) : date('M j, Y', strtotime($p['created_at']));
          $mins = readingTime($p['body']);
        ?>
        <a href="<?= APP_URL ?>/blog/<?= e($p['slug']) ?>" class="bl-card">
          <?php if ($img): ?>
            <img src="<?= e($img) ?>" alt="" class="bl-card-img" loading="lazy">
          <?php else: ?>
            <div class="bl-img-placeholder"><span class="material-symbols-outlined" style="font-size:48px;">finance</span></div>
          <?php endif; ?>
          <div class="bl-card-body">
            <div class="bl-card-meta">
              <span class="bl-card-avatar"><?= $initial ?></span>
              <span><?= e($p['author']) ?></span>
              <span>&middot;</span>
              <span><?= $date ?></span>
              <span>&middot;</span>
              <span><?= $mins ?> min read</span>
            </div>
            <h2 class="bl-card-title"><?= e($p['title']) ?></h2>
            <p class="bl-card-text"><?= e($p['excerpt']) ?></p>
            <div class="bl-card-footer">
              <span style="font-weight:600;color:var(--dash-primary);font-size:.85rem;">Read more &rarr;</span>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

      <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="<?= APP_URL ?>/blog?page=<?= $page - 1 ?>">&larr; Prev</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php if ($i === $page): ?>
            <span class="active"><?= $i ?></span>
          <?php else: ?>
            <a href="<?= APP_URL ?>/blog?page=<?= $i ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a href="<?= APP_URL ?>/blog?page=<?= $page + 1 ?>">Next &rarr;</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
