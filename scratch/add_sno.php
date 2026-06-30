<?php
require_once 'includes/config.php';
try {
    $pdo->exec("ALTER TABLE members ADD COLUMN IF NOT EXISTS sno INT AFTER id");
    echo "Success: sno column added to members table.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
