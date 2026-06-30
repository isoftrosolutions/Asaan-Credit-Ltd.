<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$method = $_SERVER['REQUEST_METHOD'];

$user = require_api_role('investor');
$userId = (int)$user['id'];

$db = db();

if ($method === 'GET') {
    $stmt = $db->prepare('
        SELECT u.bio, u.phone, u.province, u.district, u.company_name, u.investor_type, u.designation,
               ip.past_investments, ip.portfolio_companies, ip.total_capital_deployed,
               ip.preferred_sectors, ip.preferred_stages, ip.ticket_min, ip.ticket_max, ip.preferred_geography
        FROM users u
        LEFT JOIN investor_profiles ip ON ip.user_id = u.id
        WHERE u.id = ?
    ');
    $stmt->execute([$userId]);
    $profile = $stmt->fetch();
    if ($profile) {
        $profile['past_investments'] = (int)($profile['past_investments'] ?? 0);
        json_success($profile);
    } else {
        json_success([
            'bio' => $user['bio'] ?? null,
            'phone' => $user['phone'] ?? null,
            'province' => $user['province'] ?? null,
            'district' => $user['district'] ?? null,
            'company_name' => $user['company_name'] ?? null,
            'investor_type' => $user['investor_type'] ?? null,
            'designation' => $user['designation'] ?? null,
            'past_investments' => 0,
            'portfolio_companies' => null,
            'total_capital_deployed' => null,
        ]);
    }
}

if ($method === 'PUT' || $method === 'POST') {
    $input = get_json_input();

    $stmt = $db->prepare('UPDATE investor_profiles SET
        past_investments = ?, portfolio_companies = ?, total_capital_deployed = ?,
        updated_at = NOW()
        WHERE user_id = ?');
    $stmt->execute([
        $input['past_investments'] ?? null,
        $input['portfolio_companies'] ?? null,
        $input['total_capital_deployed'] ?? null,
        $userId,
    ]);

    $userFields = [];
    foreach (['bio', 'phone', 'province', 'district', 'company_name', 'investor_type', 'designation'] as $f) {
        if (array_key_exists($f, $input)) {
            $userFields[$f] = $input[$f];
        }
    }

    if (!empty($userFields)) {
        $userSets = [];
        $userParams = [];
        foreach ($userFields as $col => $val) {
            $userSets[] = "$col = ?";
            $userParams[] = $val;
        }
        $userParams[] = $userId;
        $db->prepare('UPDATE users SET ' . implode(', ', $userSets) . ', updated_at = NOW() WHERE id = ?')
           ->execute($userParams);
    }

    json_success(['updated' => true]);
}

json_error('Method not allowed', 405);
