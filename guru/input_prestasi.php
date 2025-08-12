<?php
$page_title = "Input Prestasi";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';

require_role('guru'); 

// Ambil data guru
$user_id = $_SESSION['user_id'];
$user_stmt = $koneksi->prepare("SELECT id, nama FROM guru WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
if ($user_result->num_rows === 0) {
    header("Location: ../login.php");
    exit;
}
$user = $user_result->fetch_assoc();

$msg = '';

// Proses hapus prestasi
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    // Ambil data untuk mengembalikan poin siswa
    $cek = $koneksi->prepare("SELECT id_siswa, poin, foto FROM prestasi WHERE id = ?");
    $cek->bind_param("i", $id_hapus);
    $cek->execute();
    $cek_res = $cek->get_result();
    if ($cek_res->num_rows > 0) {
        $data = $cek_res->fetch_assoc();
        // Hapus prestasi
        $del = $koneksi->prepare("DELETE FROM prestasi WHERE id = ?");
        $del->bind_param("i", $id_hapus);
        if ($del->execute()) {
            // Kurangi total prestasi siswa
            $upd = $koneksi->prepare("UPDATE siswa SET total_prestasi = total_prestasi - ? WHERE id = ?");
            $upd->bind_param("ii", $data['poin'], $data['id_siswa']);
            $upd->execute();
            // Hapus file foto jika ada
            if (!empty($data['foto']) && file_exists("../" . $data['foto'])) {
                unlink("../" . $data['foto']);
            }
            $msg = "Prestasi berhasil dihapus.";
        } else {
            $msg = "Gagal menghapus prestasi.";
        }
    } else {
        $msg = "Data prestasi tidak ditemukan.";
    }
}

// Proses tambah prestasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['hapus'])) {
    $id_siswa         = intval($_POST['id_siswa']);
    $id_poin_prestasi = intval($_POST['id_poin_prestasi']);
    $tanggal          = $_POST['tanggal'] ?: date('Y-m-d');
    $id_guru          = $user['id']; 

    // Ambil data poin prestasi
    $pp = $koneksi->prepare("SELECT keterangan, poin FROM poin_prestasi WHERE id = ?");
    $pp->bind_param("i", $id_poin_prestasi);
    $pp->execute();
    $pp_data = $pp->get_result()->fetch_assoc();

    if (!$pp_data) {
        $msg = "Data jenis prestasi tidak ditemukan.";
    } else {
        $keterangan = $pp_data['keterangan'];
        $poin       = $pp_data['poin'];

        $foto_path = null;
        if (!empty($_FILES['foto']['name'])) {
            $target_dir = "../uploads/prestasi/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($ext, $allowed_ext) && $_FILES['foto']['size'] <= 10 * 1024 * 1024) {
                $new_filename = "prestasi_" . time() . "_" . rand(1000, 9999) . "." . $ext;
                $target_file = $target_dir . $new_filename;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                    $foto_path = "uploads/prestasi/" . $new_filename;
                } else {
                    $msg = "Gagal mengupload foto.";
                }
            } else {
                $msg = "Format foto tidak valid atau ukuran terlalu besar (maks 2MB).";
            }
        } else {
            $msg = "Foto bukti wajib diupload.";
        }

        if (empty($msg)) {
            $ins = $koneksi->prepare("INSERT INTO prestasi (id_siswa, id_guru, id_poin_prestasi, keterangan, poin, tanggal, foto) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $ins->bind_param("iiisiss", $id_siswa, $id_guru, $id_poin_prestasi, $keterangan, $poin, $tanggal, $foto_path);
            if ($ins->execute()) {
                $upd = $koneksi->prepare("UPDATE siswa SET total_prestasi = total_prestasi + ? WHERE id = ?");
                $upd->bind_param("ii", $poin, $id_siswa);
                $upd->execute();
                $msg = "Prestasi berhasil dicatat.";
            } else {
                $msg = "Gagal menyimpan ke database.";
            }
        }
    }
}

// Ambil list siswa & poin prestasi
$siswa_list = $koneksi->query("SELECT id, nama, kelas FROM siswa ORDER BY nama");
$poin_prestasi_list  = $koneksi->query("SELECT id, keterangan, poin FROM poin_prestasi ORDER BY keterangan");

