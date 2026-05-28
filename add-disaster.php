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
    <title>Add Disaster Record - GS System</title>
    <link rel="stylesheet" href="assets/css/style.css?v=3.0">
    <link rel="stylesheet" href="assets/css/responsive.css?v=1.3">
    <link rel="stylesheet" href="assets/css/theme.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background-color: #f8fafc;">
    <nav class="glass-dark" style="padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="disaster.php" style="color: white;"><i class="fas fa-arrow-left"></i></a>
            <h2><i class="fas fa-house-damage" style="margin-right:0.5rem;"></i>Add Disaster Record</h2>
        </div>
    </nav>

    <div class="container animate-fade" style="max-width: 850px; padding: 2rem;">
        <?php if ($success): ?>
            <div style="background: rgba(16,185,129,0.1); border: 1px solid #10b981; color: #065f46; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <i class="fas fa-check-circle"></i> Disaster record saved successfully!
            </div>
        <?php endif; ?>

        <div class="glass" style="padding: 2.5rem; border-radius: var(--radius-lg);">
            <form action="api/save-disaster.php" method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    
                    <div class="form-group">
                        <label>SNO</label>
                        <input type="number" name="sno" placeholder="Serial Number">
                    </div>

                    <div class="form-group">
                        <label>Head of Family Name <span style="color:#ef4444">*</span></label>
                        <input type="text" name="name" required placeholder="Full Name">
                    </div>

                    <div class="form-group">
                        <label>House No</label>
                        <input type="text" name="house_no" placeholder="House Number">
                    </div>

                    <div class="form-group">
                        <label>Address <span style="color:#ef4444">*</span></label>
                        <input type="text" name="address" required placeholder="Address">
                    </div>

                    <div class="form-group">
                        <label>NIC No</label>
                        <input type="text" name="nic" placeholder="NIC Number">
                    </div>

                    <div class="form-group">
                        <label>Contact No</label>
                        <input type="text" name="contact" placeholder="Phone Number">
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
                        <label>Disaster Type / Situation <span style="color: var(--text-muted); font-size: 0.8rem;">(e.g., Flood, Fire)</span></label>
                        <input type="text" name="situation" placeholder="Nature of damage">
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label>Remarks / Assistance Needed</label>
                        <textarea name="remarks" rows="3" placeholder="Additional notes..."></textarea>
                    </div>

                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">
                    <a href="disaster.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Record
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script src="assets/js/lang.js"></script>
</body>
</html>
