<?php
require 'config.php';

// Admin check
if ($_SESSION['role'] !== 'admin') {
    die("Access denied. Admins only.");
}

// Validate book ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid book ID.");
}

$book_id = (int)$_GET['id'];

// Fetch book details using prepared statement
$stmt = $mysqli->prepare("SELECT * FROM books WHERE id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();
$stmt->close();

if (!$book) {
    die("Book not found.");
}

// Handle POST update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $price = floatval($_POST['price']);
    $cover = $book['cover'];

    // Validate inputs
    if ($title === '' || $author === '' || $price <= 0) {
        $error = "Please enter valid title, author, and price.";
    } else {
        // Handle cover upload
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === 0) {
            $allowedTypes = ['image/jpeg','image/png','image/gif'];
            $fileType = mime_content_type($_FILES['cover']['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                $error = "Invalid file type. Only JPG, PNG, GIF allowed.";
            } else {
                $dir = "uploads/";
                $fname = time() . '_' . basename($_FILES['cover']['name']);
                move_uploaded_file($_FILES['cover']['tmp_name'], $dir . $fname);
                $cover = $dir . $fname;
            }
        }

        if (!isset($error)) {
            $stmt = $mysqli->prepare("UPDATE books SET title=?, author=?, price=?, cover=? WHERE id=?");
            $stmt->bind_param("ssdsi", $title, $author, $price, $cover, $book_id);
            $stmt->execute();
            $stmt->close();

            header("Location: admin_books.php?msg=Book+updated+successfully");
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Book</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-3">
<?php include 'navbar.php'; ?>
<div class="container">
    <h1 class="mb-4">Edit Book</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="author" class="form-label">Author</label>
            <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Price</label>
            <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($book['price']); ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Current Cover</label><br>
            <img src="<?php echo htmlspecialchars($book['cover']); ?>" style="height:80px" class="mb-2"><br>
            <label for="cover" class="form-label">Upload New Cover</label>
            <input type="file" name="cover" id="cover" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="admin_books.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
