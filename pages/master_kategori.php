<?php
session_start();
require_once '../config/database.php';
require_once '../includes/funcs.php';
require_role(['admin']); // hanya admin boleh kelola kategori

$message = "";

// === Tambah kategori ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $nama = trim($_POST['nama']);
    $desk = trim($_POST['deskripsi']);

    if ($nama) {
        $stmt = $pdo->prepare("INSERT INTO kategori_produk (nama_kategori, deskripsi) VALUES (?, ?)");
        $stmt->execute([$nama, $desk]);
        log_activity($pdo, $_SESSION['user_id'], 'Tambah Kategori', $nama);
        $message = "✅ Kategori berhasil ditambahkan.";
    } else {
        $message = "⚠️ Nama kategori wajib diisi.";
    }
}

// === Hapus kategori ===
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM kategori_produk WHERE kategori_id = ?");
        $stmt->execute([$id]);
        log_activity($pdo, $_SESSION['user_id'], 'Hapus Kategori', "ID $id");
        echo "<script>alert('🗑️ Kategori berhasil dihapus!');window.location='master_kategori.php';</script>";
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "<script>alert('❌ Kategori tidak bisa dihapus karena masih digunakan oleh produk!');window.location='master_kategori.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan: " . addslashes($e->getMessage()) . "');window.location='master_kategori.php';</script>";
        }
        exit;
    }
}

// === Ambil semua data kategori ===
$kategori = $pdo->query("SELECT * FROM kategori_produk ORDER BY kategori_id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Master Kategori | PT Wijaya Sakti Utama</title>
<style>
body{background:#0f0f1a;color:#fff;font-family:Poppins,sans-serif;padding:20px;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{border:1px solid #333;padding:8px;text-align:left;}
th{background:#1f1f3a;}
a{color:#b3a7ff;text-decoration:none;}
a:hover{color:#fff;}
input,textarea,button{width:100%;padding:8px;margin-bottom:10px;border:none;border-radius:5px;background:#181830;color:#fff;}
button{background:#6b5bff;cursor:pointer;}
button:hover{background:#5648e5;}
.card{background:#181830;padding:15px;border-radius:10px;margin-bottom:20px;}
h2{color:#b3a7ff;}
</style>
</head>
<body>
<a href="dashboard.php" class="back-btn">⬅ Kembali ke Dashboard</a>
<h2>Master Kategori Produk</h2>

<?php if($message): ?><p><?=$message?></p><?php endif; ?>

<div class="card">
<form method="post">
<label>Nama Kategori:</label>
<input type="text" name="nama" required>
<label>Deskripsi:</label>
<textarea name="deskripsi" rows="3"></textarea>
<button type="submit" name="tambah">Tambah Kategori</button>
</form>
</div>

<table>
<tr>
<th>ID</th>
<th>Nama Kategori</th>
<th>Deskripsi</th>
<th>Aksi</th>
</tr>
<?php if(!$kategori): ?>
<tr><td colspan="4" align="center">Belum ada data kategori.</td></tr>
<?php else: foreach($kategori as $k): ?>
<tr>
<td><?=$k['kategori_id']?></td>
<td><?=htmlspecialchars($k['nama_kategori'])?></td>
<td><?=htmlspecialchars($k['deskripsi'] ?: '-')?></td>
<td>
<a href="master_kategori_edit.php?id=<?=$k['kategori_id']?>">✏️ Edit</a> |
<a href="?hapus=<?=$k['kategori_id']?>" onclick="return confirm('Yakin ingin menghapus kategori ini?')">🗑️ Hapus</a>
</td>
</tr>
<?php endforeach; endif; ?>
</table>

</body>
</html>
