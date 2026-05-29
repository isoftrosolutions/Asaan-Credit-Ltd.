<?php
require __DIR__ . '/../config/bootstrap.php';

$pitchId = (int)($_GET['id'] ?? 0);
if ($pitchId < 1) {
    http_response_code(404);
    require __DIR__ . '/../pages/404.php';
    exit;
}

$db = db();

$stmt = $db->prepare('SELECT p.*, s.name AS sector_name, u.name AS entrepreneur_name, u.company_name, u.province, u.district, u.verification_status, u.id AS owner_id, u.profile_photo FROM pitches p LEFT JOIN sectors s ON s.id = p.sector_id JOIN users u ON u.id = p.user_id WHERE p.id = ? AND p.is_published = 1');
$stmt->execute([$pitchId]);
$pitch = $stmt->fetch();

if (!$pitch) {
    http_response_code(404);
    require __DIR__ . '/../pages/404.php';
    exit;
}

$db->prepare('UPDATE pitches SET views = views + 1 WHERE id = ?')->execute([$pitchId]);

$user = current_user();
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

$initials = e(strtoupper(mb_substr($pitch['entrepreneur_name'] ?? '?', 0, 2)));

$pageTitle = e($pitch['tagline']) . ' — ' . APP_NAME;
require __DIR__ . '/../includes/layout-public.php';
?>
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

    <div style="margin:1.5rem 0 2rem;">
        <?php if ($pitch['sector_name']): ?>
        <div class="tag tag-accent"><?= e($pitch['sector_name']) ?> &middot; <?= e(ucfirst($pitch['stage'] ?? '')) ?></div>
        <?php endif; ?>
        <div style="margin-top:1rem; font-size:1.15rem; max-width:680px;"><?= e($pitch['short_summary'] ?: $pitch['tagline']) ?></div>
    </div>

    <div class="pitch-detail-grid" style="display:grid; grid-template-columns:2fr 1fr; gap:2rem;">
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

            <?php if ($pitch['pitch_video_url']): ?>
            <h3>Pitch Video</h3>
            <p><a href="<?= e($pitch['pitch_video_url']) ?>" target="_blank" rel="noopener"><?= e($pitch['pitch_video_url']) ?></a></p>
            <?php endif; ?>

            <?php if ($hasMatch && $pitch['entrepreneur_name']): ?>
            <div class="card" style="margin-top:1.5rem;background:#f0fdf4;">
                <h3 style="margin:0 0 0.5rem;">Contact Information</h3>
                <p><strong>Name:</strong> <?= e($pitch['entrepreneur_name']) ?></p>
                <?php if ($pitch['company_name']): ?>
                <p><strong>Company:</strong> <?= e($pitch['company_name']) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="card" style="height:fit-content;">
            <?php if ($pitch['funding_amount']): ?>
            <div style="margin-bottom:1rem;">
                <div class="label-md">FUNDING ASK</div>
                <div style="font-size:2.25rem; font-weight:700; color:var(--ink);"><?= money($pitch['funding_amount']) ?></div>
                <?php if ($pitch['equity_offered']): ?>
                <div style="font-size:0.9rem; color:#666;">for <?= e($pitch['equity_offered']) ?>% equity</div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($pitch['valuation']): ?>
            <div class="info-row"><span class="label">Valuation</span><span class="value"><?= money($pitch['valuation']) ?></span></div>
            <?php endif; ?>
            <?php if ($pitch['stage']): ?>
            <div class="info-row"><span class="label">Stage</span><span class="value"><?= e(ucfirst($pitch['stage'])) ?></span></div>
            <?php endif; ?>
            <?php if ($pitch['sector_name']): ?>
            <div class="info-row"><span class="label">Sector</span><span class="value"><?= e($pitch['sector_name']) ?></span></div>
            <?php endif; ?>

            <?php if ($user && (int)$user['id'] !== $ownerUserId && !$hasMatch): ?>
            <button onclick="document.getElementById('interest-modal').classList.add('open')" class="btn btn-accent" style="width:100%; margin-bottom:0.75rem;">Express Interest</button>
            <?php endif; ?>

            <?php if ($pitch['pitch_deck']): ?>
            <a href="<?= APP_URL ?>/public/uploads/pitch-decks/<?= e($pitch['pitch_deck']) ?>" class="btn btn-secondary" style="width:100%; display:block; text-align:center; text-decoration:none; margin-bottom:0.75rem;" target="_blank">Download Pitch Deck (PDF)</a>
            <?php endif; ?>

            <div style="margin-top:1.25rem; font-size:0.8rem; color:#666;">
                <?= (int)$pitch['views'] ?> views
            </div>
        </div>
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
