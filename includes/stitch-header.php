<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function stitch_is_active($url) {
    global $currentPath;
    $path = preg_replace('#^/assan#', '', $currentPath) ?: '/';
    if ($url === '/') return $path === '/';
    return $path === $url || str_starts_with($path, $url . '/');
}
?>
<header class="stitch-header">
  <div class="stitch-header-inner">
    <a href="<?= APP_URL ?>/" class="stitch-logo" aria-label="<?= APP_NAME ?>">
      <img src="<?= APP_URL ?>/assets/asaan-capital-logo-header.png" alt="<?= APP_NAME ?>" class="stitch-logo-img">
    </a>

    <nav class="stitch-nav" id="stitchNav">
      <button class="stitch-nav-close" onclick="closeStitchMenu()" aria-label="Close menu">&times;</button>
      <div class="stitch-nav-links">
        <a href="<?= APP_URL ?>/" class="stitch-nav-link <?= stitch_is_active('/') ? 'active' : '' ?>">Home</a>
        <a href="<?= APP_URL ?>/browse/businesses" class="stitch-nav-link <?= stitch_is_active('/browse') ? 'active' : '' ?>">Investment &amp; Opportunities</a>
        <a href="<?= APP_URL ?>/about" class="stitch-nav-link <?= stitch_is_active('/about') ? 'active' : '' ?>">About Us</a>
        <a href="<?= APP_URL ?>/blog" class="stitch-nav-link <?= stitch_is_active('/blog') ? 'active' : '' ?>">Blog</a>
        <a href="<?= APP_URL ?>/contact" class="stitch-nav-link <?= stitch_is_active('/contact') ? 'active' : '' ?>">Contact</a>
      </div>
      <div class="stitch-nav-auth">
        <?php if (current_user()): ?>
        <a href="<?= APP_URL ?>/dashboard" class="stitch-btn stitch-btn-outline">Dashboard</a>
        <?php else: ?>
        <a href="<?= APP_URL ?>/login" class="stitch-btn stitch-btn-outline">Log in</a>
        <a href="<?= APP_URL ?>/onboarding" class="stitch-btn stitch-btn-primary">Sign up</a>
        <?php endif; ?>
      </div>
    </nav>

    <button class="stitch-header-toggle" onclick="toggleStitchMenu()" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>
  </div>
  <div class="stitch-backdrop" id="stitchBackdrop" onclick="closeStitchMenu()"></div>
</header>
<script>
function toggleStitchMenu() {
  document.getElementById('stitchNav').classList.toggle('open');
  document.getElementById('stitchBackdrop').classList.toggle('open');
  document.body.classList.toggle('menu-open');
}
function closeStitchMenu() {
  document.getElementById('stitchNav').classList.remove('open');
  document.getElementById('stitchBackdrop').classList.remove('open');
  document.body.classList.remove('menu-open');
}
</script>
