<?php
// student_apply.php
session_start();
require_once 'db_connect.php';

// 1. Basic Security Check
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$student_id = intval($_SESSION['UserID']);

// 2. CRITICAL FIX: Verify the student actually exists in the database
$verify_user = $conn->prepare("SELECT UserID FROM User WHERE UserID = ? AND Roles = 'Student'");
$verify_user->bind_param("i", $student_id);
$verify_user->execute();
$user_exists = $verify_user->get_result();

if ($user_exists->num_rows === 0) {
    // Session is dead. Forcefully destroy and redirect.
    $verify_user->close();
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
$verify_user->close();

// 3. Process the Application via POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['company_id'])) {
    $company_id = intval($_POST['company_id']);

    // Check if the student already applied to this company just in case
    $check_stmt = $conn->prepare("SELECT ApplicationID FROM Application WHERE StudentID = ? AND CompanyID = ?");
    $check_stmt->bind_param("ii", $student_id, $company_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['message'] = "<div style='color: #c62828; background: #ffebee; padding: 12px; border-radius: 8px; margin-bottom: 20px;'>You have already applied to this company.</div>";
    } else {
        // Safe to Insert! Create the new application.
        $insert_stmt = $conn->prepare("INSERT INTO Application (StudentID, CompanyID, Status, SubmissionDate) VALUES (?, ?, 'Pending', NOW())");
        $insert_stmt->bind_param("ii", $student_id, $company_id);
        
        if ($insert_stmt->execute()) {
            $_SESSION['message'] = "<div style='color: #2e7d32; background: #e8f5e9; padding: 12px; border-radius: 8px; margin-bottom: 20px;'>Application successfully submitted to HR!</div>";
        } else {
            $_SESSION['message'] = "<div style='color: #c62828; background: #ffebee; padding: 12px; border-radius: 8px; margin-bottom: 20px;'>Error submitting application.</div>";
        }
        $insert_stmt->close();
    }
    $check_stmt->close();
}

// Redirect back to the browse companies page so they can see the success message
header("Location: student_companies.php");
exit();
?>