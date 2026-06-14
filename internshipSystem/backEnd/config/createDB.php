<?php

//Q1: Set username, password, database name
$servername = "localhost";
$username = "root";
$password = "";

//Q2: Create connection
$conn = mysqli_connect($servername,$username,$password);

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

//Q3: Create database
$sql = "CREATE DATABASE internshipDB";
if (mysqli_query($conn, $sql)) {
  echo "Database created successfully";
} else {
  echo "Error creating database: " . mysqli_error($conn);
}


?>
