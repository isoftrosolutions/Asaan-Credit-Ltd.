<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$user = current_user();
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

// Handle POST toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $businessId = (int)($_POST['business_id'] ?? 0);
    $field = $_POST['field'] ?? '';

    $allowed = ['email_verified', 'phone_verified', 'identity_verified', 'company_verified'];
    if ($businessId < 1 || !in_array($field, $allowed, true)) {
        flash_set('error', 'Invalid request.');
        redirect('/admin/business-verifications');
    }

    $db = db();

    // Ensure verification row exists
    $db->prepare('INSERT IGNORE INTO business_verifications (business_id, created_at, updated_at) VALUES (?, NOW(), NOW())')->execute([$businessId]);

    // Toggle the field
    $db->prepare("UPDATE business_verifications SET $field = NOT COALESCE($field, 0), updated_at = NOW() WHERE business_id = ?")->execute([$businessId]);

    admin_log('toggle_verification', 'business_verifications', $businessId, ['field' => $field]);
    flash_set('success', 'Verification badge toggled.');
    redirect('/admin/business-verifications');
}

$stmt = db()->prepare("SELECT b.id, b.business_name, b.slug, b.user_id, u.name AS owner_name, u.email AS owner_email, bv.email_verified, bv.phone_verified, bv.identity_verified, bv.company_verified, bv.updated_at AS verified_at FROM businesses b JOIN users u ON u.id = b.user_id LEFT JOIN business_verifications bv ON bv.business_id = b.id ORDER BY b.created_at DESC LIMIT {$perPage} OFFSET ?");
$stmt->execute([($page - 1) * $perPage]);
$listings = $stmt->fetchAll();

$countStmt = db()->query('SELECT COUNT(*) FROM businesses');
$totalCount = (int)$countStmt->fetchColumn();
$lastPage = (int)ceil($totalCount / $perPage);

$pageTitle = 'Business Verifications';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('Business Verifications', 'Toggle verification badges (Email, Phone, Identity, Company) for each business listing.');
?>

<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>Business</th><th>Owner</th>
        <th class="ta-center">Email</th>
        <th class="ta-center">Phone</th>
        <th class="ta-center">Identity</th>
        <th class="ta-center">Company</th>
        <th>Updated</th>
      </tr></thead>
      <tbody>
      <?php foreach ($listings as $b): ?>
        <tr>
          <td><a href="<?= APP_URL ?>/business/<?= $b['slug'] ?: $b['id'] ?>" class="dash-section-link" target="_blank"><?= e($b['business_name']) ?></a></td>
          <td><span class="t-strong"><?= e($b['owner_name']) ?></span><br><span class="t-muted"><?= e($b['owner_email']) ?></span></td>
          <?php foreach (['email_verified', 'phone_verified', 'identity_verified', 'company_verified'] as $f): ?>
          <td class="ta-center">
            <form method="post" style="display:inline;">
              <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
              <input type="hidden" name="business_id" value="<?= $b['id'] ?>">
              <input type="hidden" name="field" value="<?= $f ?>">
              <button type="submit" class="btn btn-sm <?= $b[$f] ? 'btn-primary' : 'btn-outline' ?>" style="min-width:60px;" title="Click to toggle"><?= $b[$f] ? 'Yes' : 'No' ?></button>
            </form>
          </td>
          <?php endforeach; ?>
          <td class="t-muted"><?= $b['verified_at'] ? date_human($b['verified_at']) : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($listings)): ?>
        <tr><td colspan="7"><?php ui_empty_state(['icon' => 'check', 'title' => 'No businesses found', 'text' => 'Create a business listing first.']); ?></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php if ($lastPage > 1): ?>
<?= render_pagination($page, $lastPage, '/admin/business-verifications') ?>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
