<?php
require 'config.php';

// Only proceed if a session exists
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy all session data
$_SESSION = [];
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>
