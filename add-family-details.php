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
    <title>Add Family Details - GS System</title>
    <link rel="stylesheet" href="assets/css/style.css?v=3.0">
    <link rel="stylesheet" href="assets/css/responsive.css?v=1.3">
    <link rel="stylesheet" href="assets/css/theme.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background-color: #f8fafc;">
    <nav class="glass-dark" style="padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="search.php" style="color: white;"><i class="fas fa-arrow-left"></i></a>
            <h2><i class="fas fa-folder-open" style="margin-right:0.5rem;"></i>Add Family Details</h2>
        </div>
        <div class="lang-switcher" style="position: static;">
            <button class="lang-btn" data-lang="en">EN</button>
            <button class="lang-btn" data-lang="ta">தமிழ்</button>
            <button class="lang-btn" data-lang="si">සිංහල</button>
        </div>
    </nav>

    <div class="container animate-fade" style="max-width: 900px;">
        <?php if ($success): ?>
            <div style="background: rgba(16,185,129,0.1); border: 1px solid #10b981; color: #065f46; padding: 1rem 1.5rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-check-circle"></i> Entry saved successfully!
            </div>
        <?php endif; ?>

        <div class="glass" style="padding: 2.5rem; border-radius: var(--radius-lg);">
            <h3 style="margin-bottom: 2rem; color: var(--primary); border-bottom: 2px solid #f1f5f9; padding-bottom: 1rem;">
                Family Details Entry
            </h3>

            <form action="api/save-family-details.php" method="POST">

                <!-- Family Info -->
                <p style="font-weight: 700; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">Family Information</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label>SNO</label>
                        <input type="number" name="sno" placeholder="Serial Number">
                    </div>
                    <div class="form-group">
                        <label>Family No</label>
                        <input type="text" name="family_no" placeholder="e.g. FAM-001">
                    </div>
                    <div class="form-group">
                        <label>House No</label>
                        <input type="text" name="house_no" placeholder="House Number">
                    </div>
                    <div class="form-group">
                        <label>Family Member Count</label>
                        <input type="number" name="member_count" placeholder="e.g. 4">
                    </div>
                </div>
                <div class="form-group">
                    <label>Address <span style="color:#ef4444">*</span></label>
                    <textarea name="address" rows="2" required placeholder="Full address..."></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label>Contact No <span style="color:#ef4444">*</span></label>
                        <input type="text" name="contact_no" required placeholder="Family contact number">
                    </div>
                    <div class="form-group">
                        <label>Income Level</label>
                        <select name="income_level">
                            <option value="">Select Level</option>
                            <option value="Low (< 25,000)">Low (< 25,000)</option>
                            <option value="Middle (25,000 - 75,000)">Middle (25,000 - 75,000)</option>
                            <option value="High (> 75,000)">High (> 75,000)</option>
                        </select>
                    </div>
                </div>

                <hr style="margin: 1.5rem 0; border-color: #e2e8f0;">

                <!-- Member Info -->
                <p style="font-weight: 700; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">Member Information</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Full Name <span style="color:#ef4444">*</span></label>
                        <input type="text" name="name" required placeholder="Full Name">
                    </div>
                    <div class="form-group">
                        <label>NIC No</label>
                        <input type="text" name="nic_no" placeholder="National ID">
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
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
                        <label>Personal Contact No</label>
                        <input type="text" name="member_contact" placeholder="Personal contact">
                    </div>
                    <div class="form-group">
                        <label>Person's House No <span style="color: var(--text-muted); font-size: 0.8rem;">(if different)</span></label>
                        <input type="text" name="person_house_no" placeholder="Leave blank if same">
                    </div>
                </div>

                <hr style="margin: 1.5rem 0; border-color: #e2e8f0;">

                <!-- Status Fields -->
                <p style="font-weight: 700; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">Status / Requirements</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
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
                        <label>Elder</label>
                        <input type="text" name="is_elder" placeholder="e.g. 5000 or Yes">
                    </div>
                    <div class="form-group">
                        <label>PMAM</label>
                        <input type="text" name="pmam" value="0" placeholder="Amount">
                    </div>
                    <div class="form-group">
                        <label>Kidney Disease</label>
                        <select name="kidney_disease">
                            <option value="0">No</option>
                            <option value="Disease">Yes (Disease)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Disabled</label>
                        <select name="disabled">
                            <option value="0">No</option>
                            <option value="Disabled">Yes (Disabled)</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                    <a href="search.php" class="btn btn-outline">Cancel</a>
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
