<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/funcs.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$username = $_SESSION['username'];
$role     = ucfirst($_SESSION['role']);

// =====================
// RINGKASAN DATA
// =====================
$total_produk    = $pdo->query("SELECT COUNT(*) FROM produk")->fetchColumn();
$total_pelanggan = $pdo->query("SELECT COUNT(*) FROM customer")->fetchColumn();
$total_penjualan = $pdo->query("SELECT COUNT(*) FROM penjualan")->fetchColumn();
$stok_menipis    = $pdo->query("SELECT COUNT(*) FROM produk WHERE stok < stok_minimum")->fetchColumn();

// =====================
// FILTER TANGGAL
// =====================
$start = $_GET['start'] ?? '';
$end   = $_GET['end'] ?? '';

$where = "";
$params = [];

// Jika user pilih tanggal
if ($start) { $where .= " AND tanggal >= ?"; $params[] = $start . " 00:00:00"; }
if ($end)   { $where .= " AND tanggal <= ?"; $params[] = $end   . " 23:59:59"; }

// Default 30 hari
if (!$start && !$end) {
    $where .= " AND tanggal >= CURDATE() - INTERVAL 30 DAY";
}

// Tentukan apakah grafik tampil per JAM atau per HARI
$single_day = ($start && $end && $start == $end);

// PER JAM (jika 1 hari)
if ($single_day) {
    $sql_chart = "
        SELECT DATE_FORMAT(tanggal, '%H:%i') AS label, SUM(total) AS total
        FROM penjualan
        WHERE 1=1 $where
        GROUP BY HOUR(tanggal), MINUTE(tanggal)
        ORDER BY tanggal ASC
    ";
}
// PER HARI (default)
else {
    $sql_chart = "
        SELECT DATE(tanggal) AS label, SUM(total) AS total
        FROM penjualan
        WHERE 1=1 $where
        GROUP BY DATE(tanggal)
        ORDER BY label ASC
    ";
}

$stmt = $pdo->prepare($sql_chart);
$stmt->execute($params);
$chart = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buat label & dataset
$labels = [];
$totals = [];
foreach ($chart as $row) {
    $labels[] = $row['label'];
    $totals[] = (float)$row['total'];
}

