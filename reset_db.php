<?php
// reset_db.php
require_once 'db_connect.php';

// Disable constraints to allow dropping tables safely
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Drop existing tables if they exist to start completely fresh
$conn->query("DROP TABLE IF EXISTS Application");
$conn->query("DROP TABLE IF EXISTS User");
$conn->query("DROP TABLE IF EXISTS Company");

// CREATE COMPANY TABLE
$conn->query("CREATE TABLE Company (
    CompanyID INT AUTO_INCREMENT PRIMARY KEY,
    CompanyName VARCHAR(255) NOT NULL,
    Industry VARCHAR(100)
)");

// CREATE USER TABLE
$conn->query("CREATE TABLE User (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Email VARCHAR(255) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Roles ENUM('Admin', 'Supervisor', 'Student') NOT NULL,
    MatricNumber VARCHAR(50) NULL,
    CGPA DECIMAL(3,2) NULL,
    Major VARCHAR(100) NULL,
    ContactNumber VARCHAR(20) NULL,
    CompanyID INT NULL,
    FOREIGN KEY (CompanyID) REFERENCES Company(CompanyID) ON DELETE SET NULL
)");

// CREATE APPLICATION TABLE
$conn->query("CREATE TABLE Application (
    ApplicationID INT AUTO_INCREMENT PRIMARY KEY,
    StudentID INT NOT NULL,
    CompanyID INT NOT NULL,
    Status ENUM('Pending', 'Accepted', 'Rejected') DEFAULT 'Pending',
    SubmissionDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (StudentID) REFERENCES User(UserID) ON DELETE CASCADE,
    FOREIGN KEY (CompanyID) REFERENCES Company(CompanyID) ON DELETE CASCADE
)");

// Re-enable constraints
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// 4. Insert company datas
$conn->query("INSERT INTO Company (CompanyID, CompanyName, Industry) VALUES
(1, 'Microsoft Corporation', 'Technology'),
(2, 'Apple Inc.', 'Consumer Electronics'),
(3, 'NVIDIA Corporation', 'Semiconductors'),
(4, 'Amazon.com, Inc.', 'E-commerce & Cloud'),
(5, 'Alphabet Inc. (Google)', 'Internet Services'),
(6, 'Meta Platforms, Inc.', 'Social Media'),
(7, 'Berkshire Hathaway', 'Financial Services'),
(8, 'Tesla, Inc.', 'Automotive'),
(9, 'Eli Lilly and Company', 'Pharmaceuticals'),
(10, 'Broadcom Inc.', 'Semiconductors')");

// Generate secure hash for password
$password = password_hash('yx123', PASSWORD_DEFAULT);

// Insert Admin, 10 CEO Supervisors, and 5 Students
$conn->query("INSERT INTO User (UserID, Name, Email, Password, Roles, MatricNumber, CGPA, Major, ContactNumber, CompanyID) VALUES
-- Admin
(1, 'System Admin', 'admin@utm.com', '$password', 'Admin', NULL, NULL, NULL, NULL, NULL),

-- Supervisors (CEOs assigned to Companies 1-10)
(2, 'Satya Nadella', 'satya@microsoft.com', '$password', 'Supervisor', NULL, NULL, NULL, '+1800111222', 1),
(3, 'Tim Cook', 'tim@apple.com', '$password', 'Supervisor', NULL, NULL, NULL, '+1800111333', 2),
(4, 'Jensen Huang', 'jensen@nvidia.com', '$password', 'Supervisor', NULL, NULL, NULL, '+1800111444', 3),
(5, 'Andy Jassy', 'andy@amazon.com', '$password', 'Supervisor', NULL, NULL, NULL, '+1800111555', 4),
(6, 'Sundar Pichai', 'sundar@google.com', '$password', 'Supervisor', NULL, NULL, NULL, '+1800111666', 5),
(7, 'Mark Zuckerberg', 'mark@meta.com', '$password', 'Supervisor', NULL, NULL, NULL, '+1800111777', 6),
(8, 'Warren Buffett', 'warren@berkshire.com', '$password', 'Supervisor', NULL, NULL, NULL, '+1800111888', 7),
(9, 'Elon Musk', 'elon@tesla.com', '$password', 'Supervisor', NULL, NULL, NULL, '+1800111999', 8),
(10, 'David Ricks', 'david@elililly.com', '$password', 'Supervisor', NULL, NULL, NULL, '+1800111000', 9),
(11, 'Hock Tan', 'hock@broadcom.com', '$password', 'Supervisor', NULL, NULL, NULL, '+1800111111', 10),

-- Students
(12, 'Looi Yu Xiang', 'yuxiang@gmail.com', '$password', 'Student', 'A24CS0107', 3.85, 'Network and Security', '+60111222333', NULL),
(13, 'Tan Jia Yie', 'jiayie@gmail.com', '$password', 'Student', 'A24CS0108', 3.70, 'Software Engineering', '+60111222444', NULL),
(14, 'Ng She Ling', 'sheling@gmail.com', '$password', 'Student', 'A24CS0109', 3.90, 'Data Engineering', '+60111222555', NULL),
(15, 'Mohammad Adrian Syahirin', 'adrian@gmail.com', '$password', 'Student', 'A24CS0110', 3.65, 'Bioinformatics', '+60111222666', NULL),
(16, 'Ezralyn', 'ezralyn@gmail.com', '$password', 'Student', 'A24CS0111', 3.80, 'Graphics and Multimedia', '+60111222777', NULL)");

// 7. Insert Automated Applications (All Status = Pending)
$conn->query("INSERT INTO Application (StudentID, CompanyID, Status, SubmissionDate) VALUES
-- Yu Xiang applies to 4 companies
(12, 1, 'Pending', '2026-06-15 09:00:00'),
(12, 3, 'Pending', '2026-06-15 10:15:00'),
(12, 10, 'Pending', '2026-06-16 11:30:00'),
(12, 5, 'Pending', '2026-06-16 14:00:00'),

-- Jia Yie applies to 3 companies
(13, 2, 'Pending', '2026-06-17 08:45:00'),
(13, 4, 'Pending', '2026-06-17 13:20:00'),
(13, 6, 'Pending', '2026-06-18 09:10:00'),

-- She Ling applies to 4 companies
(14, 1, 'Pending', '2026-06-14 10:00:00'),
(14, 5, 'Pending', '2026-06-15 11:15:00'),
(14, 6, 'Pending', '2026-06-16 12:30:00'),
(14, 9, 'Pending', '2026-06-16 16:45:00'),

-- Adrian applies to 3 companies
(15, 3, 'Pending', '2026-06-16 09:30:00'),
(15, 5, 'Pending', '2026-06-17 10:45:00'),
(15, 2, 'Pending', '2026-06-18 14:15:00'),

-- Ezralyn applies to 3 companies
(16, 7, 'Pending', '2026-06-15 13:00:00'),
(16, 8, 'Pending', '2026-06-16 15:30:00'),
(16, 4, 'Pending', '2026-06-18 11:00:00')");

// 8. Success Message UI
echo "Data importation into databse : SUCCESSFUL<br>";