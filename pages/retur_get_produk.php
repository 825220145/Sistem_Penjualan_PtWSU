<?php
require_once '../config/database.php';

$penjualan_id = (int)($_GET['penjualan_id'] ?? 0);
if(!$penjualan_id){ exit; }

$stmt = $pdo->prepare("
    SELECT dp.produk_id, pr.nama_produk, dp.jumlah, dp.harga_satuan 
    FROM detail_penjualan dp
    JOIN produk pr ON dp.produk_id = pr.produk_id
    WHERE dp.penjualan_id = ?
");
$stmt->execute([$penjualan_id]);
$produk = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(!$produk){
    echo "<p>Tidak ada produk pada nota ini.</p>";
    exit;
}

echo "<table>";
echo "<tr><th>Produk</th><th>Qty Beli</th><th>Jumlah Retur</th></tr>";
foreach($produk as $p){
    echo "<tr>";
    echo "<td>".htmlspecialchars($p['nama_produk'])."</td>";
    echo "<td>".$p['jumlah']."</td>";
    echo "<td><input type='hidden' name='produk_id[]' value='{$p['produk_id']}'><input type='number' name='jumlah_retur[]' min='0' max='{$p['jumlah']}' value='0'></td>";
    echo "</tr>";
}
echo "</table>";
