<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once '../config/database.php';
require_once '../includes/funcs.php';
require_once '../vendor/fpdf182/fpdf.php';
require_role(['admin', 'owner']);

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$user = $_GET['user'] ?? '';

$where = [];
$params = [];

if ($start) {
    $where[] = "la.waktu >= ?";
    $params[] = $start . " 00:00:00";
}
if ($end) {
    $where[] = "la.waktu <= ?";
    $params[] = $end . " 23:59:59";
}
if ($user) {
    $where[] = "u.user_id = ?";
    $params[] = $user;
}
if ($keyword = trim($_GET['keyword'] ?? '')) {
    $where[] = "(la.aktivitas LIKE ? OR la.keterangan LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
}

$sql = "SELECT la.waktu, u.username, la.aktivitas, la.keterangan 
        FROM log_aktivitas la
        LEFT JOIN users u ON la.user_id = u.user_id";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY la.waktu DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,8,'PT Wijaya Sakti Utama',0,1,'C');
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,6,'Laporan Log Aktivitas Sistem',0,1,'C');
$pdf->Ln(6);

if ($start || $end) {
    $pdf->SetFont('Arial','I',9);
    $pdf->Cell(0,6,'Periode: ' . ($start ?: '-') . ' s/d ' . ($end ?: '-') ,0,1,'C');
}

$pdf->Ln(5);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(10,8,'No',1,0,'C');
$pdf->Cell(40,8,'Waktu',1,0,'C');
$pdf->Cell(40,8,'User',1,0,'C');
$pdf->Cell(45,8,'Aktivitas',1,0,'C');
$pdf->Cell(55,8,'Keterangan',1,1,'C');

$pdf->SetFont('Arial','',9);
$no = 1;
foreach ($data as $row) {
    $pdf->Cell(10,7,$no++,1,0,'C');
    $pdf->Cell(40,7,date('d-m-Y H:i', strtotime($row['waktu'])),1,0,'L');
    $pdf->Cell(40,7,utf8_decode($row['username']),1,0,'L');
    $pdf->Cell(45,7,utf8_decode($row['aktivitas']),1,0,'L');
    $pdf->Cell(55,7,utf8_decode($row['keterangan']),1,1,'L');
}

$pdf->Ln(5);
$pdf->SetFont('Arial','I',8);
$pdf->Cell(0,8,'Dicetak pada: '.date('d-m-Y H:i'),0,1,'R');

$pdf->Output('I','Laporan_Log_Aktivitas.pdf');
exit;
