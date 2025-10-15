<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$message = '';
$type = ''; // alert type: 'success', 'danger', 'info'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email) {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id);
            $stmt->fetch();

            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt2 = $mysqli->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE id=?");
            $stmt2->bind_param("ssi", $token, $expires, $user_id);
            $stmt2->execute();
            $stmt2->close();

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $folder = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $reset_link = "{$protocol}://{$host}{$folder}/reset_password.php?token={$token}";

            $message = "We’ve sent you a password reset link. <a href='$reset_link' class='fw-bold'>Click here</a> to reset your password.";
            $type = 'success';
        } else {
            $message = "We couldn’t find an account with that email.";
            $type = 'danger';
        }
        $stmt->close();
    } else {
        $message = "Please enter your email address.";
        $type = 'warning';
    }
}
?>
<!doctype html>
<html>
<head>
<title>Forgot Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.reset-link { word-break: break-all; word-wrap: break-word; display: inline-block; max-width: 100%; }
.alert { font-size: 0.95rem; border-radius: 8px; padding: 0.8rem 1rem; }
</style>
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:400px;">
    <div class="card shadow-sm p-4">
        <h3 class="mb-3 text-center">Forgot Password</h3>

        <?php if($message): ?>
            <div class="alert alert-<?= $type ?> d-flex align-items-center" role="alert">
                <?php if($type==='success'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:24px;height:24px;margin-right:8px;" fill="green" viewBox="0 0 16 16">
                        <path d="M16 2L6 14l-6-6 1.5-1.5L6 11l8.5-8.5z"/>
                    </svg>
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:24px;height:24px;margin-right:8px;" fill="red" viewBox="0 0 16 16">
                        <path d="M8 1.5A6.5 6.5 0 1 1 1.5 8 6.508 6.508 0 0 1 8 1.5zm0 1A5.5 5.5 0 1 0 13.5 8 5.507 5.507 0 0 0 8 2.5zM7.25 4h1.5v4h-1.5V4zm0 5h1.5v1.5h-1.5V9z"/>
                    </svg>
                <?php endif; ?>
                <div><?= $message ?></div>
            </div>
        <?php endif; ?>

        <form method="post" class="mt-3">
            <input type="email" name="email" class="form-control mb-3" placeholder="Enter your email" required>
            <button class="btn btn-primary w-100">Send Reset Link</button>
            <a href="login.php" class="d-block mt-3 text-center text-decoration-none">Back to Login</a>
        </form>
    </div>
</div>
</body>
</html>
