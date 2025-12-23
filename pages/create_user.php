<?php
// pages/create_user.php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/funcs.php';

require_role(['admin']);

$message = '';
$error = '';
$username = '';
$role = 'kasir';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_password = $_POST['admin_password'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'kasir';

    if (!$admin_password) {
        $error = 'Masukkan password admin untuk konfirmasi.';
    } elseif (!$username) {
        $error = 'Username wajib diisi.';
    } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
        $error = 'Username harus 3-50 karakter, hanya huruf, angka, titik, underscore, strip.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Password dan konfirmasi tidak cocok.';
    } elseif (!in_array($role, ['admin','kasir','owner'])) {
        $error = 'Peran tidak valid.';
    } else {
        // cek re-authenticate admin
        $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $admin_hash = $stmt->fetchColumn();

        if (!$admin_hash || !password_verify($admin_password, $admin_hash)) {
            $error = 'Password admin salah — tidak dapat membuat akun.';
        } else {
            // untuk cek unique username
            $s = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $s->execute([$username]);
            if ($s->fetchColumn() > 0) {
                $error = 'Username sudah digunakan, pilih username lain.';
            } else {

                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $ins = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                try {
                    $ins->execute([$username, $hashed, $role]);

                    if (function_exists('log_activity')) {
                        log_activity($pdo, $_SESSION['user_id'], 'Tambah User', "Buat user: {$username}, role: {$role}");
                    }

                    $message = "✅ Akun <b>{$username}</b> dengan role <b>{$role}</b> berhasil dibuat.";
 
                    $username = '';
                    $role = 'kasir';
                } catch (PDOException $e) {
                    $error = "Terjadi kesalahan saat menyimpan: " . htmlspecialchars($e->getMessage());
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Buat Akun Baru | PT Wijaya Sakti Utama</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body{background:#0f0f1a;color:#fff;font-family:Poppins, sans-serif;padding:20px;}
.card{background:#181830;padding:16px;border-radius:10px;max-width:720px;margin:auto;}
h2{color:#b3a7ff;margin-top:0;}
label{display:block;margin-top:10px;font-weight:600;color:#d8d8ff;}
input,select{width:100%;padding:10px;border-radius:8px;border:none;background:#0f1625;color:#fff;margin-top:6px;}
small{color:#bfbfbf;}
.btn{margin-top:12px;background:#6b5bff;color:#fff;padding:10px 14px;border-radius:8px;border:none;cursor:pointer;}
.msg{background:#15303a;padding:10px;border-radius:8px;margin-bottom:12px;}
.err{background:#3b1a1a;padding:10px;border-radius:8px;margin-bottom:12px;color:#ffdcdc;}
.help{color:#9fa3c0;font-size:0.9rem;}
</style>
</head>
<body>
<div class="card">
    <a href="dashboard.php" class="back-btn">⬅ Kembali ke Dashboard</a>
  <h2>Buat Akun Baru</h2>
  <p class="help">Hanya admin yang dapat membuat akun.</p>

  <?php if ($message): ?><div class="msg"><?= $message ?></div><?php endif; ?>
  <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <form method="post">
    <label>Konfirmasi Password Admin (wajib)</label>
    <input type="password" name="admin_password" placeholder="Masukkan password admin untuk konfirmasi" required>

    <label>Username</label>
    <input type="text" name="username" value="<?= htmlspecialchars($username ?? '') ?>" placeholder="username (3-50, tanpa spasi)" required>

    <label>Password</label>
    <input type="password" name="password" placeholder="Password baru (min 6 karakter)" required>

    <label>Konfirmasi Password</label>
    <input type="password" name="confirm_password" placeholder="Ulangi password" required>

    <label>Role</label>
    <select name="role" required>
      <option value="kasir" <?= ($role=='kasir')?'selected':''; ?>>Kasir</option>
      <option value="admin" <?= ($role=='admin')?'selected':''; ?>>Admin</option>
      <option value="owner" <?= ($role=='owner')?'selected':''; ?>>Owner</option>
    </select>

    <button class="btn" type="submit">Buat Akun</button>
  </form>

  <p class="help" style="margin-top:12px;">Catatan: username tidak boleh duplikat</p>
</div>
</body>
</html>

