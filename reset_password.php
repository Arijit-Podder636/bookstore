<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$token = $_GET['token'] ?? '';
$toastMessage = '';
$toastType = '';

if (!$token) die("Invalid reset link.");

// Validate token
$stmt = $mysqli->prepare("SELECT id, reset_expires FROM users WHERE reset_token=?");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->bind_result($user_id, $expires);
$stmt->fetch();
$stmt->close();

if (!$user_id || strtotime($expires) < time()) die("Reset link expired or invalid.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (!$password || !$confirm) {
        $toastMessage = "Please fill all fields.";
        $toastType = "danger";
    } elseif ($password !== $confirm) {
        $toastMessage = "Passwords do not match.";
        $toastType = "danger";
    } elseif (strlen($password) < 6) {
        $toastMessage = "Password must be at least 6 characters.";
        $toastType = "danger";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("UPDATE users SET password=?, reset_token=NULL, reset_expires=NULL WHERE id=?");
        $stmt->bind_param("si", $hash, $user_id);
        if ($stmt->execute()) {
            $toastMessage = "Password updated successfully!";
            $toastType = "success";
        } else {
            $toastMessage = "Error updating password. Try again.";
            $toastType = "danger";
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html>
<head>
<title>Reset Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#f8f9fa; }
.card { max-width:400px; margin:auto; margin-top:100px; padding:20px; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1); }
#toast {
    position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
    z-index: 9999; min-width: 250px; max-width: 90%;
    padding: 0.8rem 1rem; border-radius: 8px; display: none; font-size: 0.95rem;
    box-shadow: 0 3px 12px rgba(0,0,0,0.2); color: #fff;
}
#toast.success { background-color: #28a745; }
#toast.danger { background-color: #dc3545; }
#toast.show { display: block; animation: slideDown 0.5s forwards, fadeOut 0.5s 2.5s forwards; }
@keyframes slideDown { from { transform: translate(-50%, -100%); } to { transform: translate(-50%, 0); } }
@keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }

.input-group-text { cursor: pointer; user-select: none; }
</style>
</head>
<body>

<div id="toast"></div>

<div class="card">
    <h4 class="text-center mb-3">Reset Password</h4>
    <form method="post" id="resetForm">
        <div class="mb-3 input-group">
            <input type="password" id="password" name="password" class="form-control" placeholder="New password" required>
            <span class="input-group-text" onclick="togglePassword('password', this)">👁️</span>
        </div>
        <div class="mb-3 input-group">
            <input type="password" id="confirm" name="confirm" class="form-control" placeholder="Confirm password" required>
            <span class="input-group-text" onclick="togglePassword('confirm', this)">👁️</span>
        </div>
        <button class="btn btn-primary w-100">Reset Password</button>
        <a href="login.php" class="d-block mt-3 text-center">Back to Login</a>
    </form>
</div>

<script>
const password = document.getElementById('password');
const confirm = document.getElementById('confirm');
const toast = document.getElementById('toast');

function showToast(message, type) {
    toast.innerText = message;
    toast.className = `show ${type}`;
    setTimeout(() => { toast.className = ''; }, 3000);
}

// Live password match check
function checkMatch() {
    if (password.value && confirm.value) {
        if (password.value === confirm.value) {
            showToast("Passwords match!", "success");
        } else {
            showToast("Passwords do not match.", "danger");
        }
    }
}

password.addEventListener('input', checkMatch);
confirm.addEventListener('input', checkMatch);

// Show PHP toast on page load if any
<?php if($toastMessage): ?>
showToast("<?= addslashes($toastMessage) ?>", "<?= $toastType ?>");
<?php endif; ?>

// Toggle password visibility
function togglePassword(fieldId, icon) {
    const field = document.getElementById(fieldId);
    if (field.type === "password") {
        field.type = "text";
        icon.innerText = "🙈";
    } else {
        field.type = "password";
        icon.innerText = "👁️";
    }
}
</script>

</body>
</html>
