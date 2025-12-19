<?php
// includes/header.php
if(session_status() === PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Wijaya Sakti - Sistem Penjualan</title>
<link rel="stylesheet" href="/wijaya_sakti/assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<nav class="topbar">
  <div class="brand">PT WIJAYA SAKTI UTAMA</div>
  <div class="nav-right">
    <?php if(isset($_SESSION['user'])): ?>
      <span class="small">Hi, <?=htmlspecialchars($_SESSION['user']['username'])?> (<?=htmlspecialchars($_SESSION['user']['role'])?>)</span>
      &nbsp;|&nbsp;<a href="/wijaya_sakti/logout.php" class="small">Logout</a>
    <?php else: ?>
      <a href="/wijaya_sakti/login.php" class="small">Login</a>
    <?php endif; ?>
  </div>
</nav>
<div class="main">
  <aside class="sidebar">
    <a href="/wijaya_sakti/index.php">Dashboard</a>
    <?php if(isset($_SESSION['user'])): ?>
      <?php if(is_admin() || is_kasir()): ?>
        <a href="/wijaya_sakti/pages/penjualan.php">Input Transaksi</a>
        <a href="/wijaya_sakti/pages/pembayaran.php">Pembayaran</a>
        <a href="/wijaya_sakti/pages/stok_masuk.php">Stok Masuk</a>
        <a href="/wijaya_sakti/pages/retur.php">Retur</a>
      <?php endif; ?>
      <a href="/wijaya_sakti/pages/master_produk.php">Master Produk</a>
      <a href="/wijaya_sakti/pages/master_kategori.php">Master Kategori</a>
      <a href="/wijaya_sakti/pages/customer.php">Master Pelanggan</a>
      <?php if(is_admin()): ?><a href="/wijaya_sakti/pages/user.php">Manajemen User</a><?php endif; ?>
      <a href="/wijaya_sakti/pages/laporan_stok.php">Laporan Stok</a>
      <a href="/wijaya_sakti/pages/log_aktivitas.php">Log Aktivitas</a>
    <?php endif; ?>
  </aside>
  <section class="content">
