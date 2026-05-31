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
?>

<div class="saved-listings-page">
    <h2>Saved Listings</h2>

    <?php if (empty($allItems)): ?>
        <div class="card">
            <p style="text-align:center; padding:2rem; color:var(--color-text-muted);">You haven't saved any listings yet. <a href="<?= APP_URL ?>/browse/businesses">Browse businesses</a></p>
        </div>
    <?php else: ?>
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
            $badge = ucfirst($type);
            ?>
            <div class="card" style="margin-bottom:1rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem;">
                    <div style="flex:1;">
                        <span class="badge badge-accent" style="margin-bottom:0.25rem;"><?= $badge ?></span>
                        <strong style="display:block; font-size:1.05rem;"><?= $title ?></strong>
                        <span style="font-size:0.85rem; color:var(--color-text-muted);"><?= $info ?></span>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.75rem; flex-shrink:0;">
                        <span style="font-size:0.8rem; color:var(--color-text-muted);">Saved <?= date_human($item['created_at']) ?></span>
                        <a href="<?= $detailUrl ?>" class="btn btn-sm btn-primary">View</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div style="margin-top:2rem;">
        <a href="<?= APP_URL ?>/dashboard" class="btn btn-sm btn-secondary">&larr; Back to Dashboard</a>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
