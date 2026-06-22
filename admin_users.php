<?php
// admin_users.php
session_start();
require_once 'db_connect.php';

// Security Check
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Admin') {
    header("Location: admin_login.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ACTION: Delete User
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $delete_id = intval($_POST['user_id']);
        if ($delete_id === $_SESSION['UserID']) {
            $message = "<div style='color: #c62828; background: #ffebee; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>You cannot delete your own admin account.</div>";
        } else {
            $stmt = $conn->prepare("DELETE FROM User WHERE UserID = ?");
            $stmt->bind_param("i", $delete_id);
            if ($stmt->execute()) {
                $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>User deleted successfully.</div>";
            }
            $stmt->close();
        }
    }
    
    // ACTION: Add Student
    if (isset($_POST['action']) && $_POST['action'] === 'add_student') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $matric = trim($_POST['matric']);
        $cgpa = floatval($_POST['cgpa']);
        $major = trim($_POST['major']);
        $contact_number = trim($_POST['contact_number']);

        $stmt = $conn->prepare("INSERT INTO User (Name, Email, Password, Roles, MatricNumber, CGPA, Major, ContactNumber) VALUES (?, ?, ?, 'Student', ?, ?, ?, ?)");
        $stmt->bind_param("ssssdss", $name, $email, $password, $matric, $cgpa, $major, $contact_number);
        if ($stmt->execute()) {
            $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Student added successfully.</div>";
        } else {
            $message = "<div style='color: #c62828; background: #ffebee; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Error adding student. Email or Matric might be taken.</div>";
        }
        $stmt->close();
    }

    // ACTION: Add Supervisor
    if (isset($_POST['action']) && $_POST['action'] === 'add_supervisor') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $company_id = !empty($_POST['company_id']) ? intval($_POST['company_id']) : NULL;
        $contact_number = trim($_POST['contact_number']);

        $stmt = $conn->prepare("INSERT INTO User (Name, Email, Password, Roles, CompanyID, ContactNumber) VALUES (?, ?, ?, 'Supervisor', ?, ?)");
        $stmt->bind_param("sssis", $name, $email, $password, $company_id, $contact_number);
        if ($stmt->execute()) {
            $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Supervisor added successfully.</div>";
        } else {
            $message = "<div style='color: #c62828; background: #ffebee; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Error adding supervisor. Email might be taken.</div>";
        }
        $stmt->close();
    }

    // ACTION: Edit Existing User
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $edit_id = intval($_POST['user_id']);
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $role = $_POST['role']; 
        $new_password = $_POST['password'];

        $matric = ($role === 'Student') ? trim($_POST['matric']) : NULL;
        $cgpa = ($role === 'Student') ? floatval($_POST['cgpa']) : NULL;
        $major = ($role === 'Student') ? trim($_POST['major']) : NULL;
        $company_id = ($role === 'Supervisor' && !empty($_POST['company_id'])) ? intval($_POST['company_id']) : NULL;
        $contact_number = ($role === 'Student' || $role === 'Supervisor') ? trim($_POST['contact_number']) : NULL; 

        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE User SET Name=?, Email=?, Password=?, MatricNumber=?, CGPA=?, Major=?, CompanyID=?, ContactNumber=? WHERE UserID=?");
            $stmt->bind_param("ssssdsisi", $name, $email, $hashed_password, $matric, $cgpa, $major, $company_id, $contact_number, $edit_id);
        } else {
            $stmt = $conn->prepare("UPDATE User SET Name=?, Email=?, MatricNumber=?, CGPA=?, Major=?, CompanyID=?, ContactNumber=? WHERE UserID=?");
            $stmt->bind_param("sssdsisi", $name, $email, $matric, $cgpa, $major, $company_id, $contact_number, $edit_id);
        }
        if ($stmt->execute()) {
            $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>User updated successfully.</div>";
        }
        $stmt->close();
    }
}

