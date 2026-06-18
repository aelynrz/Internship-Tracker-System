<?php
$host = 'localhost';
$user = 'root';
$pass = ''; //no password
$dbname = 'internship_tracker'; //database name

$conn = new mysqli($host, $user, $pass); //try to connect to db

if ($conn->connect_error) { //if cannot connect to db
    die("Connection to database FAILED<br>" . $conn->connect_error);
}

//check if the database already exists
$db_check = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");

if ($db_check->num_rows == 0) {
    //create db if doesnt exist
    $sql = "CREATE DATABASE $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "Database '$dbname' created successfully.<br>";
    } else {
        die("ERROR creating database: " . $conn->error);
    }
}

// try to connect to the database again
$conn->select_db($dbname);
echo "Connection to database : SUCCESSFUL"; 
?>
