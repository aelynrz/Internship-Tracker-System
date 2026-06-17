<?php
// student_companies.php
session_start();
require_once 'db_connect.php';

// Security Check
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['UserID'];

// 1. Fetch Student Details for the Confirmation Form
$stmt_student = $conn->prepare("SELECT Name, Email, MatricNumber, CGPA, Major, ContactNumber FROM User WHERE UserID = ?");
$stmt_student->bind_param("i", $student_id);
$stmt_student->execute();
$student_data = $stmt_student->get_result()->fetch_assoc();
$stmt_student->close();

// 2. Fetch all available companies
$comp_query = "SELECT CompanyID, CompanyName, Industry FROM Company ORDER BY CompanyName ASC";
$companies = $conn->query($comp_query);

// 3. Fetch the companies this student has ALREADY applied to
$applied_stmt = $conn->prepare("SELECT CompanyID FROM Application WHERE StudentID = ?");
$applied_stmt->bind_param("i", $student_id);
$applied_stmt->execute();
$applied_result = $applied_stmt->get_result();

$applied_companies = [];
while ($row = $applied_result->fetch_assoc()) {
    $applied_companies[] = $row['CompanyID'];
}
$applied_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Companies - InternTrack</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-card { background: white; padding: 30px; border-radius: var(--radius-lg); width: 100%; max-width: 500px; }
        .data-box { background: #f9f9f9; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; margin: 20px 0; }
        .data-row { display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .data-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .data-label { color: var(--text-secondary); font-size: 13px; font-weight: 500; }
        .data-value { font-weight: 600; color: var(--text-primary); text-align: right; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand">InternTrack</div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="student_dashboard.php" class="nav-link">My Dashboard</a></li>
            <li class="nav-item"><a href="student_companies.php" class="nav-link active">Browse Companies</a></li>
            <li class="nav-item"><a href="student_my_applications.php" class="nav-link">My Applications</a></li>
        </ul>
        <div class="nav-menu" style="flex-grow: 0; margin-top: auto;">
            <li class="nav-item"><a href="logout.php" class="nav-link">Log out</a></li>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Available Internships</h1>
        </header>

        <section class="data-section">
            <?php 
            if (isset($_SESSION['message'])) {
                echo $_SESSION['message'];
                unset($_SESSION['message']);
            }
            ?>
            <h2 class="section-title">Partner Companies Directory</h2>
            <table>
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Industry</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($companies->num_rows > 0) {
                        while($row = $companies->fetch_assoc()) { 
                            $is_applied = in_array($row['CompanyID'], $applied_companies);
                    ?>
                    <tr>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($row['CompanyName']); ?></td>
                        <td><?php echo htmlspecialchars($row['Industry'] ?? 'N/A'); ?></td>
                        <td>
                            <?php if ($is_applied): ?>
                                <button disabled style="background: #e0e0e0; color: #7a7a7a; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: not-allowed;">
                                    Applied
                                </button>
                            <?php else: ?>
                                <button onclick="openApplyModal(<?php echo $row['CompanyID']; ?>, '<?php echo addslashes($row['CompanyName']); ?>')" 
                                        style="background: var(--accent-dark); color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer;">
                                    Apply Now
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='3'>No companies currently available.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>

    <div class="modal-overlay" id="applyModal">
        <div class="modal-card">
            <h2 style="margin-bottom: 5px;">Confirm Application</h2>
            <p style="font-size: 14px; color: var(--text-secondary);">You are applying to <strong id="modalCompanyName" style="color: var(--accent-dark);"></strong>. The following profile data will be sent to the company's HR supervisor for review.</p>
            
            <div class="data-box">
                <div class="data-row"><span class="data-label">Full Name</span><span class="data-value"><?php echo htmlspecialchars($student_data['Name']); ?></span></div>
                <div class="data-row"><span class="data-label">Matrik No.</span><span class="data-value"><?php echo htmlspecialchars($student_data['MatricNumber'] ?? 'N/A'); ?></span></div>
                <div class="data-row"><span class="data-label">Major</span><span class="data-value"><?php echo htmlspecialchars($student_data['Major'] ?? 'N/A'); ?></span></div>
                <div class="data-row"><span class="data-label">CGPA</span><span class="data-value"><?php echo htmlspecialchars($student_data['CGPA'] ?? 'N/A'); ?></span></div>
                <div class="data-row"><span class="data-label">Email</span><span class="data-value"><?php echo htmlspecialchars($student_data['Email']); ?></span></div>
                <div class="data-row"><span class="data-label">Contact</span><span class="data-value"><?php echo htmlspecialchars($student_data['ContactNumber'] ?? 'N/A'); ?></span></div>
            </div>

            <form method="POST" action="student_apply.php">
                <input type="hidden" name="company_id" id="modalCompanyId">
                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="closeModal()" style="flex: 1; padding: 12px; border: 1px solid var(--border-color); background: white; border-radius: 8px; cursor: pointer; font-weight: 600;">Cancel</button>
                    <button type="submit" style="flex: 1; padding: 12px; border: none; background: #2e7d32; color: white; border-radius: 8px; cursor: pointer; font-weight: 600;">Confirm & Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openApplyModal(companyId, companyName) {
            document.getElementById('modalCompanyId').value = companyId;
            document.getElementById('modalCompanyName').innerText = companyName;
            document.getElementById('applyModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('applyModal').style.display = 'none';
        }

        // Close modal if user clicks outside of the card
        window.onclick = function(event) {
            if (event.target == document.getElementById('applyModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>