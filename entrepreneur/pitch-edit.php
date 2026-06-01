<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role(ROLE_ENTREPRENEUR);

$user = current_user();
$userId = (int)$user['id'];
$pitchId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($pitchId < 1) {
    $stmt = db()->prepare('SELECT id FROM pitches WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1');
    $stmt->execute([$userId]);
    $lastId = (int)$stmt->fetchColumn();
    if ($lastId > 0) {
        redirect('/entrepreneur/pitch-edit.php?id=' . $lastId);
    }
    flash_set('error', 'No pitch found. Create one first.');
    redirect('/entrepreneur/pitch-create.php');
}

$stmt = db()->prepare('SELECT * FROM pitches WHERE id = ? AND user_id = ?');
$stmt->execute([$pitchId, $userId]);
$pitch = $stmt->fetch();

if (!$pitch) {
    flash_set('error', 'Pitch not found.');
    redirect('/entrepreneur/dashboard.php');
}

$sectors = db()->query('SELECT id, name FROM sectors WHERE is_active = 1 ORDER BY name')->fetchAll();
$stages = ['idea', 'seed', 'early', 'growth', 'expansion', 'pre_ipo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $tagline = trim(mb_substr($_POST['tagline'] ?? '', 0, 140));
    $shortSummary = trim(mb_substr($_POST['short_summary'] ?? '', 0, 300));
    $problemStatement = trim($_POST['problem_statement'] ?? '');
    $solution = trim($_POST['solution'] ?? '');
    $marketSize = trim($_POST['market_size'] ?? '');
    $businessModel = trim($_POST['business_model'] ?? '');
    $fundingAmount = !empty($_POST['funding_amount']) ? (float)$_POST['funding_amount'] : null;
    $equityOffered = !empty($_POST['equity_offered']) ? (float)$_POST['equity_offered'] : null;
    $valuation = !empty($_POST['valuation']) ? (float)$_POST['valuation'] : null;
    $sectorId = !empty($_POST['sector_id']) ? (int)$_POST['sector_id'] : null;
    $stage = $_POST['stage'] ?? '';
    $pitchVideoUrl = trim($_POST['pitch_video_url'] ?? '');
    $isPublished = !empty($_POST['is_published']) ? 1 : 0;

    if ($tagline === '') {
        flash_set('error', 'Tagline is required.');
        redirect_back();
    }

    $pitchDeck = $pitch['pitch_deck'];
    if (!empty($_FILES['pitch_deck']) && $_FILES['pitch_deck']['error'] === UPLOAD_ERR_OK) {
        $allowedMime = ['application/pdf'];
        $destDir = upload_path('pitch-decks');
        $newDeck = handle_upload($_FILES['pitch_deck'], $allowedMime, UPLOAD_MAX_BYTES, $destDir);
        if (!$newDeck) {
            flash_set('error', 'Pitch deck must be a PDF under 10MB.');
            redirect_back();
        }
        if ($pitchDeck) {
            $oldPath = $destDir . '/' . $pitchDeck;
            if (file_exists($oldPath)) unlink($oldPath);
        }
        $pitchDeck = $newDeck;
    }

    $db = db();

    try {
        $stmt = $db->prepare('UPDATE pitches SET tagline = ?, short_summary = ?, problem_statement = ?, solution = ?, market_size = ?, business_model = ?, funding_amount = ?, equity_offered = ?, valuation = ?, sector_id = ?, stage = ?, pitch_deck = ?, pitch_video_url = ?, is_published = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
        $stmt->execute([$tagline, $shortSummary, $problemStatement, $solution, $marketSize, $businessModel, $fundingAmount, $equityOffered, $valuation, $sectorId, $stage, $pitchDeck, $pitchVideoUrl, $isPublished, $pitchId, $userId]);

        flash_set('success', 'Pitch updated successfully.');
        redirect('/entrepreneur/pitch-edit.php?id=' . $pitchId);
    } catch (\Throwable $e) {
        flash_set('error', 'Failed to update pitch. Please try again.');
        if (DEBUG_MODE) error_log('pitch edit error: ' . $e->getMessage());
        redirect_back();
    }
}

$pageTitle = 'Edit Pitch';
require __DIR__ . '/../includes/layout-dashboard.php';
?>
<h2 style="margin-bottom:0.25rem;">Edit Your Pitch</h2>
<p style="color:var(--color-text-muted);">Update your pitch to attract the right investors.</p>

<form method="POST" enctype="multipart/form-data" style="margin-top:1.5rem;">
    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="id" value="<?= $pitchId ?>">

    <div class="card" style="margin-bottom:1.5rem;">
        <h4>Pitch Content</h4>
        <div class="input-group">
            <label>One-line Tagline <span class="required">*</span></label>
            <input type="text" name="tagline" class="input" maxlength="140" value="<?= e($pitch['tagline']) ?>" required>
        </div>
        <div class="input-group">
            <label>Short Summary (max 300 characters)</label>
            <textarea name="short_summary" class="input" maxlength="300" style="min-height:60px;"><?= e($pitch['short_summary']) ?></textarea>
        </div>
        <div class="input-group">
            <label>Problem Statement</label>
            <textarea name="problem_statement" class="input" style="min-height:120px;"><?= e($pitch['problem_statement']) ?></textarea>
        </div>
        <div class="input-group">
            <label>Solution</label>
            <textarea name="solution" class="input" style="min-height:120px;"><?= e($pitch['solution']) ?></textarea>
        </div>
        <div class="input-group">
            <label>Market Size</label>
            <textarea name="market_size" class="input" style="min-height:80px;"><?= e($pitch['market_size']) ?></textarea>
        </div>
        <div class="input-group">
            <label>Business Model</label>
            <textarea name="business_model" class="input" style="min-height:80px;"><?= e($pitch['business_model']) ?></textarea>
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
        <h4>Funding & Details</h4>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="input-group">
                <label>Sector / Industry</label>
                <select name="sector_id" class="input">
                    <option value="">Select sector...</option>
                    <?php foreach ($sectors as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= (int)$pitch['sector_id'] === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Stage</label>
                <select name="stage" class="input">
                    <option value="">Select stage...</option>
                    <?php foreach ($stages as $s): ?>
                    <option value="<?= $s ?>" <?= $pitch['stage'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Amount Sought (NPR)</label>
                <input type="number" name="funding_amount" class="input" min="0" step="0.01" value="<?= e($pitch['funding_amount']) ?>">
            </div>
            <div class="input-group">
                <label>Equity Offered (%)</label>
                <input type="number" name="equity_offered" class="input" min="0" max="100" step="0.01" value="<?= e($pitch['equity_offered']) ?>">
            </div>
            <div class="input-group">
                <label>Valuation (NPR)</label>
                <input type="number" name="valuation" class="input" min="0" step="0.01" value="<?= e($pitch['valuation']) ?>">
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
        <h4>Media</h4>
        <div class="input-group">
            <label>Pitch Deck (PDF, max 10MB)</label>
            <input type="file" name="pitch_deck" class="input" accept="application/pdf">
            <?php if ($pitch['pitch_deck']): ?>
            <p style="font-size:0.8rem;color:var(--color-text-muted);margin-top:0.25rem;">Current: <?= e($pitch['pitch_deck']) ?></p>
            <?php endif; ?>
        </div>
        <div class="input-group">
            <label>Pitch Video (YouTube / Vimeo URL)</label>
            <input type="url" name="pitch_video_url" class="input" value="<?= e($pitch['pitch_video_url']) ?>" placeholder="https://youtube.com/watch?v=...">
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
        <label style="display:flex;align-items:center;gap:0.5rem;">
            <input type="checkbox" name="is_published" value="1" <?= $pitch['is_published'] ? 'checked' : '' ?>>
            Published
        </label>
    </div>

    <div style="display:flex;gap:1rem;">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="dashboard.php" class="btn btn-outline">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
</div></div>