// Ambil 5 prestasi terbaru
$prestasi_list = $koneksi->query("
    SELECT pr.*, s.nama AS nama_siswa, s.kelas, g.nama AS nama_guru
    FROM prestasi pr
    JOIN siswa s ON pr.id_siswa = s.id
    JOIN guru g ON pr.id_guru = g.id
    ORDER BY pr.tanggal DESC, pr.id DESC
    LIMIT 5
");
?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">Input Prestasi</h1>

<?php if ($msg): ?>
  <div id="alert-msg" class="mb-8 max-w-3xl mx-auto px-6 py-4 rounded-lg text-white 
    <?= strpos($msg, 'berhasil') !== false ? 'bg-green-600' : 'bg-red-600' ?> flex justify-between items-center shadow-lg">
    <span class="text-lg"><?= htmlspecialchars($msg) ?></span>
    <button onclick="document.getElementById('alert-msg').style.display='none'" class="font-extrabold hover:text-gray-200 text-2xl leading-none ml-4">&times;</button>
  </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="bg-white rounded-2xl max-w-4xl mx-auto p-10 space-y-10">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <div>
      <label for="id_siswa" class="block mb-3 font-semibold text-gray-800">Nama Siswa</label>
      <input type="text" id="search-siswa" placeholder="Cari siswa..." class="w-full border rounded-lg px-5 py-3 mb-3" autocomplete="off" />
      <select id="id_siswa" name="id_siswa" required class="w-full border rounded-lg px-5 py-3">
      <option value="" disabled selected>-- Pilih siswa --</option>
      <?php while ($r = $siswa_list->fetch_assoc()): ?>
        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nama'] . ' - ' . $r['kelas']) ?></option>
      <?php endwhile; ?>
      </select>
    </div>
    <div>
      <label for="id_poin_prestasi" class="block mb-3 font-semibold text-gray-800">Jenis Prestasi</label>
      <input type="text" id="search-prestasi" placeholder="Cari jenis prestasi..." class="w-full border rounded-lg px-5 py-3 mb-3" autocomplete="off" />
      <select id="id_poin_prestasi" name="id_poin_prestasi" required class="w-full border rounded-lg px-5 py-3">
      <option value="" disabled selected>-- Pilih prestasi --</option>
      <?php while ($t = $poin_prestasi_list->fetch_assoc()): ?>
        <option value="<?= $t['id'] ?>" data-poin="<?= $t['poin'] ?>">
        <?= htmlspecialchars($t['keterangan']) ?> (<?= $t['poin'] ?> poin)
        </option>
      <?php endwhile; ?>
      </select>
    </div>
    <script>
      // Siswa search
      document.getElementById('search-siswa').addEventListener('input', function () {
      let filter = this.value.toLowerCase();
      let select = document.getElementById('id_siswa');
      for (let opt of select.options) {
        if (opt.value === "") continue;
        let text = opt.text.toLowerCase();
        opt.style.display = text.includes(filter) ? "" : "none";
      }
      });
      // Prestasi search
      document.getElementById('search-prestasi').addEventListener('input', function () {
      let filter = this.value.toLowerCase();
      let select = document.getElementById('id_poin_prestasi');
      for (let opt of select.options) {
        if (opt.value === "") continue;
        let text = opt.text.toLowerCase();
        opt.style.display = text.includes(filter) ? "" : "none";
      }
      });
    </script>
    <div>
      <label class="block mb-3 font-semibold text-gray-800">Poin</label>
      <input id="poin_display" type="number" readonly class="w-full bg-gray-100 border rounded-lg px-5 py-3" />
    </div>
    <div>
      <label for="tanggal" class="block mb-3 font-semibold text-gray-800">Tanggal</label>
      <input id="tanggal" name="tanggal" type="date" value="<?= date('Y-m-d') ?>" required class="w-full border rounded-lg px-5 py-3" />
    </div>
  </div>

  <div>
    <label for="foto" class="block mb-3 font-semibold text-gray-800">Upload Foto Bukti (Wajib)</label>
    <input id="foto" type="file" name="foto" accept="image/jpeg,image/png,image/gif" capture="environment" class="w-full border rounded-lg px-5 py-3" />
    <p class="mt-2 text-xs text-gray-500 italic">Format: JPG, PNG, GIF. Maks: 10MB. <span class="text-blue-600">Bisa langsung foto dari kamera HP.</span></p>
    <img id="preview-foto" class="mt-4 max-h-52 rounded-lg shadow-md hidden mx-auto" />
    </div>
    <script>
    // Preview file upload
    document.getElementById('foto').addEventListener('change', function () {
      const preview = document.getElementById('preview-foto');
      const file = this.files[0];
      if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
      };
      reader.readAsDataURL(file);
      } else {
      preview.src = '';
      preview.classList.add('hidden');
      }
    });

    // Otomatis buka kamera di HP saat klik input file
    document.getElementById('foto').addEventListener('click', function(e) {
      // Untuk browser HP, capture="environment" sudah memicu kamera belakang
      // Tidak perlu tombol lain
    });
    </script>

  <div>
    <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg">Simpan</button>
  </div>
