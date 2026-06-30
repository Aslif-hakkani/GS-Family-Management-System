<?php
/**
 * includes/adv_filter.php
 * ──────────────────────────────────────────────────────────────────────────
 * Reusable server-side filter + pagination for all category pages.
 *
 * SET BEFORE INCLUDING:
 *   $pageCategory  – e.g. 'widow', 'elderly', 'pregnant', 'homeless', 'disaster'
 *   $pageFile      – filename used for links, e.g. 'widows.php'
 *
 * EXPOSES after including:
 *   $filteredResults  – array of rows for current page
 *   $activeCount      – number of active filter fields
 *   $totalRecords     – total matching records (pre-pagination)
 *   $totalPages       – total pages
 *   $currentPage      – current page number
 *   $perPage          – records per page (50)
 *   All $f_* filter variables
 * ──────────────────────────────────────────────────────────────────────────
 */

// ── Filter params ─────────────────────────────────────────────────────────
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

$activeCount = count(array_filter([
    $f_sno,$f_family_no,$f_name,$f_house_no,$f_address,
    $f_sex,$f_member,$f_nic,$f_dob_from,$f_dob_to,
    $f_age_min,$f_age_max,$f_occupation,$f_conduct_no,
    $f_person_house,$f_income,$f_aswesuma,$f_elder,$f_pama,$f_kidney
]));

// ── Pagination params ─────────────────────────────────────────────────────
$perPage     = 50;
$currentPage = max(1, (int)($_GET['page'] ?? 1));

// ── Build shared WHERE clause ─────────────────────────────────────────────
$where  = "f.page_category = ?";
$params = [$pageCategory];

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
if ($f_conduct_no !== '')   { $where .= " AND (p.contact_number LIKE ? OR f.contact_no LIKE ?)"; $params[] = "%$f_conduct_no%"; $params[] = "%$f_conduct_no%"; } /* BUG 4 FIX: search both contact fields */
if ($f_person_house !== '') { $where .= " AND p.person_house_number LIKE ?";    $params[] = "%$f_person_house%"; }
if ($f_income !== '')       { $where .= " AND f.income_level = ?";              $params[] = $f_income; }

$noVals = "('0','','No','no','None','none','N','n')";
if ($f_aswesuma === 'yes') $where .= " AND (r.aswesuma NOT IN $noVals AND r.aswesuma IS NOT NULL)";
elseif ($f_aswesuma === 'no')  $where .= " AND (r.aswesuma IN $noVals OR r.aswesuma IS NULL)";
if ($f_elder === 'yes')    $where .= " AND (r.is_elder NOT IN $noVals AND r.is_elder IS NOT NULL)";
elseif ($f_elder === 'no')     $where .= " AND (r.is_elder IN $noVals OR r.is_elder IS NULL)";
if ($f_pama === 'yes')     $where .= " AND (r.pmam IS NOT NULL AND r.pmam > 0)";
elseif ($f_pama === 'no')      $where .= " AND (r.pmam IS NULL OR r.pmam = 0)";
if ($f_kidney === 'yes')   $where .= " AND (r.kidney_disease NOT IN $noVals AND r.kidney_disease IS NOT NULL)";
elseif ($f_kidney === 'no')    $where .= " AND (r.kidney_disease IN $noVals OR r.kidney_disease IS NULL)";

$orderBy = "ORDER BY CASE WHEN r.person_sno IS NULL OR r.person_sno = 0 THEN 1 ELSE 0 END, r.person_sno ASC, r.id ASC";

try {
    // ── COUNT query (total matching records) ──────────────────────────────
    $countSQL  = "SELECT COUNT(*) FROM person_page_records r
                  JOIN families f ON r.family_id = f.id
                  JOIN persons p  ON r.person_id = p.id
                  WHERE $where";
    $countStmt = $pdo->prepare($countSQL);
    $countStmt->execute($params);
    $totalRecords = (int)$countStmt->fetchColumn();
    $totalPages   = max(1, (int)ceil($totalRecords / $perPage));
    $currentPage  = min($currentPage, $totalPages); // clamp to valid range
    $offset       = ($currentPage - 1) * $perPage;

    // ── Main SELECT query with LIMIT/OFFSET ───────────────────────────────
    $filterSQL = "SELECT
        f.id as family_id, f.sno as fam_sno, f.family_number, f.family_code,
        f.house_number, f.road, f.address as fam_address, f.member_count, f.signature,
        f.housing_condition, f.remarks, f.contact_no,
        p.id as person_id, r.id as record_id, r.person_sno, p.full_name, p.gender,
        p.nic, p.dob, p.age, p.occupation, p.contact_number, p.person_house_number,
        r.aswesuma, r.pmam, r.kidney_disease, r.disabled, r.is_elder
    FROM person_page_records r
    JOIN families f ON r.family_id = f.id
    JOIN persons p  ON r.person_id = p.id
    WHERE $where
    $orderBy
    LIMIT $perPage OFFSET $offset";

    $filterStmt = $pdo->prepare($filterSQL);
    $filterStmt->execute($params);
    $filteredResults = $filterStmt->fetchAll();

} catch (PDOException $e) {
    die("Filter error: " . $e->getMessage());
}
