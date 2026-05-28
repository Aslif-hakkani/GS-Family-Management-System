<?php
require_once 'includes/config.php';
check_auth();

// ── Advanced filter parameters ────────────────────────────────────────────────
$f_sno          = trim($_GET['f_sno']          ?? '');
$f_family_no    = trim($_GET['f_family_no']    ?? '');
$f_name         = trim($_GET['f_name']         ?? '');
$f_house_no     = trim($_GET['f_house_no']     ?? '');
$f_address      = trim($_GET['f_address']      ?? '');
$f_sex          = trim($_GET['f_sex']          ?? '');
$f_member       = trim($_GET['f_member']       ?? '');
$f_nic          = trim($_GET['f_nic']          ?? '');
$f_dob_from     = trim($_GET['f_dob_from']     ?? '');
$f_dob_to       = trim($_GET['f_dob_to']       ?? '');
$f_age_min      = trim($_GET['f_age_min']      ?? '');
$f_age_max      = trim($_GET['f_age_max']      ?? '');
$f_occupation   = trim($_GET['f_occupation']   ?? '');
$f_conduct_no   = trim($_GET['f_conduct_no']   ?? '');
$f_person_house = trim($_GET['f_person_house'] ?? '');
$f_income       = trim($_GET['f_income']       ?? '');
$f_aswesuma     = trim($_GET['f_aswesuma']     ?? '');
$f_elder        = trim($_GET['f_elder']        ?? '');
$f_pama         = trim($_GET['f_pama']         ?? '');
$f_kidney       = trim($_GET['f_kidney']       ?? '');

// Legacy compat
$search = trim($_GET['q'] ?? '');
$income = $f_income ?: trim($_GET['income'] ?? '');

// Count active filters for badge
$activeFilters = array_filter([$f_sno,$f_family_no,$f_name,$f_house_no,$f_address,
    $f_sex,$f_member,$f_nic,$f_dob_from,$f_dob_to,$f_age_min,$f_age_max,
    $f_occupation,$f_conduct_no,$f_person_house,$income,$f_aswesuma,$f_elder,$f_pama,$f_kidney,$search]);
$activeCount = count($activeFilters);

// ── Pagination ─────────────────────────────────────────────────────────────────
$perPage     = 50;
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$pageFile    = 'search.php';

// ── Build shared WHERE clause + params ────────────────────────────────────────
$where  = "f.page_category = 'general'";
$params = [];

if ($f_sno !== '')          { $where .= " AND r.person_sno = ?";               $params[] = (int)$f_sno; }
if ($f_family_no !== '')    { $where .= " AND f.family_number = ?";             $params[] = $f_family_no; }
if ($f_name !== '')         { $where .= " AND p.full_name LIKE ?";              $params[] = "%$f_name%"; }
if ($f_house_no !== '')     { $where .= " AND f.house_number LIKE ?";           $params[] = "%$f_house_no%"; }
if ($f_address !== '')      { $where .= " AND f.address LIKE ?";                $params[] = "%$f_address%"; }
if ($f_sex !== '')          { $where .= " AND p.gender = ?";                    $params[] = $f_sex; }
if ($f_member !== '')       { $where .= " AND f.member_count = ?";              $params[] = (int)$f_member; }
if ($f_nic !== '')          { $where .= " AND p.nic LIKE ?";                    $params[] = "%$f_nic%"; }
if ($f_dob_from !== '')     { $where .= " AND p.dob >= ?";                      $params[] = $f_dob_from; }
if ($f_dob_to !== '')       { $where .= " AND p.dob <= ?";                      $params[] = $f_dob_to; }
if ($f_age_min !== '')      { $where .= " AND p.age >= ?";                      $params[] = (int)$f_age_min; }
if ($f_age_max !== '')      { $where .= " AND p.age <= ?";                      $params[] = (int)$f_age_max; }
if ($f_occupation !== '')   { $where .= " AND p.occupation LIKE ?";             $params[] = "%$f_occupation%"; }
if ($f_conduct_no !== '')   { $where .= " AND p.contact_number LIKE ?";         $params[] = "%$f_conduct_no%"; }
if ($f_person_house !== '') { $where .= " AND p.person_house_number LIKE ?";    $params[] = "%$f_person_house%"; }
if ($income !== '')         { $where .= " AND f.income_level = ?";              $params[] = $income; }
if (!empty($search)) {
    $st = "%$search%";
    $where .= " AND (f.address LIKE ? OR f.family_code LIKE ? OR f.family_number LIKE ? OR p.full_name LIKE ? OR p.nic LIKE ?)";
    array_push($params, $st, $st, $st, $st, $st);
}

