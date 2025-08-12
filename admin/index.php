<?php
// admin/index.php
session_start();
require_once '../inc/koneksi.php';
require_once '../inc/auth.php';
require_once '../inc/header.php';
require_role('admin');

// Helper query function
function safe_query($koneksi, $sql) {
    $res = $koneksi->query($sql);
    if ($res === false) {
        return ['ok' => false, 'error' => $koneksi->error];
    }
    return ['ok' => true, 'result' => $res];
}

// Ambil data pelanggaran
$sql_pelanggaran_detail = "SELECT s.nama, s.kelas, p.kategori, p.deskripsi, p.poin, p.tanggal FROM pelanggaran p JOIN siswa s ON s.id = p.siswa_id ORDER BY p.tanggal DESC";
$tryP = safe_query($koneksi, $sql_pelanggaran_detail);
if ($tryP['ok']) {
    $pelanggaran_result = $tryP['result'];
    $pelanggaran_mode = 'detail';
} else {
    $sql_pelanggaran_fallback = "SELECT nama, kelas, total_pelanggaran FROM siswa ORDER BY total_pelanggaran DESC LIMIT 100";
    $tryP2 = safe_query($koneksi, $sql_pelanggaran_fallback);
    if ($tryP2['ok']) {
        $pelanggaran_result = $tryP2['result'];
        $pelanggaran_mode = 'summary';
    } else {
        $pelanggaran_result = false;
        $pelanggaran_error = "Query pelanggaran gagal: " . ($tryP2['error'] ?? $tryP['error']);
    }
}

// Ambil data prestasi
$sql_prestasi_detail = "SELECT s.nama, s.kelas, pr.kategori, pr.deskripsi, pr.poin, pr.tanggal FROM prestasi pr JOIN siswa s ON s.id = pr.siswa_id ORDER BY pr.tanggal DESC";
$tryPr = safe_query($koneksi, $sql_prestasi_detail);
if ($tryPr['ok']) {
    $prestasi_result = $tryPr['result'];
    $prestasi_mode = 'detail';
} else {
    $sql_prestasi_fallback = "SELECT nama, kelas, total_prestasi FROM siswa ORDER BY total_prestasi DESC LIMIT 100";
    $tryPr2 = safe_query($koneksi, $sql_prestasi_fallback);
    if ($tryPr2['ok']) {
        $prestasi_result = $tryPr2['result'];
        $prestasi_mode = 'summary';
    } else {
        $prestasi_result = false;
        $prestasi_error = "Query prestasi gagal: " . ($tryPr2['error'] ?? $tryPr['error']);
    }
}
?>

<!-- DataTables CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />

