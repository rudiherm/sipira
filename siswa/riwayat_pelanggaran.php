<?php
$page_title = "Riwayat Pelanggaran";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('siswa');

$id = $_SESSION['user_id'];

// Ambil data siswa
$stmt_siswa = $koneksi->prepare("SELECT nama FROM siswa WHERE id=?");
$stmt_siswa->bind_param("i", $id);
$stmt_siswa->execute();
$res_siswa = $stmt_siswa->get_result();
$siswa = $res_siswa->fetch_assoc();

// Ambil data pelanggaran (tanpa foto)
$stmt = $koneksi->prepare("SELECT id, keterangan, poin, tanggal FROM pelanggaran WHERE id_siswa=? ORDER BY tanggal DESC");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
?>

<div class="w-full px-4 sm:px-6 lg:px-8 py-8">
  <div class="max-w-7xl mx-auto">
  <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Riwayat Pelanggaran</h1>
      <a href="index.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
      </a>
    </div>

  <!-- Wrapper scroll horizontal untuk tabel -->
   <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">
      <div class="overflow-x-auto">
        <table class="w-full table-auto">
          <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2 text-left whitespace-nowrap">No</th>
          <th class="px-3 py-2 text-left whitespace-nowrap">Tanggal</th>
          <th class="px-3 py-2 text-left">Keterangan</th>
          <th class="px-3 py-2 text-left whitespace-nowrap">Poin</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; ?>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr class="border-t hover:bg-gray-50">
          <td class="px-3 py-2 whitespace-nowrap"><?= $no++ ?></td>
          <td class="px-3 py-2 whitespace-nowrap">
            <i class="far fa-calendar-alt text-blue-500 mr-1"></i>
            <?= date('d M Y', strtotime($row['tanggal'])) ?>
          </td>
          <td class="px-3 py-2 break-words max-w-xs"><?= htmlspecialchars($row['keterangan']) ?></td>
          <td class="px-3 py-2 text-red-600 font-semibold">
            <i class="fas fa-exclamation-circle mr-1"></i>
            <?= $row['poin'] ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <?php if ($res->num_rows == 0): ?>
      <div class="p-4 text-center text-gray-500 italic">
        <i class="fas fa-info-circle mr-1"></i> Belum ada pelanggaran.
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include '../inc/footer.php'; ?>
