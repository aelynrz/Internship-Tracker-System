<?php
// student_my_applications.php
session_start();
require_once 'db_connect.php';

// Security Check: Must be logged in AND be a Student
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$student_id = intval($_SESSION['UserID']);

// Fetch all applications for this specific student, joined with the Company table
$query = "
    SELECT a.ApplicationID, c.CompanyName, c.Industry, a.Status, a.SubmissionDate 
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - InternTrack</title>
    <link rel="stylesheet" href="assets/css/student.css">
</head>
<body>

    <aside class="sidebar">
        <div class="brand"><img src="assets/images/red.svg" class="logo">InternTrack</div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="student_dashboard.php" class="nav-link">My Dashboard</a></li>
            <li class="nav-item"><a href="student_companies.php" class="nav-link">Browse Companies</a></li>
            <li class="nav-item"><a href="student_my_applications.php" class="nav-link active">My Applications</a></li>
        </ul>
        <ul class="nav-menu" style="margin-top: auto;">
            <li class="nav-item"><a href="logout.php" class="nav-link">Log out</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Application Tracker</h1>
        </header>

        <section class="data-section">
            <h2 class="section-title">My Application History</h2>
            <table>
                <thead>
                    <tr>
                        <th>Application ID</th>
                        <th>Target Company</th>
                        <th>Industry</th>
                        <th>Date Submitted</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) { 
                            // Determine CSS class based on status for the colored badge
                            $statusClass = strtolower($row['Status']);
                            
                            // Format the ID with the cool custom prefix
                            $custom_app_id = sprintf("APP_%05d", $row['ApplicationID']);
                    ?>
                    <tr>
                        <td style="font-weight: 600; color: var(--accent-dark);"><?php echo $custom_app_id; ?></td>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($row['CompanyName']); ?></td>
                        <td><?php echo htmlspecialchars($row['Industry'] ?? 'N/A'); ?></td>
                        <td><?php echo date('d M Y, h:i A', strtotime($row['SubmissionDate'])); ?></td>
                        <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['Status']); ?></span></td>
                    </tr>
                    <?php 
                        }
                    } else {
                        // Empty state if the student hasn't applied anywhere yet
                        echo "<tr><td colspan='5' style='text-align: center; padding: 30px; color: #7a7a7a;'>You haven't submitted any internship applications yet. Go to 'Browse Companies' to get started!</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>
<?php
$stmt->close();
?>