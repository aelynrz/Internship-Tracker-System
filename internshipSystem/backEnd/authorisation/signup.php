<?php
$conn = new mysqli("localhost", "root", "", "internship");

$data = json_decode(file_get_contents("php://input"));

$username = $data->username;
$password = password_hash($data->password, PASSWORD_DEFAULT);
$role = $data->role;

$sql = "INSERT INTO users (username, password, role)
        VALUES ('$username', '$password', '$role')";

if ($conn->query($sql)) {
    echo "Signup successful";
} else {
    echo "Error: " . $conn->error;
}
?>