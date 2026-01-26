<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session completely
session_destroy();

// Redirect to admin login with logout message
header('Location: admin_login.php?logged_out=1');
exit;
?>