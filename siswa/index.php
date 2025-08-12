<?php
$page_title = "Dashboard Siswa";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('siswa');
if (!isset($_SESSION['user_id'])) {
  header('Location: ../login.php');
  exit;
}
$id = $_SESSION['user_id'];
$stmt = $koneksi->prepare("SELECT id, nama, kelas, total_prestasi, total_pelanggaran, foto FROM siswa WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$siswa = $res->fetch_assoc();
$stmt->close();
if (!$siswa) {
  echo "<div style='color:red; text-align:center; margin-top:2em;'>Data siswa tidak ditemukan.</div>";
  include '../inc/footer.php';
  exit;
}
$foto_folder = '/uploads/foto_siswa/';
$foto_path = '';
$foto_url = '';
if (!empty($siswa['foto'])) {
  $foto_path = __DIR__ . '/../' . trim($foto_folder, '/') . '/' . $siswa['foto'];
  if (file_exists($foto_path)) {
    $foto_url = $foto_folder . $siswa['foto'];
  }
}
function getSuratPeringatan($poin)
{
  if ($poin >= 76) return "Surat Peringatan Ketiga (SP 3)";
  if ($poin >= 51) return "Surat Peringatan Kedua (SP 2)";
  if ($poin >= 25) return "Surat Peringatan Pertama (SP 1)";
  return "Tidak ada Surat Peringatan";
}
function getPredikatPrestasi($poin)
{
  if ($poin >= 151) return "Sertifikat, Hadiah dan gelar “Anugerah Waluya Utama”";
  if ($poin >= 126) return "Sertifikat dan Hadiah sebagai siswa berprestasi";
  if ($poin >= 100) return "Sertifikat sebagai siswa berprestasi";
  return "Tidak ada predikat prestasi";
}
$surat_peringatan = getSuratPeringatan(intval($siswa['total_pelanggaran']));
$predikat_prestasi = getPredikatPrestasi(intval($siswa['total_prestasi']));
?>
<div class="mb-8 px-4 md:px-0 max-w-7xl mx-auto">
  <div class="bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 text-white rounded-2xl shadow-lg overflow-hidden p-6 mb-6">
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
      <div class="flex items-center gap-5">
      <div>
        <h1 class="text-4xl font-extrabold drop-shadow-lg">Halo, <?= htmlspecialchars($siswa['nama']) ?> 👋</h1>
        <p class="text-lg md:text-xl mt-1 opacity-90">Kelas: <span class="font-semibold"><?= htmlspecialchars($siswa['kelas']) ?></span></p>
      </div>
      </div>
      <div class="flex flex-wrap gap-3">
        <a href="profil.php" class="px-4 py-2 rounded-full bg-white text-blue-600 font-medium shadow-md hover:shadow-lg transition flex items-center gap-2">
          <i class="fa-solid fa-key"></i> Ganti Password
        </a>
        <a href="riwayat_prestasi.php" class="px-4 py-2 rounded-full bg-green-500 text-white font-medium shadow-md hover:shadow-lg transition flex items-center gap-2">
          <i class="fa-solid fa-medal"></i> Prestasi
        </a>
        <a href="file_pdf.php" class="px-4 py-2 rounded-full bg-red-500 text-white font-medium shadow-md hover:shadow-lg transition flex items-center gap-2">
          <i class="fa-solid fa-file-pdf"></i> Dokumen
        </a>
      </div>
    </div>
  </div>
  <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg mb-6 shadow-sm flex items-start gap-3" role="alert" aria-live="polite">
    <i class="fas fa-exclamation-triangle text-red-600 text-2xl mt-1" aria-hidden="true"></i>
    <div>
      <p class="font-bold text-red-800">Surat Peringatan:</p>
      <p class="text-red-700">
        <?= htmlspecialchars($surat_peringatan) ?>
      </p>
    </div>
  </div>
  <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg mb-6 shadow-sm flex items-start gap-3" role="alert" aria-live="polite">
    <i class="fas fa-trophy text-green-600 text-2xl mt-1" aria-hidden="true"></i>
    <div>
      <p class="font-bold text-green-800">Predikat Prestasi:</p>
      <p class="text-green-700">
        <?= htmlspecialchars($predikat_prestasi) ?>
      </p>
    </div>
  </div>
<div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
  <div class="bg-white rounded-2xl p-6 shadow-lg transform transition hover:-translate-y-1">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="p-3 rounded-full bg-green-50" title="Ikon prestasi">
          <i class="fas fa-trophy text-green-600 text-2xl" aria-hidden="true"></i>
        </div>
        <div>
          <p class="text-sm text-gray-500">Total Prestasi</p>
          <h3 class="text-3xl font-bold text-green-600" id="count-prestasi">0</h3>
        </div>
      </div>
    </div>
    <p class="text-xs mt-3 text-gray-400">Semangat! Catatan prestasi Anda tersimpan di riwayat.</p>
  </div>
  <div class="bg-white rounded-2xl p-6 shadow-lg transform transition hover:-translate-y-1">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="p-3 rounded-full bg-red-50" title="Ikon pelanggaran">
          <i class="fas fa-exclamation-triangle text-red-600 text-2xl" aria-hidden="true"></i>
        </div>
        <div>
          <p class="text-sm text-gray-500">Total Pelanggaran</p>
          <h3 class="text-3xl font-bold text-red-600" id="count-pelanggaran"><?= intval($siswa['total_pelanggaran']) ?></h3>
        </div>
      </div>
    </div>
    <div class="mt-4">
      <div class="flex items-center justify-between mb-1">
        <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($surat_peringatan) ?></span>
      </div>
    </div>
  </div>
  <div class="bg-white rounded-2xl p-6 shadow-lg transform transition hover:-translate-y-1">
    <p class="text-sm text-gray-500">Akses Cepat</p>
    <div class="mt-4 grid grid-cols-1 gap-3">
      <a href="riwayat_prestasi.php" class="flex items-center gap-3 p-3 rounded-lg bg-white hover:shadow-md transition transform hover:-translate-y-0.5" title="Riwayat Prestasi">
        <i class="fas fa-medal text-green-500 text-xl" aria-hidden="true"></i>
        <div>
          <div class="font-semibold">Riwayat Prestasi</div>
          <div class="text-xs text-gray-500">Lihat semua prestasi</div>
        </div>
      </a>
      <a href="riwayat_pelanggaran.php" class="flex items-center gap-3 p-3 rounded-lg bg-white hover:shadow-md transition transform hover:-translate-y-0.5" title="Riwayat Pelanggaran">
        <i class="fas fa-history text-red-500 text-xl" aria-hidden="true"></i>
        <div>
          <div class="font-semibold">Riwayat Pelanggaran</div>
          <div class="text-xs text-gray-500">Detail catatan pelanggaran</div>
        </div>
      </a>
      <a href="pengumuman.php" class="flex items-center gap-3 p-3 rounded-lg bg-white hover:shadow-md transition transform hover:-translate-y-0.5" title="Pengumuman">
        <i class="fas fa-bullhorn text-yellow-500 text-xl" aria-hidden="true"></i>
        <div>
          <div class="font-semibold">Pengumuman</div>
          <div class="text-xs text-gray-500">Informasi penting & update</div>
        </div>
      </a>
      <a href="file_pdf.php" class="flex items-center gap-3 p-3 rounded-lg bg-white hover:shadow-md transition transform hover:-translate-y-0.5" title="Lihat File PDF">
        <i class="fas fa-file-pdf text-red-600 text-xl" aria-hidden="true"></i>
        <div>
          <div class="font-semibold">Dokumen Pancawaluya</div>
          <div class="text-xs text-gray-500">Dokumen & materi terkait</div>
        </div>
      </a>
    </div>
  </div>
</div>
</div>
<script>
  function countUp(elId, endValue, duration = 1200) {
    const el = document.getElementById(elId);
    if (!el) return;
    let start = 0;
    let startTime = null;
    function animate(time) {
      if (!startTime) startTime = time;
      let progress = time - startTime;
      let val = Math.min(Math.floor((progress / duration) * endValue), endValue);
      el.textContent = val.toLocaleString();
      if (val < endValue) {
        requestAnimationFrame(animate);
      }
    }
    requestAnimationFrame(animate);
  }
  countUp('count-prestasi', <?= intval($siswa['total_prestasi']) ?>);
  countUp('count-pelanggaran', <?= intval($siswa['total_pelanggaran']) ?>);
</script>
<?php include '../inc/footer.php'; ?>