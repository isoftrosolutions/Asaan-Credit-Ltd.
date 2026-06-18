<?php
require __DIR__ . '/../config/bootstrap.php';

$pitchId = (int)($_GET['id'] ?? 0);
if ($pitchId < 1) {
    http_response_code(404);
    require __DIR__ . '/../pages/404.php';
    exit;
}

$db = db();
$user = current_user();
$userId = $user ? (int)$user['id'] : 0;

$stmt = $db->prepare('SELECT p.*, s.name AS sector_name, u.name AS entrepreneur_name, u.company_name, u.province, u.district, u.verification_status, u.id AS owner_id, u.profile_photo FROM pitches p LEFT JOIN sectors s ON s.id = p.sector_id JOIN users u ON u.id = p.user_id WHERE p.id = ? AND (p.is_published = 1 OR p.user_id = ?)');
$stmt->execute([$pitchId, $userId]);
$pitch = $stmt->fetch();

if (!$pitch) {
    http_response_code(404);
    require __DIR__ . '/../pages/404.php';
    exit;
}

$db->prepare('UPDATE pitches SET views = views + 1 WHERE id = ?')->execute([$pitchId]);

$ownerUserId = (int)$pitch['owner_id'];
$viewerIsPremium = $user && (!empty($user['is_premium']) || !empty($user['is_admin']) || $userId === $ownerUserId);
$hasMatch = false;

if ($user) {
    $userId = (int)$user['id'];
    $matchStmt = $db->prepare("SELECT id FROM matches WHERE context_type = 'pitch' AND context_id = ? AND ((user_a_id = ? AND user_b_id = ?) OR (user_a_id = ? AND user_b_id = ?)) AND closed_status = 'open' LIMIT 1");
    $matchStmt->execute([$pitchId, $userId, $ownerUserId, $ownerUserId, $userId]);
    $hasMatch = (bool)$matchStmt->fetch();
}

$mediaStmt = $db->prepare('SELECT * FROM pitch_media WHERE pitch_id = ? ORDER BY sort_order');
$mediaStmt->execute([$pitchId]);
$media = $mediaStmt->fetchAll();

$teamStmt = $db->prepare('SELECT * FROM pitch_team_members WHERE pitch_id = ? ORDER BY id');
$teamStmt->execute([$pitchId]);
$teamMembers = $teamStmt->fetchAll();

$initials = e(strtoupper(mb_substr($pitch['entrepreneur_name'] ?? '?', 0, 2)));

$pageTitle = e($pitch['tagline']) . ' — ' . APP_NAME;
$pageDescription = 'View this investment pitch on Asaan Capital Ltd. Learn about the funding opportunity, equity offered, and entrepreneur background.';

$location = array_filter([$pitch['district'] ?? '', $pitch['province'] ?? '']);
$locStr = !empty($location) ? implode(', ', $location) : 'Nepal';

$images = array_values(array_filter($media, fn($m) => $m['media_type'] === 'image'));
$firstImg = $images[0] ?? null;

require __DIR__ . '/../includes/layout-public.php';
?>
<div class="stitch-detail">

<nav class="stitch-breadcrumb">
  <a href="<?= APP_URL ?>/">Home</a>
  <span class="sep">›</span>
  <a href="<?= APP_URL ?>/browse/entrepreneurs">Entrepreneurs</a>
  <span class="sep">›</span>
  <span><?= e($pitch['tagline']) ?></span>
</nav>

<section class="stitch-hero">
  <div class="stitch-hero-inner">
    <div class="stitch-hero-left">
      <div class="stitch-hero-badges">
        <?php if ($pitch['sector_name']): ?>
        <span class="stitch-badge stitch-badge-industry"><?= e($pitch['sector_name']) ?></span>
        <?php endif; ?>
        <?php if ($pitch['stage']): ?>
        <span class="stitch-badge stitch-badge-sale"><?= e(ucfirst($pitch['stage'])) ?></span>
        <?php endif; ?>
        <?php if ($pitch['verification_status'] === 'verified'): ?>
        <span class="stitch-badge stitch-badge-verified"><i class="fas fa-shield-alt" style="font-size:11px;"></i> Verified</span>
        <?php endif; ?>
      </div>

      <h1 class="stitch-hero-title"><?= e($pitch['tagline']) ?></h1>

      <div class="stitch-hero-location">
        <i class="fas fa-map-marker-alt" style="font-size:15px;"></i>
        <span><?= e($locStr) ?></span>
      </div>

      <p class="stitch-hero-desc"><?= e($pitch['short_summary'] ?: $pitch['tagline']) ?></p>

      <div class="stitch-hero-metrics">
        <?php if ($pitch['funding_amount']): ?>
        <div class="stitch-metric-card">
          <span class="label">Funding Ask</span>
          <span class="value"><?= money($pitch['funding_amount']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($pitch['equity_offered']): ?>
        <div class="stitch-metric-card">
          <span class="label">Equity Offered</span>
          <span class="value"><?= e($pitch['equity_offered']) ?>%</span>
        </div>
        <?php endif; ?>
        <?php if ($pitch['valuation']): ?>
        <div class="stitch-metric-card">
          <span class="label">Valuation</span>
          <span class="value"><?= money($pitch['valuation']) ?></span>
        </div>
        <?php endif; ?>
      </div>

      <div class="stitch-hero-actions">
        <?php if ($user && (int)$user['id'] === $ownerUserId): ?>
        <a href="<?= APP_URL ?>/entrepreneur/pitch-edit.php?id=<?= (int)$pitchId ?>" class="stitch-btn stitch-btn-secondary">
          <i class="fas fa-edit" style="font-size:15px;"></i>
          Edit Pitch
        </a>
        <?php elseif ($viewerIsPremium): ?>
        <button class="stitch-btn stitch-btn-primary" onclick="document.getElementById('interest-modal').classList.add('open')">
          <i class="fas fa-envelope" style="font-size:16px;"></i>
          Contact Entrepreneur
        </button>
        <?php elseif ($user): ?>
        <a href="<?= APP_URL ?>/upgrade" class="stitch-btn stitch-btn-primary" style="text-decoration:none;">
          <i class="fas fa-crown" style="font-size:16px;"></i>
          Unlock Contact — Go Premium
        </a>
        <?php else: ?>
        <button class="stitch-btn stitch-btn-primary" onclick="document.getElementById('interest-modal').classList.add('open')">
          <i class="fas fa-envelope" style="font-size:16px;"></i>
          Contact Entrepreneur
        </button>
        <?php endif; ?>
        <button class="stitch-btn stitch-btn-secondary" onclick="navigator.share? navigator.share({title:'<?= e($pitch['tagline']) ?>',url:window.location.href}) : navigator.clipboard.writeText(window.location.href)">
          <i class="fas fa-external-link-alt" style="font-size:13px;"></i>
          Share
        </button>
      </div>
    </div>

    <div class="stitch-gallery" id="pitchGallery">
      <?php if ($firstImg): ?>
      <div class="stitch-gallery-main-wrap">
        <img src="<?= upload_url($firstImg['file_url']) ?>" alt="<?= e($pitch['tagline']) ?>" class="stitch-gallery-main">
      </div>
      <?php elseif ($pitch['profile_photo']): ?>
      <div class="stitch-gallery-main-wrap">
        <img src="<?= upload_url($pitch['profile_photo']) ?>" alt="<?= e($pitch['entrepreneur_name']) ?>" class="stitch-gallery-main" style="border-radius:50%;width:200px;height:200px;object-fit:cover;">
      </div>
      <?php else: ?>
      <div class="stitch-gallery-fallback">
        <div style="width:120px;height:120px;border-radius:50%;background:var(--color-bg-soft);display:flex;align-items:center;justify-content:center;font-size:48px;font-weight:700;color:var(--dash-ink-soft);"><?= $initials ?></div>
      </div>
      <?php endif; ?>
      <?php if (count($images) > 1): ?>
      <div class="stitch-gallery-thumbs" id="galleryThumbs">
        <?php foreach ($images as $i => $img): ?>
        <img src="<?= upload_url($img['file_url']) ?>" alt="" class="stitch-gallery-thumb <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>" onclick="document.querySelector('#pitchGallery .stitch-gallery-main').src=this.src;document.querySelectorAll('#galleryThumbs .stitch-gallery-thumb').forEach(function(t){t.classList.remove('active')});this.classList.add('active')" loading="lazy">
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<div class="stitch-layout">

  <div class="stitch-content">

    <?php if ($pitch['problem_statement']): ?>
    <section class="stitch-section">
      <h2 class="stitch-section-title">The Problem</h2>
      <div class="stitch-overview-text"><?= nl2br(e($pitch['problem_statement'])) ?></div>
    </section>
    <?php endif; ?>

    <?php if ($pitch['solution']): ?>
    <section class="stitch-section">
      <h2 class="stitch-section-title">Our Solution</h2>
      <div class="stitch-overview-text"><?= nl2br(e($pitch['solution'])) ?></div>
    </section>
    <?php endif; ?>

    <?php if ($pitch['market_size']): ?>
    <section class="stitch-section">
      <h2 class="stitch-section-title">Market Opportunity</h2>
      <div class="stitch-overview-text"><?= nl2br(e($pitch['market_size'])) ?></div>
    </section>
    <?php endif; ?>

    <?php if ($pitch['business_model']): ?>
    <section class="stitch-section">
      <h2 class="stitch-section-title">Business Model</h2>
      <div class="stitch-overview-text"><?= nl2br(e($pitch['business_model'])) ?></div>
    </section>
    <?php endif; ?>

    <?php if ($pitch['pitch_video_url']):
        $videoUrl = $pitch['pitch_video_url'];
        $embedHtml = '';
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $videoUrl, $m)) {
            $embedHtml = '<iframe width="100%" height="390" src="https://www.youtube-nocookie.com/embed/' . e($m[1]) . '?rel=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="border-radius:var(--radius-md);"></iframe>';
        } elseif (preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $m)) {
            $embedHtml = '<div style="padding:56.25% 0 0 0;position:relative;"><iframe src="https://player.vimeo.com/video/' . e($m[1]) . '?badge=0" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen style="position:absolute;top:0;left:0;width:100%;height:100%;border-radius:var(--radius-md);"></iframe></div>';
        }
        ?>
    <section class="stitch-section">
      <h2 class="stitch-section-title">Pitch Video</h2>
      <?php if ($embedHtml): ?>
      <div style="max-width:640px;"><?= $embedHtml ?></div>
      <?php else: ?>
      <p><a href="<?= e($videoUrl) ?>" target="_blank" rel="noopener"><?= e($videoUrl) ?></a></p>
      <?php endif; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($teamMembers)): ?>
    <section class="stitch-section">
      <h2 class="stitch-section-title">Team</h2>
      <div style="display:flex;flex-direction:column;gap:0.75rem;">
        <?php foreach ($teamMembers as $member): ?>
        <div style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem;background:var(--surface-container-high);border-radius:0.75rem;">
          <div style="width:44px;height:44px;border-radius:50%;background:var(--color-bg-soft);display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:var(--dash-ink-soft);flex-shrink:0;"><?= e(strtoupper(mb_substr($member['name'], 0, 2))) ?></div>
          <div>
            <div style="font-weight:600;font-size:0.9rem;"><?= e($member['name']) ?></div>
            <?php if ($member['role']): ?>
            <div style="font-size:0.8rem;color:var(--color-text-muted);"><?= e($member['role']) ?></div>
            <?php endif; ?>
          </div>
          <?php if ($member['linkedin_url']): ?>
          <a href="<?= e($member['linkedin_url']) ?>" target="_blank" rel="noopener" style="margin-left:auto;font-size:0.8rem;color:var(--color-secondary);text-decoration:none;">LinkedIn</a>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($hasMatch && $pitch['entrepreneur_name']): ?>
    <section class="stitch-section" style="background:rgba(30,122,77,0.04);border-radius:var(--radius-lg);padding:1.5rem;">
      <h2 class="stitch-section-title" style="margin:0 0 0.75rem;">Contact Information</h2>
      <div style="display:flex;flex-direction:column;gap:0.5rem;">
        <div><span style="font-weight:600;">Name:</span> <?= e($pitch['entrepreneur_name']) ?></div>
        <?php if ($pitch['company_name']): ?>
        <div><span style="font-weight:600;">Company:</span> <?= e($pitch['company_name']) ?></div>
        <?php endif; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php
    $relS = $db->prepare('SELECT p.id, p.tagline, p.funding_amount, p.equity_offered, p.sector_id, s.name AS sector_name FROM pitches p LEFT JOIN sectors s ON s.id = p.sector_id WHERE p.sector_id = ? AND p.id != ? AND p.is_published = 1 AND p.is_hidden = 0 ORDER BY RAND() LIMIT 3');
    $relS->execute([$pitch['sector_id'], $pitchId]);
    $related = $relS->fetchAll();
    if (!empty($related)): ?>
    <section class="stitch-section">
      <div class="stitch-section-header-row">
        <h2 class="stitch-section-title" style="margin:0">Related Pitches</h2>
        <a href="<?= APP_URL ?>/browse/entrepreneurs" class="stitch-view-all">View all</a>
      </div>
      <div class="stitch-related-grid">
        <?php foreach ($related as $r): ?>
        <div class="stitch-related-card" onclick="location.href='<?= APP_URL ?>/entrepreneur/pitch.php?id=<?= (int)$r['id'] ?>'" tabindex="0" role="link">
          <div class="stitch-related-img">
            <div class="fallback"><i class="fas fa-image" style="font-size:40px;opacity:0.4;"></i></div>
          </div>
          <div class="stitch-related-body">
            <h4><?= e($r['tagline']) ?></h4>
            <div class="loc"><?= e($r['sector_name'] ?? '') ?></div>
            <div class="stats">
              <div><span class="l">Funding Ask</span><span class="v"><?= money($r['funding_amount'] ?? 0) ?></span></div>
              <div><span class="l">Equity</span><span class="v"><?= e($r['equity_offered'] ?? '—') ?>%</span></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

  </div>

  <aside class="stitch-sidebar">
    <div class="stitch-sidebar-sticky">

      <div class="stitch-sidebar-card">
        <?php if ($pitch['funding_amount']): ?>
        <div class="stitch-sidebar-price">
          <span class="label">Funding Ask</span>
          <span class="value"><?= money($pitch['funding_amount']) ?></span>
        </div>
        <?php if ($pitch['equity_offered']): ?>
        <div style="margin-top:6px;font-size:0.85rem;color:var(--color-text-muted);">for <?= e($pitch['equity_offered']) ?>% equity</div>
        <?php endif; ?>
        <?php else: ?>
        <div class="stitch-sidebar-price">
          <span class="label">Funding Ask</span>
          <span class="value">Negotiable</span>
        </div>
        <?php endif; ?>

        <?php if ($user && (int)$user['id'] === $ownerUserId): ?>
        <a href="<?= APP_URL ?>/entrepreneur/pitch-edit.php?id=<?= (int)$pitchId ?>" class="stitch-sidebar-cta">Edit Pitch</a>
        <?php elseif ($viewerIsPremium): ?>
        <button class="stitch-sidebar-cta" onclick="document.getElementById('interest-modal').classList.add('open')">Contact Entrepreneur</button>
        <a href="<?= APP_URL ?>/connections" class="stitch-sidebar-cta" style="display:block;text-align:center;margin-top:6px;">View My Connections</a>
        <?php elseif ($user): ?>
        <a href="<?= APP_URL ?>/upgrade" class="stitch-sidebar-cta" style="display:block;text-align:center;">Unlock Contact — Go Premium</a>
        <?php else: ?>
        <a href="<?= APP_URL ?>/upgrade" class="stitch-sidebar-cta" style="display:block;text-align:center;">Unlock Contact — Go Premium</a>
        <?php endif; ?>

        <?php if (!$userId): ?>
        <div class="stitch-sidebar-auth">
          <button class="stitch-auth-btn" onclick="location.href='<?= APP_URL ?>/login'">
            <i class="fab fa-google" style="font-size:22px;"></i>
            Continue with Google
          </button>
          <button class="stitch-auth-btn" onclick="location.href='<?= APP_URL ?>/login'">
            <i class="fab fa-linkedin-in" style="font-size:15px;"></i>
            Continue with LinkedIn
          </button>
        </div>
        <?php endif; ?>

        <div class="stitch-sidebar-meta">
          <div><span class="l">Views</span><span class="v"><?= number_format((int)$pitch['views']) ?></span></div>
          <div><span class="l">Listed</span><span class="v"><?= date('M d, Y', strtotime($pitch['created_at'])) ?></span></div>
          <?php if ($pitch['stage']): ?>
          <div><span class="l">Stage</span><span class="v"><?= e(ucfirst($pitch['stage'])) ?></span></div>
          <?php endif; ?>
          <?php if ($pitch['sector_name']): ?>
          <div><span class="l">Sector</span><span class="v"><?= e($pitch['sector_name']) ?></span></div>
          <?php endif; ?>
        </div>

        <?php if ($pitch['pitch_deck']): ?>
          <?php if ($viewerIsPremium): ?>
          <a href="<?= APP_URL ?>/public/uploads/pitch-decks/<?= e($pitch['pitch_deck']) ?>" class="stitch-sidebar-cta" style="display:block;text-align:center;margin-top:0.75rem;border-color:var(--color-secondary);color:var(--color-secondary);" target="_blank">
            <i class="fas fa-file-pdf" style="font-size:13px;"></i> Download Pitch Deck
          </a>
          <?php else: ?>
          <a href="<?= APP_URL ?>/upgrade" class="stitch-sidebar-cta" style="display:block;text-align:center;margin-top:0.75rem;">Upgrade to Download Pitch Deck</a>
          <?php endif; ?>
        <?php endif; ?>

        <div class="stitch-sidebar-disclaimer">
          <p>Asaan Capital Ltd is a discovery platform. Conduct your own due diligence.</p>
        </div>
      </div>

      <div class="stitch-sidebar-card">
        <div class="stitch-card-header">
          <i class="fas fa-user-circle" style="font-size:15px;"></i>
          About the Entrepreneur
        </div>
        <div class="stitch-card-body" style="display:flex;gap:12px;align-items:center;">
          <?php if ($pitch['profile_photo']): ?>
          <img src="<?= upload_url($pitch['profile_photo']) ?>" alt="" style="width:44px;height:44px;border-radius:50%;object-fit:cover;">
          <?php else: ?>
          <div style="width:44px;height:44px;border-radius:50%;background:var(--color-bg-soft);display:flex;align-items:center;justify-content:center;font-size:18px;color:var(--color-text-muted);"><i class="fas fa-user"></i></div>
          <?php endif; ?>
          <div>
            <strong style="display:block;font-size:14px;"><?= e($pitch['entrepreneur_name']) ?></strong>
            <?php if ($pitch['company_name']): ?>
            <span style="font-size:12px;color:var(--color-text-muted);"><?= e($pitch['company_name']) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="stitch-sidebar-card">
        <div class="stitch-card-header">
          <i class="fas fa-clock" style="font-size:15px;"></i>
          Recent Activity
        </div>
        <div class="stitch-card-body activity">
          <div class="stitch-activity-item">
            <span class="dot"></span>
            <div><span class="t">Listed on marketplace</span><span class="ts"><?= date_human($pitch['created_at']) ?></span></div>
          </div>
          <?php if ((int)$pitch['views'] > 0): ?>
          <div class="stitch-activity-item">
            <span class="dot"></span>
            <div><span class="t"><?= number_format((int)$pitch['views']) ?> profile views</span><span class="ts">Total views</span></div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="stitch-sidebar-card security">
        <div class="stitch-card-header">
          <i class="fas fa-lock" style="font-size:15px;"></i>
          Secure &amp; Confidential
        </div>
        <div class="stitch-card-body">
          <ul>
            <li><i class="fas fa-check" style="font-size:13px;"></i> Secure communication</li>
            <li><i class="fas fa-check" style="font-size:13px;"></i> Confidential inquiries</li>
            <li><i class="fas fa-check" style="font-size:13px;"></i> Verified entrepreneurs</li>
          </ul>
        </div>
      </div>

    </div>
  </aside>

