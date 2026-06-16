<?php
// student_apply.php
session_start();
require_once 'db_connect.php';

// Security Check
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['UserID'];
$company_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';

// Check if valid company ID was passed
if ($company_id === 0) {
    header("Location: student_companies.php");
    exit();
}

// Check if the student has ALREADY applied to this company
$check_query = "SELECT ApplicationID FROM Application WHERE StudentID = ? AND CompanyID = ?";
$stmt_check = $conn->prepare($check_query);
$stmt_check->bind_param("ii", $student_id, $company_id);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    $message = "<div style='color: #c62828; background: #ffebee; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>You have already applied to this company!</div>";
} else {
    // If not applied yet, process the application form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $insert_query = "INSERT INTO Application (StudentID, CompanyID, Status) VALUES (?, ?, 'Pending')";
        $stmt_insert = $conn->prepare($insert_query);
        $stmt_insert->bind_param("ii", $student_id, $company_id);
        
        if ($stmt_insert->execute()) {
            $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>Application submitted successfully!</div>";
        } else {
            $message = "<div style='color: #c62828; background: #ffebee; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>Error submitting application.</div>";
        }
        $stmt_insert->close();
    }
}
$stmt_check->close();

// Fetch company details for display
$comp_query = "SELECT CompanyName FROM Company WHERE CompanyID = ?";
$stmt_comp = $conn->prepare($comp_query);
$stmt_comp->bind_param("i", $company_id);
$stmt_comp->execute();
$comp_result = $stmt_comp->get_result();
$company = $comp_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Application</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <aside class="sidebar">
        <div class="brand">InternTrack</div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="student_dashboard.php" class="nav-link">My Dashboard</a></li>
            <li class="nav-item"><a href="student_companies.php" class="nav-link">Browse Companies</a></li>
            <li class="nav-item"><a href="student_my_applications.php" class="nav-link">My Applications</a></li>
        </ul>
        <div class="nav-menu" style="flex-grow: 0; margin-top: auto;">
            <li class="nav-item"><a href="logout.php" class="nav-link">Log out</a></li>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Confirm Application</h1>
            <a href="student_companies.php" style="color: var(--text-secondary); text-decoration: none;">&larr; Back to Companies</a>
        </header>

        <section class="data-section" style="max-width: 600px;">
            <?php echo $message; ?>
            
            <?php if (strpos($message, 'successfully') === false && strpos($message, 'already applied') === false): ?>
                <h2 style="margin-bottom: 10px;">Apply to <?php echo htmlspecialchars($company['CompanyName']); ?></h2>
                <p style="color: var(--text-secondary); margin-bottom: 25px; line-height: 1.5;">
                    By clicking confirm below, your profile will be sent to the company's supervisor for review. You can track the status of this application in your dashboard.
                </p>
                <form method="POST" action="">
                    <button type="submit" style="background: var(--accent-dark); color: white; border: none; padding: 12px 24px; border-radius: var(--radius-md); font-weight: 600; cursor: pointer; width: 100%;">
                        Confirm Application Submit
                    </button>
                </form>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>