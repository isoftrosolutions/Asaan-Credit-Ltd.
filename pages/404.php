<?php
$pageTitle = 'Page Not Found';
$pageDescription = 'Page not found. The page you are looking for does not exist on Asaan Capital Ltd.';
require __DIR__ . '/../includes/layout-public.php';
?>
<div style="text-align:center;padding:6rem 2rem;">
  <h1 style="font-size:5rem;color:var(--brand-red);margin-bottom:0.5rem;">404</h1>
  <h2 style="margin-bottom:1rem;">Page Not Found</h2>
  <p style="color:var(--secondary-text);margin-bottom:2rem;font-size:1.1rem;">The page you're looking for doesn't exist or has been moved.</p>
  <a href="/" class="btn btn-primary">Go Home</a>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
