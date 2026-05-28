<?php
require_once 'includes/config.php';
check_auth();

$id = $_GET['id'] ?? 0;
$family_id = $_GET['family_id'] ?? 0;
$back_url = $_SERVER['HTTP_REFERER'] ?? 'search.php';
// If the referer is the same page, default to search.php to avoid loops
if (strpos($back_url, 'edit-family.php') !== false) {
    $back_url = 'search.php';
}

try {
    if ($id > 0) {
        // Fetch Record, Person and their Family
        $stmt = $pdo->prepare("SELECT p.*, f.*, r.*, p.id as person_id, f.id as family_id, r.id as record_id, f.sno as fam_sno
                              FROM person_page_records r 
                              JOIN persons p ON r.person_id = p.id
                              JOIN families f ON r.family_id = f.id 
                              WHERE r.id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
    } elseif ($family_id > 0) {
        // Fetch the first record (usually Head) for this family
        $stmt = $pdo->prepare("SELECT p.*, f.*, r.*, p.id as person_id, f.id as family_id, r.id as record_id, f.sno as fam_sno
                              FROM families f 
                              JOIN person_page_records r ON r.family_id = f.id
                              JOIN persons p ON r.person_id = p.id
                              WHERE f.id = ?
                              ORDER BY r.id ASC LIMIT 1");
        $stmt->execute([$family_id]);
        $data = $stmt->fetch();
    } else {
        die("Invalid request.");
    }

    if (!$data) {
        die("Record not found.");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$is_widow = $data['is_widow'];
$is_pregnant = $data['is_pregnant'];
$is_elder = $data['is_elder'] || $data['age'] >= 60;
$is_homeless = $data['is_homeless'];
$is_disaster = $data['is_disaster'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Record - GS System</title>
    <link rel="stylesheet" href="assets/css/style.css?v=3.0">
    <link rel="stylesheet" href="assets/css/responsive.css?v=1.3">
    <link rel="stylesheet" href="assets/css/theme.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background-color: #f8fafc;">
    <nav class="glass-dark" style="padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="javascript:history.back()" style="color: white;"><i class="fas fa-arrow-left"></i></a>
            <h2><i class="fas fa-edit" style="margin-right:0.5rem;"></i>Edit Record</h2>
        </div>
    </nav>

    <div class="container animate-fade" style="max-width: 900px; padding: 2rem;">
        <div class="glass" style="padding: 2.5rem; border-radius: var(--radius-lg);">
            <form action="api/update-family.php" method="POST">
                <input type="hidden" name="record_id" value="<?php echo $data['record_id']; ?>">
                <input type="hidden" name="person_id" value="<?php echo $data['person_id']; ?>">
                <input type="hidden" name="family_id" value="<?php echo $data['family_id']; ?>">
                <input type="hidden" name="back_url" value="<?php echo htmlspecialchars($back_url); ?>">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    
                    <!-- Family Group -->
                    <div style="grid-column: span 2; border-bottom: 2px solid #f1f5f9; padding-bottom: 0.5rem; margin-top: 1rem;">
                        <h4 style="color: var(--primary);"><i class="fas fa-home"></i> Family Information</h4>
                    </div>

                    <div class="form-group">
                        <label>SNO</label>
                        <input type="number" name="sno" value="<?php echo htmlspecialchars($data['fam_sno']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Family Number</label>
                        <input type="text" name="family_number" value="<?php echo htmlspecialchars($data['family_number']); ?>">
                    </div>

                    <div class="form-group">
                        <label>House No (Family)</label>
                        <input type="text" name="house_number" value="<?php echo htmlspecialchars($data['house_number']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Road</label>
                        <input type="text" name="road" value="<?php echo htmlspecialchars($data['road']); ?>">
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label>Postal Address</label>
                        <textarea name="address" rows="1" required style="resize: none;"><?php echo htmlspecialchars($data['address']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Family Member Count</label>
                        <input type="number" name="member_count" value="<?php echo htmlspecialchars($data['member_count'] ?? 1); ?>">
                    </div>

                    <!-- Member Group -->
                    <div style="grid-column: span 2; border-bottom: 2px solid #f1f5f9; padding-bottom: 0.5rem; margin-top: 1.5rem;">
                        <h4 style="color: var(--primary);"><i class="fas fa-user-circle"></i> Member Information (<?php echo htmlspecialchars($data['full_name']); ?>)</h4>
                    </div>

                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" required value="<?php echo htmlspecialchars($data['full_name']); ?>">
                    </div>

                    <div class="form-group">
                        <label>NIC Number</label>
                        <input type="text" name="nic" value="<?php echo htmlspecialchars($data['nic']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="dob" value="<?php echo $data['dob']; ?>">
                    </div>

                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" name="age" value="<?php echo $data['age']; ?>">
                    </div>

                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="Male" <?php if($data['gender']=='Male') echo 'selected'; ?>>Male</option>
                            <option value="Female" <?php if($data['gender']=='Female') echo 'selected'; ?>>Female</option>
                            <option value="Other" <?php if($data['gender']=='Other') echo 'selected'; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Occupation</label>
                        <input type="text" name="occupation" value="<?php echo htmlspecialchars($data['occupation']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Contact No</label>
                        <input type="text" name="contact_number" value="<?php echo htmlspecialchars($data['contact_number']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Person's House No</label>
                        <input type="text" name="person_house_number" value="<?php echo htmlspecialchars($data['person_house_number']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Relationship (to Family Head)</label>
                        <input type="text" name="relationship" value="<?php echo htmlspecialchars($data['relationship'] ?? ''); ?>" placeholder="e.g., Head, Spouse, Son">
                    </div>

                    <!-- Category Status Group -->
                    <div style="grid-column: span 2; border-bottom: 2px solid #f1f5f9; padding-bottom: 0.5rem; margin-top: 1.5rem;">
                        <h4 style="color: var(--primary);"><i class="fas fa-tags"></i> Status & Category Details</h4>
                    </div>

                    <div class="form-group">
                        <label>Aswesuma Status</label>
                        <input type="text" name="aswesuma" value="<?php echo htmlspecialchars($data['aswesuma']); ?>" placeholder="e.g., poor, vulnerable">
                    </div>

                    <div class="form-group">
                        <label>Elder Amount / Status</label>
                        <input type="text" name="is_elder" value="<?php echo htmlspecialchars($data['is_elder']); ?>" placeholder="e.g., 5000">
                    </div>

                    <div class="form-group">
                        <label>PMAM Amount</label>
                        <input type="text" name="pmam" value="<?php echo htmlspecialchars($data['pmam']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Kidney Disease Status</label>
                        <input type="text" name="kidney_disease" value="<?php echo htmlspecialchars($data['kidney_disease']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Disabled Status</label>
                        <input type="text" name="disabled" value="<?php echo htmlspecialchars($data['disabled']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Situation (Homeless/Disaster)</label>
                        <input type="text" name="housing_condition" value="<?php echo htmlspecialchars($data['housing_condition']); ?>">
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label>Remarks</label>
                        <textarea name="remarks" rows="2"><?php echo htmlspecialchars($data['remarks']); ?></textarea>
                    </div>

                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">
                    <button type="button" onclick="history.back()" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Update Record
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script src="assets/js/lang.js"></script>
</body>
</html>
