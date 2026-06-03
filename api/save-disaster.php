<?php
require_once '../includes/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../disaster.php');
}

$sno              = $_POST['sno'] ?? null;
$name             = trim($_POST['name'] ?? '');
$house_no         = $_POST['house_no'] ?? '';
$address          = trim($_POST['address'] ?? '');
$nic              = $_POST['nic'] ?? '';
$contact          = $_POST['contact'] ?? '';
$aswesuma         = $_POST['aswesuma'] ?? '0';
$situation       = $_POST['situation'] ?? '';
$remarks          = $_POST['remarks'] ?? '';

if (empty($name) || empty($address)) {
    die("Name and Address are required.");
}

try {
    $pdo->beginTransaction();

    // 1. Check if person already exists (NIC or Name)
    $stmtCheckPerson = $pdo->prepare("SELECT id FROM persons WHERE (nic = ? AND nic != '') OR (full_name = ?)");
    $stmtCheckPerson->execute([$nic, $name]);
    $existingPerson = $stmtCheckPerson->fetch();

    if ($existingPerson) {
        $person_id = $existingPerson['id'];
        // Update person details if they changed
        $stmtUpdatePerson = $pdo->prepare("UPDATE persons SET contact_number = ? WHERE id = ?");
        $stmtUpdatePerson->execute([$contact, $person_id]);
    } else {
        $stmtInsPerson = $pdo->prepare("INSERT INTO persons (full_name, nic, contact_number) VALUES (?, ?, ?)");
        $stmtInsPerson->execute([$name, $nic ?: null, $contact]);
        $person_id = $pdo->lastInsertId();
    }

    $family_number = $_POST['family_number'] ?? '';
    $family_code = $family_number;
    $stmtFam = $pdo->prepare("INSERT INTO families 
        (sno, family_code, family_number, house_number, address, contact_no, housing_condition, remarks, is_disaster, member_count, page_category) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 1, 'disaster')");
    $stmtFam->execute([$sno ?: null, $family_code, $family_number, $house_no, $address, $contact, $situation, $remarks]);
    $family_id = $pdo->lastInsertId();

    // 2. Insert Page Record
    $stmtRec = $pdo->prepare("INSERT INTO person_page_records 
        (person_id, family_id, page_category, person_sno, aswesuma) 
        VALUES (?, ?, 'disaster', ?, ?)");
    
    $stmtRec->execute([$person_id, $family_id, $sno ?: null, $aswesuma]);

    $pdo->commit();
    redirect('../add-disaster.php?success=1');
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Error saving entry: " . $e->getMessage());
}
?>
