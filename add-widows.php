<?php
require_once 'includes/config.php';
check_auth();
$success = isset($_GET['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Widow Entry - GS System</title>
    <link rel="stylesheet" href="assets/css/style.css?v=3.0">
    <link rel="stylesheet" href="assets/css/responsive.css?v=1.3">
    <link rel="stylesheet" href="assets/css/theme.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background-color: #f8fafc;">
    <nav class="glass-dark" style="padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="widows.php" style="color: white;"><i class="fas fa-arrow-left"></i></a>
            <h2><i class="fas fa-user-tag" style="margin-right:0.5rem;"></i>Add Widow Entry</h2>
        </div>
    </nav>

    <div class="container animate-fade" style="max-width: 850px; padding: 2rem;">
        <?php if ($success): ?>
            <div style="background: rgba(16,185,129,0.1); border: 1px solid #10b981; color: #065f46; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <i class="fas fa-check-circle"></i> Widow entry saved successfully!
            </div>
        <?php endif; ?>

        <div class="glass" style="padding: 2.5rem; border-radius: var(--radius-lg);">
            <form action="api/save-widow.php" method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    
                    <div class="form-group">
                        <label>SNO</label>
                        <input type="number" name="sno" placeholder="Serial Number">
                    </div>

                    <div class="form-group">
                        <label>Full Name <span style="color:#ef4444">*</span></label>
                        <input type="text" name="name" required placeholder="Full Name">
                    </div>

                    <div class="form-group">
                        <label>House No <span style="color: var(--text-muted); font-size: 0.8rem;">(e.g., 177/1)</span></label>
                        <input type="text" name="house_no" placeholder="House Number">
                    </div>

                    <div class="form-group">
                        <label>Address <span style="color:#ef4444">*</span></label>
                        <input type="text" name="address" required placeholder="Address">
                    </div>

                    <div class="form-group">
                        <label>Family Member</label>
                        <input type="number" name="member_count" placeholder="e.g. 4">
                    </div>

                    <div class="form-group">
                        <label>NIC No</label>
                        <input type="text" name="nic" placeholder="NIC Number">
                    </div>

                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="dob">
                    </div>

                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" name="age" placeholder="Age">
                    </div>

                    <div class="form-group">
                        <label>Occupation</label>
                        <input type="text" name="occupation" placeholder="Occupation">
                    </div>

                    <div class="form-group">
                        <label>Conduct No</label>
                        <input type="text" name="contact" placeholder="Phone Number">
                    </div>

                    <div class="form-group">
                        <label>Person's House No <span style="color: var(--text-muted); font-size: 0.8rem;">(e.g., 179J/1)</span></label>
                        <input type="text" name="person_house_no" placeholder="If different">
                    </div>

                    <div class="form-group">
                        <label>Aswesuma</label>
                        <select name="aswesuma">
                            <option value="0">None</option>
                            <option value="Severely Poor">Severely Poor</option>
                            <option value="Poor">Poor</option>
                            <option value="Vulnerable">Vulnerable</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Elder Amount <span style="color: var(--text-muted); font-size: 0.8rem;">(e.g., 5000)</span></label>
                        <input type="text" name="elder" placeholder="Amount or text">
                    </div>

                    <div class="form-group">
                        <label>PAMA <span style="color: var(--text-muted); font-size: 0.8rem;">(e.g., 250)</span></label>
                        <input type="text" name="pmam" placeholder="Amount">
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label>Kidney Disease/Disabled</label>
                        <select name="health">
                            <option value="0">None</option>
                            <option value="Disease">Disease</option>
                            <option value="Disabled">Disabled</option>
                            <option value="Both">Both</option>
                        </select>
                    </div>

                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">
                    <a href="widows.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script src="assets/js/lang.js"></script>
</body>
</html>
