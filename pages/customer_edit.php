<?php
// pages/customer_edit.php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/funcs.php';
require_role(['admin','kasir']); // admin & kasir boleh edit customer

if (!isset($_GET['id'])) {
    header('Location: customer.php');
    exit;
}

$id = (int)$_GET['id'];

// Ambil data lama dari database
$stmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id=?");
$stmt->execute([$id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    echo "<script>alert('Pelanggan tidak ditemukan!');window.location='customer.php';</script>";
    exit;
}

// === Tahap 1: User klik Simpan (POST tahap awal) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['konfirmasi'])) {
    $nama_baru = trim($_POST['nama_toko']);
    $alamat_baru = trim($_POST['alamat']);
    $telepon_baru = trim($_POST['telepon']);

    // Simpan data baru sementara ke session untuk tahap konfirmasi
    $_SESSION['edit_temp'] = [
        'id' => $id,
        'nama_lama' => $customer['nama_toko'],
        'alamat_lama' => $customer['alamat'],
        'telepon_lama' => $customer['telepon'],
        'nama_baru' => $nama_baru,
        'alamat_baru' => $alamat_baru,
        'telepon_baru' => $telepon_baru
    ];

    // Tampilkan halaman konfirmasi
    header('Location: customer_edit.php?id='.$id.'&confirm=1');
    exit;
}

// === Tahap 2: Halaman konfirmasi ===
if (isset($_GET['confirm']) && isset($_SESSION['edit_temp'])) {
    $temp = $_SESSION['edit_temp'];

    // Jika user menekan Konfirmasi & Simpan
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi'])) {
        $stmt = $pdo->prepare("UPDATE customer SET nama_toko=?, alamat=?, telepon=? WHERE customer_id=?");
        $stmt->execute([$temp['nama_baru'], $temp['alamat_baru'], $temp['telepon_baru'], $temp['id']]);

        log_activity($pdo, $_SESSION['user_id'], 'Edit Pelanggan', "Ubah: {$temp['nama_lama']} → {$temp['nama_baru']}");
        unset($_SESSION['edit_temp']);
        echo "<script>alert('✅ Data pelanggan berhasil diperbarui!');window.location='customer.php';</script>";
        exit;
    }

    // Jika user menekan Batal
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batal'])) {
        unset($_SESSION['edit_temp']);
        header('Location: customer_edit.php?id='.$id);
        exit;
    }

    // Tampilkan halaman konfirmasi
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
    <meta charset="UTF-8">
    <title>Konfirmasi Perubahan | PT Wijaya Sakti Utama</title>
    <style>
    body{background:#0f0f1a;color:#fff;font-family:Poppins,sans-serif;padding:20px;}
    table{width:100%;border-collapse:collapse;margin-top:10px;}
    th,td{border:1px solid #333;padding:8px;text-align:left;}
    th{background:#1f1f3a;}
    button{background:#6b5bff;border:none;padding:10px 18px;color:#fff;border-radius:6px;cursor:pointer;margin-right:10px;}
    button:hover{background:#5648e5;}
    h2{color:#b3a7ff;}
    .diff{color:#ffcc66;font-weight:bold;}
    </style>
    </head>
    <body>
    <h2>Konfirmasi Perubahan Data Pelanggan</h2>
    <table>
    <tr><th>Kolom</th><th>Data Lama</th><th>Data Baru</th></tr>
    <tr><td>Nama Toko</td><td><?=htmlspecialchars($temp['nama_lama'])?></td><td class="diff"><?=htmlspecialchars($temp['nama_baru'])?></td></tr>
    <tr><td>Alamat</td><td><?=nl2br(htmlspecialchars($temp['alamat_lama']))?></td><td class="diff"><?=nl2br(htmlspecialchars($temp['alamat_baru']))?></td></tr>
    <tr><td>Telepon</td><td><?=htmlspecialchars($temp['telepon_lama'])?></td><td class="diff"><?=htmlspecialchars($temp['telepon_baru'])?></td></tr>
    </table>

    <form method="post" style="margin-top:20px;">
        <button type="submit" name="konfirmasi">✅ Konfirmasi & Simpan</button>
        <button type="submit" name="batal">❌ Batal</button>
    </form>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Pelanggan | PT Wijaya Sakti Utama</title>
<style>
body{background:#0f0f1a;color:#fff;font-family:Poppins,sans-serif;padding:20px;}
.card{background:#181830;padding:15px;border-radius:10px;max-width:600px;margin:auto;}
input,textarea,button{width:100%;padding:8px;margin-bottom:10px;border:none;border-radius:5px;background:#181830;color:#fff;}
button{background:#6b5bff;cursor:pointer;}
button:hover{background:#5648e5;}
h2{color:#b3a7ff;text-align:center;}
</style>
</head>
<body>
<div class="card">
<a href="customer.php" class="back-btn">⬅ Kembali ke Master Pelanggan</a>
<h2>Edit Data Pelanggan</h2>
<form method="post">
<label>Nama Toko:</label>
<input type="text" name="nama_toko" value="<?=htmlspecialchars($customer['nama_toko'])?>" required>

<label>Alamat:</label>
<textarea name="alamat" rows="3"><?=htmlspecialchars($customer['alamat'])?></textarea>

<label>Nomor Telepon:</label>
<input type="text" name="telepon" value="<?=htmlspecialchars($customer['telepon'])?>">

<button type="submit">Simpan</button>
<a href="customer.php" style="color:#b3a7ff;text-decoration:none;">Batal</a>
</form>
</div>
</body>
</html>
