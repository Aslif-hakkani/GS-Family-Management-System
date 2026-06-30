/* ============================================================
   responsive.css  v1.0 – GS Family Management System
   Full responsive layer for all screen sizes
   Mobile-first approach with progressive enhancement
   ============================================================ */

/* ── 1. BASE RESETS & MOBILE SAFETY ─────────────────────── */
*, *::before, *::after { box-sizing: border-box; }
html { -webkit-text-size-adjust: 100%; }
body { overflow-x: hidden; min-height: 100vh; }
img, video, iframe, svg { max-width: 100%; height: auto; }

/* ── 2. NAVIGATION ───────────────────────────────────────── */
nav.glass-dark {
    padding: 0.75rem 1.5rem !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 0.5rem !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 200 !important;
}
nav.glass-dark > div:first-child {
    display: flex !important;
    align-items: center !important;
    gap: 0.75rem !important;
    min-width: 0 !important;
    flex: 1 1 auto !important;
}
nav.glass-dark h2 {
    font-size: clamp(0.85rem, 2.2vw, 1.2rem) !important;
    margin: 0 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}
nav.glass-dark > div:last-child {
    display: flex !important;
    align-items: center !important;
    gap: 0.6rem !important;
    flex-wrap: wrap !important;
    flex-shrink: 0 !important;
}

/* Lang switcher */
.lang-switcher { position: static !important; display: flex !important; gap: 0.3rem !important; }
.lang-btn { padding: 0.3rem 0.55rem !important; font-size: 0.75rem !important; }

/* ── 3. CONTAINER ────────────────────────────────────────── */
.container {
    width: 100% !important;
    padding: 1.5rem 1.25rem !important;
    margin: 0 auto !important;
}

/* ── 4. STATS / CATEGORY CARDS ──────────────────────────── */
.stats-grid {
    display: grid !important;
    gap: 1rem !important;
    margin-bottom: 1.5rem !important;
}
.stat-card {
    border-radius: var(--radius-md) !important;
    display: flex !important;
    align-items: center !important;
    gap: 0.75rem !important;
    transition: transform 0.18s !important;
}
.stat-card:hover { transform: translateY(-3px); }

/* ── 5. TABLES ───────────────────────────────────────────── */
.table-container {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
    border-radius: var(--radius-md) !important;
}
.table-container table,
div[style*="overflow-x: auto"] table,
div[style*="overflow-x:auto"] table {
    min-width: 600px;
}
/* Ensure all glass-wrapped tables scroll */
.glass .table-container { overflow-x: auto !important; }

/* ── 6. FILTER PANEL ─────────────────────────────────────── */
.filter-grid {
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 0.55rem 0.75rem !important;
}
.btn-filter-toggle { white-space: nowrap !important; }

/* ── 7. PAGE TOOLBAR (category pages) ───────────────────── */
/* The outer toolbar flex row */
div[style*="justify-content:space-between"][style*="align-items:center"],
div[style*="justify-content: space-between"][style*="align-items: center"] {
    flex-wrap: wrap !important;
}

/* ── 8. BUTTONS ──────────────────────────────────────────── */
.btn { white-space: nowrap !important; }

/* ── 9. QUICK ACTIONS (dashboard) ───────────────────────── */
div[style*="gap: 1rem; margin-bottom: 2.5rem"],
div[style*="gap: 1rem; margin-bottom:2.5rem"] {
    flex-wrap: wrap !important;
}

/* ── 10. PAGINATION ──────────────────────────────────────── */
div[style*="justify-content: center"][style*="align-items: center"][style*="flex-wrap: wrap"] {
    gap: 0.35rem !important;
}

/* ── 11. FORM PAGES (add/edit) ───────────────────────────── */
/* Wizard steps scroll */
.wizard-steps { overflow-x: auto; }

/* ============================================================
   DESKTOP LARGE  (1400px+)
   ============================================================ */
@media (min-width: 1400px) {
    .container { padding: 2rem 2.5rem !important; }
    .stats-grid { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important; }
    .filter-grid { grid-template-columns: repeat(5, 1fr) !important; }
}

/* ============================================================
   DESKTOP  (1024px – 1399px)
   ============================================================ */
@media (max-width: 1399px) and (min-width: 1025px) {
    .stats-grid { grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)) !important; }
    .filter-grid { grid-template-columns: repeat(4, 1fr) !important; }
}

/* ============================================================
   TABLET  (max 1024px)
   ============================================================ */
