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

$hasMatch = false;
$ownerUserId = (int)$pitch['owner_id'];

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

        <div class="detail-sidebar-card">
            <div class="detail-sidebar-inner">
                <?php if ($pitch['funding_amount']): ?>
                <div class="sidebar-price"><?= money($pitch['funding_amount']) ?></div>
                <div class="sidebar-label"><?= $pitch['equity_offered'] ? 'for ' . e($pitch['equity_offered']) . '% equity' : 'Funding Ask' ?></div>
                <?php endif; ?>

                <button class="btn-contact" onclick="document.getElementById('interest-modal').classList.add('open')">Contact Entrepreneur</button>

                <div class="sidebar-social-proof">
                    <strong><?= number_format(rand(5, 30)) ?> investors</strong> viewed this pitch this month
                </div>

                <div class="sidebar-login-options">
                    <a href="<?= APP_URL ?>/login" class="sidebar-login-btn">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        Sign in with Google
                    </a>
                    <a href="<?= APP_URL ?>/login" class="sidebar-login-btn">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.5 2h-17A1.5 1.5 0 002 3.5v17A1.5 1.5 0 003.5 22h17a1.5 1.5 0 001.5-1.5v-17A1.5 1.5 0 0020.5 2zM8 19H5v-9h3zM6.5 8.25A1.75 1.75 0 118.3 6.5a1.78 1.78 0 01-1.8 1.75zM19 19h-3v-4.74c0-1.42-.6-1.93-1.38-1.93A1.74 1.74 0 0013 14.19a.66.66 0 000 .14V19h-3v-9h2.9v1.3a3.11 3.11 0 012.7-1.4c1.55 0 3.36.86 3.36 3.67z"/></svg>
                        Sign in with LinkedIn
                    </a>
                    <a href="<?= APP_URL ?>/login" class="sidebar-login-btn">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 011.141.195v3.325a8.623 8.623 0 00-.653-.036 26.805 26.805 0 00-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 00-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647z"/></svg>
                        Sign in with Facebook
                    </a>
                    <a href="https://wa.me/" class="sidebar-login-btn" target="_blank" rel="noopener">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.48 2 2 6.48 2 12c0 2.42.85 4.66 2.28 6.42L3 22l3.61-1.17A9.93 9.93 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm0 18c-1.97 0-3.81-.6-5.34-1.63l-.37-.22-2.22.72.73-2.16-.24-.39A7.94 7.94 0 014 12c0-4.41 3.59-8 8-8s8 3.59 8 8-3.59 8-8 8z"/></svg>
                        Contact via WhatsApp
                    </a>
                </div>

                <?php if ($pitch['pitch_deck']): ?>
                <a href="<?= APP_URL ?>/public/uploads/pitch-decks/<?= e($pitch['pitch_deck']) ?>" class="btn btn-secondary" style="width:100%;display:block;text-align:center;text-decoration:none;margin-top:1rem;" target="_blank">Download Pitch Deck (PDF)</a>
                <?php endif; ?>

                <div style="margin-top:1rem;padding-top:0.75rem;border-top:1px solid var(--color-border);font-size:0.75rem;color:var(--color-text-muted);">
                    <div style="display:flex;align-items:center;gap:4px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <?= (int)$pitch['views'] ?> views
                    </div>
                </div>

                <div style="margin-top:0.75rem;padding:0.75rem;background:rgba(199,122,18,0.1);border-radius:8px;font-size:0.75rem;color:var(--color-warning);">
                    <strong>Disclaimer:</strong> Asaan Capital Ltd is a discovery platform. Conduct your own due diligence.
                </div>

                <button class="btn btn-ghost btn-sm" style="width:100%;margin-top:0.75rem;font-size:0.75rem;color:var(--color-text-muted);cursor:pointer;border:none;background:none;padding:8px;font-family:inherit;" onclick="document.getElementById('report-modal').classList.add('open')">Report listing</button>
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

<div id="report-modal" class="modal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-content" onclick="event.stopImmediatePropagation()">
        <div class="modal-header">
            <h3>Report Listing</h3>
            <button class="close-btn" onclick="document.getElementById('report-modal').classList.remove('open')">&times;</button>
        </div>
        <form method="POST" action="/connections/send-interest" onsubmit="event.preventDefault();const f=this;fetch('/api/report.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams(new FormData(f))}).then(r=>r.json()).then(d=>{if(d.ok){alert('Report submitted. Thank you.');f.closest('.modal').classList.remove('open')}else{alert('Error submitting report.')}}).catch(()=>{alert('Error submitting report.')})">
            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="target_type" value="pitch">
            <input type="hidden" name="target_id" value="<?= $pitchId ?>">
            <div class="input-group">
                <label>Reason</label>
                <select name="reason" class="input" required>
                    <option value="">Select a reason...</option>
                    <option value="inaccurate_info">Inaccurate information</option>
                    <option value="suspicious">Suspicious or fraudulent</option>
                    <option value="duplicate">Duplicate listing</option>
                    <option value="inappropriate">Inappropriate content</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="input-group">
                <label>Details (optional)</label>
                <textarea name="details" class="input" rows="3" placeholder="Provide additional context..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Submit Report</button>
        </form>
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
