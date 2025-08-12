<?php
$page_title = "Laporan";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('admin');

$q = $koneksi->query("SELECT id,nama,kelas,total_prestasi,total_pelanggaran FROM siswa ORDER BY nama");
?>
<h1 class="text-2xl font-bold mb-4">Laporan Rekapitulasi</h1>

<div class="bg-white rounded shadow overflow-x-auto">
  <table class="min-w-full text-sm">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-4 py-2">Nama</th><th class="px-4 py-2">Kelas</th><th class="px-4 py-2">Prestasi</th><th class="px-4 py-2">Pelanggaran</th>
      </tr>
    </thead>
    <tbody>
      <?php while($r = $q->fetch_assoc()): ?>
      <tr class="border-t hover:bg-gray-50">
        <td class="px-4 py-2"><?=htmlspecialchars($r['nama'])?></td>
        <td class="px-4 py-2"><?=htmlspecialchars($r['kelas'])?></td>
        <td class="px-4 py-2"><?= $r['total_prestasi'] ?></td>
        <td class="px-4 py-2"><?= $r['total_pelanggaran'] ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include '../inc/footer.php'; ?>