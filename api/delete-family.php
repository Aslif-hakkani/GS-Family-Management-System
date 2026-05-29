<?php
require_once '../includes/config.php';
check_auth();

$id = $_GET['id'] ?? 0;

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM families WHERE id = ?");
        $stmt->execute([$id]);
        
        // Redirect back to or search.php as fallback
        $referer = $_SERVER['HTTP_REFERER'] ?? '../search.php';
        // Ensure we don't accidentally redirect to the delete script itself
        if (strpos($referer, 'delete-family.php') !== false) {
            $referer = '../search.php';
        }
        header("Location: " . $referer);
        exit();
    } catch (PDOException $e) {
        die("Error deleting record: " . $e->getMessage());
    }
} else {
    $referer = $_SERVER['HTTP_REFERER'] ?? '../search.php';
    header("Location: " . $referer);
    exit();
}
?>
