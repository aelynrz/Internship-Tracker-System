<?php
// student_dashboard.php
session_start();
require_once 'db_connect.php';

// Security Check: Must be logged in AND be a Student
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['UserID'];

// Get recent applications FOR THIS STUDENT ONLY
$query = "
    SELECT a.ApplicationID, c.CompanyName, a.Status, a.SubmissionDate 
    FROM Application a
    JOIN Company c ON a.CompanyID = c.CompanyID
    WHERE a.StudentID = ?
    ORDER BY a.SubmissionDate DESC LIMIT 5
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - Internship Tracker</title>
    <link rel="stylesheet" href="assets/css/student.css">
</head>
<body>

    <aside class="sidebar">
        <div class="brand">InternTrack</div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="student_dashboard.php" class="nav-link active">My Dashboard</a></li>
            <li class="nav-item"><a href="student_companies.php" class="nav-link">Browse Companies</a></li>
            <li class="nav-item"><a href="student_my_applications.php" class="nav-link">My Applications</a></li>
        </ul>
        <ul class="nav-menu" style="margin-top: auto;">
            <li class="nav-item"><a href="logout.php" class="nav-link">Log out</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Student Portal</h1>
            <div style="background: white; padding: 10px 20px; border-radius: 20px; font-size: 14px; color: #7a7a7a;">
                Logged in as: <?php echo htmlspecialchars($_SESSION['Name']); ?>
            </div>
        </header>

        <section class="kpi-grid">
            <div class="kpi-card dark">
                <div class="kpi-title">Total Applications</div>
                <div class="kpi-value">3</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-title">Interviews</div>
                <div class="kpi-value">1</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-title">Offers</div>
                <div class="kpi-value">0</div>
            </div>
        </section>

        <section class="data-section">
            <h2 class="section-title">My Recent Activity</h2>
            <table>
                <thead>
                    <tr>
                        <th>App ID</th>
                        <th>Company</th>
                        <th>Date Applied</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) { 
                            $statusClass = strtolower($row['Status']);
                    ?>
                    <tr>
                        <td>#APP-<?php echo htmlspecialchars($row['ApplicationID']); ?></td>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($row['CompanyName']); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['SubmissionDate'])); ?></td>
                        <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['Status']); ?></span></td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='4'>You haven't applied anywhere yet!</td></tr>";
                    }
                    $stmt->close();
                    ?>
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>