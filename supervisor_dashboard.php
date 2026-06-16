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

// 1. Find the company managed by this supervisor
$comp_query = "SELECT CompanyID, CompanyName FROM Company WHERE SupervisorID = ?";
$stmt = $conn->prepare($comp_query);
$stmt->bind_param("i", $supervisor_id);
$stmt->execute();
$comp_result = $stmt->get_result();
$company = $comp_result->fetch_assoc();

$company_id = $company ? $company['CompanyID'] : null;
$company_name = $company ? $company['CompanyName'] : "No Company Assigned";

// 2. Fetch recent applications for this specific company
$recent_apps = null;
if ($company_id) {
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
            <li class="nav-item"><a href="supervisor_profile.php" class="nav-link">Company Profile</a></li>
        </ul>
        <div class="nav-menu" style="flex-grow: 0; margin-top: auto;">
            <li class="nav-item"><a href="logout.php" class="nav-link">Log out</a></li>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title"><?php echo htmlspecialchars($company_name); ?> Dashboard</h1>
            <div style="background: white; padding: 10px 20px; border-radius: 20px; font-size: 14px; color: #7a7a7a;">
                Supervisor: <?php echo htmlspecialchars($_SESSION['Name']); ?>