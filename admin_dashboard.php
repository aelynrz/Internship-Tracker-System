<?php
// admin_dashboard.php
session_start();

// MOCK SESSION FOR TESTING UI (Remove this when integrating login)
$_SESSION['UserID'] = 1;
$_SESSION['Role'] = 'Admin';
$_SESSION['Name'] = 'Admin User';

// Security Check
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Admin') {
    // header("Location: login.php");
    // exit();
}

// require_once 'db_connect.php'; // Uncomment when ready to fetch real data
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Internship Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
            <li class="nav-item"><a href="#" class="nav-link">Settings</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link">Log out</a></li>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Dashboard</h1>
            <div style="background: white; padding: 10px 20px; border-radius: 20px; font-size: 14px; color: #7a7a7a;">
                Welcome, <?php echo htmlspecialchars($_SESSION['Name']); ?>
            </div>
        </header>

        <section class="kpi-grid">
            <div class="kpi-card dark">
                <div class="kpi-title">Total Applications</div>
                <div class="kpi-value">1,245</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Active Students</div>
                <div class="kpi-value">842</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Partner Companies</div>
                <div class="kpi-value">156</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Pending Approvals</div>
                <div class="kpi-value">34</div>
            </div>
        </section>

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
                    <tr>
                        <td>#APP-1004</td>
                        <td>Aria Montgomery</td>
                        <td>TechNova Solutions</td>
                        <td>24 Sep 2024</td>
                        <td><span class="status-badge accepted">Accepted</span></td>
                    </tr>
                    <tr>
                        <td>#APP-1005</td>
                        <td>James Harrison</td>
                        <td>Global Finance Corp</td>
                        <td>25 Sep 2024</td>
                        <td><span class="status-badge pending">Pending</span></td>
                    </tr>
                    <tr>
                        <td>#APP-1006</td>
                        <td>Maya Lin</td>
                        <td>Creative Studios</td>
                        <td>26 Sep 2024</td>
                        <td><span class="status-badge accepted">Accepted</span></td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>