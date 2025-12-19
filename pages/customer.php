<?php
session_start();
require_once '../config/database.php';
require_once '../includes/funcs.php';
require_role(['admin','kasir']); // admin & kasir boleh kelola pelanggan

$message = "";

// === Tambah pelanggan baru ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $nama = trim($_POST['nama_toko']);
    $alamat = trim($_POST['alamat']);
    $telepon = trim($_POST['telepon']);

    if ($nama) {
        $stmt = $pdo->prepare("INSERT INTO customer (nama_toko, alamat, telepon) VALUES (?, ?, ?)");
        $stmt->execute([$nama, $alamat, $telepon]);
        log_activity($pdo, $_SESSION['user_id'], 'Tambah Pelanggan', $nama);
        $message = "✅ Pelanggan baru berhasil ditambahkan.";
    } else {
        $message = "⚠️ Nama pelanggan wajib diisi.";
    }
}

// === Hapus pelanggan ===
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM customer WHERE customer_id = ?");
        $stmt->execute([$id]);
        log_activity($pdo, $_SESSION['user_id'], 'Hapus Pelanggan', "ID $id");
        echo "<script>alert('🗑️ Data pelanggan berhasil dihapus!');window.location='customer.php';</script>";
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('❌ Tidak bisa menghapus pelanggan, kemungkinan sudah digunakan di transaksi!');window.location='customer.php';</script>";
        exit;
    }
}

// === Ambil semua data pelanggan ===
$pelanggan = $pdo->query("SELECT * FROM customer ORDER BY customer_id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Master Pelanggan | PT Wijaya Sakti Utama</title>
<style>
body{background:#0f0f1a;color:#fff;font-family:Poppins,sans-serif;padding:20px;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{border:1px solid #333;padding:8px;text-align:left;}
th{background:#1f1f3a;}
input,textarea,button{width:100%;padding:8px;margin-bottom:10px;border:none;border-radius:5px;background:#181830;color:#fff;}
button{background:#6b5bff;cursor:pointer;}
button:hover{background:#5648e5;}
.card{background:#181830;padding:15px;border-radius:10px;margin-bottom:20px;}
h2{color:#b3a7ff;}
a{color:#b3a7ff;text-decoration:none;}
a:hover{color:#fff;}
</style>
</head>
<body>
<a href="dashboard.php" class="back-btn">⬅ Kembali ke Dashboard</a>
<h2>Master Pelanggan</h2>

<?php if($message): ?><p><?=$message?></p><?php endif; ?>

<!-- Form Tambah Pelanggan -->
<div class="card">
<form method="post">
<label>Nama Toko:</label>
<input type="text" name="nama_toko" required>

<label>Alamat:</label>
<textarea name="alamat" rows="2"></textarea>

<label>Nomor Telepon:</label>
<input type="text" name="telepon">

<button type="submit" name="tambah">Tambah Pelanggan</button>
</form>
</div>

<!-- Tabel Pelanggan -->
<table>
<tr>
<th>ID</th>
<th>Nama Toko</th>
<th>Alamat</th>
<th>Telepon</th>
<th>Aksi</th>
</tr>
<?php if(!$pelanggan): ?>
<tr><td colspan="5" align="center">Belum ada data pelanggan.</td></tr>
<?php else: foreach($pelanggan as $c): ?>
<tr>
<td><?=$c['customer_id']?></td>
<td><?=htmlspecialchars($c['nama_toko'])?></td>
<td><?=htmlspecialchars($c['alamat'] ?: '-')?></td>
<td><?=htmlspecialchars($c['telepon'] ?: '-')?></td>
<td>
<a href="customer_edit.php?id=<?=$c['customer_id']?>">✏️ Edit</a> |
<a href="?hapus=<?=$c['customer_id']?>" onclick="return confirm('Yakin ingin menghapus pelanggan ini?')">🗑️ Hapus</a>
</td>
</tr>
<?php endforeach; endif; ?>
</table>

</body>
</html>
