<?php 
session_start();
require_once '../config/database.php';
require_once '../includes/funcs.php';
require_role(['admin','owner']); // hanya admin & owner bisa lihat laporan

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$status = $_GET['status'] ?? '';

$where = [];
$params = [];

if ($start) { $where[] = "pj.tanggal >= ?"; $params[] = $start . " 00:00:00"; }
if ($end)   { $where[] = "pj.tanggal <= ?"; $params[] = $end . " 23:59:59"; }
if ($status && in_array($status, ['Lunas','Belum Lunas'])) { 
    $where[] = "pj.status_pembayaran = ?"; 
    $params[] = $status;
}

$sql = "
SELECT 
    pj.penjualan_id,
    pj.no_nota,
    pj.tanggal,
    pj.total AS total_awal,
    COALESCE(SUM(rd.nilai_retur),0) AS total_retur,
    (pj.total - COALESCE(SUM(rd.nilai_retur),0)) AS total_akhir,
    pj.status_pembayaran,
    c.nama_toko,
    u.username,
    pb.metode
FROM penjualan pj
LEFT JOIN customer c ON pj.customer_id = c.customer_id
LEFT JOIN users u ON pj.user_id = u.user_id
LEFT JOIN pembayaran pb ON pb.penjualan_id = pj.penjualan_id
LEFT JOIN retur r ON pj.penjualan_id = r.penjualan_id
LEFT JOIN retur_detail rd ON r.retur_id = rd.retur_id
";

if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " GROUP BY pj.penjualan_id ORDER BY pj.tanggal DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$penjualan = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Penjualan Lengkap</title>
<style>
body{background:#0f0f1a;color:#fff;font-family:Poppins,sans-serif;padding:20px;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{border:1px solid #333;padding:8px;text-align:left;}
th{background:#1f1f3a;}
h2{color:#b3a7ff;}
a{color:#b3a7ff;text-decoration:none;}
button{background:#6b5bff;border:none;padding:8px 12px;color:#fff;border-radius:6px;cursor:pointer;}
button:hover{background:#5648e5;}
hr{border:0.5px solid #333;margin:20px 0;}
.status-lunas{color:#7dff7d;font-weight:bold;}
.status-belum{color:#ff8080;font-weight:bold;}
.total-retur{color:#ffb3b3;}
.total-akhir{color:#a7ffb3;font-weight:bold;}
</style>
</head>
<body>
<a href="dashboard.php" class="back-btn">⬅ Kembali ke Dashboard</a>
<h2>Laporan Penjualan Lengkap</h2>

<form method="get">
<label>Mulai:</label><input type="date" name="start" value="<?=htmlspecialchars($start)?>">
<label>Sampai:</label><input type="date" name="end" value="<?=htmlspecialchars($end)?>">
<label>Status Pembayaran:</label>
<select name="status">
<option value="">Semua</option>
<option value="Lunas" <?=($status=='Lunas'?'selected':'')?>>Lunas</option>
<option value="Belum Lunas" <?=($status=='Belum Lunas'?'selected':'')?>>Belum Lunas</option>
</select>
<button type="submit">Tampilkan</button>
</form>

<p>
<a href="export_penjualan_excel.php?start=<?=urlencode($start)?>&end=<?=urlencode($end)?>&status=<?=urlencode($status)?>">Export ke Excel (CSV)</a> |
<a href="export_penjualan_pdf.php?start=<?=urlencode($start)?>&end=<?=urlencode($end)?>&status=<?=urlencode($status)?>">Export ke PDF</a>
</p>

<?php if(!$penjualan): ?>
<p>Tidak ada data penjualan.</p>
<?php else: foreach($penjualan as $pj): ?>
<h3>No Nota #<?=str_pad($pj['no_nota'],3,'0',STR_PAD_LEFT)?> | <?=date('d M Y', strtotime($pj['tanggal']))?></h3>
<p>
<b>Pelanggan:</b> <?=htmlspecialchars($pj['nama_toko'] ?: '-')?><br>
<b>Kasir:</b> <?=htmlspecialchars($pj['username'])?><br>
<b>Metode Pembayaran:</b> <?=htmlspecialchars($pj['metode'] ?: '-')?><br>
<b>Status:</b> 
<span class="<?=($pj['status_pembayaran']=='Lunas'?'status-lunas':'status-belum')?>">
    <?=htmlspecialchars($pj['status_pembayaran'])?>
</span>
</p>

<table>
<tr>
    <th>Produk</th>
    <th>Harga</th>
    <th>Jumlah</th>
    <th>Subtotal</th>
</tr>

<?php
// =============================
// 1. DETAIL TRANSAKSI AWAL
// =============================
$detail = $pdo->prepare("
    SELECT d.*, p.nama_produk 
    FROM detail_penjualan d
    JOIN produk p ON p.produk_id = d.produk_id
    WHERE d.penjualan_id = ?
");
$detail->execute([$pj['penjualan_id']]);

$total_awal = 0;

foreach ($detail as $d):
    $total_awal += $d['subtotal']; // hitung total awal
?>
<tr>
    <td><?= htmlspecialchars($d['nama_produk']) ?></td>
    <td><?= rupiah($d['harga_satuan']) ?></td>
    <td><?= $d['jumlah'] ?></td>
    <td><?= rupiah($d['subtotal']) ?></td>
</tr>
<?php endforeach; ?>

<tr>
    <td colspan="3" align="right"><b>Total Awal</b></td>
    <td><b><?= rupiah($total_awal) ?></b></td>
</tr>

<?php
// =============================
// 2. DETAIL RETUR PER PRODUK
// =============================
$retur = $pdo->prepare("
    SELECT rd.*, p.nama_produk 
    FROM retur_detail rd
    JOIN retur r ON rd.retur_id = r.retur_id
    JOIN produk p ON rd.produk_id = p.produk_id
    WHERE r.penjualan_id = ?
");
$retur->execute([$pj['penjualan_id']]);

$total_retur = 0;

foreach ($retur as $rt):
    $total_retur += $rt['nilai_retur'];
?>
<tr>
    <td style="color:#ff9999;">Retur: <?= htmlspecialchars($rt['nama_produk']) ?></td>
    <td><?= rupiah($rt['nilai_retur'] / $rt['jumlah_retur']) ?></td>
    <td><?= $rt['jumlah_retur'] ?></td>
    <td style="color:#ff8080;">- <?= rupiah($rt['nilai_retur']) ?></td>
</tr>
<?php endforeach; ?>

<?php if ($total_retur > 0): ?>
<tr>
    <td colspan="3" align="right"><b>Retur (-)</b></td>
    <td class="total-retur"><b><?= rupiah($total_retur) ?></b></td>
</tr>
<?php endif; ?>

<?php
$total_akhir = $total_awal - $total_retur;
?>

<tr>
    <td colspan="3" align="right"><b>Total Akhir</b></td>
    <td class="total-akhir"><b><?= rupiah($total_akhir) ?></b></td>
</tr>

</table>
<hr>
<?php endforeach; endif; ?>
</body>
</html>
