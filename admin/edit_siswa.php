<?php
$page_title = "Edit Siswa";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('admin');

$id = intval($_GET['id'] ?? 0);
if(!$id) header("Location: kelola_siswa.php");

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $nama = $_POST['nama'];
  $kelas = $_POST['kelas'];
  $username = $_POST['username'];
  $password = $_POST['password'];

  // Mulai query untuk update
  if(!empty($password)){
    // Jika password diisi, hash dan update
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = $koneksi->prepare("UPDATE siswa SET nama=?, kelas=?, username=?, password=? WHERE id=?");
    $sql->bind_param("ssssi", $nama, $kelas, $username, $hashed_password, $id);
  } else {
    // Jika password kosong, jangan ubah password
    $sql = $koneksi->prepare("UPDATE siswa SET nama=?, kelas=?, username=? WHERE id=?");
    $sql->bind_param("sssi", $nama, $kelas, $username, $id);
  }
  $sql->execute();
  header("Location: kelola_siswa.php");
  exit;
}

$r = $koneksi->query("SELECT * FROM siswa WHERE id=$id")->fetch_assoc();
?>
<h1 class="text-2xl font-bold mb-4">Edit Siswa</h1>

<form method="post" class="bg-white p-6 rounded shadow space-y-3">
  <input name="nama" value="<?=htmlspecialchars($r['nama'])?>" class="w-full border p-2 rounded" required>
  <input name="kelas" value="<?=htmlspecialchars($r['kelas'])?>" class="w-full border p-2 rounded" required>
  <input name="username" value="<?=htmlspecialchars($r['username'])?>" class="w-full border p-2 rounded" required>
  <input type="password" name="password" placeholder="Password baru (kosongkan jika tidak ingin mengubah)" class="w-full border p-2 rounded">
  <div><button class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button></div>
</form>

<?php include '../inc/footer.php'; ?>