$noVals = "('0','','No','no','None','none','N','n')";
if ($f_aswesuma === 'yes')  $where .= " AND (r.aswesuma NOT IN $noVals AND r.aswesuma IS NOT NULL)";
elseif ($f_aswesuma === 'no')  $where .= " AND (r.aswesuma IN $noVals OR r.aswesuma IS NULL)";
if ($f_elder === 'yes')     $where .= " AND (r.is_elder NOT IN $noVals AND r.is_elder IS NOT NULL)";
elseif ($f_elder === 'no')     $where .= " AND (r.is_elder IN $noVals OR r.is_elder IS NULL)";
if ($f_pama === 'yes')      $where .= " AND (r.pmam IS NOT NULL AND r.pmam > 0)";
elseif ($f_pama === 'no')      $where .= " AND (r.pmam IS NULL OR r.pmam = 0)";
if ($f_kidney === 'yes')    $where .= " AND (r.kidney_disease NOT IN $noVals AND r.kidney_disease IS NOT NULL)";
elseif ($f_kidney === 'no')    $where .= " AND (r.kidney_disease IN $noVals OR r.kidney_disease IS NULL)";

$orderBy = "ORDER BY CASE WHEN r.person_sno IS NULL OR r.person_sno = 0 THEN 1 ELSE 0 END, r.person_sno ASC, f.id DESC, r.id ASC";

try {
    // ── COUNT query (total matching rows) ─────────────────────────────────────
    $countSQL  = "SELECT COUNT(*)
                  FROM families f
                  LEFT JOIN person_page_records r ON r.family_id = f.id
                  LEFT JOIN persons p ON r.person_id = p.id
                  WHERE $where";
    $countStmt = $pdo->prepare($countSQL);
    $countStmt->execute($params);
    $totalRecords = (int)$countStmt->fetchColumn();
    $totalPages   = max(1, (int)ceil($totalRecords / $perPage));
    $currentPage  = min($currentPage, $totalPages);
    $offset       = ($currentPage - 1) * $perPage;

    // ── Main SELECT with LIMIT / OFFSET ───────────────────────────────────────
    $query = "SELECT
                f.id as family_id, f.sno, f.family_number, f.house_number, f.address, f.contact_no, f.income_level, f.family_code, f.member_count,
                p.id as person_id, r.id as record_id, r.person_sno, p.full_name, p.gender, p.nic, p.dob, p.age,
                p.occupation, p.contact_number, p.person_house_number,
                r.aswesuma, r.pmam, r.kidney_disease, r.disabled,
                r.is_widow, r.is_pregnant, r.is_elder,
                CASE WHEN r.id = (SELECT MIN(rh.id) FROM person_page_records rh WHERE rh.family_id = f.id) THEN 1 ELSE 0 END AS is_head
              FROM families f
              LEFT JOIN person_page_records r ON r.family_id = f.id
              LEFT JOIN persons p ON r.person_id = p.id
              WHERE $where
              $orderBy
              LIMIT $perPage OFFSET $offset";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Search error: " . $e->getMessage());
}

