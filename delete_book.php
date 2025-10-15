<?php
require 'config.php';

// Only admins can delete
if ($_SESSION['role'] !== 'admin') {
    die("Access denied. Admins only.");
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid book ID.");
}

$bookId = (int)$_GET['id'];

// Prepare statement for security
$stmt = $mysqli->prepare("DELETE FROM books WHERE id = ?");
$stmt->bind_param("i", $bookId);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: admin_books.php?msg=Book+deleted+successfully");
    exit;
} else {
    $stmt->close();
    die("Error deleting book: " . $mysqli->error);
}
?>
