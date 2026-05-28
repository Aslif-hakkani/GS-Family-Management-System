<?php
require_once 'includes/config.php';
check_auth();

$pageCategory = 'widow';
$pageFile     = 'widows.php';
require_once 'includes/adv_filter.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-t="widow">Widows - GS System</title>
    <link rel="stylesheet" href="assets/css/style.css?v=3.0">
    <link rel="stylesheet" href="assets/css/responsive.css?v=1.3">
    <link rel="stylesheet" href="assets/css/theme.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom Delete Modal */
        #deleteModal {
            display: none; position: fixed; inset: 0; z-index: 9999;
            background: rgba(0,0,0,0.5); align-items: center; justify-content: center;
        }
        #deleteModal.active { display: flex; }
        #deleteModalBox {
            background: white; border-radius: 12px; padding: 2rem;
            max-width: 420px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center; animation: modalIn 0.2s ease;
        }
        @keyframes modalIn { from { transform: scale(0.85); opacity:0; } to { transform: scale(1); opacity:1; } }
        #deleteModal .modal-icon { font-size: 3rem; color: #ef4444; margin-bottom: 1rem; }
        #deleteModal h3 { margin: 0 0 0.5rem; font-size: 1.15rem; color: #1e293b; }
        #deleteModal p { margin: 0 0 1.5rem; color: #64748b; font-size: 0.9rem; }
        #deleteModal .modal-btns { display: flex; gap: 0.75rem; justify-content: center; }
        #deleteModal .btn-cancel { background:#f1f5f9; color:#475569; padding:0.6rem 1.5rem; border:none; border-radius:8px; cursor:pointer; font-weight:600; }
        #deleteModal .btn-danger { background:#ef4444; color:white; padding:0.6rem 1.5rem; border:none; border-radius:8px; cursor:pointer; font-weight:600; }
        #deleteModal .btn-cancel:hover { background:#e2e8f0; }
        #deleteModal .btn-danger:hover { background:#dc2626; }
    </style>
</head>
<body style="background-color: #f1f5f9;">

<!-- Custom Delete Confirmation Modal -->
<div id="deleteModal">
    <div id="deleteModalBox">
        <div class="modal-icon"><i class="fas fa-trash-alt"></i></div>
        <h3 id="modalTitle">Delete Record?</h3>
        <p id="modalMsg">This record will be permanently deleted. This action cannot be undone.</p>
        <div class="modal-btns">
            <button class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn-danger" id="modalConfirmBtn">Yes, Delete</button>
        </div>
    </div>
