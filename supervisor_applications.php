<?php
// supervisor_applications.php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Supervisor') {
    header("Location: login.php");
    exit();
}

$supervisor_id = $_SESSION['UserID'];
$message = '';

// Handle Application Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_id'], $_POST['new_status'])) {
    $app_id = intval($_POST['app_id']);
    $new_status = $_POST['new_status'];
    
    // Security check: Update status by joining the Company table to ensure this 
    // supervisor actually owns the company this application is for.
    $update_query = "
        UPDATE Application a
        JOIN Company c ON a.CompanyID = c.CompanyID
        SET a.Status = ? 
        WHERE a.ApplicationID = ? AND c.SupervisorID = ?
    ";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sii", $new_status, $app_id, $supervisor_id);
    
    if ($update_stmt->execute()) {
        $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Application #$app_id updated to $new_status.</div>";
    }
    $update_stmt->close();
}

// Fetch all applications for ALL companies managed by this supervisor
// Now grabbing CGPA and Major from the User table!
$query = "
    SELECT a.ApplicationID, u.Name AS StudentName, u.Email, u.CGPA, u.Major, c.CompanyName, a.Status, a.SubmissionDate 
    FROM Application a
    JOIN User u ON a.StudentID = u.UserID
    JOIN Company c ON a.CompanyID = c.CompanyID
    WHERE c.SupervisorID = ?
    ORDER BY a.SubmissionDate DESC
";
$stmt_apps = $conn->prepare($query);
$stmt_apps->bind_param("i", $supervisor_id);
$stmt_apps->execute();
$apps = $stmt_apps->get_result();
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
            <li class="nav-item"><a href="supervisor_profile.php" class="nav-link">My Profile</a></li>
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
            <h2 class="section-title">Review Student Applications</h2>
            <table>
                <thead>
                    <tr>
                        <th>Candidate Profile</th>
                        <th>Target Company</th>
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
                        <td>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($row['StudentName']); ?></div>
                            <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">
                                <?php echo htmlspecialchars($row['Major'] ?? 'Undeclared Major'); ?> | CGPA: <strong><?php echo htmlspecialchars($row['CGPA'] ?? 'N/A'); ?></strong>
                            </div>
                            <div style="font-size: 12px; color: var(--text-secondary);"><?php echo htmlspecialchars($row['Email']); ?></div>
                        </td>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($row['CompanyName']); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['SubmissionDate'])); ?></td>
                        <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['Status']); ?></span></td>
                        <td>
                            <form method="POST" style="display: flex; gap: 10px; align-items: center; margin: 0;">
                                <input type="hidden" name="app_id" value="<?php echo $row['ApplicationID']; ?>">
                                <select name="new_status" style="padding: 6px; border-radius: 6px; border: 1px solid var(--border-color); outline: none;">
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