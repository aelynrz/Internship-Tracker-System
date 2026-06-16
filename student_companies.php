<?php
// student_companies.php
session_start();
require_once 'db_connect.php';

// Security Check
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

// Fetch all companies
$query = "SELECT CompanyID, CompanyName, Industry FROM Company ORDER BY CompanyName ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Companies - Student</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <aside class="sidebar">
        <div class="brand">InternTrack</div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="student_dashboard.php" class="nav-link">My Dashboard</a></li>
            <li class="nav-item"><a href="student_companies.php" class="nav-link active">Browse Companies</a></li>
            <li class="nav-item"><a href="student_my_applications.php" class="nav-link">My Applications</a></li>
        </ul>
        <div class="nav-menu" style="flex-grow: 0; margin-top: auto;">
            <li class="nav-item"><a href="logout.php" class="nav-link">Log out</a></li>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Available Internships</h1>
        </header>

        <section class="data-section">
            <h2 class="section-title">Partner Companies Directory</h2>
            <table>
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Industry</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) { 
                    ?>
                    <tr>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($row['CompanyName']); ?></td>
                        <td><?php echo htmlspecialchars($row['Industry'] ?? 'General'); ?></td>
                        <td>
                            <a href="student_apply.php?id=<?php echo $row['CompanyID']; ?>" 
                               style="background: var(--accent-dark); color: white; padding: 6px 12px; border-radius: var(--radius-md); text-decoration: none; font-size: 13px;">
                               Apply Now
                            </a>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='3'>No companies are currently registered.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>