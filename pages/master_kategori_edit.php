<?php
// pages/master_kategori_edit.php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/funcs.php';
require_role(['admin']); // hanya admin boleh edit kategori

if (!isset($_GET['id'])) {
    header('Location: master_kategori.php');
    exit;
}

$id = (int)$_GET['id'];

// Ambil data lama
$stmt = $pdo->prepare("SELECT * FROM kategori_produk WHERE kategori_id=?");
$stmt->execute([$id]);
$kategori = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kategori) {
    echo "<script>alert('Kategori tidak ditemukan!');window.location='master_kategori.php';</script>";
    exit;
}

// === Tahap 1: POST tahap awal ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['konfirmasi'])) {
    $_SESSION['edit_temp'] = [
        'id' => $id,
        'nama_lama' => $kategori['nama_kategori'],
        'desk_lama' => $kategori['deskripsi'],
        'nama_baru' => $_POST['nama_kategori'],
        'desk_baru' => $_POST['deskripsi']
    ];
    header('Location: master_kategori_edit.php?id='.$id.'&confirm=1');
    exit;
}

// === Tahap 2: Halaman konfirmasi ===
if (isset($_GET['confirm']) && isset($_SESSION['edit_temp'])) {
    $temp = $_SESSION['edit_temp'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi'])) {
        $stmt = $pdo->prepare("UPDATE kategori_produk SET nama_kategori=?, deskripsi=? WHERE kategori_id=?");
        $stmt->execute([$temp['nama_baru'], $temp['desk_baru'], $temp['id']]);

        log_activity($pdo, $_SESSION['user_id'], 'Edit Kategori', "Ubah: {$temp['nama_lama']} → {$temp['nama_baru']}");
        unset($_SESSION['edit_temp']);
        echo "<script>alert('✅ Kategori berhasil diperbarui!');window.location='master_kategori.php';</script>";
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batal'])) {
        unset($_SESSION['edit_temp']);
        header('Location: master_kategori_edit.php?id='.$id);
        exit;
    }

    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
    <meta charset="UTF-8">
    <title>Konfirmasi Perubahan Kategori</title>
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
    <h2>Konfirmasi Perubahan Data Kategori</h2>
    <table>
    <tr><th>Kolom</th><th>Data Lama</th><th>Data Baru</th></tr>
    <tr><td>Nama Kategori</td><td><?=htmlspecialchars($temp['nama_lama'])?></td><td class="diff"><?=htmlspecialchars($temp['nama_baru'])?></td></tr>
    <tr><td>Deskripsi</td><td><?=nl2br(htmlspecialchars($temp['desk_lama']))?></td><td class="diff"><?=nl2br(htmlspecialchars($temp['desk_baru']))?></td></tr>
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
<title>Edit Kategori | PT Wijaya Sakti Utama</title>
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
<a href="master_kategori.php" class="back-btn">⬅ Kembali ke Master Kategori</a>
<h2>Edit Kategori Produk</h2>
<form method="post">
<label>Nama Kategori:</label>
<input type="text" name="nama_kategori" value="<?=htmlspecialchars($kategori['nama_kategori'])?>" required>

<label>Deskripsi:</label>
<textarea name="deskripsi" rows="3"><?=htmlspecialchars($kategori['deskripsi'])?></textarea>

<button type="submit">Simpan</button>
<a href="master_kategori.php" style="color:#b3a7ff;text-decoration:none;">Batal</a>
</form>
</div>
</body>
</html>
