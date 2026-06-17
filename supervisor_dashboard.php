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

// 1. Fetch Aggregate KPIs across ALL companies managed by this supervisor
$kpi_query = "
    SELECT 
        COUNT(a.ApplicationID) AS TotalApps,
        SUM(CASE WHEN a.Status = 'Pending' THEN 1 ELSE 0 END) AS PendingApps,
        SUM(CASE WHEN a.Status = 'Accepted' THEN 1 ELSE 0 END) AS AcceptedApps,
        SUM(CASE WHEN a.Status = 'Rejected' THEN 1 ELSE 0 END) AS RejectedApps
    FROM Application a
    JOIN Company c ON a.CompanyID = c.CompanyID
    WHERE c.SupervisorID = ?
";
$stmt_kpi = $conn->prepare($kpi_query);
$stmt_kpi->bind_param("i", $supervisor_id);
$stmt_kpi->execute();
$kpi_result = $stmt_kpi->get_result()->fetch_assoc();

$total_apps = $kpi_result['TotalApps'] ?? 0;
$pending_apps = $kpi_result['PendingApps'] ?? 0;
$accepted_apps = $kpi_result['AcceptedApps'] ?? 0;
$rejected_apps = $kpi_result['RejectedApps'] ?? 0;

// 2. Fetch the breakdown per company
$comp_query = "
    SELECT 
        c.CompanyID, 
        c.CompanyName,
        COUNT(a.ApplicationID) AS TotalApps,
        SUM(CASE WHEN a.Status = 'Pending' THEN 1 ELSE 0 END) AS PendingApps
    FROM Company c
    LEFT JOIN Application a ON c.CompanyID = a.CompanyID
    WHERE c.SupervisorID = ?
    GROUP BY c.CompanyID
    ORDER BY c.CompanyName ASC
";
$stmt_comp = $conn->prepare($comp_query);
$stmt_comp->bind_param("i", $supervisor_id);
$stmt_comp->execute();
$companies_managed = $stmt_comp->get_result();

// 3. Fetch recent applications across ALL companies
$app_query = "
    SELECT a.ApplicationID, u.Name AS StudentName, c.CompanyName, a.Status, a.SubmissionDate 
    FROM Application a
    JOIN User u ON a.StudentID = u.UserID
    JOIN Company c ON a.CompanyID = c.CompanyID
    WHERE c.SupervisorID = ?
    ORDER BY a.SubmissionDate DESC LIMIT 5
";
$stmt_app = $conn->prepare($app_query);
$stmt_app->bind_param("i", $supervisor_id);
$stmt_app->execute();
$recent_apps = $stmt_app->get_result();
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
            <h1 class="page-title">Supervisor Dashboard</h1>
            <div style="background: white; padding: 10px 20px; border-radius: 20px; font-size: 14px; color: #7a7a7a; border: 1px solid var(--border-color);">
                Supervisor: <?php echo htmlspecialchars($_SESSION['Name']); ?>
            </div>
        </header>

        <?php if ($companies_managed->num_rows === 0): ?>
            <div style="background: #fff3e0; color: #e65100; padding: 20px; border-radius: 12px; margin-bottom: 30px; border: 1px solid #ffe0b2;">
                <strong>Notice:</strong> You have not been assigned to any companies yet. Please contact the System Administrator.
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

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
                
                <section class="data-section" style="align-self: start;">
                    <h2 class="section-title">Companies Managed</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Pending</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($comp = $companies_managed->fetch_assoc()) { ?>
                            <tr>
                                <td style="font-weight: 500;"><?php echo htmlspecialchars($comp['CompanyName']); ?></td>
                                <td>
                                    <?php if($comp['PendingApps'] > 0): ?>
                                        <span style="background: #fff3e0; color: #e65100; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                            <?php echo $comp['PendingApps']; ?> Action Required
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--text-secondary); font-size: 13px;">0</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </section>

                <section class="data-section" style="align-self: start;">
                    <h2 class="section-title">Recent Submissions</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>App ID</th>
                                <th>Student Name</th>
                                <th>Target Company</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($recent_apps && $recent_apps->num_rows > 0) {
                                while($row = $recent_apps->fetch_assoc()) { 
                                    $statusClass = strtolower($row['Status']);
                            ?>
                            <tr>
                                <td>#APP-<?php echo htmlspecialchars($row['ApplicationID']); ?></td>
                                <td style="font-weight: 500;"><?php echo htmlspecialchars($row['StudentName']); ?></td>
                                <td><?php echo htmlspecialchars($row['CompanyName']); ?></td>
                                <td><?php echo date('d M', strtotime($row['SubmissionDate'])); ?></td>
                                <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['Status']); ?></span></td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='5'>No applications have been submitted yet.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </section>

            </div>

        <?php endif; ?>

    </main>

</body>
</html>