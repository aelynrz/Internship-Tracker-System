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
    <link rel="stylesheet" href="assets/css/admin_dashboard.css">
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
        <ul class="nav-menu-bottom">
            <li><a href="logout_admin.php" class="nav-link">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <section class="welcome-hero">
            <div class="welcome-card">
                <h2 class="welcome-text">
                    Welcome back,<br>
                    <?php echo htmlspecialchars($_SESSION['Name']); ?>!
                </h2>
            </div>
        </section>

        <section class="dashboard-grid">
            <a href="admin_users.php" class="dashboard-card"><div>
                <h3><?php echo $active_students; ?></h3>
                <p>Total Students</p>
            </div></a>

            <a href="admin_companies.php" class="dashboard-card"><div>
                <h3><?php echo $partner_companies; ?></h3>
                <p>Partner Companies</p>
            </div></a>

            <a href="admin_applications.php" class="dashboard-card"><div>
                <h3><?php echo $total_apps; ?></h3>
                <p>Total Applications</p>
            </div></a>

            <a href="admin_applications.php" class="dashboard-card"><div>
                <h3><?php echo $pending_approvals; ?></h3>
                <p>Pending Applications</p>
            </div></a>
    </main>

</body>
</html>
