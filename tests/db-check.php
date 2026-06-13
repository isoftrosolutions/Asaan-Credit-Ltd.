<?php
require __DIR__ . '/../config/bootstrap.php';
try {
    $count = db()->query("SELECT COUNT(*) FROM businesses")->fetchColumn();
    echo "Total businesses: " . $count . "\n";
    $approved = db()->query("SELECT COUNT(*) FROM businesses WHERE status = 'approved'")->fetchColumn();
    echo "Approved businesses: " . $approved . "\n";
    $featured = db()->query("SELECT COUNT(*) FROM businesses WHERE status = 'approved' AND is_featured = 1")->fetchColumn();
    echo "Featured businesses: " . $featured . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
