<?php
// admin_users.php
session_start();
require_once 'db_connect.php';

// Security Check: Must be logged in AND be an Admin
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$message = '';

// Handle Form Submissions
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
            } else {
                $message = "<div style='color: #c62828; background: #ffebee; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Error deleting user.</div>";
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

        $stmt = $conn->prepare("INSERT INTO User (Name, Email, Password, Roles, MatricNumber, CGPA, Major) VALUES (?, ?, ?, 'Student', ?, ?, ?)");
        $stmt->bind_param("ssssds", $name, $email, $password, $matric, $cgpa, $major);
        
        if ($stmt->execute()) {
            $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Student added successfully.</div>";
        } else {
            $message = "<div style='color: #c62828; background: #ffebee; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Error adding student. Email or Matric Number might already exist.</div>";
        }
        $stmt->close();
    }

    // ACTION: Add Supervisor
    if (isset($_POST['action']) && $_POST['action'] === 'add_supervisor') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $dept = trim($_POST['department']);

        $stmt = $conn->prepare("INSERT INTO User (Name, Email, Password, Roles, Department) VALUES (?, ?, ?, 'Supervisor', ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $dept);
        
        if ($stmt->execute()) {
            $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Supervisor added successfully.</div>";
        } else {
            $message = "<div style='color: #c62828; background: #ffebee; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Error adding supervisor. Email might already exist.</div>";
        }
        $stmt->close();
    }

    // ACTION: Edit Existing User
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $edit_id = intval($_POST['user_id']);
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $role = $_POST['role']; // Readonly in form, passed as hidden or readonly
        $new_password = $_POST['password'];

        // Determine extra fields based on role
        $matric = ($role === 'Student') ? trim($_POST['matric']) : NULL;
        $cgpa = ($role === 'Student') ? floatval($_POST['cgpa']) : NULL;
        $major = ($role === 'Student') ? trim($_POST['major']) : NULL;
        $dept = ($role === 'Supervisor') ? trim($_POST['department']) : NULL;

        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE User SET Name=?, Email=?, Password=?, MatricNumber=?, CGPA=?, Major=?, Department=? WHERE UserID=?");
            $stmt->bind_param("ssssdssi", $name, $email, $hashed_password, $matric, $cgpa, $major, $dept, $edit_id);
        } else {
            $stmt = $conn->prepare("UPDATE User SET Name=?, Email=?, MatricNumber=?, CGPA=?, Major=?, Department=? WHERE UserID=?");
            $stmt->bind_param("sssdssi", $name, $email, $matric, $cgpa, $major, $dept, $edit_id);
        }

        if ($stmt->execute()) {
            $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>User updated successfully.</div>";
        } else {
            $message = "<div style='color: #c62828; background: #ffebee; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Error updating user.</div>";
        }
        $stmt->close();
    }
}

