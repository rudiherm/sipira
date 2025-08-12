<?php
$page_title = "Tata Tertib";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('guru');

$msg = '';

// Hapus
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $koneksi->query("DELETE FROM tata_tertib WHERE id=$id");
    $msg = "Data berhasil dihapus.";
}

// Tambah 3 poin sekaligus (keterangan + nomor)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $judul = $_POST['judul'];
    $keterangan = $_POST['keterangan'];

    $poin_list = [
        intval($_POST['poin1']),
        intval($_POST['poin2']),
        intval($_POST['poin3'])
    ];

    $stmt = $koneksi->prepare("INSERT INTO tata_tertib (judul, keterangan, poin) VALUES (?,?,?)");
    $berhasil = true;

    foreach ($poin_list as $index => $poin) {
        $keterangan_fix = $keterangan . " - " . ($index + 1); // Tambahkan " - 1", " - 2", " - 3"
        $stmt->bind_param("ssi", $judul, $keterangan_fix, $poin);
        if (!$stmt->execute()) {
            $berhasil = false;
            break;
        }
    }

    $msg = $berhasil ? "3 Tata tertib berhasil ditambahkan." : "Gagal menambahkan data.";
}

// Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = intval($_POST['edit_id']);
    $judul = $_POST['judul'];
    $keterangan = $_POST['keterangan'];
    $poin = intval($_POST['poin']);
    $stmt = $koneksi->prepare("UPDATE tata_tertib SET judul=?, keterangan=?, poin=? WHERE id=?");
    $stmt->bind_param("ssii", $judul, $keterangan, $poin, $id);
    if ($stmt->execute()) {
        $msg = "Tata tertib berhasil diperbarui.";
    } else {
        $msg = "Gagal memperbarui data.";
    }
}

$data = $koneksi->query("SELECT * FROM tata_tertib ORDER BY dibuat_pada DESC");
?>

<h1 class="text-3xl font-bold mb-6 text-gray-900 px-4 sm:px-0">Data Poin Pelanggaran</h1>

<?php if ($msg): ?>
  <div class="max-w-4xl mx-auto mb-6 p-4 rounded-lg
      <?= strpos($msg, 'berhasil') !== false ? 'bg-green-100 border-green-600 text-green-800' : 'bg-red-100 border-red-600 text-red-800' ?> 
      border-l-4 shadow px-4 sm:px-6">
    <?= htmlspecialchars($msg) ?>
  </div>
<?php endif; ?>

<!-- Form tambah -->
<div class="max-w-full mx-auto bg-white rounded-xl p-6 sm:p-8 mb-8 px-4 sm:px-6">
  <h2 class="text-xl font-bold mb-4 border-b pb-2">Tambah Data</h2>
  <form method="post" class="space-y-6" novalidate>
    <input type="hidden" name="tambah" value="1">
    <div>
      <label for="judul" class="block mb-2 font-medium text-gray-700">Judul</label>
      <input id="judul" name="judul" type="text" required placeholder="Masukkan judul tata tertib"
        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:outline-none transition" />
    </div>
    <div>
      <label for="keterangan" class="block mb-2 font-medium text-gray-700">Keterangan</label>
      <textarea id="keterangan" name="keterangan" required rows="4" placeholder="Masukkan keterangan"
        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:outline-none transition resize-none"></textarea>
    </div>

    <!-- Input 3 poin -->
    <div>
      <label class="block mb-2 font-medium text-gray-700">Poin Pelanggaran 1</label>
      <input name="poin1" type="number" min="0" value="0" required class="w-full border border-gray-300 rounded-lg px-4 py-3" />
    </div>
    <div>
      <label class="block mb-2 font-medium text-gray-700">Poin Pelanggaran 2</label>
      <input name="poin2" type="number" min="0" value="0" required class="w-full border border-gray-300 rounded-lg px-4 py-3" />
    </div>
    <div>
      <label class="block mb-2 font-medium text-gray-700">Poin Pelanggaran 3</label>
      <input name="poin3" type="number" min="0" value="0" required class="w-full border border-gray-300 rounded-lg px-4 py-3" />
    </div>

    <button type="submit"
      class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg px-6 py-3 transition shadow inline-flex items-center gap-2 justify-center">
      <i class="fa-solid fa-plus"></i> Simpan
    </button>
  </form>
</div>

