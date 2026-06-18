<?php
// admin_dashboard.php
session_start();
require_once 'db_connect.php';

// Security Check: Must be logged in AND be an Admin
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Admin') {
    header("Location: admin_login.php");
    exit();
}

// 1. Fetch KPI Statistics from the database
$total_apps = $conn->query("SELECT COUNT(ApplicationID) FROM Application")->fetch_row()[0];
$active_students = $conn->query("SELECT COUNT(UserID) FROM User WHERE Roles = 'Student'")->fetch_row()[0];
$partner_companies = $conn->query("SELECT COUNT(CompanyID) FROM Company")->fetch_row()[0];
$pending_approvals = $conn->query("SELECT COUNT(ApplicationID) FROM Application WHERE Status = 'Pending'")->fetch_row()[0];

// 2. Fetch the 5 most recent applications for the table
$recent_query = "
    SELECT a.ApplicationID, u.Name AS StudentName, c.CompanyName, a.SubmissionDate, a.Status
    FROM Application a
    JOIN User u ON a.StudentID = u.UserID
    JOIN Company c ON a.CompanyID = c.CompanyID
    ORDER BY a.SubmissionDate DESC
    LIMIT 5
";
$recent_apps = $conn->query($recent_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - InternTrack</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Specific Dashboard Layout Styles */
        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .kpi-card { background: white; padding: 25px; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .kpi-card.dark { background: #1a1a1a; color: white; border: none; }
        .kpi-title { font-size: 14px; font-weight: 500; margin-bottom: 15px; color: var(--text-secondary); }
        .kpi-card.dark .kpi-title { color: #a0a0a0; }
        .kpi-value { font-size: 36px; font-weight: 700; }
        .kpi-card.dark .kpi-value { color: white; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand">InternTrack</div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="admin_dashboard.php" class="nav-link active">Dashboard</a></li>
            <li class="nav-item"><a href="admin_users.php" class="nav-link">Users</a></li>
            <li class="nav-item"><a href="admin_companies.php" class="nav-link">Companies</a></li>
            <li class="nav-item"><a href="admin_applications.php" class="nav-link">Applications</a></li>
        </ul>
        <div class="nav-menu" style="flex-grow: 0; margin-top: auto;">
            <!-- Added a Settings link to match your screenshot -->
            <li class="nav-item"><a href="#" class="nav-link">Settings</a></li> 
            <li class="nav-item"><a href="logout_admin.php" class="nav-link">Log out</a></li>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Dashboard</h1>
            <div style="background: white; padding: 10px 20px; border-radius: 20px; font-size: 14px; color: var(--text-secondary); border: 1px solid var(--border-color);">
                Welcome, <?php echo htmlspecialchars($_SESSION['Name']); ?>
            </div>
        </header>

        <!-- Dynamic KPI Stats Section -->
        <section class="kpi-grid">
            <div class="kpi-card dark">
                <div class="kpi-title">Total Applications</div>
                <div class="kpi-value"><?php echo number_format($total_apps); ?></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Active Students</div>
                <div class="kpi-value"><?php echo number_format($active_students); ?></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Partner Companies</div>
                <div class="kpi-value"><?php echo number_format($partner_companies); ?></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Pending Approvals</div>
                <div class="kpi-value"><?php echo number_format($pending_approvals); ?></div>
            </div>
        </section>

        <!-- Dynamic Recent Applications Table -->
        <section class="data-section">
            <h2 class="section-title">Recent Internship Applications</h2>
            <table>
                <thead>
                    <tr>
                        <th>Application ID</th>
                        <th>Student Name</th>
                        <th>Target Company</th>
                        <th>Date Submitted</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($recent_apps && $recent_apps->num_rows > 0) {
                        while($row = $recent_apps->fetch_assoc()) { 
                            // Convert the status text to a lowercase CSS class (e.g., 'pending', 'accepted')
                            $statusClass = strtolower($row['Status']);
                            
                            // Format the custom ID
                            $custom_app_id = sprintf("#APP-%04d", $row['ApplicationID']);
                            
                            // Format the date to match your screenshot (e.g., "24 Sep 2024")
                            $formatted_date = date('d M Y', strtotime($row['SubmissionDate']));
                    ?>
                    <tr>
                        <td style="color: #4a4a4a;"><?php echo $custom_app_id; ?></td>
                        <td style="font-weight: 500; color: #1e1e1e;"><?php echo htmlspecialchars($row['StudentName']); ?></td>
                        <td style="color: #4a4a4a;"><?php echo htmlspecialchars($row['CompanyName']); ?></td>
                        <td style="color: #4a4a4a;"><?php echo $formatted_date; ?></td>
                        <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['Status']); ?></span></td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align: center; padding: 20px; color: #7a7a7a;'>No applications have been submitted yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>