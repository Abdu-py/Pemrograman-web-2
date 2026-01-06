<?php
/**
 * Simple Logout - Direct Method
 * File: logout.php
 * Location: root folder (republik_computer/)
 */

// Start session
session_start();

// Debug mode - uncomment untuk cek masalah
// echo "Session before logout: ";
// print_r($_SESSION);

// Unset all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Debug mode - uncomment untuk cek hasil
// echo "<br>Session after logout: ";
// print_r($_SESSION);

// Redirect to login page
header("Location: login.php");
exit();
?>