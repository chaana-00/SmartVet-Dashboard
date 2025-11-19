<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>
