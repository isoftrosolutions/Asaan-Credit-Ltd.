<?php
$pageTitle = 'Page Not Found';
$pageDescription = 'Page not found. The page you are looking for does not exist on Asaan Capital Ltd.';
require __DIR__ . '/../includes/layout-public.php';
?>
<main class="pub-page">
  <section class="pub-section" style="text-align:center;">
    <div class="pub-wrap-narrow">
      <h1 class="pub-h1" style="font-size:5rem;color:var(--dash-primary);line-height:1;margin-bottom:var(--space-2);">404</h1>
      <h2 class="pub-h2" style="margin-bottom:var(--space-4);">Page Not Found</h2>
      <p class="pub-lead" style="margin-bottom:var(--space-6);">The page you're looking for doesn't exist or has been moved.</p>
      <a href="/" class="btn btn-primary btn-lg">Go Home</a>
    </div>
  </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
