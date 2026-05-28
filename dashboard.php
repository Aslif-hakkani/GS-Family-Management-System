<?php
require_once 'includes/config.php';
check_auth();

try {
    // ── Core counts ──────────────────────────────────────────
    $totalFamilies = $pdo->query("SELECT COUNT(DISTINCT family_number) FROM families WHERE family_number IS NOT NULL AND family_number != ''")->fetchColumn();
    $totalMembers  = $pdo->query("SELECT COUNT(*) FROM persons")->fetchColumn();

    // ── Gender distribution ───────────────────────────────────
    $genderRows = $pdo->query("SELECT gender, COUNT(*) as cnt FROM persons GROUP BY gender")->fetchAll();
    $genderMap = ['Male'=>0,'Female'=>0,'Other'=>0];
    foreach ($genderRows as $g) {
        $k = ucfirst(strtolower($g['gender'] ?? ''));
        if (isset($genderMap[$k])) $genderMap[$k] = (int)$g['cnt'];
    }

    // ── Age groups ────────────────────────────────────────────
    $ageGroups = $pdo->query("
        SELECT
          SUM(CASE WHEN age < 18  THEN 1 ELSE 0 END) as youth,
          SUM(CASE WHEN age BETWEEN 18 AND 35 THEN 1 ELSE 0 END) as adult,
          SUM(CASE WHEN age BETWEEN 36 AND 59 THEN 1 ELSE 0 END) as middle,
          SUM(CASE WHEN age >= 60 THEN 1 ELSE 0 END) as senior
        FROM persons WHERE age IS NOT NULL
    ")->fetch();

    // ── Category distribution ─────────────────────────────────
    $cats = [
        'widow'    => ['Widows',          '#ec4899', 'fas fa-person-dress-burst', 'widows.php'],
        'pregnant' => ['Pregnant',         '#f43f5e', 'fas fa-person-pregnant',   'pregnant.php'],
        'elderly'  => ['Elderly',          '#8b5cf6', 'fas fa-person-cane',       'elderly.php'],
        'homeless' => ['House Needs',      '#f59e0b', 'fas fa-house-crack',       'homeless.php'],
        'disaster' => ['Disaster Affected','#ef4444', 'fas fa-cloud-showers-heavy','disaster.php'],
        'general'  => ['Family Details',   '#1a5fa8', 'fas fa-folder-open',       'search.php'],
    ];
    $catCounts = [];
    foreach (array_keys($cats) as $cat) {
        $catCounts[$cat] = (int)$pdo->query("SELECT COUNT(*) FROM person_page_records WHERE page_category='$cat'")->fetchColumn();
    }

    // ── Alerts ────────────────────────────────────────────────
    $missingNIC  = (int)$pdo->query("SELECT COUNT(*) FROM persons WHERE nic IS NULL OR nic=''")->fetchColumn();
    $missingDOB  = (int)$pdo->query("SELECT COUNT(*) FROM persons WHERE dob IS NULL OR dob=''")->fetchColumn();
    $missingName = (int)$pdo->query("SELECT COUNT(*) FROM persons WHERE full_name IS NULL OR full_name=''")->fetchColumn();
    $dupNIC      = (int)$pdo->query("SELECT COUNT(*) FROM (SELECT nic FROM persons WHERE nic IS NOT NULL AND nic!='' GROUP BY nic HAVING COUNT(*)>1) t")->fetchColumn();
    $missingAddr = (int)$pdo->query("SELECT COUNT(*) FROM families WHERE address IS NULL OR address=''")->fetchColumn();

    $totalAlerts = $missingNIC + $missingDOB + $missingName + $dupNIC + $missingAddr;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-t="title">GS Family Management System</title>
    <link rel="stylesheet" href="assets/css/style.css?v=3.0">
    <link rel="stylesheet" href="assets/css/responsive.css?v=1.3">
    <link rel="stylesheet" href="assets/css/theme.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ── Dashboard-specific styles ── */
        .dash-grid { display:grid; gap:1.25rem; }
        .dash-grid-2 { grid-template-columns: repeat(2,1fr); }
        .dash-grid-3 { grid-template-columns: repeat(3,1fr); }
        .dash-grid-4 { grid-template-columns: repeat(4,1fr); }
        .dash-grid-6 { grid-template-columns: repeat(6,1fr); }

        .widget { background:#fff; border-radius:14px; padding:1.25rem 1.35rem; border:1px solid #dde5f0; box-shadow:0 2px 8px rgba(26,95,168,.07); transition:transform .18s,box-shadow .18s; }
        .widget:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(26,95,168,.12); }

        /* KPI widget */
        .kpi-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; margin-bottom:.75rem; }
        .kpi-val  { font-size:2rem; font-weight:700; font-family:'Outfit',sans-serif; line-height:1; color:#0f172a; }
        .kpi-label{ font-size:.78rem; color:#64748b; margin-top:.3rem; font-weight:500; text-transform:uppercase; letter-spacing:.4px; }
        .kpi-sub  { font-size:.72rem; color:#94a3b8; margin-top:.2rem; }

        /* Section header */
        .section-hd { display:flex; align-items:center; gap:.6rem; margin-bottom:1rem; }
        .section-hd h3 { margin:0; font-size:1rem; color:#0f172a; }
        .section-hd .pill { background:var(--primary-pale,#e8f1fb); color:#1a5fa8; font-size:.7rem; font-weight:700; padding:.15rem .55rem; border-radius:20px; }

        /* Category card */
        .cat-card { display:flex; align-items:center; gap:.85rem; padding:1rem 1.1rem; background:#fff; border-radius:12px; border:1px solid #dde5f0; text-decoration:none; color:inherit; transition:all .18s; }
        .cat-card:hover { border-color:var(--primary,#1a5fa8); box-shadow:0 4px 14px rgba(26,95,168,.13); transform:translateY(-2px); }
        .cat-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
        .cat-info p { margin:0; font-size:.73rem; color:#64748b; }
        .cat-info strong { font-size:1.15rem; font-family:'Outfit',sans-serif; color:#0f172a; }

        /* Gender bar */
        .gender-bar { display:flex; height:10px; border-radius:20px; overflow:hidden; margin:.6rem 0; }
        .gender-bar-m { background:#1a5fa8; }
        .gender-bar-f { background:#ec4899; }
        .gender-bar-o { background:#94a3b8; }

        /* Age bar */
        .age-row { display:flex; align-items:center; gap:.6rem; margin-bottom:.55rem; }
        .age-label { width:90px; font-size:.73rem; color:#4b5563; flex-shrink:0; }
        .age-bar-wrap { flex:1; background:#eef2f7; border-radius:20px; height:8px; overflow:hidden; }
        .age-bar-fill { height:100%; border-radius:20px; transition:width .5s ease; }
        .age-count { font-size:.73rem; font-weight:600; color:#0f172a; width:42px; text-align:right; flex-shrink:0; }

        /* Alert item */
        .alert-item { display:flex; align-items:center; gap:.75rem; padding:.7rem .85rem; border-radius:10px; margin-bottom:.5rem; border:1px solid transparent; }
        .alert-item:last-child { margin-bottom:0; }
        .alert-warn  { background:#fffbeb; border-color:#fde68a; }
        .alert-danger { background:#fff5f5; border-color:#fecaca; }
        .alert-info   { background:#f0f9ff; border-color:#bae6fd; }
        .alert-ok     { background:#f0fdf4; border-color:#bbf7d0; }
        .alert-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:.85rem; flex-shrink:0; }
        .alert-text p { margin:0; font-size:.78rem; font-weight:600; color:#0f172a; }
        .alert-text span { font-size:.7rem; color:#64748b; }
        .alert-badge { margin-left:auto; font-size:.7rem; font-weight:700; padding:.15rem .5rem; border-radius:20px; flex-shrink:0; }


        @media(max-width:900px) {
            .dash-grid-4,.dash-grid-6 { grid-template-columns:repeat(2,1fr); }
            .dash-grid-3 { grid-template-columns:repeat(2,1fr); }
        }
        @media(max-width:600px) {
            .dash-grid-2,.dash-grid-3,.dash-grid-4,.dash-grid-6 { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>
<nav class="glass-dark" style="padding:.85rem 1.5rem; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; z-index:200;">
    <div style="display:flex; align-items:center; gap:.75rem;">
        <div style="width:36px;height:36px;background:white;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#1a5fa8;font-weight:700;font-size:.9rem;">GS</div>
        <h2 data-t="title" style="font-size:1.1rem;margin:0;color:white;">GS Family Management System</h2>
    </div>
    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
        <div class="lang-switcher" style="position:static;">
            <button class="lang-btn" data-lang="en">EN</button>
            <button class="lang-btn" data-lang="ta">தமிழ்</button>
            <button class="lang-btn" data-lang="si">සිංහල</button>
        </div>
        <a href="logout.php" class="btn" style="padding:.4rem .85rem;font-size:.85rem;background:rgba(239,68,68,.15);color:#fca5a5;border:1px solid rgba(239,68,68,.3);text-decoration:none;border-radius:8px;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>

<div class="container animate-fade" style="max-width:1400px;">

    <!-- ── Row 1: KPI Cards ────────────────────────────────── -->
    <div class="dash-grid dash-grid-4" style="margin-bottom:1.5rem;">

        <div class="widget">
            <div class="kpi-icon" style="background:#e8f1fb;color:#1a5fa8;"><i class="fas fa-home"></i></div>
            <div class="kpi-val"><?= number_format($totalFamilies) ?></div>
            <div class="kpi-label">Total Families</div>
            <div class="kpi-sub">Unique family units registered</div>
        </div>

        <div class="widget">
            <div class="kpi-icon" style="background:#f0fdf4;color:#059669;"><i class="fas fa-users"></i></div>
            <div class="kpi-val"><?= number_format($totalMembers) ?></div>
            <div class="kpi-label">Total Members</div>
            <div class="kpi-sub">Persons across all categories</div>
        </div>

        <div class="widget">
            <div class="kpi-icon" style="background:#eff6ff;color:#3b82f6;"><i class="fas fa-mars"></i></div>
            <div class="kpi-val"><?= number_format($genderMap['Male']) ?></div>
            <div class="kpi-label">Male Members</div>
            <?php $maleP = $totalMembers>0 ? round($genderMap['Male']/$totalMembers*100,1) : 0; ?>
            <div class="kpi-sub"><?= $maleP ?>% of total members</div>
        </div>

        <div class="widget">
            <div class="kpi-icon" style="background:#fdf2f8;color:#ec4899;"><i class="fas fa-venus"></i></div>
            <div class="kpi-val"><?= number_format($genderMap['Female']) ?></div>
            <div class="kpi-label">Female Members</div>
            <?php $femP = $totalMembers>0 ? round($genderMap['Female']/$totalMembers*100,1) : 0; ?>
            <div class="kpi-sub"><?= $femP ?>% of total members</div>
        </div>

    </div>

    <!-- ── Row 2: Gender + Age + Alerts ───────────────────── -->
    <div class="dash-grid dash-grid-3" style="margin-bottom:1.5rem;">

        <!-- Gender Widget -->
        <div class="widget">
            <div class="section-hd"><i class="fas fa-venus-mars" style="color:#1a5fa8;"></i><h3>Gender Distribution</h3></div>
            <?php
            $total = max(1,$totalMembers);
            $mW = round($genderMap['Male']/$total*100,1);
            $fW = round($genderMap['Female']/$total*100,1);
            $oW = round($genderMap['Other']/$total*100,1);
            ?>
            <div class="gender-bar" style="margin:.85rem 0;">
                <div class="gender-bar-m" style="width:<?=$mW?>%;"></div>
                <div class="gender-bar-f" style="width:<?=$fW?>%;"></div>
                <div class="gender-bar-o" style="width:<?=$oW?>%;"></div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin-top:.75rem;">
                <div style="text-align:center;">
                    <div style="font-size:1.2rem;font-weight:700;color:#1a5fa8;"><?=$genderMap['Male']?></div>
                    <div style="font-size:.7rem;color:#64748b;display:flex;align-items:center;justify-content:center;gap:.25rem;"><span style="width:8px;height:8px;border-radius:50%;background:#1a5fa8;display:inline-block;"></span>Male</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:1.2rem;font-weight:700;color:#ec4899;"><?=$genderMap['Female']?></div>
                    <div style="font-size:.7rem;color:#64748b;display:flex;align-items:center;justify-content:center;gap:.25rem;"><span style="width:8px;height:8px;border-radius:50%;background:#ec4899;display:inline-block;"></span>Female</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:1.2rem;font-weight:700;color:#94a3b8;"><?=$genderMap['Other']?></div>
                    <div style="font-size:.7rem;color:#64748b;display:flex;align-items:center;justify-content:center;gap:.25rem;"><span style="width:8px;height:8px;border-radius:50%;background:#94a3b8;display:inline-block;"></span>Other</div>
                </div>
            </div>
        </div>

        <!-- Age Groups Widget -->
        <div class="widget">
            <div class="section-hd"><i class="fas fa-chart-bar" style="color:#1a5fa8;"></i><h3>Age Distribution</h3></div>
            <?php
            $ageTotal = max(1, ($ageGroups['youth']+$ageGroups['adult']+$ageGroups['middle']+$ageGroups['senior']));
            $ageData = [
                ['Under 18',   $ageGroups['youth'],  '#60a5fa'],
                ['18 – 35',    $ageGroups['adult'],  '#1a5fa8'],
                ['36 – 59',    $ageGroups['middle'], '#7c3aed'],
                ['60 & Above', $ageGroups['senior'], '#0891b2'],
            ];
            ?>
            <div style="margin-top:.5rem;">
            <?php foreach ($ageData as [$lbl,$cnt,$clr]): $pct = round($cnt/$ageTotal*100); ?>
                <div class="age-row">
                    <div class="age-label"><?=$lbl?></div>
                    <div class="age-bar-wrap"><div class="age-bar-fill" style="width:<?=$pct?>%;background:<?=$clr?>;"></div></div>
                    <div class="age-count"><?=number_format($cnt)?></div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>

        <!-- Alerts Widget -->
        <div class="widget">
            <div class="section-hd">
                <i class="fas fa-bell" style="color:#f59e0b;"></i>
                <h3>Data Alerts</h3>
                <?php if($totalAlerts>0): ?>
                <span class="pill" style="background:#fff3cd;color:#856404;"><?=$totalAlerts?> Issues</span>
                <?php else: ?>
                <span class="pill" style="background:#d1fae5;color:#065f46;">All Clear</span>
                <?php endif; ?>
            </div>

            <?php if($dupNIC > 0): ?>
            <div class="alert-item alert-danger">
                <div class="alert-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-fingerprint"></i></div>
                <div class="alert-text"><p>Duplicate NICs</p><span>Same NIC linked to multiple persons</span></div>
                <span class="alert-badge" style="background:#fee2e2;color:#dc2626;"><?=$dupNIC?></span>
            </div>
            <?php endif; ?>

            <?php if($missingNIC > 0): ?>
            <div class="alert-item alert-warn">
                <div class="alert-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-id-card"></i></div>
                <div class="alert-text"><p>Missing NIC</p><span>Records without NIC number</span></div>
                <span class="alert-badge" style="background:#fef3c7;color:#d97706;"><?=$missingNIC?></span>
            </div>
            <?php endif; ?>

            <?php if($missingDOB > 0): ?>
            <div class="alert-item alert-warn">
                <div class="alert-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-calendar-times"></i></div>
                <div class="alert-text"><p>Missing Date of Birth</p><span>Records without DOB</span></div>
                <span class="alert-badge" style="background:#fef3c7;color:#d97706;"><?=$missingDOB?></span>
            </div>
            <?php endif; ?>

            <?php if($missingName > 0): ?>
            <div class="alert-item alert-danger">
                <div class="alert-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-user-times"></i></div>
                <div class="alert-text"><p>Missing Name</p><span>Records without full name</span></div>
                <span class="alert-badge" style="background:#fee2e2;color:#dc2626;"><?=$missingName?></span>
            </div>
            <?php endif; ?>

            <?php if($missingAddr > 0): ?>
            <div class="alert-item alert-info">
                <div class="alert-icon" style="background:#e0f2fe;color:#0369a1;"><i class="fas fa-map-marker-alt"></i></div>
                <div class="alert-text"><p>Missing Address</p><span>Families without address info</span></div>
                <span class="alert-badge" style="background:#e0f2fe;color:#0369a1;"><?=$missingAddr?></span>
            </div>
            <?php endif; ?>

            <?php if($totalAlerts === 0): ?>
            <div class="alert-item alert-ok">
                <div class="alert-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-check-circle"></i></div>
                <div class="alert-text"><p>No Issues Found</p><span>All records look complete</span></div>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ── Row 3: Category Cards ──────────────────────────── -->
    <div style="margin-bottom:1.5rem;">
        <div class="section-hd" style="margin-bottom:1rem;">
            <i class="fas fa-th-large" style="color:#1a5fa8;"></i>
            <h3 style="margin:0;font-size:1rem;color:#0f172a;">Special Categories</h3>
            <span class="pill">6 Modules</span>
        </div>
        <div class="dash-grid dash-grid-3">
            <?php foreach ($cats as $key => [$label,$color,$icon,$link]): ?>
            <a href="<?=$link?>" class="cat-card">
                <div class="cat-icon" style="background:<?=$color?>18;color:<?=$color?>;"><i class="<?=$icon?>"></i></div>
                <div class="cat-info">
                    <p><?=$label?></p>
                    <strong><?=number_format($catCounts[$key])?></strong>
                </div>
                <i class="fas fa-chevron-right" style="margin-left:auto;color:#cbd5e1;font-size:.75rem;"></i>
            </a>
            <?php endforeach; ?>
        </div>
    </div>


</div><!-- /container -->

<script src="assets/js/lang.js"></script>
</body>
</html>
