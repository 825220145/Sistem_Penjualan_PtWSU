<?php
session_start();
require_once '../config/database.php';
require_once '../includes/funcs.php';
require_role(['admin']); // hanya admin yang bisa kelola produk

$message = "";

// === Tambah produk ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $nama = trim($_POST['nama']);
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $stok_minimum = $_POST['stok_minimum'];
    $kategori = $_POST['kategori_id'] ?: null;

    if ($nama && $harga > 0) {
        $stmt = $pdo->prepare("INSERT INTO produk (nama_produk, harga, stok, stok_minimum, kategori_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nama, $harga, $stok, $stok_minimum, $kategori]);
        log_activity($pdo, $_SESSION['user_id'], 'Tambah Produk', $nama);
        $message = "✅ Produk berhasil ditambahkan.";
    } else {
        $message = "⚠️ Nama dan harga wajib diisi dengan benar.";
    }
}

// === Hapus produk ===
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $pdo->prepare("DELETE FROM produk WHERE produk_id=?");
    $stmt->execute([$id]);
    log_activity($pdo, $_SESSION['user_id'], 'Hapus Produk', "ID $id");
    echo "<script>alert('🗑️ Produk berhasil dihapus!');window.location='master_produk.php';</script>";
    exit;
}

// === Ambil data kategori & produk ===
$kategori = $pdo->query("SELECT * FROM kategori_produk ORDER BY nama_kategori")->fetchAll();
$produk = $pdo->query("
    SELECT p.*, k.nama_kategori 
    FROM produk p 
    LEFT JOIN kategori_produk k ON p.kategori_id = k.kategori_id 
    ORDER BY p.produk_id ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Master Produk | PT Wijaya Sakti Utama</title>
<style>
body{background:#0f0f1a;color:#fff;font-family:Poppins,sans-serif;padding:20px;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{border:1px solid #333;padding:8px;text-align:left;}
th{background:#1f1f3a;}
input,select,button{width:100%;padding:8px;margin-bottom:10px;border:none;border-radius:5px;background:#181830;color:#fff;}
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
<h2>Master Produk</h2>

<?php if($message): ?><p><?=$message?></p><?php endif; ?>

<!-- Form Tambah Produk -->
<div class="card">
<form method="post">
<label>Nama Produk:</label>
<input type="text" name="nama" required>
<label>Harga:</label>
<input type="number" name="harga" min="0" step="0.01" required>
<label>Stok:</label>
<input type="number" name="stok" min="0" required>
<label>Stok Minimum:</label>
<input type="number" name="stok_minimum" min="0" required>
<label>Kategori:</label>
<select name="kategori_id">
<option value="">-- Pilih Kategori --</option>
<?php foreach($kategori as $k): ?>
<option value="<?=$k['kategori_id']?>"><?=htmlspecialchars($k['nama_kategori'])?></option>
<?php endforeach; ?>
</select>
<button type="submit" name="tambah">Tambah Produk</button>
</form>
</div>

<!-- Tabel Produk -->
<table>
<tr>
<th>ID</th>
<th>Nama Produk</th>
<th>Harga</th>
<th>Stok</th>
<th>Stok Minimum</th>
<th>Kategori</th>
<th>Aksi</th>
</tr>
<?php if(!$produk): ?>
<tr><td colspan="7" align="center">Belum ada data produk.</td></tr>
<?php else: foreach($produk as $p): ?>
<tr>
<td><?=$p['produk_id']?></td>
<td><?=htmlspecialchars($p['nama_produk'])?></td>
<td><?=number_format($p['harga'],0,',','.')?></td>
<td><?=$p['stok']?></td>
<td><?=$p['stok_minimum']?></td>
<td><?=htmlspecialchars($p['nama_kategori'] ?: 'Tanpa Kategori')?></td>
<td>
<a href="master_produk_edit.php?id=<?=$p['produk_id']?>">✏️ Edit</a> |
<a href="?hapus=<?=$p['produk_id']?>" onclick="return confirm('Yakin hapus produk ini?')">🗑️ Hapus</a>
</td>
</tr>
<?php endforeach; endif; ?>
</table>

</body>
</html>
