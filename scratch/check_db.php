<?php
require '../includes/config.php';

$tables = ['families', 'persons', 'person_page_records'];
foreach ($tables as $t) {
    echo "=== $t ===\n";
    $stmt = $pdo->query("DESCRIBE $t");
    foreach ($stmt->fetchAll() as $col) {
        echo "  {$col['Field']} ({$col['Type']})\n";
    }
}

echo "\n=== families.page_category values ===\n";
$stmt = $pdo->query("SELECT DISTINCT page_category, COUNT(*) as cnt FROM families GROUP BY page_category");
foreach ($stmt->fetchAll() as $r) echo "  '{$r['page_category']}': {$r['cnt']}\n";

echo "\n=== person_page_records.page_category values ===\n";
$stmt = $pdo->query("SELECT DISTINCT page_category, COUNT(*) as cnt FROM person_page_records GROUP BY page_category");
foreach ($stmt->fetchAll() as $r) echo "  '{$r['page_category']}': {$r['cnt']}\n";

echo "\n=== Sample JOIN test (widow) ===\n";
$stmt = $pdo->query("SELECT COUNT(*) as total FROM person_page_records r JOIN families f ON r.family_id = f.id JOIN persons p ON r.person_id = p.id WHERE f.page_category = 'widow'");
echo "  widow records (join on families): " . $stmt->fetchColumn() . "\n";

$stmt = $pdo->query("SELECT COUNT(*) as total FROM person_page_records r JOIN families f ON r.family_id = f.id JOIN persons p ON r.person_id = p.id WHERE r.page_category = 'widow'");
echo "  widow records (join on person_page_records): " . $stmt->fetchColumn() . "\n";
