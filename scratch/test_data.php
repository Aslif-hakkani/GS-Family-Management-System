<?php
require_once 'includes/config.php';
$stmt = $pdo->query("SELECT * FROM persons LIMIT 10");
$persons = $stmt->fetchAll();
print_r($persons);

$stmt2 = $pdo->query("SELECT * FROM person_page_records LIMIT 10");
$records = $stmt2->fetchAll();
print_r($records);
