<?php
session_start();
require_once '../config/database.php';
require_once '../includes/funcs.php';
require_role(['admin','owner']); 

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=laporan_penjualan_" . date('Ymd_His') . ".csv");
header("Pragma: no-cache");
header("Expires: 0");

$start = $_GET['start'] ?? '';
$end   = $_GET['end'] ?? '';
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
    pj.status_pembayaran,
    c.nama_toko,
    u.username,
    pb.metode
FROM penjualan pj
LEFT JOIN customer c ON pj.customer_id = c.customer_id
LEFT JOIN users u ON pj.user_id = u.user_id
LEFT JOIN pembayaran pb ON pb.penjualan_id = pj.penjualan_id
";

if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY pj.penjualan_id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data_penjualan = $stmt->fetchAll(PDO::FETCH_ASSOC);

$out = fopen('php://output', 'w');

// HEADER CSV
fputcsv($out, [
    'No Nota', 'Tanggal', 'Pelanggan', 'Kasir', 'Metode Pembayaran',
    'Produk', 'Harga Satuan', 'Jumlah', 'Subtotal',
    'Retur Produk', 'Jumlah Retur', 'Nilai Retur',
    'Total Awal', 'Total Retur', 'Total Akhir', 'Status Pembayaran'
]);

foreach ($data_penjualan as $pj) {

    // DETAIL PENJUALAN
    $detail = $pdo->prepare("
        SELECT d.*, p.nama_produk 
        FROM detail_penjualan d
        JOIN produk p ON p.produk_id = d.produk_id
        WHERE d.penjualan_id = ?
    ");
    $detail->execute([$pj['penjualan_id']]);
    $detail_list = $detail->fetchAll(PDO::FETCH_ASSOC);

    // DETAIL RETUR
    $retur = $pdo->prepare("
        SELECT rd.*, p.nama_produk
        FROM retur_detail rd
        JOIN retur r ON rd.retur_id = r.retur_id
        JOIN produk p ON rd.produk_id = p.produk_id
        WHERE r.penjualan_id = ?
    ");
    $retur->execute([$pj['penjualan_id']]);
    $retur_list = $retur->fetchAll(PDO::FETCH_ASSOC);

    // HITUNG TOTAL AWAL
    $total_awal = 0;
    foreach ($detail_list as $d) {
        $total_awal += $d['subtotal'];
    }

    // HITUNG TOTAL RETUR
    $total_retur = 0;
    foreach ($retur_list as $rt) {
        $total_retur += $rt['nilai_retur'];
    }

    $total_akhir = $total_awal - $total_retur;

    // MASUKKAN KE CSV
    foreach ($detail_list as $d) {
        fputcsv($out, [
            str_pad($pj['no_nota'], 3, '0', STR_PAD_LEFT),
            $pj['tanggal'],
            $pj['nama_toko'],
            $pj['username'],
            $pj['metode'] ?: '-',

            $d['nama_produk'],
            $d['harga_satuan'],
            $d['jumlah'],
            $d['subtotal'],

            "", "", "", // retur diisi setelah ini
            $total_awal,
            $total_retur,
            $total_akhir,
            $pj['status_pembayaran']
        ]);
    }

    // TAMBAHKAN DATA RETUR TERPISAH
    foreach ($retur_list as $rt) {
        fputcsv($out, [
            str_pad($pj['no_nota'], 3, '0', STR_PAD_LEFT),
            $pj['tanggal'],
            $pj['nama_toko'],
            $pj['username'],
            $pj['metode'] ?: '-',

            "", "", "", "", // kolom produk kosong

            "RETUR: ".$rt['nama_produk'],
            $rt['jumlah_retur'],
            $rt['nilai_retur'],

            $total_awal,
            $total_retur,
            $total_akhir,
            $pj['status_pembayaran']
        ]);
    }
}

fclose($out);
exit;
