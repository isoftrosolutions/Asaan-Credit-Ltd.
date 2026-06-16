<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

$userId = (int)current_user()['id'];
$db = db();

// Quick count mode
if (isset($_GET['count'])) {
    $stmt = $db->prepare('SELECT COUNT(*) FROM saved_listings WHERE user_id = ?');
    $stmt->execute([$userId]);
    echo json_encode(['count' => (int)$stmt->fetchColumn()]);
    exit;
}

$items = $db->prepare('
    SELECT sl.listing_type, sl.listing_id, sl.created_at,
           b.business_name, b.asking_price, b.annual_revenue,
           p.tagline, p.funding_amount, p.equity_offered,
           f.brand_name, f.franchise_fee
    FROM saved_listings sl
    LEFT JOIN businesses b ON sl.listing_type = \'business\' AND sl.listing_id = b.id
    LEFT JOIN pitches p ON sl.listing_type = \'pitch\' AND sl.listing_id = p.id
    LEFT JOIN franchises f ON sl.listing_type = \'franchise\' AND sl.listing_id = f.id
    WHERE sl.user_id = ?
    ORDER BY sl.created_at DESC
');
$items->execute([$userId]);
$rows = $items->fetchAll();

$result = [];
foreach ($rows as $row) {
    $type = $row['listing_type'];
    $url = APP_URL . '/' . $type . '/' . (int)$row['listing_id'];

    switch ($type) {
        case 'business':
            $title = e($row['business_name'] ?? 'Untitled Business');
            $info = money($row['annual_revenue'] ?? 0) . ' revenue &middot; ' . money($row['asking_price'] ?? 0) . ' asking';
            $typeLabel = 'Business';
            break;
        case 'pitch':
            $title = e($row['tagline'] ?? 'Untitled Pitch');
            $info = 'Seeking ' . money($row['funding_amount'] ?? 0);
            if (!empty($row['equity_offered'])) $info .= ' &middot; ' . e($row['equity_offered']) . '% equity';
            $typeLabel = 'Pitch';
            break;
        case 'franchise':
            $title = e($row['brand_name'] ?? 'Untitled Franchise');
            $info = 'Fee: ' . money($row['franchise_fee'] ?? 0);
            $typeLabel = 'Franchise';
            break;
        default:
            continue 2;
    }

    $result[] = [
        'type'      => $type,
        'type_label'=> $typeLabel,
        'title'     => $title,
        'info'      => $info,
        'url'       => $url,
        'since'     => date_human($row['created_at']),
    ];
}

echo json_encode(['count' => count($result), 'items' => $result]);
