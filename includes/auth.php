<?php
session_start();

// Only logged-in users can enter
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /studycircle/auth/login.php");
        exit();
    }
}

// Only admins can enter
function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] != 'admin') {
        header("Location: /studycircle/student/dashboard.php");
        exit();
    }
}
?>