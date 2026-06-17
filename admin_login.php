<?php
// admin_login.php
session_start();

// 1. UNCOMMENTED: We are now connecting to the real database!
require_once 'db_connect.php';

// Redirect if already logged in as Admin
if (isset($_SESSION['UserID']) && $_SESSION['Role'] == 'Admin') {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Changed from 'username' to 'email' to match your database
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 3. Query the database SPECIFICALLY for an Admin with this email
    $stmt = $conn->prepare("SELECT UserID, Name, Password FROM User WHERE Email = ? AND Roles = 'Admin'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        
        // 4. Securely verify the password (handles both encrypted 'yx123' and plain text)
        if ($password === $row['Password'] || password_verify($password, $row['Password'])) {
            
            // Set the REAL session variables from the database
            $_SESSION['UserID'] = $row['UserID']; 
            $_SESSION['Name'] = $row['Name'];
            $_SESSION['Role'] = 'Admin';
            
            header("Location: admin_dashboard.php");
            exit();
            
        } else {
            $error = "Invalid password. Access denied.";
        }
    } else {
        $error = "Admin account not found or invalid email.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Access - InternTrack</title>
    <style>
        * { box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { margin: 0; padding: 0; }
        .auth-container { display: flex; justify-content: center; align-items: center; height: 100vh; width: 100%; background: #1a1a1a; }
        .auth-card { background: white; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; margin-bottom: 20px; outline: none; }
        .btn-primary { width: 100%; padding: 12px; background: #c62828; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 16px; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2 style="margin-bottom: 25px; text-align: center; color: #c62828;">Admin Secure Login</h2>
            
            <?php if($error) echo "<div style='color: #c62828; padding: 12px; background: #ffebee; margin-bottom: 20px; border-radius: 6px; font-size: 14px;'>$error</div>"; ?>
            
            <form method="POST">
                <input type="email" name="email" class="form-control" placeholder="Admin Email (admin@intern.com)" required>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                
                <button type="submit" class="btn-primary">Authenticate</button>
            </form>
        </div>
    </div>
</body>
</html>