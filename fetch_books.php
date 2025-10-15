<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Require login
if (!isset($_SESSION['uid'])) {
    http_response_code(403);
    echo "Access denied";
    exit;
}

$category = $_GET['category'] ?? 'All';
$search_query = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'latest';

// Determine ORDER BY
$order_by = "id DESC"; // default latest
if ($sort === "low") $order_by = "price ASC";
if ($sort === "high") $order_by = "price DESC";

// Prepare SQL
$sql = "SELECT id, title, author, price, cover, category FROM books WHERE 1=1";
$params = [];
$types = "";

// Category filter
if ($category !== 'All') {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

// Search filter
if ($search_query !== '') {
    $sql .= " AND (title LIKE CONCAT('%',?,'%') OR author LIKE CONCAT('%',?,'%'))";
    $params[] = $search_query;
    $params[] = $search_query;
    $types .= "ss";
}

$sql .= " ORDER BY $order_by";

// Execute query
if (!empty($params)) {
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $mysqli->query($sql);
}

// Return HTML
if ($res->num_rows > 0) {
    while($book = $res->fetch_assoc()) {
        ?>
        <div class="col-md-3 col-sm-6 book-card-container" data-category="<?=htmlspecialchars($book['category'])?>">
          <div class="card book-card shadow-sm">
            <img src="uploads/<?=htmlspecialchars($book['cover'])?>" class="card-img-top book-img" alt="Book Cover">
            <div class="card-body">
              <h6 class="card-title text-truncate"><?=htmlspecialchars($book['title'])?></h6>
              <p class="card-text text-muted mb-1 small"><?=htmlspecialchars($book['author'])?></p>
              <p class="fw-bold text-success mb-2">₹<?=number_format($book['price'],2)?></p>
              <div class="btn-group-custom">
                <form method="post" action="place_orders.php" class="flex-fill">
                  <input type="hidden" name="book_id" value="<?=$book['id']?>">
                  <input type="hidden" name="action" value="buy">
                  <button type="submit" class="btn btn-buy">Buy at ₹<?=number_format($book['price'],2)?></button>
                </form>
                <form method="post" action="place_orders.php" class="flex-fill">
                  <input type="hidden" name="book_id" value="<?=$book['id']?>">
                  <input type="hidden" name="action" value="cart">
                  <button type="submit" class="btn btn-cart">Add to Cart</button>
                </form>
              </div>
            </div>
          </div>
        </div>
        <?php
    }
} else {
    echo '<div class="col-12 text-center"><p class="text-muted">No books found.</p></div>';
}
?>
