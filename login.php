<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/funcs.php';

if (isset($_SESSION['user_id'])) {
    header("Location: pages/dashboard.php");
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        log_activity($pdo, $user['user_id'], 'Login', 'Berhasil login');
        header("Location: pages/dashboard.php");
        exit;
    } else {
        $message = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login | PT Wijaya Sakti Utama</title>
<style>
body{background:#0f0f1a;color:#fff;font-family:Arial;display:flex;justify-content:center;align-items:center;height:100vh;}
form{background:#181830;padding:30px;border-radius:10px;width:300px;text-align:center;}
input{width:100%;padding:10px;margin-bottom:10px;border:none;border-radius:6px;background:#1f1f3a;color:#fff;}
button{width:100%;background:#6b5bff;color:#fff;padding:10px;border:none;border-radius:6px;cursor:pointer;}
button:hover{background:#5648e5;}
</style>
</head>
<body>
<form method="post">
<h2>Login Sistem</h2>
<?php if ($message): ?><p style="color:#ff9a9a;"><?=$message?></p><?php endif; ?>
<input name="username" placeholder="Username" required>
<input name="password" placeholder="Password" type="password" required>
<button>Masuk</button>
</form>
</body>
</html>
