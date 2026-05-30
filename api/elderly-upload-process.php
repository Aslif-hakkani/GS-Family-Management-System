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
    if (is_array($value)) continue; // Skip nested if any
    
    // First remove apostrophes entirely so "Person's" becomes "persons"
    // Also handle curly apostrophes
    $cleanKey = str_replace(["'", "’", "‘", "`"], "", strtolower(trim($key)));
    
    // Replace non-alphanumeric with underscore
    $cleanKey = preg_replace('/[^a-z0-9]/', '_', $cleanKey);
    // Reduce multiple underscores to single
    $cleanKey = preg_replace('/_+/', '_', $cleanKey);
    $cleanKey = trim($cleanKey, '_');
    $cleanData[$cleanKey] = $value;
}

$sno              = $cleanData['sno'] ?? $cleanData['serial_no'] ?? null;
$name             = trim($cleanData['name'] ?? $cleanData['full_name'] ?? '');
$house_no         = $cleanData['house_no'] ?? $cleanData['house_number'] ?? '';
$address          = trim($cleanData['address'] ?? '');
$raw_sex          = strtolower(trim($cleanData['sex'] ?? $cleanData['gender'] ?? 'm'));
if ($raw_sex === 'f' || $raw_sex === 'female') {
    $gender = 'Female';
} else {
    $gender = 'Male';
}

// Catch different variations of NIC
$nic              = trim($cleanData['nic_no'] ?? $cleanData['nic'] ?? $cleanData['nic_number'] ?? '');

// Catch different variations of Date of Birth
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
            if (!$d) $d = DateTime::createFromFormat('d/m/Y', $dob);
            if (!$d) $d = DateTime::createFromFormat('d-m-Y', $dob);
            if ($d) $dob = $d->format('Y-m-d');
        }
    }
}
$age              = (int)($cleanData['age'] ?? 0);
$occupation       = $cleanData['occupation'] ?? '';
$contact          = $cleanData['conduct_no'] ?? $cleanData['contact_no'] ?? $cleanData['contact_number'] ?? $cleanData['phone_number'] ?? '';
$person_house_no  = $cleanData['persons_house_no'] ?? $cleanData['person_house_no'] ?? $cleanData['person_house_number'] ?? $cleanData['person_s_house_no'] ?? '';
$aswesuma         = $cleanData['aswesuma'] ?? '0';
$elder            = $cleanData['elder'] ?? '5000';
$raw_members = $cleanData['family_member'] ?? $cleanData['family_member_count'] ?? $cleanData['member_count'] ?? '';
$member_count = ($raw_members !== '') ? (int)$raw_members : null;
$family_no_raw = $cleanData['family_no'] ?? $cleanData['family_number'] ?? $cleanData['no'] ?? '';
$family_no     = trim((string)$family_no_raw);

// Elderly template has no Family No column — auto-generate a unique code
if ($family_no === '' || $family_no === '0') {
    $family_no = '';
    $family_code = 'ELD-' . date('Ymd') . '-' . substr(uniqid(), -6);
} else {
    $family_code = $family_no;
}

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
        $stmtUpdatePerson = $pdo->prepare("UPDATE persons SET gender = ?, age = ?, occupation = ?, contact_number = ?, person_house_number = ? WHERE id = ?");
        $stmtUpdatePerson->execute([$gender, $age, $occupation, $contact, $person_house_no, $person_id]);
    } else {
        $stmtInsPerson = $pdo->prepare("INSERT INTO persons (full_name, nic, gender, dob, age, occupation, contact_number, person_house_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtInsPerson->execute([$name, $nic ?: null, $gender, $dob ?: null, $age, $occupation, $contact, $person_house_no]);
        $person_id = $pdo->lastInsertId();
    }

    $stmtFam = $pdo->prepare("INSERT INTO families 
        (sno, family_code, family_number, house_number, address, contact_no, income_level, member_count, page_category) 
        VALUES (?, ?, ?, ?, ?, ?, 'Elderly Upload', ?, 'elderly')");
    $stmtFam->execute([$sno ?: null, $family_code, $family_no ?: null, $house_no, $address, $contact, $member_count]);
    $family_id = $pdo->lastInsertId();

    // 2. Insert Page Record
    $stmtRec = $pdo->prepare("INSERT INTO person_page_records 
        (person_id, family_id, page_category, person_sno, aswesuma, is_elder) 
        VALUES (?, ?, 'elderly', ?, ?, ?)");
    
    $stmtRec->execute([
        $person_id, $family_id, $sno ?: null, 
        $aswesuma, $elder
    ]);

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
