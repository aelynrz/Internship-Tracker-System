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

// Handle Form Submissions (Add, Edit, or Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ACTION: Delete Company
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $delete_id = intval($_POST['company_id']);
        $stmt = $conn->prepare("DELETE FROM Company WHERE CompanyID = ?");
        $stmt->bind_param("i", $delete_id);
        
        if ($stmt->execute()) {
            $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Company deleted successfully.</div>";
        } else {
            $message = "<div style='color: #c62828; background: #ffebee; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Error deleting company.</div>";
        }
        $stmt->close();
    }
    
    // ACTION: Add New Company
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = trim($_POST['company_name']);
        $industry = trim($_POST['industry']);
        // If empty string, set to NULL for the database
        $supervisor_id = !empty($_POST['supervisor_id']) ? intval($_POST['supervisor_id']) : NULL;

        $stmt = $conn->prepare("INSERT INTO Company (CompanyName, Industry, SupervisorID) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $industry, $supervisor_id);
        
        if ($stmt->execute()) {
            $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Company added successfully.</div>";
        } else {
            $message = "<div style='color: #c62828; background: #ffebee; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Error adding company.</div>";
        }
        $stmt->close();
    }

    // ACTION: Edit Existing Company
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $edit_id = intval($_POST['company_id']);
        $name = trim($_POST['company_name']);
        $industry = trim($_POST['industry']);
        $supervisor_id = !empty($_POST['supervisor_id']) ? intval($_POST['supervisor_id']) : NULL;

        $stmt = $conn->prepare("UPDATE Company SET CompanyName=?, Industry=?, SupervisorID=? WHERE CompanyID=?");
        $stmt->bind_param("ssii", $name, $industry, $supervisor_id, $edit_id);

        if ($stmt->execute()) {
            $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Company updated successfully.</div>";
        } else {
            $message = "<div style='color: #c62828; background: #ffebee; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Error updating company.</div>";
        }
        $stmt->close();
    }
}

// Fetch all Supervisors for the Add/Edit dropdowns
$supervisors = [];
$sup_query = "SELECT UserID, Name FROM User WHERE Roles = 'Supervisor' ORDER BY Name ASC";
$sup_result = $conn->query($sup_query);
if ($sup_result->num_rows > 0) {
    while($s = $sup_result->fetch_assoc()) {
        $supervisors[] = $s;
    }
}

// Fetch companies and their assigned supervisor's name
$query = "
    SELECT c.CompanyID, c.CompanyName, c.Industry, c.SupervisorID, u.Name AS SupervisorName 
    FROM Company c
    LEFT JOIN User u ON c.SupervisorID = u.UserID
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
            <li class="nav-item"><a href="admin_users.php" class="nav-link">Users</a></li>
            <li class="nav-item"><a href="admin_companies.php" class="nav-link active">Companies</a></li>
            <li class="nav-item"><a href="admin_applications.php" class="nav-link">Applications</a></li>
        </ul>
        <div class="nav-menu" style="flex-grow: 0; margin-top: auto;">
            <li class="nav-item"><a href="#" class="nav-link">Settings</a></li>
            <li class="nav-item"><a href="logout_admin.php" class="nav-link">Log out</a></li>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Manage Companies</h1>
            <button onclick="openModal()" style="padding: 10px 20px; background: var(--accent-dark); color: white; border: none; border-radius: var(--radius-md); cursor: pointer; font-weight: 500;">+ Add Company</button>
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
                        <td>#COMP-<?php echo htmlspecialchars($row['CompanyID']); ?></td>
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
                                    onclick="openEditModal(<?php echo $row['CompanyID']; ?>, '<?php echo addslashes($row['CompanyName']); ?>', '<?php echo addslashes($row['Industry']); ?>', '<?php echo $sup_id; ?>')" 
                                    style="background: #e0e0e0; color: #333; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500;">
                                Edit
                            </button>
                            
                            <form method="POST" action="" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete <?php echo addslashes($row['CompanyName']); ?>? This will also delete any related student applications.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="company_id" value="<?php echo $row['CompanyID']; ?>">
                                <button type="submit" style="background: #ffcdd2; color: #c62828; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500;">
                                    Delete
                                </button>
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
            <h2 style="margin-bottom: 20px;">Add New Company</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="company_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Industry</label>
                    <input type="text" name="industry" class="form-control" placeholder="e.g., Tech, Finance">
                </div>
                <div class="form-group">
                    <label>Assign Supervisor (Optional)</label>
                    <select name="supervisor_id" class="form-control">
                        <option value="">-- None --</option>
                        <?php foreach($supervisors as $sup): ?>
                            <option value="<?php echo $sup['UserID']; ?>"><?php echo htmlspecialchars($sup['Name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="button" onclick="closeModal()" style="flex: 1; padding: 10px; border: 1px solid var(--border-color); background: white; border-radius: var(--radius-md); cursor: pointer;">Cancel</button>
                    <button type="submit" style="flex: 1; padding: 10px; border: none; background: var(--accent-dark); color: white; border-radius: var(--radius-md); cursor: pointer;">Save Company</button>
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
                
                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="company_name" id="edit_company_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Industry</label>
                    <input type="text" name="industry" id="edit_industry" class="form-control">
                </div>
                <div class="form-group">
                    <label>Assign Supervisor</label>
                    <select name="supervisor_id" id="edit_supervisor_id" class="form-control">
                        <option value="">-- None --</option>
                        <?php foreach($supervisors as $sup): ?>
                            <option value="<?php echo $sup['UserID']; ?>"><?php echo htmlspecialchars($sup['Name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="button" onclick="closeEditModal()" style="flex: 1; padding: 10px; border: 1px solid var(--border-color); background: white; border-radius: var(--radius-md); cursor: pointer;">Cancel</button>
                    <button type="submit" style="flex: 1; padding: 10px; border: none; background: var(--accent-dark); color: white; border-radius: var(--radius-md); cursor: pointer;">Update Company</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Add Modal Logic
        const addModal = document.getElementById('addCompanyModal');
        function openModal() { addModal.style.display = 'flex'; }
        function closeModal() { addModal.style.display = 'none'; }

        // Edit Modal Logic
        const editModal = document.getElementById('editCompanyModal');
        function openEditModal(id, name, industry, sup_id) {
            document.getElementById('edit_company_id').value = id;
            document.getElementById('edit_company_name').value = name;
            document.getElementById('edit_industry').value = industry;
            document.getElementById('edit_supervisor_id').value = sup_id;
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