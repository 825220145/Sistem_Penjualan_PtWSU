<?php
session_start();
require_once '../config/database.php';
require_once '../includes/funcs.php';
require_role(['admin','owner']); // hanya admin & owner

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=laporan_stok_" . date('Ymd_His') . ".csv");
header("Pragma: no-cache");
header("Expires: 0");

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$where = [];
$params = [];

if ($start) { $where[] = "sm.tanggal >= ?"; $params[] = $start . " 00:00:00"; }
if ($end)   { $where[] = "sm.tanggal <= ?"; $params[] = $end . " 23:59:59"; }

$sql = "SELECT sm.stok_id, sm.tanggal, p.nama_produk, sm.jumlah, sm.keterangan, u.username
        FROM stok_masuk sm
        LEFT JOIN produk p ON sm.produk_id = p.produk_id
        LEFT JOIN users u ON sm.user_id = u.user_id";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY sm.tanggal DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$out = fopen('php://output', 'w');

// Header kolom
fputcsv($out, ['ID Stok', 'Tanggal', 'Produk', 'Jumlah', 'User Input', 'Keterangan']);

if ($data) {
    foreach ($data as $r) {
        fputcsv($out, [
            $r['stok_id'],
            $r['tanggal'],
            $r['nama_produk'],
            $r['jumlah'],
            $r['username'],
            $r['keterangan']
        ]);
    }
} else {
    fputcsv($out, ['Tidak ada data stok masuk pada periode ini.']);
}
fclose($out);
exit;
