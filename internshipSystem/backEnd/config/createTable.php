<?php

require_once ("config.php");

$sql_users = "CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','student','supervisor') NOT NULL
)";

$sql_internships = "CREATE TABLE internships (
    internship_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    deadline DATE,
    company_id INT NOT NULL,
    supervisor_id INT NOT NULL,
    FOREIGN KEY (company_id) REFERENCES companies(company_id),
    FOREIGN KEY (supervisor_id) REFERENCES users(user_id)
)";

$sql_applications = "CREATE TABLE applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    internship_id INT NOT NULL,
    status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(user_id),
    FOREIGN KEY (internship_id) REFERENCES internships(internship_id)
)";

$sql_companies = "CREATE TABLE companies (
    company_id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    industry VARCHAR(255),
    supervisor_id INT NOT NULL,
    FOREIGN KEY (supervisor_id) REFERENCES users(user_id)
)";

if(mysqli_query($conn, $sql_users)){
  if(mysqli_query($conn, $sql_companies)){
    if(mysqli_query($conn, $sql_internships)){
      if(mysqli_query($conn, $sql_applications)){
        echo "All tables created<br>";}
      else
        echo mysqli_error($conn);}
    else
      echo mysqli_error($conn);}
  else
    echo mysqli_error($conn);}
?>