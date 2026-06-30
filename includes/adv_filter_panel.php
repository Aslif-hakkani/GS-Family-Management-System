<?php
/**
 * includes/adv_filter_panel.php
 * ──────────────────────────────────────────────────────────────────────────
 * Outputs the CSS + collapsible filter panel HTML for any category page.
 *
 * REQUIRED variables (set before including):
 *   $pageFile    – filename used for the Reset link, e.g. 'widows.php'
 *   $activeCount – number of active filters (from adv_filter.php)
 *   $f_*         – all filter variables (from adv_filter.php)
 *   $filteredResults – result array (from adv_filter.php)
 *
 * OUTPUT: toggle button row + collapsible panel
 * ──────────────────────────────────────────────────────────────────────────
 */

// ── Defensive defaults (suppress warnings if adv_filter.php wasn't included) ──
$pageFile       = $pageFile       ?? '';
$activeCount    = $activeCount    ?? 0;
$f_sno          = $f_sno          ?? '';
$f_family_no    = $f_family_no    ?? '';
$f_name         = $f_name         ?? '';
$f_house_no     = $f_house_no     ?? '';
$f_address      = $f_address      ?? '';
$f_sex          = $f_sex          ?? '';
$f_member       = $f_member       ?? '';
$f_nic          = $f_nic          ?? '';
$f_dob_from     = $f_dob_from     ?? '';
$f_dob_to       = $f_dob_to       ?? '';
$f_age_min      = $f_age_min      ?? '';
$f_age_max      = $f_age_max      ?? '';
$f_occupation   = $f_occupation   ?? '';
$f_conduct_no   = $f_conduct_no   ?? '';
$f_person_house = $f_person_house ?? '';
$f_income       = $f_income       ?? '';
$f_aswesuma     = $f_aswesuma     ?? '';
$f_elder        = $f_elder        ?? '';
$f_pama         = $f_pama         ?? '';
$f_kidney       = $f_kidney       ?? '';
$isFilterActive = ($activeCount > 0);
?>


