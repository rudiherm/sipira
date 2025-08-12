<?php
$page_title = "Kelola Siswa";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';
require '../vendor/autoload.php';

// Hapus siswa tertentu
if(isset($_GET['hapus'])){
  $id = intval($_GET['hapus']);
  $koneksi->query("DELETE FROM siswa WHERE id = $id");
  header("Location: kelola_siswa.php");
  exit;
}

// Hapus semua siswa
if(isset($_GET['hapus_semua']) && $_GET['hapus_semua'] == 'true'){
  $koneksi->query("DELETE FROM siswa");
  header("Location: kelola_siswa.php");
  exit;
}

// Import dari Excel
if(isset($_POST['import_excel'])){
  if(isset($_FILES['file_excel']) && $_FILES['file_excel']['error'] == UPLOAD_ERR_OK){
    $fileTmpPath = $_FILES['file_excel']['tmp_name'];
    $fileType = $_FILES['file_excel']['type'];
    $allowedTypes = [
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'application/vnd.ms-excel'
    ];
    if(in_array($fileType, $allowedTypes)){
      try {
        $spreadsheet = IOFactory::load($fileTmpPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        foreach($rows as $index => $row){
          if($index === 0) continue; // Lewati header
          $nama = trim($row[0] ?? '');
          $kelas = trim($row[1] ?? '');
          $username = trim($row[2] ?? '');
          $passwordPlain = trim($row[3] ?? '');

          if($nama && $kelas && $username && $passwordPlain){
            $stmtCheck = $koneksi->prepare("SELECT id FROM siswa WHERE username=?");
            $stmtCheck->bind_param("s", $username);
            $stmtCheck->execute();
            $stmtCheck->store_result();

            if($stmtCheck->num_rows == 0){
              $passwordHash = password_hash($passwordPlain, PASSWORD_DEFAULT);
              $stmtInsert = $koneksi->prepare("INSERT INTO siswa (nama, kelas, username, total_prestasi, total_pelanggaran) VALUES (?, ?, ?, 0, 0)");
              $stmtInsert->bind_param("sss", $nama, $kelas, $username);
              $stmtInsert->execute();
              $stmtInsert->close();
            }
            $stmtCheck->close();
          }
        }
        header("Location: kelola_siswa.php");
        exit;
      } catch(Exception $e){
        echo "<script>alert('Gagal memproses file: " . htmlspecialchars($e->getMessage()) . "');</script>";
      }
    } else {
      echo "<script>alert('Format file tidak didukung. Unggah XLSX atau XLS.');</script>";
    }
  } else {
    echo "<script>alert('Gagal mengupload file.');</script>";
  }
}

// Query utama
$search = isset($_GET['search']) ? $koneksi->real_escape_string($_GET['search']) : '';
if($search){
  $q = $koneksi->query("SELECT id,nama,kelas,username,total_prestasi,total_pelanggaran FROM siswa WHERE nama LIKE '%$search%' OR kelas LIKE '%$search%' ORDER BY nama");
} else {
  $q = $koneksi->query("SELECT id,nama,kelas,username,total_prestasi,total_pelanggaran FROM siswa ORDER BY nama");
}
?>

<div class="max-w-7xl mx-auto p-6 bg-white-50 min-h-screen">
  <h1 class="text-4xl font-extrabold mb-8 text-gray-800 flex items-center gap-3">
    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0112 21.5a12.083 12.083 0 01-6.16-10.922L12 14z" />
    </svg>
    Kelola Siswa
  </h1>

  <!-- Upload Excel & Hapus Semua -->
  <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
    <!-- Upload Excel -->
    <form method="post" enctype="multipart/form-data" class="flex items-center gap-3 bg-white p-4 rounded-lg shadow-md shadow-gray-300">
      <input type="file" name="file_excel" accept=".xlsx,.xls" required class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 w-64">
      <button type="submit" name="import_excel" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-semibold">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Import Excel
      </button>
    </form>

    <!-- Hapus semua data siswa -->
    <a href="?hapus_semua=true" onclick="return confirm('Yakin hapus semua data siswa? tindakan ini tidak dapat dibatalkan.');" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg flex items-center gap-2 transition font-semibold shadow-md shadow-red-200">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4m4 4h8m-8-8h8" />
      </svg>
      Hapus Semua Data
    </a>
  </div>

  <!-- Form Tambah Siswa -->
  <div class="bg-white p-6 rounded-xl shadow-lg mb-8">
    <form class="grid grid-cols-1 md:grid-cols-4 gap-4" method="post">
      <input type="text" name="nama" placeholder="Nama Siswa" class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
      <input type="text" name="kelas" placeholder="Kelas" class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
      <input type="text" name="username" placeholder="Username" class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
      <input type="password" name="password" placeholder="Password" class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
      <button name="add_siswa" class="col-span-1 md:col-span-4 bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg flex items-center justify-center gap-2 font-semibold transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Siswa
      </button>
    </form>
  </div>

  <!-- Tabel Siswa -->
  <div class="overflow-x-auto bg-white rounded-xl shadow-lg border border-gray-200">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gradient-to-r from-green-100 to-green-200">
        <tr>
          <th class="px-4 py-3 text-left font-semibold text-gray-700">No</th>
          <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama</th>
          <th class="px-4 py-3 text-left font-semibold text-gray-700">Kelas</th>
          <th class="px-4 py-3 text-left font-semibold text-gray-700">Username</th>
          <th class="px-4 py-3 text-center font-semibold text-gray-700">Prestasi</th>
          <th class="px-4 py-3 text-center font-semibold text-gray-700">Pelanggaran</th>
          <th class="px-4 py-3 text-center font-semibold text-gray-700">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; while($r = $q->fetch_assoc()): ?>
        <tr class="border-t hover:bg-green-50 transition">
          <td class="px-4 py-3"><?= $no++ ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($r['nama']) ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($r['kelas']) ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($r['username']) ?></td>
          <td class="px-4 py-3 text-center">
            <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full"><?= $r['total_prestasi'] ?></span>
          </td>
          <td class="px-4 py-3 text-center">
            <span class="inline-block px-3 py-1 bg-red-100 text-red-700 rounded-full"><?= $r['total_pelanggaran'] ?></span>
          </td>
          <td class="px-4 py-3 flex justify-center gap-2">
            <a href="edit_siswa.php?id=<?=$r['id']?>" class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-lg flex items-center gap-1 transition shadow-md shadow-blue-200">
              <i class="fas fa-edit"></i>
              <span class="hidden md:inline">Edit</span>
            </a>
            <a href="?hapus=<?=$r['id']?>" onclick="return confirm('Yakin hapus siswa ini?');" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-lg flex items-center gap-1 transition shadow-md shadow-red-200">
              <i class="fas fa-trash"></i>
              <span class="hidden md:inline">Hapus</span>
            </a>
          </td>
        </tr>
        <?php endwhile; ?>
        <?php if($q->num_rows == 0): ?>
        <tr>
          <td colspan="7" class="px-4 py-6 text-center text-gray-500">Tidak ada data siswa.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../inc/footer.php'; ?>