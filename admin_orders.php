<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ Admin check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied: Admins only");
}

// Handle Delete
if (isset($_POST['delete'])) {
    $id = intval($_POST['delete_id']);
    $stmt = $mysqli->prepare("DELETE FROM orders WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_orders.php?msg=deleted");
    exit;
}

// ✅ Handle search
$search = trim($_GET['search'] ?? '');

$sql = "SELECT 
            o.*, 
            u.name AS user_name, 
            u.email AS user_email,
            b.title AS book_title, 
            b.author AS book_author, 
            b.cover AS book_cover, 
            b.price AS book_price, 
            b.category AS book_category
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN books b ON o.book_id = b.id
        WHERE 1=1";

if ($search !== '') {
    $searchEscaped = $mysqli->real_escape_string($search);
    $sql .= " AND (
        u.name LIKE '%$searchEscaped%' OR 
        u.email LIKE '%$searchEscaped%' OR 
        b.title LIKE '%$searchEscaped%' OR 
        b.author LIKE '%$searchEscaped%' OR 
        b.category LIKE '%$searchEscaped%' OR 
        o.id LIKE '%$searchEscaped%' OR 
        o.user_id LIKE '%$searchEscaped%' OR 
        o.book_id LIKE '%$searchEscaped%' OR 
        o.status LIKE '%$searchEscaped%'
    )";
}

$sql .= " ORDER BY o.id DESC";
$orders = $mysqli->query($sql);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Panel | Orders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f4f6f8; }
.table-img { height: 60px; object-fit: contain; cursor: pointer; }
.modal-img { max-width: 100%; max-height: 400px; }
.clickable { cursor: pointer; text-decoration: underline; color: #2874f0; }

/* ✅ Back to Admin Panel Button */
.top-right-btn {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1050;
  background-color: #2874f0;
  color: #fff;
  border: none;
  border-radius: 30px;
  padding: 10px 20px;
  font-weight: 500;
  transition: all 0.3s ease;
  box-shadow: 0 3px 8px rgba(0,0,0,0.25);
  text-decoration: none;
}
.top-right-btn:hover {
  background-color: #0d5fe8;
  transform: translateY(-2px);
}
</style>
</head>
<body class="p-4">

<!-- ✅ Back to Admin Panel Button -->
<a href="admin_dashboard.php" class="top-right-btn">🔙 Back to Admin Panel</a>

<div class="container">
    <h3>Admin Panel - Orders</h3>

    <?php if(isset($_GET['msg'])): ?>
      <div class="alert alert-success text-center">
        <?= htmlspecialchars($_GET['msg']); ?> successfully!
      </div>
    <?php endif; ?>

    <!-- 🔍 Unified Search Bar -->
    <form class="d-flex mb-3" method="get" style="max-width: 400px;">
        <input class="form-control me-2"
               type="search"
               name="search"
               placeholder="Search by user name, book title, ID, or status..."
               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <button class="btn btn-primary">Search</button>
        <?php if (!empty($_GET['search'])): ?>
          <a href="admin_orders.php" class="btn btn-outline-secondary ms-2">Clear</a>
        <?php endif; ?>
    </form>

    <!-- Orders Table -->
    <table class="table table-bordered bg-white">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Book</th>
                <th>Cover</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($o = $orders->fetch_assoc()): ?>
            <tr>
                <td><?= $o['id'] ?></td>

                <!-- User -->
                <td>
                    <span class="clickable" data-bs-toggle="modal" data-bs-target="#userModal<?= $o['user_id'] ?>">
                        <?= htmlspecialchars($o['user_name']) ?> (ID: <?= $o['user_id'] ?>)
                    </span>
                </td>

                <!-- Book -->
                <td>
                    <span class="clickable" data-bs-toggle="modal" data-bs-target="#bookModal<?= $o['book_id'] ?>">
                        <?= htmlspecialchars($o['book_title']) ?> (ID: <?= $o['book_id'] ?>)
                    </span>
                </td>

                <!-- Book Cover -->
                <td>
                    <img src="uploads/<?= htmlspecialchars($o['book_cover']) ?>" class="table-img" 
                        data-bs-toggle="modal" data-bs-target="#bookModal<?= $o['book_id'] ?>">
                </td>

                <td><?= $o['quantity'] ?></td>
                <td>₹<?= number_format($o['quantity'] * $o['book_price'], 2) ?></td>
                <td><?= $o['created_at'] ?></td>

                <td>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="delete_id" value="<?= $o['id'] ?>">
                        <button class="btn btn-sm btn-danger" name="delete" onclick="return confirm('Delete this order?')">Delete</button>
                    </form>
                </td>
            </tr>

            <!-- Book Modal -->
            <div class="modal fade" id="bookModal<?= $o['book_id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?= htmlspecialchars($o['book_title']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="uploads/<?= htmlspecialchars($o['book_cover']) ?>" class="modal-img mb-3">
                            <p><strong>Author:</strong> <?= htmlspecialchars($o['book_author']) ?></p>
                            <p><strong>Category:</strong> <?= htmlspecialchars($o['book_category']) ?></p>
                            <p><strong>Price:</strong> ₹<?= number_format($o['book_price'],2) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Modal -->
            <div class="modal fade" id="userModal<?= $o['user_id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?= htmlspecialchars($o['user_name']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p><strong>User ID:</strong> <?= $o['user_id'] ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($o['user_email']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
