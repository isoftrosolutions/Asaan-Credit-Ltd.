<?php
require __DIR__ . '/../config/bootstrap.php';
$dbs = ['invest_match', 'asaancapital_assan_capital'];
foreach ($dbs as $dbName) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . $dbName . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, 'root', '');
        $count = $pdo->query("SELECT COUNT(*) FROM businesses")->fetchColumn();
        echo "Database $dbName: $count businesses\n";
        
        $latest = $pdo->query("SELECT id, business_name, status FROM businesses ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($latest as $b) {
            echo "  ID: {$b['id']}, Name: {$b['business_name']}, Status: {$b['status']}\n";
        }
    } catch (Exception $e) {
        echo "Database $dbName: Error - " . $e->getMessage() . "\n";
    }
}
