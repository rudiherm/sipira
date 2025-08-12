<?php
$page_title = "Input Pelanggaran";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';

require_role('guru'); // pastikan sudah login dan role guru

// Ambil data guru dari database berdasarkan session user_id
$user_id = $_SESSION['user_id'];
$user_stmt = $koneksi->prepare("SELECT id, nama FROM guru WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
if ($user_result->num_rows === 0) {
    // user tidak ditemukan, logout
    header("Location: ../login.php");
    exit;
}
$user = $user_result->fetch_assoc();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_siswa       = intval($_POST['id_siswa']);
    $id_tata_tertib = intval($_POST['id_tata_tertib']);
    $tanggal        = $_POST['tanggal'] ?: date('Y-m-d');
    $id_guru        = $user['id'];  // pakai dari database

    // Ambil data tata tertib
    $tt = $koneksi->prepare("SELECT keterangan, poin FROM tata_tertib WHERE id = ?");
    $tt->bind_param("i", $id_tata_tertib);
    $tt->execute();
    $tt_data = $tt->get_result()->fetch_assoc();

    if (!$tt_data) {
        $msg = "Data tata tertib tidak ditemukan.";
    } else {
        $keterangan = $tt_data['keterangan'];
        $poin       = $tt_data['poin'];

        $foto_path = null;
        if (!empty($_FILES['foto']['name'])) {
            $target_dir = "../uploads/pelanggaran/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($ext, $allowed_ext) && $_FILES['foto']['size'] <= 10 * 1024 * 1024) {
                $new_filename = "pelanggaran_" . time() . "_" . rand(1000, 9999) . "." . $ext;
                $target_file = $target_dir . $new_filename;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                    $foto_path = "uploads/pelanggaran/" . $new_filename;
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
            // Insert pelanggaran dengan id_guru
            $ins = $koneksi->prepare("INSERT INTO pelanggaran (id_siswa, id_guru, keterangan, poin, tanggal, foto) VALUES (?, ?, ?, ?, ?, ?)");
            $ins->bind_param("iissss", $id_siswa, $id_guru, $keterangan, $poin, $tanggal, $foto_path);
            if ($ins->execute()) {
                // Update total pelanggaran siswa
                $upd = $koneksi->prepare("UPDATE siswa SET total_pelanggaran = total_pelanggaran + ? WHERE id = ?");
                $upd->bind_param("ii", $poin, $id_siswa);
                $upd->execute();
                $msg = "Pelanggaran berhasil dicatat.";
            } else {
                $msg = "Gagal menyimpan ke database.";
            }
        }
    }
}

// Ambil list siswa dan tata tertib
$siswa_list = $koneksi->query("SELECT id, nama, kelas FROM siswa ORDER BY nama");
$tata_list  = $koneksi->query("SELECT id, keterangan, poin FROM tata_tertib ORDER BY keterangan");

