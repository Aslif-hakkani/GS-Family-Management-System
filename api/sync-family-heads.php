<?php
/**
 * sync-family-heads.php
 * ─────────────────────────────────────────────────────────────────────────────
 * One-time (and safe to re-run) migration + sync tool:
 *   1. Adds `is_head` TINYINT(1) column to `person_page_records` if absent.
 *   2. Resets all is_head = 0.
 *   3. Sets is_head = 1 for the record with MIN(id) per family_id.
 *
 * Run via browser: http://localhost/family%20details/api/sync-family-heads.php
 * (Requires admin session)
 * ─────────────────────────────────────────────────────────────────────────────
 */
require_once '../includes/config.php';
session_start();
// Only allow admins
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

header('Content-Type: application/json');

try {
    // 1. Add column if it doesn't already exist
    $colCheck = $pdo->query("
        SELECT COUNT(*) as cnt
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'person_page_records'
          AND COLUMN_NAME  = 'is_head'
    ")->fetch();

    $columnAdded = false;
    if ((int)$colCheck['cnt'] === 0) {
        $pdo->exec("ALTER TABLE person_page_records ADD COLUMN is_head TINYINT(1) NOT NULL DEFAULT 0");
        $columnAdded = true;
    }

    // 2. Reset all to 0
    $pdo->exec("UPDATE person_page_records SET is_head = 0");

    // 3. Set is_head = 1 for the minimum id per family_id
    $pdo->exec("
        UPDATE person_page_records pr
        INNER JOIN (
            SELECT MIN(id) AS head_id, family_id
            FROM person_page_records
            GROUP BY family_id
        ) heads ON pr.id = heads.head_id
        SET pr.is_head = 1
    ");

    // Count how many were marked
    $markedCount = (int)$pdo->query("SELECT COUNT(*) FROM person_page_records WHERE is_head = 1")->fetchColumn();

    echo json_encode([
        'success'      => true,
        'column_added' => $columnAdded,
        'heads_marked' => $markedCount,
        'message'      => ($columnAdded ? 'is_head column created. ' : '') .
                          "Marked {$markedCount} family head(s) successfully."
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
