<?php
// pages/user.php
require_once __DIR__ . '/../includes/funcs.php';
require_login();
if(!is_admin()) { header('Location: /wijaya_sakti/index.php'); exit; }
require_once __DIR__ . '/../config/database.php';
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if($_POST['action']==='add'){
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?,?,?)')->execute([$_POST['username'], $hash, $_POST['role']]);
    $msg='User ditambahkan';
  } elseif($_POST['action']==='delete'){
    $pdo->prepare('DELETE FROM users WHERE user_id = ?')->execute([$_POST['id']]);
    $msg='User dihapus';
  }
}
$users = $pdo->query('SELECT user_id, username, role, created_at FROM users ORDER BY user_id DESC')->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="card">
  <h2>Manajemen User (Admin)</h2>
  <?php if($msg): ?><div class="small" style="color:#b2f5b3"><?=$msg?></div><?php endif; ?>
  <form method="post" style="display:grid;grid-template-columns:1fr 1fr 140px;gap:8px;margin-bottom:10px">
    <input name="username" placeholder="Username" required>
    <input name="password" placeholder="Password" required>
    <select name="role"><option value="admin">Admin</option><option value="kasir">Kasir</option><option value="owner">Owner</option></select>
    <input type="hidden" name="action" value="add">
    <button class="btn">Tambah</button>
  </form>
  <table class="table"><thead><tr><th>ID</th><th>Username</th><th>Role</th><th>Created</th><th>Aksi</th></tr></thead>
  <tbody>
    <?php foreach($users as $u): ?>
    <tr>
      <td><?=$u['user_id']?></td>
      <td><?=htmlspecialchars($u['username'])?></td>
      <td><?=$u['role']?></td>
      <td><?=$u['created_at']?></td>
      <td>
        <form method="post" style="display:inline" onsubmit="return confirm('Hapus user?')">
          <input type="hidden" name="id" value="<?=$u['user_id']?>">
          <input type="hidden" name="action" value="delete">
          <button class="btn secondary">Hapus</button>
        </form>
      </td>
    </tr>
    <?php endforeach;?>
  </tbody></table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