// Ambil 5 pelanggaran terbaru beserta nama siswa dan guru
$pelanggaran_list = $koneksi->query("
    SELECT p.*, s.nama AS nama_siswa, s.kelas, g.nama AS nama_guru
    FROM pelanggaran p
    JOIN siswa s ON p.id_siswa = s.id
    JOIN guru g ON p.id_guru = g.id
    ORDER BY p.tanggal DESC, p.id DESC
    LIMIT 5
");
?>

<h1 class="text-3xl font-bold mb-6 text-gray-800">Input Pelanggaran</h1>

<?php if ($msg): ?>
  <div 
    id="alert-msg"
    class="mb-8 max-w-3xl mx-auto px-6 py-4 rounded-lg text-white 
      <?= strpos($msg, 'berhasil') !== false ? 'bg-green-600' : 'bg-red-600' ?> flex justify-between items-center shadow-lg"
    role="alert"
  >
    <span class="text-lg"><?= htmlspecialchars($msg) ?></span>
    <button 
      onclick="document.getElementById('alert-msg').style.display='none'" 
      class="font-extrabold hover:text-gray-200 text-2xl leading-none ml-4"
      aria-label="Close alert"
    >&times;</button>
  </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="bg-white rounded-2xl max-w-4xl mx-auto p-10 space-y-10">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <div>
      <label for="id_siswa" class="block mb-3 text-base font-semibold text-gray-800 cursor-pointer">Nama Siswa</label>
      <input type="text" id="search-siswa" placeholder="Cari nama atau kelas..." autocomplete="off"
      class="w-full border border-gray-300 rounded-lg px-5 py-3 mb-3 text-gray-700 text-base focus:outline-none focus:ring-4 focus:ring-blue-400 hover:border-blue-500 transition" />
      <select id="id_siswa" name="id_siswa" required
      class="w-full border border-gray-300 rounded-lg px-5 py-3 text-gray-700 text-base
           focus:outline-none focus:ring-4 focus:ring-blue-400 hover:border-blue-500 transition">
      <option value="" disabled selected>-- Pilih siswa --</option>
      <?php while ($r = $siswa_list->fetch_assoc()): ?>
        <option value="<?= $r['id'] ?>" data-nama="<?= htmlspecialchars(strtolower($r['nama'])) ?>" data-kelas="<?= htmlspecialchars(strtolower($r['kelas'])) ?>">
        <?= htmlspecialchars($r['nama'] . ' - ' . $r['kelas']) ?>
        </option>
      <?php endwhile; ?>
      </select>
    </div>

    <div>
      <label for="id_tata_tertib" class="block mb-3 text-base font-semibold text-gray-800 cursor-pointer">Jenis Pelanggaran</label>
      <input type="text" id="search-tata" placeholder="Cari pelanggaran..." autocomplete="off"
      class="w-full border border-gray-300 rounded-lg px-5 py-3 mb-3 text-gray-700 text-base focus:outline-none focus:ring-4 focus:ring-blue-400 hover:border-blue-500 transition" />
      <select id="id_tata_tertib" name="id_tata_tertib" required
      class="w-full border border-gray-300 rounded-lg px-5 py-3 text-gray-700 text-base
           focus:outline-none focus:ring-4 focus:ring-blue-400 hover:border-blue-500 transition">
      <option value="" disabled selected>-- Pilih pelanggaran --</option>
      <?php while ($t = $tata_list->fetch_assoc()): ?>
        <option value="<?= $t['id'] ?>" data-keterangan="<?= htmlspecialchars(strtolower($t['keterangan'])) ?>" data-poin="<?= $t['poin'] ?>">
        <?= htmlspecialchars($t['keterangan']) ?> (<?= $t['poin'] ?> poin)
        </option>
      <?php endwhile; ?>
      </select>
    </div>
  </div>

  <script>
    // Pencarian siswa
    document.getElementById('search-siswa').addEventListener('input', function () {
      const keyword = this.value.toLowerCase();
      const select = document.getElementById('id_siswa');
      for (let i = 0; i < select.options.length; i++) {
        const opt = select.options[i];
        if (i === 0) continue; // skip placeholder
        const nama = opt.getAttribute('data-nama') || '';
        const kelas = opt.getAttribute('data-kelas') || '';
        opt.style.display = (nama.includes(keyword) || kelas.includes(keyword)) ? '' : 'none';
      }
    });

    // Pencarian tata tertib
    document.getElementById('search-tata').addEventListener('input', function () {
      const keyword = this.value.toLowerCase();
      const select = document.getElementById('id_tata_tertib');
      for (let i = 0; i < select.options.length; i++) {
        const opt = select.options[i];
        if (i === 0) continue; // skip placeholder
        const ket = opt.getAttribute('data-keterangan') || '';
        opt.style.display = ket.includes(keyword) ? '' : 'none';
      }
    });
  </script>

  <div>
    <label for="poin_display" class="block mb-3 text-base font-semibold text-gray-800">Poin</label>
    <input id="poin_display" type="number" readonly
      class="w-full bg-gray-100 border border-gray-300 rounded-lg px-5 py-3 text-gray-600 cursor-not-allowed text-lg font-semibold" />
  </div>

  <div>
    <label for="tanggal" class="block mb-3 text-base font-semibold text-gray-800 cursor-pointer">Tanggal</label>
    <input id="tanggal" name="tanggal" type="date" value="<?= date('Y-m-d') ?>" required
      class="w-full border border-gray-300 rounded-lg px-5 py-3 text-gray-700 text-base
             focus:outline-none focus:ring-4 focus:ring-blue-400 hover:border-blue-500 transition" />
  </div>

  <div>
    <label for="foto" class="block mb-3 text-base font-semibold text-gray-800 cursor-pointer">Upload Foto Bukti (Wajib)</label>
    <input 
      id="foto" 
      type="file" 
      name="foto" 
      accept="image/jpeg,image/png,image/gif" 
      capture="environment"
      class="w-full border border-gray-300 rounded-lg px-5 py-3 text-gray-700 text-base
             focus:outline-none focus:ring-4 focus:ring-blue-400 hover:border-blue-500 transition" 
    />
    <p class="mt-2 text-xs text-gray-500 italic">
      Format: JPG, PNG, GIF. Maks: 10 MB.<br>
      <span class="text-blue-600">Bisa langsung foto dari kamera HP.</span>
    </p>
    <img id="preview-foto" class="mt-4 max-h-52 rounded-lg shadow-md hidden mx-auto" alt="Preview Foto Bukti" />
  </div>

  <div>
    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition focus:outline-none focus:ring-4 focus:ring-blue-400">
      Simpan
    </button>
  </div>
</form>

<?php
// Aksi hapus pelanggaran (hanya oleh guru yang bersangkutan)
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
  $hapus_id = intval($_GET['hapus']);
  // Cek apakah pelanggaran milik guru ini
  $cek = $koneksi->prepare("SELECT id, id_guru, id_siswa, poin, foto FROM pelanggaran WHERE id = ?");
  $cek->bind_param("i", $hapus_id);
  $cek->execute();
  $cek_result = $cek->get_result();
  if ($cek_result->num_rows > 0) {
    $pel = $cek_result->fetch_assoc();
    if ($pel['id_guru'] == $user['id']) {
      // Hapus file foto jika ada
      if ($pel['foto'] && file_exists('../' . $pel['foto'])) {
        unlink('../' . $pel['foto']);
      }
      // Kurangi total_pelanggaran siswa
      $upd = $koneksi->prepare("UPDATE siswa SET total_pelanggaran = GREATEST(total_pelanggaran - ?, 0) WHERE id = ?");
      $upd->bind_param("ii", $pel['poin'], $pel['id_siswa']);
      $upd->execute();
      // Hapus pelanggaran
      $del = $koneksi->prepare("DELETE FROM pelanggaran WHERE id = ?");
      $del->bind_param("i", $hapus_id);
      if ($del->execute()) {
        $msg = "Pelanggaran berhasil dihapus.";
      } else {
        $msg = "Gagal menghapus pelanggaran.";
      }
    } else {
      $msg = "Anda tidak berhak menghapus data ini.";
    }
  } else {
    $msg = "Data pelanggaran tidak ditemukan.";
  }
}
?>