</form>

<!-- Daftar Prestasi Terbaru -->
<div class="max-w-7xl mx-auto mt-12 bg-white rounded-2xl p-8 shadow">
  <h2 class="text-2xl font-bold mb-6 text-gray-800">Daftar Prestasi Terbaru</h2>
  <div class="overflow-x-auto">
    <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-3 py-3 text-center text-gray-700 font-semibold border-b">No</th>
          <th class="px-4 py-3 text-left text-gray-700 font-semibold border-b">Tanggal</th>
          <th class="px-4 py-3 text-left text-gray-700 font-semibold border-b">Siswa</th>
          <th class="px-4 py-3 text-left text-gray-700 font-semibold border-b">Kelas</th>
          <th class="px-4 py-3 text-left text-gray-700 font-semibold border-b">Prestasi</th>
          <th class="px-4 py-3 text-center text-gray-700 font-semibold border-b">Poin</th>
          <th class="px-4 py-3 text-left text-gray-700 font-semibold border-b">Guru</th>
          <th class="px-4 py-3 text-center text-gray-700 font-semibold border-b">Foto</th>
          <th class="px-4 py-3 text-center text-gray-700 font-semibold border-b">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($prestasi_list->num_rows > 0): $no = 1; ?>
          <?php while ($p = $prestasi_list->fetch_assoc()): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-3 py-3 border-b text-center"><?= $no++ ?></td>
              <td class="px-4 py-3 border-b"><?= htmlspecialchars($p['tanggal']) ?></td>
              <td class="px-4 py-3 border-b"><?= htmlspecialchars($p['nama_siswa']) ?></td>
              <td class="px-4 py-3 border-b"><?= htmlspecialchars($p['kelas']) ?></td>
              <td class="px-4 py-3 border-b"><?= htmlspecialchars($p['keterangan']) ?></td>
              <td class="px-4 py-3 border-b text-center font-bold text-green-600"><?= $p['poin'] ?></td>
              <td class="px-4 py-3 border-b"><?= htmlspecialchars($p['nama_guru']) ?></td>
              <td class="px-4 py-3 border-b text-center">
                <?php if (!empty($p['foto'])): ?>
                  <a href="../<?= htmlspecialchars($p['foto']) ?>" target="_blank" class="text-blue-600 hover:underline">Lihat</a>
                <?php else: ?>
                  <span class="text-gray-400 italic">Tidak ada</span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3 border-b text-center">
                <a href="?hapus=<?= $p['id'] ?>" onclick="return confirm('Yakin ingin menghapus prestasi ini?')" class="text-red-600 hover:underline">Hapus</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" class="px-4 py-6 text-center text-gray-500">Belum ada data prestasi.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  document.getElementById('id_poin_prestasi').addEventListener('change', function () {
    let poin = this.options[this.selectedIndex].getAttribute('data-poin');
    document.getElementById('poin_display').value = poin || '';
  });
  document.getElementById('foto').addEventListener('change', function () {
    const preview = document.getElementById('preview-foto');
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
      };
      reader.readAsDataURL(file);
    } else {
      preview.src = '';
      preview.classList.add('hidden');
    }
  });
</script>

<?php include '../inc/footer.php'; ?>