// UPGRADED SQL: Fetch companies AND find out who is assigned to them
$companies = [];
$comp_query = $conn->query("
    SELECT c.CompanyID, c.CompanyName, u.UserID AS AssignedSupervisor 
    FROM Company c 
    LEFT JOIN User u ON c.CompanyID = u.CompanyID AND u.Roles = 'Supervisor' 
    ORDER BY c.CompanyName ASC
");
while ($c = $comp_query->fetch_assoc()) {
    $companies[] = $c;
}

// Fetch all system users
$query = "
    SELECT u.UserID, u.Name, u.Email, u.Roles, u.MatricNumber, u.CGPA, u.Major, u.ContactNumber, u.CompanyID, c.CompanyName 
    FROM User u 
    LEFT JOIN Company c ON u.CompanyID = c.CompanyID
    ORDER BY u.UserID DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="assets/css/admin_dashboard.css">
</head>
<body>

    <aside class="sidebar">
        <div class="brand">InternTrack</div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="admin_dashboard.php" class="nav-link">Dashboard</a></li>
            <li class="nav-item"><a href="admin_users.php" class="nav-link active">Users</a></li>
            <li class="nav-item"><a href="admin_companies.php" class="nav-link">Companies</a></li>
            <li class="nav-item"><a href="admin_applications.php" class="nav-link">Applications</a></li>
        </ul>
        <div class="nav-menu" style="flex-grow: 0; margin-top: auto;">
            <li class="nav-item"><a href="logout_admin.php" class="nav-link">Log out</a></li>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <div style="display: flex; gap: 10px;">
                <button onclick="document.getElementById('addStudentModal').style.display='flex'" style="padding: 10px 20px; background: var(--accent-dark); color: white; border: none; border-radius: var(--radius-md); cursor: pointer; font-weight: 500;">+ Add Student</button>
                <button onclick="document.getElementById('addSupervisorModal').style.display='flex'" style="padding: 10px 20px; background: #4a4a4a; color: white; border: none; border-radius: var(--radius-md); cursor: pointer; font-weight: 500;">+ Add Supervisor</button>
            </div>
        </header>

        <section class="data-section">
            <?php echo $message; ?>
            <h2 class="section-title">System Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Additional Details</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) { 
                            $badgeClass = ($row['Roles'] == 'Admin') ? 'accepted' : 'pending';
                            
                            $custom_id = "";
                            if ($row['Roles'] === 'Student') {
                                $custom_id = sprintf("ST_%05d", $row['UserID']);
                            } elseif ($row['Roles'] === 'Supervisor') {
                                $custom_id = sprintf("SU_%05d", $row['UserID']);
                            } else {
                                $custom_id = sprintf("AD_%05d", $row['UserID']);
                            }
                            
                            $detailsHtml = "";
                            if ($row['Roles'] === 'Student') {
                                $detailsHtml = "<strong>Matrik:</strong> " . htmlspecialchars($row['MatricNumber'] ?? 'N/A') . "<br>";
                                $detailsHtml .= "<strong>CGPA:</strong> " . htmlspecialchars($row['CGPA'] ?? 'N/A') . "<br>";
                                $detailsHtml .= "<strong>Major:</strong> " . htmlspecialchars($row['Major'] ?? 'N/A') . "<br>";
                                $detailsHtml .= "<strong>Contact:</strong> " . htmlspecialchars($row['ContactNumber'] ?? 'N/A');
                            } elseif ($row['Roles'] === 'Supervisor') {
                                $comp_name = $row['CompanyName'] ? htmlspecialchars($row['CompanyName']) : '<span style="color:red; font-style:italic;">No Company Assigned</span>';
                                $detailsHtml = "<strong>Company:</strong> " . $comp_name . "<br>";
                                $detailsHtml .= "<strong>Contact:</strong> " . htmlspecialchars($row['ContactNumber'] ?? 'N/A');
                            } else {
                                $detailsHtml = "<em>System Administrator</em>";
                            }
                    ?>
                    <tr>
                        <td style="font-weight: 600; color: var(--accent-dark);"><?php echo $custom_id; ?></td>
                        <td>
                            <div style="font-weight: 500;"><?php echo htmlspecialchars($row['Name']); ?></div>
                            <div style="font-size: 12px; color: var(--text-secondary);"><?php echo htmlspecialchars($row['Email']); ?></div>
                        </td>
                        <td><span class="status-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($row['Roles']); ?></span></td>
                        <td class="details-cell"><?php echo $detailsHtml; ?></td>
                        <td style="display: flex; gap: 10px; align-items: center;">
                            
                            <button type="button" 
                                class="edit-user-btn"
                                data-userid="<?php echo $row['UserID']; ?>"
                                data-name="<?php echo htmlspecialchars($row['Name']); ?>"
                                data-email="<?php echo htmlspecialchars($row['Email']); ?>"
                                data-role="<?php echo $row['Roles']; ?>"
                                data-matric="<?php echo htmlspecialchars($row['MatricNumber'] ?? ''); ?>"
                                data-cgpa="<?php echo htmlspecialchars($row['CGPA'] ?? ''); ?>"
                                data-major="<?php echo htmlspecialchars($row['Major'] ?? ''); ?>"
                                data-companyid="<?php echo htmlspecialchars($row['CompanyID'] ?? ''); ?>"
                                data-contact="<?php echo htmlspecialchars($row['ContactNumber'] ?? ''); ?>"
                                style="background: #e0e0e0; color: #333; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500;">
                                Edit
                            </button>
                            
                            <form method="POST" action="" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete <?php echo addslashes($row['Name']); ?>?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo $row['UserID']; ?>">
                                <button type="submit" style="background: #ffcdd2; color: #c62828; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500;">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='5'>No users found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>

    <div class="modal-overlay" id="addSupervisorModal">
        <div class="modal-card">
            <h2 style="margin-bottom: 20px;">Add New Supervisor</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_supervisor">
                <div class="form-group"><label>Full Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label>Email Address</label><input type="email" name="email" class="form-control" required></div>
                <div class="form-group"><label>Contact Number</label><input type="text" name="contact_number" class="form-control" placeholder="e.g., +60123456789"></div>
                
                <div class="form-group">
                    <label>Assign to Existing Company <span style="color: #7a7a7a; font-weight: normal; font-size: 12px;">(Optional)</span></label>
                    <select name="company_id" class="form-control">
                        <option value="">-- Unassigned / No Company --</option>
                        <?php foreach($companies as $comp): ?>
                            <?php if(is_null($comp['AssignedSupervisor'])): ?>
                                <option value="<?php echo $comp['CompanyID']; ?>"><?php echo htmlspecialchars($comp['CompanyName']); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group"><label>Temporary Password</label><input type="password" name="password" class="form-control" required></div>
                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="button" onclick="document.getElementById('addSupervisorModal').style.display='none'" style="flex: 1; padding: 10px; border: 1px solid var(--border-color); background: white; border-radius: var(--radius-md); cursor: pointer;">Cancel</button>
                    <button type="submit" style="flex: 1; padding: 10px; border: none; background: #4a4a4a; color: white; border-radius: var(--radius-md); cursor: pointer;">Save Supervisor</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="addStudentModal">
        <div class="modal-card">
            <h2 style="margin-bottom: 20px;">Add New Student</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_student">
                <div class="form-group"><label>Full Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label>Email Address</label><input type="email" name="email" class="form-control" required></div>
                <div class="form-group"><label>Contact Number</label><input type="text" name="contact_number" class="form-control" placeholder="e.g., +60123456789"></div>
                <div class="form-row">
                    <div class="form-group"><label>Matrik No.</label><input type="text" name="matric" class="form-control" required></div>
                    <div class="form-group"><label>CGPA</label><input type="number" step="0.01" name="cgpa" class="form-control"></div>
                </div>
                <div class="form-group"><label>Major</label><input type="text" name="major" class="form-control"></div>
                <div class="form-group"><label>Temporary Password</label><input type="password" name="password" class="form-control" required></div>
                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="button" onclick="document.getElementById('addStudentModal').style.display='none'" style="flex: 1; padding: 10px; border: 1px solid var(--border-color); background: white; border-radius: var(--radius-md); cursor: pointer;">Cancel</button>
                    <button type="submit" style="flex: 1; padding: 10px; border: none; background: var(--accent-dark); color: white; border-radius: var(--radius-md); cursor: pointer;">Save Student</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="editUserModal">
        <div class="modal-card">
            <h2 style="margin-bottom: 20px;">Edit User Details</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" id="edit_user_id">
                <input type="hidden" name="role" id="edit_role">
                
                <div class="form-group"><label>Full Name</label><input type="text" name="name" id="edit_name" class="form-control" required></div>
                <div class="form-group"><label>Email Address</label><input type="email" name="email" id="edit_email" class="form-control" required></div>
                <div class="form-group"><label>Contact Number</label><input type="text" name="contact_number" id="edit_contact_number" class="form-control"></div>
                
                <div id="dynamic_student_fields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group"><label>Matrik No.</label><input type="text" name="matric" id="edit_matric" class="form-control"></div>
                        <div class="form-group"><label>CGPA</label><input type="number" step="0.01" name="cgpa" id="edit_cgpa" class="form-control"></div>
                    </div>
                    <div class="form-group"><label>Major</label><input type="text" name="major" id="edit_major" class="form-control"></div>
                </div>

                <div id="dynamic_supervisor_fields" style="display: none;">
                    <div class="form-group">
                        <label>Assigned Company <span style="color: #7a7a7a; font-weight: normal; font-size: 12px;">(Optional)</span></label>
                        <select name="company_id" id="edit_company_id" class="form-control">
                            <option value="">-- Unassigned / No Company --</option>
                            <?php foreach($companies as $comp): ?>
                                <option value="<?php echo $comp['CompanyID']; ?>" data-assigned="<?php echo $comp['AssignedSupervisor'] ?? ''; ?>">
                                    <?php echo htmlspecialchars($comp['CompanyName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group"><label>New Password <span style="font-weight: normal; color: #7a7a7a; font-size: 12px;">(Leave blank to keep)</span></label><input type="password" name="password" class="form-control"></div>
                
                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="button" onclick="document.getElementById('editUserModal').style.display='none'" style="flex: 1; padding: 10px; border: 1px solid var(--border-color); background: white; border-radius: var(--radius-md); cursor: pointer;">Cancel</button>
                    <button type="submit" style="flex: 1; padding: 10px; border: none; background: var(--accent-dark); color: white; border-radius: var(--radius-md); cursor: pointer;">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            const editButtons = document.querySelectorAll('.edit-user-btn');

            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    
                    const userId = this.getAttribute('data-userid');
                    const name = this.getAttribute('data-name');
                    const email = this.getAttribute('data-email');
                    const role = this.getAttribute('data-role');
                    const matric = this.getAttribute('data-matric');
                    const cgpa = this.getAttribute('data-cgpa');
                    const major = this.getAttribute('data-major');
                    const companyId = this.getAttribute('data-companyid');
                    const contact = this.getAttribute('data-contact');

                    document.getElementById('edit_user_id').value = userId;
                    document.getElementById('edit_name').value = name;
                    document.getElementById('edit_email').value = email;
                    document.getElementById('edit_role').value = role;
                    document.getElementById('edit_contact_number').value = contact;

                    const studentFields = document.getElementById('dynamic_student_fields');
                    const supervisorFields = document.getElementById('dynamic_supervisor_fields');

                    studentFields.style.display = 'none';
                    supervisorFields.style.display = 'none';

                    if (role === 'Student') {
                        studentFields.style.display = 'block';
                        document.getElementById('edit_matric').value = matric;
                        document.getElementById('edit_cgpa').value = cgpa;
                        document.getElementById('edit_major').value = major;
                        
                    } else if (role === 'Supervisor') {
                        supervisorFields.style.display = 'block';
                        
                        // SMART DROPDOWN LOGIC: Disable companies that belong to OTHER supervisors
                        const companySelect = document.getElementById('edit_company_id');
                        Array.from(companySelect.options).forEach(option => {
                            const assignedUser = option.getAttribute('data-assigned');
                            
                            // If it's assigned to someone AND that someone is NOT the user we are currently editing
                            if (assignedUser && assignedUser !== userId && option.value !== "") {
                                option.disabled = true;
                                if (!option.text.includes('(Assigned)')) option.text += ' (Assigned)';
                            } else {
                                option.disabled = false;
                                option.text = option.text.replace(' (Assigned)', '');
                            }
                        });
                        
                        document.getElementById('edit_company_id').value = companyId;
                    }

                    document.getElementById('editUserModal').style.display = 'flex';
                });
            });
        });

        window.onclick = function(event) {
            const addStudentModal = document.getElementById('addStudentModal');
            const addSupervisorModal = document.getElementById('addSupervisorModal');
            const editUserModal = document.getElementById('editUserModal');

            if (event.target == addStudentModal) addStudentModal.style.display = 'none';
            if (event.target == addSupervisorModal) addSupervisorModal.style.display = 'none';
            if (event.target == editUserModal) editUserModal.style.display = 'none';
        };
    </script>
</body>
</html>