@media (max-width: 1024px) {
    .container { padding: 1.25rem 1rem !important; }

    .stats-grid { grid-template-columns: repeat(3, 1fr) !important; gap: 0.85rem !important; }

    .filter-grid { grid-template-columns: repeat(3, 1fr) !important; }

    /* Dashboard search bar */
    form[action="dashboard.php"] input[name="q"] { min-width: 180px !important; }

    /* Dashboard section header */
    div[style*="justify-content: space-between"][style*="align-items: center"][style*="margin-bottom: 1.25rem"] {
        flex-direction: row !important;
        flex-wrap: wrap !important;
        gap: 0.75rem !important;
    }

    /* Category page table min-width smaller on tablet */
    .table-container table { min-width: 700px !important; }

    .scroll-hint { display: block !important; }
}

/* ============================================================
   SMALL TABLET  (max 768px)
   ============================================================ */
@media (max-width: 768px) {

    /* ── Nav ── */
    nav.glass-dark { padding: 0.6rem 1rem !important; }
    nav.glass-dark h2 { font-size: 0.95rem !important; }
    .lang-btn { font-size: 0.68rem !important; padding: 0.25rem 0.45rem !important; }

    /* ── Container ── */
    .container { padding: 1rem 0.85rem !important; }

    /* ── Stats grid: 2 columns ── */
    .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 0.75rem !important; }
    .stat-card  { padding: 1rem 0.85rem !important; gap: 0.6rem !important; }
    .stat-card h2[style] { font-size: 1.35rem !important; }

    /* ── Dashboard quick actions ── */
    div[style*="gap: 1rem; margin-bottom: 2.5rem"] { gap: 0.6rem !important; }

    /* ── Page toolbar – stack vertically ── */
    div[style*="margin-bottom:0.5rem"][style*="justify-content:space-between"],
    div[style*="margin-bottom: 0.5rem"][style*="justify-content: space-between"] {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.6rem !important;
    }
    div[style*="margin-bottom:0.5rem"][style*="justify-content:space-between"] > div:last-child,
    div[style*="margin-bottom: 0.5rem"][style*="justify-content: space-between"] > div:last-child {
        justify-content: flex-start !important;
    }

    /* ── Filter ── */
    #advFilterPanel .filter-grid,
    .filter-grid { grid-template-columns: repeat(2, 1fr) !important; }
    #advFilterPanel { margin-top: 0.75rem !important; }
    .filter-actions { flex-wrap: wrap !important; }

    /* ── Glass card ── */
    .glass[style*="padding: 2rem"] { padding: 1.1rem 0.9rem !important; }
    .glass[style*="padding: 1.5rem"] { padding: 1rem 0.85rem !important; }
    .glass[style*="padding:1.25rem"] { padding: 1rem 0.85rem !important; }

    /* ── Buttons ── */
    .btn { padding: 0.5rem 0.9rem !important; font-size: 0.85rem !important; }

    /* ── Dashboard search ── */
    form[action="dashboard.php"] {
        flex-wrap: wrap !important;
        width: 100% !important;
    }
    form[action="dashboard.php"] input[name="q"] {
        min-width: unset !important;
        width: 100% !important;
        flex: 1 1 100% !important;
    }

    /* ── Dashboard section header (Family Details table) ── */
    div[style*="justify-content: space-between"][style*="margin-bottom: 1.25rem"],
    div[style*="justify-content:space-between"][style*="margin-bottom:1.25rem"] {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.75rem !important;
    }
    div[style*="justify-content: space-between"][style*="margin-bottom: 1.25rem"] > div:last-child,
    div[style*="justify-content:space-between"][style*="margin-bottom:1.25rem"] > div:last-child {
        flex-wrap: wrap !important;
        gap: 0.5rem !important;
    }

    /* ── Upload dropzone ── */
    .upload-dropzone { padding: 1.25rem 0.75rem !important; }

    /* ── Scroll hint always visible on tablet ── */
    .scroll-hint { display: block !important; }

    /* ── Wizard steps ── */
    .wizard-steps { overflow-x: auto; padding-bottom: 0.5rem; }
    .step-num { width: 30px !important; height: 30px !important; font-size: 0.8rem !important; }
}

/* ============================================================
   MOBILE  (max 480px)
   ============================================================ */
