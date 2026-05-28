<?php
require_once 'includes/config.php';
check_auth();

$id = $_GET['id'] ?? 0;

try {
    // Fetch Family
    $stmtFam = $pdo->prepare("SELECT * FROM families WHERE id = ?");
    $stmtFam->execute([$id]);
    $family = $stmtFam->fetch();

    if (!$family) {
        die("Family not found.");
    }

    // Fetch Members joining persons and page records
    $stmtMem = $pdo->prepare("SELECT p.*, r.* 
                             FROM person_page_records r
                             JOIN persons p ON r.person_id = p.id
                             WHERE r.family_id = ?");
    $stmtMem->execute([$id]);
    $members = $stmtMem->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Helper: normalize gender display (M → Male, F → Female)
function normalizeGender($val) {
    $map = ['m' => 'Male', 'f' => 'Female', 'male' => 'Male', 'female' => 'Female', 'other' => 'Other'];
    return $map[strtolower(trim($val ?? ''))] ?? ($val ?: 'N/A');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-t="view">View Family - GS System</title>
    <link rel="stylesheet" href="assets/css/style.css?v=3.0">
    <link rel="stylesheet" href="assets/css/responsive.css?v=1.3">
    <link rel="stylesheet" href="assets/css/theme.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .glass-dark, .btn, .lang-switcher, .nav, .lang-btn, a[href*="logout"] { display: none !important; }
            .container { max-width: 100% !important; padding: 0 !important; }
            .glass { border: none !important; box-shadow: none !important; padding: 0 !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body style="background-color: #f1f5f9;">
    <nav class="glass-dark" style="padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="search.php" style="color: white;"><i class="fas fa-arrow-left"></i></a>
            <h2 data-t="view">Family Details</h2>
        </div>
        <div class="lang-switcher" style="position: static;">
            <button class="lang-btn" data-lang="en">EN</button>
            <button class="lang-btn" data-lang="ta">தமிழ்</button>
            <button class="lang-btn" data-lang="si">සිංහල</button>
        </div>
    </nav>

    <div class="container animate-fade">
        <!-- Family Info Card -->
        <div class="glass" style="padding: 2.5rem; border-radius: var(--radius-lg); margin-bottom: 2rem; position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; width: 5px; height: 100%; background: var(--primary);"></div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                <div>
                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 1px;">SNO: <?php echo htmlspecialchars($family['sno'] ?: 'N/A'); ?> | Family Number: <?php echo htmlspecialchars($family['family_number'] ?: 'N/A'); ?></span>
                    <h2 style="font-size: 2rem; color: var(--text-dark);"><?php echo htmlspecialchars($family['family_code']); ?></h2>
                </div>
                <div style="text-align: right;">
                    <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
                        <?php if ($family['is_homeless']): ?>
                            <span style="background: rgba(245, 158, 11, 0.1); color: #b45309; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700;" data-t="homeless">HOUSE NEEDS</span>
                        <?php endif; ?>
                        <?php if ($family['is_disaster']): ?>
                            <span style="background: rgba(239, 68, 68, 0.1); color: #dc2626; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700;" data-t="disaster">DISASTER</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-top: 1rem;">
                <div>
                    <label style="color: var(--text-muted); font-size: 0.8rem;">House No & Road</label>
                    <p style="font-weight: 500;"><?php echo htmlspecialchars($family['house_number'] ?: 'N/A'); ?>, <?php echo htmlspecialchars($family['road'] ?: 'N/A'); ?></p>
                </div>
                <div style="grid-column: span 2;">
                    <label style="color: var(--text-muted); font-size: 0.8rem;" data-t="address">Address</label>
                    <p style="font-weight: 500;"><?php echo nl2br(htmlspecialchars($family['address'])); ?></p>
                </div>
                <div>
                    <label style="color: var(--text-muted); font-size: 0.8rem;" data-t="contact">Contact Number</label>
                    <p style="font-weight: 500;"><?php echo htmlspecialchars($family['contact_no']); ?></p>
                </div>
                <div>
                    <label style="color: var(--text-muted); font-size: 0.8rem;">Current Situation</label>
                    <p style="font-weight: 500; font-size: 0.9rem;"><?php echo nl2br(htmlspecialchars($family['housing_condition'] ?: 'N/A')); ?></p>
                </div>
                <div>
                    <label style="color: var(--text-muted); font-size: 0.8rem;">Remarks</label>
                    <p style="font-weight: 500; font-size: 0.9rem;"><?php echo nl2br(htmlspecialchars($family['remarks'] ?: 'N/A')); ?></p>
                </div>
            </div>
        </div>

        <!-- Members List -->
        <h3 style="margin-bottom: 1.5rem;">Family Members (<?php echo count($members); ?>)</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">
            <?php foreach ($members as $member): ?>
                <div class="glass animate-fade" style="padding: 1.5rem; border-radius: var(--radius-md); border-top: 3px solid var(--primary);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h4 style="color: var(--primary); font-size: 1.25rem;"><?php echo htmlspecialchars($member['full_name']); ?></h4>
                        <span style="font-size: 0.75rem; background: #e2e8f0; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600;"><?php echo htmlspecialchars($member['relationship'] ?? 'Member'); ?></span>
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
                        <?php if ($member['aswesuma']): ?>
                            <span style="background: rgba(16, 185, 129, 0.1); color: #059669; padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.7rem; font-weight: 700;">ASWESUMA</span>
                        <?php endif; ?>
                        <?php if ($member['kidney_disease']): ?>
                            <span style="background: rgba(239, 68, 68, 0.1); color: #dc2626; padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.7rem; font-weight: 700;">KIDNEY DISEASE</span>
                        <?php endif; ?>
                        <?php if ($member['disabled']): ?>
                            <span style="background: rgba(245, 158, 11, 0.1); color: #b45309; padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.7rem; font-weight: 700;">DISABLED</span>
                        <?php endif; ?>
                        <?php if ($member['is_widow']): ?>
                            <span style="background: rgba(236, 72, 153, 0.1); color: #db2777; padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.7rem; font-weight: 700;">WIDOW</span>
                        <?php endif; ?>
                        <?php if ($member['is_pregnant']): ?>
                            <span style="background: rgba(244, 63, 94, 0.1); color: #e11d48; padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.7rem; font-weight: 700;">PREGNANT</span>
                        <?php endif; ?>
                        <?php if ($member['is_elder'] || $member['age'] >= 60): ?>
                            <span style="background: rgba(139, 92, 246, 0.1); color: #7c3aed; padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.7rem; font-weight: 700;">ELDERLY</span>
                        <?php endif; ?>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                        <div>
                            <span style="display: block; font-size: 0.75rem; color: var(--text-muted);" data-t="nic">NIC Number</span>
                            <span style="font-weight: 500;"><?php echo htmlspecialchars($member['nic'] ?: 'N/A'); ?></span>
                        </div>
                        <div>
                            <span style="display: block; font-size: 0.75rem; color: var(--text-muted);">Date of Birth</span>
                            <span style="font-weight: 500;"><?php echo htmlspecialchars($member['dob'] ?: 'N/A'); ?></span>
                        </div>
                        <div>
                            <span style="display: block; font-size: 0.75rem; color: var(--text-muted);">Occupation</span>
                            <span style="font-weight: 500; font-size: 0.9rem;"><?php echo htmlspecialchars($member['occupation'] ?: 'N/A'); ?></span>
                        </div>
                        <div>
                            <span style="display: block; font-size: 0.75rem; color: var(--text-muted);">Personal Contact</span>
                            <span style="font-weight: 500; font-size: 0.9rem;"><?php echo htmlspecialchars($member['contact_number'] ?: 'N/A'); ?></span>
                        </div>
                        <div>
                            <span style="display: block; font-size: 0.75rem; color: var(--text-muted);">Age / Gender</span>
                            <span style="font-weight: 500;"><?php echo $member['age']; ?> / <?php echo htmlspecialchars(normalizeGender($member['gender'])); ?></span>
                        </div>
                        <div>
                            <span style="display: block; font-size: 0.75rem; color: var(--text-muted);">PMAM Value</span>
                            <span style="font-weight: 500;"><?php echo $member['pmam']; ?></span>
                        </div>
                        <div style="grid-column: span 2;">
                            <span style="display: block; font-size: 0.75rem; color: var(--text-muted);">Member House No.</span>
                            <span style="font-weight: 500; font-size: 0.9rem;"><?php echo htmlspecialchars($member['person_house_number'] ?: 'Same as Family'); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="margin-top: 3rem; text-align: center;">
            <a href="edit-family.php?family_id=<?php echo $id; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> <span data-t="edit">Edit Family</span>
            </a>
            <button onclick="window.print()" class="btn btn-outline" style="margin-left: 1rem;">
                <i class="fas fa-print"></i> <span>Print Report</span>
            </button>
        </div>
    </div>

    <script src="assets/js/lang.js"></script>
</body>
</html>
