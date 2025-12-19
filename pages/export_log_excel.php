<?php
session_start();
require_once '../config/database.php';
require_once '../includes/funcs.php';
require_role(['admin','owner']); // hanya admin & owner boleh export log
date_default_timezone_set('Asia/Jakarta');

// Filter
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
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=log_aktivitas_" . date('Ymd_His') . ".csv");
header("Pragma: no-cache");
header("Expires: 0");

$out = fopen('php://output', 'w');
fputcsv($out, ['Waktu', 'Username', 'Aktivitas', 'Keterangan']);

foreach ($data as $row) {
    fputcsv($out, [
        $row['waktu'],
        $row['username'],
        $row['aktivitas'],
        $row['keterangan']
    ]);
}
fclose($out);
exit;
