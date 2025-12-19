<?php
session_start();
require_once '../config/database.php';
require_once '../includes/funcs.php';
require_role(['admin','owner']); // hanya admin & owner yang bisa export PDF

require_once '../vendor/fpdf182/fpdf.php';

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
$stok = $stmt->fetchAll();

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',16);
        $this->Cell(0,8,'PT Wijaya Sakti Utama',0,1,'C');
        $this->SetFont('Arial','',11);
        $this->Cell(0,6,'Laporan Stok Masuk',0,1,'C');
        $this->Ln(5);
        $this->Line(10, 28, 287, 28); // garis bawah header
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Dicetak pada '.date('d-m-Y H:i').' | Hal. '.$this->PageNo(),0,0,'C');
    }
}

$pdf = new PDF('L','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

if (!$stok) {
    $pdf->Cell(0,10,'Tidak ada data stok masuk dalam periode ini.',0,1,'C');
} else {
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(20,8,'ID',1);
    $pdf->Cell(35,8,'Tanggal',1);
    $pdf->Cell(80,8,'Produk',1);
    $pdf->Cell(25,8,'Jumlah',1);
    $pdf->Cell(40,8,'User Input',1);
    $pdf->Cell(70,8,'Keterangan',1,1);
    $pdf->SetFont('Arial','',10);

    foreach ($stok as $row) {
        $pdf->Cell(20,8,$row['stok_id'],1);
        $pdf->Cell(35,8,date('d/m/Y H:i', strtotime($row['tanggal'])),1);
        $pdf->Cell(80,8,substr($row['nama_produk'],0,40),1);
        $pdf->Cell(25,8,$row['jumlah'],1,0,'C');
        $pdf->Cell(40,8,$row['username'],1);
        $pdf->Cell(70,8,substr($row['keterangan'] ?: '-',0,50),1,1);
    }
}

$pdf->Output('I','Laporan_Stok_Masuk.pdf');
