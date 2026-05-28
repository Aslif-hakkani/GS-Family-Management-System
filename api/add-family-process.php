<?php
require_once '../includes/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Family Data
    $sno = $_POST['sno'] ?? null;
    $family_number = $_POST['family_number'] ?? '';
    $address = $_POST['address'] ?? '';
    $house_number = $_POST['house_number'] ?? '';
    $road = $_POST['road'] ?? '';
    $income_level = $_POST['income_level'] ?? '';
    $contact_no = $_POST['contact_no'] ?? '';
    $housing_condition = $_POST['housing_condition'] ?? '';
    $remarks = $_POST['remarks'] ?? '';
    $is_homeless = isset($_POST['is_homeless']) ? 1 : 0;
    $is_disaster = isset($_POST['is_disaster']) ? 1 : 0;
    
    // Generate a simple family code
    $family_code = $family_number;

    // 2. Members Data (Arrays)
    $names = $_POST['member_name'] ?? [];
    $nics = $_POST['member_nic'] ?? [];
    $dobs = $_POST['member_dob'] ?? [];
    $ages = $_POST['member_age'] ?? [];
    $genders = $_POST['member_gender'] ?? [];
    $occupations = $_POST['member_occupation'] ?? [];
    $member_contacts = $_POST['member_contact'] ?? [];
    $relations = $_POST['member_relation'] ?? [];
    $person_house_numbers = $_POST['member_house_no'] ?? [];
    $aswesumas = $_POST['aswesuma'] ?? [];
    $pmams = $_POST['pmam'] ?? [];
    $kidney_diseases = $_POST['kidney_disease'] ?? [];
    $disableds = $_POST['disabled'] ?? [];
    $is_widow = $_POST['is_widow'] ?? [];
    $is_pregnant = $_POST['is_pregnant'] ?? [];
    $is_elder = $_POST['is_elder'] ?? [];

    try {
        $pdo->beginTransaction();

        // Insert Family
        $stmtFam = $pdo->prepare("INSERT INTO families (sno, family_code, family_number, address, house_number, road, income_level, contact_no, housing_condition, remarks, is_homeless, is_disaster, page_category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'general')");
        $stmtFam->execute([$sno, $family_code, $family_number, $address, $house_number, $road, $income_level, $contact_no, $housing_condition, $remarks, $is_homeless, $is_disaster]);
        $family_id = $pdo->lastInsertId();

        // Check if is_head column exists (safe for both old and new DB)
        $hasHeadCol = (int)$pdo->query("
            SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'person_page_records'
              AND COLUMN_NAME  = 'is_head'
        ")->fetchColumn();

        // Insert Members — first member in the list becomes family head
        for ($i = 0; $i < count($names); $i++) {
            if (!empty($names[$i])) {
                $name = $names[$i];
                $nic = $nics[$i];
                $dob = !empty($dobs[$i]) ? $dobs[$i] : null;

                // 1. Check if person already exists (NIC or Name+DOB)
                $stmtCheckPerson = $pdo->prepare("SELECT id FROM persons WHERE (nic = ? AND nic != '') OR (full_name = ? AND dob = ?)");
                $stmtCheckPerson->execute([$nic, $name, $dob]);
                $existingPerson = $stmtCheckPerson->fetch();

                if ($existingPerson) {
                    $person_id = $existingPerson['id'];
                    // Update person details if they changed
                    $stmtUpdatePerson = $pdo->prepare("UPDATE persons SET gender = ?, age = ?, occupation = ?, contact_number = ?, person_house_number = ? WHERE id = ?");
                    $stmtUpdatePerson->execute([$genders[$i], (int)$ages[$i], $occupations[$i], $member_contacts[$i], $person_house_numbers[$i], $person_id]);
                } else {
                    $stmtInsPerson = $pdo->prepare("INSERT INTO persons (full_name, nic, gender, dob, age, occupation, contact_number, person_house_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmtInsPerson->execute([$name, $nic ?: null, $genders[$i], $dob, (int)$ages[$i], $occupations[$i], $member_contacts[$i], $person_house_numbers[$i]]);
                    $person_id = $pdo->lastInsertId();
                }

                // First member of this family is the head
                $is_head_val = ($i === 0) ? 1 : 0;

                // 2. Insert Page Record
                if ($hasHeadCol) {
                    $stmtRec = $pdo->prepare("INSERT INTO person_page_records (person_id, family_id, page_category, person_sno, aswesuma, pmam, kidney_disease, disabled, is_widow, is_pregnant, is_elder, is_head) VALUES (?, ?, 'general', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmtRec->execute([
                        $person_id, $family_id, $sno,
                        $aswesumas[$i] ?? '0', (int)($pmams[$i] ?? 0),
                        $kidney_diseases[$i] ?? '0', $disableds[$i] ?? '0',
                        $is_widow[$i] ?? 0, $is_pregnant[$i] ?? 0,
                        $is_elder[$i] ?? '0', $is_head_val
                    ]);
                } else {
                    $stmtRec = $pdo->prepare("INSERT INTO person_page_records (person_id, family_id, page_category, person_sno, aswesuma, pmam, kidney_disease, disabled, is_widow, is_pregnant, is_elder) VALUES (?, ?, 'general', ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmtRec->execute([
                        $person_id, $family_id, $sno,
                        $aswesumas[$i] ?? '0', (int)($pmams[$i] ?? 0),
                        $kidney_diseases[$i] ?? '0', $disableds[$i] ?? '0',
                        $is_widow[$i] ?? 0, $is_pregnant[$i] ?? 0,
                        $is_elder[$i] ?? '0'
                    ]);
                }
            }
        }

        $pdo->commit();
        redirect('../dashboard.php?success=1');

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error saving family: " . $e->getMessage());
    }
} else {
    redirect('../add-family.php');
}
?>
