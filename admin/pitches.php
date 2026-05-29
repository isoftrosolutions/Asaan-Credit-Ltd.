<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$pageTitle = 'Pitch Moderation';
require __DIR__ . '/../includes/layout-admin.php';

$action = $_GET['action'] ?? '';
$pitchId = (int)($_GET['id'] ?? 0);

if ($action === 'hide' && $pitchId) {
    db()->prepare('UPDATE pitches SET is_hidden = 1 WHERE id = ?')->execute([$pitchId]);
    admin_log('hide_pitch', 'pitch', $pitchId);
    flash_set('success', 'Pitch hidden from public.');
    redirect('/admin/pitches');
}
if ($action === 'unhide' && $pitchId) {
    db()->prepare('UPDATE pitches SET is_hidden = 0 WHERE id = ?')->execute([$pitchId]);
    admin_log('unhide_pitch', 'pitch', $pitchId);
    flash_set('success', 'Pitch is now visible.');
    redirect('/admin/pitches');
}
if ($action === 'flag' && $pitchId) {
    db()->prepare('UPDATE pitches SET is_hidden = 1 WHERE id = ?')->execute([$pitchId]);
    admin_log('flag_pitch', 'pitch', $pitchId, ['reason' => 'Flagged for review']);
    flash_set('warning', 'Pitch flagged and hidden for review.');
    redirect('/admin/pitches');
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where = ['1=1'];
$params = [];
if ($search) { $where[] = '(p.tagline LIKE ? OR u.name LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

$whereClause = implode(' AND ', $where);
$countStmt = db()->prepare("SELECT COUNT(*) FROM pitches p JOIN users u ON u.id = p.user_id WHERE $whereClause");
$countStmt->execute($params);
$pagi = paginate($countStmt, $page, $perPage);

$stmt = db()->prepare("SELECT p.*, u.name, u.email, u.is_suspended FROM pitches p JOIN users u ON u.id = p.user_id WHERE $whereClause ORDER BY p.created_at DESC LIMIT {$pagi['perPage']} OFFSET {$pagi['offset']}");
$stmt->execute($params);
$pitches = $stmt->fetchAll();
?>
<h2>Pitch Moderation</h2>
<div class="card" style="margin-bottom:1rem;">
  <form method="get" style="display:flex;gap:0.5rem;align-items:flex-end;">
    <div class="input-group" style="flex:1;">
      <label>Search</label>
      <input type="text" name="search" class="input" value="<?= e($search) ?>" placeholder="Search pitch or user...">
    </div>
    <button type="submit" class="btn btn-sm btn-primary">Search</button>
    <a href="<?= APP_URL ?>/admin/pitches" class="btn btn-sm btn-secondary">Clear</a>
  </form>
</div>
<div class="card">
  <table style="width:100%;">
    <tr style="border-bottom:1px solid var(--color-border);">
      <th style="text-align:left;padding:8px;">User</th>
      <th style="text-align:left;padding:8px;">Tagline</th>
      <th style="padding:8px;">Stage</th>
      <th style="padding:8px;">Amount</th>
      <th style="padding:8px;">Status</th>
      <th style="padding:8px;">Created</th>
      <th style="padding:8px;">Actions</th>
    </tr>
    <?php foreach ($pitches as $p): ?>
    <tr style="border-bottom:1px solid var(--color-border);">
      <td style="padding:10px 8px;font-weight:600;"><?= e($p['name']) ?><br><small style="color:var(--color-text-muted);"><?= e($p['email']) ?></small></td>
      <td style="padding:10px 8px;max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($p['tagline']) ?></td>
      <td style="padding:10px 8px;"><span class="badge"><?= e($p['stage']) ?></span></td>
      <td style="padding:10px 8px;"><?= $p['funding_amount'] ? money($p['funding_amount']) : '—' ?></td>
      <td style="padding:10px 8px;">
        <?php if ($p['is_hidden']): ?><span style="color:var(--color-error);">Hidden</span>
        <?php else: ?><span style="color:var(--color-success);">Visible</span>
        <?php endif; ?>
        <?php if (!$p['is_published']): ?> / <span style="color:var(--color-text-muted);">Draft</span><?php endif; ?>
      </td>
      <td style="padding:10px 8px;font-size:0.85rem;"><?= date_human($p['created_at']) ?></td>
      <td style="padding:10px 8px;">
        <a href="<?= APP_URL ?>/pitch/<?= $p['id'] ?>" target="_blank" class="btn btn-sm btn-outline">View</a>
        <?php if ($p['is_hidden']): ?>
          <a href="?action=unhide&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline" style="color:var(--color-success);">Unhide</a>
        <?php else: ?>
          <a href="?action=hide&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline" style="color:var(--color-error);" onclick="return confirm('Hide this pitch?')">Hide</a>
        <?php endif; ?>
        <a href="?action=flag&id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Flag for review?')">Flag</a>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($pitches)): ?>
    <tr><td colspan="7" style="padding:20px;text-align:center;color:var(--color-text-muted);">No pitches found.</td></tr>
    <?php endif; ?>
  </table>
</div>
<?= render_pagination($pagi['page'], $pagi['lastPage'], '/admin/pitches?' . http_build_query(array_filter(['search' => $search]))) ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
