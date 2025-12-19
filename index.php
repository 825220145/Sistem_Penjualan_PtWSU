<?php
// index.php (login)
session_start();
require_once __DIR__ . '/config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: pages/dashboard.php");
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // log login safely
        require_once __DIR__ . '/includes/funcs.php';
        log_activity($pdo, $user['user_id'], 'Login', 'User login');

        header("Location: pages/dashboard.php");
        exit;
    } else {
        $message = "Username atau password salah!";
    }
}
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><title>Login</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
/* minimal style */
body{background:#0f0f1a;color:#fff;font-family:Arial;display:flex;height:100vh;align-items:center;justify-content:center}
form{background:#181830;padding:24px;border-radius:12px;width:320px}
input{width:100%;padding:10px;margin:8px 0;border-radius:6px;border:none;background:#1f1f3a;color:#fff}
button{width:100%;padding:10px;background:#6b5bff;border:none;color:#fff;border-radius:6px;cursor:pointer}
.small{font-size:13px;color:#bbb}
</style>
</head>
<body>
<form method="post">
  <h2>Login</h2>
  <?php if($message): ?><div style="color:#ff9a9a"><?=$message?></div><?php endif;?>
  <input name="username" placeholder="Username" required>
  <input name="password" placeholder="Password" type="password" required>
  <button>Masuk</button>
</form>
</body>
</html>
