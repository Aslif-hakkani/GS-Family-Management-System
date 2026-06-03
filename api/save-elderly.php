<?php
require_once '../includes/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../elderly.php');
}

$sno              = $_POST['sno'] ?? null;
$name             = trim($_POST['name'] ?? '');
$house_no         = $_POST['house_no'] ?? '';
$address          = trim($_POST['address'] ?? '');
$gender           = $_POST['gender'] ?? 'Male';
$nic              = $_POST['nic'] ?? '';
$dob              = $_POST['dob'] ?? null;
$age              = (int)($_POST['age'] ?? 0);
$occupation       = $_POST['occupation'] ?? '';
$contact          = $_POST['contact'] ?? '';
$aswesuma         = $_POST['aswesuma'] ?? '0';
$elder            = $_POST['elder'] ?? '5000';
$person_house_no  = $_POST['person_house_no'] ?? '';
$health           = $_POST['health'] ?? '0';

if (empty($name) || empty($address)) {
    die("Name and Address are required.");
}

try {
    $pdo->beginTransaction();

    // 1. Check if person already exists (NIC or Name+DOB)
    $stmtCheckPerson = $pdo->prepare("SELECT id FROM persons WHERE (nic = ? AND nic != '') OR (full_name = ? AND dob = ?)");
    $stmtCheckPerson->execute([$nic, $name, !empty($dob) ? $dob : null]);
    $existingPerson = $stmtCheckPerson->fetch();

    if ($existingPerson) {
        $person_id = $existingPerson['id'];
        // Update person details if they changed
        $stmtUpdatePerson = $pdo->prepare("UPDATE persons SET gender = ?, age = ?, occupation = ?, contact_number = ?, person_house_number = ? WHERE id = ?");
        $stmtUpdatePerson->execute([$gender, $age, $occupation, $contact, $person_house_no, $person_id]);
    } else {
        $stmtInsPerson = $pdo->prepare("INSERT INTO persons (full_name, nic, gender, dob, age, occupation, contact_number, person_house_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtInsPerson->execute([$name, $nic ?: null, $gender, !empty($dob) ? $dob : null, $age, $occupation, $contact, $person_house_no]);
        $person_id = $pdo->lastInsertId();
    }

    $family_number = $_POST['family_number'] ?? '';
    // Auto-generate family_code if missing
    if (empty($family_number)) {
        $family_code = 'ELD-' . date('Ymd') . '-' . substr(uniqid(), -5);
    } else {
        $family_code = $family_number;
    }

    $member_count = ($_POST['member_count'] !== '') ? (int)$_POST['member_count'] : null;

    $stmtFam = $pdo->prepare("INSERT INTO families 
        (sno, family_code, family_number, house_number, address, contact_no, income_level, member_count, page_category) 
        VALUES (?, ?, ?, ?, ?, ?, 'Elderly Entry', ?, 'elderly')");
    $stmtFam->execute([$sno ?: null, $family_code, $family_number, $house_no, $address, $contact, $member_count]);
    $family_id = $pdo->lastInsertId();

    // 2. Insert Page Record
    $stmtRec = $pdo->prepare("INSERT INTO person_page_records 
        (person_id, family_id, page_category, person_sno, aswesuma, kidney_disease, disabled, is_elder) 
        VALUES (?, ?, 'elderly', ?, ?, ?, ?, ?)");
    
    $stmtRec->execute([
        $person_id, $family_id, $sno ?: null, 
        $aswesuma, $health, $health, $elder
    ]);

    $pdo->commit();
    redirect('../add-elderly.php?success=1');
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Error saving entry: " . $e->getMessage());
}
?>
