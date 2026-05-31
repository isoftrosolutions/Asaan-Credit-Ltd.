<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'Blog — ' . APP_NAME;
$pageDescription = 'Insights on buying, selling, valuing, and funding businesses in Nepal from the ' . APP_NAME . ' team.';
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
<main>
<section style="padding:48px 0 24px;background:#ffffff;">
  <div style="max-width:1000px;margin:0 auto;padding:0 24px;">
    <div class="breadcrumbs" style="font-size:13px;color:#887271;font-family:Inter,sans-serif;margin-bottom:16px;">
      <a href="<?= APP_URL ?>/" style="color:#887271;text-decoration:none;">Home</a> <span style="margin:0 6px;">/</span>
      <span style="color:#554242;">Blog</span>
    </div>
    <h1 style="font-size:36px;line-height:44px;font-weight:800;letter-spacing:-0.02em;color:#6B1D22;font-family:Montserrat,sans-serif;margin:0 0 14px;">Blog</h1>
    <p style="font-size:17px;line-height:27px;color:#554242;font-family:Inter,sans-serif;margin:0;">Practical guides on buying, selling, valuing, and funding businesses in Nepal.</p>
  </div>
</section>

<section style="padding:24px 0 56px;background:#ffffff;">
  <div style="max-width:1000px;margin:0 auto;padding:0 24px;">
    <?php if (empty($posts)): ?>
      <div style="padding:48px 24px;background:#fcf9f8;border-radius:16px;border:1px solid #dbc0bf4d;text-align:center;">
        <h3 style="margin:0 0 0.5rem;font-family:Montserrat,sans-serif;color:#1c1b1b;">Articles coming soon</h3>
        <p style="margin:0;color:#554242;font-family:Inter,sans-serif;">We're preparing our first set of guides. Check back shortly.</p>
      </div>
    <?php else: ?>
      <div class="bl-grid" style="display:grid;gap:20px;">
        <?php foreach ($posts as $p): ?>
        <a href="<?= APP_URL ?>/blog/<?= e($p['slug']) ?>" style="display:block;background:#fff;border:1px solid #dbc0bf4d;border-radius:16px;border-top:4px solid #6B1D22;padding:22px;text-decoration:none;transition:box-shadow 0.3s,transform 0.2s;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,0.08)';this.style.transform='translateY(-2px)'" onmouseout="this.style.boxShadow='none';this.style.transform='none'">
          <div style="font-size:12px;color:#887271;font-family:Inter,sans-serif;margin-bottom:8px;"><?= $p['published_at'] ? e(date('M j, Y', strtotime($p['published_at']))) : '' ?></div>
          <h2 style="font-size:18px;line-height:25px;font-weight:700;color:#1c1b1b;font-family:Montserrat,sans-serif;margin:0 0 10px;"><?= e($p['title']) ?></h2>
          <p style="font-size:14px;line-height:21px;color:#554242;font-family:Inter,sans-serif;margin:0 0 14px;"><?= e($p['excerpt']) ?></p>
          <span style="font-size:13px;font-weight:600;color:#6B1D22;font-family:Inter,sans-serif;">Read more &rarr;</span>
        </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
