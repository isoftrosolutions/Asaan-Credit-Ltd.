<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role(ROLE_ENTREPRENEUR);

$user = current_user();
$userId = (int)$user['id'];
$db = db();

$stmt = $db->prepare('SELECT * FROM pitches WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$pitches = $stmt->fetchAll();

$totalPitches = count($pitches);
$published = 0;
$totalViews = 0;
$totalInterest = 0;
$fundingAmounts = [];
foreach ($pitches as $p) {
    if ($p['is_published']) $published++;
    if ($p['funding_amount'] > 0) $fundingAmounts[] = (float)$p['funding_amount'];
}
$topFunding = !empty($fundingAmounts) ? max($fundingAmounts) : 0;

$pitchIds = array_column($pitches, 'id');
$interestRequests = [];
if (!empty($pitchIds)) {
    $placeholders = implode(',', array_fill(0, count($pitchIds), '?'));

    $viewStmt = $db->prepare("SELECT SUM(views) FROM pitches WHERE id IN ($placeholders)");
    $viewStmt->execute($pitchIds);
    $totalViews = (int)$viewStmt->fetchColumn();

    $irStmt = $db->prepare("SELECT COUNT(*) FROM interest_requests WHERE pitch_id IN ($placeholders)");
    $irStmt->execute($pitchIds);
    $totalInterest = (int)$irStmt->fetchColumn();

    $irDetailStmt = $db->prepare("
        SELECT ir.*, u.name AS sender_name, u.role AS sender_role, p.tagline AS pitch_tagline
        FROM interest_requests ir
        JOIN users u ON u.id = ir.sender_id
        JOIN pitches p ON p.id = ir.pitch_id
        WHERE ir.pitch_id IN ($placeholders) AND ir.status = 'pending'
        ORDER BY ir.created_at DESC
    ");
    $irDetailStmt->execute($pitchIds);
    $interestRequests = $irDetailStmt->fetchAll();
}

$pageTitle = 'Entrepreneur Dashboard';
require __DIR__ . '/../includes/layout-dashboard.php';
?>
<h2 style="margin-bottom:0.25rem;">Entrepreneur Dashboard</h2>
<p style="color:#666;">Manage your pitches and track investor interest.</p>

<?php if (empty($pitches)): ?>
<div class="card" style="text-align:center;padding:3rem 2rem;margin-top:1.5rem;">
  <h3 style="margin-bottom:0.5rem;">No pitches yet</h3>
  <p style="color:#666;margin-bottom:1rem;">Create your first pitch to start connecting with investors.</p>
  <a href="pitch-create.php" class="btn btn-primary">Create Pitch</a>
</div>
<?php else: ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
  <div></div>
  <a href="pitch-create.php" class="btn btn-primary">+ New Pitch</a>
</div>

<div class="stats-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem;">
  <div class="stat-card"><div class="stat-value"><?= $totalViews ?></div><div class="stat-label">Profile views</div></div>
  <div class="stat-card"><div class="stat-value"><?= $totalInterest ?></div><div class="stat-label">Interest requests</div></div>
  <div class="stat-card"><div class="stat-value"><?= $published ?>/<?= $totalPitches ?></div><div class="stat-label">Published</div></div>
  <div class="stat-card"><div class="stat-value"><?= $topFunding ? money($topFunding) : '—' ?></div><div class="stat-label">Current ask</div></div>
</div>

<h3>Your Pitches</h3>
<div class="card" style="padding:0;">
  <table style="width:100%;border-collapse:collapse;">
    <tr style="border-bottom:1px solid #eae8e6;background:#faf8f6;">
      <th style="text-align:left;padding:14px 18px;font-weight:600;">Tagline</th>
      <th style="text-align:left;padding:14px 18px;font-weight:600;">Stage</th>
      <th style="text-align:left;padding:14px 18px;font-weight:600;">Funding Ask</th>
      <th style="text-align:center;padding:14px 18px;font-weight:600;">Status</th>
      <th style="text-align:right;padding:14px 18px;font-weight:600;"></th>
    </tr>
    <?php foreach ($pitches as $p): ?>
    <tr style="border-bottom:1px solid #eae8e6;">
      <td style="padding:14px 18px;">
        <strong><?= e($p['tagline']) ?></strong>
        <?php if ($p['is_featured']): ?><span class="tx-badge tx-badge-sale" style="font-size:0.7rem;margin-left:0.5rem;">Featured</span><?php endif; ?>
      </td>
      <td style="padding:14px 18px;"><?= e(ucfirst($p['stage'] ?? '—')) ?></td>
      <td style="padding:14px 18px;"><?= $p['funding_amount'] ? money($p['funding_amount']) : '—' ?></td>
      <td style="padding:14px 18px;text-align:center;">
        <?php if ($p['is_published']): ?><span style="color:#166534;font-weight:600;">Published</span>
        <?php else: ?><span style="color:#b91c1c;font-weight:600;">Draft</span>
        <?php endif; ?>
      </td>
      <td style="padding:14px 18px;text-align:right;">
        <a href="<?= APP_URL ?>/pitch/<?= $p['id'] ?>" class="btn btn-sm btn-secondary" style="text-decoration:none;">View</a>
        <a href="pitch-edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-accent" style="text-decoration:none;">Edit</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

<?php if (!empty($interestRequests)): ?>
<h3 style="margin-top:2rem;">Recent Interest Requests</h3>
<div class="card" style="padding:0;">
  <table style="width:100%;border-collapse:collapse;">
    <tr style="border-bottom:1px solid #eae8e6;background:#faf8f6;">
      <th style="text-align:left;padding:14px 18px;font-weight:600;">Investor</th>
      <th style="text-align:left;padding:14px 18px;font-weight:600;">Pitch</th>
      <th style="text-align:left;padding:14px 18px;font-weight:600;">Message</th>
      <th style="padding:14px 18px;font-weight:600;">Date</th>
      <th style="padding:14px 18px;font-weight:600;"></th>
    </tr>
    <?php foreach ($interestRequests as $ir): ?>
    <tr style="border-bottom:1px solid #eae8e6;">
      <td style="padding:14px 18px;">
        <strong><?= e($ir['sender_name']) ?></strong><br>
        <span class="text-xs"><?= e(ucfirst(str_replace('_', ' ', $ir['sender_role'] ?? ''))) ?></span>
      </td>
      <td style="padding:14px 18px;font-size:0.9rem;"><?= e(mb_substr($ir['pitch_tagline'], 0, 40)) ?>...</td>
      <td style="padding:14px 18px;font-size:0.9rem;"><?= e($ir['message'] ?? '—') ?></td>
      <td style="padding:14px 18px;font-size:0.85rem;color:#666;"><?= date_human($ir['created_at']) ?></td>
      <td style="padding:14px 18px;">
        <form method="POST" action="<?= APP_URL ?>/connections/respond" style="display:flex;gap:0.5rem;">
          <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="request_id" value="<?= $ir['id'] ?>">
          <button type="submit" name="action" value="accept" class="btn btn-accent btn-sm">Accept & Connect</button>
          <button type="submit" name="action" value="reject" class="btn btn-outline btn-sm" onclick="return confirm('Decline this interest request?')">Decline</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
</div></div>
