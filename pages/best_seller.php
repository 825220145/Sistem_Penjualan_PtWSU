<?php 
session_start();
require_once '../config/database.php';
require_once '../includes/funcs.php';

require_role(['admin', 'owner', 'kasir']);

// Ambil tanggal mulai & sampai dari GET buat tampilan form
$startInput = isset($_GET['start']) ? $_GET['start'] : '';
$endInput   = isset($_GET['end'])   ? $_GET['end']   : '';

// Untuk query, kalau kosong pakai batas luas
$startRange = $startInput !== '' ? $startInput : '0000-01-01';
$endRange   = $endInput   !== '' ? $endInput   : '9999-12-31';

$params = array(
    ':start_pj'     => $startRange,
    ':end_pj'       => $endRange,
    ':start_retur'  => $startRange,
    ':end_retur'    => $endRange
);


$sql = "
    SELECT 
        p.produk_id,
        p.nama_produk,

        /* Total penjualan pada range tanggal */
        COALESCE(SUM(
            CASE 
                WHEN DATE(pj.tanggal) BETWEEN :start_pj AND :end_pj
                THEN dp.jumlah
                ELSE 0
            END
        ), 0) AS total_terjual,

        /* Total retur pada range tanggal */
        COALESCE((
            SELECT SUM(rd.jumlah_retur)
            FROM retur_detail rd
            JOIN retur r ON rd.retur_id = r.retur_id
            WHERE rd.produk_id = p.produk_id
              AND DATE(r.tanggal_retur) BETWEEN :start_retur AND :end_retur
        ), 0) AS total_retur

    FROM produk p
    LEFT JOIN detail_penjualan dp ON dp.produk_id = p.produk_id
    LEFT JOIN penjualan pj ON pj.penjualan_id = dp.penjualan_id
    GROUP BY p.produk_id;
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total bersih di PHP
foreach ($data as $i => $row) {
    $data[$i]['total_bersih'] = $row['total_terjual'] - $row['total_retur'];
}

// Sort berdasarkan total_bersih DESC
usort($data, function ($a, $b) {
    return $b['total_bersih'] <=> $a['total_bersih'];
});
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Produk Terlaris</title>
    <style>
        body {
            background: #0f0f1a;
            color: #fff;
            font-family: Poppins, sans-serif;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
        }
        th {
            background: #1f1f3a;
        }
        h2 {
            color: #b3a7ff;
        }
        .low {
            color: #ff8080;
            font-weight: bold;
        }
        .best {
            color: #7dff7d;
            font-weight: bold;
        }
        a {
            color: #b3a7ff;
            text-decoration: none;
        }
        .filter-box {
            margin-top: 15px;
            padding: 10px;
            background: #151526;
            border-radius: 8px;
            display: inline-block;
        }
        .filter-box label {
            margin-right: 4px;
        }
        .filter-box input[type="date"] {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #333;
            background: #0f0f1a;
            color: #fff;
            margin-right: 8px;
        }
        .filter-box button {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            background: #b3a7ff;
            cursor: pointer;
            margin-left: 5px;
        }
        .reset-link {
            margin-left: 10px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <a href="dashboard.php">⬅ Kembali ke Dashboard</a>

    <h2>Produk Terlaris</h2>

    <!-- FILTER RANGE TANGGAL (gaya biar sama seperti fitur lain) -->
    <form method="get" class="filter-box">
        <label>Mulai:</label>
        <input type="date" name="start" value="<?= htmlspecialchars($startInput) ?>">
        <label>Sampai:</label>
        <input type="date" name="end" value="<?= htmlspecialchars($endInput) ?>">
        <button type="submit">Tampilkan</button>
        <?php if ($startInput !== '' || $endInput !== ''): ?>
            <a href="<?= $_SERVER['PHP_SELF']; ?>" class="reset-link">Reset</a>
        <?php endif; ?>
    </form>

    <table>
        <tr>
            <th>Produk</th>
            <th>Total Terjual</th>
            <th>Total Retur</th>
            <th>Jumlah Bersih</th>
        </tr>

        <?php if (empty($data)): ?>
            <tr>
                <td colspan="4" style="text-align:center;">Belum ada data.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($data as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                <td><?= $row['total_terjual'] ?></td>
                <td class="low"><?= $row['total_retur'] ?></td>
                <td class="best"><?= $row['total_bersih'] ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>

</body>
</html>
