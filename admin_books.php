<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ Require login
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit;
}

// ✅ Restrict to admins only
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die("Access denied!");
}


// Handle Delete
if (isset($_POST['delete'])) {
    $id = intval($_POST['delete_id']);
    $stmt = $mysqli->prepare("DELETE FROM books WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_books.php?msg=deleted");
    exit;
}

// Handle category filter
$category_filter = $_GET['category'] ?? '';
if ($category_filter) {
    $stmt = $mysqli->prepare("SELECT * FROM books WHERE category=? ORDER BY id DESC");
    $stmt->bind_param("s", $category_filter);
    $stmt->execute();
    $books = $stmt->get_result();
} else {
    $books = $mysqli->query("SELECT * FROM books ORDER BY id DESC");
}

// Categories for filter pills
$categories = ['Fiction','Non-Fiction','Science','Romance','Children','Comics'];
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Panel | Books</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f4f6f8; }
.table-img { height: 60px; object-fit: contain; cursor: pointer; }
.category-pill {
    display: inline-block;
    padding: 6px 12px;
    margin: 2px;
    border-radius: 50px;
    background-color: #e0e0e0;
    color: #333;
    text-decoration: none;
    transition: all 0.2s;
}
.category-pill.active, .category-pill:hover {
    background-color: #2874f0;
    color: #fff;
}

/* ✅ Floating Circular Home Button */
.floating-btn {
    position: fixed;
    top: 20px;
    right: 20px;
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background-color: #2874f0;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.25);
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 1000;
}
.floating-btn:hover {
    background-color: #0d5fe8;
    transform: scale(1.1);
}

/* Tooltip */
.floating-btn::after {
    content: "Back to Admin Panel";
    position: absolute;
    top: 65px;
    right: 50%;
    transform: translateX(50%);
    background-color: #333;
    color: #fff;
    padding: 5px 8px;
    border-radius: 5px;
    font-size: 12px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}
.floating-btn:hover::after {
    opacity: 1;
}
</style>
</head>
<body class="p-4">

<!-- ✅ Floating Button -->
<a href="admin_dashboard.php" class="floating-btn" title="Back to Admin Panel">🏠</a>

<div class="container">
    <h3>Admin Panel - Books</h3>

    <?php if(isset($_GET['msg'])): ?>
      <div class="alert alert-success text-center">
        <?= htmlspecialchars($_GET['msg']); ?> successfully!
      </div>
    <?php endif; ?>

    <!-- Add New Book Button -->
    <div class="mb-3">
        <a href="admin_add_book.php" class="btn btn-primary">+ Add New Book</a>
    </div>

    <!-- Category Pills Filter -->
    <div class="mb-3">
        <a href="admin_books.php" class="category-pill <?= $category_filter==''?'active':'' ?>">All</a>
        <?php foreach($categories as $cat): ?>
            <a href="admin_books.php?category=<?= urlencode($cat) ?>" class="category-pill <?= $category_filter==$cat?'active':'' ?>">
                <?= htmlspecialchars($cat) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Books Table -->
    <table class="table table-bordered bg-white">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Cover</th>
                <th>Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($b = $books->fetch_assoc()): ?>
            <tr>
                <td><?= $b['id'] ?></td>
                <td>
                    <?php if(!empty($b['cover'])): ?>
                        <img src="uploads/<?= htmlspecialchars($b['cover']) ?>" 
                             class="table-img" 
                             data-bs-toggle="modal" 
                             data-bs-target="#coverModal"
                             data-img="uploads/<?= htmlspecialchars($b['cover']) ?>">
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($b['title']) ?></td>
                <td><?= htmlspecialchars($b['author']) ?></td>
                <td><?= isset($b['category']) ? htmlspecialchars($b['category']) : 'N/A' ?></td>
                <td>₹<?= number_format($b['price'],2) ?></td>
                <td>
                    <a href="admin_edit_book.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="delete_id" value="<?= $b['id'] ?>">
                        <button class="btn btn-sm btn-danger" name="delete" onclick="return confirm('Delete this book?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Single Reusable Modal -->
<div class="modal fade" id="coverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body p-0">
                <img src="" class="img-fluid w-100" id="modalCoverImg" alt="Book Cover">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Change modal image dynamically when a cover is clicked
const coverModal = document.getElementById('coverModal');
coverModal.addEventListener('show.bs.modal', function (event) {
    const img = event.relatedTarget.getAttribute('data-img');
    document.getElementById('modalCoverImg').src = img;
});
</script>
</body>
</html>
