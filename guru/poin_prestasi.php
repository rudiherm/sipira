<?php
$page_title = "Poin Prestasi";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('guru');

$msg = '';

// Hapus
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $koneksi->query("DELETE FROM poin_prestasi WHERE id=$id");
    $msg = "Data prestasi berhasil dihapus.";
}

// Tambah 1 poin saja
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $judul = $_POST['judul'];
    $keterangan = $_POST['keterangan'];
    $poin = intval($_POST['poin1']);

    $stmt = $koneksi->prepare("INSERT INTO poin_prestasi (judul, keterangan, poin) VALUES (?,?,?)");
    $stmt->bind_param("ssi", $judul, $keterangan, $poin);

    if ($stmt->execute()) {
        $msg = "Prestasi berhasil ditambahkan.";
    } else {
        $msg = "Gagal menambahkan data.";
    }
}

// Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = intval($_POST['edit_id']);
    $judul = $_POST['judul'];
    $keterangan = $_POST['keterangan'];
    $poin = intval($_POST['poin']);
    $stmt = $koneksi->prepare("UPDATE poin_prestasi SET judul=?, keterangan=?, poin=? WHERE id=?");
    $stmt->bind_param("ssii", $judul, $keterangan, $poin, $id);
    if ($stmt->execute()) {
        $msg = "Prestasi berhasil diperbarui.";
    } else {
        $msg = "Gagal memperbarui data.";
    }
}

$data = $koneksi->query("SELECT * FROM poin_prestasi ORDER BY dibuat_pada DESC");
?>

<h1 class="text-3xl font-bold mb-6 text-gray-900 px-4 sm:px-0">Data Poin Prestasi</h1>

<?php if ($msg): ?>
  <div class="max-w-4xl mx-auto mb-6 p-4 rounded-lg
      <?= strpos($msg, 'berhasil') !== false ? 'bg-green-100 border-green-500 text-green-800' : 'bg-red-100 border-red-500 text-red-800' ?> 
      border-l-4 shadow px-4 sm:px-6">
    <?= htmlspecialchars($msg) ?>
  </div>
<?php endif; ?>

<!-- Form tambah -->
<div class="max-w-full mx-auto bg-white rounded-xl p-6 sm:p-8 mb-8 px-4 sm:px-6">
  <h2 class="text-xl font-bold mb-4 border-b pb-2">Tambah Data Prestasi</h2>
  <form method="post" class="space-y-6" novalidate>
    <input type="hidden" name="tambah" value="1">
    <div>
      <label for="judul" class="block mb-2 font-medium text-gray-700">Judul</label>
      <input id="judul" name="judul" type="text" required placeholder="Masukkan judul prestasi"
        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:outline-none transition" />
    </div>
    <div>
      <label for="keterangan" class="block mb-2 font-medium text-gray-700">Keterangan</label>
      <textarea id="keterangan" name="keterangan" required rows="4" placeholder="Masukkan keterangan"
        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:outline-none transition resize-none"></textarea>
    </div>

    <!-- Input poin -->
    <div>
      <label class="block mb-2 font-medium text-gray-700">Poin Prestasi</label>
      <input name="poin1" type="number" min="0" value="0" required 
        class="w-full border border-gray-300 rounded-lg px-4 py-3" />
    </div>

    <button type="submit"
      class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg px-6 py-3 transition shadow inline-flex items-center gap-2 justify-center">
      <i class="fa-solid fa-plus"></i> Simpan
    </button>
  </form>
</div>

