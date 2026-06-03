<?php
require_once '../includes/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../search.php');
}

$sno            = $_POST['sno'] ?? null;
$family_no      = $_POST['family_no'] ?? '';
$house_no       = $_POST['house_no'] ?? '';
$address        = trim($_POST['address'] ?? '');
$contact_no     = $_POST['contact_no'] ?? '';
$income_level   = $_POST['income_level'] ?? '';
$name           = trim($_POST['name'] ?? '');
$nic_no         = $_POST['nic_no'] ?? '';
$gender         = $_POST['gender'] ?? '';
$dob            = $_POST['dob'] ?? null;
$age            = $_POST['age'] ?? 0;
$occupation     = $_POST['occupation'] ?? '';
$member_contact = $_POST['member_contact'] ?? '';
$person_house_no = $_POST['person_house_no'] ?? '';
$aswesuma       = $_POST['aswesuma'] ?? '';
$is_elder       = $_POST['is_elder'] ?? '0';
$pmam           = $_POST['pmam'] ?? '0';
$kidney_disease = $_POST['kidney_disease'] ?? '0';
$disabled       = $_POST['disabled'] ?? '0';

if (empty($name) || empty($address)) {
    die("Name and Address are required.");
}

// Normalize gender
$genderMap = ['m' => 'Male', 'f' => 'Female', 'male' => 'Male', 'female' => 'Female', 'other' => 'Other'];
$gender = $genderMap[strtolower(trim($gender))] ?? $gender;

try {
    $pdo->beginTransaction();

    // 1. Check if person already exists (NIC or Name+DOB)
    $stmtCheckPerson = $pdo->prepare("SELECT id FROM persons WHERE (nic = ? AND nic != '') OR (full_name = ? AND dob = ?)");
    $stmtCheckPerson->execute([$nic_no, $name, !empty($dob) ? $dob : null]);
    $existingPerson = $stmtCheckPerson->fetch();

    if ($existingPerson) {
        $person_id = $existingPerson['id'];
        // Update person details if they changed
        $stmtUpdatePerson = $pdo->prepare("UPDATE persons SET gender = ?, age = ?, occupation = ?, contact_number = ?, person_house_number = ? WHERE id = ?");
        $stmtUpdatePerson->execute([$gender, (int)$age, $occupation, $member_contact ?: $contact_no, $person_house_no, $person_id]);
    } else {
        $stmtInsPerson = $pdo->prepare("INSERT INTO persons (full_name, nic, gender, dob, age, occupation, contact_number, person_house_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtInsPerson->execute([$name, $nic_no ?: null, $gender, !empty($dob) ? $dob : null, (int)$age, $occupation, $member_contact ?: $contact_no, $person_house_no]);
        $person_id = $pdo->lastInsertId();
    }

    $member_count = $_POST['member_count'] ?? 1;

    // Auto-generate family_code if missing
    if (empty($family_no)) {
        $family_code = 'FAM-' . date('Ymd') . '-' . substr(uniqid(), -5);
    } else {
        $family_code = $family_no;
    }

    $stmtFam = $pdo->prepare("INSERT INTO families 
        (sno, family_code, family_number, house_number, address, contact_no, income_level, is_homeless, is_disaster, member_count, page_category) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, ?, 'general')");
    $stmtFam->execute([$sno ?: null, $family_code, $family_no, $house_no, $address, $contact_no, $income_level, $member_count]);
    $family_id = $pdo->lastInsertId();

    // 2. Insert Page Record
    $stmtRec = $pdo->prepare("INSERT INTO person_page_records 
        (person_id, family_id, page_category, person_sno, aswesuma, pmam, kidney_disease, disabled, is_elder, is_widow, is_pregnant) 
        VALUES (?, ?, 'general', ?, ?, ?, ?, ?, ?, 0, 0)");
    $stmtRec->execute([
        $person_id,
        $family_id,
        $sno ?: null,
        $aswesuma,
        (int)$pmam,
        $kidney_disease,
        $disabled,
        $is_elder
    ]);

    $pdo->commit();
    redirect('../add-family-details.php?success=1');
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Error saving entry: " . $e->getMessage());
}
?>
