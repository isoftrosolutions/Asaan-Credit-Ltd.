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

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Entrepreneurs","item":"'.APP_URL.'/discover/entrepreneurs.php"},
    {"@type": "ListItem","position":3,"name":"'.e($pitch['tagline']).'","item":"'.APP_URL.'/entrepreneur/pitch.php?id='.$pitchId.'"}
  ]
}</script>';
require __DIR__ . '/../includes/layout-public.php';
?>
<?= $breadcrumbSchema ?>
<style>
.pitch-detail-sidebar {
  background: rgba(255,255,255,0.06);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255,255,255,0.12);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  box-shadow: 0 8px 32px rgba(0,0,0,0.08);
}

.detail-sidebar-inner > .btn-contact {
  width: 100%;
  padding: 14px 24px;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-vivid) 100%);
  color: #fff;
  border: none;
  border-radius: var(--radius-md);
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  transition: transform 160ms var(--ease-out), box-shadow 160ms var(--ease-out);
  margin: 1rem 0;
}
.detail-sidebar-inner > .btn-contact:active {
  transform: scale(0.97);
}
.detail-sidebar-inner > .btn-contact:hover {
  box-shadow: 0 8px 24px rgba(107,29,34,0.3);
}

.sidebar-login-options {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 1rem;
}
.sidebar-login-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: var(--radius-md);
  color: var(--dash-ink);
  font-size: 0.85rem;
  font-weight: 500;
  text-decoration: none;
  transition: background 200ms ease, border-color 200ms ease;
}
.sidebar-login-btn:hover {
  background: rgba(255,255,255,0.08);
  border-color: rgba(255,255,255,0.16);
}

.sidebar-social-proof {
  text-align: center;
  padding: 0.75rem;
  background: rgba(152,32,42,0.06);
  border-radius: var(--radius-md);
  font-size: 0.8rem;
  color: var(--dash-ink-soft);
  margin: 1rem 0;
}

.sidebar-price {
  font-size: 1.75rem;
  font-weight: 800;
  color: var(--color-primary-vivid);
  line-height: 1.2;
}
.sidebar-label {
  font-size: 0.85rem;
  color: var(--dash-ink-soft);
  margin-bottom: 0.5rem;
}

/* Recent Activity Feed */
.activity-feed { max-width:960px; margin:0 auto; padding:0 1rem; }
.activity-feed h3 { font-size:1.1rem; font-weight:700; margin-bottom:1rem; color:var(--dash-ink); }
.activity-item { display:flex; align-items:flex-start; gap:0.75rem; padding:0.75rem 0; border-bottom:1px solid var(--dash-border); }
.activity-item:last-child { border-bottom:none; }
.activity-dot { width:8px; height:8px; border-radius:50%; background:var(--color-primary-vivid); margin-top:6px; flex-shrink:0; }
.activity-text { font-size:0.85rem; color:var(--dash-ink); line-height:1.5; }
.activity-time { font-size:0.75rem; color:var(--dash-ink-soft); margin-top:2px; }

/* Responsive */
@media (max-width: 900px) {
  .pitch-detail-grid { grid-template-columns:1fr; }
  .detail-sidebar-card { position:static; }
  .pitch-detail-grid > div:first-child { order:1; }
  .pitch-detail-grid > div:last-child { order:0; }
}
@media (max-width: 640px) {
  .pitch-detail-grid > div:first-child h3 { font-size:1.1rem; }
}
</style>
<div class="breadcrumbs container">
    <a href="<?= APP_URL ?>/">Home</a> <span>/</span>
    <a href="<?= APP_URL ?>/browse/entrepreneurs">Entrepreneurs</a> <span>/</span>
    <span><?= e($pitch['tagline']) ?></span>
</div>

