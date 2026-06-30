<?php
// Add page_category column to families table to enable full page isolation
require_once dirname(__DIR__) . '/includes/config.php';
try {
    $pdo->exec("ALTER TABLE families ADD COLUMN page_category VARCHAR(30) DEFAULT 'general'");
    echo "Column 'page_category' added.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Column already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Backfill existing rows based on income_level used during uploads
$pdo->exec("UPDATE families SET page_category = 'widow'    WHERE income_level = 'Widow Upload'    AND page_category = 'general'");
$pdo->exec("UPDATE families SET page_category = 'elderly'  WHERE income_level = 'Elderly Upload'  AND page_category = 'general'");
$pdo->exec("UPDATE families SET page_category = 'disaster' WHERE income_level = 'Disaster Upload' AND page_category = 'general'");
$pdo->exec("UPDATE families SET page_category = 'homeless' WHERE is_homeless = 1                  AND page_category = 'general'");
$pdo->exec("UPDATE families SET page_category = 'disaster' WHERE is_disaster = 1                  AND page_category = 'general'");

// Pregnant: created via pregnant-upload-process, mark those
$pdo->exec("UPDATE families SET page_category = 'pregnant'
            WHERE id IN (
                SELECT DISTINCT family_id FROM members WHERE is_pregnant = 1
            ) AND page_category = 'general' AND income_level NOT IN ('Widow Upload','Elderly Upload','Disaster Upload')
            AND is_homeless = 0 AND is_disaster = 0");

echo "Backfill complete.\n";
$r = $pdo->query("SELECT page_category, COUNT(*) as cnt FROM families GROUP BY page_category")->fetchAll(PDO::FETCH_ASSOC);
foreach ($r as $row) echo "  " . $row['page_category'] . ": " . $row['cnt'] . "\n";
