<?php
session_start();
require 'config.php';

// --- Admin Access Check ---
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Only admin can view. <a href='dashboard.php'>Back</a>");
}

// --- Fetch Users ---
$result = $mysqli->query("SELECT id, name, email, role, profile_pic, created_at FROM users ORDER BY id DESC");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>All Users</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.img-thumb { width:50px; height:50px; border-radius:6px; object-fit:cover; }
</style>
</head>
<body class="p-3">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>All Users</h3>
        <a href="add_user_form.php" class="btn btn-sm btn-success">Add User</a>
    </div>
    <table class="table table-striped align-middle">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Photo</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td>
                    <?php if ($row['profile_pic']): ?>
                        <img src="<?= htmlspecialchars($row['profile_pic']) ?>" class="img-thumb">
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['role']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td class="d-flex gap-1">
                    <a href="edit_user_form.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="delete_user.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this user?');" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
<?php
$result->free();
$mysqli->close();
?>
