<?php
// ==========================================
// DATABASE CONNECTION SETTINGS
// ==========================================

// Default credentials for XAMPP
$host = "localhost";      
$username = "root";       // The default XAMPP MySQL username
$password = "";           // The default XAMPP MySQL password is just empty
$database = "internship_system"; // Make sure this perfectly matches the name in phpMyAdmin

// 1. Attempt to create the connection
$conn = mysqli_connect($host, $username, $password, $database);

// 2. Check if the connection actually worked
if (!$conn) {
    // If it fails, stop running the page and show the exact error message
    die("Database connection failed: " . mysqli_connect_error());
}

// 3. Optional but highly recommended: Set the character set to handle special symbols securely
mysqli_set_charset($conn, "utf8mb4");

// Note: Do not put a closing ?> tag if this file only contains PHP. 
// Leaving it off prevents accidental blank spaces from breaking your HTML/Session headers later!