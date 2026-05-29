<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role(ROLE_BUSINESS_OWNER);

$user = current_user();
$userId = (int)$user['id'];

$stmt = db()->prepare('SELECT * FROM businesses WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$businesses = $stmt->fetchAll();

$totalListings = count($businesses);
$published = 0;
$totalViews = 0;
$ratings = [];
$askingPrices = [];
foreach ($businesses as $b) {
    if ($b['is_published']) $published++;
    $totalViews += (int)$b['views'];
    if ($b['rating'] > 0) $ratings[] = (float)$b['rating'];
    if ($b['asking_price'] > 0) $askingPrices[] = (float)$b['asking_price'];
}
$avgRating = count($ratings) ? round(array_sum($ratings) / count($ratings), 1) : '—';

$interestRequests = [];
$interestCount = 0;
$matchCount = 0;
if (!empty($businesses)) {
    $ids = array_column($businesses, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $irStmt = db()->prepare("SELECT COUNT(*) FROM interest_requests WHERE business_id IN ($placeholders)");
    $irStmt->execute($ids);
    $interestCount = (int)$irStmt->fetchColumn();

    $mStmt = db()->prepare("SELECT COUNT(*) FROM matches WHERE context_type = 'business' AND context_id IN ($placeholders) AND closed_status = 'open'");
    $mStmt->execute($ids);
    $matchCount = (int)$mStmt->fetchColumn();

    $irDetailStmt = db()->prepare("
        SELECT ir.*, u.name AS sender_name, u.role AS sender_role, b.business_name,
               b.rating AS business_rating
        FROM interest_requests ir
        JOIN users u ON u.id = ir.sender_id
        JOIN businesses b ON b.id = ir.business_id
        WHERE ir.business_id IN ($placeholders) AND ir.status = 'pending'
        ORDER BY ir.created_at DESC
    ");
    $irDetailStmt->execute($ids);
    $interestRequests = $irDetailStmt->fetchAll();
}

$topAskingPrice = !empty($askingPrices) ? max($askingPrices) : 0;

$pageTitle = 'Business Owner Dashboard';
require __DIR__ . '/../includes/layout-dashboard.php';
?>
<h2 style="margin-bottom:0.25rem;">Business Dashboard</h2>
<p style="color:var(--color-text-muted);">Manage your business listings and track performance.</p>

<?php if (empty($businesses)): ?>
<div class="card" style="text-align:center;padding:3rem 2rem;margin-top:1.5rem;">
  <h3 style="margin-bottom:0.5rem;">No business listings yet</h3>
  <p style="color:var(--color-text-muted);margin-bottom:1rem;">Create your first business listing to start connecting with investors and buyers.</p>
  <a href="create.php" class="btn btn-primary">Create Business Listing</a>
</div>
<?php else: ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
  <div></div>
  <a href="create.php" class="btn btn-primary">+ New Business</a>
</div>

<div class="stats-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem;">
  <div class="stat-card"><div class="stat-value"><?= $totalViews ?></div><div class="stat-label">Profile views</div></div>
  <div class="stat-card"><div class="stat-value"><?= $interestCount ?></div><div class="stat-label">Interest requests</div></div>
  <div class="stat-card"><div class="stat-value"><?= $matchCount ?></div><div class="stat-label">Accepted matches</div></div>
  <div class="stat-card"><div class="stat-value"><?= $topAskingPrice ? money($topAskingPrice) : '—' ?></div><div class="stat-label">Asking price</div></div>
</div>

<h3>Your Listings</h3>
<div class="card" style="padding:0;">
  <table style="width:100%;border-collapse:collapse;">
    <tr style="border-bottom:1px solid var(--color-border);background:var(--color-bg-soft);">
      <th style="text-align:left;padding:14px 18px;font-weight:600;">Business</th>
      <th style="text-align:left;padding:14px 18px;font-weight:600;">Type</th>
      <th style="text-align:left;padding:14px 18px;font-weight:600;">Asking Price</th>
      <th style="text-align:center;padding:14px 18px;font-weight:600;">Status</th>
      <th style="text-align:center;padding:14px 18px;font-weight:600;">Views</th>
      <th style="text-align:right;padding:14px 18px;font-weight:600;"></th>
    </tr>
    <?php foreach ($businesses as $b): ?>
    <tr style="border-bottom:1px solid var(--color-border);">
      <td style="padding:14px 18px;">
        <strong><?= e($b['business_name']) ?></strong>
        <?php if ($b['is_featured']): ?><span class="tx-badge tx-badge-sale" style="font-size:0.7rem;margin-left:0.5rem;">Featured</span><?php endif; ?>
      </td>
      <td style="padding:14px 18px;"><?= e(ucfirst(str_replace('_', ' ', $b['listing_type']))) ?></td>
      <td style="padding:14px 18px;"><?= $b['asking_price'] ? money($b['asking_price']) : '—' ?></td>
      <td style="padding:14px 18px;text-align:center;">
        <?php if ($b['is_published']): ?><span style="color:var(--color-success);font-weight:600;">Published</span>
        <?php else: ?><span style="color:var(--color-error);font-weight:600;">Draft</span>
        <?php endif; ?>
      </td>
      <td style="padding:14px 18px;text-align:center;"><?= (int)$b['views'] ?></td>
      <td style="padding:14px 18px;text-align:right;">
        <a href="<?= APP_URL ?>/business/<?= $b['id'] ?>" class="btn btn-sm btn-secondary" style="text-decoration:none;">View</a>
        <a href="edit.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-accent" style="text-decoration:none;">Edit</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

<?php if (!empty($interestRequests)): ?>
<h3 style="margin-top:2rem;">Interest Requests</h3>
<div class="card" style="padding:0;">
  <table style="width:100%;border-collapse:collapse;">
    <tr style="border-bottom:1px solid var(--color-border);background:var(--color-bg-soft);">
      <th style="text-align:left;padding:14px 18px;font-weight:600;">Investor / Buyer</th>
      <th style="text-align:left;padding:14px 18px;font-weight:600;">Business</th>
      <th style="text-align:left;padding:14px 18px;font-weight:600;">Message</th>
      <th style="padding:14px 18px;font-weight:600;">Date</th>
      <th style="padding:14px 18px;font-weight:600;"></th>
    </tr>
    <?php foreach ($interestRequests as $ir): ?>
    <tr style="border-bottom:1px solid var(--color-border);">
      <td style="padding:14px 18px;">
        <strong><?= e($ir['sender_name']) ?></strong><br>
        <span class="text-xs"><?= e(ucfirst(str_replace('_', ' ', $ir['sender_role'] ?? ''))) ?></span>
      </td>
      <td style="padding:14px 18px;font-size:0.9rem;"><?= e($ir['business_name']) ?></td>
      <td style="padding:14px 18px;font-size:0.9rem;"><?= e($ir['message'] ?? '—') ?></td>
      <td style="padding:14px 18px;font-size:0.85rem;color:var(--color-text-muted);"><?= date_human($ir['created_at']) ?></td>
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
