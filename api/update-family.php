<?php
require_once '../includes/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../search.php');
}

$record_id      = $_POST['record_id'] ?? 0;
$person_id      = $_POST['person_id'] ?? 0;
$family_id      = $_POST['family_id'] ?? 0;

if (!$record_id || !$person_id || !$family_id) {
    die("Invalid IDs provided.");
}

// Data collection
$sno                = $_POST['sno'] ?? null;
$family_number      = $_POST['family_number'] ?? '';
$house_number       = $_POST['house_number'] ?? '';
$road               = $_POST['road'] ?? '';
$address            = trim($_POST['address'] ?? '');

$full_name          = trim($_POST['full_name'] ?? '');
$nic                = $_POST['nic'] ?? '';
$dob                = $_POST['dob'] ?? null;
$age                = (int)($_POST['age'] ?? 0);
$gender             = $_POST['gender'] ?? 'Male';
$occupation         = $_POST['occupation'] ?? '';
$contact_number     = $_POST['contact_number'] ?? '';
$person_house_number = $_POST['person_house_number'] ?? '';

$aswesuma           = $_POST['aswesuma'] ?? '';
$is_elder           = $_POST['is_elder'] ?? '';
$pmam               = $_POST['pmam'] ?? '0';
$kidney_disease     = $_POST['kidney_disease'] ?? '';
$disabled           = $_POST['disabled'] ?? '';
$housing_condition  = $_POST['housing_condition'] ?? '';
$remarks            = $_POST['remarks'] ?? '';

$member_count       = $_POST['member_count'] ?? 1;

try {
    $pdo->beginTransaction();

    // 1. Update Family
    $stmtFam = $pdo->prepare("UPDATE families SET 
        sno = ?, 
        family_number = ?, 
        house_number = ?, 
        road = ?, 
        address = ?, 
        contact_no = ?,
        housing_condition = ?, 
        remarks = ?,
        member_count = ?
        WHERE id = ?");
    $stmtFam->execute([$sno, $family_number, $house_number, $road, $address, $contact_number, $housing_condition, $remarks, $member_count, $family_id]);

    // 2. Update Person
    $stmtPerson = $pdo->prepare("UPDATE persons SET 
        full_name = ?, 
        nic = ?, 
        dob = ?, 
        age = ?, 
        gender = ?, 
        occupation = ?, 
        contact_number = ?, 
        person_house_number = ? 
        WHERE id = ?");
    $stmtPerson->execute([
        $full_name, $nic, 
        $dob ?: null, $age, $gender, 
        $occupation, $contact_number, $person_house_number, 
        $person_id
    ]);

    $relationship       = $_POST['relationship'] ?? '';
    // 3. Update Page Record
    $stmtRec = $pdo->prepare("UPDATE person_page_records SET 
        person_sno = ?,
        aswesuma = ?, 
        is_elder = ?, 
        pmam = ?, 
        kidney_disease = ?, 
        disabled = ?,
        relationship = ?
        WHERE id = ?");
    $stmtRec->execute([
        $sno,
        $aswesuma, $is_elder, $pmam, 
        $kidney_disease, $disabled, 
        $relationship,
        $record_id
    ]);

    $pdo->commit();

    // Smart redirect: use the back_url passed from the form
    $redirect_url = $_POST['back_url'] ?? '../search.php';
    
    // Ensure the updated flag is appended correctly
    $separator = (strpos($redirect_url, '?') !== false) ? '&' : '?';
    
    header("Location: " . $redirect_url . $separator . "updated=1");
    exit();

} catch (PDOException $e) {
    $pdo->rollBack();
    die("Update error: " . $e->getMessage());
}
?>
