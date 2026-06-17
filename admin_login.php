<?php
// admin_login.php
session_start();
//require_once 'db_connect.php';

if (isset($_SESSION['UserID']) && $_SESSION['Role'] == 'Admin') {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // --- THE HARDCODED CHECK ---
    if ($username === 'admin' && $password === 'Password') {
        
        // Give the hardcoded admin a fake UserID so other pages don't throw errors
        $_SESSION['UserID'] = 9999; 
        $_SESSION['Name'] = 'System Administrator';
        $_SESSION['Role'] = 'Admin';
        
        header("Location: admin_dashboard.php");
        exit();
        
    } else {
        $error = "Invalid credentials. Access denied.";
    }
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
                <input type="text" name="username" class="form-control" placeholder="Admin Username" required>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                
                <button type="submit" class="btn-primary">Authenticate</button>
            </form>
        </div>
    </div>
</body>
</html>