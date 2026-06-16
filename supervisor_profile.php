<?php
// supervisor_profile.php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Supervisor') {
    header("Location: login.php");
    exit();
}

$supervisor_id = $_SESSION['UserID'];
$message = '';

// Check if supervisor already has a company
$stmt = $conn->prepare("SELECT CompanyID, CompanyName, Industry FROM Company WHERE SupervisorID = ?");
$stmt->bind_param("i", $supervisor_id);
$stmt->execute();
$result = $stmt->get_result();
$company = $result->fetch_assoc();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comp_name = trim($_POST['company_name']);
    $industry = trim($_POST['industry']);

    if ($company) {
        // Update existing company
        $update = $conn->prepare("UPDATE Company SET CompanyName = ?, Industry = ? WHERE SupervisorID = ?");
        $update->bind_param("ssi", $comp_name, $industry, $supervisor_id);
        $update->execute();
        $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Profile updated successfully!</div>";
    } else {
        // Insert new company
        $insert = $conn->prepare("INSERT INTO Company (CompanyName, Industry, SupervisorID) VALUES (?, ?, ?)");
        $insert->bind_param("ssi", $comp_name, $industry, $supervisor_id);
        $insert->execute();
        $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 20px;'>Company registered successfully!</div>";
    }
    
    // Refresh data
    $stmt->execute();
    $company = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Company Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <aside class="sidebar">
        <div class="brand">InternTrack</div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="supervisor_dashboard.php" class="nav-link">Company Overview</a></li>
            <li class="nav-item"><a href="supervisor_applications.php" class="nav-link">Manage Applications</a></li>
            <li class="nav-item"><a href="supervisor_profile.php" class="nav-link active">Company Profile</a></li>
        </ul>
        <div class="nav-menu" style="flex-grow: 0; margin-top: auto;">
            <li class="nav-item"><a href="logout.php" class="nav-link">Log out</a></li>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Company Setup</h1>
        </header>

        <section class="data-section" style="max-width: 500px;">
            <?php echo $message; ?>
            <h2 class="section-title">Update Details</h2>
            
            <form method="POST" action="">
                <div style="margin-bottom: 20px;">
                    <label style="display: