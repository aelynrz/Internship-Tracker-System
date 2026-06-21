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
    $contact_number = trim($_POST['contact_number']); 

    if (empty($name) || empty($email) || empty($password) || empty($matric)) {
        $error = "Name, Email, Password, and Matric Number are required.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

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
    <link rel="stylesheet" href="assets/css/student_register.css">

</head>
<body class="register-page">

<div class="register-wrapper">
    <div class="register-card">

        <div class="register-left">
            <div class="image-overlay">
                <h2>Join InternTrack</h2>
                <p>Create your student account and start managing your internship applications.</p>
            </div>
        </div>

        <div class="register-right">
            <div class="register-header">
            <img src="assets/images/red.svg" alt="ICB Logo" class="register-logo">
            <h1>Create Account</h1>
            </div>
            <p class="subtitle">Register to access your internship dashboard</p>

            <form method="POST" action="">
                <label>Name</label>
                <input type="text" name="name" required>

                <div class="form-row">
                    <div>
                        <label>Matric Number</label>
                        <input type="text" name="matric_number" required>
                    </div>

                    <div>
                        <label>CGPA</label>
                        <input type="text" name="cgpa" required>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label>Major</label>
                        <input type="text" name="major" required>
                    </div>

                    <div>
                        <label>Contact No</label>
                        <input type="text" name="contact_number" required>
                    </div>
                </div>

                <label>Email Address</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <button type="submit">Register</button>
            </form>

            <p class="login-text">
                Already have an account?
                <a href="login.php">Log in here</a>
            </p>
        </div>

    </div>
</div>

</body>
</html>