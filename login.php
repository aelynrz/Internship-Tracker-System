<?php
// login.php
session_start();
require_once 'db_connect.php';

// If already logged in, redirect to respective dashboard
if (isset($_SESSION['UserID'])) {
    $role = $_SESSION['Role'];
    if ($role === 'Admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: " . strtolower($role) . "_dashboard.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT UserID, Name, Password, Roles FROM User WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['Password'])) {
            
            // NEW SECURITY CHECK: Reject Admins from this login page
            if ($row['Roles'] == 'Admin') {
                $error = "Administrators must use the secure Admin Login portal.";
            } else {
                // Set session variables
                $_SESSION['UserID'] = $row['UserID'];
                $_SESSION['Name'] = $row['Name'];
                $_SESSION['Role'] = $row['Roles'];

                // Route to correct dashboard
                if ($row['Roles'] == 'Supervisor') header("Location: supervisor_dashboard.php");
                else header("Location: student_dashboard.php");
                exit();
            }

        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that email.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Internship Tracker</title>
    <link rel="stylesheet" href="assets/css/student_login.css">
</head>
<body class="login-page">

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-left">
            <img src="assets/images/red.svg" alt="ICB Logo" class="login-logo">

            <h1>Welcome Back</h1>
            <p class="subtitle">Login to continue your internship journey</p>

            <form method="POST" action="">
                <label>Email Address</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <button type="submit">Log In</button>
            </form>

            <p class="register-text">
                Don't have an account?
                <a href="register.php">Register here</a>
            </p>
        </div>

        <div class="login-right">
            <div class="image-overlay">
                <h2>InternTrack</h2>
                <p>Manage applications, track progress, and connect with internship opportunities.</p>
            </div>
        </div>

    </div>
</div>

</body>
</html>