</div>

<div class="stitch-mobile-cta" id="mobileCta">
  <?php if ($viewerIsPremium): ?>
  <button onclick="document.getElementById('interest-modal').classList.add('open')">Contact Entrepreneur</button>
  <?php else: ?>
  <a href="<?= APP_URL ?>/upgrade">Unlock Contact</a>
  <?php endif; ?>
</div>

<footer class="stitch-footer">
  <div class="stitch-footer-inner">
    <div class="stitch-footer-brand">
      <strong><?= APP_NAME ?></strong>
      <p>The leading institutional-grade business marketplace in South Asia, connecting serious entrepreneurs with global capital.</p>
    </div>
    <div class="stitch-footer-links">
      <div>
        <span class="h">Company</span>
        <a href="#">About Us</a>
        <a href="<?= APP_URL ?>/contact">Contact</a>
        <a href="#">FAQ</a>
      </div>
      <div>
        <span class="h">Resources</span>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Cookie Policy</a>
      </div>
      <div>
        <span class="h">Connect</span>
        <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
      </div>
    </div>
  </div>
</footer>

</div>

<div id="interest-modal" class="stitch-overlay" onclick="if(event.target===this)this.classList.remove('open')" role="dialog" aria-modal="true">
  <div class="stitch-overlay-content" onclick="event.stopImmediatePropagation()">
    <div class="stitch-overlay-header">
      <h3>Contact Entrepreneur</h3>
      <button class="stitch-overlay-close" onclick="document.getElementById('interest-modal').classList.remove('open')">&times;</button>
    </div>
    <?php if ($userId): ?>
    <form method="POST" action="<?= APP_URL ?>/connections/send-interest">
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="pitch_id" value="<?= $pitchId ?>">
      <input type="hidden" name="receiver_id" value="<?= $ownerUserId ?>">
      <div class="stitch-field">
        <label for="interest-message">Message</label>
        <textarea id="interest-message" name="message" rows="4" placeholder="Introduce yourself and explain your interest in this pitch..."></textarea>
      </div>
      <button type="submit" class="stitch-btn-primary" style="width:100%">Send Request</button>
    </form>
    <?php else: ?>
    <p style="margin-bottom:16px;color:var(--color-text-muted);font-size:0.875rem;">Please sign in to contact the entrepreneur.</p>
    <a href="<?= APP_URL ?>/login" class="stitch-btn-primary" style="display:block;text-align:center;">Sign In</a>
    <?php endif; ?>
  </div>
