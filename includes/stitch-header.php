<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function stitch_is_active($url) {
    global $currentPath;
    if ($url === '/') return $currentPath === '/';
    return $currentPath === $url || str_starts_with($currentPath, $url . '/');
}
?>
<header class="stitch-header">
  <div class="stitch-header-inner">
    <a href="/" class="stitch-logo" aria-label="<?= APP_NAME ?>">
      <span class="stitch-logo-mark">AC</span>
      <span class="stitch-logo-text">Asaan Capital</span>
    </a>

    <nav class="stitch-nav" id="stitchNav">
      <button class="stitch-nav-close" onclick="closeStitchMenu()" aria-label="Close menu">&times;</button>
      <div class="stitch-nav-links">
        <a href="/" class="stitch-nav-link <?= stitch_is_active('/') ? 'active' : '' ?>">Home</a>
        <a href="/browse/businesses" class="stitch-nav-link <?= stitch_is_active('/browse') ? 'active' : '' ?>">Opportunities</a>
        <a href="/about" class="stitch-nav-link <?= stitch_is_active('/about') ? 'active' : '' ?>">About</a>
        <a href="/blog" class="stitch-nav-link <?= stitch_is_active('/blog') ? 'active' : '' ?>">Blog</a>
        <a href="/contact" class="stitch-nav-link <?= stitch_is_active('/contact') ? 'active' : '' ?>">Contact</a>
      </div>
      <div class="stitch-nav-auth">
        <?php if (current_user()): ?>
        <a href="/dashboard" class="stitch-btn stitch-btn-outline">Dashboard</a>
        <?php else: ?>
        <a href="/login" class="stitch-btn stitch-btn-outline">Log in</a>
        <a href="/onboarding" class="stitch-btn stitch-btn-primary">Sign up</a>
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
