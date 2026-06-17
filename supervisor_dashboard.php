<?php
// supervisor_dashboard.php
session_start();
require_once 'db_connect.php';

// Security Check
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Supervisor') {
    header("Location: login.php");
    exit();
}

$supervisor_id = $_SESSION['UserID'];

// 1. Find the company assigned to this supervisor via the User table
$comp_query = "
    SELECT u.CompanyID, c.CompanyName 
    FROM User u 
    LEFT JOIN Company c ON u.CompanyID = c.CompanyID 
    WHERE u.UserID = ?
";
$stmt = $conn->prepare($comp_query);
$stmt->bind_param("i", $supervisor_id);
$stmt->execute();
$comp_result = $stmt->get_result();
$company = $comp_result->fetch_assoc();

$company_id = $company['CompanyID'] ?? null;
$company_name = $company['CompanyName'] ?? "Unassigned";

// 2. Initialize stats
$total_apps = 0;
$pending_apps = 0;
$accepted_apps = 0;
$rejected_apps = 0;
$recent_apps = null;

if ($company_id) {
    // 3. Fetch KPI Statistics for this specific company
    $stats_query = "
        SELECT 
            COUNT(ApplicationID) AS TotalApps,
            SUM(CASE WHEN Status = 'Pending' THEN 1 ELSE 0 END) AS PendingApps,
            SUM(CASE WHEN Status = 'Accepted' THEN 1 ELSE 0 END) AS AcceptedApps,
            SUM(CASE WHEN Status = 'Rejected' THEN 1 ELSE 0 END) AS RejectedApps
        FROM Application 
        WHERE CompanyID = ?
    ";
    $stmt_stats = $conn->prepare($stats_query);
    $stmt_stats->bind_param("i", $company_id);
    $stmt_stats->execute();
    $stats_result = $stmt_stats->get_result()->fetch_assoc();
    
    $total_apps = $stats_result['TotalApps'] ?? 0;
    $pending_apps = $stats_result['PendingApps'] ?? 0;
    $accepted_apps = $stats_result['AcceptedApps'] ?? 0;
    $rejected_apps = $stats_result['RejectedApps'] ?? 0;

    // 4. Fetch recent applications
    $app_query = "
        SELECT a.ApplicationID, u.Name AS StudentName, a.Status, a.SubmissionDate 
        FROM Application a
        JOIN User u ON a.StudentID = u.UserID
        WHERE a.CompanyID = ?
        ORDER BY a.SubmissionDate DESC LIMIT 5
    ";
    $stmt_app = $conn->prepare($app_query);
    $stmt_app->bind_param("i", $company_id);
    $stmt_app->execute();
    $recent_apps = $stmt_app->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supervisor Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <aside class="sidebar">
        <div class="brand">InternTrack</div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="supervisor_dashboard.php" class="nav-link active">Company Overview</a></li>
            <li class="nav-item"><a href="supervisor_applications.php" class="nav-link">Manage Applications</a></li>
            <li class="nav-item"><a href="supervisor_profile.php" class="nav-link">My Profile</a></li>
        </ul>
        <div class="nav-menu" style="flex-grow: 0; margin-top: auto;">
            <li class="nav-item"><a href="logout.php" class="nav-link">Log out</a></li>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title"><?php echo htmlspecialchars($company_name); ?> Dashboard</h1>
            <div style="background: white; padding: 10px 20px; border-radius: 20px; font-size: 14px; color: #7a7a7a; border: 1px solid var(--border-color);">
                Supervisor: <?php echo htmlspecialchars($_SESSION['Name']); ?>
            </div>
        </header>

        <?php if (!$company_id): ?>
            <div style="background: #fff3e0; color: #e65100; padding: 20px; border-radius: 12px; margin-bottom: 30px; border: 1px solid #ffe0b2;">
                <strong>Notice:</strong> You have not been assigned to a company yet. Please contact the System Administrator.
            </div>
        <?php else: ?>

            <section class="kpi-grid" style="margin-bottom: 40px;">
                <div class="kpi-card dark">
                    <div class="kpi-title" style="color: #a0a0a0;">Total Applicants</div>
                    <div class="kpi-value" style="color: white;"><?php echo $total_apps; ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title" style="color: #ff9800; font-weight: 600;">Pending Review</div>
                    <div class="kpi-value"><?php echo $pending_apps; ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title" style="color: #2e7d32; font-weight: 600;">Accepted</div>
                    <div class="kpi-value"><?php echo $accepted_apps; ?></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title" style="color: #c62828; font-weight: 600;">Rejected</div>
                    <div class="kpi-value"><?php echo $rejected_apps; ?></div>
                </div>
            </section>

            <section class="data-section">
                <h2 class="section-title">Recent Submissions</h2>
                <table>
                    <thead>
                        <tr>
                            <th>App ID</th>
                            <th>Student Name</th>
                            <th>Date Applied</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($recent_apps && $recent_apps->num_rows > 0) {
                            while($row = $recent_apps->fetch_assoc()) { 
                                $statusClass = strtolower($row['Status']);
                                $custom_app_id = sprintf("APP_%05d", $row['ApplicationID']);
                        ?>
                        <tr>
                            <td style="font-weight: 600; color: var(--accent-dark);"><?php echo $custom_app_id; ?></td>
                            <td style="font-weight: 500;"><?php echo htmlspecialchars($row['StudentName']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['SubmissionDate'])); ?></td>
                            <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['Status']); ?></span></td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='4'>No applications have been submitted to your company yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>

        <?php endif; ?>

    </main>

</body>
</html>