<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role(ROLE_ENTREPRENEUR);

$user = current_user();
$userId = (int)$user['id'];

if (!canCreatePitch($user)) {
    flash_set('error', 'You have reached the maximum number of pitches. Upgrade to Premium to create more.');
    redirect('/entrepreneur/dashboard');
}

$sectors = db()->query('SELECT id, name FROM sectors WHERE is_active = 1 ORDER BY name')->fetchAll();

$stages = ['idea', 'seed', 'early', 'growth', 'expansion', 'pre_ipo'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    unset($_SESSION['_old']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $_SESSION['_old'] = $_POST;

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
    $isPublished = 1;

    $errors = [];

    if ($tagline === '') {
        $errors[] = 'Tagline is required.';
    }
    if (!$sectorId) {
        $errors[] = 'Please select a sector.';
    }
    if ($stage === '') {
        $errors[] = 'Please select a stage.';
    }
    if (!$fundingAmount || $fundingAmount <= 0) {
        $errors[] = 'Funding amount is required and must be greater than 0.';
    }

    if (!empty($errors)) {
        flash_set('error', implode('<br>', $errors));
        redirect_back();
    }

    $pitchImage = null;
    if (!empty($_FILES['pitch_image']) && $_FILES['pitch_image']['error'] === UPLOAD_ERR_OK) {
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
        $destDir = upload_path('pitch-images');
        $uploaded = handle_upload($_FILES['pitch_image'], $allowedMime, UPLOAD_MAX_BYTES_PHOTO, $destDir);
        if ($uploaded) {
            $pitchImage = '/public/uploads/pitch-images/' . $uploaded;
        }
    }

    $pitchDeck = null;
    if (!empty($_FILES['pitch_deck']) && $_FILES['pitch_deck']['error'] === UPLOAD_ERR_OK) {
        $allowedMime = ['application/pdf'];
        $destDir = upload_path('pitch-decks');
        $pitchDeck = handle_upload($_FILES['pitch_deck'], $allowedMime, UPLOAD_MAX_BYTES, $destDir);
        if (!$pitchDeck) {
            flash_set('error', 'Pitch deck must be a PDF under 10MB.');
            redirect_back();
        }
    }

    $db = db();

    try {
        $stmt = $db->prepare('INSERT INTO pitches (user_id, tagline, short_summary, problem_statement, solution, market_size, business_model, funding_amount, equity_offered, valuation, sector_id, stage, pitch_deck, pitch_video_url, pitch_image, is_published, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
        $stmt->execute([$userId, $tagline, $shortSummary, $problemStatement, $solution, $marketSize, $businessModel, $fundingAmount, $equityOffered, $valuation, $sectorId, $stage, $pitchDeck, $pitchVideoUrl, $pitchImage, $isPublished]);
        $pitchId = (int)$db->lastInsertId();

        send_mail(
            $user['email'],
            'Your pitch is now published',
            '<p>Hello ' . e($user['name'] ?? 'there') . ',</p>' .
            '<p>Your pitch <strong>' . e($tagline) . '</strong> has been created and published on ' . APP_NAME . '.</p>' .
            '<p><a href="' . APP_URL . '/pitch/' . $pitchId . '">View your published pitch</a></p>'
        );

        unset($_SESSION['_old']);
        flash_set('success', 'Pitch created and published successfully.');
        redirect('/entrepreneur/pitch-edit.php?id=' . $pitchId);
    } catch (\Throwable $e) {
        flash_set('error', 'Failed to create pitch. Please try again.');
        if (DEBUG_MODE) error_log('pitch create error: ' . $e->getMessage());
        redirect_back();
    }
}

$pageTitle = 'Create Pitch';
require __DIR__ . '/../includes/layout-dashboard.php';
?>
<h2 style="margin-bottom:0.25rem;">Create Your Pitch</h2>
<p style="color:var(--color-text-muted);">Present your venture to thousands of pre-verified investors.</p>

<form method="POST" enctype="multipart/form-data" class="form-steps" novalidate style="margin-top:1.5rem;">
    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

    <div class="form-step-progress" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="3">
        <div class="step-indicator">
            <div class="step-segment active" data-step="1">
                <div class="step-number"><span class="step-check">&#10003;</span><span class="step-num">1</span></div>
                <span class="step-label">Pitch</span>
            </div>
            <div class="step-line"><div class="step-line-fill" style="width:0%"></div></div>
            <div class="step-segment" data-step="2">
                <div class="step-number">2</div>
                <span class="step-label">Funding</span>
            </div>
            <div class="step-line"><div class="step-line-fill" style="width:0%"></div></div>
            <div class="step-segment" data-step="3">
                <div class="step-number">3</div>
                <span class="step-label">Media</span>
            </div>
        </div>
    </div>

    <div class="step-panel" data-step="1">
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Pitch Content</h4>
            <div class="input-group">
                <label>One-line Tagline <span class="required">*</span></label>
                <input type="text" name="tagline" class="input" maxlength="140" value="<?= e(old('tagline')) ?>" required>
            </div>
            <div class="input-group">
                <label>Short Summary (max 300 characters)</label>
                <textarea name="short_summary" class="input" maxlength="300" style="min-height:60px;"><?= e(old('short_summary')) ?></textarea>
            </div>
            <div class="input-group">
                <label>Problem Statement</label>
                <textarea name="problem_statement" class="input" style="min-height:120px;"><?= e(old('problem_statement')) ?></textarea>
            </div>
            <div class="input-group">
                <label>Solution</label>
                <textarea name="solution" class="input" style="min-height:120px;"><?= e(old('solution')) ?></textarea>
            </div>
            <div class="input-group">
                <label>Market Size</label>
                <textarea name="market_size" class="input" style="min-height:80px;"><?= e(old('market_size')) ?></textarea>
            </div>
            <div class="input-group">
                <label>Business Model</label>
                <textarea name="business_model" class="input" style="min-height:80px;"><?= e(old('business_model')) ?></textarea>
            </div>
        </div>
    </div>

    <div class="step-panel" data-step="2" style="display:none">
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Funding & Details</h4>
            <div class="r-2">
                <div class="input-group">
                    <label>Sector / Industry</label>
                    <select name="sector_id" class="input">
                        <option value="">Select sector...</option>
                        <?php foreach ($sectors as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= old('sector_id') == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>Stage</label>
                    <select name="stage" class="input">
                        <option value="">Select stage...</option>
                        <?php foreach ($stages as $s): ?>
                        <option value="<?= $s ?>" <?= old('stage') === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>Amount Sought (NPR)</label>
                    <input type="number" name="funding_amount" class="input" min="0" step="0.01" value="<?= e(old('funding_amount')) ?>">
                </div>
                <div class="input-group">
                    <label>Equity Offered (%)</label>
                    <input type="number" name="equity_offered" class="input" min="0" max="100" step="0.01" value="<?= e(old('equity_offered')) ?>">
                </div>
                <div class="input-group">
                    <label>Valuation (NPR)</label>
                    <input type="number" name="valuation" class="input" min="0" step="0.01" value="<?= e(old('valuation')) ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="step-panel" data-step="3" style="display:none">
        <div class="card" style="margin-bottom:1.5rem;">
            <h4>Media</h4>
            <div class="input-group">
                <label>Pitch Image / Logo</label>
                <input type="file" name="pitch_image" class="input" accept="image/jpeg,image/png,image/webp" onchange="previewPitchImage(this)">
                <div id="pitch-image-preview" style="margin-top:0.5rem;display:none;">
                    <img src="" alt="Preview" style="width:200px;height:150px;object-fit:cover;border-radius:8px;border:1px solid var(--dash-border);">
                </div>
                <p style="font-size:0.8rem;color:var(--color-text-muted);margin-top:0.25rem;">Max 2MB. JPEG, PNG, WebP.</p>
            </div>
            <div class="input-group">
                <label>Pitch Deck (PDF, max 10MB)</label>
                <input type="file" name="pitch_deck" class="input" accept="application/pdf">
            </div>
            <div class="input-group">
                <label>Pitch Video (YouTube / Vimeo URL)</label>
                <input type="url" name="pitch_video_url" class="input" value="<?= e(old('pitch_video_url')) ?>" placeholder="https://youtube.com/watch?v=...">
            </div>
        </div>

        <div class="card" style="margin-bottom:1.5rem;">
            <strong>Publish immediately</strong>
            <p style="margin:0.35rem 0 0;color:var(--color-text-muted);font-size:0.9rem;">New pitches are published as soon as they are created.</p>
        </div>
    </div>

    <div class="step-nav">
        <button type="button" class="btn btn-outline btn-step-back" style="display:none">Back</button>
        <div class="step-nav-right">
            <button type="button" class="btn btn-primary btn-step-next">Next</button>
            <button type="submit" class="btn btn-primary btn-step-submit" style="display:none">Create Pitch</button>
            <a href="dashboard.php" class="btn btn-outline" style="margin-left:8px;">Cancel</a>
        </div>
    </div>
</form>

<script>
function previewPitchImage(input) {
    var preview = document.getElementById('pitch-image-preview');
    var img = preview.querySelector('img');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) { img.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}
</script>
<script src="<?= APP_URL ?>/assets/form-steps.js"></script>
<script>
initFormSteps({});
document.querySelector('.form-steps')?.addEventListener('submit', function() {
  var btn = this.querySelector('.btn-step-submit');
  if (btn) btn.disabled = true;
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
</div></div>
