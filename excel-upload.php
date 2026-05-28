<?php
require_once 'includes/config.php';
check_auth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-t="upload_excel">Excel Upload - GS System</title>
    <link rel="stylesheet" href="assets/css/style.css?v=3.0">
    <link rel="stylesheet" href="assets/css/responsive.css?v=1.3">
    <link rel="stylesheet" href="assets/css/theme.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
    <style>
        .drop-zone {
            border: 2px dashed #cbd5e1;
            border-radius: var(--radius-lg);
            padding: 4rem 2rem;
            text-align: center;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
        }
        .drop-zone.dragover {
            border-color: var(--primary);
            background: rgba(15, 76, 92, 0.05);
        }
        .progress-bar {
            height: 10px;
            background: #e2e8f0;
            border-radius: 5px;
            margin: 2rem 0;
            overflow: hidden;
            display: none;
        }
        .progress-fill {
            height: 100%;
            background: var(--primary);
            width: 0%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body style="background-color: #f8fafc;">
    <nav class="glass-dark" style="padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="dashboard.php" style="color: white;"><i class="fas fa-arrow-left"></i></a>
            <h2 data-t="upload_excel">Bulk Upload</h2>
        </div>
        <div class="lang-switcher" style="position: static;">
            <button class="lang-btn" data-lang="en">EN</button>
            <button class="lang-btn" data-lang="ta">தமிழ்</button>
            <button class="lang-btn" data-lang="si">සිංහල</button>
        </div>
    </nav>

    <div class="container" style="max-width: 800px;">
        <div class="glass animate-fade" style="padding: 3rem; border-radius: var(--radius-lg);">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h3 data-t="upload_excel">Upload Family Data</h3>
                <p style="color: var(--text-muted); margin-top: 0.5rem;">Upload an Excel file with family and member details.</p>
            </div>

            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">Target Category (Optional)</label>
                <select id="categorySelect" class="form-control" style="width: 100%; padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid #cbd5e1;">
                    <option value="">None (General Upload)</option>
                    <option value="widow">Widow</option>
                    <option value="elderly">Elderly</option>
                    <option value="pregnant">Pregnant</option>
                    <option value="homeless">House Needs (Housing Assistance)</option>
                    <option value="disaster">Disaster Affected</option>
                </select>
                <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.5rem;">If selected, all records in the file will be automatically assigned to this category.</p>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-bottom: 1rem;">
                <button onclick="downloadTemplate()" class="btn btn-secondary" style="background: white; border: 1px solid #cbd5e1; padding: 0.5rem 1rem; border-radius: var(--radius-md); display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <i class="fas fa-download"></i> Download Sample Excel Template
                </button>
            </div>

            <div id="dropZone" class="drop-zone">
                <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #94a3b8; margin-bottom: 1rem;"></i>
                <p style="font-size: 1.1rem; font-weight: 500;" data-t="drag_drop">Drag & Drop Excel file here</p>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.5rem;">or click to browse files</p>
                <input type="file" id="fileInput" hidden accept=".xlsx, .xls">
            </div>

            <div class="progress-bar" id="progressBar">
                <div class="progress-fill" id="progressFill"></div>
            </div>

            <div id="resultArea" style="display: none; margin-top: 2rem;">
                <h4 style="margin-bottom: 1rem;" data-t="upload_result">Upload Result</h4>
                <div style="background: #f1f5f9; padding: 1.5rem; border-radius: var(--radius-md);">
                    <p>Total Families Found: <span id="totalFound" style="font-weight: 600;">0</span></p>
                    <p style="color: var(--success);">Successfully Uploaded: <span id="successCount" style="font-weight: 600;">0</span></p>
                    <p style="color: var(--error);">Errors: <span id="errorCount" style="font-weight: 600;">0</span></p>
                </div>
                <div id="errorDetails" style="margin-top: 1rem; color: var(--error); font-size: 0.875rem;"></div>
            </div>
        </div>
    </div>

    <script src="assets/js/lang.js"></script>
    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');

        dropZone.onclick = () => fileInput.click();

        dropZone.ondragover = (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        };

        dropZone.ondragleave = () => dropZone.classList.remove('dragover');

        dropZone.ondrop = (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            const file = e.dataTransfer.files[0];
            handleFile(file);
        };

        fileInput.onchange = (e) => {
            const file = e.target.files[0];
            handleFile(file);
        };

        function handleFile(file) {
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = (e) => {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const firstSheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[firstSheetName];
                // range:5 → row 6 (0-indexed 5) is the header row;
                // actual data starts from row 7 onward.
                const jsonData = XLSX.utils.sheet_to_json(worksheet, { range: 5 });
                
                if (jsonData.length === 0) {
                    alert("The file seems to be empty or has no data after the header row.");
                    return;
                }

                uploadData(jsonData);
            };
            reader.readAsArrayBuffer(file);
        }

        async function uploadData(data) {
            const category = document.getElementById('categorySelect').value;
            document.getElementById('progressBar').style.display = 'block';
            document.getElementById('resultArea').style.display = 'block';
            document.getElementById('totalFound').innerText = data.length;
            
            let success = 0;
            let skipped = 0;
            let errors = 0;
            let errorMsgs = [];

            for (let i = 0; i < data.length; i++) {
                try {
                    const payload = { 
                        ...data[i],
                        GlobalCategory: category 
                    };

                    const response = await fetch('api/bulk-upload-process.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const result = await response.json();
                    
                    if (result.skipped) {
                        skipped++; // blank / non-data row — silently ignore
                    } else if (result.success) {
                        success++;
                    } else {
                        errors++;
                        errorMsgs.push(`Row ${i + 7}: ${result.error}`);
                    }
                } catch (e) {
                    errors++;
                    errorMsgs.push(`Row ${i + 7}: Network Error`);
                }

                const progress = ((i + 1) / data.length) * 100;
                document.getElementById('progressFill').style.width = `${progress}%`;
                document.getElementById('successCount').innerText = success;
                document.getElementById('errorCount').innerText = errors;
            }

            // Show skipped count if any blank rows were silently ignored
            const details = [];
            if (skipped > 0) details.push(`<span style="color:#64748b">⚠️ ${skipped} blank/header row(s) skipped.</span>`);
            if (errorMsgs.length > 0) details.push(errorMsgs.join('<br>'));
            document.getElementById('errorDetails').innerHTML = details.join('<br>');
        }

        function downloadTemplate() {
            const template = [
                {
                    "SNO": 1,
                    "Family_No": "FAM-001",
                    "Name": "Sunil Perera",
                    "House_No": "123",
                    "Address": "Main Street, Colombo 01",
                    "Gender": "Male",
                    "Family_Member_Count": 4,
                    "NIC_No": "198012345678",
                    "Date_of_Birth": "1980-05-15",
                    "Age": 43,
                    "Occupation": "Teacher",
                    "Contact_No": "0771234567",
                    "Person_House_No": "",
                    "Aswesuma": "Severely Poor",
                    "Elder": 5000,
                    "PMAM": 250,
                    "Kidney_Disease_Disabled": "No"
                }
            ];
            const ws = XLSX.utils.json_to_sheet(template);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Families");
            XLSX.writeFile(wb, "GS_Family_Template.xlsx");
        }
    </script>
</body>
</html>
