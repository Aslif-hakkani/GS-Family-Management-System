<?php
/**
 * includes/pagination_ui.php
 * ──────────────────────────────────────────────────────────────────────────
 * Reusable pagination controls — outputs CSS + HTML pagination bar.
 *
 * REQUIRED variables (from adv_filter.php):
 *   $currentPage, $totalPages, $totalRecords, $perPage, $pageFile
 * ──────────────────────────────────────────────────────────────────────────
 */

if ($totalPages <= 1 && $totalRecords === 0) return; // nothing to paginate

/** Build URL preserving all current GET params, only changing 'page' */
function paginationUrl(int $page, string $pageFile): string {
    $params = $_GET;
    unset($params['page']);
    if ($page > 1) $params['page'] = $page;
    $qs = $params ? '?' . http_build_query($params) : '';
    return htmlspecialchars($pageFile . $qs);
}

/** Compute the window of page numbers to display */
function paginationWindow(int $current, int $total): array {
    if ($total <= 9) return range(1, $total);
    $pages = [1, 2];
    $mid = range(max(3, $current - 2), min($total - 2, $current + 2));
    $pages = array_unique(array_merge($pages, $mid, [$total - 1, $total]));
    sort($pages);
    return $pages;
}

$startRecord = $totalRecords === 0 ? 0 : ($currentPage - 1) * $perPage + 1;
$endRecord   = min($currentPage * $perPage, $totalRecords);
$window      = paginationWindow($currentPage, $totalPages);
?>
<style>
/* ── Pagination UI ──────────────────────────────────────────────────────── */
.pagination-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 1.25rem;
    padding: 0.75rem 1rem;
    background: white;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}
.pagination-info {
    font-size: 0.8rem;
    color: #64748b;
    white-space: nowrap;
}
.pagination-info strong { color: #1e293b; }
.pagination-pages {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    flex-wrap: wrap;
}
.pg-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    height: 34px;
    padding: 0 0.5rem;
    border-radius: 7px;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    border: 1.5px solid #e2e8f0;
    color: #374151;
    background: white;
    transition: all 0.15s;
    cursor: pointer;
    white-space: nowrap;
}
.pg-btn:hover  { border-color: var(--primary); color: var(--primary); background: rgba(15,76,92,0.05); }
.pg-btn.active { border-color: var(--primary); background: var(--primary); color: white; cursor: default; }
.pg-btn.disabled { opacity: 0.38; pointer-events: none; }
.pg-ellipsis   { color: #94a3b8; font-size: 0.8rem; padding: 0 0.2rem; line-height: 34px; }

@media (max-width: 600px) {
    .pagination-bar { justify-content: center; }
    .pg-btn.pg-num { display: none; }
    .pg-btn.pg-num.active,
    .pg-btn.pg-num.active-adj { display: inline-flex; }
}
</style>

<div class="pagination-bar">
    <!-- Info: showing X–Y of Z -->
    <div class="pagination-info">
        <?php if ($totalRecords === 0): ?>
            No records found
        <?php else: ?>
            Showing <strong><?php echo number_format($startRecord); ?></strong>–<strong><?php echo number_format($endRecord); ?></strong>
            of <strong><?php echo number_format($totalRecords); ?></strong> records
            &nbsp;·&nbsp; Page <strong><?php echo $currentPage; ?></strong> / <?php echo $totalPages; ?>
        <?php endif; ?>
    </div>

    <!-- Page buttons -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination-pages">
        <!-- Previous -->
        <?php if ($currentPage > 1): ?>
            <a href="<?php echo paginationUrl($currentPage - 1, $pageFile); ?>" class="pg-btn" title="Previous page">
                <i class="fas fa-chevron-left"></i> Prev
            </a>
        <?php else: ?>
            <span class="pg-btn disabled"><i class="fas fa-chevron-left"></i> Prev</span>
        <?php endif; ?>

        <!-- Page numbers with ellipsis -->
        <?php
        $prev = 0;
        foreach ($window as $p):
            if ($prev > 0 && $p - $prev > 1):
        ?>
            <span class="pg-ellipsis">…</span>
        <?php
            endif;
            $isActive   = $p === $currentPage;
            $isAdjacent = abs($p - $currentPage) === 1;
            $classes    = 'pg-btn pg-num' . ($isActive ? ' active' : '') . ($isAdjacent ? ' active-adj' : '');
        ?>
            <?php if ($isActive): ?>
                <span class="<?php echo $classes; ?>"><?php echo $p; ?></span>
            <?php else: ?>
                <a href="<?php echo paginationUrl($p, $pageFile); ?>" class="<?php echo $classes; ?>"><?php echo $p; ?></a>
            <?php endif; ?>
        <?php
            $prev = $p;
        endforeach;
        ?>

        <!-- Next -->
        <?php if ($currentPage < $totalPages): ?>
            <a href="<?php echo paginationUrl($currentPage + 1, $pageFile); ?>" class="pg-btn" title="Next page">
                Next <i class="fas fa-chevron-right"></i>
            </a>
        <?php else: ?>
            <span class="pg-btn disabled">Next <i class="fas fa-chevron-right"></i></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
