<?php
require 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['uid'])) {
    die("Login required");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)$_POST['order_id'];
    $user_id = $_SESSION['uid'];

    if ($order_id <= 0) {
        die("Invalid order ID");
    }

    $stmt = $mysqli->prepare("DELETE FROM orders WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $order_id, $user_id);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: my_orders.php?msg=Order+deleted+successfully");
        exit;
    } else {
        $stmt->close();
        die("Error deleting order: " . $mysqli->error);
    }
}
?>
