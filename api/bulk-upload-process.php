<?php
require_once '../includes/config.php';
session_start();

// Expecting JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid data format']);
    exit();
}

// Extract fields matching the new Excel template columns
// Sanitize keys: to lowercase, replace non-alphanumeric with underscores, and trim
$cleanData = [];
foreach ($data as $key => $value) {
    if (is_array($value)) continue;
    
    // First remove all types of apostrophes so "Person's" becomes "persons"
    $cleanKey = str_replace(["'", "’", "‘"], "", strtolower(trim($key)));
    
    // Replace non-alphanumeric with underscore
    $cleanKey = preg_replace('/[^a-z0-9]/', '_', $cleanKey);
    // Reduce multiple underscores to single
    $cleanKey = preg_replace('/_+/', '_', $cleanKey);
    $cleanKey = trim($cleanKey, '_');
    $cleanData[$cleanKey] = $value;
}

$sno            = $cleanData['sno'] ?? $cleanData['serial_no'] ?? null;
$family_no_raw  = $cleanData['family_no'] ?? $cleanData['family_number'] ?? $cleanData['no'] ?? '';
$family_no      = trim((string)$family_no_raw);

// Skip blank rows or rows without a valid numeric Family No
if ($family_no === '' || !is_numeric($family_no)) {
    echo json_encode(['success' => true, 'skipped' => true]);
    exit();
}
$name           = trim($cleanData['name'] ?? $cleanData['full_name'] ?? '');
$house_no       = $cleanData['house_no'] ?? $cleanData['house_number'] ?? '';
$address        = $cleanData['address'] ?? '';
$gender         = $cleanData['sex'] ?? $cleanData['gender'] ?? '';

// Normalize gender: M → Male, F → Female
$genderMap = ['m' => 'Male', 'f' => 'Female', 'male' => 'Male', 'female' => 'Female', 'other' => 'Other'];
$gender = $genderMap[strtolower(trim($gender))] ?? $gender;

$member_count   = $cleanData['family_member'] ?? $cleanData['family_member_count'] ?? $cleanData['members'] ?? 1;
$nic            = trim($cleanData['nic_no'] ?? $cleanData['nic'] ?? '');
$dob_raw        = $cleanData['date_of_birth'] ?? $cleanData['dob'] ?? null;
$dob            = $dob_raw ? trim($dob_raw) : null;
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
$age            = $cleanData['age'] ?? 0;
$occupation     = $cleanData['occupation'] ?? '';
$contact_no     = $cleanData['conduct_no'] ?? $cleanData['contact_no'] ?? $cleanData['phone_number'] ?? '';
$person_house_no = $cleanData['persons_house_no'] ?? $cleanData['person_s_house_no'] ?? $cleanData['person_house_no'] ?? $cleanData['person_house_number'] ?? '';
$aswesuma       = $cleanData['aswesuma'] ?? '0';
$is_elder       = $cleanData['elder'] ?? '0';
$pmam           = $cleanData['pama'] ?? $cleanData['pmam'] ?? 0;
$kd_val         = $cleanData['kidney_disease_disabled'] ?? $cleanData['kidney_disease'] ?? '0';

// Global category from the UI dropdown
$global_category = $data['GlobalCategory'] ?? '';

$kidney_disease = $kd_val;
$disabled = $kd_val; 

// If global category is 'elderly', and no elder_val was provided as an amount, set it to 'Yes'
if ($global_category === 'elderly' && ($is_elder === 'No' || $is_elder === '0')) {
    $is_elder = 'Yes';
}

// Map global category to DB flags
$is_widow    = ($global_category === 'widow')    ? 1 : 0;
$is_pregnant = ($global_category === 'pregnant') ? 1 : 0;
// Note: is_elder in DB is currently TINYINT(1) in schema but might be used for amount in some pages. 
// For bulk upload, we'll store the numeric/string flag.
if ($global_category === 'elderly' && $is_elder === 'Yes') $is_elder = 1;

$is_homeless = ($global_category === 'homeless') ? 1 : 0;
$is_disaster = ($global_category === 'disaster') ? 1 : 0;

