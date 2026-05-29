<?php
require __DIR__ . '/../config/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

require_login();
require_verified();

$user = current_user();
$userId = $user['id'];
$limit = min(50, max(1, (int)($_GET['limit'] ?? 10)));

$cachedStmt = db()->prepare(
    "SELECT target_type, target_id, match_score, score_breakdown
     FROM smart_suggestion_cache
     WHERE user_id = ? AND cached_until > NOW()
     ORDER BY match_score DESC
     LIMIT ?"
);
$cachedStmt->execute([$userId, $limit]);
$cached = $cachedStmt->fetchAll();

if (count($cached) >= $limit) {
    $results = [];
    foreach ($cached as $row) {
        $results[] = [
            'target_type' => $row['target_type'],
            'target_id' => (int)$row['target_id'],
            'match_score' => (float)$row['match_score'],
            'score_breakdown' => json_decode($row['score_breakdown'], true),
        ];
    }
    echo json_encode(['suggestions' => $results, 'cached' => true, 'source' => 'cache']);
    exit;
}

$role = $user['role'];
$suggestions = [];
$cachedUntil = date('Y-m-d H:i:s', strtotime('+24 hours'));

if ($role === 'investor') {
    $ipStmt = db()->prepare("SELECT * FROM investor_profiles WHERE user_id = ?");
    $ipStmt->execute([$userId]);
    $profile = $ipStmt->fetch();

    if (!$profile) {
        echo json_encode(['suggestions' => [], 'cached' => false, 'error' => 'Complete your investor profile first.']);
        exit;
    }

    $preferredSectors = json_decode($profile['preferred_sectors'] ?? '[]', true) ?: [];
    $preferredStages = json_decode($profile['preferred_stages'] ?? '[]', true) ?: [];
    $ticketMin = (float)($profile['ticket_min'] ?? 0);
    $ticketMax = (float)($profile['ticket_max'] ?? 999999999);

    $suggestions = array_merge(
        scoreTargets($userId, 'business', $preferredSectors, $preferredStages, $ticketMin, $ticketMax, $cachedUntil),
        scoreTargets($userId, 'pitch', $preferredSectors, $preferredStages, $ticketMin, $ticketMax, $cachedUntil)
    );

} elseif (in_array($role, ['entrepreneur', 'business_owner', 'franchisor'])) {
    $userSectorId = null;
    $userStage = null;
    $userFundingMin = 0;
    $userFundingMax = 999999999;

    if ($role === 'entrepreneur') {
        $pStmt = db()->prepare("SELECT sector_id, stage, funding_amount FROM pitches WHERE user_id = ? AND is_published = 1 LIMIT 1");
        $pStmt->execute([$userId]);
        $pitch = $pStmt->fetch();
        if ($pitch) {
            $userSectorId = $pitch['sector_id'];
            $userStage = $pitch['stage'];
            if ($pitch['funding_amount']) {
                $userFundingMin = (float)$pitch['funding_amount'] * 0.5;
                $userFundingMax = (float)$pitch['funding_amount'] * 2;
            }
        }
    } elseif ($role === 'business_owner') {
        $bStmt = db()->prepare("SELECT sector_id, asking_price FROM businesses WHERE user_id = ? AND is_published = 1 LIMIT 1");
        $bStmt->execute([$userId]);
        $business = $bStmt->fetch();
        if ($business) {
            $userSectorId = $business['sector_id'];
            if ($business['asking_price']) {
                $userFundingMin = (float)$business['asking_price'] * 0.5;
                $userFundingMax = (float)$business['asking_price'] * 2;
            }
        }
    }

    if ($userSectorId) {
        $sectorStmt = db()->query("SELECT id, name FROM sectors WHERE id = $userSectorId");
        $sector = $sectorStmt->fetch();
        $sectorName = $sector ? $sector['name'] : '';

        $invStmt = db()->prepare(
            "SELECT u.id as user_id, ip.*
             FROM users u
             JOIN investor_profiles ip ON u.id = ip.user_id
             WHERE u.role = 'investor' AND u.verification_status = 'verified'"
        );
        $invStmt->execute();
        $investors = $invStmt->fetchAll();

        foreach ($investors as $inv) {
            $preferredSectors = json_decode($inv['preferred_sectors'] ?? '[]', true) ?: [];
            $preferredStages = json_decode($inv['preferred_stages'] ?? '[]', true) ?: [];
            $invTicketMin = (float)($inv['ticket_min'] ?? 0);
            $invTicketMax = (float)($inv['ticket_max'] ?? 999999999);

            $score = computeScore($sectorName, $preferredSectors, $userStage, $preferredStages, $userFundingMin, $userFundingMax, $invTicketMin, $invTicketMax);

            if ($score > 0) {
                $suggestions[] = [
                    'target_type' => 'investor',
                    'target_id' => (int)$inv['user_id'],
                    'match_score' => $score,
                    'score_breakdown' => json_encode([
                        'sector' => round(sectorOverlap($sectorName, $preferredSectors) * 40, 1),
                        'stage' => round(stageOverlap($userStage, $preferredStages) * 30, 1),
                        'budget' => round(budgetOverlap($userFundingMin, $userFundingMax, $invTicketMin, $invTicketMax) * 30, 1),
                    ]),
                ];
            }
        }
    }
}

