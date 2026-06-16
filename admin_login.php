<?php
// admin_login.php
session_start();
require_once 'db_connect.php';

if (isset($_SESSION['UserID']) && $_SESSION['Role'] == 'Admin') {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Only search for Admins
    $stmt = $conn->prepare("SELECT UserID, Name, Password FROM User WHERE Email = ? AND Roles = 'Admin'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['Password'])) {
            $_SESSION['UserID'] = $row['UserID'];
            $_SESSION['Name'] = $row['Name'];
            $_SESSION['Role'] = 'Admin';
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "Unauthorized access or incorrect credentials.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Access - InternTrack</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-container { display: flex; justify-content: center; align-items: center; height: 100vh; width: 100%; background: #1a1a1a; } /* Dark theme for admin login */
        .auth-card { background: white; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; margin-bottom: 20px; outline: none; }
        .btn-primary { width: 100%; padding: 12px; background: #c62828; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; } /* Red button for admin */
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2 style="margin-bottom: 20px; text-align: center; color: #c62828;">Admin Secure Login</h2>
            <?php if($error) echo "<div style='color: #c62828; padding: 10px; background: #ffebee; margin-bottom: 20px; border-radius: 5px;'>$error</div>"; ?>
            <form method="POST">
                <input type="email" name="email" class="form-control" placeholder="Admin Email" required>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                <button type="submit" class="btn-primary">Authenticate</button>
            </form>
        </div>
    </div>
</body>
</html>