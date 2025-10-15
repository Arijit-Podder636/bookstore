<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['uid'])) {
    http_response_code(401);
    echo json_encode(['status'=>'error','message'=>'Login required']);
    exit;
}

$user_id = $_SESSION['uid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $qty      = max(1, intval($_POST['quantity'] ?? 1));

    if ($order_id <= 0) {
        echo json_encode(['status'=>'error','message'=>'Invalid order ID']);
        exit;
    }

    $stmt = $mysqli->prepare("UPDATE orders SET quantity=? WHERE id=? AND user_id=? AND status='cart'");
    $stmt->bind_param("iii", $qty, $order_id, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['status'=>'success','quantity'=>$qty]);
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to update quantity']);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['status'=>'error','message'=>'Invalid request method']);
}
?>