usort($suggestions, function ($a, $b) {
    return $b['match_score'] <=> $a['match_score'];
});

$suggestions = array_slice($suggestions, 0, $limit);

$insertStmt = db()->prepare(
    "INSERT INTO smart_suggestion_cache (user_id, target_type, target_id, match_score, score_breakdown, cached_until)
     VALUES (?, ?, ?, ?, ?, ?)"
);
foreach ($suggestions as $s) {
    $insertStmt->execute([
        $userId,
        $s['target_type'],
        $s['target_id'],
        $s['match_score'],
        $s['score_breakdown'],
        $cachedUntil,
    ]);
}

$results = [];
foreach ($suggestions as $s) {
    $results[] = [
        'target_type' => $s['target_type'],
        'target_id' => $s['target_id'],
        'match_score' => $s['match_score'],
        'score_breakdown' => json_decode($s['score_breakdown'], true),
    ];
}

echo json_encode(['suggestions' => $results, 'cached' => false, 'source' => 'fresh']);

function scoreTargets(int $userId, string $type, array $preferredSectors, array $preferredStages, float $ticketMin, float $ticketMax, string $cachedUntil): array {
    $results = [];

    if ($type === 'business') {
        $sql = "SELECT b.*, s.name as sector_name
                FROM businesses b
                LEFT JOIN sectors s ON b.sector_id = s.id
                WHERE b.is_published = 1 AND b.is_hidden = 0
                  AND b.user_id != ?";
    } else {
        $sql = "SELECT p.*, s.name as sector_name
                FROM pitches p
                LEFT JOIN sectors s ON p.sector_id = s.id
                WHERE p.is_published = 1 AND p.is_hidden = 0
                  AND p.user_id != ?";
    }

    $stmt = db()->prepare($sql);
    $stmt->execute([$userId]);
    $targets = $stmt->fetchAll();

    foreach ($targets as $t) {
        $targetSector = $t['sector_name'] ?? '';
        $targetStage = $t['stage'] ?? '';
        $targetAmount = (float)($t['funding_amount'] ?? $t['asking_price'] ?? 0);

        $score = computeScore($targetSector, $preferredSectors, $targetStage, $preferredStages, $targetAmount, $targetAmount, $ticketMin, $ticketMax);

        if ($score > 0) {
            $results[] = [
                'target_type' => $type,
                'target_id' => (int)$t['id'],
                'match_score' => $score,
                'score_breakdown' => json_encode([
                    'sector' => round(sectorOverlap($targetSector, $preferredSectors) * 40, 1),
                    'stage' => round(stageOverlap($targetStage, $preferredStages) * 30, 1),
                    'budget' => round(budgetOverlap(max($ticketMin, $targetAmount), min($ticketMax, $targetAmount), $ticketMin, $ticketMax) * 30, 1),
                ]),
            ];
        }
    }

    return $results;
}

function computeScore(string $targetSector, array $preferredSectors, ?string $targetStage, array $preferredStages, float $targetAmountMin, float $targetAmountMax, float $investorMin, float $investorMax): float {
    $sectorScore = sectorOverlap($targetSector, $preferredSectors);
    $stageScore = stageOverlap($targetStage, $preferredStages);
    $budgetScore = budgetOverlap($targetAmountMin, $targetAmountMax, $investorMin, $investorMax);

    return round($sectorScore * 40 + $stageScore * 30 + $budgetScore * 30, 1);
}

function sectorOverlap(string $targetSector, array $preferredSectors): float {
    if (empty($targetSector) || empty($preferredSectors)) return 0;
    return in_array($targetSector, $preferredSectors) ? 1.0 : 0.0;
}

function stageOverlap(?string $targetStage, array $preferredStages): float {
    if (empty($targetStage) || empty($preferredStages)) return 0;
    return in_array($targetStage, $preferredStages) ? 1.0 : 0.0;
}

function budgetOverlap(float $targetMin, float $targetMax, float $investorMin, float $investorMax): float {
    if ($investorMax <= 0 || $targetMax <= 0) return 0.5;
    $overlapMin = max($targetMin, $investorMin);
    $overlapMax = min($targetMax, $investorMax);
    if ($overlapMax < $overlapMin) return 0;
    $targetRange = $targetMax - $targetMin;
    $invRange = $investorMax - $investorMin;
    if ($targetRange <= 0 && $invRange <= 0) return ($investorMin >= $targetMin * 0.8 && $investorMin <= $targetMax * 1.2) ? 1.0 : 0;
    if ($targetRange <= 0) $targetRange = $targetMax * 0.5;
    if ($invRange <= 0) $invRange = $investorMax * 0.5;
    $union = max($targetMax, $investorMax) - min($targetMin, $investorMin);
    if ($union <= 0) return 0;
    $overlap = $overlapMax - $overlapMin;
    return min(1.0, $overlap / $union);
}
