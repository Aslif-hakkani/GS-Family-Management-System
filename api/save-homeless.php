<?php
require_once '../includes/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../homeless.php');
}

$sno       = $_POST['sno'] ?? null;
$family_no = $_POST['family_no'] ?? '';
$name      = trim($_POST['name'] ?? '');
$nic_no    = $_POST['nic_no'] ?? '';
$house_no  = $_POST['house_no'] ?? '';
$road      = $_POST['road'] ?? '';
$occupation = $_POST['occupation'] ?? '';
$phone_no  = $_POST['phone_no'] ?? '';
$situation = $_POST['situation'] ?? '';
$aswesuma  = $_POST['aswesuma'] ?? '';
$remarks   = $_POST['remarks'] ?? '';

if (empty($name)) {
    die("Name is required.");
}

// Derive address from house number + road
$address = trim(($house_no ? $house_no . ', ' : '') . $road);
if (empty($address)) $address = 'N/A';

try {
    $pdo->beginTransaction();

    // 1. Check if person already exists (NIC or Name)
    $stmtCheckPerson = $pdo->prepare("SELECT id FROM persons WHERE (nic = ? AND nic != '') OR (full_name = ?)");
    $stmtCheckPerson->execute([$nic_no, $name]);
    $existingPerson = $stmtCheckPerson->fetch();

    if ($existingPerson) {
        $person_id = $existingPerson['id'];
        // Update person details if they changed
        $stmtUpdatePerson = $pdo->prepare("UPDATE persons SET occupation = ?, contact_number = ? WHERE id = ?");
        $stmtUpdatePerson->execute([$occupation, $phone_no, $person_id]);
    } else {
        $stmtInsPerson = $pdo->prepare("INSERT INTO persons (full_name, nic, occupation, contact_number) VALUES (?, ?, ?, ?)");
        $stmtInsPerson->execute([$name, $nic_no ?: null, $occupation, $phone_no]);
        $person_id = $pdo->lastInsertId();
    }

    $family_code = $family_no;
    $stmtFam = $pdo->prepare("INSERT INTO families 
        (sno, family_code, family_number, house_number, road, address, contact_no, housing_condition, remarks, is_homeless, member_count, page_category) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1, 'homeless')");
    $stmtFam->execute([$sno ?: null, $family_code, $family_no, $house_no, $road, $address, $phone_no, $situation, $remarks]);
    $family_id = $pdo->lastInsertId();

    // 2. Insert Page Record
    $stmtRec = $pdo->prepare("INSERT INTO person_page_records 
        (person_id, family_id, page_category, person_sno, aswesuma) 
        VALUES (?, ?, 'homeless', ?, ?)");
    $stmtRec->execute([$person_id, $family_id, $sno ?: null, $aswesuma]);

    $pdo->commit();
    redirect('../add-homeless.php?success=1');
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Error saving entry: " . $e->getMessage());
}
?>
