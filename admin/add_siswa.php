<?php
$page_title = "Tambah Siswa";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('admin');

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $kelas = $_POST['kelas'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $koneksi->prepare("INSERT INTO siswa (nama,kelas,username,password) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $nama, $kelas, $username, $password);
    if ($stmt->execute()) {
        header("Location: kelola_siswa.php");
        exit;
    } else {
        $msg = "Gagal menyimpan.";
    }
}
?>
<div class="max-w-md mx-auto mt-6 sm:mt-10 px-4">
  <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8">
    <!-- Header form -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl sm:text-2xl font-extrabold text-gray-800 flex items-center gap-2">
        <i class="fas fa-user-plus text-green-600"></i> Tambah Siswa
      </h1>
      <a href="kelola_siswa.php" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs sm:text-sm bg-gray-200 hover:bg-gray-300 rounded-lg transition">
        <i class="fas fa-arrow-left"></i> Kembali
      </a>
    </div>

    <?php if ($msg): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-center text-sm">
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-5">
      <div>
        <label class="block text-gray-700 font-semibold mb-2" for="nama">Nama</label>
        <div class="flex items-center border border-gray-300 rounded-lg focus-within:ring-2 focus-within:ring-green-500">
          <span class="px-3 text-gray-500"><i class="fas fa-user"></i></span>
          <input name="nama" id="nama" placeholder="Nama" class="w-full p-2 sm:p-3 text-sm sm:text-base rounded-r-lg focus:outline-none" required>
        </div>
      </div>
      <div>
        <label class="block text-gray-700 font-semibold mb-2" for="kelas">Kelas</label>
        <div class="flex items-center border border-gray-300 rounded-lg focus-within:ring-2 focus-within:ring-green-500">
          <span class="px-3 text-gray-500"><i class="fas fa-school"></i></span>
          <input name="kelas" id="kelas" placeholder="Kelas" class="w-full p-2 sm:p-3 text-sm sm:text-base rounded-r-lg focus:outline-none">
        </div>
      </div>
      <div>
        <label class="block text-gray-700 font-semibold mb-2" for="username">Username</label>
        <div class="flex items-center border border-gray-300 rounded-lg focus-within:ring-2 focus-within:ring-green-500">
          <span class="px-3 text-gray-500"><i class="fas fa-user-circle"></i></span>
          <input name="username" id="username" placeholder="Username" class="w-full p-2 sm:p-3 text-sm sm:text-base rounded-r-lg focus:outline-none" required>
        </div>
      </div>
      <div>
        <label class="block text-gray-700 font-semibold mb-2" for="password">Password</label>
        <div class="flex items-center border border-gray-300 rounded-lg focus-within:ring-2 focus-within:ring-green-500">
          <span class="px-3 text-gray-500"><i class="fas fa-lock"></i></span>
          <input name="password" id="password" placeholder="Password" type="password" class="w-full p-2 sm:p-3 text-sm sm:text-base rounded-r-lg focus:outline-none" required>
        </div>
      </div>
      <div class="flex justify-end">
        <button class="px-4 sm:px-6 py-2 bg-green-600 hover:bg-green-700 text-white text-sm sm:text-base font-bold rounded-lg transition duration-150">
          <i class="fas fa-save mr-1"></i> Simpan
        </button>
      </div>
    </form>
  </div>
</div>
<?php include '../inc/footer.php'; ?>
