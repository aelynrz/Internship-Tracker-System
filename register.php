<?php
// register.php
session_start();
require_once 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } else {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO User (Name, Email, Password, Roles) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

        if ($stmt->execute()) {
            $success = "Registration successful! You can now login.";
        } else {
            $error = "Registration failed. Email might already exist.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Internship Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-container { display: flex; justify-content: center; align-items: center; height: 100vh; background: var(--bg-color); }
        .auth-card { background: white; padding: 40px; border-radius: var(--radius-lg); width: 100%; max-width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; }
        .form-control { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); outline: none; }
        .btn-primary { width: 100%; padding: 12px; background: var(--accent-dark); color: white; border: none; border-radius: var(--radius-md); cursor: pointer; font-weight: 600; }
        .alert { padding: 10px; margin-bottom: 20px; border-radius: 8px; font-size: 14px; }
        .alert-error { background: #ffebee; color: #c62828; }
        .alert-success { background: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2 style="margin-bottom: 20px; text-align: center;">Create Account</h2>
            
            <?php if($error) echo "<div class='alert alert-error'>$error</div>"; ?>
            <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>I am a...</label>
                    <select name="role" class="form-control" required>
                        <option value="Student">Student</option>
                        <option value="Supervisor">Company Supervisor</option>
                        <option value="Admin">Administrator</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Register</button>
            </form>
            <p style="text-align: center; margin-top: 20px; font-size: 14px;">
                Already have an account? <a href="login.php" style="color: var(--accent-dark);">Log in here</a>
            </p>
        </div>
    </div>
</body>
</html>