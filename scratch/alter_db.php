<?php
require_once dirname(__DIR__) . '/includes/config.php';
try {
    $pdo->exec("ALTER TABLE families ADD COLUMN signature VARCHAR(255) DEFAULT NULL;");
    echo "Column added successfully or already exists.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
