<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'config.php';

$current_page = basename($_SERVER['PHP_SELF']);
$loggedIn = isset($_SESSION['uid']);

// Get cart count if logged in
$cart_count = 0;
if ($loggedIn) {
    $stmt = $mysqli->prepare("SELECT COUNT(*) FROM orders WHERE user_id=? AND status='cart'");
    $stmt->bind_param("i", $_SESSION['uid']);
    $stmt->execute();
    $stmt->bind_result($cart_count);
    $stmt->fetch();
    $stmt->close();
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top border-bottom">
  <div class="container-fluid px-4 px-md-5">
    <a class="navbar-brand fw-bold text-primary" href="index.php" style="font-size:1.6rem;">
      Book<span class="text-dark">Store</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item">
          <a class="nav-link <?= $current_page=='index.php'?'active':'' ?>" href="index.php">
            Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $current_page=='browse_books.php'?'active':'' ?>" href="browse_books.php">
            Browse Books
          </a>
        </li>

        <?php if($loggedIn): ?>
          <li class="nav-item position-relative">
            <a class="nav-link <?= $current_page=='my_cart.php'?'active':'' ?>" href="my_cart.php">
              Cart
              <?php if($cart_count>0): ?>
                <span class="badge bg-danger rounded-circle position-absolute top-0 start-100 translate-middle">
                  <?=$cart_count?>
                </span>
              <?php endif; ?>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $current_page=='my_orders.php'?'active':'' ?>" href="my_orders.php">
              Orders
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="logout.php">Logout</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link <?= $current_page=='login.php'?'active':'' ?>" href="login.php">Login</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $current_page=='register.php'?'active':'' ?>" href="register.php">Register</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<style>
/* General layout */
body {
  padding-top: 70px; /* Space for fixed navbar */
}

/* Navbar look */
.navbar {
  width: 100%;
  border-radius: 0;
  background-color: #fff !important;
  transition: all 0.3s ease;
}

/* Nav links */
.navbar-nav .nav-link {
  color: #333 !important;
  font-weight: 500;
  margin: 0 5px;
  border-radius: 6px;
  padding: 0.6rem 1rem;
  transition: background 0.2s, color 0.2s;
}

/* Hover + Active */
.navbar-nav .nav-link:hover {
  background-color: #e7f1ff;
  color: #007bff !important;
}
.navbar-nav .nav-link.active {
  background-color: #007bff;
  color: #fff !important;
}

/* Cart badge */
.navbar-nav .badge {
  font-size: 0.7rem;
  padding: 0.25em 0.45em;
}

/* Responsive padding */
@media (max-width: 768px) {
  .navbar {
    padding: 0.5rem 1rem;
  }
  .navbar-brand {
    font-size: 1.3rem;
  }
}
</style>