<?php if ($pelanggaran_list && $pelanggaran_list->num_rows > 0): ?>
  <section class="max-w-4xl mx-auto mt-16 mb-10">
  <h2 class="text-2xl font-semibold mb-6 text-gray-800 border-b border-gray-300 pb-2">Daftar Pelanggaran Terbaru</h2>
  <div class="overflow-x-auto">
    <table class="min-w-full border border-gray-300 rounded-lg text-sm text-left text-gray-700">
    <thead class="bg-gray-100">
      <tr>
      <th class="px-4 py-2 border-b border-gray-300">No</th>
      <th class="px-4 py-2 border-b border-gray-300">Tanggal</th>
      <th class="px-4 py-2 border-b border-gray-300">Nama Siswa</th>
      <th class="px-4 py-2 border-b border-gray-300">Kelas</th>
      <th class="px-4 py-2 border-b border-gray-300">Pelanggaran</th>
      <th class="px-4 py-2 border-b border-gray-300 text-center">Poin</th>
      <th class="px-4 py-2 border-b border-gray-300">Pelapor</th>
      <th class="px-4 py-2 border-b border-gray-300 text-center">Bukti</th>
      <th class="px-4 py-2 border-b border-gray-300 text-center">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $no = 1;
      // Ambil ulang data pelanggaran (karena sudah di-fetch sebelumnya jika ada aksi hapus)
      $pelanggaran_list = $koneksi->query("
        SELECT p.*, s.nama AS nama_siswa, s.kelas, g.nama AS nama_guru
        FROM pelanggaran p
        JOIN siswa s ON p.id_siswa = s.id
        JOIN guru g ON p.id_guru = g.id
        ORDER BY p.tanggal DESC, p.id DESC
        LIMIT 5
      ");
      while ($p = $pelanggaran_list->fetch_assoc()):
      ?>
      <tr class="border-b border-gray-200 hover:bg-gray-50">
        <td class="px-4 py-2 text-center"><?= $no++ ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($p['tanggal']) ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($p['nama_siswa']) ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($p['kelas']) ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($p['keterangan']) ?></td>
        <td class="px-4 py-2 text-center font-semibold text-red-600"><?= (int)$p['poin'] ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($p['nama_guru']) ?></td>
        <td class="px-4 py-2 text-center">
        <?php if ($p['foto'] && file_exists('../' . $p['foto'])): ?>
          <img
          src="../<?= htmlspecialchars($p['foto']) ?>"
          alt="Foto Bukti"
          class="h-16 mx-auto rounded-md border border-gray-300 cursor-pointer"
          onclick="zoomFoto(this)"
          title="Klik untuk zoom"
          />
        <?php else: ?>
          <span class="text-gray-400 italic text-xs">Tidak ada</span>
        <?php endif; ?>
        </td>
        <td class="px-4 py-2 text-center">
        <?php if ($p['id_guru'] == $user['id']): ?>
          <a href="?hapus=<?= $p['id'] ?>" onclick="return confirm('Yakin ingin menghapus pelanggaran ini?')" class="text-red-600 hover:underline font-bold text-xs">Hapus</a>
        <?php else: ?>
          <span class="text-gray-400 italic text-xs">-</span>
        <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
    </table>
  </div>
  </section>
<?php endif; ?>

<!-- Modal zoom foto -->
<div
  id="modalZoomFoto"
  class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
  style="display: none;"
  onclick="closeZoom()"
>
  <img id="imgZoom" src="" alt="Zoom Foto" class="max-w-full max-h-full rounded-lg shadow-lg" />
</div>

<script>
  function zoomFoto(img) {
    const modal = document.getElementById('modalZoomFoto');
    const imgZoom = document.getElementById('imgZoom');
    imgZoom.src = img.src;
    modal.style.display = 'flex';
  }

  function closeZoom() {
    const modal = document.getElementById('modalZoomFoto');
    modal.style.display = 'none';
    document.getElementById('imgZoom').src = '';
  }
</script>

<script>
  // Update poin saat pilih pelanggaran
  document.getElementById('id_tata_tertib').addEventListener('change', function () {
    let poin = this.options[this.selectedIndex].getAttribute('data-poin');
    document.getElementById('poin_display').value = poin || '';
  });

  // Preview foto upload
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