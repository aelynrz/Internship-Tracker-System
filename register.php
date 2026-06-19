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
    
    $matric = trim($_POST['matric']);
    $cgpa = floatval($_POST['cgpa']);
    $major = trim($_POST['major']);
    $contact_number = trim($_POST['contact_number']); // NEW: Get Contact Number

    if (empty($name) || empty($email) || empty($password) || empty($matric)) {
        $error = "Name, Email, Password, and Matric Number are required.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // NEW: Added ContactNumber to the INSERT query
        $stmt = $conn->prepare("INSERT INTO User (Name, Email, Password, Roles, MatricNumber, CGPA, Major, ContactNumber) VALUES (?, ?, ?, 'Student', ?, ?, ?, ?)");
        $stmt->bind_param("ssssdss", $name, $email, $hashed_password, $matric, $cgpa, $major, $contact_number);

        if ($stmt->execute()) {
            $success = "Registration successful! You can now login.";
        } else {
            $error = "Registration failed. Email or Matric Number might already exist.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Registration - Internship Tracker</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <style>
        * { box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { margin: 0; padding: 0; }
        .auth-container { 
            display: flex; justify-content: center; align-items: center; min-height: 100vh; width: 100%; 
            background-image: url('assets/images/background.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;
            padding: 20px;
        }
        .auth-card { background: white; padding: 40px; border-radius: 20px; width: 100%; max-width: 450px; box-shadow: 0 8px 30px rgba(0,0,0,0.5); }
        .form-row { display: flex; gap: 15px; }
        .form-row .form-group { flex: 1; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 12px; outline: none; }
        .btn-primary { width: 100%; padding: 12px; background: #1e1e1e; color: white; border: none; border-radius: 12px; cursor: pointer; font-weight: 600; margin-top: 10px; }
        .alert { padding: 10px; margin-bottom: 20px; border-radius: 8px; font-size: 14px; }
        .alert-error { background: #ffebee; color: #c62828; }
        .alert-success { background: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2 class="auth-title">Student Portal</h2>
            <p class="auth-subtitle">Register for your internship account</p>
            
            <?php if($error) echo "<div class='alert alert-error'>$error</div>"; ?>
            <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Matriks Number</label>
                        <input type="text" name="matric" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>CGPA</label>
                        <input type="number" step="0.01" min="0" max="4.00" name="cgpa" class="form-control" placeholder="e.g. 3.75">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Major / Course</label>
                        <input type="text" name="major" class="form-control" placeholder="e.g. Computer Science">
                    </div>
                    <div class="form-group">
                        <label>Contact No.</label>
                        <input type="text" name="contact_number" class="form-control" placeholder="+6012345678">
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn-primary">Register</button>
            </form>
            <p class="auth-footer">
                Already have an account? <a href="login.php" style="color: #1e1e1e;">Log in here</a>
            </p>
        </div>
    </div>
</body>
</html>