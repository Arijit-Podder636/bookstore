<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ Restrict to admins based on role
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("Access denied!");
}


$id = intval($_GET['id'] ?? 0);
if($id <= 0) die("Invalid book ID");

// Fetch book details
$stmt = $mysqli->prepare("SELECT * FROM books WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$res = $stmt->get_result();
$book = $res->fetch_assoc();
$stmt->close();

if(!$book) die("Book not found");

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title    = trim($_POST['title']);
    $author   = trim($_POST['author']);
    $category = $_POST['category'];
    $price    = floatval($_POST['price']);
    
    // Handle cover upload
    $cover = $book['cover'];
    if(isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK){
        $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
        $cover = uniqid() . "." . $ext;
        move_uploaded_file($_FILES['cover']['tmp_name'], "uploads/$cover");
    }

    $stmt = $mysqli->prepare("UPDATE books SET title=?, author=?, category=?, price=?, cover=? WHERE id=?");
    $stmt->bind_param("sssdsi", $title, $author, $category, $price, $cover, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_books.php");
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Book | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h3>Edit Book</h3>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($book['title']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Author</label>
            <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($book['author']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Category</label>
            <select name="category" class="form-control" required>
                <?php 
                $categories = ['Fiction','Non-Fiction','Science','Romance','Children','Comics'];
                $currentCategory = $book['category'] ?? '';
                foreach($categories as $cat):
                ?>
                <option value="<?= $cat ?>" <?= $currentCategory == $cat ? 'selected' : '' ?>><?= $cat ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Price</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= $book['price'] ?>" required>
        </div>
        <div class="mb-3">
            <label>Cover Image</label>
            <input type="file" name="cover" class="form-control" accept="image/*">
            <?php if($book['cover']): ?>
            <!-- Thumbnail -->
            <img src="uploads/<?= htmlspecialchars($book['cover']) ?>" style="height:80px; margin-top:5px; cursor:pointer;" data-bs-toggle="modal" data-bs-target="#coverModal">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-success">Update Book</button>
        <a href="admin_books.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<!-- Modal for Cover Image -->
<div class="modal fade" id="coverModal" tabindex="-1" aria-labelledby="coverModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-body p-0">
        <img src="uploads/<?= htmlspecialchars($book['cover']) ?>" class="img-fluid w-100" alt="Book Cover">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