// Fetch all users from database including new fields
$query = "SELECT UserID, Name, Email, Roles, MatricNumber, CGPA, Major, Department FROM User ORDER BY UserID DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-card { background: white; padding: 30px; border-radius: var(--radius-lg); width: 100%; max-width: 450px; max-height: 90vh; overflow-y: auto; }
        .form-row { display: flex; gap: 15px; }
        .form-row .form-group { flex: 1; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 14px; font-weight: 500; }
        .form-control { width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-md); }
        .details-cell { font-size: 13px; color: var(--text-secondary); line-height: 1.4; }
        .details-cell strong { color: var(--text-primary); }
    </style>
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
            <h1 class="page-title">Manage Users</h1>
            
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
                            
                            // Format the Details column based on the role
                            $detailsHtml = "";
                            if ($row['Roles'] === 'Student') {
                                $detailsHtml = "<strong>Matrik:</strong> " . htmlspecialchars($row['MatricNumber'] ?? 'N/A') . "<br>";
                                $detailsHtml .= "<strong>CGPA:</strong> " . htmlspecialchars($row['CGPA'] ?? 'N/A') . "<br>";
                                $detailsHtml .= "<strong>Major:</strong> " . htmlspecialchars($row['Major'] ?? 'N/A');
                            } elseif ($row['Roles'] === 'Supervisor') {
                                $detailsHtml = "<strong>Department:</strong> " . htmlspecialchars($row['Department'] ?? 'N/A');
                            } else {
                                $detailsHtml = "<em>System Administrator</em>";
                            }
                    ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($row['UserID']); ?></td>
                        <td>
                            <div style="font-weight: 500;"><?php echo htmlspecialchars($row['Name']); ?></div>
                            <div style="font-size: 12px; color: var(--text-secondary);"><?php echo htmlspecialchars($row['Email']); ?></div>
                        </td>
                        <td><span class="status-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($row['Roles']); ?></span></td>
                        <td class="details-cell"><?php echo $detailsHtml; ?></td>
                        <td style="display: flex; gap: 10px; align-items: center;">
                            
                            <button type="button" 
                                    onclick="openEditModal(
                                        <?php echo $row['UserID']; ?>, 
                                        '<?php echo addslashes($row['Name']); ?>', 
                                        '<?php echo addslashes($row['Email']); ?>', 
                                        '<?php echo $row['Roles']; ?>',
                                        '<?php echo addslashes($row['MatricNumber'] ?? ''); ?>',
                                        '<?php echo $row['CGPA'] ?? ''; ?>',
                                        '<?php echo addslashes($row['Major'] ?? ''); ?>',
                                        '<?php echo addslashes($row['Department'] ?? ''); ?>'
                                    )" 
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

    <div class="modal-overlay" id="addStudentModal">
        <div class="modal-card">
            <h2 style="margin-bottom: 20px;">Add New Student</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_student">
                <div class="form-group"><label>Full Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label>Email Address</label><input type="email" name="email" class="form-control" required></div>
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

    <div class="modal-overlay" id="addSupervisorModal">
        <div class="modal-card">
            <h2 style="margin-bottom: 20px;">Add New Supervisor</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_supervisor">
                <div class="form-group"><label>Full Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label>Email Address</label><input type="email" name="email" class="form-control" required></div>
                <div class="form-group"><label>Department</label><input type="text" name="department" class="form-control" placeholder="e.g., HR, Engineering"></div>
                <div class="form-group"><label>Temporary Password</label><input type="password" name="password" class="form-control" required></div>
                
                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="button" onclick="document.getElementById('addSupervisorModal').style.display='none'" style="flex: 1; padding: 10px; border: 1px solid var(--border-color); background: white; border-radius: var(--radius-md); cursor: pointer;">Cancel</button>
                    <button type="submit" style="flex: 1; padding: 10px; border: none; background: #4a4a4a; color: white; border-radius: var(--radius-md); cursor: pointer;">Save Supervisor</button>
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
                
                <div id="dynamic_student_fields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group"><label>Matrik No.</label><input type="text" name="matric" id="edit_matric" class="form-control"></div>
                        <div class="form-group"><label>CGPA</label><input type="number" step="0.01" name="cgpa" id="edit_cgpa" class="form-control"></div>
                    </div>
                    <div class="form-group"><label>Major</label><input type="text" name="major" id="edit_major" class="form-control"></div>
                </div>

                <div id="dynamic_supervisor_fields" style="display: none;">
                    <div class="form-group"><label>Department</label><input type="text" name="department" id="edit_department" class="form-control"></div>
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
        // Intelligent Edit Modal: Shows only the fields relevant to the user's role
        function openEditModal(id, name, email, role, matric, cgpa, major, dept) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            
            // Hide both blocks first
            document.getElementById('dynamic_student_fields').style.display = 'none';
            document.getElementById('dynamic_supervisor_fields').style.display = 'none';

            // Show relevant fields and populate data based on Role
            if (role === 'Student') {
                document.getElementById('dynamic_student_fields').style.display = 'block';
                document.getElementById('edit_matric').value = matric;
                document.getElementById('edit_cgpa').value = cgpa;
                document.getElementById('edit_major').value = major;
            } else if (role === 'Supervisor') {
                document.getElementById('dynamic_supervisor_fields').style.display = 'block';
                document.getElementById('edit_department').value = dept;
            }

            document.getElementById('editUserModal').style.display = 'flex';
        }

        // Close modals when clicking outside the white card
        window.onclick = function(event) {
            if (event.target == document.getElementById('addStudentModal')) document.getElementById('addStudentModal').style.display = 'none';
            if (event.target == document.getElementById('addSupervisorModal')) document.getElementById('addSupervisorModal').style.display = 'none';
            if (event.target == document.getElementById('editUserModal')) document.getElementById('editUserModal').style.display = 'none';
        }
    </script>
</body>
</html>