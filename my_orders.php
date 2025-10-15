<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['uid'])) die("Please login first!");

$user_id = $_SESSION['uid'];
$message = '';

// Handle delete request securely
if(isset($_POST['delete_id'])){
    $del_id = (int)$_POST['delete_id'];
    $stmt = $mysqli->prepare("DELETE FROM orders WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $del_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $message = "Order deleted successfully.";
}

// Fetch orders securely
$stmt = $mysqli->prepare("
    SELECT o.id, b.title, b.cover, o.quantity, o.price, o.created_at 
    FROM orders o 
    JOIN books b ON o.book_id=b.id 
    WHERE o.user_id=? 
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

// Calculate grand total
$orders = [];
$grand_total = 0;
while($o=$res->fetch_assoc()){
    $o['total'] = $o['price'] * $o['quantity'];
    $grand_total += $o['total'];
    $orders[] = $o;
}
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Orders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.order-img { height: 80px; object-fit: contain; background:#f9f9f9; cursor:pointer; }
.price-btn { cursor:pointer; color:#0d6efd; text-decoration:underline; border:none; background:none; padding:0; }
</style>
</head>
<body class="p-3">
<div class="container">
<h3>My Orders</h3>

<?php if($message): ?>
<div class="alert alert-success"><?=htmlspecialchars($message)?></div>
<?php endif; ?>

<?php if(count($orders) > 0): ?>
<table class="table table-bordered align-middle">
    <thead class="table-light">
        <tr>
            <th>S.No</th>
            <th>Book</th>
            <th>Cover</th>
            <th>Quantity</th>
            <th>Price (₹)</th>
            <th>Date & Time</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($orders as $idx => $o): ?>
        <tr>
            <td><?=$idx+1?></td>
            <td><?=htmlspecialchars($o['title'])?></td>
            <td>
                <img src="uploads/<?=htmlspecialchars($o['cover'])?>" 
                     class="order-img rounded border"
                     data-bs-toggle="modal" 
                     data-bs-target="#imgModal<?=$o['id']?>">
            </td>
            <td><?=$o['quantity']?></td>
            <td>
                <button class="price-btn" data-bs-toggle="modal" data-bs-target="#priceModal<?=$o['id']?>">
                    ₹<?=number_format($o['total'],2)?>
                </button>
            </td>
            <td><?=date("d M Y, h:i:s A", strtotime($o['created_at']))?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="delete_id" value="<?=$o['id']?>">
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this order?')">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
            <td colspan="3"><strong>₹<?=number_format($grand_total,2)?></strong></td>
        </tr>
    </tfoot>
</table>

<!-- Modals -->
<?php foreach($orders as $o): ?>
<div class="modal fade" id="imgModal<?=$o['id']?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-body p-0">
        <img src="uploads/<?=htmlspecialchars($o['cover'])?>" class="img-fluid w-100">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="priceModal<?=$o['id']?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Total Price</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Quantity: <?=$o['quantity']?></p>
        <p>Unit Price: ₹<?=number_format($o['price'],2)?></p>
        <hr>
        <p><strong>Total: ₹<?=number_format($o['total'],2)?></strong></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<?php else: ?>
<p>No orders found. <a href="index.php">Shop now</a>.</p>
<?php endif; ?>

<a href="index.php" class="btn btn-secondary mt-3">Back to Store</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