</div>
    <nav class="glass-dark" style="padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="dashboard.php" style="color: white;"><i class="fas fa-arrow-left"></i></a>
            <h2 data-t="widow">Widows</h2>
        </div>
        <div class="lang-switcher" style="position: static;">
            <button class="lang-btn" data-lang="en">EN</button>
            <button class="lang-btn" data-lang="ta">தமிழ்</button>
            <button class="lang-btn" data-lang="si">සිංහල</button>
        </div>
    </nav>

    <div class="container animate-fade" style="max-width: 1400px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem; flex-wrap:wrap; gap:0.75rem;">
            <div>
                <h3 data-t="widow">Widows Management</h3>
                <p style="color:var(--text-muted); font-size:0.875rem;">
                    Total found: <strong><?php echo number_format($totalRecords); ?></strong>
                    <?php if ($activeCount > 0): ?><span style="color:#0f4c5c; margin-left:0.5rem;">(filtered)</span><?php endif; ?>
                </p>
            </div>
            <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
                <a href="add-widows.php" class="btn btn-primary" style="text-decoration:none;">
                    <i class="fas fa-plus"></i> Add Widow
                </a>
                <button onclick="toggleUpload()" class="btn" style="background:#0f4c5c; color:white;"><i class="fas fa-file-excel"></i> Upload Excel</button>
            </div>
        </div>
        <?php require 'includes/adv_filter_panel.php'; ?>

        <!-- Bulk Actions Bar -->
        <div id="bulkActions" style="display: none; align-items: center; gap: 1rem; background: #fee2e2; padding: 0.6rem 1rem; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 1rem;">
            <span style="font-size: 0.85rem; font-weight: 600; color: #b91c1c;"><i class="fas fa-check-square"></i> <span id="selectedCount">0</span> Selected</span>
            <button id="bulkDeleteBtn" class="btn" style="background: #ef4444; color: white; padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                <i class="fas fa-trash-alt"></i> Delete Selected
            </button>
        </div>

        <!-- Embedded Upload Section (hidden by default) -->
        <div id="uploadSection" style="display:none; margin-bottom: 1.5rem;">
            <div class="glass" style="padding: 1.5rem; border-radius: var(--radius-lg);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h4 style="color: var(--primary);"><i class="fas fa-file-excel"></i> Upload Excel (Widows Template)</h4>
                    <button onclick="downloadWidowsTemplate()" class="btn" style="background:#e2e8f0; font-size:0.8rem; color:var(--text-dark); padding:0.4rem 0.8rem;">
                        <i class="fas fa-download"></i> Download Template
                    </button>
                </div>
                <div id="widowDrop" style="border: 2px dashed #cbd5e1; border-radius: 8px; padding: 2rem; text-align: center; cursor: pointer; background: rgba(255,255,255,0.5);">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #94a3b8; margin-bottom: 0.5rem;"></i>
                    <p style="font-size: 0.95rem;">Drag &amp; Drop Excel file here or click to browse</p>
                    <p style="font-size: 0.75rem; color: var(--text-muted);">Columns: SNo, Name, House No, Address, Family Member, NIC No, Date of Birth, Age, Occupation, Conduct No, Person’s House No, Aswesuma, Elder, PAMA, Kidney Disease/Disabled</p>
                    <input type="file" id="widowFile" hidden accept=".xlsx,.xls">
                </div>
                <div id="widowUploadResult" style="display:none; margin-top:1rem; padding:1rem; background:#f1f5f9; border-radius:8px;"></div>
            </div>
        </div>

        <div class="glass" style="padding: 2rem;">
            <div class="table-wrapper">
                <table style="font-size: 0.85rem; width: 100%; min-width: 1600px;">
                    <thead>
                        <tr>
                            <th style="width: 40px;"><input type="checkbox" id="selectAll" onclick="toggleAll(this)"></th>
                            <th>SNo</th>
                            <th>Name</th>
                            <th>House No</th>
                            <th>Address</th>
                            <th>Family Member</th>
                            <th>NIC No</th>
                            <th>Date of Birth</th>
                            <th>Age</th>
                            <th>Occupation</th>
                            <th>Conduct No</th>
                            <th>Person's House No</th>
                            <th>Aswesuma</th>
                            <th>Elder</th>
                            <th>PAMA</th>
                            <th>Kidney Disease/Disabled</th>
                            <th data-t="actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($filteredResults)): ?>
                            <tr>
                                <td colspan="17" style="text-align: center; padding: 2rem; color: var(--text-muted);">No records found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($filteredResults as $row): ?>
                                <tr>
                                    <td style="text-align: center;"><input type="checkbox" class="row-checkbox" value="<?php echo $row['family_id']; ?>" onclick="updateSelection()"></td>
                                    <td><?php echo htmlspecialchars($row['person_sno'] ?: '-'); ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['full_name'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['house_number'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['address'] ?? $row['fam_address'] ?? '-'); ?></td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-yes"><?php echo (int)($row['member_count'] ?? 1); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['nic'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['dob'] ?? '-'); ?></td>
                                    <td style="text-align: center;"><?php echo htmlspecialchars($row['age'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['occupation'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact_number'] ?: $row['contact_no'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['person_house_number'] ?: '-'); ?></td>
                                    <td style="text-align: center;">
                                        <?php if (!empty($row['aswesuma'])): ?>
                                            <span class="badge badge-yes"><?php echo htmlspecialchars($row['aswesuma']); ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-no">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;"><?php echo htmlspecialchars($row['is_elder'] ?: '-'); ?></td>
                                    <td style="text-align: center;"><?php echo (!empty($row['pmam']) && (int)$row['pmam'] > 0) ? htmlspecialchars($row['pmam']) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($row['kidney_disease'] ?: $row['disabled'] ?: '-'); ?></td>
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
                <?php require 'includes/pagination_ui.php'; ?>
            </div>
        </div>
    </div>

    <script src="assets/js/lang.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
    <script>
        function confirmDelete(id) {
            showDeleteModal(
                'Delete Family Record?',
                'This widow record and all family members will be permanently deleted. This action cannot be undone.',
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

        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });

        function toggleUpload() {
            const s = document.getElementById('uploadSection');
            s.style.display = s.style.display === 'none' ? 'block' : 'none';
        }
        
        const dropZ = document.getElementById('widowDrop');
        const fileIn = document.getElementById('widowFile');
        if (dropZ) {
            dropZ.onclick = () => fileIn.click();
            dropZ.ondragover = e => { e.preventDefault(); dropZ.style.borderColor='var(--primary)'; };
            dropZ.ondragleave = () => dropZ.style.borderColor='';
            dropZ.ondrop = e => { e.preventDefault(); dropZ.style.borderColor=''; handleWidowFile(e.dataTransfer.files[0]); };
            fileIn.onchange = e => handleWidowFile(e.target.files[0]);
        }
        function handleWidowFile(file) {
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                const wb = XLSX.read(new Uint8Array(e.target.result), { type: 'array' });
                const json = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]]);
                uploadWidowData(json);
            };
            reader.readAsArrayBuffer(file);
        }
        async function uploadWidowData(rows) {
            const res = document.getElementById('widowUploadResult');
            res.style.display = 'block';
            res.innerHTML = '<p>Uploading...</p>';
            let ok = 0, err = 0, errs = [];
            for (let i = 0; i < rows.length; i++) {
                const r = await fetch('api/widow-upload-process.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(rows[i])
                });
                const j = await r.json();
                j.success ? ok++ : (err++, errs.push(`Row ${i+2}: ${j.error}`));
            }
            res.innerHTML = `<p>✅ Uploaded: <b>${ok}</b> &nbsp; ❌ Errors: <b>${err}</b></p><p style="color:#ef4444;font-size:0.8rem">${errs.join('<br>')}</p>`;
            if (ok > 0) setTimeout(() => location.reload(), 2000);
        }
        function downloadWidowsTemplate() {
            const t = [{ 
                SNo: 1, 
                Name: 'Widow Name', 
                'House No': '177/1', 
                Address: 'Main Road', 
                'Family Member': 4, 
                'NIC No': '197012345678', 
                'Date of Birth': '1970-01-01', 
                Age: 53, 
                Occupation: 'None', 
                'Conduct No': '0771234567', 
                'Person’s House No': '', 
                Aswesuma: 'severely poor', 
                Elder: 5000, 
                PAMA: 250, 
                'Kidney Disease/Disabled': 'disease' 
            }];
            const ws = XLSX.utils.json_to_sheet(t);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Widows');
            XLSX.writeFile(wb, 'Widows_Template.xlsx');
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
                bulkActions.style.display = 'flex';
                countSpan.innerText = selected.length;
            } else {
                bulkActions.style.display = 'none';
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
                ids.length + ' selected widow record(s) and all their members will be permanently deleted.',
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
                            closeDeleteModal();
                            const t = document.createElement('div');
                            t.style.cssText = 'position:fixed;bottom:2rem;right:2rem;background:#ef4444;color:white;padding:1rem 1.5rem;border-radius:8px;font-size:0.9rem;z-index:9998;';
                            t.textContent = 'Error: ' + (result.error || 'Unknown error');
                            document.body.appendChild(t);
                            setTimeout(() => t.remove(), 4000);
                        }
                    } catch (e) {
                        closeDeleteModal();
                    }
                }
            );
        }

        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('bulkDeleteBtn');
            if (btn) btn.addEventListener('click', doBulkDelete);
        });
    </script>
</body>
</html>