@media (max-width: 600px) {

    /* ── Nav ── */
    nav.glass-dark { padding: 0.55rem 0.75rem !important; }
    nav.glass-dark > div:first-child { gap: 0.5rem !important; }

    /* GS logo box smaller */
    nav.glass-dark div[style*="width: 40px"] {
        width: 32px !important;
        height: 32px !important;
        font-size: 0.78rem !important;
    }
    nav.glass-dark h2 { font-size: 0.82rem !important; }

    /* Logout text hidden – icon only */
    nav.glass-dark a[href="logout.php"] { font-size: 0 !important; padding: 0.35rem 0.55rem !important; }
    nav.glass-dark a[href="logout.php"] i { font-size: 1rem !important; }
    nav.glass-dark a[href="../logout.php"] { font-size: 0 !important; padding: 0.35rem 0.55rem !important; }
    nav.glass-dark a[href="../logout.php"] i { font-size: 1rem !important; }

    /* ── Container ── */
    .container { padding: 0.75rem 0.6rem !important; }

    /* ── Stats ── */
    .stats-grid { grid-template-columns: 1fr 1fr !important; gap: 0.55rem !important; }
    .stat-card  {
        padding: 0.75rem 0.6rem !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 0.35rem !important;
    }
    .stat-icon { width: 34px !important; height: 34px !important; min-width: 34px !important; font-size: 1rem !important; }
    .stat-card h2[style] { font-size: 1.1rem !important; }
    .stat-card p { font-size: 0.72rem !important; }

    /* Category grid cards – 2 columns on phone */
    .stats-grid[style*="repeat(auto-fit, minmax(200px"] {
        grid-template-columns: repeat(2, 1fr) !important;
    }

    /* ── Filter – single column ── */
    #advFilterPanel .filter-grid,
    .filter-grid {
        grid-template-columns: 1fr !important;
        gap: 0.45rem !important;
    }
    /* Kill span-2 on tiny screens */
    #advFilterPanel .filter-group,
    .filter-group {
        grid-column: span 1 !important;
    }
    .filter-actions .btn-primary, .filter-actions a {
        flex: 1 1 auto !important;
        justify-content: center !important;
    }

    /* ── Buttons ── */
    .btn { padding: 0.5rem 0.75rem !important; font-size: 0.82rem !important; }

    /* ── Quick actions – wrap and stretch ── */
    div[style*="gap: 1rem; margin-bottom: 2.5rem"] { flex-wrap: wrap !important; gap: 0.5rem !important; }
    div[style*="gap: 1rem; margin-bottom: 2.5rem"] > a.btn,
    div[style*="gap: 1rem; margin-bottom: 2.5rem"] > a { flex: 1 1 calc(50% - 0.25rem) !important; justify-content: center !important; }

    /* ── Glass panels ── */
    .glass[style*="padding: 2rem"] { padding: 0.85rem 0.65rem !important; }
    .glass[style*="padding: 1.5rem"] { padding: 0.85rem 0.65rem !important; }

    /* ── Pagination – tighter ── */
    div[style*="justify-content: center"][style*="flex-wrap: wrap"] a,
    div[style*="justify-content: center"][style*="flex-wrap: wrap"] span {
        padding: 0.28rem 0.5rem !important;
        font-size: 0.73rem !important;
    }

    /* ── Action buttons in table rows ── */
    div[style*="display:flex; gap:0.3rem"] a,
    div[style*="display:flex; gap:0.3rem"] button,
    div[style*="display: flex; gap: 0.3rem"] a,
    div[style*="display: flex; gap: 0.3rem"] button {
        padding: 0.25rem 0.4rem !important;
    }

    /* ── Input fields ── */
    input, select, textarea { font-size: 0.875rem !important; }

    /* ── h3 headings ── */
    h3 { font-size: 1rem !important; }
    h2 { font-size: 1.25rem !important; }

    /* ── Delete modal ── */
    .delete-modal-box { padding: 1.25rem !important; }

    /* ── Scroll hint ── */
    .scroll-hint { display: block !important; }
}

/* ============================================================
   EXTRA SMALL  (max 360px)
   ============================================================ */
@media (max-width: 420px) {
    .stats-grid { grid-template-columns: 1fr !important; }
    nav.glass-dark h2 { display: none !important; }
    .lang-switcher { gap: 0.2rem !important; }
    .lang-btn { padding: 0.2rem 0.35rem !important; font-size: 0.65rem !important; }
    .container { padding: 0.6rem 0.5rem !important; }
}

/* ============================================================
   PRINT
   ============================================================ */
@media print {
    nav.glass-dark, .btn-filter-toggle, .btn-upload,
    #bulkActions, .upload-section, #uploadSection { display: none !important; }
    .container { padding: 0 !important; max-width: 100% !important; }
    body { background: white !important; }
    .glass { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
    table { font-size: 0.7rem !important; }
    .table-container { overflow: visible !important; }
}