function normalizeGender($val) {
    $map = ['m' => 'Male', 'f' => 'Female', 'male' => 'Male', 'female' => 'Female', 'other' => 'Other'];
    return $map[strtolower(trim($val ?? ''))] ?? ($val ?: '-');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Details - GS System</title>
    <link rel="stylesheet" href="assets/css/style.css?v=3.0">
    <link rel="stylesheet" href="assets/css/responsive.css?v=1.3">
    <link rel="stylesheet" href="assets/css/theme.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom Delete Modal (legacy override if needed) */
    </style>
</head>
<body style="background-color: #f1f5f9;">

<!-- Custom Delete Confirmation Modal -->
<div id="deleteModal" class="delete-modal">
    <div id="deleteModalBox" class="delete-modal-box">
        <div class="modal-icon"><i class="fas fa-trash-alt"></i></div>
        <h3 id="modalTitle">Delete Record?</h3>
        <p id="modalMsg">This family record and all its members will be permanently deleted. This action cannot be undone.</p>
        <div class="modal-btns">
            <button class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn-danger" id="modalConfirmBtn">Yes, Delete</button>
        </div>
    </div>
</div>
    <nav class="glass-dark" style="padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="dashboard.php" style="color: white;"><i class="fas fa-arrow-left"></i></a>
            <h2>Family Details</h2>
        </div>
        <div class="lang-switcher" style="position: static;">
            <button class="lang-btn" data-lang="en">EN</button>
            <button class="lang-btn" data-lang="ta">தமிழ்</button>
            <button class="lang-btn" data-lang="si">සිංහල</button>
        </div>
    </nav>

    <div class="listing-container animate-fade">
        <!-- Quick bar: toggle + Add/Upload buttons -->
        <div class="listing-toolbar">
            <div class="listing-toolbar-left">
                <a href="add-family.php" class="btn btn-primary" style="text-decoration:none; height:40px; display:inline-flex; align-items:center; gap:0.4rem;">
                    <i class="fas fa-plus"></i> Add Family
                </a>
                <a href="add-family-details.php" class="btn btn-accent" style="text-decoration:none; height:40px; display:inline-flex; align-items:center; gap:0.4rem;">
                    <i class="fas fa-user-plus"></i> Simple Add
                </a>
                <button onclick="toggleFDUpload()" class="btn btn-upload">
                    <i class="fas fa-file-excel"></i> Upload Excel
                </button>
                <!-- Advanced Filter Toggle -->
                <button type="button" id="filterToggleBtn" class="btn-filter-toggle <?php echo $activeCount > 0 ? 'active' : ''; ?>" onclick="toggleFilterPanel()" style="height:40px;">
                    <i class="fas fa-sliders-h"></i> Advanced Filter
                    <?php if ($activeCount > 0): ?>
                        <span class="badge-count"><?php echo $activeCount; ?></span>
                    <?php endif; ?>
                </button>
                <?php if ($activeCount > 0): ?>
                    <a href="search.php" class="btn-clear-filters" title="Clear all filters">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                <?php endif; ?>
            </div>
            <p class="listing-toolbar-right">
                Total records: <strong><?php echo number_format($totalRecords); ?></strong>
                <?php if ($activeCount > 0): ?>
                    <span style="color:#0f4c5c; margin-left:0.5rem;">(filtered)</span>
                <?php endif; ?>
                &nbsp;·&nbsp;
                <span style="color:#64748b;">Page <?php echo $currentPage; ?> / <?php echo $totalPages; ?></span>
            </p>
        </div>

        <!-- ── Advanced Filter Panel ──────────────────────────────────────── -->
        <?php $isFilterActive = isset($activeCount) && $activeCount > 0; ?>
        <div id="advFilterPanel" class="<?php echo $isFilterActive ? 'open' : ''; ?>" style="margin-top:1rem;">
            <div class="glass" style="padding:1.5rem; border-radius:var(--radius-lg);">
                <form action="search.php" method="GET" id="advFilterForm">
                    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem;">
                        <i class="fas fa-filter" style="color:var(--primary);"></i>
                        <strong style="font-size:0.9rem; color:#1e293b;">Filter Records</strong>
                        <span style="font-size:0.75rem; color:#94a3b8; margin-left:0.25rem;">— leave blank to skip any field</span>
                    </div>

                    <div class="filter-grid">
                        <!-- SNO -->
                        <div class="filter-group">
                            <label>SNO</label>
                            <input type="number" name="f_sno" value="<?php echo htmlspecialchars($f_sno); ?>" placeholder="e.g. 5" min="1">
                        </div>
                        <!-- Family No -->
                        <div class="filter-group">
                            <label>Family No</label>
                            <input type="text" name="f_family_no" value="<?php echo htmlspecialchars($f_family_no); ?>" placeholder="e.g. 101">
                        </div>
                        <!-- Name -->
                        <div class="filter-group">
                            <label>Name</label>
                            <input type="text" name="f_name" value="<?php echo htmlspecialchars($f_name); ?>" placeholder="Full or partial name">
                        </div>
                        <!-- House No -->
                        <div class="filter-group">
                            <label>House No</label>
                            <input type="text" name="f_house_no" value="<?php echo htmlspecialchars($f_house_no); ?>" placeholder="e.g. 175/2">
                        </div>
                        <!-- Address -->
                        <div class="filter-group">
                            <label>Address</label>
                            <input type="text" name="f_address" value="<?php echo htmlspecialchars($f_address); ?>" placeholder="Street or area">
                        </div>
                        <!-- Sex -->
                        <div class="filter-group">
                            <label>Sex</label>
                            <select name="f_sex">
                                <option value="">All</option>
                                <option value="Male"   <?php echo $f_sex === 'Male'   ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo $f_sex === 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other"  <?php echo $f_sex === 'Other'  ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <!-- Family Member count -->
                        <div class="filter-group">
                            <label>Family Members</label>
                            <input type="number" name="f_member" value="<?php echo htmlspecialchars($f_member); ?>" placeholder="Exact count" min="1">
                        </div>
                        <!-- NIC -->
                        <div class="filter-group">
                            <label>NIC No</label>
                            <input type="text" name="f_nic" value="<?php echo htmlspecialchars($f_nic); ?>" placeholder="Full or partial NIC">
                        </div>
                        <!-- Date of Birth range -->
                        <div class="filter-group" style="grid-column: span 2;">
                            <label>Date of Birth (From – To)</label>
                            <div class="filter-range">
                                <input type="date" name="f_dob_from" value="<?php echo htmlspecialchars($f_dob_from); ?>">
                                <span>to</span>
                                <input type="date" name="f_dob_to" value="<?php echo htmlspecialchars($f_dob_to); ?>">
                            </div>
                        </div>
                        <!-- Age range -->
                        <div class="filter-group" style="grid-column: span 2;">
                            <label>Age (Min – Max)</label>
                            <div class="filter-range">
                                <input type="number" name="f_age_min" value="<?php echo htmlspecialchars($f_age_min); ?>" placeholder="Min" min="0">
                                <span>–</span>
                                <input type="number" name="f_age_max" value="<?php echo htmlspecialchars($f_age_max); ?>" placeholder="Max" min="0">
                            </div>
                        </div>
                        <!-- Occupation -->
                        <div class="filter-group">
                            <label>Occupation</label>
                            <input type="text" name="f_occupation" value="<?php echo htmlspecialchars($f_occupation); ?>" placeholder="e.g. Teacher">
                        </div>
                        <!-- Conduct No -->
                        <div class="filter-group">
                            <label>Conduct No</label>
                            <input type="text" name="f_conduct_no" value="<?php echo htmlspecialchars($f_conduct_no); ?>" placeholder="Phone / contact">
                        </div>
                        <!-- Person's House No -->
                        <div class="filter-group">
                            <label>Person's House No</label>
                            <input type="text" name="f_person_house" value="<?php echo htmlspecialchars($f_person_house); ?>" placeholder="e.g. 175/2A">
                        </div>
                        <!-- Income Level -->
                        <div class="filter-group">
                            <label>Income Level</label>
                            <select name="f_income">
                                <option value="">All</option>
                                <option value="Low (< 25,000)"          <?php echo $income === 'Low (< 25,000)'          ? 'selected' : ''; ?>>Low (< 25,000)</option>
                                <option value="Middle (25,000 - 75,000)" <?php echo $income === 'Middle (25,000 - 75,000)' ? 'selected' : ''; ?>>Middle (25,000–75,000)</option>
                                <option value="High (> 75,000)"          <?php echo $income === 'High (> 75,000)'          ? 'selected' : ''; ?>>High (> 75,000)</option>
                            </select>
                        </div>
                        <!-- Aswesuma -->
                        <div class="filter-group">
                            <label>Aswesuma</label>
                            <select name="f_aswesuma">
                                <option value="">All</option>
                                <option value="yes" <?php echo $f_aswesuma === 'yes' ? 'selected' : ''; ?>>Yes</option>
                                <option value="no"  <?php echo $f_aswesuma === 'no'  ? 'selected' : ''; ?>>No</option>
                            </select>
                        </div>
                        <!-- Elder -->
                        <div class="filter-group">
                            <label>Elder</label>
                            <select name="f_elder">
                                <option value="">All</option>
                                <option value="yes" <?php echo $f_elder === 'yes' ? 'selected' : ''; ?>>Yes</option>
                                <option value="no"  <?php echo $f_elder === 'no'  ? 'selected' : ''; ?>>No</option>
                            </select>
                        </div>
                        <!-- PAMA -->
                        <div class="filter-group">
                            <label>PAMA</label>
                            <select name="f_pama">
                                <option value="">All</option>
                                <option value="yes" <?php echo $f_pama === 'yes' ? 'selected' : ''; ?>>Yes</option>
                                <option value="no"  <?php echo $f_pama === 'no'  ? 'selected' : ''; ?>>No</option>
                            </select>
                        </div>
                        <!-- Kidney / Disabled -->
                        <div class="filter-group">
                            <label>Kidney / Disabled</label>
                            <select name="f_kidney">
                                <option value="">All</option>
                                <option value="yes" <?php echo $f_kidney === 'yes' ? 'selected' : ''; ?>>Yes</option>
                                <option value="no"  <?php echo $f_kidney === 'no'  ? 'selected' : ''; ?>>No</option>
                            </select>
                        </div>
                    </div><!-- /.filter-grid -->

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary" style="height:40px; padding:0 1.5rem;" onclick="document.getElementById('advFilterForm').submit();">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="search.php" class="btn-clear-filters">
                            <i class="fas fa-redo-alt"></i> Reset All
                        </a>
                    </div>
                </form>
            </div>
        </div><!-- /#advFilterPanel -->


        <!-- Bulk Actions Bar -->
        <div id="bulkActions" class="bulk-actions-bar">
            <span style="font-size: 0.85rem; font-weight: 600; color: #b91c1c;"><i class="fas fa-check-square"></i> <span id="selectedCount">0</span> Selected</span>
            <button id="bulkDeleteBtn" class="btn" style="background: #ef4444; color: white; padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                <i class="fas fa-trash-alt"></i> Delete Selected
            </button>
        </div>

        <!-- Embedded Upload Section (hidden by default) -->
        <div id="fdUploadSection" style="display:none; margin-bottom: 1.5rem;">
            <div class="glass" style="padding: 1.5rem; border-radius: var(--radius-lg);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h4 style="color: var(--primary);"><i class="fas fa-file-excel"></i> Upload Excel (Family Details Template)</h4>
                    <button onclick="downloadFDTemplate()" class="btn" style="background:#e2e8f0; font-size:0.8rem; color:var(--text-dark); padding:0.4rem 0.8rem;">
                        <i class="fas fa-download"></i> Download Template
                    </button>
                </div>
                <div id="fdDrop" class="upload-dropzone">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #94a3b8; margin-bottom: 0.5rem;"></i>
                    <p style="font-size: 0.95rem;">Drag &amp; Drop Excel file here or click to browse</p>
                    <p style="font-size: 0.75rem; color: var(--text-muted);">Columns: SNo, Family No, Name, House No, Address, Sex, Family Member, NIC No, Date of Birth, Age, Occupation, Conduct No, Person’s House No, Aswesuma, Elder, PAMA, Kidney Disease/Disabled</p>
                    <input type="file" id="fdFile" hidden accept=".xlsx,.xls">
                </div>
                <div id="fdUploadResult" style="display:none; margin-top:1rem; padding:1rem; background:#f1f5f9; border-radius:8px;"></div>
            </div>
        </div>

        <p class="scroll-hint"><i class="fas fa-arrows-left-right"></i> Scroll horizontally to see all columns</p>

        <!-- Results Table -->
        <div class="glass" style="padding: 1.5rem; border-radius: var(--radius-lg);">
            <div class="table-wrapper">
                <table class="details-table">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="selectAll" onclick="toggleAll(this)"></th>
                        <th>SNo</th>
                        <th>Family No</th>
                        <th>Name</th>
                        <th>House No</th>
                        <th>Address</th>
                        <th>Sex</th>
                        <th>Family Member</th>
                        <th>NIC No</th>
                        <th>Date of Birth</th>
                        <th>Age</th>
                        <th>Occupation</th>
                        <th>Conduct No</th>
                        <th>Person’s House No</th>
                        <th>Aswesuma</th>
                        <th>Elder</th>
                        <th>PAMA</th>
                        <th>Kidney Disease/Disabled</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr>
                            <td colspan="19" style="text-align: center; color: var(--text-muted); padding: 2rem;">No matching records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($results as $row): ?>
                            <tr class="<?php echo $row['is_head'] ? 'is-family-head' : ''; ?>">
                                <td style="text-align: center;"><input type="checkbox" class="row-checkbox" value="<?php echo $row['family_id']; ?>" onclick="updateSelection()"></td>
                                <td><?php echo htmlspecialchars($row['person_sno'] ?: '-'); ?></td>
                                <td style="color: var(--primary); font-weight: 600;"><?php echo htmlspecialchars($row['family_number'] ?? $row['family_code'] ?? '-'); ?></td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:0.4rem;">
                                        <strong><?php echo htmlspecialchars($row['full_name'] ?? 'N/A'); ?></strong>
                                        <?php if ($row['is_head']): ?>
                                            <span class="badge-head">👑 Head</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($row['house_number'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['address'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars(normalizeGender($row['gender'] ?? '-')); ?></td>
                                <td style="text-align: center;">
                                    <span class="badge badge-yes"><?php echo (int)($row['member_count'] ?? 1); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($row['nic'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['dob'] ?? '-'); ?></td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($row['age'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['occupation'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['contact_number'] ?? $row['contact_no'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['person_house_number'] ?: '-'); ?></td>
                                <td style="text-align: center;">
                                    <?php if (!empty($row['aswesuma']) && $row['aswesuma'] !== '0'): ?>
                                        <span class="badge badge-yes"><?php echo htmlspecialchars($row['aswesuma']); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-no">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($row['is_elder'] ?: '-'); ?></td>
                                <td style="text-align: center;"><?php echo (int)($row['pmam'] ?? 0); ?></td>
                                <td><?php echo htmlspecialchars($row['kidney_disease'] ?: '-'); ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.4rem;">
                                        <a href="view-family.php?id=<?php echo $row['family_id']; ?>" class="btn" style="padding: 0.35rem 0.6rem; background: #e2e8f0; font-size: 0.75rem; color: var(--text-dark); text-decoration: none;" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="edit-family.php?id=<?php echo $row['record_id']; ?>" class="btn" style="padding: 0.35rem 0.6rem; background: #dbeafe; font-size: 0.75rem; color: #1d4ed8; text-decoration: none;" title="Edit"><i class="fas fa-edit"></i></a>
                                        <button onclick="confirmDelete(<?php echo $row['family_id']; ?>)" class="btn" style="padding: 0.35rem 0.6rem; background: #fee2e2; color: #ef4444; font-size: 0.75rem;" title="Delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>

        <?php require 'includes/pagination_ui.php'; ?>
    </div>

    <script src="assets/js/lang.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
    <script>
        function confirmDelete(id) {
            showDeleteModal(
                'Delete Family Record?',
                'This family record and all its members will be permanently deleted. This action cannot be undone.',
                function() { window.location.href = 'api/delete-family.php?id=' + id; }
            );
        }

        function showDeleteModal(title, msg, onConfirm) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalMsg').textContent = msg;
            const modal = document.getElementById('deleteModal');
            modal.classList.add('active');
            const btn = document.getElementById('modalConfirmBtn');
            btn.onclick = function() { closeDeleteModal(); onConfirm(); };
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        // Close modal if clicking outside the box
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });

        // Family Details Upload
        function toggleFDUpload() {
            const s = document.getElementById('fdUploadSection');
            s.style.display = s.style.display === 'none' ? 'block' : 'none';
        }
        const fdDrop = document.getElementById('fdDrop');
        const fdFile = document.getElementById('fdFile');
        if (fdDrop) {
            fdDrop.onclick = () => fdFile.click();
            fdDrop.ondragover = e => { e.preventDefault(); fdDrop.style.borderColor = 'var(--primary)'; };
            fdDrop.ondragleave = () => fdDrop.style.borderColor = '';
            fdDrop.ondrop = e => { e.preventDefault(); fdDrop.style.borderColor = ''; handleFDFile(e.dataTransfer.files[0]); };
            fdFile.onchange = e => handleFDFile(e.target.files[0]);
        }
        function handleFDFile(file) {
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                const wb = XLSX.read(new Uint8Array(e.target.result), { type: 'array' });
                // range:5 → row 6 (0-indexed 5) is the header row;
                // actual data starts from row 7 onward.
                const json = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], { range: 5 });
                uploadFDData(json);
            };
            reader.readAsArrayBuffer(file);
        }
        async function uploadFDData(rows) {
            const res = document.getElementById('fdUploadResult');
            res.style.display = 'block';
            res.innerHTML = '<p>Uploading...</p>';
            let ok = 0, skipped = 0, err = 0, errs = [];
            for (let i = 0; i < rows.length; i++) {
                const payload = { ...rows[i], GlobalCategory: '' };
                const r = await fetch('api/bulk-upload-process.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const j = await r.json();
                if (j.skipped)      { skipped++; }
                else if (j.success) { ok++; }
                else                { err++; errs.push(`Row ${i + 7}: ${j.error}`); }
            }
            const skipNote = skipped > 0 ? `<span style="color:#64748b">⚠️ ${skipped} blank/header row(s) skipped.</span><br>` : '';
            res.innerHTML = `<p>✅ Uploaded: <b>${ok}</b> &nbsp; ❌ Errors: <b>${err}</b></p>${skipNote}<p style="color:#ef4444;font-size:0.8rem">${errs.join('<br>')}</p>`;
            if (ok > 0) setTimeout(() => location.reload(), 2000);
        }
        function downloadFDTemplate() {
            const t = [{ 'SNo': 1, 'Family No': 'FAM-001', 'Name': 'Sample Name', 'House No': '101', 'Address': 'Sample Road', 'Sex': 'M', 'Family Member': 4, 'NIC No': '199012345678', 'Date of Birth': '1990-01-01', 'Age': 34, 'Occupation': 'Teacher', 'Conduct No': '0771234567', 'Persons House No': '101A', 'Aswesuma': 'Vulnerable', 'Elder': 'No', 'PAMA': 0, 'Kidney Disease/Disabled': 'None' }];
            const ws = XLSX.utils.json_to_sheet(t);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'FamilyDetails');
            XLSX.writeFile(wb, 'Family_Details_Template.xlsx');
        }
        // Bulk Actions Logic
        function toggleAll(master) {
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = master.checked);
            updateSelection();
        }

        function updateSelection() {
            const selected = document.querySelectorAll('.row-checkbox:checked');
            const bulkActions = document.getElementById('bulkActions');
            const countSpan = document.getElementById('selectedCount');
            if (selected.length > 0) {
                bulkActions.classList.add('show');
                countSpan.innerText = selected.length;
            } else {
                bulkActions.classList.remove('show');
                const sa = document.getElementById('selectAll');
                if (sa) sa.checked = false;
            }
        }

        async function doBulkDelete() {
            const selected = document.querySelectorAll('.row-checkbox:checked');
            const ids = Array.from(selected).map(cb => cb.value);
            if (ids.length === 0) return;
            showDeleteModal(
                'Delete ' + ids.length + ' Record(s)?',
                ids.length + ' selected family record(s) and all their members will be permanently deleted.',
                async function() {
                    try {
                        const res = await fetch('api/bulk-delete.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ ids: ids })
                        });
                        const result = await res.json();
                        if (result.success) {
                            location.reload();
                        } else {
                            document.getElementById('deleteModal').classList.remove('active');
                            showErrorToast('Error: ' + (result.error || 'Unknown error'));
                        }
                    } catch (e) {
                        document.getElementById('deleteModal').classList.remove('active');
                        showErrorToast('Network error: ' + e.message);
                    }
                }
            );
        }

        function showErrorToast(msg) {
            const t = document.createElement('div');
            t.style.cssText = 'position:fixed;bottom:2rem;right:2rem;background:#ef4444;color:white;padding:1rem 1.5rem;border-radius:8px;font-size:0.9rem;z-index:9998;box-shadow:0 4px 20px rgba(0,0,0,0.2);';
            t.textContent = msg;
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 4000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('bulkDeleteBtn');
            if (btn) btn.addEventListener('click', doBulkDelete);
            
            const panel = document.getElementById('advFilterPanel');
            const toggleBtn = document.getElementById('filterToggleBtn');
            if (panel) {
                <?php if ($isFilterActive): ?>
                panel.classList.add('open');
                if (toggleBtn) toggleBtn.classList.add('active');
                <?php else: ?>
                panel.classList.remove('open');
                <?php endif; ?>
            }
        });

        function toggleFilterPanel() {
            var panel = document.getElementById('advFilterPanel');
            var btn   = document.getElementById('filterToggleBtn');
            if (!panel) return;

            if (panel.classList.contains('open')) {
                panel.classList.remove('open');
                if (btn) {
                    var badge = btn.querySelector('.badge-count');
                    if (!badge) btn.classList.remove('active');
                }
            } else {
                panel.classList.add('open');
                if (btn) btn.classList.add('active');
            }
        }
    </script>
</body>
</html>