</div>

<div id="report-modal" class="stitch-overlay" onclick="if(event.target===this)this.classList.remove('open')" role="dialog" aria-modal="true">
  <div class="stitch-overlay-content" onclick="event.stopImmediatePropagation()">
    <div class="stitch-overlay-header">
      <h3>Report Pitch</h3>
      <button class="stitch-overlay-close" onclick="document.getElementById('report-modal').classList.remove('open')">&times;</button>
    </div>
    <form method="POST" onsubmit="event.preventDefault();const f=this;fetch('/api/report.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams(new FormData(f))}).then(r=>r.json()).then(d=>{if(d.ok){alert('Report submitted.');f.closest('.stitch-overlay').classList.remove('open')}}).catch(()=>{alert('Error')})">
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="target_type" value="pitch">
      <input type="hidden" name="target_id" value="<?= $pitchId ?>">
      <div class="stitch-field">
        <label>Reason</label>
        <select name="reason" required>
          <option value="">Select...</option>
          <option value="inaccurate_info">Inaccurate information</option>
          <option value="suspicious">Suspicious</option>
          <option value="duplicate">Duplicate</option>
          <option value="inappropriate">Inappropriate</option>
        </select>
      </div>
      <div class="stitch-field">
        <label>Details</label>
        <textarea name="details" rows="3"></textarea>
      </div>
      <button type="submit" class="stitch-btn-primary" style="width:100%">Submit</button>
    </form>
  </div>
</div>

<?php $hidePublicFooter = true; require __DIR__ . '/../includes/footer.php'; ?>
