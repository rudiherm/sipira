<?php
$page_title = "Data Siswa";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('guru');

require '../vendor/autoload.php'; // PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;

// Tambah siswa
if (isset($_POST['tambah'])) {
    $nama = trim($_POST['nama']);
    $kelas = trim($_POST['kelas']);
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    if ($nama && $kelas && $username && !empty($_POST['password'])) {
        $stmt = $koneksi->prepare("INSERT INTO siswa (nama, kelas, username, password, total_prestasi, total_pelanggaran) VALUES (?, ?, ?, ?, 0, 0)");
        $stmt->bind_param("ssss", $nama, $kelas, $username, $password);
        $stmt->execute();
        echo "<script>alert('Siswa berhasil ditambahkan'); location.href='data_siswa.php';</script>";
        exit;
    } else {
        echo "<script>alert('Semua field wajib diisi');</script>";
    }
}

// Import dari Excel
if (isset($_POST['import']) && isset($_FILES['file_excel']['tmp_name'])) {
    $file_tmp = $_FILES['file_excel']['tmp_name'];
    try {
        $spreadsheet = IOFactory::load($file_tmp);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $baris = 0;
        foreach ($rows as $row) {
            $baris++;
            if ($baris == 1) continue; // skip header

            $nama = trim($row[0]);
            $kelas = trim($row[1]);
            $username = trim($row[2]);
            $password = password_hash(trim($row[3]), PASSWORD_DEFAULT);

            if ($nama && $kelas && $username && $row[3]) {
                $stmt = $koneksi->prepare("INSERT INTO siswa (nama, kelas, username, password, total_prestasi, total_pelanggaran) VALUES (?, ?, ?, ?, 0, 0)");
                $stmt->bind_param("ssss", $nama, $kelas, $username, $password);
                $stmt->execute();
            }
        }
        echo "<script>alert('Import berhasil'); location.href='data_siswa.php';</script>";
        exit;
    } catch (Exception $e) {
        echo "<script>alert('Gagal membaca file: " . $e->getMessage() . "');</script>";
    }
}

// Edit siswa
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $nama = trim($_POST['nama']);
    $kelas = trim($_POST['kelas']);
    $username = trim($_POST['username']);
    $total_prestasi = intval($_POST['total_prestasi']);
    $total_pelanggaran = intval($_POST['total_pelanggaran']);

    if (!empty($_POST['password'])) {
        $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        $stmt = $koneksi->prepare("UPDATE siswa SET nama=?, kelas=?, username=?, password=?, total_prestasi=?, total_pelanggaran=? WHERE id=?");
        $stmt->bind_param("sssiiii", $nama, $kelas, $username, $password, $total_prestasi, $total_pelanggaran, $id);
    } else {
        $stmt = $koneksi->prepare("UPDATE siswa SET nama=?, kelas=?, username=?, total_prestasi=?, total_pelanggaran=? WHERE id=?");
        $stmt->bind_param("sssiii", $nama, $kelas, $username, $total_prestasi, $total_pelanggaran, $id);
    }

    $stmt->execute();
    echo "<script>alert('Data siswa berhasil diubah'); location.href='data_siswa.php';</script>";
    exit;
}

