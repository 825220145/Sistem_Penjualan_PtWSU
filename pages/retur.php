<?php
session_start();
require_once '../config/database.php';
require_once '../includes/funcs.php';
require_role(['admin','kasir']);
date_default_timezone_set('Asia/Jakarta');

$user_id = $_SESSION['user_id'];
$message = "";

// Saat form retur dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $penjualan_id = (int)$_POST['penjualan_id'];
    $alasan = trim($_POST['alasan']);
    $produk_id = $_POST['produk_id'] ?? [];
    $jumlah_retur = $_POST['jumlah_retur'] ?? [];

    try {
        $pdo->beginTransaction();

        // Simpan retur utama
        $stmt = $pdo->prepare("INSERT INTO retur (penjualan_id, alasan, tanggal_retur) VALUES (?, ?, NOW())");
        $stmt->execute([$penjualan_id, $alasan]);
        $retur_id = $pdo->lastInsertId();

        // Loop produk retur
        $total_retur = 0;
        $insertDetail = $pdo->prepare("INSERT INTO retur_detail (retur_id, produk_id, jumlah_retur, nilai_retur) VALUES (?, ?, ?, ?)");
        $updateStok = $pdo->prepare("UPDATE produk SET stok = stok + ? WHERE produk_id=?");

        foreach ($produk_id as $i => $pid) {
            $pid = (int)$pid;
            $qty = (int)$jumlah_retur[$i];
            if ($qty <= 0) continue;

            // Ambil harga dari detail_penjualan
            $stmt = $pdo->prepare("SELECT harga_satuan FROM detail_penjualan WHERE penjualan_id=? AND produk_id=?");
            $stmt->execute([$penjualan_id, $pid]);
            $harga = $stmt->fetchColumn();

            $nilai = $harga * $qty;
            $insertDetail->execute([$retur_id, $pid, $qty, $nilai]);
            $updateStok->execute([$qty, $pid]);
            $total_retur += $nilai;
        }

        // Kurangi total penjualan di tabel penjualan
        $pdo->prepare("UPDATE penjualan SET total = total - ? WHERE penjualan_id=?")->execute([$total_retur, $penjualan_id]);

        // Catat aktivitas
        log_activity($pdo, $user_id, 'Retur Penjualan', "Nota #$penjualan_id dengan nilai retur Rp " . number_format($total_retur, 0, ',', '.'));

        $pdo->commit();
        $message = "✅ Retur berhasil disimpan dan laporan penjualan telah diperbarui.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "❌ Gagal menyimpan retur: " . $e->getMessage();
    }
}

// Ambil daftar nota (no_nota)
$nota = $pdo->query("SELECT penjualan_id, no_nota FROM penjualan ORDER BY penjualan_id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Input Retur | PT Wijaya Sakti Utama</title>
<style>
body{background:#0f0f1a;color:#fff;font-family:Poppins,sans-serif;padding:20px;}
input,select,textarea{width:100%;padding:8px;margin-bottom:10px;border:none;border-radius:5px;background:#181830;color:#fff;}
button{background:#6b5bff;border:none;padding:8px 14px;color:#fff;border-radius:6px;cursor:pointer;}
button:hover{background:#5648e5;}
label{display:block;margin-top:6px;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{border:1px solid #333;padding:6px;text-align:left;}
th{background:#1f1f3a;}
h2{color:#b3a7ff;}
</style>
<script>
function loadProduk() {
    const nota = document.getElementById('penjualan_id').value;
    if(!nota) return;
    fetch('retur_get_produk.php?penjualan_id='+nota)
      .then(res=>res.text())
      .then(html=>{
        document.getElementById('produkTable').innerHTML = html;
      });
}
</script>
</head>
<body>
<a href="dashboard.php" class="back-btn">⬅ Kembali ke Dashboard</a>
<h2>Input Retur Penjualan</h2>
<?php if($message): ?><p><?=$message?></p><?php endif; ?>

<form method="post">
<label>Pilih Nomor Nota:</label>
<select name="penjualan_id" id="penjualan_id" onchange="loadProduk()" required>
<option value="">-- Pilih Nota --</option>
<?php foreach($nota as $n): ?>
<option value="<?=$n['penjualan_id']?>">Nota #<?=$n['no_nota']?></option>
<?php endforeach; ?>
</select>

<div id="produkTable">
  <p style="color:#aaa;">Pilih nomor nota untuk menampilkan produk...</p>
</div>

<label>Alasan Retur:</label>
<textarea name="alasan" rows="3" required></textarea>

<button type="submit">Simpan Retur</button>
</form>
</body>
</html>
