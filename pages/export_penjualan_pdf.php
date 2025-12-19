<?php
session_start();
require_once '../config/database.php';
require_once '../includes/funcs.php';
require_role(['admin','owner']);

require_once '../vendor/fpdf182/fpdf.php';

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$status = $_GET['status'] ?? '';

$where = [];
$params = [];

if ($start) { $where[] = "pj.tanggal >= ?"; $params[] = $start . " 00:00:00"; }
if ($end)   { $where[] = "pj.tanggal <= ?"; $params[] = $end . " 23:59:59"; }
if ($status && in_array($status,['Lunas','Belum Lunas'])) {
    $where[] = "pj.status_pembayaran = ?";
    $params[] = $status;
}

$sql = "
SELECT pj.penjualan_id, pj.no_nota, pj.tanggal,
       pj.status_pembayaran, c.nama_toko,
       u.username, pb.metode
FROM penjualan pj
LEFT JOIN customer c ON pj.customer_id = c.customer_id
LEFT JOIN users u ON pj.user_id = u.user_id
LEFT JOIN pembayaran pb ON pb.penjualan_id = pj.penjualan_id
";

if ($where) $sql .= " WHERE ".implode(" AND ", $where);
$sql .= " ORDER BY pj.tanggal DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$list = $stmt->fetchAll();


// ========================================
//             TEMPLATE PDF
// ========================================
class PDF extends FPDF {

    function Header() {
        $this->SetFont('Arial','B',16);
        $this->Cell(0,8,'PT Wijaya Sakti Utama',0,1,'C');
        $this->SetFont('Arial','',11);
        $this->Cell(0,6,'Laporan Penjualan Lengkap',0,1,'C');
        $this->Ln(5);
        $this->Line(10, 28, 200, 28);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        date_default_timezone_set('Asia/Jakarta');
        $this->Cell(0,10,'Dicetak pada '.date('d-m-Y H:i').' | Hal. '.$this->PageNo(),0,0,'C');
    }
}


$pdf = new PDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','',11);

if (!$list) {
    $pdf->Cell(0,10,'Tidak ada data pada periode ini.',0,1,'C');
    $pdf->Output();
    exit;
}

foreach ($list as $pj) {

    // HEADER NOTA
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(0,8,'No Nota: '.str_pad($pj['no_nota'],3,'0',STR_PAD_LEFT).' | '.date('d M Y',strtotime($pj['tanggal'])),0,1);

    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,6,'Pelanggan: '.$pj['nama_toko'],0,1);
    $pdf->Cell(0,6,'Kasir: '.$pj['username'].' | Metode Pembayaran: '.($pj['metode'] ?: '-'),0,1);

    // WARNA STATUS PEMBAYARAN
    if ($pj['status_pembayaran']=='Lunas') {
        $pdf->SetTextColor(0,180,0); // hijau
    } else {
        $pdf->SetTextColor(255,0,0); // merah
    }
    $pdf->Cell(0,6,'Status Pembayaran: '.$pj['status_pembayaran'],0,1);

    $pdf->SetTextColor(0,0,0); // reset ke hitam
    $pdf->Ln(3);


    // ========================================
    //   DETAIL PRODUK (TABLE)
    // ========================================
    $pdf->SetFont('Arial','B',10);
    $pdf->SetFillColor(40,40,70);
    $pdf->SetTextColor(255,255,255);

    $pdf->Cell(80,8,'Produk',1,0,'C',true);
    $pdf->Cell(30,8,'Harga',1,0,'C',true);
    $pdf->Cell(25,8,'Jumlah',1,0,'C',true);
    $pdf->Cell(35,8,'Subtotal',1,1,'C',true);

    // AMBIL DETAIL PENJUALAN
    $pdf->SetFont('Arial','',10);
    $pdf->SetTextColor(0,0,0);

    $dstmt = $pdo->prepare("
        SELECT d.*, p.nama_produk
        FROM detail_penjualan d
        JOIN produk p ON p.produk_id = d.produk_id
        WHERE d.penjualan_id = ?
    ");
    $dstmt->execute([$pj['penjualan_id']]);
    $detail = $dstmt->fetchAll();

    $total_awal = 0;

    foreach ($detail as $d) {
        $total_awal += $d['subtotal'];

        $pdf->Cell(80,8,substr($d['nama_produk'],0,40),1);
        $pdf->Cell(30,8,number_format($d['harga_satuan'],0,',','.'),1);
        $pdf->Cell(25,8,$d['jumlah'],1,0,'C');
        $pdf->Cell(35,8,number_format($d['subtotal'],0,',','.'),1,1,'R');
    }


    // ========================================
    //     DETAIL RETUR (JIKA ADA)
    // ========================================
    $rstmt = $pdo->prepare("
        SELECT rd.*, p.nama_produk
        FROM retur_detail rd
        JOIN retur r ON rd.retur_id = r.retur_id
        JOIN produk p ON p.produk_id = rd.produk_id
        WHERE r.penjualan_id = ?
    ");
    $rstmt->execute([$pj['penjualan_id']]);
    $retur_list = $rstmt->fetchAll();

    $total_retur = 0;

    if ($retur_list) {

        $pdf->Ln(1);
        $pdf->SetFont('Arial','B',10);
        $pdf->SetFillColor(70,0,0); // merah gelap
        $pdf->SetTextColor(255,255,255);

        $pdf->Cell(80,8,'RETUR PRODUK',1,0,'C',true);
        $pdf->Cell(30,8,'Harga',1,0,'C',true);
        $pdf->Cell(25,8,'Qty Retur',1,0,'C',true);
        $pdf->Cell(35,8,'Nilai Retur',1,1,'C',true);

        $pdf->SetFont('Arial','',10);
        $pdf->SetTextColor(0,0,0);

        foreach ($retur_list as $rt) {
            $total_retur += $rt['nilai_retur'];

            $harga_retur = $rt['nilai_retur'] / $rt['jumlah_retur'];

            $pdf->Cell(80,8,'Retur: '.substr($rt['nama_produk'],0,40),1);
            $pdf->Cell(30,8,number_format($harga_retur,0,',','.'),1);
            $pdf->Cell(25,8,$rt['jumlah_retur'],1,0,'C');
            $pdf->Cell(35,8,'-'.number_format($rt['nilai_retur'],0,',','.'),1,1,'R');
        }
    }

    // ========================================
    //     TOTAL AWAL - TOTAL RETUR = TOTAL AKHIR
    // ========================================
    $total_akhir = $total_awal - $total_retur;

    $pdf->Ln(1);
    $pdf->SetFont('Arial','B',10);

    // TOTAL AWAL
    $pdf->Cell(135,8,'Total Awal',1);
    $pdf->Cell(35,8,number_format($total_awal,0,',','.'),1,1,'R');

    // TOTAL RETUR (JIKA ADA)
    if ($total_retur > 0) {
        $pdf->Cell(135,8,'Total Retur (-)',1);
        $pdf->SetTextColor(255,0,0);
        $pdf->Cell(35,8,'-'.number_format($total_retur,0,',','.'),1,1,'R');
        $pdf->SetTextColor(0,0,0);
    }

    // TOTAL AKHIR
    $pdf->Cell(135,8,'Total Akhir',1);
    $pdf->SetTextColor(0,150,0);
    $pdf->Cell(35,8,number_format($total_akhir,0,',','.'),1,1,'R');
    $pdf->SetTextColor(0,0,0);

    $pdf->Ln(8);
}

$pdf->Output('I','Laporan_Penjualan_Lengkap.pdf');