<style>
  /* Supaya DataTables tidak bentrok dengan Tailwind */
  table.dataTable thead th { background: #f9fafb; }
</style>
</head>
<body class="font-sans text-white-900">

<div class="max-w-7xl mx-auto p-6 bg-white-50 min-h-screen">

  <!-- Header Utama -->
  <div class="flex flex-col md:flex-row items-center justify-between mb-8">
    <div>
      <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800 flex items-center gap-3 mb-2 md:mb-0">
        <i class="fa-solid fa-school text-blue-600"></i> Dashboard Administrator
      </h1>
      <p class="text-gray-600 text-sm md:text-base">Kelola data siswa, pelanggaran, prestasi, dan laporan secara modern dan informatif.</p>
    </div>
    <div class="mt-4 md:mt-0 flex space-x-3">
      <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-md flex items-center gap-2 transition" onclick="location.reload()">
        <i class="fas fa-sync-alt"></i> Refresh
      </button>
      <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow-md flex items-center gap-2 transition" onclick="alert('Export data belum tersedia')">
        <i class="fas fa-file-export"></i> Export
      </button>
    </div>
  </div>

  <!-- Pesan Error -->
  <?php if (!empty($pelanggaran_error) || !empty($prestasi_error)): ?>
    <div class="grid md:grid-cols-2 gap-4 mb-8">
      <?php if (!empty($pelanggaran_error)): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg shadow hover:shadow-lg transition">
          <div class="flex items-center space-x-2 mb-2">
            <i class="fas fa-exclamation-triangle text-yellow-500"></i>
            <h3 class="font-semibold text-yellow-800 text-sm">Peringatan (Pelanggaran)</h3>
          </div>
          <p class="text-sm text-yellow-700"><?php echo htmlspecialchars($pelanggaran_error); ?></p>
        </div>
      <?php endif; ?>
      <?php if (!empty($prestasi_error)): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg shadow hover:shadow-lg transition">
          <div class="flex items-center space-x-2 mb-2">
            <i class="fas fa-exclamation-triangle text-yellow-500"></i>
            <h3 class="font-semibold text-yellow-800 text-sm">Peringatan (Prestasi)</h3>
          </div>
          <p class="text-sm text-yellow-700"><?php echo htmlspecialchars($prestasi_error); ?></p>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Card Ringkasan Data -->
  <div class="grid md:grid-cols-2 gap-6 mb-8">
    <!-- Card Pelanggaran -->
    <div class="bg-white rounded-lg shadow-lg border border-gray-200 hover:shadow-xl transition duration-300 p-4">
      <div class="flex items-center justify-between mb-4">
        <h2 class="flex items-center gap-2 text-xl font-semibold text-gray-800">
          <i class="fa-solid fa-triangle-exclamation text-red-500"></i> Data Pelanggaran
        </h2>
        <span class="text-sm text-gray-500">Overview</span>
      </div>
      <p class="text-gray-600 mb-4 text-sm">
        <?php if (isset($pelanggaran_mode) && $pelanggaran_mode === 'detail'): ?>
          Menampilkan catatan pelanggaran (detail).
        <?php elseif (isset($pelanggaran_mode) && $pelanggaran_mode === 'summary'): ?>
          Ringkasan total pelanggaran per siswa.
        <?php else: ?>
          Tidak ada data pelanggaran.
        <?php endif; ?>
      </p>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 rounded-lg" id="tabelPelanggaran">
          <thead class="bg-gray-50 text-gray-600 uppercase text-sm font-semibold">
            <tr>
              <th class="px-4 py-3 text-left">Nama</th>
              <th class="px-4 py-3 text-left">Kelas</th>
              <?php if (isset($pelanggaran_mode) && $pelanggaran_mode === 'detail'): ?>
                <th class="px-4 py-3 text-left">Kategori</th>
                <th class="px-4 py-3 text-left">Deskripsi</th>
                <th class="px-4 py-3 text-left">Poin</th>
                <th class="px-4 py-3 text-left">Tanggal</th>
              <?php else: ?>
                <th class="px-4 py-3 text-left">Total Pelanggaran</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200 text-gray-700">
            <?php if ($pelanggaran_result !== false): ?>
              <?php while ($row = $pelanggaran_result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50 transition">
                  <td class="px-4 py-3"><?php echo htmlspecialchars($row['nama']); ?></td>
                  <td class="px-4 py-3"><?php echo htmlspecialchars($row['kelas']); ?></td>
                  <?php if ($pelanggaran_mode === 'detail'): ?>
                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['kategori'] ?? '-'); ?></td>
                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['deskripsi'] ?? '-'); ?></td>
                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['poin'] ?? '-'); ?></td>
                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['tanggal'] ?? '-'); ?></td>
                  <?php else: ?>
                    <td class="px-4 py-3 font-semibold text-red-600"><?php echo htmlspecialchars($row['total_pelanggaran'] ?? '0'); ?></td>
                  <?php endif; ?>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="<?php echo ($pelanggaran_mode === 'detail') ? 6 : 3; ?>" class="px-4 py-4 text-center text-gray-500 text-sm">
                  Data pelanggaran tidak tersedia.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Card Prestasi -->
    <div class="bg-white rounded-lg shadow-lg border border-gray-200 hover:shadow-xl transition duration-300 p-4">
      <div class="flex items-center justify-between mb-4">
        <h2 class="flex items-center gap-2 text-xl font-semibold text-gray-800">
          <i class="fa-solid fa-trophy text-yellow-500"></i> Data Prestasi
        </h2>
        <span class="text-sm text-gray-500">Overview</span>
      </div>
      <p class="text-gray-600 mb-4 text-sm">
        <?php if (isset($prestasi_mode) && $prestasi_mode === 'detail'): ?>
          Menampilkan catatan prestasi (detail).
        <?php elseif (isset($prestasi_mode) && $prestasi_mode === 'summary'): ?>
          Ringkasan total prestasi per siswa.
        <?php else: ?>
          Tidak ada data prestasi.
        <?php endif; ?>
      </p>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 rounded-lg" id="tabelPrestasi">
          <thead class="bg-gray-50 text-gray-600 uppercase text-sm font-semibold">
            <tr>
              <th class="px-4 py-3 text-left">Nama</th>
              <th class="px-4 py-3 text-left">Kelas</th>
              <?php if (isset($prestasi_mode) && $prestasi_mode === 'detail'): ?>
                <th class="px-4 py-3 text-left">Kategori</th>
                <th class="px-4 py-3 text-left">Deskripsi</th>
                <th class="px-4 py-3 text-left">Poin</th>
                <th class="px-4 py-3 text-left">Tanggal</th>
              <?php else: ?>
                <th class="px-4 py-3 text-left">Total Prestasi</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200 text-gray-700">
            <?php if ($prestasi_result !== false): ?>
              <?php while ($row = $prestasi_result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50 transition">
                  <td class="px-4 py-3"><?php echo htmlspecialchars($row['nama']); ?></td>
                  <td class="px-4 py-3"><?php echo htmlspecialchars($row['kelas']); ?></td>
                  <?php if ($prestasi_mode === 'detail'): ?>
                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['kategori'] ?? '-'); ?></td>
                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['deskripsi'] ?? '-'); ?></td>
                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['poin'] ?? '-'); ?></td>
                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['tanggal'] ?? '-'); ?></td>
                  <?php else: ?>
                    <td class="px-4 py-3 font-semibold text-green-600"><?php echo htmlspecialchars($row['total_prestasi'] ?? '0'); ?></td>
                  <?php endif; ?>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="<?php echo ($prestasi_mode === 'detail') ? 6 : 3; ?>" class="px-4 py-4 text-center text-gray-500 text-sm">
                  Data prestasi tidak tersedia.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Shortcut Navigasi -->
  <div class="grid md:grid-cols-4 gap-6 mb-8">
    <a href="kelola_siswa.php" class="bg-white rounded-lg shadow hover:shadow-xl transform transition hover:scale-105 border-2 border-gray-200 p-4 flex flex-col items-center hover:border-blue-400">
      <i class="fas fa-user-graduate text-4xl mb-2 text-blue-600"></i>
      <span class="font-semibold text-gray-700">Kelola Siswa</span>
    </a>
    <a href="kelola_guru.php" class="bg-white rounded-lg shadow hover:shadow-xl transform transition hover:scale-105 border-2 border-gray-200 p-4 flex flex-col items-center hover:border-blue-400">
      <i class="fas fa-chalkboard-teacher text-4xl mb-2 text-blue-600"></i>
      <span class="font-semibold text-gray-700">Kelola Guru</span>
    </a>
  </div>

</div>

<!-- DataTables JS -->
<script>
$(document).ready(function () {
    $('#tabelPelanggaran, #tabelPrestasi').DataTable({
        pageLength: 10,
        responsive: true,
        autoWidth: false,
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
    });
});
</script>

<?php include '../inc/footer.php'; ?>