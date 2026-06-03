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
    
    $cleanKey = str_replace("'", "", strtolower(trim($key)));
    $cleanKey = preg_replace('/[^a-z0-9]/', '_', $cleanKey);
    $cleanKey = preg_replace('/_+/', '_', $cleanKey);
    $cleanKey = trim($cleanKey, '_');
    $cleanData[$cleanKey] = $value;
}

$sno              = $cleanData['sno'] ?? $cleanData['serial_no'] ?? null;
$family_no_raw = $cleanData['family_no'] ?? $cleanData['family_number'] ?? $cleanData['no'] ?? '';
$family_no     = trim((string)$family_no_raw);


$name             = trim($cleanData['name'] ?? $cleanData['full_name'] ?? '');
$house_no         = $cleanData['house_no'] ?? $cleanData['house_number'] ?? '';
$address          = trim($cleanData['address'] ?? '');
$member_count     = $cleanData['family_member'] ?? $cleanData['family_member_count'] ?? $cleanData['member_count'] ?? 1;

// Catch different variations of NIC
$nic              = trim($cleanData['nic_no'] ?? $cleanData['nic'] ?? $cleanData['nic_number'] ?? $cleanData['national_identity_card'] ?? $cleanData['id_no'] ?? '');

// Catch different variations of Date of Birth
$dob_raw          = $cleanData['date_of_birth'] ?? $cleanData['dob'] ?? $cleanData['birthday'] ?? $cleanData['birth_date'] ?? null;
$dob              = $dob_raw ? trim($dob_raw) : null;

$age              = $cleanData['age'] ?? 0;
$occupation       = $cleanData['occupation'] ?? '';
$contact          = $cleanData['conduct_no'] ?? $cleanData['contact_no'] ?? $cleanData['contact_number'] ?? $cleanData['phone_number'] ?? $cleanData['phone_no'] ?? '';
$person_house_no  = $cleanData['person_house_no'] ?? $cleanData['persons_house_no'] ?? $cleanData['person_house_number'] ?? '';

if ($dob) {
    if (is_numeric($dob)) {
        $dob = gmdate("Y-m-d", ($dob - 25569) * 86400);
    } else {
        $ts = strtotime($dob);
        if ($ts) {
            $dob = date('Y-m-d', $ts);
        } else {
            $d = DateTime::createFromFormat('m/d/Y', $dob);
            if (!$d) $d = DateTime::createFromFormat('m-d-Y', $dob);
            if (!$d) $d = DateTime::createFromFormat('d/m/Y', $dob);
            if (!$d) $d = DateTime::createFromFormat('d-m-Y', $dob);
            if ($d) $dob = $d->format('Y-m-d');
        }
    }
}

$aswesuma         = $cleanData['aswesuma'] ?? '0';
$elder            = $cleanData['elder'] ?? '0';
$pmam             = $cleanData['pama'] ?? $cleanData['pmam'] ?? 0;
$health           = $cleanData['kidney_disease_disabled'] ?? $cleanData['kidney_disease'] ?? $cleanData['disabled'] ?? '0';

if (empty($name) || empty($address)) {
    echo json_encode(['success' => false, 'error' => 'Name and Address are required']);
    exit();
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

    // 2. Handle Family
    // Auto-generate family_code if missing
    if (empty($family_no)) {
        $family_code = 'WID-' . date('Ymd') . '-' . substr(uniqid(), -5);
    } else {
        $family_code = $family_no;
    }

    $stmtFam = $pdo->prepare("INSERT INTO families 
        (sno, family_code, family_number, house_number, address, contact_no, income_level, member_count, page_category) 
        VALUES (?, ?, ?, ?, ?, ?, 'Widow Upload', ?, 'widow')");
    $stmtFam->execute([$sno ?: null, $family_code, $family_no, $house_no, $address, $contact, $member_count]);
    $family_id = $pdo->lastInsertId();

    // 3. Insert Page Record
    $stmtRec = $pdo->prepare("INSERT INTO person_page_records 
        (person_id, family_id, page_category, person_sno, aswesuma, pmam, kidney_disease, disabled, is_widow, is_elder) 
        VALUES (?, ?, 'widow', ?, ?, ?, ?, ?, 1, ?)");
    
    $stmtRec->execute([
        $person_id, $family_id, $sno ?: null, 
        $aswesuma, (int)$pmam, $health, $health, $elder
    ]);

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
