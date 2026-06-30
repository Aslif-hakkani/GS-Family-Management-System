<?php
require_once 'includes/config.php';

try {
    // Check current types
    $stmt = $pdo->query("DESCRIBE members");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $types = [];
    foreach ($columns as $col) {
        $types[$col['Field']] = $col['Type'];
    }

    echo "Current types:\n";
    print_r($types);

    // Update columns if they are still tinyint
    $queries = [
        "ALTER TABLE members MODIFY aswesuma VARCHAR(100) DEFAULT '0'",
        "ALTER TABLE members MODIFY kidney_disease VARCHAR(100) DEFAULT '0'",
        "ALTER TABLE members MODIFY disabled VARCHAR(100) DEFAULT '0'"
    ];

    foreach ($queries as $q) {
        echo "Executing: $q ... ";
        $pdo->exec($q);
        echo "Done.\n";
    }

    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