if (empty($name)) {
    echo json_encode(['success' => false, 'error' => 'Missing required field: Name']);
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
        $stmtUpdatePerson = $pdo->prepare("UPDATE persons SET gender = ?, age = ?, occupation = ?, contact_number = ?, person_house_number = ? WHERE id = ?");
        $stmtUpdatePerson->execute([$gender, (int)$age, $occupation, $contact_no, $person_house_no, $person_id]);
    } else {
        $stmtInsPerson = $pdo->prepare("INSERT INTO persons (full_name, nic, gender, dob, age, occupation, contact_number, person_house_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtInsPerson->execute([$name, $nic ?: null, $gender, !empty($dob) ? $dob : null, (int)$age, $occupation, $contact_no, $person_house_no]);
        $person_id = $pdo->lastInsertId();
    }

    // 2. Handle Family grouping
    $whereClause = "family_number = ? AND address = ? AND family_number != ''";
    $params = [$family_no, $address];
    
    // Determine page_category for this upload
    $page_cat = 'general';
    if ($is_homeless) $page_cat = 'homeless';
    elseif ($is_disaster) $page_cat = 'disaster';
    
    $whereClause .= " AND page_category = ?";
    $params[] = $page_cat;

    $stmtCheckFam = $pdo->prepare("SELECT id FROM families WHERE " . $whereClause);
    $stmtCheckFam->execute($params);
    $existingFamily = $stmtCheckFam->fetch();

    if ($existingFamily) {
        $family_id = $existingFamily['id'];
        if ($is_homeless || $is_disaster) {
            $stmtUpdateFam = $pdo->prepare("UPDATE families SET is_homeless = CASE WHEN ? = 1 THEN 1 ELSE is_homeless END, is_disaster = CASE WHEN ? = 1 THEN 1 ELSE is_disaster END WHERE id = ?");
            $stmtUpdateFam->execute([$is_homeless, $is_disaster, $family_id]);
        }
    } else {
        $family_code = $family_no;
        if ($family_code === '') {
            $family_code = 'FAM-' . date('Ymd') . '-' . substr(uniqid(), -5);
        }
        $stmtInsFam = $pdo->prepare("INSERT INTO families (sno, family_code, family_number, house_number, address, contact_no, is_homeless, is_disaster, member_count, page_category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtInsFam->execute([$sno ?: null, $family_code, $family_no, $house_no, $address, $contact_no, $is_homeless, $is_disaster, $member_count, $page_cat]);
        $family_id = $pdo->lastInsertId();
    }

    // 3. Determine if this person is the first (head) for their family
    $stmtCountRecs = $pdo->prepare("SELECT COUNT(*) FROM person_page_records WHERE family_id = ?");
    $stmtCountRecs->execute([$family_id]);
    $existingRecs = (int)$stmtCountRecs->fetchColumn();
    $is_head_val = ($existingRecs === 0) ? 1 : 0;

    // Check if is_head column exists (safe for both old and new DB)
    $hasHeadCol = (int)$pdo->query("
        SELECT COUNT(*) FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'person_page_records'
          AND COLUMN_NAME  = 'is_head'
    ")->fetchColumn();

    // 4. Insert Page Record
    if ($hasHeadCol) {
        $stmtRec = $pdo->prepare("INSERT INTO person_page_records 
            (person_id, family_id, page_category, person_sno, aswesuma, pmam, kidney_disease, disabled, is_widow, is_pregnant, is_elder, is_head) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtRec->execute([
            $person_id, $family_id, $page_cat, $sno ?: null,
            $aswesuma, (int)$pmam, $kidney_disease, $disabled,
            $is_widow, $is_pregnant, $is_elder, $is_head_val
        ]);
    } else {
        $stmtRec = $pdo->prepare("INSERT INTO person_page_records 
            (person_id, family_id, page_category, person_sno, aswesuma, pmam, kidney_disease, disabled, is_widow, is_pregnant, is_elder) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtRec->execute([
            $person_id, $family_id, $page_cat, $sno ?: null,
            $aswesuma, (int)$pmam, $kidney_disease, $disabled,
            $is_widow, $is_pregnant, $is_elder
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