<!-- ── Filter Toggle Button (inline in the caller's action bar) ─────────── -->
<button type="button" id="filterToggleBtn" class="btn-filter-toggle <?php echo $activeCount > 0 ? 'active' : ''; ?>" onclick="toggleFilterPanel()">
    <i class="fas fa-sliders-h"></i> Advanced Filter
    <?php if ($activeCount > 0): ?>
        <span class="badge-count"><?php echo $activeCount; ?></span>
    <?php endif; ?>
</button>
<?php if ($activeCount > 0): ?>
    <a href="<?php echo htmlspecialchars($pageFile); ?>" class="btn-clear-filters" title="Clear all filters">
        <i class="fas fa-times"></i> Clear
    </a>
<?php endif; ?>

<!-- ── Collapsible Filter Panel ─────────────────────────────────────────── -->
<div id="advFilterPanel" style="margin-top:1rem;" class="<?php echo $isFilterActive ? 'open' : ''; ?>">
    <div class="glass" style="padding:1.25rem 1.5rem; border-radius:var(--radius-lg);">
        <form action="<?php echo htmlspecialchars($pageFile); ?>" method="GET" id="advFilterForm">
            <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem;">
                <i class="fas fa-filter" style="color:var(--primary);"></i>
                <strong style="font-size:0.9rem; color:#1e293b;">Filter Records</strong>
                <span style="font-size:0.75rem; color:#94a3b8; margin-left:0.25rem;">— leave any field blank to skip it</span>
            </div>

            <div class="filter-grid">
                <div class="filter-group">
                    <label>SNO</label>
                    <input type="number" name="f_sno" value="<?php echo htmlspecialchars($f_sno); ?>" placeholder="e.g. 5" min="1">
                </div>
                <div class="filter-group">
                    <label>Family No</label>
                    <input type="text" name="f_family_no" value="<?php echo htmlspecialchars($f_family_no); ?>" placeholder="e.g. 101">
                </div>
                <div class="filter-group">
                    <label>Name</label>
                    <input type="text" name="f_name" value="<?php echo htmlspecialchars($f_name); ?>" placeholder="Full or partial">
                </div>
                <div class="filter-group">
                    <label>House No</label>
                    <input type="text" name="f_house_no" value="<?php echo htmlspecialchars($f_house_no); ?>" placeholder="e.g. 175/2">
                </div>
                <div class="filter-group">
                    <label>Address</label>
                    <input type="text" name="f_address" value="<?php echo htmlspecialchars($f_address); ?>" placeholder="Street or area">
                </div>
                <div class="filter-group">
                    <label>Sex</label>
                    <select name="f_sex">
                        <option value="">All</option>
                        <option value="Male"   <?php echo $f_sex === 'Male'   ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $f_sex === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other"  <?php echo $f_sex === 'Other'  ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Family Members</label>
                    <input type="number" name="f_member" value="<?php echo htmlspecialchars($f_member); ?>" placeholder="Exact count" min="1">
                </div>
                <div class="filter-group">
                    <label>NIC No</label>
                    <input type="text" name="f_nic" value="<?php echo htmlspecialchars($f_nic); ?>" placeholder="Full or partial NIC">
                </div>
                <div class="filter-group" style="grid-column: span 2;">
                    <label>Date of Birth (From – To)</label>
                    <div class="filter-range">
                        <input type="date" name="f_dob_from" value="<?php echo htmlspecialchars($f_dob_from); ?>">
                        <span>to</span>
                        <input type="date" name="f_dob_to" value="<?php echo htmlspecialchars($f_dob_to); ?>">
                    </div>
                </div>
                <div class="filter-group" style="grid-column: span 2;">
                    <label>Age (Min – Max)</label>
                    <div class="filter-range">
                        <input type="number" name="f_age_min" value="<?php echo htmlspecialchars($f_age_min); ?>" placeholder="Min" min="0">
                        <span>–</span>
                        <input type="number" name="f_age_max" value="<?php echo htmlspecialchars($f_age_max); ?>" placeholder="Max" min="0">
                    </div>
                </div>
                <div class="filter-group">
                    <label>Occupation</label>
                    <input type="text" name="f_occupation" value="<?php echo htmlspecialchars($f_occupation); ?>" placeholder="e.g. Teacher">
                </div>
                <div class="filter-group">
                    <label>Conduct No</label>
                    <input type="text" name="f_conduct_no" value="<?php echo htmlspecialchars($f_conduct_no); ?>" placeholder="Phone / contact">
                </div>
                <div class="filter-group">
                    <label>Person's House No</label>
                    <input type="text" name="f_person_house" value="<?php echo htmlspecialchars($f_person_house); ?>" placeholder="e.g. 175/2A">
                </div>
                <div class="filter-group">
                    <label>Aswesuma</label>
                    <select name="f_aswesuma">
                        <option value="">All</option>
                        <option value="yes" <?php echo $f_aswesuma === 'yes' ? 'selected' : ''; ?>>Yes</option>
                        <option value="no"  <?php echo $f_aswesuma === 'no'  ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Elder</label>
                    <select name="f_elder">
                        <option value="">All</option>
                        <option value="yes" <?php echo $f_elder === 'yes' ? 'selected' : ''; ?>>Yes</option>
                        <option value="no"  <?php echo $f_elder === 'no'  ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>PAMA</label>
                    <select name="f_pama">
                        <option value="">All</option>
                        <option value="yes" <?php echo $f_pama === 'yes' ? 'selected' : ''; ?>>Yes</option>
                        <option value="no"  <?php echo $f_pama === 'no'  ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
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
                <button type="submit" class="btn btn-primary" style="height:38px; padding:0 1.4rem;" onclick="document.getElementById('advFilterForm').submit();">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="<?php echo htmlspecialchars($pageFile); ?>" class="btn-clear-filters">
                    <i class="fas fa-redo-alt"></i> Reset All
                </a>
            </div>
        </form>
    </div>
</div><!-- /#advFilterPanel -->

<script>
(function() {
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

    window.toggleFilterPanel = toggleFilterPanel;

    document.addEventListener('DOMContentLoaded', function() {
        var panel = document.getElementById('advFilterPanel');
        var btn   = document.getElementById('filterToggleBtn');
        if (!panel) return;

        <?php if ($isFilterActive): ?>
        panel.classList.add('open');
        if (btn) btn.classList.add('active');
        <?php else: ?>
        panel.classList.remove('open');
        <?php endif; ?>
    });
})();
</script>

