<?php
$page_title = "Riwayat Prestasi";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('siswa');

// Check if user_id is set in session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['user_id'];

$stmt = $koneksi->prepare("SELECT id, keterangan, poin, tanggal FROM prestasi WHERE id_siswa=? ORDER BY tanggal DESC");
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($koneksi->error));
}

$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
?>

<div class="w-full px-4 sm:px-6 lg:px-8 py-8">
  <div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Riwayat Prestasi</h1>
      <a href="index.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
      </a>
    </div>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">
      <div class="overflow-x-auto">
      <table class="w-full table-auto">
        <thead class="bg-gray-50">
        <tr>
          <th class="px-8 py-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider w-1/12">No</th>
          <th class="px-8 py-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider w-1/6">Tanggal</th>
          <th class="px-8 py-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider w-4/6">Keterangan</th>
          <th class="px-8 py-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider w-1/6">Poin</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
        <?php $no = 1; while ($row = $res->fetch_assoc()): ?>
          <tr class="hover:bg-gray-50 transition">
          <td class="px-8 py-4 text-sm text-gray-700"><?= $no++ ?></td>
          <td class="px-8 py-4 text-sm text-gray-700">
            <div class="flex items-center gap-2">
            <i class="far fa-calendar-alt text-blue-500"></i>
            <span><?= htmlspecialchars(date('d M Y', strtotime($row['tanggal']))) ?></span>
            </div>
          </td>
          <td class="px-8 py-4 text-sm text-gray-700">
            <?= htmlspecialchars($row['keterangan']) ?>
          </td>
          <td class="px-8 py-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
            <i class="fas fa-star text-yellow-500 mr-1"></i>
            <?= htmlspecialchars($row['poin']) ?>
            </span>
          </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>

      <?php if ($res->num_rows === 0): ?>
        <div class="p-8 text-center text-gray-500">
        <i class="fas fa-info-circle text-blue-400 text-lg mr-2"></i>
        Belum ada riwayat prestasi.
        </div>
      <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php
$stmt->close();
include '../inc/footer.php';
?>
