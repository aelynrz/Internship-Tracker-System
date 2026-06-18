<?php
// admin_dashboard.php
session_start();
require_once 'db_connect.php';

// Security Check: Must be logged in AND be an Admin
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Admin') {
    header("Location: admin_login.php");
    exit();
}

// Fetch statistics from the database
$total_apps = $conn->query("SELECT COUNT(ApplicationID) FROM Application")->fetch_row()[0];
$active_students = $conn->query("SELECT COUNT(UserID) FROM User WHERE Roles = 'Student'")->fetch_row()[0];
$partner_companies = $conn->query("SELECT COUNT(CompanyID) FROM Company")->fetch_row()[0];
$pending_approvals = $conn->query("SELECT COUNT(ApplicationID) FROM Application WHERE Status = 'Pending'")->fetch_row()[0];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - InternTrack</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin_dashboard.css">
    <!-- Added Google Fonts for a fancy typography look -->
    <link rel="preconnect" href="https://googleapis.com">
    <link rel="preconnect" href="https://gstatic.com" crossorigin>
    <link href="https://googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="family">
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
            <li class="nav-item"><a href="logout_admin.php" class="nav-link">Log out</a></li>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title"></h1>
        </header>

        <section class="welcome-hero">
            <div class="welcome-card">
                <h2 class="welcome-text">
                    Welcome back,<br>
                    <?php echo htmlspecialchars($_SESSION['Name']); ?>!
                </h2>
            </div>
        </section>

        <section class="dashboard-grid">
            <a href="admin_users.php" class="dashboard-card">
                <h3>Manage Users</h3>
                <p><?php echo $active_students; ?> Students</p>
            </a>

            <a href="admin_companies.php" class="dashboard-card">
                <h3>Companies</h3>
                <p><?php echo $partner_companies; ?> Registered</p>
            </a>

            <a href="admin_applications.php" class="dashboard-card">
                <h3>Applications</h3>
                <p><?php echo $total_apps; ?> Submitted</p>
            </a>

            <a href="#" class="dashboard-card">
                <h3>Reports</h3>
                <p><?php echo $pending_approvals; ?> Pending</p>
            </a>

        </section>
    </main>

</body>
</html>
