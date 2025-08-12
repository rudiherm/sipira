<?php
$page_title = "Edit Profil";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('siswa');

$id = $_SESSION['user_id'];
$password_error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $nama = $_POST['nama'];
  $kelas = $_POST['kelas'];
  $alamat = $_POST['alamat'];
  $motivasi = $_POST['motivasi'];

  // Password logic
  $new_password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  if(!empty($new_password)){
    if($new_password === $confirm_password){
      $hashed = password_hash($new_password, PASSWORD_DEFAULT);
      $stmt = $koneksi->prepare("UPDATE siswa SET password=? WHERE id=?");
      $stmt->bind_param("si", $hashed, $id);
      $stmt->execute();
    } else {
      $password_error = "Password baru dan konfirmasi tidak cocok.";
    }
  }

  if(empty($password_error)){
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0){
      $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
      $nama_file = 'siswa_'.$id.'_'.time().'.'.$ext;
      move_uploaded_file($_FILES['foto']['tmp_name'], "../uploads/".$nama_file);
      $stmt = $koneksi->prepare("UPDATE siswa SET nama=?,kelas=?,alamat=?,motivasi=?,foto=? WHERE id=?");
      $stmt->bind_param("sssssi",$nama,$kelas,$alamat,$motivasi,$nama_file,$id);
    } else {
      $stmt = $koneksi->prepare("UPDATE siswa SET nama=?,kelas=?,alamat=?,motivasi=? WHERE id=?");
      $stmt->bind_param("ssssi",$nama,$kelas,$alamat,$motivasi,$id);
    }
    $stmt->execute();
    header("Location: profil.php");
    exit;
  }
}

$stmt = $koneksi->prepare("SELECT nama,kelas,alamat,motivasi FROM siswa WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
?>
<h1 class="text-2xl font-bold mb-4">Edit Profil</h1>

<?php if(!empty($password_error)): ?>
  <div class="bg-red-100 text-red-700 p-2 mb-2 rounded"><?=htmlspecialchars($password_error)?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="bg-white p-6 rounded shadow space-y-4">
  <div>
  <label class="block text-sm">Nama</label>
  <input name="nama" readonly value="<?=htmlspecialchars($data['nama'])?>" class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-500 cursor-not-allowed" required>
  </div>
  <div>
  <label class="block text-sm">Kelas</label>
  <input name="kelas" readonly value="<?=htmlspecialchars($data['kelas'])?>" class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-500 cursor-not-allowed">
  </div>
  <div>
  <label class="block text-sm">Alamat</label>
  <textarea name="alamat" class="w-full border rounded px-3 py-2"><?=htmlspecialchars($data['alamat'])?></textarea>
  </div>
  <div>
  <label class="block text-sm">Motivasi</label>
  <textarea name="motivasi" class="w-full border rounded px-3 py-2"><?=htmlspecialchars($data['motivasi'])?></textarea>
  </div>
  <div>
  <label class="block text-sm">Foto Profil (opsional)</label>
  <input type="file" name="foto" accept="image/*">
  </div>
  <div>
  <label class="block text-sm">Password Baru (opsional)</label>
  <input type="password" name="password" class="w-full border rounded px-3 py-2" autocomplete="new-password">
  </div>
  <div>
  <label class="block text-sm">Konfirmasi Password Baru</label>
  <input type="password" name="confirm_password" class="w-full border rounded px-3 py-2" autocomplete="new-password">
  </div>
  <div>
  <button class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
  <a href="profil.php" class="ml-2 text-gray-600">Batal</a>
  </div>
</form>

<?php include '../inc/footer.php'; ?>