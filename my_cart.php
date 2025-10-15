<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['uid'])) die("Please login first!");
$user_id = $_SESSION['uid'];

// Fetch cart items
$stmt = $mysqli->prepare("
    SELECT o.id AS order_id, b.id AS book_id, b.title, b.author, b.cover, o.quantity, o.price 
    FROM orders o 
    JOIN books b ON o.book_id = b.id 
    WHERE o.user_id=? AND o.status='cart'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

// Prepare cart items array
$grand_total = 0;
$cart_items = [];
while ($row = $res->fetch_assoc()) {
    $row['total'] = $row['price'] * $row['quantity'];
    $grand_total += $row['total'];
    $cart_items[] = $row;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Cart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
input.qty { width: 60px; }
.card-img-top, .cart-img { cursor:pointer; }
.price-btn { cursor:pointer; color:#0d6efd; text-decoration:underline; border:none; background:none; padding:0; }
.table td, .table th { vertical-align: middle; }
</style>
</head>
<body class="p-3">
<div class="container">
<h3>🛒 My Cart</h3>
<a href="index.php" class="btn btn-secondary mb-3">Back to Store</a>

<?php if(count($cart_items) > 0): ?>
<table class="table table-bordered align-middle">
<thead>
<tr>
    <th>Cover</th>
    <th>Title</th>
    <th>Author</th>
    <th>Qty</th>
    <th>Price</th>
    <th>Total</th>
    <th>Action</th>
</tr>
</thead>
<tbody>
<?php foreach($cart_items as $row): ?>
<tr data-order="<?=$row['order_id']?>" data-price="<?=$row['price']?>">
    <td>
        <img src="uploads/<?=htmlspecialchars($row['cover'])?>" width="60" 
             data-bs-toggle="modal" data-bs-target="#cartImg<?=$row['order_id']?>">
    </td>
    <td><?=htmlspecialchars($row['title'])?></td>
    <td><?=htmlspecialchars($row['author'])?></td>
    <td>
        <!-- Added onchange AJAX -->
        <input type="number" min="1" class="form-control qty" value="<?=$row['quantity']?>" 
               onchange="updateQty(<?=$row['order_id']?>, this.value)">
    </td>
    <td>₹<?=number_format($row['price'],2)?></td>
    <td class="row-total">₹<?=number_format($row['total'],2)?></td>
    <td>
        <div class="d-flex gap-1">
            <form method="post" action="remove_from_cart.php" onsubmit="return confirm('Remove this item?');">
                <input type="hidden" name="order_id" value="<?=$row['order_id']?>">
                <button type="submit" class="btn btn-danger btn-sm">Remove</button>
            </form>
            <form method="post" action="place_orders.php">
                <button type="submit" name="place_single" value="<?=$row['order_id']?>" class="btn btn-success btn-sm">Buy Now</button>
            </form>
        </div>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
<tfoot>
<tr>
    <td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
    <td colspan="2" id="grand-total"><strong>₹<?=number_format($grand_total,2)?></strong></td>
</tr>
</tfoot>
</table>

<!-- Place all orders button -->
<form method="post" action="place_orders.php">
    <button type="submit" name="place_all" class="btn btn-primary">Place All Orders</button>
</form>

<?php else: ?>
<p>No items in your cart.</p>
<?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Update quantity via AJAX
function updateQty(orderId, qty){
    fetch('update_cart_qty.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `order_id=${orderId}&quantity=${qty}`
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success'){
            const row = document.querySelector(`tr[data-order='${orderId}']`);
            const price = parseFloat(row.dataset.price);
            const rowTotal = price * qty;
            row.querySelector('.row-total').innerText = '₹' + rowTotal.toFixed(2);

            // Update grand total
            let grand = 0;
            document.querySelectorAll('.row-total').forEach(rt=>{
                grand += parseFloat(rt.innerText.replace('₹',''));
            });
            document.getElementById('grand-total').innerHTML = '<strong>₹'+grand.toFixed(2)+'</strong>';
        } else {
            alert(data.message);
        }
    })
    .catch(err => console.error(err));
}
</script>
</body>
</html>
