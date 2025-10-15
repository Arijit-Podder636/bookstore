<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$error = '';

// Redirect if already logged in
if (isset($_SESSION['uid'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';

    if ($email && $pass) {
        $stmt = $mysqli->prepare("SELECT id, password, role, name FROM users WHERE email=?");
        $stmt->bind_param("s", $email); 
        $stmt->execute();
        $stmt->bind_result($id, $hash, $role, $name);

        if ($stmt->fetch()) {
            // ✅ Support both password_hash and MD5
            if (password_verify($pass, $hash) || $hash === md5($pass)) {
                $_SESSION['uid'] = $id; 
                $_SESSION['role'] = $role; 
                $_SESSION['name'] = $name;

                // Redirect based on role
                if ($role === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            } else {
                $error = "Invalid email or password";
            }
        } else {
            $error = "Invalid email or password";
        }
        $stmt->close();
    } else {
        $error = "Please enter both email and password";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.pass-toggle { 
  cursor:pointer; position:absolute; right:10px; top:50%; transform:translateY(-50%); font-size: 18px; 
}
.position-relative { position:relative; }
</style>
</head>
<body class="p-3">
<div class="container" style="max-width:400px;">
    <h3 class="mb-4">Login</h3>

    <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?=htmlspecialchars($error)?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3 position-relative">
            <input class="form-control" name="email" type="email" placeholder="Email" required>
        </div>
        <div class="mb-3 position-relative">
            <input class="form-control" name="password" type="password" placeholder="Password" required id="loginPass">
            <span class="pass-toggle" onclick="togglePass('loginPass')" id="toggleIcon">🙈</span>
        </div>

        <p class="mb-3">
            <a href="forgot_password.php">Forgot Password?</a>
        </p>

        <button class="btn btn-primary w-100">Login</button>
        <a href="register.php" class="btn btn-link w-100 mt-2 text-center">Register</a>
    </form>
</div>

<script>
function togglePass(id){
  const input = document.getElementById(id);
  const icon = document.getElementById('toggleIcon');
  if(input.type === 'password'){
    input.type = 'text';
    icon.textContent = '👁️';
  } else {
    input.type = 'password';
    icon.textContent = '🙈';
  }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
