<?php
// includes/funcs.php

function log_activity(PDO $pdo, $user_id = null, $aksi = '', $keterangan = '') {
    try {
        $stmt = $pdo->prepare("INSERT INTO log_aktivitas (user_id, aktivitas, keterangan) VALUES (?, ?, ?)");
        $stmt->execute([$user_id ?: null, $aksi, $keterangan]);
    } catch (PDOException $e) {}
}

function require_role($allowed) {
    if (!is_array($allowed)) $allowed = [$allowed];
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../index.php');
        exit;
    }
    $role = $_SESSION['role'] ?? null;
    if (!in_array($role, $allowed)) {
        echo "<script>alert('Akses ditolak!');window.location='dashboard.php';</script>";
        exit;
    }
}

function rupiah($n){
    return "Rp " . number_format($n,0,',','.');
}
