<?php
session_start();
require_once '../config/database.php';
require_once '../includes/funcs.php';
require_role(['admin','owner']);

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$where = [];
$params = [];

if ($start) { $where[] = "sm.tanggal >= ?"; $params[] = $start . " 00:00:00"; }
if ($end)   { $where[] = "sm.tanggal <= ?"; $params[] = $end . " 23:59:59"; }

$sql = "SELECT sm.stok_id, sm.tanggal, p.nama_produk, sm.jumlah, sm.keterangan, u.username
        FROM stok_masuk sm
        LEFT JOIN produk p ON sm.produk_id=p.produk_id
        LEFT JOIN users u ON sm.user_id=u.user_id";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY sm.tanggal DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Stok Masuk Lengkap</title>
<style>
body{background:#0f0f1a;color:#fff;font-family:Poppins,sans-serif;padding:20px;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{border:1px solid #333;padding:8px;text-align:left;}
th{background:#1f1f3a;}
h2{color:#b3a7ff;}
button{background:#6b5bff;border:none;padding:8px 12px;color:#fff;border-radius:6px;cursor:pointer;}
</style>
</head>
<body>
<a href="dashboard.php" class="back-btn">⬅ Kembali ke Dashboard</a>
<h2>Laporan Stok Masuk</h2>
<form method="get">
<label>Mulai:</label><input type="date" name="start" value="<?=htmlspecialchars($start)?>">
<label>Sampai:</label><input type="date" name="end" value="<?=htmlspecialchars($end)?>">
<button type="submit">Tampilkan</button>
</form>

<p>
<a href="laporan_stok_export_csv.php?start=<?=urlencode($start)?>&end=<?=urlencode($end)?>">Export ke Excel (CSV)</a> |
<a href="laporan_stok_export_pdf.php?start=<?=urlencode($start)?>&end=<?=urlencode($end)?>">Export ke PDF</a>
</p>

<table>
<tr><th>ID</th><th>Tanggal</th><th>Produk</th><th>Jumlah</th><th>Keterangan</th><th>User</th></tr>
<?php foreach($rows as $r): ?>
<tr>
<td><?=$r['stok_id']?></td>
<td><?=$r['tanggal']?></td>
<td><?=htmlspecialchars($r['nama_produk'])?></td>
<td><?=$r['jumlah']?></td>
<td><?=htmlspecialchars($r['keterangan'])?></td>
<td><?=htmlspecialchars($r['username'])?></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>
