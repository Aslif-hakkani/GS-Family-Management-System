<?php
require_once 'includes/config.php';

try {
    $pdo->beginTransaction();

    // Fetch all members with their family category
    $stmt = $pdo->query("SELECT m.*, f.page_category FROM members m JOIN families f ON m.family_id = f.id");
    $old_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Migrating " . count($old_members) . " records...\n";

    $person_cache = []; // key: NIC or Name|DOB, value: person_id

    foreach ($old_members as $m) {
        $nic = trim($m['nic'] ?? '');
        $name = trim($m['full_name']);
        $dob = $m['dob'] ?? '';
        
        $cache_key = !empty($nic) ? $nic : ($name . '|' . $dob);
        
        if (!isset($person_cache[$cache_key])) {
            // Check if person already exists in new table (just in case)
            $check = $pdo->prepare("SELECT id FROM persons WHERE (nic = ? AND nic != '') OR (full_name = ? AND dob = ?)");
            $check->execute([$nic, $name, $dob ?: null]);
            $existing = $check->fetchColumn();
            
            if ($existing) {
                $person_id = $existing;
            } else {
                // Insert new person
                $ins = $pdo->prepare("INSERT INTO persons (full_name, nic, gender, dob, age, occupation, contact_number, person_house_number, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $ins->execute([
                    $name, $nic ?: null, $m['gender'], $dob ?: null, 
                    $m['age'], $m['occupation'], $m['contact_number'], 
                    $m['person_house_number'], $m['created_at']
                ]);
                $person_id = $pdo->lastInsertId();
            }
            $person_cache[$cache_key] = $person_id;
        } else {
            $person_id = $person_cache[$cache_key];
        }

        // Create page record
        $insRec = $pdo->prepare("INSERT INTO person_page_records 
            (person_id, family_id, page_category, person_sno, aswesuma, pmam, kidney_disease, disabled, is_widow, is_pregnant, is_elder, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $insRec->execute([
            $person_id, $m['family_id'], $m['page_category'] ?: 'general', 
            $m['person_sno'], $m['aswesuma'], $m['pmam'], 
            $m['kidney_disease'], $m['disabled'], $m['is_widow'], 
            $m['is_pregnant'], $m['is_elder'], $m['created_at']
        ]);
    }

    $pdo->commit();
    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error during migration: " . $e->getMessage() . "\n";
}
