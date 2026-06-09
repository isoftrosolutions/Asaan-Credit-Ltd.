<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'Blog — ' . APP_NAME;
$pageDescription = 'Insights on buying, selling, valuing, and funding businesses in Nepal from the ' . APP_NAME_LONG . ' team.';
$forcePublicHeader = true;

$posts = [];
try {
    $posts = db()->query("SELECT title, slug, excerpt, author, published_at FROM blog_posts WHERE status='published' ORDER BY published_at DESC, id DESC")->fetchAll();
} catch (\Throwable $e) { $posts = []; }

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Blog","item":"'.APP_URL.'/blog"}
  ]
}</script>';
require __DIR__ . '/../includes/header.php';
?>
<?= $breadcrumbSchema ?>
<style>
@media (min-width:768px){ .bl-grid{ grid-template-columns:repeat(3,1fr); } }
@media (max-width:767px){ .bl-grid{ grid-template-columns:1fr; } }
</style>
<main class="pub-page">

<section class="pub-section tight">
  <div class="pub-wrap">
    <div class="breadcrumbs pub-text" style="margin-bottom:var(--space-4);">
      <a href="<?= APP_URL ?>/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a> <span style="margin:0 6px;">/</span>
      <span>Blog</span>
    </div>
    <h1 class="pub-h1">Blog</h1>
    <p class="pub-lead">Practical guides on buying, selling, valuing, and funding businesses in Nepal.</p>
  </div>
</section>

<section class="pub-section">
  <div class="pub-wrap">
    <?php if (empty($posts)): ?>
      <div class="pub-card" style="padding:var(--space-8) var(--space-5);text-align:center;">
        <h3 class="pub-h3" style="margin-bottom:var(--space-2);">Articles coming soon</h3>
        <p class="pub-text">We're preparing our first set of guides. Check back shortly.</p>
      </div>
    <?php else: ?>
      <div class="bl-grid" style="display:grid;gap:var(--space-5);">
        <?php foreach ($posts as $p): ?>
        <a href="<?= APP_URL ?>/blog/<?= e($p['slug']) ?>" class="pub-card hover" style="display:block;border-top:4px solid var(--dash-primary);text-decoration:none;color:inherit;">
          <div class="pub-text" style="font-size:.8rem;margin-bottom:var(--space-2);"><?= $p['published_at'] ? e(date('M j, Y', strtotime($p['published_at']))) : '' ?></div>
          <h2 class="pub-card-title" style="font-size:1.05rem;margin-bottom:var(--space-2);"><?= e($p['title']) ?></h2>
          <p class="pub-card-text"><?= e($p['excerpt']) ?></p>
          <span style="font-size:.85rem;font-weight:600;color:var(--dash-primary);">Read more &rarr;</span>
        </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
