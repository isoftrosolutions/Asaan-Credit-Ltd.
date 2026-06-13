<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$action = $_GET['action'] ?? '';
$businessId = (int)($_GET['id'] ?? 0);
$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

if ($action === 'approve' && $businessId) {
    db()->prepare("UPDATE businesses SET status = 'approved', is_published = 1 WHERE id = ?")->execute([$businessId]);
    admin_log('approve_business', 'business', $businessId);
    flash_set('success', 'Business approved and published.');
    redirect_back();
}
if ($action === 'reject' && $businessId) {
    db()->prepare("UPDATE businesses SET status = 'rejected', is_published = 0 WHERE id = ?")->execute([$businessId]);
    admin_log('reject_business', 'business', $businessId);
    flash_set('warning', 'Business rejected.');
    redirect_back();
}
if ($action === 'hide' && $businessId) {
    db()->prepare('UPDATE businesses SET is_hidden = 1 WHERE id = ?')->execute([$businessId]);
    admin_log('hide_business', 'business', $businessId);
    flash_set('success', 'Business hidden from public.');
    redirect('/admin/businesses');
}
if ($action === 'unhide' && $businessId) {
    db()->prepare('UPDATE businesses SET is_hidden = 0 WHERE id = ?')->execute([$businessId]);
    admin_log('unhide_business', 'business', $businessId);
    flash_set('success', 'Business is now visible.');
    redirect('/admin/businesses');
}
if ($action === 'delete' && $businessId) {
    db()->prepare('DELETE FROM businesses WHERE id = ?')->execute([$businessId]);
    admin_log('delete_business', 'business', $businessId);
    flash_set('success', 'Business deleted.');
    redirect('/admin/businesses');
}

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where = ['1=1'];
$params = [];

if ($statusFilter !== '') {
    $where[] = 'b.status = ?';
    $params[] = $statusFilter;
}
if ($search) {
    $where[] = '(b.business_name LIKE ? OR u.name LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = implode(' AND ', $where);
$countStmt = db()->prepare("SELECT COUNT(*) FROM businesses b JOIN users u ON u.id = b.user_id WHERE $whereClause");
$countStmt->execute($params);
$pagi = paginate($countStmt, $page, $perPage);

$stmt = db()->prepare("SELECT b.*, u.name AS user_name, u.email, u.is_suspended FROM businesses b JOIN users u ON u.id = b.user_id WHERE $whereClause ORDER BY b.created_at DESC LIMIT {$pagi['perPage']} OFFSET {$pagi['offset']}");
$stmt->execute($params);
$businesses = $stmt->fetchAll();

$statusLabels = ['draft' => 'Draft', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'sold' => 'Sold'];
$statusClasses = ['draft' => 'draft', 'pending' => 'pending', 'approved' => 'published', 'rejected' => 'rejected', 'sold' => 'rejected'];

$pageTitle = 'Business Management';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('Business Management', 'Review, approve, or manage all business listings.');
?>
<form method="get" class="dash-filterbar">
  <div class="input-group grow">
    <label>Search</label>
    <input type="text" name="search" class="input" value="<?= e($search) ?>" placeholder="Search business or user...">
  </div>
  <div class="input-group" style="width:160px;">
    <label>Status</label>
    <select name="status" class="input" onchange="this.form.submit()">
      <option value="">All statuses</option>
      <?php foreach (['draft','pending','approved','rejected','sold'] as $s): ?>
        <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button type="submit" class="btn btn-sm btn-primary" style="margin-top:22px;">Filter</button>
  <a href="<?= APP_URL ?>/admin/businesses" class="btn btn-sm btn-outline" style="margin-top:22px;">Clear</a>
</form>

<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>Image</th><th>Business</th><th>Owner</th><th>Listing type</th>
        <th class="ta-center">Status</th><th class="ta-center">Hidden</th><th>Created</th><th class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($businesses as $b): ?>
        <tr>
          <td>
            <?php
              $bsrc = '';
              if (!empty($b['thumbnail_url'])) $bsrc = (str_starts_with($b['thumbnail_url'], 'http') || str_starts_with($b['thumbnail_url'], '/')) ? $b['thumbnail_url'] : '/public/uploads/business-thumbnails/' . $b['thumbnail_url'];
            ?>
            <?php if ($bsrc): ?>
              <img src="<?= e($bsrc) ?>" alt="" style="width:48px;height:36px;object-fit:cover;border-radius:4px;">
            <?php else: ?>
              <div style="width:48px;height:36px;background:var(--color-bg-soft);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--color-text-muted);"><i class="fas fa-building"></i></div>
            <?php endif; ?>
          </td>
          <td><span class="t-strong"><?= e($b['business_name']) ?></span></td>
          <td><span class="t-strong"><?= e($b['user_name']) ?></span><br><span class="t-muted"><?= e($b['email']) ?></span></td>
          <td><span class="dash-pill open"><?= e(ucfirst(str_replace('_', ' ', $b['listing_type']))) ?></span></td>
          <td class="ta-center"><span class="dash-pill <?= $statusClasses[$b['status']] ?? 'draft' ?>"><?= $statusLabels[$b['status']] ?? $b['status'] ?></span></td>
          <td class="ta-center"><?= $b['is_hidden'] ? '<span class="dash-pill draft">Yes</span>' : '<span class="dash-pill published">No</span>' ?></td>
          <td class="t-muted"><?= date_human($b['created_at']) ?></td>
          <td class="ta-right">
            <span class="dash-table-actions" style="display:flex;flex-wrap:wrap;gap:4px;max-width:200px;justify-content:flex-end;">
              <a href="<?= APP_URL ?>/business/<?= $b['id'] ?>" target="_blank" class="btn btn-sm btn-outline">View</a>
              <?php if ($b['status'] === 'pending' || $b['status'] === 'draft'): ?>
                <a href="?action=approve&id=<?= $b['id'] ?>" class="btn btn-sm btn-primary" onclick="return confirm('Approve this business?')">Approve</a>
                <a href="?action=reject&id=<?= $b['id'] ?>" class="btn btn-sm btn-outline" onclick="return confirm('Reject this business?')">Reject</a>
              <?php endif; ?>
              <?php if ($b['is_hidden']): ?>
                <a href="?action=unhide&id=<?= $b['id'] ?>" class="btn btn-sm btn-outline">Unhide</a>
              <?php else: ?>
                <a href="?action=hide&id=<?= $b['id'] ?>" class="btn btn-sm btn-outline" onclick="return confirm('Hide this business?')">Hide</a>
              <?php endif; ?>
              <a href="?action=delete&id=<?= $b['id'] ?>" class="btn btn-sm btn-primary" onclick="return confirm('Delete this business permanently?')" style="background:var(--color-error);border-color:var(--color-error);">Delete</a>
            </span>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($businesses)): ?>
        <tr><td colspan="8"><?php ui_empty_state(['icon' => 'briefcase', 'title' => 'No businesses found', 'text' => 'Try a different filter or search.']); ?></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= render_pagination($pagi['page'], $pagi['lastPage'], '/admin/businesses?' . http_build_query(array_filter(['search' => $search, 'status' => $statusFilter]))) ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
