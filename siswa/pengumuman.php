<?php
$page_title = "Daftar Pengumuman";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('siswa');
$user_id = $_SESSION['user_id'] ?? 0;
$q = $koneksi->query("
  SELECT id, judul, isi, tgl_posting, ditujukan_untuk, id_siswa_tertentu
  FROM pengumuman
  WHERE ditujukan_untuk IN ('semua', 'tertentu')
  ORDER BY tgl_posting DESC
");
?>
<h1 class="text-3xl font-extrabold mb-8 text-center text-gray-900">Daftar Pengumuman</h1>
<div class="max-w-4xl mx-auto space-y-6" role="list" aria-label="Daftar pengumuman">
  <?php
  $ada = false;
  while ($row = $q->fetch_assoc()):
    if ($row['ditujukan_untuk'] === 'semua') {
      $tampilkan = true;
    } elseif ($row['ditujukan_untuk'] === 'tertentu') {
      $ids = json_decode($row['id_siswa_tertentu'], true);
      $tampilkan = is_array($ids) && in_array($user_id, $ids);
    } else {
      $tampilkan = false;
    }
    if (!$tampilkan) continue;
    $ada = true;
    $preview = strip_tags($row['isi']);
    if (mb_strlen($preview) > 180) {
      $preview = mb_substr($preview, 0, 180) . '...';
    }
    $judul_esc = htmlspecialchars($row['judul']);
    $tgl = date('d M Y', strtotime($row['tgl_posting']));
  ?>
  <article
    tabindex="0"
    role="listitem"
    data-id="<?= $row['id'] ?>"
    data-judul="<?= $judul_esc ?>"
    data-isi="<?= htmlspecialchars($row['isi']) ?>"
    data-tgl="<?= $tgl ?>"
    class="border rounded-lg shadow-md p-6 hover:shadow-lg transition cursor-pointer bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
  >
    <header class="flex justify-between items-center mb-3">
      <h2 class="text-xl font-semibold text-gray-900"><?= $judul_esc ?></h2>
      <time class="text-sm text-gray-400 font-mono" datetime="<?= date('Y-m-d', strtotime($row['tgl_posting'])) ?>">
        <?= $tgl ?>
      </time>
    </header>
    <p class="text-gray-700 leading-relaxed whitespace-pre-line"><?= htmlspecialchars($preview) ?></p>
  </article>
  <?php endwhile; ?>
  <?php if (!$ada): ?>
    <p class="p-6 text-center text-gray-500 italic">Belum ada pengumuman.</p>
  <?php endif; ?>
</div>
<div id="modal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg max-w-3xl w-full p-8 relative shadow-2xl max-h-[90vh] overflow-y-auto">
    <button id="modalClose" class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 text-3xl font-bold leading-none focus:outline-none" aria-label="Tutup modal">&times;</button>
    <h3 id="modalTitle" class="text-2xl font-bold mb-5 text-gray-900"></h3>
    <div id="modalContent" class="prose max-w-full mb-6 text-gray-700 whitespace-pre-wrap"></div>
    <time id="modalDate" class="block text-gray-500 text-sm font-mono"></time>
  </div>
</div>
<div class="max-w-4xl mx-auto mt-8">
  <a href="index.php" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-md shadow-md transition focus:outline-none focus:ring-2 focus:ring-blue-400">
    <i class="fas fa-arrow-left mr-3"></i> Kembali
  </a>
</div>
<script>
  const modal = document.getElementById('modal');
  const modalTitle = document.getElementById('modalTitle');
  const modalContent = document.getElementById('modalContent');
  const modalDate = document.getElementById('modalDate');
  const modalClose = document.getElementById('modalClose');
  document.querySelectorAll('[role="listitem"]').forEach(item => {
    item.addEventListener('click', () => {
      const judul = item.getAttribute('data-judul');
      const isi = item.getAttribute('data-isi');
      const tgl = item.getAttribute('data-tgl');
      modalTitle.textContent = judul;
      modalContent.innerHTML = isi.replace(/\n/g, "<br>");
      modalDate.textContent = tgl;
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      modalClose.focus();
    });
    item.addEventListener('keydown', e => {
      if (e.key === 'Enter') {
        e.preventDefault();
        item.click();
      }
    });
  });
  modalClose.addEventListener('click', () => {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  });
  modal.addEventListener('click', e => {
    if (e.target === modal) {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }
  });
</script>
<?php include '../inc/footer.php'; ?>