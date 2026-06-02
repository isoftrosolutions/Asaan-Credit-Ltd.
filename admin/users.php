<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$action = $_GET['action'] ?? '';
$userId = (int)($_GET['id'] ?? 0);

if ($action === 'suspend' && $userId) {
    db()->prepare('UPDATE users SET is_suspended = 1 WHERE id = ?')->execute([$userId]);
    admin_log('suspend_user', 'user', $userId);
    flash_set('success', 'User suspended.');
    redirect('/admin/users');
}
if ($action === 'unsuspend' && $userId) {
    db()->prepare('UPDATE users SET is_suspended = 0 WHERE id = ?')->execute([$userId]);
    admin_log('unsuspend_user', 'user', $userId);
    flash_set('success', 'User unsuspended.');
    redirect('/admin/users');
}
if ($action === 'edit_role' && $userId && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $role = $_POST['role'] ?? '';
    $allowedRoles = ['investor', 'business_owner', 'entrepreneur', 'franchisor', 'advisor'];
    if (in_array($role, $allowedRoles, true)) {
        db()->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $userId]);
        admin_log('edit_user_role', 'user', $userId, ['role' => $role]);
        flash_set('success', 'User role updated.');
    }
    redirect('/admin/users');
}

$roleFilter = $_GET['role'] ?? '';
$vStatusFilter = $_GET['verification_status'] ?? '';
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where = ['1=1'];
$params = [];
if ($roleFilter) { $where[] = 'u.role = ?'; $params[] = $roleFilter; }
if ($vStatusFilter) { $where[] = 'u.verification_status = ?'; $params[] = $vStatusFilter; }
if ($search) { $where[] = '(u.name LIKE ? OR u.email LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

$whereClause = implode(' AND ', $where);
$countStmt = db()->prepare("SELECT COUNT(*) FROM users u WHERE $whereClause");
$countStmt->execute($params);
$pagi = paginate($countStmt, $page, $perPage);

$stmt = db()->prepare("SELECT u.* FROM users u WHERE $whereClause ORDER BY u.created_at DESC LIMIT {$pagi['perPage']} OFFSET {$pagi['offset']}");
$stmt->execute($params);
$users = $stmt->fetchAll();

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="users-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Name', 'Email', 'Role', 'Verification Status', 'Suspended', 'Created']);
    foreach ($users as $u) {
        fputcsv($out, [$u['id'], $u['name'], $u['email'], $u['role'], $u['verification_status'], $u['is_suspended'] ? 'Yes' : 'No', $u['created_at']]);
    }
    fclose($out);
    exit;
}
$pageTitle = 'User Management';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('User Management', number_format($pagi['total'] ?? count($users)) . ' users matched.');
?>
<form method="get" class="dash-filterbar">
  <div class="input-group grow">
    <label>Search</label>
    <input type="text" name="search" class="input" value="<?= e($search) ?>" placeholder="Name or email...">
  </div>
  <div class="input-group">
    <label>Role</label>
    <select name="role" class="select">
      <option value="">All</option>
      <option value="investor" <?= $roleFilter === 'investor' ? 'selected' : '' ?>>Investor</option>
      <option value="business_owner" <?= $roleFilter === 'business_owner' ? 'selected' : '' ?>>Business Owner</option>
      <option value="entrepreneur" <?= $roleFilter === 'entrepreneur' ? 'selected' : '' ?>>Entrepreneur</option>
      <option value="franchisor" <?= $roleFilter === 'franchisor' ? 'selected' : '' ?>>Franchisor</option>
      <option value="advisor" <?= $roleFilter === 'advisor' ? 'selected' : '' ?>>Advisor</option>
    </select>
  </div>
  <div class="input-group">
    <label>Verification</label>
    <select name="verification_status" class="select">
      <option value="">All</option>
      <option value="unverified" <?= $vStatusFilter === 'unverified' ? 'selected' : '' ?>>Unverified</option>
      <option value="pending" <?= $vStatusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
      <option value="verified" <?= $vStatusFilter === 'verified' ? 'selected' : '' ?>>Verified</option>
      <option value="rejected" <?= $vStatusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
    </select>
  </div>
  <button type="submit" class="btn btn-sm btn-primary">Filter</button>
  <a href="<?= APP_URL ?>/admin/users" class="btn btn-sm btn-outline">Clear</a>
  <a href="<?= APP_URL ?>/admin/users?export=csv&<?= e(http_build_query($_GET)) ?>" class="btn btn-sm btn-outline">CSV export</a>
</form>

<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>ID</th><th>Name</th><th>Email</th><th>Role</th>
        <th class="ta-center">Verification</th><th class="ta-center">Status</th><th>Created</th><th class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td class="t-muted"><?= $u['id'] ?></td>
          <td class="t-strong"><?= e($u['name']) ?></td>
          <td class="t-muted"><?= e($u['email']) ?></td>
          <td><span class="dash-pill open"><?= e(ucfirst(str_replace('_', ' ', $u['role']))) ?></span></td>
          <td class="ta-center"><span class="dash-pill <?= $u['verification_status'] === 'verified' ? 'published' : 'draft' ?>"><?= e($u['verification_status']) ?></span></td>
          <td class="ta-center"><span class="dash-pill <?= $u['is_suspended'] ? 'draft' : 'published' ?>"><?= $u['is_suspended'] ? 'Suspended' : 'Active' ?></span></td>
          <td class="t-muted"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
          <td class="ta-right">
            <div class="dash-table-actions" style="align-items:center;">
              <?php if ($u['is_suspended']): ?>
                <a href="?action=unsuspend&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline">Unsuspend</a>
              <?php else: ?>
                <a href="?action=suspend&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline" onclick="return confirm('Suspend this user?')">Suspend</a>
              <?php endif; ?>
              <form method="post" action="?action=edit_role&id=<?= $u['id'] ?>" style="display:inline;">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <select name="role" class="select" style="font-size:0.8rem;padding:4px 8px;" onchange="this.form.submit()">
                  <option value="investor" <?= $u['role'] === 'investor' ? 'selected' : '' ?>>Investor</option>
                  <option value="business_owner" <?= $u['role'] === 'business_owner' ? 'selected' : '' ?>>Biz Owner</option>
                  <option value="entrepreneur" <?= $u['role'] === 'entrepreneur' ? 'selected' : '' ?>>Entrepreneur</option>
                  <option value="franchisor" <?= $u['role'] === 'franchisor' ? 'selected' : '' ?>>Franchisor</option>
                  <option value="advisor" <?= $u['role'] === 'advisor' ? 'selected' : '' ?>>Advisor</option>
                </select>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($users)): ?>
        <tr><td colspan="8"><?php ui_empty_state(['icon' => 'users', 'title' => 'No users found', 'text' => 'Try adjusting your filters.']); ?></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= render_pagination($pagi['page'], $pagi['lastPage'], '/admin/users?' . http_build_query(array_filter(['role' => $roleFilter, 'verification_status' => $vStatusFilter, 'search' => $search]))) ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
