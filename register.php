<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (!$name || !$email || !$pass) {
        $message = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email address!";
    } else {
        // Check if email already exists
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = "Email already registered!";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt2 = $mysqli->prepare("INSERT INTO users(name,email,password) VALUES(?,?,?)");
            $stmt2->bind_param("sss", $name, $email, $hash);
            if ($stmt2->execute()) {
                $_SESSION['msg'] = "Registration successful! Please login.";
                header("Location: login.php");
                exit;
            } else {
                $message = "Database error: ".$mysqli->error;
            }
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html>
<head>
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-3">
<div class="container">
    <h3>Register</h3>

    <?php if($message): ?>
        <div class="alert alert-warning"><?=$message?></div>
    <?php endif; ?>

    <form method="post">
        <input class="form-control mb-2" name="name" placeholder="Name" required>
        <input class="form-control mb-2" type="email" name="email" placeholder="Email" required>
        <input class="form-control mb-2" type="password" name="password" placeholder="Password" required>
        <button class="btn btn-primary">Register</button>
        <a href="login.php" class="ms-2">Login</a>
    </form>
</div>
</body>
</html>
