<?php 
require 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit;
}

// Escape session values for security
$userName = htmlspecialchars($_SESSION['name']);
$userRole = $_SESSION['role'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-3">
    <div class="container">
        <h1 class="mb-4">Welcome, <?php echo $userName; ?>!</h1>

        <div class="mb-3">
            <a href="index.php" class="btn btn-secondary me-2 mb-2">Browse Books</a>
            <a href="my_orders.php" class="btn btn-info me-2 mb-2">My Orders</a>

            <?php if ($userRole === 'admin'): ?>
                <a href="admin_books.php" class="btn btn-warning me-2 mb-2">Manage Books</a>
                <a href="admin_orders.php" class="btn btn-dark me-2 mb-2">All Orders</a>
            <?php endif; ?>

            <a href="logout.php" class="btn btn-danger mb-2">Logout</a>
        </div>
    </div>
</body>
</html>
