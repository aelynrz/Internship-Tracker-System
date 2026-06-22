<?php
// admin_companies.php
session_start();
require_once 'db_connect.php';

// Security Check
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Admin') {
    header("Location: admin_login.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ACTION: Delete Company
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $delete_id = intval($_POST['company_id']);
        
        $clear_stmt = $conn->prepare("UPDATE User SET CompanyID = NULL WHERE CompanyID = ?");
        $clear_stmt->bind_param("i", $delete_id);
        $clear_stmt->execute();
        $clear_stmt->close();

        $stmt = $conn->prepare("DELETE FROM Company WHERE CompanyID = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Company deleted successfully.</div>";
        }
        $stmt->close();
    }
    
    // ACTION: Add New Company (With Option to Assign Existing or Create New Supervisor)
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        // Company Details
        $name = trim($_POST['company_name']);
        $industry = trim($_POST['industry']);
        $assignment_type = $_POST['assignment_type']; // 'existing' or 'new'
        
        // 1. Create the Company First
        $stmt = $conn->prepare("INSERT INTO Company (CompanyName, Industry) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $industry);
        
        if ($stmt->execute()) {
            $new_company_id = $stmt->insert_id; // Grab the new CP ID
            
            // 2A. Assign EXISTING Supervisor
            if ($assignment_type === 'existing') {
                $existing_sup_id = !empty($_POST['existing_supervisor_id']) ? intval($_POST['existing_supervisor_id']) : NULL;
                
                if ($existing_sup_id) {
                    $assign_stmt = $conn->prepare("UPDATE User SET CompanyID = ? WHERE UserID = ? AND Roles = 'Supervisor'");
                    $assign_stmt->bind_param("ii", $new_company_id, $existing_sup_id);
                    $assign_stmt->execute();
                    $assign_stmt->close();
                    $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Company created and existing Supervisor assigned successfully.</div>";
                } else {
                    $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Company created successfully (Left unassigned).</div>";
                }
            } 
            // 2B. Create NEW Supervisor
            elseif ($assignment_type === 'new') {
                $sup_name = trim($_POST['sup_name']);
                $sup_email = trim($_POST['sup_email']);
                $sup_contact = trim($_POST['sup_contact']);
                $sup_password = password_hash($_POST['sup_password'], PASSWORD_DEFAULT);

                $sup_stmt = $conn->prepare("INSERT INTO User (Name, Email, Password, Roles, CompanyID, ContactNumber) VALUES (?, ?, ?, 'Supervisor', ?, ?)");
                $sup_stmt->bind_param("sssis", $sup_name, $sup_email, $sup_password, $new_company_id, $sup_contact);
                
                if ($sup_stmt->execute()) {
                    $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Company and New Supervisor account created successfully.</div>";
                } else {
                    $message = "<div style='color: #ff9800; background: #fff3e0; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Company created, but error creating Supervisor (Email might be taken).</div>";
                }
                $sup_stmt->close();
            }
        } else {
            $message = "<div style='color: #c62828; background: #ffebee; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Error creating company.</div>";
        }
        $stmt->close();
    }

    // ACTION: Edit Existing Company
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $edit_company_id = intval($_POST['company_id']);
        $name = trim($_POST['company_name']);
        $industry = trim($_POST['industry']);
        $new_supervisor_id = !empty($_POST['supervisor_id']) ? intval($_POST['supervisor_id']) : NULL;

        $stmt = $conn->prepare("UPDATE Company SET CompanyName=?, Industry=? WHERE CompanyID=?");
        $stmt->bind_param("ssi", $name, $industry, $edit_company_id);

        if ($stmt->execute()) {
            $clear_old = $conn->prepare("UPDATE User SET CompanyID = NULL WHERE CompanyID = ? AND Roles = 'Supervisor'");
            $clear_old->bind_param("i", $edit_company_id);
            $clear_old->execute();
            $clear_old->close();

            if ($new_supervisor_id) {
                $assign_new = $conn->prepare("UPDATE User SET CompanyID = ? WHERE UserID = ? AND Roles = 'Supervisor'");
                $assign_new->bind_param("ii", $edit_company_id, $new_supervisor_id);
                $assign_new->execute();
                $assign_new->close();
            }
            $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Company updated successfully.</div>";
        }
        $stmt->close();
    }
}

// Fetch all available supervisors for the Edit Modal
$supervisors = [];
$sup_query = "SELECT UserID, Name FROM User WHERE Roles = 'Supervisor' ORDER BY Name ASC";
$sup_result = $conn->query($sup_query);
if ($sup_result->num_rows > 0) {
    while($s = $sup_result->fetch_assoc()) {
        $supervisors[] = $s;
    }
}

