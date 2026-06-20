<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$user = require_api_role('investor');
$userId = (int)$user['id'];

$input = get_json_input();

$db = db();

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
foreach (['bio', 'phone', 'province', 'district', 'company_name'] as $f) {
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
