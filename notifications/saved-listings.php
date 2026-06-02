<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

$user = current_user();
$userId = (int)$user['id'];

$db = db();
$items = $db->prepare('
    SELECT sl.listing_type, sl.listing_id, sl.created_at,
           b.business_name, b.annual_revenue, b.asking_price,
           p.tagline, p.funding_amount,
           f.brand_name, f.franchise_fee
    FROM saved_listings sl
    LEFT JOIN businesses b ON sl.listing_type = \'business\' AND sl.listing_id = b.id
    LEFT JOIN pitches p ON sl.listing_type = \'pitch\' AND sl.listing_id = p.id
    LEFT JOIN franchises f ON sl.listing_type = \'franchise\' AND sl.listing_id = f.id
    WHERE sl.user_id = ?
    ORDER BY sl.created_at DESC
');
$items->execute([$userId]);
$allItems = $items->fetchAll();

$pageTitle = 'Saved Listings';
require __DIR__ . '/../includes/layout-dashboard.php';

ui_page_header('Saved Listings', 'Listings you&rsquo;ve bookmarked to revisit later.');
?>

<?php if (empty($allItems)): ?>
  <div class="dash-panel">
    <?php ui_empty_state(['icon' => 'heart', 'title' => 'No saved listings yet', 'text' => 'Bookmark businesses, pitches and franchises to find them here.', 'ctaHref' => APP_URL . '/browse/businesses', 'ctaLabel' => 'Browse businesses']); ?>
  </div>
<?php else: ?>
  <div class="dash-panel dash-list">
    <?php foreach ($allItems as $item): ?>
      <?php
      $type = $item['listing_type'];
      $detailUrl = APP_URL . '/' . $type . '/' . (int)$item['listing_id'];
      if ($type === 'business'):
          $title = e($item['business_name'] ?? '');
          $info = money($item['annual_revenue'] ?? 0) . ' revenue &middot; ' . money($item['asking_price'] ?? 0) . ' asking';
      elseif ($type === 'pitch'):
          $title = e($item['tagline'] ?? '');
          $info = 'Seeking ' . money($item['funding_amount'] ?? 0);
      elseif ($type === 'franchise'):
          $title = e($item['brand_name'] ?? '');
          $info = 'Franchise fee: ' . money($item['franchise_fee'] ?? 0);
      else:
          continue;
      endif;
      ?>
      <div class="dash-listrow">
        <div class="dash-listrow-main">
          <span class="dash-pill open" style="margin-bottom:6px;"><?= e(ucfirst($type)) ?></span>
          <div class="dash-listrow-title"><?= $title ?></div>
          <div class="dash-listrow-sub"><?= $info ?></div>
        </div>
        <div class="dash-listrow-actions">
          <span class="dash-tl-time">Saved <?= date_human($item['created_at']) ?></span>
          <a href="<?= $detailUrl ?>" class="btn btn-sm btn-primary">View</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
