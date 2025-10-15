<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['uid'])) {
    die("Please login first!");
}
$user_id = $_SESSION['uid'];

// --- Buy single item (from cart row) ---
if (isset($_POST['place_single'])) {
    $order_id = intval($_POST['place_single']);

    // Check if the order exists and belongs to the user
    $stmt = $mysqli->prepare("SELECT id FROM orders WHERE id=? AND user_id=? AND status='cart'");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        die("Order not found or already purchased.");
    }
    $stmt->close();

    // Update order status to 'ordered'
    $stmt = $mysqli->prepare("UPDATE orders SET status='ordered', created_at=NOW() WHERE id=?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    header("Location: my_orders.php");
    exit;
}

// --- Place all items in cart ---
if (isset($_POST['place_all'])) {
    $stmt = $mysqli->prepare("UPDATE orders SET status='ordered', created_at=NOW() WHERE user_id=? AND status='cart'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: my_orders.php");
    exit;
}

// --- Backward compatibility for Buy Now / Add to Cart from books page ---
if (isset($_POST['book_id']) && isset($_POST['action'])) {
    $book_id = intval($_POST['book_id']);
    $qty     = intval($_POST['qty'] ?? 1);
    $action  = $_POST['action'];

    // Fetch book price
    $stmt = $mysqli->prepare("SELECT price FROM books WHERE id=?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $stmt->bind_result($price);
    if (!$stmt->fetch()) die("Book not found!");
    $stmt->close();

    if ($action === "cart") {
        // Add to cart logic
        $stmt = $mysqli->prepare("SELECT id, quantity FROM orders WHERE user_id=? AND book_id=? AND status='cart'");
        $stmt->bind_param("ii", $user_id, $book_id);
        $stmt->execute();
        $stmt->bind_result($order_id, $old_qty);

        if ($stmt->fetch()) {
            $stmt->close();
            $new_qty = $old_qty + $qty;
            $stmt = $mysqli->prepare("UPDATE orders SET quantity=? WHERE id=?");
            $stmt->bind_param("ii", $new_qty, $order_id);
            $stmt->execute();
        } else {
            $stmt->close();
            $stmt = $mysqli->prepare("INSERT INTO orders (user_id, book_id, quantity, price, status, created_at) VALUES (?,?,?,?, 'cart', NOW())");
            $stmt->bind_param("iiid", $user_id, $book_id, $qty, $price);
            $stmt->execute();
        }
        header("Location: my_cart.php");
        exit;
    }

    if ($action === "buy") {
        $stmt = $mysqli->prepare("INSERT INTO orders (user_id, book_id, quantity, price, status, created_at) VALUES (?,?,?,?, 'ordered', NOW())");
        $stmt->bind_param("iiid", $user_id, $book_id, $qty, $price);
        $stmt->execute();
        header("Location: my_orders.php");
        exit;
    }
}
?>
