<?php if (!empty($dashChrome)): ?>
  </main>
</div><!-- /.dash-shell -->
<?php endif; ?>
<?php if (empty($hidePublicFooter)): ?>
<noscript>
  <footer style="background:var(--dash-ink);color:rgba(255,255,255,0.7);padding:40px 24px;text-align:center;font-size:14px;">
    <p style="margin:0;">&copy; <?= date('Y') ?> Asaan Capital Ltd. All rights reserved.</p>
    <nav style="margin-top:12px;display:flex;gap:16px;justify-content:center;">
      <a href="/about" style="color:rgba(255,255,255,0.8);text-decoration:none;">About</a>
      <a href="/legal" style="color:rgba(255,255,255,0.8);text-decoration:none;">Privacy</a>
      <a href="/support" style="color:rgba(255,255,255,0.8);text-decoration:none;">Support</a>
    </nav>
  </footer>
</noscript>
<div id="footer-root"></div>
<script>document.addEventListener('DOMContentLoaded', injectFooter);</script>
<?php endif; ?>
</body>
</html>
