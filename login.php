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
        // ---> THIS IS THE PART YOU WERE MISSING! <---
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
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Reusing the auth styles from register for consistency */
        .auth-container { 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            width: 100%;
            background-image: url('assets/images/backg1.jpg');
            background-size: cover; 
            background-position: center; 
            background-repeat: no-repeat; 
        }
        .auth-card { background: white; padding: 40px; border-radius: var(--radius-lg); width: 100%; max-width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; }
        .form-control { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); outline: none; }
        .btn-primary { width: 100%; padding: 12px; background: var(--accent-dark); color: white; border: none; border-radius: var(--radius-md); cursor: pointer; font-weight: 600; }
        .alert-error { background: #ffebee; color: #c62828; padding: 10px; margin-bottom: 20px; border-radius: 8px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2 style="margin-bottom: 20px; text-align: center;">Welcome Back</h2>
            
            <?php if($error) echo "<div class='alert-error'>$error</div>"; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn-primary">Log In</button>
            </form>
            <p style="text-align: center; margin-top: 20px; font-size: 14px;">
                Don't have an account? <a href="register.php" style="color: var(--accent-dark);">Register here</a>
            </p>
        </div>
    </div>
</body>
</html>