// Hapus siswa
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $stmt = $koneksi->prepare("DELETE FROM siswa WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "<script>alert('Siswa berhasil dihapus'); location.href='data_siswa.php';</script>";
    exit;
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$result_count = $koneksi->query("SELECT COUNT(*) AS total FROM siswa");
$row_count = $result_count->fetch_assoc();
$total_data = $row_count['total'];
$total_pages = ceil($total_data / $limit);

$stmt = $koneksi->prepare("SELECT id,nama,kelas,username,total_prestasi,total_pelanggaran FROM siswa ORDER BY nama LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$q = $stmt->get_result();

?>

<div class="max-w-7xl mx-auto px-4 md:px-8 py-8">
  <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Data Siswa</h1>

  <!-- Toolbar: Tambah & Import -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <button
      onclick="document.getElementById('modalTambah').classList.remove('hidden')"
      class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow focus:outline-none"
    >
      <i class="fas fa-plus"></i> Tambah Siswa
    </button>
    <form method="post" enctype="multipart/form-data" class="flex items-center gap-3">
      <input
        type="file"
        name="file_excel"
        accept=".xlsx"
        required
        class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
      />
      <button
        type="submit"
        name="import"
        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md shadow focus:outline-none"
      >
        <i class="fas fa-file-import"></i> Import Excel
      </button>
    </form>
  </div>

  <!-- Search bar -->
  <div class="mb-4">
    <input
      type="text"
      id="searchInput"
      placeholder="Cari berdasarkan nama atau kelas..."
      class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
    />
  </div>

  <!-- Data table -->
  <div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full divide-y divide-gray-200 text-sm" id="siswaTable">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left font-semibold text-gray-700">No</th>
          <th class="px-6 py-3 text-left font-semibold text-gray-700">Nama</th>
          <th class="px-6 py-3 text-left font-semibold text-gray-700">Kelas</th>
          <th class="px-6 py-3 text-left font-semibold text-gray-700">Username</th>
          <th class="px-6 py-3 text-center font-semibold text-green-600">Prestasi</th>
          <th class="px-6 py-3 text-center font-semibold text-red-600">Pelanggaran</th>
          <th class="px-6 py-3 text-center font-semibold text-gray-700">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php
        $no = $offset + 1;
        while ($r = $q->fetch_assoc()) :
        ?>
        <tr class="hover:bg-gray-50 transition">
          <td class="px-6 py-4"><?= $no++ ?></td>
          <td class="px-6 py-4"><?= htmlspecialchars($r['nama']) ?></td>
          <td class="px-6 py-4"><?= htmlspecialchars($r['kelas']) ?></td>
          <td class="px-6 py-4"><?= htmlspecialchars($r['username']) ?></td>
          <td class="px-6 py-4 text-center font-semibold text-green-600"><?= $r['total_prestasi'] ?></td>
          <td class="px-6 py-4 text-center font-semibold text-red-600"><?= $r['total_pelanggaran'] ?></td>
          <td class="px-6 py-4 flex justify-center gap-2">
            <button
              onclick="editSiswa(
                <?= $r['id'] ?>,
                '<?= htmlspecialchars($r['nama'], ENT_QUOTES) ?>',
                '<?= htmlspecialchars($r['kelas'], ENT_QUOTES) ?>',
                '<?= htmlspecialchars($r['username'], ENT_QUOTES) ?>',
                <?= (int)$r['total_prestasi'] ?>,
                <?= (int)$r['total_pelanggaran'] ?>
              )"
              class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded focus:outline-none"
            >
              <i class="fas fa-edit"></i> Edit
            </button>
            <a
              href="?hapus=<?= $r['id'] ?>"
              onclick="return confirm('Hapus siswa ini?')"
              class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded focus:outline-none"
            >
              <i class="fas fa-trash-alt"></i> Hapus
            </a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($total_pages > 1): ?>
    <nav class="mt-6 flex flex-wrap justify-center gap-2" aria-label="Pagination">
      <!-- Previous Button -->
      <a
        href="?page=<?= max(1, $page - 1) ?>"
        class="px-3 py-1 rounded <?= $page == 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-200 hover:bg-gray-300 text-gray-700' ?>"
        <?= $page == 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>
      >
        &laquo;
      </a>
      <?php
        $range = 2; // show 2 pages before and after current
        $start = max(1, $page - $range);
        $end = min($total_pages, $page + $range);
        if ($start > 1) {
          echo '<a href="?page=1" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">1</a>';
          if ($start > 2) echo '<span class="px-2 py-1 text-gray-400">...</span>';
        }
        for ($i = $start; $i <= $end; $i++):
      ?>
        <a
          href="?page=<?= $i ?>"
          class="px-3 py-1 rounded <?= $i === $page ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300 text-gray-700' ?>"
          <?= $i === $page ? 'aria-current="page"' : '' ?>
        >
          <?= $i ?>
        </a>
      <?php endfor;
        if ($end < $total_pages) {
          if ($end < $total_pages - 1) echo '<span class="px-2 py-1 text-gray-400">...</span>';
          echo '<a href="?page=' . $total_pages . '" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">' . $total_pages . '</a>';
        }
      ?>
      <!-- Next Button -->
      <a
        href="?page=<?= min($total_pages, $page + 1) ?>"
        class="px-3 py-1 rounded <?= $page == $total_pages ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-200 hover:bg-gray-300 text-gray-700' ?>"
        <?= $page == $total_pages ? 'tabindex="-1" aria-disabled="true"' : '' ?>
      >
        &raquo;
      </a>
    </nav>
  <?php endif; ?>
</div>

<!-- Modal Tambah -->
<div
  id="modalTambah"
  class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
>
  <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 overflow-y-auto max-h-[90vh]">
    <h2 class="text-xl font-semibold mb-4">Tambah Siswa</h2>
    <form method="post" class="space-y-4">
      <input
        type="text"
        name="nama"
        placeholder="Nama Siswa"
        required
        class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
      <input
        type="text"
        name="kelas"
        placeholder="Kelas"
        required
        class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
      <input
        type="text"
        name="username"
        placeholder="Username"
        required
        class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
      <input
        type="password"
        name="password"
        placeholder="Password"
        required
        class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
      <div class="flex justify-end space-x-2 mt-4">
        <button
          type="button"
          onclick="document.getElementById('modalTambah').classList.add('hidden')"
          class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100"
        >
          Batal
        </button>
        <button
          type="submit"
          name="tambah"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md"
        >
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div
  id="modalEdit"
  class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
>
  <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 overflow-y-auto max-h-[90vh]">
    <h2 class="text-xl font-semibold mb-4">Edit Siswa</h2>
    <form method="post" class="space-y-4">
      <input type="hidden" name="id" id="editId" />
      <input
        type="text"
        name="nama"
        id="editNama"
        placeholder="Nama Siswa"
        required
        class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500"
      />
      <input
        type="text"
        name="kelas"
        id="editKelas"
        placeholder="Kelas"
        required
        class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500"
      />
      <input
        type="text"
        name="username"
        id="editUsername"
        placeholder="Username"
        required
        class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500"
      />
      <input
        type="password"
        name="password"
        placeholder="Kosongkan jika tidak ingin mengubah password"
        class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500"
      />
      <div class="flex space-x-2">
        <input
          type="number"
          name="total_prestasi"
          id="editPrestasi"
          placeholder="Total Prestasi"
          min="0"
          class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
        />
        <input
          type="number"
          name="total_pelanggaran"
          id="editPelanggaran"
          placeholder="Total Pelanggaran"
          min="0"
          class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
        />
      </div>
      <div class="flex justify-end space-x-2 mt-4">
        <button
          type="button"
          onclick="document.getElementById('modalEdit').classList.add('hidden')"
          class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100"
        >
          Batal
        </button>
        <button
          type="submit"
          name="edit"
          class="bg-yellow-500 hover:bg-yellow-600 px-4 py-2 text-white rounded-md"
        >
          Update
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function editSiswa(id, nama, kelas, username, prestasi, pelanggaran) {
  document.getElementById('editId').value = id;
  document.getElementById('editNama').value = nama;
  document.getElementById('editKelas').value = kelas;
  document.getElementById('editUsername').value = username;
  document.getElementById('editPrestasi').value = prestasi;
  document.getElementById('editPelanggaran').value = pelanggaran;
  document.getElementById('modalEdit').classList.remove('hidden');
}

// Search filter
document.getElementById('searchInput').addEventListener('input', function() {
  const filter = this.value.toLowerCase();
  const rows = document.querySelectorAll('#siswaTable tbody tr');
  rows.forEach(row => {
    const nama = row.children[1].textContent.toLowerCase();
    const kelas = row.children[2].textContent.toLowerCase();
    if (nama.includes(filter) || kelas.includes(filter)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
});
</script>

<?php include '../inc/footer.php'; ?>