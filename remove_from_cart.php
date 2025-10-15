<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['uid'])) {
    die("Please login first!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $user_id  = $_SESSION['uid'];

    // Use prepared statement to safely delete only the user's cart item
    $stmt = $mysqli->prepare("DELETE FROM orders WHERE id=? AND user_id=? AND status='cart'");
    $stmt->bind_param("ii", $order_id, $user_id);
    if($stmt->execute()){
        $_SESSION['msg'] = "Item removed from cart!";
    } else {
        $_SESSION['msg'] = "Failed to remove item. Try again.";
    }
    $stmt->close();
}

header("Location: my_cart.php");
exit;
?>