<!-- Tabel data -->
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
      // Pagination setup
      $per_page = 10;
      $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
      $offset = ($page - 1) * $per_page;

      // Get total rows
      $result_total = $koneksi->query("SELECT COUNT(*) as total FROM poin_prestasi");
      $total_rows = $result_total->fetch_assoc()['total'];
      $total_pages = ceil($total_rows / $per_page);

      // Fetch paginated data
      $data_page = $koneksi->query("SELECT * FROM poin_prestasi ORDER BY dibuat_pada DESC LIMIT $per_page OFFSET $offset");

      $no = $offset + 1;
      while ($r = $data_page->fetch_assoc()): ?>
        <tr class="hover:bg-gray-50 transition">
          <td class="px-4 sm:px-6 py-4 text-center"><?= $no++ ?></td>
          <td class="px-4 sm:px-6 py-4 font-semibold text-gray-900 max-w-xs break-words"><?= htmlspecialchars($r['judul']) ?></td>
          <td class="px-4 sm:px-6 py-4 text-gray-700 max-w-lg break-words whitespace-pre-line"><?= htmlspecialchars($r['keterangan']) ?></td>
          <td class="px-4 sm:px-6 py-4 text-center text-green-600 font-bold"><?= $r['poin'] ?></td>
          <td class="px-4 sm:px-6 py-4 text-center">
            <div class="flex items-center justify-center gap-2 sm:gap-3">
              <button
                onclick="openEditModal('<?= htmlspecialchars(addslashes($r['judul'])) ?>', '<?= htmlspecialchars(addslashes($r['keterangan'])) ?>', <?= $r['poin'] ?>, <?= $r['id'] ?>)"
                class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 sm:px-4 sm:py-2 rounded shadow transition font-semibold inline-flex items-center gap-2"
                aria-label="Edit prestasi">
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
      <?php if ($total_rows == 0): ?>
        <tr>
          <td colspan="5" class="text-center py-10 text-gray-400">Belum ada data prestasi.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Pagination Navigator -->
<?php if ($total_pages > 1): ?>
  <nav class="flex justify-center mt-6" aria-label="Page navigation">
    <ul class="inline-flex items-center space-x-1 text-sm">
      <!-- Previous -->
      <li>
        <a href="?page=<?= max(1, $page-1) ?>"
           class="px-3 py-2 rounded-l-lg border border-gray-300 bg-white hover:bg-gray-100 <?= $page == 1 ? 'pointer-events-none opacity-50' : '' ?>"
           aria-label="Sebelumnya">
          <i class="fa-solid fa-chevron-left"></i>
        </a>
      </li>
      <?php
        $range = 2; // show 2 pages before and after current
        $start = max(1, $page - $range);
        $end = min($total_pages, $page + $range);

        if ($start > 1) {
          echo '<li><a href="?page=1" class="px-3 py-2 rounded border border-gray-300 bg-white hover:bg-gray-100">1</a></li>';
          if ($start > 2) echo '<li><span class="px-2">...</span></li>';
        }
        for ($i = $start; $i <= $end; $i++):
      ?>
        <li>
          <a href="?page=<?= $i ?>"
             class="px-3 py-2 rounded border border-gray-300 <?= $i == $page ? 'bg-blue-600 text-white font-bold' : 'bg-white hover:bg-gray-100' ?>">
            <?= $i ?>
          </a>
        </li>
      <?php endfor;
        if ($end < $total_pages) {
          if ($end < $total_pages - 1) echo '<li><span class="px-2">...</span></li>';
          echo '<li><a href="?page='.$total_pages.'" class="px-3 py-2 rounded border border-gray-300 bg-white hover:bg-gray-100">'.$total_pages.'</a></li>';
        }
      ?>
      <!-- Next -->
      <li>
        <a href="?page=<?= min($total_pages, $page+1) ?>"
           class="px-3 py-2 rounded-r-lg border border-gray-300 bg-white hover:bg-gray-100 <?= $page == $total_pages ? 'pointer-events-none opacity-50' : '' ?>"
           aria-label="Berikutnya">
          <i class="fa-solid fa-chevron-right"></i>
        </a>
      </li>
    </ul>
  </nav>
<?php endif; ?>

<!-- Modal Edit -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 pointer-events-none transition-opacity p-4">
  <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-full overflow-auto p-6 relative">
    <h3 class="text-xl font-semibold mb-4">Edit Prestasi</h3>
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
        <label for="edit_poin" class="block mb-2 font-medium text-gray-700">Poin Prestasi</label>
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
