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
    <title>Add Homeless Entry - GS System</title>
    <link rel="stylesheet" href="assets/css/style.css?v=3.0">
    <link rel="stylesheet" href="assets/css/responsive.css?v=1.3">
    <link rel="stylesheet" href="assets/css/theme.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background-color: #f8fafc;">
    <nav class="glass-dark" style="padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="homeless.php" style="color: white;"><i class="fas fa-arrow-left"></i></a>
            <h2><i class="fas fa-house-crack" style="margin-right:0.5rem;"></i>Add Homeless Entry</h2>
        </div>
        <div class="lang-switcher" style="position: static;">
            <button class="lang-btn" data-lang="en">EN</button>
            <button class="lang-btn" data-lang="ta">தமிழ்</button>
            <button class="lang-btn" data-lang="si">සිංහල</button>
        </div>
    </nav>

    <div class="container animate-fade" style="max-width: 800px;">
        <?php if ($success): ?>
            <div style="background: rgba(16,185,129,0.1); border: 1px solid #10b981; color: #065f46; padding: 1rem 1.5rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-check-circle"></i> Entry saved successfully!
            </div>
        <?php endif; ?>

        <div class="glass" style="padding: 2.5rem; border-radius: var(--radius-lg);">
            <h3 style="margin-bottom: 2rem; color: var(--primary); border-bottom: 2px solid #f1f5f9; padding-bottom: 1rem;">
                House Needs (Housing Assistance) Entry
            </h3>

            <form action="api/save-homeless.php" method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>SNO</label>
                        <input type="number" name="sno" placeholder="Serial Number">
                    </div>
                    <div class="form-group">
                        <label>Family No</label>
                        <input type="text" name="family_no" placeholder="e.g. FAM-001">
                    </div>
                    <div class="form-group">
                        <label>Name <span style="color:#ef4444">*</span></label>
                        <input type="text" name="name" required placeholder="Full Name">
                    </div>
                    <div class="form-group">
                        <label>NIC No</label>
                        <input type="text" name="nic_no" placeholder="National ID">
                    </div>
                    <div class="form-group">
                        <label>House No</label>
                        <input type="text" name="house_no" placeholder="House Number">
                    </div>
                    <div class="form-group">
                        <label>Road</label>
                        <input type="text" name="road" placeholder="Road / Street">
                    </div>
                    <div class="form-group">
                        <label>Occupation</label>
                        <input type="text" name="occupation" placeholder="Occupation">
                    </div>
                    <div class="form-group">
                        <label>Phone No <span style="color:#ef4444">*</span></label>
                        <input type="text" name="phone_no" required placeholder="Contact Number">
                    </div>
                </div>

                <div class="form-group">
                    <label>Current Situation / Housing Details</label>
                    <textarea name="situation" rows="2" placeholder="Describe the current housing situation..."></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
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
                        <label>Remarks</label>
                        <textarea name="remarks" rows="2" placeholder="Additional remarks..."></textarea>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                    <a href="homeless.php" class="btn btn-outline">Cancel</a>
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