// Fetch UNASSIGNED supervisors for the Add Modal
$unassigned_supervisors = [];
$unsup_query = "SELECT UserID, Name FROM User WHERE Roles = 'Supervisor' AND CompanyID IS NULL ORDER BY Name ASC";
$unsup_result = $conn->query($unsup_query);
if ($unsup_result->num_rows > 0) {
    while($s = $unsup_result->fetch_assoc()) {
        $unassigned_supervisors[] = $s;
    }
}

// Fetch companies and their assigned supervisor
$query = "
    SELECT c.CompanyID, c.CompanyName, c.Industry, u.Name AS SupervisorName, u.UserID AS SupervisorID 
    FROM Company c
    LEFT JOIN User u ON c.CompanyID = u.CompanyID AND u.Roles = 'Supervisor'
    ORDER BY c.CompanyID DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Companies - Admin</title>
    <link rel="stylesheet" href="assets/css/admin_dashboard.css">
</head>
<body>

    <aside class="sidebar">
        <div class="brand">InternTrack</div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="admin_dashboard.php" class="nav-link">Dashboard</a></li>
            <li class="nav-item"><a href="admin_users.php" class="nav-link">Users</a></li>
            <li class="nav-item"><a href="admin_companies.php" class="nav-link active">Companies</a></li>
            <li class="nav-item"><a href="admin_applications.php" class="nav-link">Applications</a></li>
        </ul>
        <ul class="nav-menu" style="margin-top: auto;">
            <li class="nav-item"><a href="logout_admin.php" class="nav-link">Log out</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Manage Companies</h1>
            <button onclick="document.getElementById('addCompanyModal').style.display='flex'" style="padding: 10px 20px; background: var(--accent-dark); color: white; border: none; border-radius: var(--radius-md); cursor: pointer; font-weight: 500;">+ Add Company</button>
        </header>

        <section class="data-section">
            <?php echo $message; ?>
            <h2 class="section-title">Partner Companies</h2>
            <table>
                <thead>
                    <tr>
                        <th>Company ID</th>
                        <th>Company Name</th>
                        <th>Industry</th>
                        <th>Assigned Supervisor</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) { 
                            $sup_id = $row['SupervisorID'] ? $row['SupervisorID'] : '';
                    ?>
                    <tr>
                        <td style="font-weight: 600; color: var(--accent-dark);">
                            <?php echo sprintf("CP_%05d", $row['CompanyID']); ?>
                        </td>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($row['CompanyName']); ?></td>
                        <td><?php echo htmlspecialchars($row['Industry'] ?? 'N/A'); ?></td>
                        <td>
                            <?php if ($row['SupervisorName']): ?>
                                <span style="background: #e3f2fd; color: #1565c0; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                    <?php echo htmlspecialchars($row['SupervisorName']); ?>
                                </span>
                            <?php else: ?>
                                <span style="color: var(--text-secondary); font-style: italic; font-size: 13px;">Unassigned</span>
                            <?php endif; ?>
                        </td>
                        <td style="display: flex; gap: 10px; align-items: center;">
                            <button type="button" 
                                    class="edit-company-btn"
                                    data-companyid="<?php echo $row['CompanyID']; ?>"
                                    data-name="<?php echo htmlspecialchars($row['CompanyName']); ?>"
                                    data-industry="<?php echo htmlspecialchars($row['Industry']); ?>"
                                    data-supervisorid="<?php echo $sup_id; ?>"
                                    style="background: #e0e0e0; color: #333; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500;">
                                Edit
                            </button>
                            <form method="POST" action="" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete <?php echo addslashes($row['CompanyName']); ?>?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="company_id" value="<?php echo $row['CompanyID']; ?>">
                                <button type="submit" style="background: #ffcdd2; color: #c62828; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500;">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='5'>No companies found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>

    <div class="modal-overlay" id="addCompanyModal">
        <div class="modal-card">
            <h2 style="margin-bottom: 10px;">Register New Company</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                
                <div class="section-divider">Company Details</div>
                <div class="form-group"><label>Company Name</label><input type="text" name="company_name" class="form-control" required></div>
                <div class="form-group"><label>Industry</label><input type="text" name="industry" class="form-control" placeholder="e.g., Tech, Finance" required></div>
                
                <div class="section-divider">HR/Supervisor Setup</div>
                
                <div class="form-group" style="display: flex; gap: 20px; margin-bottom: 15px;">
                    <label style="cursor: pointer;">
                        <input type="radio" name="assignment_type" value="existing" checked onclick="toggleAssignmentType('existing')"> 
                        Assign Existing Supervisor
                    </label>
                    <label style="cursor: pointer;">
                        <input type="radio" name="assignment_type" value="new" onclick="toggleAssignmentType('new')"> 
                        Create New Supervisor
                    </label>
                </div>

                <div id="assign_existing_section">
                    <div class="form-group">
                        <select name="existing_supervisor_id" class="form-control">
                            <option value="">-- Leave Unassigned --</option>
                            <?php foreach($unassigned_supervisors as $unsup): ?>
                                <option value="<?php echo $unsup['UserID']; ?>"><?php echo htmlspecialchars($unsup['Name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: #7a7a7a; margin-top: 5px; display: block;">Only showing Supervisors who don't have a company yet.</small>
                    </div>
                </div>

                <div id="create_new_section" style="display: none;">
                    <div class="form-group"><label>Supervisor Name</label><input type="text" name="sup_name" id="req_sup_name" class="form-control"></div>
                    <div class="form-group"><label>Email Address</label><input type="email" name="sup_email" id="req_sup_email" class="form-control"></div>
                    <div class="form-group"><label>Contact Number</label><input type="text" name="sup_contact" id="req_sup_contact" class="form-control" placeholder="e.g., +60123456789"></div>
                    <div class="form-group"><label>Temporary Password</label><input type="password" name="sup_password" id="req_sup_pass" class="form-control"></div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="button" onclick="document.getElementById('addCompanyModal').style.display='none'" style="flex: 1; padding: 10px; border: 1px solid var(--border-color); background: white; border-radius: var(--radius-md); cursor: pointer;">Cancel</button>
                    <button type="submit" style="flex: 1; padding: 10px; border: none; background: var(--accent-dark); color: white; border-radius: var(--radius-md); cursor: pointer;">Save All</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="editCompanyModal">
        <div class="modal-card">
            <h2 style="margin-bottom: 20px;">Edit Company</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="company_id" id="edit_company_id">
                
                <div class="form-group"><label>Company Name</label><input type="text" name="company_name" id="edit_company_name" class="form-control" required></div>
                <div class="form-group"><label>Industry</label><input type="text" name="industry" id="edit_industry" class="form-control"></div>
                
                <div class="form-group">
                    <label>Re-Assign Supervisor <span style="font-size: 12px; font-weight: normal; color: #7a7a7a;">(Optional)</span></label>
                    <select name="supervisor_id" id="edit_supervisor_id" class="form-control">
                        <option value="">-- Unassigned --</option>
                        <?php foreach($supervisors as $sup): ?>
                            <option value="<?php echo $sup['UserID']; ?>"><?php echo htmlspecialchars($sup['Name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="button" onclick="document.getElementById('editCompanyModal').style.display='none'" style="flex: 1; padding: 10px; border: 1px solid var(--border-color); background: white; border-radius: var(--radius-md); cursor: pointer;">Cancel</button>
                    <button type="submit" style="flex: 1; padding: 10px; border: none; background: var(--accent-dark); color: white; border-radius: var(--radius-md); cursor: pointer;">Update Company</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Logic to toggle between Existing and New Supervisor in the Add Modal
        function toggleAssignmentType(type) {
            const existingSection = document.getElementById('assign_existing_section');
            const newSection = document.getElementById('create_new_section');
            
            // The input fields for the new supervisor
            const reqName = document.getElementById('req_sup_name');
            const reqEmail = document.getElementById('req_sup_email');
            const reqContact = document.getElementById('req_sup_contact');
            const reqPass = document.getElementById('req_sup_pass');

            if (type === 'existing') {
                existingSection.style.display = 'block';
                newSection.style.display = 'none';
                
                // Remove 'required' so the form can submit
                reqName.removeAttribute('required');
                reqEmail.removeAttribute('required');
                reqContact.removeAttribute('required');
                reqPass.removeAttribute('required');
            } else {
                existingSection.style.display = 'none';
                newSection.style.display = 'block';
                
                // Add 'required' back so they can't submit empty text fields
                reqName.setAttribute('required', 'required');
                reqEmail.setAttribute('required', 'required');
                reqContact.setAttribute('required', 'required');
                reqPass.setAttribute('required', 'required');
            }
        }

        // Logic for the Edit Button (using data attributes like we did before)
        document.addEventListener('DOMContentLoaded', function() {
            const editButtons = document.querySelectorAll('.edit-company-btn');

            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const companyId = this.getAttribute('data-companyid');
                    const name = this.getAttribute('data-name');
                    const industry = this.getAttribute('data-industry');
                    const supervisorId = this.getAttribute('data-supervisorid');

                    document.getElementById('edit_company_id').value = companyId;
                    document.getElementById('edit_company_name').value = name;
                    document.getElementById('edit_industry').value = industry;
                    document.getElementById('edit_supervisor_id').value = supervisorId;

                    document.getElementById('editCompanyModal').style.display = 'flex';
                });
            });
        });

        // Close modals when clicking the dark background
        window.onclick = function(event) {
            const addCompanyModal = document.getElementById('addCompanyModal');
            const editCompanyModal = document.getElementById('editCompanyModal');

            if (event.target == addCompanyModal) addCompanyModal.style.display = 'none';
            if (event.target == editCompanyModal) editCompanyModal.style.display = 'none';
        };
    </script>
</body>
</html>