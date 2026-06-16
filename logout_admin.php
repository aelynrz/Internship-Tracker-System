<?php
// logout_admin.php
session_start();
session_unset();
session_destroy();

// Redirect back to the secure Admin login portal
header("Location: admin_login.php");
exit();
?>