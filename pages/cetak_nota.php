<?php
session_start();
require_once '../config/database.php';
require_once '../includes/funcs.php';
require_once '../vendor/fpdf182/fpdf.php';
require_role(['kasir', 'admin', 'owner']);
date_default_timezone_set('Asia/Jakarta');

if (!isset($_GET['penjualan_id'])) {
    die("Parameter penjualan_id tidak ditemukan.");
}

$id = (int)$_GET['penjualan_id'];

// Ambil data transaksi utama
$stmt = $pdo->prepare("
    SELECT p.penjualan_id, p.no_nota, p.tanggal, p.total, c.nama_toko, c.alamat, c.telepon,
           u.username, pb.metode, pb.jumlah_bayar
    FROM penjualan p
    LEFT JOIN customer c ON p.customer_id = c.customer_id
    LEFT JOIN users u ON p.user_id = u.user_id
    LEFT JOIN pembayaran pb ON p.penjualan_id = pb.penjualan_id
    WHERE p.penjualan_id = ?
");
$stmt->execute([$id]);
$trx = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trx) {
    die("Transaksi tidak ditemukan.");
}

// Ambil daftar item produk
$detail = $pdo->prepare("
    SELECT dp.jumlah, dp.harga_satuan, dp.subtotal, pr.nama_produk
    FROM detail_penjualan dp
    LEFT JOIN produk pr ON dp.produk_id = pr.produk_id
    WHERE dp.penjualan_id = ?
");
$detail->execute([$id]);
$items = $detail->fetchAll(PDO::FETCH_ASSOC);

// === Cetak PDF Nota ===
$pdf = new FPDF('P','mm','A4');
$pdf->SetMargins(15,10,15);
$pdf->AddPage();

// === Header ===
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,7,'PT Wijaya Sakti Utama',0,1,'C');
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,5,'Jl. Perdana  Kusuma blok UU no. 11, Jakarta Barat',0,1,'C');
$pdf->Cell(0,5,'Telp. 0812-3456-7890',0,1,'C');
$pdf->Ln(6);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,7,'Nota Penjualan',0,1,'C');
$pdf->Ln(4);

// === Info Transaksi ===
$pdf->SetFont('Arial','',11);
$pdf->Cell(30,6,'No Nota',0,0);
$pdf->Cell(5,6,':',0,0);
$pdf->Cell(0,6,$trx['no_nota'],0,1);
$pdf->Cell(30,6,'Tanggal',0,0);
$pdf->Cell(5,6,':',0,0);
$pdf->Cell(0,6,date('d/m/Y H:i', strtotime($trx['tanggal'])),0,1);
$pdf->Cell(30,6,'Kasir',0,0);
$pdf->Cell(5,6,':',0,0);
$pdf->Cell(0,6,ucfirst($trx['username']),0,1);
$pdf->Ln(3);
$pdf->Cell(30,6,'Pelanggan',0,0);
$pdf->Cell(5,6,':',0,0);
$pdf->Cell(0,6,utf8_decode($trx['nama_toko'] ?: '-'),0,1);
$pdf->Cell(30,6,'Alamat',0,0);
$pdf->Cell(5,6,':',0,0);
$pdf->MultiCell(0,6,utf8_decode($trx['alamat'] ?: '-'));
$pdf->Ln(4);

// === Garis pemisah ===
$pdf->Cell(0,0,'','T');
$pdf->Ln(5);

// === Tabel Produk ===
$pdf->SetFont('Arial','B',11);
$pdf->SetFillColor(230,230,230);
$pdf->Cell(80,8,'Produk',1,0,'C',true);
$pdf->Cell(20,8,'Qty',1,0,'C',true);
$pdf->Cell(40,8,'Harga',1,0,'C',true);
$pdf->Cell(35,8,'Subtotal',1,1,'C',true);

$pdf->SetFont('Arial','',11);
foreach($items as $it){
    $pdf->Cell(80,8,utf8_decode($it['nama_produk']),1,0);
    $pdf->Cell(20,8,$it['jumlah'],1,0,'C');
    $pdf->Cell(40,8,number_format($it['harga_satuan'],0,',','.'),1,0,'R');
    $pdf->Cell(35,8,number_format($it['subtotal'],0,',','.'),1,1,'R');
}

// === Total ===
$pdf->SetFont('Arial','B',11);
$pdf->Cell(140,8,'Total',1,0,'R',true);
$pdf->Cell(35,8,number_format($trx['total'],0,',','.'),1,1,'R');
$pdf->Ln(4);

// === Footer ===
$pdf->SetFont('Arial','I',10);
$pdf->Cell(0,6,'Terima kasih atas pembelian Anda!',0,1,'C');
$pdf->Cell(0,6,'Kepuasan Anda adalah prioritas kami!',0,1,'C');
$pdf->Ln(6);
$pdf->SetFont('Arial','',9);
$pdf->Cell(0,6,'Dicetak pada: '.date('d-m-Y H:i'),0,1,'R');

$pdf->Output('I','Nota_Penjualan_'.$trx['no_nota'].'.pdf');
exit;
