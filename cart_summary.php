<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['uid'])) {
    echo json_encode(['error' => 'not_logged_in']);
    exit;
}

$user_id = $_SESSION['uid'];

$stmt = $mysqli->prepare("SELECT SUM(quantity) as total_qty, SUM(quantity*price) as total_price FROM orders WHERE user_id=? AND status='cart'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

$total_items = $row['total_qty'] ?? 0;
$total_amount = number_format((float)($row['total_price'] ?? 0), 2, '.', '');

echo json_encode(['items' => $total_items, 'amount' => $total_amount]);
?>
