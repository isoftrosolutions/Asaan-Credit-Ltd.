<?php $hidePublicFooter = true; ?>
<?php require __DIR__ . '/header.php'; ?>
<div class="dashboard">
  <div id="sidebar-root"></div>
  <script>
    const ROLE_MAP = {
      'investor': 'investor',
      'business_owner': 'owner',
      'entrepreneur': 'entrepreneur',
      'franchisor': 'franchisor',
      'advisor': 'advisor'
    };
    injectSidebar(ROLE_MAP[CURRENT_USER?.role] || 'investor');
  </script>
  <div class="container main-content">
