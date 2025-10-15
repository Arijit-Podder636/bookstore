<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("Access denied!");
}


// Fetch quick stats
$totalBooks   = $mysqli->query("SELECT COUNT(*) AS c FROM books")->fetch_assoc()['c'];
$totalUsers   = $mysqli->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$totalOrders  = $mysqli->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
$totalRevenue = $mysqli->query("
  SELECT COALESCE(SUM(b.price * o.quantity), 0) AS total
  FROM orders o
  JOIN books b ON o.book_id = b.id
")->fetch_assoc()['total'];


?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
        background-color: #f8f9fa;
    }
    .card {
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .nav-link {
        color: white !important;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">📊 Admin Panel</a>
    <div>
      <a href="admin_books.php" class="btn btn-outline-light btn-sm me-2">Manage Books</a>
      <a href="admin_orders.php" class="btn btn-outline-light btn-sm me-2">View Orders</a>
      <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <h2 class="text-center mb-4">Welcome, Admin 👋</h2>
  
  <div class="row g-4 text-center">
    <div class="col-md-3">
      <div class="card text-bg-primary">
        <div class="card-body">
          <h4><?= $totalBooks ?></h4>
          <p>Total Books</p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card text-bg-success">
        <div class="card-body">
          <h4><?= $totalUsers ?></h4>
          <p>Total Users</p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card text-bg-warning">
        <div class="card-body">
          <h4><?= $totalOrders ?></h4>
          <p>Total Orders</p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card text-bg-danger">
        <div class="card-body">
          <h4>₹<?= number_format($totalRevenue, 2) ?></h4>
          <p>Total Revenue</p>
        </div>
      </div>
    </div>
  </div>

  <div class="text-center mt-5">
    <a href="admin_books.php" class="btn btn-primary btn-lg me-3">📚 Manage Books</a>
    <a href="admin_orders.php" class="btn btn-success btn-lg">📦 View Orders</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
