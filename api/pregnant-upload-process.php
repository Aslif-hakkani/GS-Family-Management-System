<?php
require_once '../includes/config.php';
session_start();

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'No data provided']);
    exit();
}

// Sanitize keys: to lowercase, replace spaces with underscores, and trim
$cleanData = [];
foreach ($data as $key => $value) {
    if (is_array($value)) continue;
    $cleanKey = strtolower(trim(str_replace(' ', '_', $key)));
    $cleanKey = str_replace("'", "", $cleanKey);
    $cleanData[$cleanKey] = $value;
}

$sno              = $cleanData['sno'] ?? $cleanData['serial_no'] ?? null;
$name             = trim($cleanData['name'] ?? '');
$house_no         = $cleanData['house_no'] ?? $cleanData['house_number'] ?? '';
$address          = trim($cleanData['address'] ?? '');
$nic              = trim($cleanData['nic_no'] ?? $cleanData['nic'] ?? $cleanData['nic_number'] ?? '');
$dob_raw          = $cleanData['date_of_birth'] ?? $cleanData['dob'] ?? $cleanData['birthday'] ?? null;
$dob              = $dob_raw ? trim($dob_raw) : null;

if ($dob) {
    if (is_numeric($dob)) {
        $dob = gmdate("Y-m-d", ($dob - 25569) * 86400);
    } else {
        $ts = strtotime($dob);
        if ($ts) {
            $dob = date('Y-m-d', $ts);
        } else {
            // Backup for explicit m/d/Y if strtotime fails
            $d = DateTime::createFromFormat('m/d/Y', $dob);
            if (!$d) $d = DateTime::createFromFormat('m-d-Y', $dob);
            if ($d) $dob = $d->format('Y-m-d');
        }
    }
}
$age              = 0;
$occupation       = 'N/A';
$contact          = $cleanData['contact_no'] ?? $cleanData['contact_number'] ?? $cleanData['phone_number'] ?? $cleanData['phone_no'] ?? $cleanData['phone'] ?? '';
$person_house_no  = '';
$aswesuma         = '0';
// Pregnant template has no Family No column — auto-generate a unique code
$family_code = 'PREG-' . date('Ymd') . '-' . substr(uniqid(), -6);

if (empty($name) || empty($address)) {
    echo json_encode(['success' => false, 'error' => 'Name and Address are required']);
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Check if person already exists (NIC or Name+DOB)
    $stmtCheckPerson = $pdo->prepare("SELECT id FROM persons WHERE (nic = ? AND nic != '') OR (full_name = ? AND dob = ?)");
    $stmtCheckPerson->execute([$nic, $name, $dob ?: null]);
    $existingPerson = $stmtCheckPerson->fetch();

    if ($existingPerson) {
        $person_id = $existingPerson['id'];
        // Update person details if they changed
        $stmtUpdatePerson = $pdo->prepare("UPDATE persons SET gender = 'Female', age = ?, occupation = ?, contact_number = ?, person_house_number = ? WHERE id = ?");
        $stmtUpdatePerson->execute([(int)$age, $occupation, $contact, $person_house_no, $person_id]);
    } else {
        $stmtInsPerson = $pdo->prepare("INSERT INTO persons (full_name, nic, gender, dob, age, occupation, contact_number, person_house_number) VALUES (?, ?, 'Female', ?, ?, ?, ?, ?)");
        $stmtInsPerson->execute([$name, $nic ?: null, $dob ?: null, (int)$age, $occupation, $contact, $person_house_no]);
        $person_id = $pdo->lastInsertId();
    }

    $stmtFam = $pdo->prepare("INSERT INTO families 
        (sno, family_code, house_number, address, contact_no, income_level, page_category) 
        VALUES (?, ?, ?, ?, ?, 'Pregnant Upload', 'pregnant')");
    $stmtFam->execute([$sno ?: null, $family_code, $house_no, $address, $contact]);
    $family_id = $pdo->lastInsertId();

    // 2. Insert Page Record
    $stmtRec = $pdo->prepare("INSERT INTO person_page_records 
        (person_id, family_id, page_category, person_sno, aswesuma, is_pregnant) 
        VALUES (?, ?, 'pregnant', ?, ?, 1)");
    
    $stmtRec->execute([
        $person_id, $family_id, $sno ?: null, 
        $aswesuma
    ]);

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
