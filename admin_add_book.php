<?php
session_start();
require 'config.php';

// Only admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied: Admins only");
}

$categories = ['Fiction','Non-Fiction','Science','Romance','Children','Comics'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $category = $_POST['category'];
    $price = floatval($_POST['price']);

    // Validation
    if (empty($title) || empty($author) || empty($category) || $price <= 0) {
        die("All fields are required and price must be positive.");
    }

    // Handle file upload
    $cover = '';
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($_FILES['cover']['type'], $allowed)) {
            die("Only JPG, PNG, or WEBP files are allowed.");
        }

        $dir = "uploads/";
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $fname = time() . '_' . basename($_FILES['cover']['name']);
        move_uploaded_file($_FILES['cover']['tmp_name'], $dir . $fname);
        $cover = $fname;
    }

    // Insert into database
    $stmt = $mysqli->prepare("INSERT INTO books(title, author, category, price, cover) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssds", $title, $author, $category, $price, $cover);

    if ($stmt->execute()) {
        header("Location: admin_books.php?msg=added");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!doctype html>
<html>
<head>
    <title>Add Book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h2>Add New Book</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Title:</label>
            <input name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Author:</label>
            <input name="author" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Category:</label>
            <select name="category" class="form-control" required>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat ?>"><?= $cat ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Price:</label>
            <input name="price" type="number" step="0.01" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Cover Image:</label>
            <input type="file" name="cover" accept="image/*" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Add Book</button>
        <a href="admin_books.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
