<?php
// pages/export_csv.php
require_once __DIR__ . '/../config/database.php';
$type = $_GET['type'] ?? 'penjualan';
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

header('Content-Type: text/csv');
if($type==='stok'){
  header('Content-Disposition: attachment; filename="laporan_stok.csv"');
  $where=[];$params=[];
  if($start){ $where[]='s.tanggal>=?'; $params[]=$start.' 00:00:00'; }
  if($end){ $where[]='s.tanggal<=?'; $params[]=$end.' 23:59:59'; }
  $sql = 'SELECT s.*, p.nama_produk, u.username FROM stok_masuk s LEFT JOIN produk p ON s.produk_id=p.produk_id LEFT JOIN users u ON s.user_id=u.user_id';
  if($where) $sql .= ' WHERE '.implode(' AND ', $where);
  $stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
  $out = fopen('php://output','w'); fputcsv($out, ['ID','Tanggal','Produk','Jumlah','User','Keterangan']);
  foreach($rows as $r) fputcsv($out, [$r['stok_id'],$r['tanggal'],$r['nama_produk'],$r['jumlah'],$r['username'],$r['keterangan']]);
  fclose($out); exit;
} else {
  header('Content-Disposition: attachment; filename="laporan_penjualan.csv"');
  $where=[];$params=[];
  if($start){ $where[]='p.tanggal>=?'; $params[]=$start.' 00:00:00'; }
  if($end){ $where[]='p.tanggal<=?'; $params[]=$end.' 23:59:59'; }
  $sql = 'SELECT p.*, c.nama_toko FROM penjualan p LEFT JOIN customer c ON p.customer_id=c.customer_id';
  if($where) $sql .= ' WHERE '.implode(' AND ', $where);
  $stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
  $out = fopen('php://output','w'); fputcsv($out, ['No','Nota','Tanggal','Pelanggan','Total']);
  foreach($rows as $r) fputcsv($out, [$r['penjualan_id'],$r['penjualan_id'],$r['tanggal'],$r['nama_toko'],$r['total']]);
  fclose($out); exit;
}