<!-- Tabel data (5 per halaman) -->
<div class="max-w-full mx-auto bg-white rounded-xl shadow overflow-x-auto px-4 sm:px-6">
  <table class="min-w-full divide-y divide-gray-200 table-auto sm:table-fixed">
    <thead class="bg-50">
      <tr>
        <th class="px-4 sm:px-6 py-3 text-center text-sm font-semibold text-gray-700 uppercase tracking-wide">No</th>
        <th class="px-4 sm:px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wide">Judul</th>
        <th class="px-4 sm:px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wide">Keterangan</th>
        <th class="px-4 sm:px-6 py-3 text-center text-sm font-semibold text-gray-700 uppercase tracking-wide w-20">Poin</th>
        <th class="px-4 sm:px-6 py-3 text-center text-sm font-semibold text-gray-700 uppercase tracking-wide w-36">Aksi</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
      <?php 
      // Ambil data sesuai halaman
      $per_page = 5;
      $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
      $offset = ($page - 1) * $per_page;
      $data_limit = $koneksi->query("SELECT * FROM tata_tertib ORDER BY dibuat_pada DESC LIMIT $per_page OFFSET $offset");
      $no = $offset + 1;
      while ($r = $data_limit->fetch_assoc()): ?>
        <tr class="hover:bg-gray-50 transition">
          <td class="px-4 sm:px-6 py-4 text-center"><?= $no++ ?></td>
          <td class="px-4 sm:px-6 py-4 font-semibold text-gray-900 max-w-xs break-words"><?= htmlspecialchars($r['judul']) ?></td>
          <td class="px-4 sm:px-6 py-4 text-gray-700 max-w-lg break-words whitespace-pre-line"><?= htmlspecialchars($r['keterangan']) ?></td>
          <td class="px-4 sm:px-6 py-4 text-center text-red-600 font-bold"><?= $r['poin'] ?></td>
          <td class="px-4 sm:px-6 py-4 text-center">
            <div class="flex items-center justify-center gap-2 sm:gap-3">
              <button
                onclick="openEditModal('<?= htmlspecialchars(addslashes($r['judul'])) ?>', '<?= htmlspecialchars(addslashes($r['keterangan'])) ?>', <?= $r['poin'] ?>, <?= $r['id'] ?>)"
                class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 sm:px-4 sm:py-2 rounded shadow transition font-semibold inline-flex items-center gap-2"
                aria-label="Edit tata tertib">
                <i class="fa-solid fa-pen-to-square"></i> Edit
              </button>
              <a href="?hapus=<?= $r['id'] ?>" onclick="return confirm('Yakin hapus?')"
                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 sm:px-4 sm:py-2 rounded shadow transition font-semibold inline-flex items-center gap-2">
                <i class="fa-solid fa-trash"></i> Hapus
              </a>
            </div>
          </td>
        </tr>
      <?php endwhile; ?>
      <?php if ($data_limit->num_rows === 0): ?>
        <tr>
          <td colspan="5" class="text-center py-10 text-gray-400">Belum ada data tata tertib.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php
// Navigasi pagination responsif & rapi
$total_data = $koneksi->query("SELECT COUNT(*) as total FROM tata_tertib")->fetch_assoc()['total'];
$total_page = ceil($total_data / $per_page);

if ($total_page > 1): ?>
  <nav class="max-w-full mx-auto mt-6 flex flex-wrap gap-2 px-2 sm:px-0 items-center justify-center">
    <ul class="inline-flex flex-wrap items-center gap-1">
      <?php
      // Tombol prev
      if ($page > 1): ?>
        <li>
          <a href="?page=<?= $page - 1 ?>"
             class="px-3 py-2 rounded font-semibold shadow transition bg-gray-200 text-gray-700 hover:bg-blue-100"
             aria-label="Sebelumnya">&laquo;</a>
        </li>
      <?php endif; ?>

      <?php
      // Tampilkan max 5 halaman, dengan current di tengah jika memungkinkan
      $start = max(1, $page - 2);
      $end = min($total_page, $start + 4);
      if ($end - $start < 4) $start = max(1, $end - 4);

      for ($i = $start; $i <= $end; $i++):
        $active = $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-blue-100';
      ?>
        <li>
          <a href="?page=<?= $i ?>" class="px-3 py-2 rounded font-semibold shadow transition <?= $active ?>">
            <?= $i ?>
          </a>
        </li>
      <?php endfor; ?>

      <?php
      // Tombol next
      if ($page < $total_page): ?>
        <li>
          <a href="?page=<?= $page + 1 ?>"
             class="px-3 py-2 rounded font-semibold shadow transition bg-gray-200 text-gray-700 hover:bg-blue-100"
             aria-label="Berikutnya">&raquo;</a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
<?php endif; ?>

<!-- Modal Edit -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 pointer-events-none transition-opacity p-4">
  <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-full overflow-auto p-6 relative">
    <h3 class="text-xl font-semibold mb-4">Edit Tata Tertib</h3>
    <form method="post" id="editForm" class="space-y-6" novalidate>
      <input type="hidden" name="edit_id" id="edit_id" />
      <div>
        <label for="edit_judul" class="block mb-2 font-medium text-gray-700">Judul</label>
        <input id="edit_judul" name="judul" type="text" required
          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:outline-none transition" />
      </div>
      <div>
        <label for="edit_keterangan" class="block mb-2 font-medium text-gray-700">Keterangan</label>
        <textarea id="edit_keterangan" name="keterangan" required rows="4"
          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:outline-none transition resize-none"></textarea>
      </div>
      <div>
        <label for="edit_poin" class="block mb-2 font-medium text-gray-700">Poin Pelanggaran</label>
        <input id="edit_poin" name="poin" type="number" min="0" required
          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:outline-none transition" />
      </div>
      <div class="flex flex-col sm:flex-row justify-end gap-4">
        <button type="button" onclick="closeEditModal()" class="px-5 py-2 rounded bg-gray-300 hover:bg-gray-400 transition font-semibold w-full sm:w-auto inline-flex items-center gap-2 justify-center">
          <i class="fa-solid fa-xmark"></i> Batal
        </button>
        <button type="submit" class="px-6 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white font-semibold transition shadow inline-flex items-center gap-2 justify-center w-full sm:w-auto">
          <i class="fa-solid fa-floppy-disk"></i> Simpan
        </button>
      </div>
    </form>
    <button aria-label="Close modal" onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-3xl font-bold">&times;</button>
  </div>
</div>

<script>
  function openEditModal(judul, keterangan, poin, id) {
    document.getElementById('edit_judul').value = judul;
    document.getElementById('edit_keterangan').value = keterangan;
    document.getElementById('edit_poin').value = poin;
    document.getElementById('edit_id').value = id;

    const modal = document.getElementById('editModal');
    modal.classList.remove('opacity-0', 'pointer-events-none');
  }

  function closeEditModal() {
    const modal = document.getElementById('editModal');
    modal.classList.add('opacity-0', 'pointer-events-none');
  }

  document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
  });
</script>

<?php include '../inc/footer.php'; ?>
