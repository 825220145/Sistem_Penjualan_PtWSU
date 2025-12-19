<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }

require_once '../config/database.php';
require_once '../includes/funcs.php';
require_role(['admin','kasir']); // hanya admin & kasir bisa buka halaman stok masuk

$user_id = $_SESSION['user_id'];
$produk = $pdo->query("SELECT * FROM produk ORDER BY nama_produk ASC")->fetchAll();
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produk_id = (int)$_POST['produk_id'];
    $jumlah = (int)$_POST['jumlah'];
    $ket = trim($_POST['keterangan']);
    if ($produk_id && $jumlah > 0) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO stok_masuk (produk_id, user_id, tanggal, jumlah, keterangan) VALUES (?, ?, NOW(), ?, ?)");
            $stmt->execute([$produk_id, $user_id, $jumlah, $ket]);
            $pdo->prepare("UPDATE produk SET stok = stok + ? WHERE produk_id=?")->execute([$jumlah, $produk_id]);
            log_activity($pdo, $user_id, 'Stok Masuk', "Tambah stok $jumlah untuk produk ID $produk_id");
            $pdo->commit();
            $message = "✅ Stok berhasil ditambahkan!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "❌ Error: ".$e->getMessage();
        }
    } else $message = "⚠️ Pilih produk dan jumlah > 0!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Stok Masuk</title>
<style>
body{background:#0f0f1a;color:#fff;font-family:Poppins,sans-serif;padding:20px;}
input,select{width:100%;padding:8px;margin-bottom:10px;border:none;border-radius:5px;background:#181830;color:#fff;}
button{background:#6b5bff;border:none;padding:8px 14px;color:#fff;border-radius:6px;cursor:pointer;}
button:hover{background:#5648e5;}
</style>
</head>
<body>
<a href="dashboard.php" class="back-btn">⬅ Kembali ke Dashboard</a>
<h2>Input Stok Masuk</h2>
<?php if($message): ?><p><?=$message?></p><?php endif; ?>
<form method="post">
<label>Produk:</label>
<select name="produk_id" required>
<option value="">-- Pilih Produk --</option>
<?php foreach($produk as $p): ?>
<option value="<?=$p['produk_id']?>"><?=htmlspecialchars($p['nama_produk'])?> (Stok: <?=$p['stok']?>)</option>
<?php endforeach; ?>
</select>
<label>Jumlah:</label>
<input name="jumlah" type="number" min="1" required>
<label>Keterangan:</label>
<input name="keterangan" placeholder="Opsional">
<button type="submit">Simpan</button>
</form>
</body>
</html>
