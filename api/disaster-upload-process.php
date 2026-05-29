<?php
require_once '../includes/config.php';
session_start();

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'No data provided']);
    exit();
}

// Sanitize keys: to lowercase, replace special characters with underscores, and trim
$cleanData = [];
foreach ($data as $key => $value) {
    if (is_array($value)) continue;
    $cleanKey = strtolower(trim($key));
    // Replace non-alphanumeric with underscore
    $cleanKey = preg_replace('/[^a-z0-9]/', '_', $cleanKey);
    // Reduce multiple underscores to single
    $cleanKey = preg_replace('/_+/', '_', $cleanKey);
    $cleanKey = trim($cleanKey, '_');
    $cleanData[$cleanKey] = $value;
}

$sno              = $cleanData['sno'] ?? $cleanData['serial_no'] ?? $cleanData['no'] ?? null;
$family_no_raw    = $cleanData['family_no'] ?? $cleanData['family_number'] ?? '';
$family_no        = trim((string)$family_no_raw);

// BUG 1 FIX: Disaster template has no Family No — auto-generate unique code
if ($family_no === '' || $family_no === '0') {
    $family_no   = '';
    $family_code = 'DIS-' . date('Ymd') . '-' . substr(uniqid(), -6);
} else {
    $family_code = $family_no;
}
$name             = trim($cleanData['name'] ?? $cleanData['full_name'] ?? '');
$house_no         = $cleanData['hno'] ?? $cleanData['house_no'] ?? $cleanData['house_number'] ?? '';
$address          = trim($cleanData['address'] ?? '');
$nic              = trim($cleanData['nic_no'] ?? $cleanData['nic'] ?? $cleanData['nic_number'] ?? '');
$contact          = $cleanData['phone_no'] ?? $cleanData['contact_no'] ?? $cleanData['phone_number'] ?? $cleanData['phone'] ?? '';
$member_count     = $cleanData['family_member'] ?? $cleanData['family_member_count'] ?? $cleanData['member_count'] ?? 1;
$signature        = $cleanData['signature'] ?? '';

if (empty($name) || empty($address)) {
    echo json_encode(['success' => false, 'error' => 'Name and Address are required']);
    exit();
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

    $stmtFam = $pdo->prepare("INSERT INTO families 
        (sno, family_code, family_number, house_number, address, contact_no, signature, is_disaster, income_level, member_count, page_category) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 1, 'Disaster Upload', ?, 'disaster')");
    $stmtFam->execute([$sno ?: null, $family_code, $family_no, $house_no, $address, $contact, $signature, $member_count]);
    $family_id = $pdo->lastInsertId();

    // 2. Insert Page Record
    $stmtRec = $pdo->prepare("INSERT INTO person_page_records 
        (person_id, family_id, page_category, person_sno) 
        VALUES (?, ?, 'disaster', ?)");
    
    $stmtRec->execute([$person_id, $family_id, $sno ?: null]);

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