// =====================
// PRODUK STOK MENIPIS
// =====================
$low_stock_products = $pdo->query("
    SELECT p.*, k.nama_kategori
    FROM produk p
    LEFT JOIN kategori_produk k ON p.kategori_id = k.kategori_id
    WHERE stok < stok_minimum
    ORDER BY stok ASC
")->fetchAll();

// =====================
// PENJUALAN TERBARU
// =====================
$penjualan_recent = $pdo->query("
    SELECT pj.*, c.nama_toko, u.username
    FROM penjualan pj
    LEFT JOIN customer c ON pj.customer_id = c.customer_id
    LEFT JOIN users u ON pj.user_id = u.user_id
    ORDER BY pj.tanggal DESC
    LIMIT 5
")->fetchAll();

// =====================
// STOK MASUK TERBARU
// =====================
$stok_recent = $pdo->query("
    SELECT sm.*, p.nama_produk, u.username
    FROM stok_masuk sm
    LEFT JOIN produk p ON sm.produk_id = p.produk_id
    LEFT JOIN users u ON sm.user_id = u.user_id
    ORDER BY sm.tanggal DESC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="../assets/css/style.css">

<style>
/* semua style tetap seperti dashboard kamu sebelumnya */
body{background:#0f0f1a;color:#fff;font-family:Poppins,sans-serif;margin:0;}
.sidebar{position:fixed;top:0;left:0;height:100%;width:230px;background:#181830;padding:20px;overflow-y:auto;}
.sidebar a{display:block;color:#b3a7ff;padding:8px;margin:6px 0;text-decoration:none;border-radius:6px;}
.sidebar a:hover{background:rgba(107,91,255,0.2);}
.content{margin-left:250px;padding:20px;}
.topbar{display:flex;justify-content:flex-end;gap:12px;margin-bottom:10px;}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-top:10px;}
.card{background:#181830;border-radius:12px;padding:15px;text-align:center;}
.chart-box{background:#181830;padding:16px;border-radius:12px;margin-top:20px;}
.table{width:100%;border-collapse:collapse;margin-top:10px;}
.table th,.table td{border:1px solid #333;padding:8px;}
.btn{background:#6b5bff;color:#fff;border:none;padding:8px 12px;border-radius:6px;cursor:pointer;}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h3>📦 Menu Utama</h3>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="penjualan.php">🧾 Input Penjualan</a>
    <a href="pembayaran.php">💳 Pembayaran</a>
    <a href="retur.php">↩️ Retur</a>
    <a href="best_seller.php">⭐ Produk Terlaris</a>
    <a href="stok_masuk.php">📥 Stok Masuk</a>
    <hr style="border-color:#333;">
    <a href="master_produk.php">📦 Master Produk</a>
    <a href="master_kategori.php">🏷 Master Kategori</a>
    <a href="customer.php">👥 Master Pelanggan</a>
    <?php if ($_SESSION['role'] === 'admin'): ?>
  <a href="create_user.php">👤 Buat Akun</a>
<?php endif; ?>

    <hr style="border-color:#333;">
    <a href="laporan_penjualan.php">📈 Laporan Penjualan</a>
    <a href="laporan_stok.php">📦 Laporan Stok</a>
    <hr style="border-color:#333;">
    <a href="log_aktivitas.php">🕓 Log Aktivitas</a>
    <a href="../logout.php" style="background:#6b5bff;color:#fff;padding:8px;text-align:center;border-radius:6px;">🚪 Logout</a>
</div>

<!-- CONTENT -->
<div class="content">

<div class="topbar">
    <div style="color:#b3a7ff;font-weight:600;">Hi, <?= $username ?> (<?= $role ?>)</div>
</div>

<h2 style="text-align:center;">Dashboard Sistem Penjualan</h2>

<!-- GRID RINGKASAN -->
<div class="grid">
    <div class="card"><h3><?= $total_produk ?></h3>Total Produk</div>
    <div class="card"><h3><?= $total_pelanggan ?></h3>Total Pelanggan</div>
    <div class="card"><h3><?= $total_penjualan ?></h3>Total Penjualan</div>
    <div class="card"><h3><?= $stok_menipis ?></h3>Stok Menipis</div>
</div>

<!-- FILTER RINGKAS -->
<div class="chart-box">
    <h3 style="color:#b3a7ff;">Filter Grafik Penjualan</h3>

    <form method="get" style="display:flex;gap:10px;align-items:center;">
        <div>
            <label>Dari:</label><br>
            <input type="date" name="start" value="<?= $start ?>">
        </div>
        <div>
            <label>Sampai:</label><br>
            <input type="date" name="end" value="<?= $end ?>">
        </div>
        <button class="btn" style="height:40px;margin-top:18px;">Terapkan</button>
    </form>

    <canvas id="chartPenjualan" style="margin-top:20px;"></canvas>
</div>

<!-- STOK MENIPIS -->
<div class="chart-box">
<h3 style="color:#b3a7ff;">Produk Stok Menipis</h3>
<table class="table">
<tr><th>ID</th><th>Produk</th><th>Kategori</th><th>Stok</th><th>Min</th></tr>
<?php if(!$low_stock_products): ?>
<tr><td colspan="5" style="text-align:center;">Semua stok aman</td></tr>
<?php else: foreach($low_stock_products as $p): ?>
<tr>
    <td><?= $p['produk_id'] ?></td>
    <td><?= $p['nama_produk'] ?></td>
    <td><?= $p['nama_kategori'] ?></td>
    <td style="color:#ff7f7f;font-weight:bold;"><?= $p['stok'] ?></td>
    <td><?= $p['stok_minimum'] ?></td>
</tr>
<?php endforeach; endif; ?>
</table>
</div>

<!-- ROW BERSEBELAHAN -->
<div style="display:flex;gap:20px;margin-top:20px;flex-wrap:wrap;">

    <!-- PENJUALAN TERBARU -->
    <div class="chart-box" style="flex:1;min-width:300px;">
        <h3 style="color:#b3a7ff;">Penjualan Terbaru</h3>
        <table class="table">
            <tr><th>ID</th><th>Tanggal</th><th>Pelanggan</th><th>Total</th></tr>
            <?php foreach($penjualan_recent as $pj): ?>
            <tr>
                <td><?= $pj['penjualan_id'] ?></td>
                <td><?= $pj['tanggal'] ?></td>
                <td><?= $pj['nama_toko'] ?></td>
                <td><?= rupiah($pj['total']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- STOK MASUK TERBARU -->
    <div class="chart-box" style="flex:1;min-width:300px;">
        <h3 style="color:#b3a7ff;">Stok Masuk Terbaru</h3>
        <table class="table">
            <tr><th>ID</th><th>Tanggal</th><th>Produk</th><th>Jumlah</th></tr>
            <?php foreach($stok_recent as $sm): ?>
            <tr>
                <td><?= $sm['stok_id'] ?></td>
                <td><?= $sm['tanggal'] ?></td>
                <td><?= $sm['nama_produk'] ?></td>
                <td><?= $sm['jumlah'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

</div>

</div>

<script>
new Chart(document.getElementById('chartPenjualan'), {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: "Total Penjualan",
            data: <?= json_encode($totals) ?>,
            borderColor: "#6b5bff",
            backgroundColor: "rgba(107,91,255,0.2)",
            fill:true,
            tension:0.3
        }]
    }
});
</script>

</body>
</html>
