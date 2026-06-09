<?php
require __DIR__ . '/../config/bootstrap.php';

$db = db();

echo "=== Fixing industry tags ===\n";

// "Retail Pharmacy Chain" (id=3): sector_id 9 (Hospitality) -> 8 (Retail)
$db->prepare('UPDATE businesses SET sector_id = ? WHERE id = ? AND sector_id = ?')->execute([8, 3, 9]);
echo "Fixed Retail Pharmacy Chain: Hospitality -> Retail\n";

// "Hotel Equity Stake" (id=4): sector_id 11 (Technology) -> 9 (Hospitality)
$db->prepare('UPDATE businesses SET sector_id = ? WHERE id = ? AND sector_id = ?')->execute([9, 4, 11]);
echo "Fixed Hotel Equity Stake: Technology -> Hospitality\n";

// "Manufacturing Unit Expansion" (id=2): sector_id 8 (Retail) -> 7 (Manufacturing)
$db->prepare('UPDATE businesses SET sector_id = ? WHERE id = ? AND sector_id = ?')->execute([7, 2, 8]);
echo "Fixed Manufacturing Unit Expansion: Retail -> Manufacturing\n";

echo "\n=== Fixing FAQ text (InvestMatch -> Asaan Capital) ===\n";

$stmt = $db->query('SELECT id, question, answer FROM faqs');
while ($faq = $stmt->fetch()) {
    $newQ = str_replace('InvestMatch', 'Asaan Capital', $faq['question']);
    $newA = str_replace('InvestMatch', 'Asaan Capital', $faq['answer']);
    if ($newQ !== $faq['question'] || $newA !== $faq['answer']) {
        $db->prepare('UPDATE faqs SET question = ?, answer = ? WHERE id = ?')->execute([$newQ, $newA, $faq['id']]);
        echo "Updated FAQ id={$faq['id']}: '{$faq['question']}' -> '{$newQ}'\n";
    }
}

echo "\n=== Fixing H1 hero_title ===\n";
$db->prepare("UPDATE homepage_contents SET value = ? WHERE `key` = ?")->execute([
    'Connect with <span class="highlight">Investors</span>.<br>Sell or Grow Your Business <span class="highlight">Faster</span>.',
    'hero_title'
]);
echo "Updated hero_title in homepage_contents\n";

echo "\n=== All DB fixes complete ===\n";