<div class="container" style="max-width:960px; padding:2.5rem 0 4rem;">
    <div style="display:flex; gap:1rem; align-items:center;">
        <div class="avatar" style="width:72px; height:72px; font-size:1.6rem;"><?= $initials ?></div>
        <div>
            <h1 style="margin-bottom:0.1rem;"><?= e($pitch['entrepreneur_name']) ?></h1>
            <div><?= e($pitch['province'] ?? '') ?><?= $pitch['province'] && $pitch['district'] ? ', ' : '' ?><?= e($pitch['district'] ?? '') ?> <?php if ($pitch['verification_status'] === 'verified'): ?><span class="verified-badge">Verified</span><?php endif; ?></div>
        </div>
    </div>
    <?php if ($user && (int)$user['id'] === $ownerUserId): ?>
    <div style="margin-bottom:1rem;">
      <a href="<?= APP_URL ?>/entrepreneur/pitch-edit.php?id=<?= (int)$pitchId ?>" class="btn btn-primary btn-sm">✏ Edit Pitch</a>
    </div>
    <?php endif; ?>

    <div style="margin:1.5rem 0 2rem;">
        <?php if ($pitch['sector_name']): ?>
        <div class="tag tag-accent"><?= e($pitch['sector_name']) ?> &middot; <?= e(ucfirst($pitch['stage'] ?? '')) ?></div>
        <?php endif; ?>
        <div style="margin-top:1rem; font-size:1.15rem; max-width:680px;"><?= e($pitch['short_summary'] ?: $pitch['tagline']) ?></div>
    </div>

    <div class="pitch-detail-grid">
        <div>
            <?php if ($pitch['problem_statement']): ?>
            <h3>The Problem</h3>
            <p><?= nl2br(e($pitch['problem_statement'])) ?></p>
            <?php endif; ?>

            <?php if ($pitch['solution']): ?>
            <h3>Our Solution</h3>
            <p><?= nl2br(e($pitch['solution'])) ?></p>
            <?php endif; ?>

            <?php if ($pitch['market_size']): ?>
            <h3>Market Opportunity</h3>
            <p><?= nl2br(e($pitch['market_size'])) ?></p>
            <?php endif; ?>

            <?php if ($pitch['business_model']): ?>
            <h3>Business Model</h3>
            <p><?= nl2br(e($pitch['business_model'])) ?></p>
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
            <h3>Pitch Video</h3>
            <?php if ($embedHtml): ?>
            <div style="max-width:640px;margin-bottom:1.5rem;"><?= $embedHtml ?></div>
            <?php else: ?>
            <p><a href="<?= e($videoUrl) ?>" target="_blank" rel="noopener"><?= e($videoUrl) ?></a></p>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($teamMembers)): ?>
            <h3>Team</h3>
            <div style="display:flex;flex-direction:column;gap:0.75rem;margin-bottom:1.5rem;">
                <?php foreach ($teamMembers as $member): ?>
                <div style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem;background:var(--surface-container-high);border-radius:0.75rem;">
                    <div class="avatar avatar-sm"><?= e(strtoupper(mb_substr($member['name'], 0, 2))) ?></div>
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
            <?php endif; ?>

            <?php if ($hasMatch && $pitch['entrepreneur_name']): ?>
            <div class="card" style="margin-top:1.5rem;background:rgba(30,122,77,0.06);">
                <h3 style="margin:0 0 0.5rem;">Contact Information</h3>
                <p><strong>Name:</strong> <?= e($pitch['entrepreneur_name']) ?></p>
                <?php if ($pitch['company_name']): ?>
                <p><strong>Company:</strong> <?= e($pitch['company_name']) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="detail-sidebar-card pitch-detail-sidebar">
            <div class="detail-sidebar-inner">
                <?php if ($pitch['funding_amount']): ?>
                <div class="sidebar-price"><?= money($pitch['funding_amount']) ?></div>
                <div class="sidebar-label"><?= $pitch['equity_offered'] ? 'for ' . e($pitch['equity_offered']) . '% equity' : 'Funding Ask' ?></div>
                <?php endif; ?>

                <?php if ($viewerIsPremium): ?>
                <button class="btn-contact" onclick="document.getElementById('interest-modal').classList.add('open')">Contact Entrepreneur</button>
                <?php elseif ($user): ?>
                <a href="<?= APP_URL ?>/upgrade" class="btn-contact" style="display:block;text-align:center;text-decoration:none;">Unlock Contact — Go Premium</a>
                <?php else: ?>
                <button class="btn-contact" onclick="document.getElementById('interest-modal').classList.add('open')">Contact Entrepreneur</button>
                <?php endif; ?>

                <div class="sidebar-social-proof">
                    <strong><?= number_format(rand(5, 30)) ?> investors</strong> viewed this pitch this month
                </div>

                <div class="sidebar-login-options">
                    <a href="<?= APP_URL ?>/login" class="sidebar-login-btn">
                        <i class="fab fa-google" style="font-size:18px;"></i>
                        Sign in with Google
                    </a>
                    <a href="<?= APP_URL ?>/login" class="sidebar-login-btn">
                        <i class="fab fa-linkedin-in" style="font-size:18px;"></i>
                        Sign in with LinkedIn
                    </a>
                    <a href="<?= APP_URL ?>/login" class="sidebar-login-btn">
                        <i class="fab fa-facebook-f" style="font-size:18px;"></i>
                        Sign in with Facebook
                    </a>
                    <a href="https://wa.me/" class="sidebar-login-btn" target="_blank" rel="noopener">
                        <i class="fab fa-whatsapp" style="font-size:18px;"></i>
                        Contact via WhatsApp
                    </a>
                </div>

                <?php if ($pitch['pitch_deck']): ?>
                  <?php if ($viewerIsPremium): ?>
                  <a href="<?= APP_URL ?>/public/uploads/pitch-decks/<?= e($pitch['pitch_deck']) ?>" class="btn btn-secondary" style="width:100%;display:block;text-align:center;text-decoration:none;margin-top:1rem;" target="_blank">Download Pitch Deck (PDF)</a>
                  <?php else: ?>
                  <a href="<?= APP_URL ?>/upgrade" class="btn btn-secondary" style="width:100%;display:block;text-align:center;text-decoration:none;margin-top:1rem;">Upgrade to Download Pitch Deck</a>
                  <?php endif; ?>
                <?php endif; ?>

                <div style="margin-top:1rem;padding-top:0.75rem;border-top:1px solid var(--color-border);font-size:0.75rem;color:var(--color-text-muted);">
                    <div style="display:flex;align-items:center;gap:4px;">
                        <i class="fas fa-eye" style="font-size:14px;"></i>
                        <?= (int)$pitch['views'] ?> views
                    </div>
                </div>

                <div style="margin-top:0.75rem;padding:0.75rem;background:rgba(199,122,18,0.1);border-radius:8px;font-size:0.75rem;color:var(--color-warning);">
                    <strong>Disclaimer:</strong> Asaan Capital Ltd is a discovery platform. Conduct your own due diligence.
                </div>

            </div>
        </div>
    </div>

    <!-- Recent Activity Feed -->
    <div class="activity-feed" style="max-width:960px;margin:0 auto 2rem;padding:0 1rem;">
        <h3>Recent Activity</h3>
        <?php
        $activities = [
            ['role' => 'Angel Investor', 'city' => 'Kathmandu', 'days' => 1],
            ['role' => 'VC Firm', 'city' => 'Pokhara', 'days' => 4],
            ['role' => 'Angel Investor', 'city' => 'Lalitpur', 'days' => 10],
        ];
        foreach ($activities as $act): ?>
        <div class="activity-item">
            <div class="activity-dot"></div>
            <div>
                <div class="activity-text"><strong><?= e($act['role']) ?></strong>, <?= e($act['city']) ?> connected with this pitch <strong><?= $act['days'] ?> days ago</strong></div>
                <div class="activity-time"><?= $act['days'] === 1 ? '1 day ago' : $act['days'] . ' days ago' ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>


<div id="interest-modal" class="modal" onclick="if (event.target === this) this.classList.remove('open')">
    <div class="modal-content" onclick="event.stopImmediatePropagation()">
        <div class="modal-header">
            <h3>Express Interest</h3>
            <button class="close-btn" onclick="document.getElementById('interest-modal').classList.remove('open')">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/connections/send-interest">
            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="pitch_id" value="<?= $pitchId ?>">
            <input type="hidden" name="receiver_id" value="<?= $ownerUserId ?>">
            <div class="input-group">
                <label>Your Message</label>
                <textarea name="message" class="input" style="width:100%;height:110px;margin-bottom:1rem;" placeholder="Short note to the entrepreneur (optional)"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Send Request</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
