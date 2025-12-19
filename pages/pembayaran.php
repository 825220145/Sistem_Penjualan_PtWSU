<?php
session_start();
require_once '../config/database.php';
require_once '../includes/funcs.php';
require_role(['admin','kasir']); // hanya kasir & admin bisa input pembayaran

$user_id = $_SESSION['user_id'];
$message = "";

// Ambil semua transaksi belum lunas
$penjualan = $pdo->query("
    SELECT no_nota, penjualan_id, tanggal, total, status_pembayaran 
    FROM penjualan 
    WHERE status_pembayaran = 'Belum Lunas'
    ORDER BY tanggal DESC
")->fetchAll();

// Saat form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $penjualan_id = (int)($_POST['penjualan_id'] ?? 0);
    $metode = trim($_POST['metode'] ?? '');
    $jumlah = (float)($_POST['jumlah'] ?? 0);

    if ($penjualan_id && $metode && $jumlah > 0) {
        try {
            $pdo->beginTransaction();

            // Simpan ke tabel pembayaran
            $stmt = $pdo->prepare("INSERT INTO pembayaran (penjualan_id, tanggal_bayar, metode, jumlah_bayar) VALUES (?, NOW(), ?, ?)");
            $stmt->execute([$penjualan_id, $metode, $jumlah]);

            // Cek apakah total sudah lunas
            $cek = $pdo->prepare("SELECT total FROM penjualan WHERE penjualan_id=?");
            $cek->execute([$penjualan_id]);
            $total_penjualan = $cek->fetchColumn();

            if ($jumlah >= $total_penjualan) {
                $pdo->prepare("UPDATE penjualan SET status_pembayaran='Lunas' WHERE penjualan_id=?")->execute([$penjualan_id]);
            }

            log_activity($pdo, $user_id, 'Pembayaran', "Bayar nota ID $penjualan_id sebesar $jumlah ($metode)");
            $pdo->commit();
            $message = "✅ Pembayaran berhasil disimpan!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "❌ Gagal: " . $e->getMessage();
        }
    } else {
        $message = "⚠️ Lengkapi data pembayaran!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Input Pembayaran | PT Wijaya Sakti Utama</title>
<style>
body{background:#0f0f1a;color:#fff;font-family:Poppins,sans-serif;padding:20px;}
select,input,button{width:100%;padding:8px;margin-bottom:10px;border:none;border-radius:5px;background:#181830;color:#fff;}
button{background:#6b5bff;cursor:pointer;}
button:hover{background:#5648e5;}
.card{background:#181830;padding:15px;border-radius:10px;margin-top:20px;}
</style>
</head>
<body>
<a href="dashboard.php" class="back-btn">⬅ Kembali ke Dashboard</a>
<h2>Input Pembayaran</h2>
<?php if($message): ?><p><?=$message?></p><?php endif; ?>

<form method="post">
<label>Pilih Nota Belum Lunas:</label>
<select name="penjualan_id" required>
<option value="">-- Pilih No Nota --</option>
<?php foreach($penjualan as $p): ?>
<option value="<?=$p['penjualan_id']?>">
No <?=$p['no_nota']?> - <?=date('d M Y', strtotime($p['tanggal']))?> (Total: <?=number_format($p['total'],0,',','.')?>)
</option>
<?php endforeach; ?>
</select>

<label>Metode Pembayaran:</label>
<select name="metode" required>
<option value="">-- Pilih Metode --</option>
<option value="Cash">Cash</option>
<option value="Transfer">Transfer</option>
<option value="Giro">Giro</option>
</select>

<label>Jumlah Bayar (Rp):</label>
<input type="number" name="jumlah" min="1" required>

<button type="submit">Simpan Pembayaran</button>
</form>

<div class="card">
<h3>Daftar Nota Belum Lunas</h3>
<table border="1" cellpadding="6" cellspacing="0" width="100%">
<tr style="background:#1f1f3a;">
<th>No Nota</th><th>Tanggal</th><th>Total</th><th>Status</th>
</tr>
<?php if(!$penjualan): ?>
<tr><td colspan="4" align="center">Semua sudah lunas 🎉</td></tr>
<?php else: foreach($penjualan as $p): ?>
<tr>
<td><?=str_pad($p['no_nota'],3,'0',STR_PAD_LEFT)?></td>
<td><?=date('d M Y', strtotime($p['tanggal']))?></td>
<td><?=number_format($p['total'],0,',','.')?></td>
<td><?=$p['status_pembayaran']?></td>
</tr>
<?php endforeach; endif; ?>
</table>
</div>
</body>
</html>
