<?php
// admin_users.php
session_start();
require_once 'db_connect.php';

// Security Check: Must be logged in AND be an Admin
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Admin') {
    header("Location: admin_login.php");
    exit();
}

$message = '';

// Handle Form Submissions (Add, Edit, or Delete)
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
    
    // ACTION: Add New User
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        $stmt = $conn->prepare("INSERT INTO User (Name, Email, Password, Roles) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        
        if ($stmt->execute()) {
            $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>User added successfully.</div>";
        } else {
            $message = "<div style='color: #c62828; background: #ffebee; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Error adding user. Email might already exist.</div>";
        }
        $stmt->close();
    }

    // ACTION: Edit Existing User
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $edit_id = intval($_POST['user_id']);
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $new_password = $_POST['password']; // Can be blank

        if (!empty($new_password)) {
            // If they typed a new password, update it
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE User SET Name=?, Email=?, Password=?, Roles=? WHERE UserID=?");
            $stmt->bind_param("ssssi", $name, $email, $hashed_password, $role, $edit_id);
        } else {
            // If password field is blank, just update the text fields
            $stmt = $conn->prepare("UPDATE User SET Name=?, Email=?, Roles=? WHERE UserID=?");
            $stmt->bind_param("sssi", $name, $email, $role, $edit_id);
        }

        if ($stmt->execute()) {
            $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>User updated successfully.</div>";
        } else {
            $message = "<div style='color: #c62828; background: #ffebee; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Error updating user. Email might already be taken.</div>";
        }
        $stmt->close();
    }
}

// Fetch all users from database
$query = "SELECT UserID, Name, Email, Roles FROM User ORDER BY UserID DESC";
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
        .modal-card { background: white; padding: 30px; border-radius: var(--radius-lg); width: 100%; max-width: 400px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 14px; font-weight: 500; }
        .form-control { width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-md); }
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
            <li class="nav-item"><a href="#" class="nav-link">Settings</a></li>
            <li class="nav-item"><a href="logout_admin.php" class="nav-link">Log out</a></li>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Manage Users</h1>
            <button onclick="openModal()" style="padding: 10px 20px; background: var(--accent-dark); color: white; border: none; border-radius: var(--radius-md); cursor: pointer; font-weight: 500;">+ Add User</button>
        </header>

        <section class="data-section">
            <?php echo $message; ?>
            <h2 class="section-title">System Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) { 
                            $badgeClass = ($row['Roles'] == 'Admin') ? 'accepted' : 'pending';
                    ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($row['UserID']); ?></td>
                        <td><?php echo htmlspecialchars($row['Name']); ?></td>
                        <td><?php echo htmlspecialchars($row['Email']); ?></td>
                        <td><span class="status-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($row['Roles']); ?></span></td>
                        <td style="display: flex; gap: 10px; align-items: center;">
                            
                            <button type="button" 
                                    onclick="openEditModal(<?php echo $row['UserID']; ?>, '<?php echo addslashes($row['Name']); ?>', '<?php echo addslashes($row['Email']); ?>', '<?php echo $row['Roles']; ?>')" 
                                    style="background: #e0e0e0; color: #333; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500;">
                                Edit
                            </button>
                            
                            <form method="POST" action="" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete <?php echo addslashes($row['Name']); ?>?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo $row['UserID']; ?>">
                                <button type="submit" style="background: #ffcdd2; color: #c62828; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500;">
                                    Delete
                                </button>
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

    <div class="modal-overlay" id="addUserModal">
        <div class="modal-card">
            <h2 style="margin-bottom: 20px;">Add New User</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="form-group"><label>Full Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label>Email Address</label><input type="email" name="email" class="form-control" required></div>
                <div class="form-group"><label>Temporary Password</label><input type="password" name="password" class="form-control" required></div>
                <div class="form-group"><label>Role</label>
                    <select name="role" class="form-control" required>
                        <option value="Student">Student</option><option value="Supervisor">Company Supervisor</option><option value="Admin">Administrator</option>
                    </select>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="button" onclick="closeModal()" style="flex: 1; padding: 10px; border: 1px solid var(--border-color); background: white; border-radius: var(--radius-md); cursor: pointer;">Cancel</button>
                    <button type="submit" style="flex: 1; padding: 10px; border: none; background: var(--accent-dark); color: white; border-radius: var(--radius-md); cursor: pointer;">Save User</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="editUserModal">
        <div class="modal-card">
            <h2 style="margin-bottom: 20px;">Edit User</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" id="edit_user_id">
                
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>New Password <span style="font-weight: normal; color: #7a7a7a; font-size: 12px;">(Leave blank to keep current)</span></label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="edit_role" class="form-control" required>
                        <option value="Student">Student</option>
                        <option value="Supervisor">Company Supervisor</option>
                        <option value="Admin">Administrator</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="button" onclick="closeEditModal()" style="flex: 1; padding: 10px; border: 1px solid var(--border-color); background: white; border-radius: var(--radius-md); cursor: pointer;">Cancel</button>
                    <button type="submit" style="flex: 1; padding: 10px; border: none; background: var(--accent-dark); color: white; border-radius: var(--radius-md); cursor: pointer;">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Add Modal Logic
        const addModal = document.getElementById('addUserModal');
        function openModal() { addModal.style.display = 'flex'; }
        function closeModal() { addModal.style.display = 'none'; }

        // Edit Modal Logic
        const editModal = document.getElementById('editUserModal');
        function openEditModal(id, name, email, role) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            editModal.style.display = 'flex';
        }
        function closeEditModal() { editModal.style.display = 'none'; }
        
        // Close modals if clicking outside
        window.onclick = function(event) {
            if (event.target == addModal) { closeModal(); }
            if (event.target == editModal) { closeEditModal(); }
        }
    </script>

</body>
</html>