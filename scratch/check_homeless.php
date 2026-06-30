<?php
require_once dirname(__DIR__) . '/includes/config.php';
try {
    $stmt = $pdo->query("SELECT id, family_number, address FROM families WHERE is_homeless = 1");
    $families = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Families count: " . count($families) . "\n";
    foreach ($families as $f) {
        $stmtM = $pdo->prepare("SELECT full_name FROM members WHERE family_id = ?");
        $stmtM->execute([$f['id']]);
        $members = $stmtM->fetchAll(PDO::FETCH_ASSOC);
        echo "Family ID: {$f['id']}, FamNo: {$f['family_number']}, Members: " . count($members) . "\n";
        foreach ($members as $m) {
            echo "  - " . $m['full_name'] . "\n";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
