<?php
// supervisor_applications.php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Supervisor') {
    header("Location: login.php");
    exit();
}

$supervisor_id = $_SESSION['UserID'];

// Get company ID
$stmt = $conn->prepare("SELECT CompanyID FROM Company WHERE SupervisorID = ?");
$stmt->bind_param("i", $supervisor_id);
$stmt->execute();
$comp_result = $stmt->get_result();
$company = $comp_result->fetch_assoc();
$company_id = $company ? $company['CompanyID'] : 0;

$message = '';

// Handle Application Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_id'], $_POST['new_status'])) {
    $app_id = intval($_POST['app_id']);
    $new_status = $_POST['new_status'];
    
    // Ensure the supervisor is only updating applications for their own company
    $update_stmt = $conn->prepare("UPDATE Application SET Status = ? WHERE ApplicationID = ? AND CompanyID = ?");
    $update_stmt->bind_param("sii", $new_status, $app_id, $company_id);
    
    if ($update_stmt->execute()) {
        $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Application #$app_id updated to $new_status.</div>";
    }
    $update_stmt->close();
}

// Fetch all applications
$apps = null;
if ($company_id) {
    $query = "
        SELECT a.ApplicationID, u.Name AS StudentName, u.Email, a.Status, a.SubmissionDate 
        FROM Application a
        JOIN User u ON a.StudentID = u.UserID
        WHERE a.CompanyID = ?
        ORDER BY a.SubmissionDate DESC
    ";
    $stmt_apps = $conn->prepare($query);
    $stmt_apps->bind_param("i", $company_id);
    $stmt_apps->execute();
    $apps = $stmt_apps->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Candidates</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <aside class="sidebar">
        <div class="brand">InternTrack</div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="supervisor_dashboard.php" class="nav-link">Company Overview</a></li>
            <li class="nav-item"><a href="supervisor_applications.php" class="nav-link active">Manage Applications</a></li>
            <li class="nav-item"><a href="supervisor_profile.php" class="nav-link">Company Profile</a></li>
        </ul>
        <div class="nav-menu" style="flex-grow: 0; margin-top: auto;">
            <li class="nav-item"><a href="logout.php" class="nav-link">Log out</a></li>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Candidate Pipeline</h1>
        </header>

        <section class="data-section">
            <?php echo $message; ?>
            <h2 class="section-title">Review Applications</h2>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Contact Email</th>
                        <th>Date Applied</th>
                        <th>Current Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($apps && $apps->num_rows > 0) {
                        while($row = $apps->fetch_assoc()) { 
                            $statusClass = strtolower($row['Status']);
                    ?>
                    <tr>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($row['StudentName']); ?></td>
                        <td><?php echo htmlspecialchars($row['Email']); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['SubmissionDate'])); ?></td>
                        <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['Status']); ?></span></td>
                        <td>
                            <form method="POST" style="display: flex; gap: 10px; align-items: center;">
                                <input type="hidden" name="app_id" value="<?php echo $row['ApplicationID']; ?>">
                                <select name="new_status" style="padding: 6px; border-radius: 6px; border: 1px solid var(--border-color);">
                                    <option value="Pending" <?php if($row['Status']=='Pending') echo 'selected'; ?>>Pending</option>
                                    <option value="Accepted" <?php if($row['Status']=='Accepted') echo 'selected'; ?>>Accept</option>
                                    <option value="Rejected" <?php if($row['Status']=='Rejected') echo 'selected'; ?>>Reject</option>
                                </select>
                                <button type="submit" style="background: var(--accent-dark); color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer;">Save</button>
                            </form>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='5'>No applications to review at this time.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>