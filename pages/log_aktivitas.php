<?php
session_start();
require_once '../config/database.php';
require_once '../includes/funcs.php';
require_role(['admin', 'owner']);
date_default_timezone_set('Asia/Jakarta');

// --- Ambil filter dari form ---
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$user = $_GET['user'] ?? '';
$keyword = trim($_GET['keyword'] ?? '');

$where = [];
$params = [];

// Filter tanggal
if ($start) {
    $where[] = "la.waktu >= ?";
    $params[] = $start . " 00:00:00";
}
if ($end) {
    $where[] = "la.waktu <= ?";
    $params[] = $end . " 23:59:59";
}

// Filter user
if ($user) {
    $where[] = "u.user_id = ?";
    $params[] = $user;
}

// Filter keyword (di kolom aktivitas atau keterangan)
if ($keyword) {
    $where[] = "(la.aktivitas LIKE ? OR la.keterangan LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
}

$sql = "SELECT la.*, u.username 
        FROM log_aktivitas la
        LEFT JOIN users u ON la.user_id = u.user_id";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY la.waktu DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$users = $pdo->query("SELECT user_id, username FROM users ORDER BY username")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Log Aktivitas | PT Wijaya Sakti Utama</title>
<style>
body{background:#0f0f1a;color:#fff;font-family:Poppins,sans-serif;padding:20px;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{border:1px solid #333;padding:8px;text-align:left;}
th{background:#1f1f3a;}
a,button{color:#b3a7ff;text-decoration:none;}
a:hover,button:hover{color:#fff;}
button{background:#6b5bff;border:none;padding:8px 12px;color:#fff;border-radius:6px;cursor:pointer;margin-right:8px;}
button:hover{background:#5648e5;}
.export-bar{margin-bottom:15px;}
.filter-box{background:#181830;padding:12px;border-radius:10px;margin-bottom:15px;}
label{display:inline-block;width:110px;}
input,select{padding:6px;border:none;border-radius:6px;background:#222244;color:#fff;}
h2{color:#b3a7ff;}
</style>
</head>
<body>
<a href="dashboard.php" class="back-btn">⬅ Kembali ke Dashboard</a>
<h2>Log Aktivitas Pengguna</h2>

<!-- Filter -->
<div class="filter-box">
<form method="get" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
  <div>
    <label>Tanggal Mulai:</label>
    <input type="date" name="start" value="<?=$start?>">
  </div>
  <div>
    <label>Tanggal Akhir:</label>
    <input type="date" name="end" value="<?=$end?>">
  </div>
  <div>
    <label>User:</label>
    <select name="user">
      <option value="">-- Semua User --</option>
      <?php foreach($users as $u): ?>
      <option value="<?=$u['user_id']?>" <?=($user==$u['user_id']?'selected':'')?>><?=htmlspecialchars($u['username'])?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label>Kata Kunci:</label>
    <input type="text" name="keyword" placeholder="Cari aktivitas/keterangan..." value="<?=htmlspecialchars($keyword)?>">
  </div>
  <button type="submit">🔍 Filter</button>
  <a href="log_aktivitas.php" style="background:#333;padding:8px 10px;border-radius:6px;">🔄 Reset</a>
</form>
</div>

<!-- Tombol Export -->
<div class="export-bar">
<form method="get" action="export_log_excel.php" style="display:inline;">
    <input type="hidden" name="start" value="<?=$start?>">
    <input type="hidden" name="end" value="<?=$end?>">
    <input type="hidden" name="user" value="<?=$user?>">
    <input type="hidden" name="keyword" value="<?=htmlspecialchars($keyword)?>">
    <button type="submit">📘 Export Excel</button>
</form>
<form method="get" action="export_log_pdf.php" style="display:inline;">
    <input type="hidden" name="start" value="<?=$start?>">
    <input type="hidden" name="end" value="<?=$end?>">
    <input type="hidden" name="user" value="<?=$user?>">
    <input type="hidden" name="keyword" value="<?=htmlspecialchars($keyword)?>">
    <button type="submit">📗 Export PDF</button>
</form>
</div>

<!-- Tabel Log -->
<table>
<tr>
<th>No</th>
<th>Waktu</th>
<th>Username</th>
<th>Aktivitas</th>
<th>Keterangan</th>
</tr>
<?php if(!$logs): ?>
<tr><td colspan="5" align="center">Tidak ada data log sesuai filter.</td></tr>
<?php else: $no=1; foreach($logs as $log): ?>
<tr>
<td><?=$no++?></td>
<td><?=date('d M Y H:i', strtotime($log['waktu']))?></td>
<td><?=htmlspecialchars($log['username'] ?: '-')?></td>
<td><?=htmlspecialchars($log['aktivitas'])?></td>
<td><?=htmlspecialchars($log['keterangan'] ?: '-')?></td>
</tr>
<?php endforeach; endif; ?>
</table>
</body>
</html>
