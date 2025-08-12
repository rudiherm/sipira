<?php
// file_pdf.php
$page_title = "File PDF";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('siswa');
// Folder tempat file PDF (sesuaikan dengan folder sebenarnya)
$pdf_folder = realpath(__DIR__ . '/../uploads/pdf/') . '/';
// Pastikan path URL sesuai dengan struktur folder di server Anda
$pdf_url_base = '../uploads/pdf/';
// Baca semua file PDF dari folder tersebut
$pdf_files = [];
$error_msg = '';
if (is_dir($pdf_folder)) {
  $files = scandir($pdf_folder);
  foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    $file_path = $pdf_folder . $file;
    if (is_file($file_path) && strtolower(pathinfo($file_path, PATHINFO_EXTENSION)) === 'pdf') {
      $pdf_files[] = $file;
    }
  }
} else {
  $error_msg = "Folder file PDF tidak ditemukan.";
}
?>
<div class="mb-8 px-4 md:px-0 max-w-7xl mx-auto">
  <h1 class="text-3xl font-bold mb-6">Daftar Dokumen</h1>
  <?php if (!empty($error_msg)): ?>
  <div class="bg-red-100 text-red-700 p-4 rounded mb-6"><?= htmlspecialchars($error_msg) ?></div>
  <?php endif; ?>
  <?php if (count($pdf_files) > 0): ?>
  <div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-gray-200 rounded shadow">
    <thead>
      <tr class="bg-gray-100">
      <th class="py-2 px-4 border-b">No</th>
      <th class="py-2 px-4 border-b">Nama File</th>
      <th class="py-2 px-4 border-b">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($pdf_files as $i => $pdf): ?>
      <tr>
        <td class="py-2 px-4 border-b text-center"><?= $i + 1 ?></td>
        <td class="py-2 px-4 border-b"><?= htmlspecialchars($pdf) ?></td>
        <td class="py-2 px-4 border-b text-center space-x-2">
        <a href="<?= htmlspecialchars($pdf_url_base . $pdf) ?>" class="lihat-pdf inline-block px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Lihat</a>
        <a href="<?= htmlspecialchars($pdf_url_base . $pdf) ?>" download class="inline-block px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 transition">Unduh</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    </table>
  </div>
  <?php else: ?>
  <p class="text-gray-600">Belum ada file PDF yang tersedia.</p>
  <?php endif; ?>
  <div class="mt-8">
  <a href="index.php" class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Kembali ke Dashboard</a>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.lihat-pdf').forEach(function(link) {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const pdfUrl = this.getAttribute('href');
      showPdfPreview(pdfUrl);
    });
  });
  function showPdfPreview(url) {
    let overlay = document.getElementById('pdf-preview-overlay');
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.id = 'pdf-preview-overlay';
      overlay.style.position = 'fixed';
      overlay.style.top = 0;
      overlay.style.left = 0;
      overlay.style.width = '100vw';
      overlay.style.height = '100vh';
      overlay.style.background = 'rgba(0,0,0,0.7)';
      overlay.style.display = 'flex';
      overlay.style.alignItems = 'center';
      overlay.style.justifyContent = 'center';
      overlay.style.zIndex = 10000;
      overlay.innerHTML = `
        <div style="background:#fff;max-width:90vw;max-height:90vh;position:relative;border-radius:8px;overflow:hidden;">
          <button id="close-pdf-preview" style="position:absolute;top:8px;right:8px;background:#e53e3e;color:#fff;border:none;padding:6px 12px;border-radius:4px;cursor:pointer;z-index:2;">Tutup</button>
          <iframe src="" style="width:80vw;height:80vh;border:none;display:block;" allowfullscreen></iframe>
        </div>
      `;
      document.body.appendChild(overlay);
      overlay.querySelector('#close-pdf-preview').onclick = function() {
        overlay.style.display = 'none';
        overlay.querySelector('iframe').src = '';
      };
    }
    overlay.querySelector('iframe').src = url;
    overlay.style.display = 'flex';
  }
});
</script>
<?php include '../inc/footer.php'; ?>