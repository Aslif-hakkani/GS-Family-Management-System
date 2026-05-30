<?php
require_once '../includes/config.php';
session_start();

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit();
}

// Sanitize keys: to lowercase, replace special characters with underscores, and trim
$cleanData = [];
foreach ($data as $key => $value) {
    if (is_array($value)) continue; // Skip nested if any
    
    // First remove apostrophes entirely so "Person's" becomes "persons"
    $cleanKey = str_replace("'", "", strtolower(trim($key)));
    
    // Replace non-alphanumeric with underscore
    $cleanKey = preg_replace('/[^a-z0-9]/', '_', $cleanKey);
    // Reduce multiple underscores to single
    $cleanKey = preg_replace('/_+/', '_', $cleanKey);
    $cleanKey = trim($cleanKey, '_');
    $cleanData[$cleanKey] = $value;
}

$sno          = $cleanData['sno'] ?? $cleanData['serial_no'] ?? null;
$family_no_raw = $cleanData['family_no'] ?? $cleanData['family_number'] ?? $cleanData['no'] ?? '';
$family_no     = trim((string)$family_no_raw);

// Error if Family No is missing or zero
if ($family_no === '' || $family_no === '0') {
    echo json_encode(['success' => false, 'error' => 'Invalid Family No: "' . $family_no_raw . '". Every row must have a valid non-zero Family Number.']);
    exit();
}
$name         = trim($cleanData['name'] ?? $cleanData['full_name'] ?? '');
$house_no     = $cleanData['house_no'] ?? $cleanData['house_number'] ?? '';
$road         = $cleanData['road'] ?? '';
$nic_no       = trim($cleanData['nic_no'] ?? $cleanData['nic'] ?? $cleanData['nic_number'] ?? '');

$occupation   = $cleanData['occupation'] ?? '';
$phone_no     = $cleanData['phone_no'] ?? $cleanData['contact_no'] ?? $cleanData['contact_number'] ?? '';
$situation    = $cleanData['situation'] ?? $cleanData['housing_condition'] ?? '';
$aswesuma     = $cleanData['aswesuma'] ?? '';
$remarks      = $cleanData['remarks'] ?? '';
$member_count = $cleanData['family_member'] ?? $cleanData['member_count'] ?? 1;

if (empty($name)) {
    echo json_encode(['success' => false, 'error' => 'Name is required']);
    exit();
}

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

    $existing = null;
    if (!empty($family_no)) {
        $stmtCheck = $pdo->prepare("SELECT id FROM families WHERE family_number = ? AND page_category = 'homeless'");
        $stmtCheck->execute([$family_no]);
        $existing = $stmtCheck->fetch();
    }

    if ($existing) {
        $family_id = $existing['id'];
    } else {
        $family_code = $family_no;
        $stmtFam = $pdo->prepare("INSERT INTO families 
            (sno, family_code, family_number, house_number, road, address, contact_no, housing_condition, remarks, is_homeless, member_count, page_category) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, 'homeless')");
        $stmtFam->execute([$sno ?: null, $family_code, $family_no, $house_no, $road, $address, $phone_no, $situation, $remarks, $member_count]);
        $family_id = $pdo->lastInsertId();
    }

    // 2. Insert Page Record
    $stmtRec = $pdo->prepare("INSERT INTO person_page_records 
        (person_id, family_id, page_category, person_sno, aswesuma) 
        VALUES (?, ?, 'homeless', ?, ?)");
    $stmtRec->execute([$person_id, $family_id, $sno ?: null, $aswesuma]);

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
