<?php
// supervisor_profile.php
session_start();
require_once 'db_connect.php';

// Security Check
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Supervisor') {
    header("Location: login.php");
    exit();
}

$supervisor_id = $_SESSION['UserID'];
$message = '';

// Handle Form Submission for Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $new_password = $_POST['password'];

    // Removed "Department" from these update queries
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE User SET Name=?, Email=?, Password=? WHERE UserID=?");
        $update_stmt->bind_param("sssi", $name, $email, $hashed_password, $supervisor_id);
    } else {
        $update_stmt = $conn->prepare("UPDATE User SET Name=?, Email=? WHERE UserID=?");
        $update_stmt->bind_param("ssi", $name, $email, $supervisor_id);
    }

    if ($update_stmt->execute()) {
        $_SESSION['Name'] = $name; 
        $message = "<div style='color: #2e7d32; background: #e8f5e9; padding: 12px; border-radius: 8px; margin-bottom: 20px;'>Profile updated successfully!</div>";
    } else {
        $message = "<div style='color: #c62828; background: #ffebee; padding: 12px; border-radius: 8px; margin-bottom: 20px;'>Error updating profile. Email might be taken.</div>";
    }
    $update_stmt->close();
}

// Fetch current user info AND their assigned company based on the new User.CompanyID column
$query = "
    SELECT u.Name, u.Email, c.CompanyName 
    FROM User u 
    LEFT JOIN Company c ON u.CompanyID = c.CompanyID 
    WHERE u.UserID = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $supervisor_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

$assigned_company = $user_data['CompanyName'] ?? "No Company Assigned Yet";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Supervisor</title>
    <link rel="stylesheet" href="assets/css/supervisor.css">
</head>
<body>

    <aside class="sidebar">
        <div class="brand"><img src="assets/images/red.svg" class="logo">InternTrack</div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="supervisor_dashboard.php" class="nav-link">Company Overview</a></li>
            <li class="nav-item"><a href="supervisor_applications.php" class="nav-link">Manage Applications</a></li>
            <li class="nav-item"><a href="supervisor_profile.php" class="nav-link active">My Profile</a></li>
        </ul>
        <ul class="nav-menu" style="margin-top: auto;">
            <li class="nav-item"><a href="logout.php" class="nav-link">Log out</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Account Settings</h1>
        </header>

        <section class="data-section profile-container">
            <?php echo $message; ?>
            <div class="settings-grid">
                <div class="personal-card">
                    <h2 class="section-title">Personal Details</h2>

                    <form method="POST">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text"
                                name="name"
                                class="form-control"
                                required
                                value="<?php echo htmlspecialchars($user_data['Name']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email"
                                name="email"
                                class="form-control"
                                required
                                value="<?php echo htmlspecialchars($user_data['Email']); ?>">
                        </div>

                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password"
                                name="password"
                                class="form-control"
                                placeholder="Leave blank to keep current password">
                        </div>

                        <button type="submit" class="save-btn">
                            Save Changes
                        </button>

                    </form>
                </div>

                <div class="company-card">
                    <h2 class="section-title">Company Card</h2>
                    <p>You represent</p>
                    <h3> <?php echo htmlspecialchars($assigned_company); ?></h3>
                    <p>Contact the administrator if this information is incorrect.</p>
                </div>

            </div>
        </section>
    </main>

</body>
</html>