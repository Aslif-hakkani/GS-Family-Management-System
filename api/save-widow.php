<?php
require_once '../includes/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../widows.php');
}

$sno              = $_POST['sno'] ?? null;
$name             = trim($_POST['name'] ?? '');
$house_no         = $_POST['house_no'] ?? '';
$address          = trim($_POST['address'] ?? '');
$member_count     = $_POST['member_count'] ?? 0;
$nic              = $_POST['nic'] ?? '';
$dob              = $_POST['dob'] ?? null;
$age              = $_POST['age'] ?? 0;
$occupation       = $_POST['occupation'] ?? '';
$contact          = $_POST['contact'] ?? '';
$person_house_no  = $_POST['person_house_no'] ?? '';
$aswesuma         = $_POST['aswesuma'] ?? '0';
$elder            = $_POST['elder'] ?? '0';
$pmam             = $_POST['pmam'] ?? 0;
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
        $stmtUpdatePerson = $pdo->prepare("UPDATE persons SET gender = 'Female', age = ?, occupation = ?, contact_number = ?, person_house_number = ? WHERE id = ?");
        $stmtUpdatePerson->execute([(int)$age, $occupation, $contact, $person_house_no, $person_id]);
    } else {
        $stmtInsPerson = $pdo->prepare("INSERT INTO persons (full_name, nic, gender, dob, age, occupation, contact_number, person_house_number) VALUES (?, ?, 'Female', ?, ?, ?, ?, ?)");
        $stmtInsPerson->execute([$name, $nic ?: null, !empty($dob) ? $dob : null, (int)$age, $occupation, $contact, $person_house_no]);
        $person_id = $pdo->lastInsertId();
    }

    $family_number = $_POST['family_number'] ?? '';
    // Auto-generate family_code if missing
    if (empty($family_number)) {
        $family_code = 'WID-' . date('Ymd') . '-' . substr(uniqid(), -5);
    } else {
        $family_code = $family_number;
    }

    $stmtFam = $pdo->prepare("INSERT INTO families 
        (sno, family_code, family_number, house_number, address, contact_no, income_level, member_count, page_category) 
        VALUES (?, ?, ?, ?, ?, ?, 'Widow Entry', ?, 'widow')");
    $stmtFam->execute([$sno ?: null, $family_code, $family_number, $house_no, $address, $contact, $member_count]);
    $family_id = $pdo->lastInsertId();

    // 2. Insert Page Record
    $stmtRec = $pdo->prepare("INSERT INTO person_page_records 
        (person_id, family_id, page_category, person_sno, aswesuma, pmam, kidney_disease, disabled, is_widow, is_elder) 
        VALUES (?, ?, 'widow', ?, ?, ?, ?, ?, 1, ?)");
    
    $stmtRec->execute([
        $person_id, $family_id, $sno ?: null, 
        $aswesuma, (int)$pmam, $health, $health, $elder
    ]);

    $pdo->commit();
    redirect('../add-widows.php?success=1');
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Error saving entry: " . $e->getMessage());
}
?>
