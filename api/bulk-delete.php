<?php
require_once '../includes/config.php';
session_start();

// Expecting JSON payload: { "ids": [1, 2, 3] }
$input = json_decode(file_get_contents('php://input'), true);
$ids = $input['ids'] ?? [];

if (empty($ids)) {
    echo json_encode(['success' => false, 'error' => 'No IDs provided.']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Prepare placeholders for IN clause
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    // Delete families (members will be deleted via ON DELETE CASCADE)
    $stmt = $pdo->prepare("DELETE FROM families WHERE id IN ($placeholders)");
    $stmt->execute($ids);

    $pdo->commit();
    echo json_encode(['success' => true, 'count' => count($ids)]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
