<?php
// student_my_applications.php
session_start();
require_once 'db_connect.php';

// Security Check
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['UserID'];

// Fetch all applications for this student
$query = "
    SELECT a.ApplicationID, c.CompanyName, a.Status, a.SubmissionDate 
    FROM Application a
    JOIN Company c ON a.CompanyID = c.CompanyID
    WHERE a.StudentID = ?
    ORDER BY a.SubmissionDate DESC
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
    <title>My Applications - Student</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <aside class="sidebar">
        <div class="brand">InternTrack</div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="student_dashboard.php" class="nav-link">My Dashboard</a></li>
            <li class="nav-item"><a href="student_companies.php" class="nav-link">Browse Companies</a></li>
            <li class="nav-item"><a href="student_my_applications.php" class="nav-link active">My Applications</a></li>
        </ul>
        <div class="nav-menu" style="flex-grow: 0; margin-top: auto;">
            <li class="nav-item"><a href="logout.php" class="nav-link">Log out</a></li>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Application History</h1>
        </header>

        <section class="data-section">
            <h2 class="section-title">All Submitted Applications</h2>
            <table>
                <thead>
                    <tr>
                        <th>Application ID</th>
                        <th>Target Company</th>
                        <th>Date Applied</th>
                        <th>Current Status</th>
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
                        <td><?php echo date('d M Y, h:i A', strtotime($row['SubmissionDate'])); ?></td>
                        <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['Status']); ?></span></td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='4'>You have not submitted any applications yet.</td></tr>";
                    }
                    $stmt->close();
                    ?>
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>