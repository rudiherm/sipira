<?php
session_start();

$page_title = "Post Pengumuman";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('guru');

// Fungsi tanggal Indonesia
function tanggal_indo($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $tgl = date('j', strtotime($tanggal));
    $bln = date('n', strtotime($tanggal));
    $thn = date('Y', strtotime($tanggal));
    return $tgl . ' ' . $bulan[$bln] . ' ' . $thn;
}

$msg = '';
$error = '';

// Ambil semua siswa
$siswa_arr = [];
$siswa_result = $koneksi->query("SELECT id, nama, kelas FROM siswa ORDER BY nama");
while ($row = $siswa_result->fetch_assoc()) {
    $siswa_arr[$row['id']] = $row['nama'] . " - " . $row['kelas'];
}

// Handle hapus pengumuman via POST (tanpa CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus'])) {
    $id = intval($_POST['hapus']);
    $stmt = $koneksi->prepare("DELETE FROM pengumuman WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $msg = "Pengumuman berhasil dihapus.";
    } else {
        $error = "Gagal menghapus pengumuman.";
    }
    $stmt->close();
}

// Handle tambah / edit pengumuman via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['tambah']) || isset($_POST['edit']))) {
    $judul = trim($_POST['judul'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $selected_siswa_arr = $_POST['id_siswa_tertentu'] ?? [];

    if ($judul === '' || $isi === '') {
        $error = "Judul dan isi pengumuman wajib diisi.";
    } elseif (!is_array($selected_siswa_arr)) {
        $error = "Data siswa tidak valid.";
    } else {
        if (count($selected_siswa_arr) > 0) {
            $ditujukan_untuk = 'tertentu';
            $selected_siswa_arr = array_map('intval', $selected_siswa_arr);
            $id_siswa_tertentu = json_encode($selected_siswa_arr);
        } else {
            $ditujukan_untuk = 'semua';
            $id_siswa_tertentu = '[]';
        }

        if (isset($_POST['tambah'])) {
            $stmt = $koneksi->prepare("INSERT INTO pengumuman (judul, isi, ditujukan_untuk, id_siswa_tertentu, tgl_posting) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $judul, $isi, $ditujukan_untuk, $id_siswa_tertentu);
            if ($stmt->execute()) {
                $msg = "Pengumuman berhasil ditambahkan.";
            } else {
                $error = "Gagal menambahkan pengumuman.";
            }
            $stmt->close();
        } elseif (isset($_POST['edit'])) {
            $id_edit = intval($_POST['edit']);
            $stmt = $koneksi->prepare("UPDATE pengumuman SET judul = ?, isi = ?, ditujukan_untuk = ?, id_siswa_tertentu = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $judul, $isi, $ditujukan_untuk, $id_siswa_tertentu, $id_edit);
            if ($stmt->execute()) {
                $msg = "Pengumuman berhasil diperbarui.";
            } else {
                $error = "Gagal memperbarui pengumuman.";
            }
            $stmt->close();
        }
    }
}

// Ambil data pengumuman tanpa paginasi (bisa ditambah jika perlu)
$data = $koneksi->query("SELECT * FROM pengumuman ORDER BY tgl_posting DESC");

// Jika edit mode (ambil data)
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_edit = intval($_GET['edit']);
    $stmt_edit = $koneksi->prepare("SELECT * FROM pengumuman WHERE id = ?");
    $stmt_edit->bind_param("i", $id_edit);
    $stmt_edit->execute();
    $result_edit = $stmt_edit->get_result();
    if ($result_edit->num_rows > 0) {
        $edit_data = $result_edit->fetch_assoc();
    }
    $stmt_edit->close();
}
?>

<h1 class="text-3xl font-bold mb-6 text-gray-900 px-4 sm:px-0">Post Pengumuman</h1>

<?php if ($msg): ?>
  <div class="max-w-4xl mx-auto mb-6 p-4 rounded-lg bg-green-100 border-green-500 text-green-800 border-l-4 shadow px-4 sm:px-6">
    <?= htmlspecialchars($msg) ?>
  </div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="max-w-4xl mx-auto mb-6 p-4 rounded-lg bg-red-100 border-red-500 text-red-800 border-l-4 shadow px-4 sm:px-6">
    <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>

<!-- Form tambah / edit -->
<div class="max-w-full mx-auto bg-white rounded-xl p-6 sm:p-8 mb-8 px-4 sm:px-6">
  <h2 class="text-xl font-bold mb-4 border-b pb-2"><?= $edit_data ? 'Edit Pengumuman' : 'Tambah Pengumuman' ?></h2>
  <form method="post" class="space-y-6" novalidate>
    <?php if ($edit_data): ?>
      <input type="hidden" name="edit" value="<?= $edit_data['id'] ?>" />
    <?php else: ?>
      <input type="hidden" name="tambah" value="1" />
    <?php endif; ?>

    <div>
      <label for="judul" class="block mb-2 font-medium text-gray-700">Judul</label>
      <input id="judul" name="judul" type="text" required placeholder="Masukkan judul pengumuman"
        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:outline-none transition"
        value="<?= htmlspecialchars($_POST['judul'] ?? ($edit_data['judul'] ?? '')) ?>" />
    </div>

    <div>
      <label for="isi" class="block mb-2 font-medium text-gray-700">Isi Pengumuman</label>
      <textarea id="isi" name="isi" required rows="5" placeholder="Masukkan isi pengumuman"
        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:outline-none transition resize-none"><?= htmlspecialchars($_POST['isi'] ?? ($edit_data['isi'] ?? '')) ?></textarea>
    </div>

    <div>
      <label for="id_siswa_tertentu" class="block mb-2 font-medium text-gray-700">Pilih Siswa (Bisa pilih lebih dari satu)</label>
      <select id="id_siswa_tertentu" name="id_siswa_tertentu[]" multiple
        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:outline-none transition h-40 overflow-auto">
        <?php
        $selected_ids = $_POST['id_siswa_tertentu'] ?? null;
        if ($selected_ids === null && $edit_data) {
            $selected_ids = json_decode($edit_data['id_siswa_tertentu'], true);
        }
        if (!is_array($selected_ids)) $selected_ids = [];
        ?>
        <?php foreach ($siswa_arr as $id => $nama_kelas): ?>
          <option value="<?= $id ?>" <?= in_array($id, $selected_ids) ? 'selected' : '' ?>>
            <?= htmlspecialchars($nama_kelas) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <p class="text-sm text-gray-500 mt-1">Kosongkan untuk ditujukan ke semua siswa.</p>
    </div>

    <button type="submit"
      class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg px-6 py-3 transition shadow inline-flex items-center gap-2 justify-center">
      <i class="fa-solid fa-<?= $edit_data ? 'pen-to-square' : 'plus' ?>"></i> <?= $edit_data ? 'Perbarui' : 'Simpan' ?>
    </button>

    <?php if ($edit_data): ?>
      <a href="?" class="ml-4 inline-block align-middle text-gray-700 hover:text-gray-900 font-semibold transition">Batal</a>
    <?php endif; ?>
  </form>
</div>

<!-- Tabel daftar pengumuman -->
<div class="max-w-full mx-auto bg-white rounded-xl shadow overflow-x-auto px-4 sm:px-6">
  <table class="min-w-full divide-y divide-gray-200 table-auto sm:table-fixed">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-4 sm:px-6 py-3 text-center text-sm font-semibold text-gray-700 uppercase tracking-wide">No</th>
        <th class="px-4 sm:px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wide">Judul</th>
        <th class="px-4 sm:px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wide">Isi</th>
        <th class="px-4 sm:px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wide">Ditujukan Untuk</th>
        <th class="px-4 sm:px-6 py-3 text-center text-sm font-semibold text-gray-700 uppercase tracking-wide w-40">Tanggal Posting</th>
        <th class="px-4 sm:px-6 py-3 text-center text-sm font-semibold text-gray-700 uppercase tracking-wide w-48">Aksi</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
      <?php
      $no = 1;
      if ($data->num_rows > 0):
        while ($row = $data->fetch_assoc()):
      ?>
        <tr class="hover:bg-gray-50 transition align-top">
          <td class="px-4 sm:px-6 py-4 text-center align-top"><?= $no++ ?></td>
          <td class="px-4 sm:px-6 py-4 font-semibold text-gray-900 max-w-xs break-words"><?= htmlspecialchars($row['judul']) ?></td>
          <td class="px-4 sm:px-6 py-4 text-gray-700 max-w-lg break-words whitespace-pre-line"><?= htmlspecialchars($row['isi']) ?></td>
          <td class="px-4 sm:px-6 py-4 text-gray-700 max-w-lg">
            <?php
            if ($row['ditujukan_untuk'] === 'semua') {
              echo "<span class='font-semibold text-green-600'>Semua Siswa</span>";
            } else {
              $ids = json_decode($row['id_siswa_tertentu'], true);
              if (is_array($ids) && count($ids) > 0) {
                $names = [];
                foreach ($ids as $id_siswa) {
                  if (isset($siswa_arr[$id_siswa])) {
                    $names[] = htmlspecialchars($siswa_arr[$id_siswa]);
                  }
                }
                echo implode(", ", $names);
              } else {
                echo "<span class='text-gray-400 italic'>Tidak ada siswa terpilih</span>";
              }
            }
            ?>
          </td>
          <td class="px-4 sm:px-6 py-4 text-center"><?= tanggal_indo($row['tgl_posting']) ?></td>
          <td class="px-4 sm:px-6 py-4 text-center space-x-2">
            <a href="?edit=<?= $row['id'] ?>"
               class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded shadow transition font-semibold inline-flex items-center gap-2">
              <i class="fa-solid fa-pen-to-square"></i> Edit
            </a>
            <form method="post" class="inline" onsubmit="return confirm('Yakin hapus pengumuman ini?')">
              <button type="submit" name="hapus" value="<?= $row['id'] ?>"
                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded shadow transition font-semibold inline-flex items-center gap-2">
                <i class="fa-solid fa-trash"></i> Hapus
              </button>
            </form>
          </td>
        </tr>
      <?php
        endwhile;
      else:
      ?>
        <tr>
          <td colspan="6" class="text-center py-10 text-gray-400">Belum ada pengumuman.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include '../inc/footer.php'; ?>
