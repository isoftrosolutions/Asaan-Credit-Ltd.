<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role(ROLE_INVESTOR);

$userId = current_user()['id'];

$stmt = db()->prepare('SELECT * FROM verification_documents WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$documents = $stmt->fetchAll();

$userVerificationStatus = current_user()['verification_status'] ?? 'unverified';

$docTypes = [
    'citizenship' => 'Citizenship / Passport',
    'pan' => 'PAN Card',
    'company_reg' => 'Company Registration',
    'other' => 'Other Document',
];

$destDir = STORAGE_PATH . '/verification-docs';
$allowedMime = explode(',', UPLOAD_ALLOWED_MIME);
$maxBytes = UPLOAD_MAX_BYTES;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (!empty($_FILES['document']['name'][0])) {
        foreach ($_FILES['document']['name'] as $i => $name) {
            if ($_FILES['document']['error'][$i] !== UPLOAD_ERR_OK) continue;

            $file = [
                'name' => $_FILES['document']['name'][$i],
                'type' => $_FILES['document']['type'][$i],
                'tmp_name' => $_FILES['document']['tmp_name'][$i],
                'error' => $_FILES['document']['error'][$i],
                'size' => $_FILES['document']['size'][$i],
            ];

            $filename = handle_upload($file, $allowedMime, $maxBytes, $destDir);
            if ($filename) {
                $docType = $_POST['document_type'][$i] ?? 'other';
                $stmt = db()->prepare('INSERT INTO verification_documents (user_id, document_type, file_path, status, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())');
                $stmt->execute([$userId, $docType, $filename, 'pending']);
            }
        }
        flash_set('success', 'Documents uploaded for review.');
    }

    redirect('/investor/documents-edit.php');
}

$pageTitle = 'Verification Documents';
require __DIR__ . '/../includes/layout-dashboard.php';
?>
<div style="max-width:720px; margin:0 auto; padding:2rem 0 3rem;">
  <h2>Verification Documents</h2>
  <p style="color:#666;">These documents are reviewed by our team within 48 hours. They are never shown publicly.</p>

  <?php if (!empty($documents)): ?>
    <?php foreach ($documents as $doc): ?>
    <div class="card" style="margin-top:1.5rem;">
      <h4><?= e($docTypes[$doc['document_type']] ?? ucfirst($doc['document_type'])) ?></h4>
      <div style="border:2px dashed #ccc; border-radius:1rem; padding:1.5rem; text-align:center; margin-top:1rem;">
        <div style="font-size:1.5rem; margin-bottom:0.5rem;">📄</div>
        <strong><?= e(basename($doc['file_path'])) ?></strong><br>
        <span style="font-size:0.8rem; color:#888;">
          Uploaded <?= date_human($doc['created_at']) ?>
          <?php if ($doc['status'] === 'verified'): ?>
            • Status: <strong style="color:#166534;">Verified</strong>
          <?php elseif ($doc['status'] === 'rejected'): ?>
            • Status: <strong style="color:#dc2626;">Rejected</strong>
            <?php if ($doc['rejection_reason']): ?> — <?= e($doc['rejection_reason']) ?><?php endif; ?>
          <?php else: ?>
            • Status: <strong style="color:#ca8a04;">Pending Review</strong>
          <?php endif; ?>
        </span>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" style="margin-top:1.5rem;">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">

    <div class="card">
      <h4>Upload New Documents</h4>
      <p style="font-size:0.85rem; color:#666; margin-bottom:1rem;">Accepted: JPG, PNG, PDF (max 10MB per file)</p>

      <div id="upload-rows">
        <div class="upload-row" style="display:flex; gap:1rem; align-items:end; margin-bottom:1rem;">
          <div class="input-group" style="flex:1;">
            <label>Document Type</label>
            <select name="document_type[]" class="input">
              <?php foreach ($docTypes as $val => $label): ?>
              <option value="<?= e($val) ?>"><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="input-group" style="flex:2;">
            <label>File</label>
            <input type="file" name="document[]" class="input" accept=".jpg,.jpeg,.png,.pdf">
          </div>
          <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#dc2626;cursor:pointer;padding:0 0 4px;font-size:1.2rem;">×</button>
        </div>
      </div>

      <button type="button" onclick="addUploadRow()" class="btn btn-secondary btn-sm" style="margin-bottom:1rem;">+ Add another file</button>
    </div>

    <button type="submit" class="btn btn-primary" style="margin-top:1.5rem; width:100%;">Submit for Verification</button>
  </form>

  <div style="margin-top:1rem; font-size:0.85rem; color:#888; text-align:center;">
    Verification Status:
    <strong style="<?= $userVerificationStatus === 'verified' ? 'color:#166534;' : ($userVerificationStatus === 'rejected' ? 'color:#dc2626;' : 'color:#ca8a04;') ?>">
      <?= e(ucfirst($userVerificationStatus)) ?>
    </strong>
    <?php if ($userVerificationStatus === 'verified'): ?>
      (last reviewed on file upload)
    <?php endif; ?>
  </div>
</div>

<script>
function addUploadRow() {
  var div = document.createElement('div');
  div.className = 'upload-row';
  div.style.cssText = 'display:flex; gap:1rem; align-items:end; margin-bottom:1rem;';
  div.innerHTML =
    '<div class="input-group" style="flex:1;"><label>Document Type</label><select name="document_type[]" class="input">' +
    '<?php foreach ($docTypes as $val => $label): ?><option value="<?= e($val) ?>"><?= e($label) ?></option><?php endforeach; ?>' +
    '</select></div>' +
    '<div class="input-group" style="flex:2;"><label>File</label><input type="file" name="document[]" class="input" accept=".jpg,.jpeg,.png,.pdf"></div>' +
    '<button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#dc2626;cursor:pointer;padding:0 0 4px;font-size:1.2rem;">×</button>';
  document.getElementById('upload-rows').appendChild(div);
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
