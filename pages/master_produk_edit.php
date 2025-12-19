<?php
// pages/master_produk_edit.php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/funcs.php';
require_role(['admin']); // hanya admin boleh edit produk

if (!isset($_GET['id'])) {
    header('Location: master_produk.php');
    exit;
}

$id = (int)$_GET['id'];

// Ambil data lama
$stmt = $pdo->prepare("SELECT * FROM produk WHERE produk_id=?");
$stmt->execute([$id]);
$produk = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produk) {
    echo "<script>alert('Produk tidak ditemukan!');window.location='master_produk.php';</script>";
    exit;
}

// Ambil daftar kategori
$cats = $pdo->query("SELECT * FROM kategori_produk ORDER BY nama_kategori")->fetchAll();

// === Tahap 1: Form dikirim awal ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['konfirmasi'])) {
    $_SESSION['edit_temp'] = [
        'id' => $id,
        'nama_lama' => $produk['nama_produk'],
        'harga_lama' => $produk['harga'],
        'stok_lama' => $produk['stok'],
        'stokmin_lama' => $produk['stok_minimum'],
        'kategori_lama' => $produk['kategori_id'],
        'nama_baru' => $_POST['nama_produk'],
        'harga_baru' => $_POST['harga'],
        'stok_baru' => $_POST['stok'],
        'stokmin_baru' => $_POST['stok_minimum'],
        'kategori_baru' => $_POST['kategori_id'] ?: null
    ];
    header('Location: master_produk_edit.php?id='.$id.'&confirm=1');
    exit;
}

// === Tahap 2: Konfirmasi ===
if (isset($_GET['confirm']) && isset($_SESSION['edit_temp'])) {
    $temp = $_SESSION['edit_temp'];

    // Jika konfirmasi
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi'])) {
        $stmt = $pdo->prepare("UPDATE produk SET nama_produk=?, harga=?, stok=?, stok_minimum=?, kategori_id=? WHERE produk_id=?");
        $stmt->execute([$temp['nama_baru'], $temp['harga_baru'], $temp['stok_baru'], $temp['stokmin_baru'], $temp['kategori_baru'], $temp['id']]);

        log_activity($pdo, $_SESSION['user_id'], 'Edit Produk', "Ubah: {$temp['nama_lama']} → {$temp['nama_baru']}");
        unset($_SESSION['edit_temp']);
        echo "<script>alert('✅ Data produk berhasil diperbarui!');window.location='master_produk.php';</script>";
        exit;
    }

    // Jika batal
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batal'])) {
        unset($_SESSION['edit_temp']);
        header('Location: master_produk_edit.php?id='.$id);
        exit;
    }

    // Tampilan konfirmasi
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
    <meta charset="UTF-8">
    <title>Konfirmasi Perubahan Produk</title>
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
    <h2>Konfirmasi Perubahan Data Produk</h2>
    <table>
    <tr><th>Kolom</th><th>Data Lama</th><th>Data Baru</th></tr>
    <tr><td>Nama Produk</td><td><?=htmlspecialchars($temp['nama_lama'])?></td><td class="diff"><?=htmlspecialchars($temp['nama_baru'])?></td></tr>
    <tr><td>Harga</td><td><?=number_format($temp['harga_lama'],0,',','.')?></td><td class="diff"><?=number_format($temp['harga_baru'],0,',','.')?></td></tr>
    <tr><td>Stok</td><td><?=$temp['stok_lama']?></td><td class="diff"><?=$temp['stok_baru']?></td></tr>
    <tr><td>Stok Minimum</td><td><?=$temp['stokmin_lama']?></td><td class="diff"><?=$temp['stokmin_baru']?></td></tr>
    <tr><td>Kategori</td>
        <td><?=$temp['kategori_lama']?></td>
        <td class="diff"><?=$temp['kategori_baru']?></td>
    </tr>
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
<title>Edit Produk | PT Wijaya Sakti Utama</title>
<style>
body{background:#0f0f1a;color:#fff;font-family:Poppins,sans-serif;padding:20px;}
.card{background:#181830;padding:15px;border-radius:10px;max-width:800px;margin:auto;}
input,select,button{width:100%;padding:8px;margin-bottom:10px;border:none;border-radius:5px;background:#181830;color:#fff;}
button{background:#6b5bff;cursor:pointer;}
button:hover{background:#5648e5;}
h2{color:#b3a7ff;text-align:center;}
.back-btn{
    background:#303040;
    padding:7px 12px;
    border-radius:6px;
    color:#fff;
    text-decoration:none;
    margin-bottom:15px;
    display:inline-block;
    transition:0.2s;
}
.back-btn:hover{
    background:#505060;
}
</style>
</head>
<body>
<div class="card">

<a href="master_produk.php" class="back-btn">⬅ Kembali ke Master Produk</a>

<h2>Edit Produk</h2>
<form method="post">

<label>Nama Produk:</label>
<input type="text" name="nama_produk" value="<?=htmlspecialchars($produk['nama_produk'])?>" required>

<label>Harga:</label>
<input type="number" step="0.01" name="harga" value="<?=$produk['harga']?>" required>

<label>Stok:</label>
<input type="number" name="stok" value="<?=$produk['stok']?>" required>

<label>Stok Minimum:</label>
<input type="number" name="stok_minimum" value="<?=$produk['stok_minimum']?>" required>

<label>Kategori:</label>
<select name="kategori_id">
<option value="">-- Pilih Kategori --</option>
<?php foreach($cats as $c): ?>
<option value="<?=$c['kategori_id']?>" <?=($produk['kategori_id']==$c['kategori_id']?'selected':'')?>>
<?=htmlspecialchars($c['nama_kategori'])?>
</option>
<?php endforeach;?>
</select>

<button type="submit">Simpan</button>
</form>

</div>
</body>
</html>
