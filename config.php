<?php
// Load configuration securely
$config = parse_ini_file(__DIR__ . '/config.ini', true);

// Access database section
$db = $config['database'];

$mysqli = new mysqli(
    $db['DB_HOST'],
    $db['DB_USER'],
    $db['DB_PASS'],
    $db['DB_NAME'],
    $db['DB_PORT']
);

if ($mysqli->connect_error) {
    error_log("Database connection failed: " . $mysqli->connect_error);
    die("Internal server error. Please try again later.");
}

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
