<?php
require_once 'includes/config.php';
check_auth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-t="add_family">Add Family - GS System</title>
    <link rel="stylesheet" href="assets/css/style.css?v=3.0">
    <link rel="stylesheet" href="assets/css/responsive.css?v=1.3">
    <link rel="stylesheet" href="assets/css/theme.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .step-content { display: none; }
        .step-content.active { display: block; animation: fadeIn 0.3s ease-out; }
    </style>
</head>
<body style="background-color: #f8fafc;">
    <nav class="glass-dark" style="padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="dashboard.php" style="color: white;"><i class="fas fa-arrow-left"></i></a>
            <h2 data-t="add_family">Add Family</h2>
        </div>
        <div class="lang-switcher" style="position: static;">
            <button class="lang-btn" data-lang="en">EN</button>
            <button class="lang-btn" data-lang="ta">தமிழ்</button>
            <button class="lang-btn" data-lang="si">සිංහල</button>
        </div>
    </nav>

    <div class="container" style="max-width: 800px;">
        <div class="glass animate-fade" style="padding: 3rem; border-radius: var(--radius-lg);">
            
            <!-- Wizard Stepper -->
            <div class="wizard-steps">
                <div class="step active" id="step-node-1">
                    <div class="step-num">1</div>
                    <p style="font-size: 0.75rem; font-weight: 600;" data-t="address">Family Details</p>
                </div>
                <div style="flex: 1; height: 1px; background: #e2e8f0; margin-top: 17px;"></div>
                <div class="step" id="step-node-2">
                    <div class="step-num">2</div>
                    <p style="font-size: 0.75rem; font-weight: 600;" data-t="add_member">Add Members</p>
                </div>
            </div>

            <form id="familyForm" action="api/add-family-process.php" method="POST">
                
                <!-- Step 1: Family Details -->
                <div class="step-content active" id="step-1">
                    <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Serial Number (SNO)</label>
                            <input type="number" name="sno">
                        </div>
                        <div class="form-group">
                            <label>Family Number</label>
                            <input type="text" name="family_number">
                        </div>
                        <div class="form-group">
                            <label>House Number</label>
                            <input type="text" name="house_number">
                        </div>
                        <div class="form-group">
                            <label>Road</label>
                            <input type="text" name="road">
                        </div>
                    </div>

                    <div class="form-group">
                        <label data-t="address">Address</label>
                        <textarea name="address" rows="2" required></textarea>
                    </div>

                        <div class="form-group">
                            <label data-t="income">Income Level</label>
                            <select name="income_level" required>
                                <option value="">Select Level</option>
                                <option value="Low (< 25,000)">Low (< 25,000)</option>
                                <option value="Middle (25,000 - 75,000)">Middle (25,000 - 75,000)</option>
                                <option value="High (> 75,000)">High (> 75,000)</option>
                            </select>
                        </div>

                    <div class="form-group">
                        <label data-t="contact">Contact Number</label>
                        <input type="text" name="contact_no" required>
                    </div>

                    <div class="form-group">
                        <label>Current Situation (Housing/Remarks)</label>
                        <textarea name="housing_condition" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group" style="display: flex; gap: 2rem; margin-top: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_homeless" value="1"> <span data-t="homeless">House Needs (Housing Assistance)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_disaster" value="1"> <span data-t="disaster">Disaster Affected</span>
                        </label>
                    </div>
                    
                    <div style="text-align: right; margin-top: 2rem;">
                        <button type="button" class="btn btn-primary" onclick="nextStep(2)">
                            <span data-t="next">Next</span> <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Member Details -->
                <div class="step-content" id="step-2">
                    <div id="membersContainer">
                        <!-- Dynamic Member Rows -->
                        <div class="member-card glass" style="padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1rem; position: relative; border: 1px solid #e2e8f0; background: #fff;">
                            <h4 style="margin-bottom: 1.5rem; color: var(--primary);" data-t="add_member">Member 1 (Head)</h4>
                            <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label data-t="name">Full Name</label>
                                    <input type="text" name="member_name[]" required>
                                </div>
                                <div class="form-group">
                                    <label data-t="nic">NIC Number</label>
                                    <input type="text" name="member_nic[]" required>
                                </div>
                                <div class="form-group">
                                    <label>Date of Birth</label>
                                    <input type="date" name="member_dob[]">
                                </div>
                                <div class="form-group">
                                    <label data-t="age">Age</label>
                                    <input type="number" name="member_age[]" required>
                                </div>
                                <div class="form-group">
                                    <label data-t="gender">Gender</label>
                                    <select name="member_gender[]" required>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Occupation</label>
                                    <input type="text" name="member_occupation[]">
                                </div>
                                <div class="form-group">
                                    <label>Contact Number (Personal)</label>
                                    <input type="text" name="member_contact[]">
                                </div>
                                <div class="form-group">
                                    <label data-t="relationship">Relationship</label>
                                    <input type="text" name="member_relation[]" value="Head of Household" required>
                                </div>
                                <div class="form-group" style="grid-column: span 2;">
                                    <label>Person's House Number (If different)</label>
                                    <input type="text" name="member_house_no[]">
                                </div>
                                
                                <div class="form-group" style="grid-column: span 2;">
                                    <label style="font-weight: 600; margin-bottom: 0.5rem; display: block;">Additional Status/Requirements</label>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; background: #f8fafc; padding: 1.5rem; border-radius: 8px;">
                                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.85rem;">
                                            <input type="checkbox" name="aswesuma[0]" value="1"> <span>Aswesuma</span>
                                        </label>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <label style="font-size: 0.85rem; min-width: 50px;">PMAM:</label>
                                            <input type="number" name="pmam[]" placeholder="Integer" style="padding: 0.25rem; width: 80px;">
                                        </div>
                                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.85rem;">
                                            <input type="checkbox" name="kidney_disease[0]" value="1"> <span>Kidney Disease</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.85rem;">
                                            <input type="checkbox" name="disabled[0]" value="1"> <span>Disabled</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.85rem;">
                                            <input type="checkbox" name="is_widow[0]" value="1"> <span data-t="widow">Widow</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.85rem;">
                                            <input type="checkbox" name="is_pregnant[0]" value="1"> <span data-t="pregnant">Pregnant</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.85rem;">
                                            <input type="checkbox" name="is_elder[0]" value="1"> <span data-t="elder">Elderly</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline" style="width: 100%; margin-bottom: 2rem;" onclick="addMember()">
                        <i class="fas fa-plus"></i> <span data-t="add_member">Add Another Member</span>
                    </button>

                    <div style="display: flex; justify-content: space-between; margin-top: 2rem;">
                        <button type="button" class="btn btn-outline" onclick="nextStep(1)">
                            <i class="fas fa-chevron-left"></i> <span data-t="prev">Previous</span>
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <span data-t="save">Save Family</span>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <script src="assets/js/lang.js"></script>
    <script>
        let currentStep = 1;
        let memberCount = 1;

        function nextStep(step) {
            // Basic validation for Step 1
            if (step === 2) {
                const address = document.querySelector('textarea[name="address"]').value;
                const contact = document.querySelector('input[name="contact_no"]').value;
                if (!address || !contact) {
                    alert("Please fill in all family details");
                    return;
                }
            }

            document.getElementById(`step-${currentStep}`).classList.remove('active');
            document.getElementById(`step-node-${currentStep}`).classList.remove('active');
            
            currentStep = step;
            
            document.getElementById(`step-${currentStep}`).classList.add('active');
            document.getElementById(`step-node-${currentStep}`).classList.add('active');
            
            window.scrollTo(0, 0);
        }

        function addMember() {
            memberCount++;
            const container = document.getElementById('membersContainer');
            const newMember = document.createElement('div');
            newMember.className = 'member-card glass animate-fade';
            newMember.style = 'padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1rem; position: relative; border: 1px solid #e2e8f0; background: #fff;';
            
            newMember.innerHTML = `
                <button type="button" onclick="this.parentElement.remove()" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; color: #ef4444; cursor: pointer;"><i class="fas fa-times"></i></button>
                <h4 style="margin-bottom: 1.5rem; color: var(--primary);">Member ${memberCount}</h4>
                <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="member_name[]" required>
                    </div>
                    <div class="form-group">
                        <label>NIC Number</label>
                        <input type="text" name="member_nic[]" required>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="member_dob[]">
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" name="member_age[]" required>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="member_gender[]" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Occupation</label>
                        <input type="text" name="member_occupation[]">
                    </div>
                    <div class="form-group">
                        <label>Contact Number (Personal)</label>
                        <input type="text" name="member_contact[]">
                    </div>
                    <div class="form-group">
                        <label>Relationship</label>
                        <input type="text" name="member_relation[]" required>
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Person's House Number (If different)</label>
                        <input type="text" name="member_house_no[]">
                    </div>
                    
                    <div class="form-group" style="grid-column: span 2;">
                        <label style="font-weight: 600; margin-bottom: 0.5rem; display: block;">Additional Status/Requirements</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; background: #f8fafc; padding: 1.5rem; border-radius: 8px;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.85rem;">
                                <input type="checkbox" name="aswesuma[${memberCount-1}]" value="1"> <span>Aswesuma</span>
                            </label>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <label style="font-size: 0.85rem; min-width: 50px;">PMAM:</label>
                                <input type="number" name="pmam[]" placeholder="Integer" style="padding: 0.25rem; width: 80px;">
                            </div>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.85rem;">
                                <input type="checkbox" name="kidney_disease[${memberCount-1}]" value="1"> <span>Kidney Disease</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.85rem;">
                                <input type="checkbox" name="disabled[${memberCount-1}]" value="1"> <span>Disabled</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.85rem;">
                                <input type="checkbox" name="is_widow[${memberCount-1}]" value="1"> <span>Widow</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.85rem;">
                                <input type="checkbox" name="is_pregnant[${memberCount-1}]" value="1"> <span>Pregnant</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.85rem;">
                                <input type="checkbox" name="is_elder[${memberCount-1}]" value="1"> <span>Elderly</span>
                            </label>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newMember);
        }
    </script>
</body>
</html>
