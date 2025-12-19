<?php
// export_pdf.php
require_once __DIR__ . '/config/database.php';

// create logo PNG if not exists
$logoPath = __DIR__ . '/assets/img/logo.png';
if(!file_exists($logoPath)){
  if(!is_dir(dirname($logoPath))) mkdir(dirname($logoPath), 0755, true);
  $w=420;$h=120;$im = imagecreatetruecolor($w,$h);
  $bg = imagecolorallocate($im, 15, 13, 40);
  $txtc = imagecolorallocate($im, 255, 255, 255);
  imagefill($im,0,0,$bg);
  imagestring($im, 5, 18, 40, 'PT WIJAYA SAKTI UTAMA', $txtc);
  imagestring($im, 3, 22, 70, 'Sistem Pencatatan Penjualan', $txtc);
  imagepng($im, $logoPath);
  imagedestroy($im);
}

$fpdf_file = __DIR__ . '/vendor/fpdf182/fpdf.php';
if(!file_exists($fpdf_file)){
  die('FPDF tidak ditemukan. Silakan download fpdf.php dan letakkan di /vendor/fpdf182/fpdf.php.');
}
require_once $fpdf_file;

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$where=[];$params=[];
if($start){ $where[]='tanggal >= ?'; $params[]=$start.' 00:00:00'; }
if($end){ $where[]='tanggal <= ?'; $params[]=$end.' 23:59:59'; }
$sql = 'SELECT p.*, c.nama_toko FROM penjualan p LEFT JOIN customer c ON p.customer_id=c.customer_id';
if($where) $sql .= ' WHERE '.implode(' AND ', $where);
$sql .= ' ORDER BY tanggal DESC';
$stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();

class PDF extends FPDF {
  function Header(){
    global $logoPath;
    $this->Image($logoPath,10,8,50);
    $this->SetFont('Arial','B',14);
    $this->SetXY(70,10);
    $this->Cell(0,6,'PT WIJAYA SAKTI UTAMA',0,1);
    $this->SetFont('Arial','',10);
    $this->SetX(70);
    $this->Cell(0,6,'Sistem Pencatatan Penjualan',0,1);
    $this->Ln(6);
    $this->SetDrawColor(120,100,238);
    $this->SetLineWidth(0.8);
    $this->Line(10,34,200,34);
    $this->Ln(6);
  }
  function Footer(){
    $this->SetY(-15);
    $this->SetFont('Arial','I',8);
    $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C');
  }
}

$pdf = new PDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,6,'Laporan Penjualan',0,1,'C');
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,6,'Periode: '.($start?:'--').' s/d '.($end?:'--'),0,1,'C');
$pdf->Ln(4);

// table header
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(240,240,240);
$pdf->Cell(20,8,'No',1,0,'C',true);
$pdf->Cell(30,8,'Nota',1,0,'C',true);
$pdf->Cell(50,8,'Tanggal',1,0,'C',true);
$pdf->Cell(60,8,'Pelanggan',1,0,'C',true);
$pdf->Cell(30,8,'Total',1,1,'C',true);

$pdf->SetFont('Arial','',10);
$grand = 0;
$i=1;
foreach($rows as $r){
  $pdf->Cell(20,8,$i,1,0,'C');
  $pdf->Cell(30,8,$r['penjualan_id'],1,0,'C');
  $pdf->Cell(50,8,$r['tanggal'],1,0,'L');
  $pdf->Cell(60,8,substr($r['nama_toko'] ?? 'Umum',0,30),1,0,'L');
  $pdf->Cell(30,8,number_format($r['total'],2,',','.'),1,1,'R');
  $grand += (float)$r['total'];
  $i++;
}
$pdf->SetFont('Arial','B',11);
$pdf->Cell(160,10,'TOTAL',1,0,'R',true);
$pdf->Cell(30,10,number_format($grand,2,',','.'),1,1,'R',true);

$pdf->Ln(8);
$pdf->SetFont('Arial','',9);
$pdf->Cell(0,6,'Dicetak pada: '.date('Y-m-d H:i'),0,1);
$pdf->Output('I','laporan_penjualan.pdf');
exit;
