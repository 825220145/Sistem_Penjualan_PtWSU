<?php
session_start();
require_once '../config/database.php';
require_once '../includes/funcs.php';
require_role(['admin','kasir']); // hanya kasir & admin bisa input transaksi

$user_id = $_SESSION['user_id'];
$message = "";
$show_cetak = false;
$penjualan_id = 0;

// Ambil data pelanggan dan produk
$pelanggan = $pdo->query("SELECT * FROM customer ORDER BY nama_toko ASC")->fetchAll();
$produk = $pdo->query("SELECT * FROM produk ORDER BY nama_produk ASC")->fetchAll();

// Saat form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $produk_id = $_POST['produk_id'] ?? [];
    $jumlah = $_POST['jumlah'] ?? [];

    if ($customer_id && count($produk_id) > 0) {
        try {
            $pdo->beginTransaction();

            // === Ambil nomor nota terakhir ===
            $last_nota = $pdo->query("SELECT MAX(no_nota) FROM penjualan")->fetchColumn();
            $next_nota = $last_nota ? $last_nota + 1 : 1;

            // === Hitung total transaksi ===
            $total = 0;
            foreach ($produk_id as $i => $pid) {
                $pid = (int)$pid;
                $qty = max(0, (int)$jumlah[$i]);
                if ($qty <= 0) continue;
                $stmt = $pdo->prepare("SELECT harga, stok FROM produk WHERE produk_id=?");
                $stmt->execute([$pid]);
                $row = $stmt->fetch();
                if (!$row) continue;
                if ($row['stok'] < $qty) throw new Exception("Stok produk ID $pid tidak cukup!");
                $subtotal = $row['harga'] * $qty;
                $total += $subtotal;
            }

            // === Simpan ke tabel penjualan (status otomatis Belum Lunas) ===
            $stmt = $pdo->prepare("INSERT INTO penjualan (no_nota, tanggal, total, customer_id, user_id, status_pembayaran) VALUES (?, NOW(), ?, ?, ?, 'Belum Lunas')");
            $stmt->execute([$next_nota, $total, $customer_id, $user_id]);
            $penjualan_id = $pdo->lastInsertId();

            // === Simpan ke detail_penjualan dan kurangi stok ===
            $detail = $pdo->prepare("INSERT INTO detail_penjualan (penjualan_id, produk_id, jumlah, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)");
            $update_stok = $pdo->prepare("UPDATE produk SET stok = stok - ? WHERE produk_id=?");

            foreach ($produk_id as $i => $pid) {
                $pid = (int)$pid;
                $qty = max(0, (int)$jumlah[$i]);
                if ($qty <= 0) continue;
                $stmt = $pdo->prepare("SELECT harga FROM produk WHERE produk_id=?");
                $stmt->execute([$pid]);
                $harga = $stmt->fetchColumn();
                $subtotal = $harga * $qty;
                $detail->execute([$penjualan_id, $pid, $qty, $harga, $subtotal]);
                $update_stok->execute([$qty, $pid]);
            }

            // === Catat aktivitas ===
            log_activity($pdo, $user_id, 'Transaksi Penjualan', "Nota #$next_nota (Belum Lunas)");

            $pdo->commit();
            $message = "✅ Transaksi berhasil disimpan!<br>Nomor Nota: <b>$next_nota</b> (Status: Belum Lunas)<br>Total: Rp " . number_format($total,0,',','.');
            $show_cetak = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "❌ Gagal: " . $e->getMessage();
        }
    } else {
        $message = "⚠️ Lengkapi pelanggan dan produk!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Input Penjualan | PT Wijaya Sakti Utama</title>
<style>
body{background:#0f0f1a;color:#fff;font-family:Poppins,sans-serif;padding:20px;}
input,select{width:100%;padding:8px;margin-bottom:10px;border:none;border-radius:5px;background:#181830;color:#fff;}
button{background:#6b5bff;border:none;padding:8px 14px;color:#fff;border-radius:6px;cursor:pointer;}
button:hover{background:#5648e5;}
label{display:block;margin-top:6px;}
hr{border:0.5px solid #333;margin:12px 0;}
h2{color:#b3a7ff;}
.message-box{
  background:#181830;
  padding:12px;
  border-radius:8px;
  margin-bottom:15px;
}
.btn-cetak{
  background:#5bc0de;
  color:#fff;
  border:none;
  border-radius:6px;
  padding:8px 12px;
  cursor:pointer;
  margin-top:8px;
}
.btn-cetak:hover{background:#47a8c3;}
</style>
<script>
function tambahProduk(){
  const container=document.getElementById('produkContainer');
  const clone=document.querySelector('.produk-item').cloneNode(true);
  clone.querySelectorAll('input,select').forEach(x=>x.value='');
  container.appendChild(clone);
}
</script>
</head>
<body>
<a href="dashboard.php" class="back-btn">⬅ Kembali ke Dashboard</a>
<h2>Input Penjualan</h2>
<?php if($message): ?>
<div class="message-box">
  <?=$message?>
  <?php if($show_cetak && $penjualan_id): ?>
    <br>
    <button class="btn-cetak" onclick="window.location='cetak_nota.php?penjualan_id=<?=$penjualan_id?>'">🧾 Cetak Nota (Opsional)</button>
  <?php endif; ?>
</div>
<?php endif; ?>

<form method="post">
<label>Pelanggan:</label>
<select name="customer_id" required>
<option value="">-- Pilih Pelanggan --</option>
<?php foreach($pelanggan as $p): ?>
<option value="<?=$p['customer_id']?>"><?=htmlspecialchars($p['nama_toko'])?></option>
<?php endforeach; ?>
</select>

<div id="produkContainer">
<div class="produk-item">
<label>Produk:</label>
<select name="produk_id[]" required>
<option value="">-- Pilih Produk --</option>
<?php foreach($produk as $pr): ?>
<option value="<?=$pr['produk_id']?>"><?=htmlspecialchars($pr['nama_produk'])?> (Stok: <?=$pr['stok']?>)</option>
<?php endforeach; ?>
</select>
<label>Jumlah:</label>
<input name="jumlah[]" type="number" min="1" required>
<hr>
</div>
</div>

<button type="button" onclick="tambahProduk()">+ Tambah Produk</button>

<button type="submit">Simpan Transaksi</button>
</form>
</body>
</html>
