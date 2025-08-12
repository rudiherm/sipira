<?php
$page_title = "Dashboard Guru";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('guru');
$batas_pelanggaran = file_exists(__DIR__ . '/../admin/batas_pelanggaran.txt') ? intval(file_get_contents(__DIR__ . '/../admin/batas_pelanggaran.txt')) : 100;
$count_pelanggaran = $koneksi->query("SELECT COUNT(*) AS total FROM pelanggaran")->fetch_assoc()['total'] ?? 0;
$count_prestasi = $koneksi->query("SELECT COUNT(*) AS total FROM prestasi")->fetch_assoc()['total'] ?? 0;
$count_siswa = $koneksi->query("SELECT COUNT(*) AS total FROM siswa")->fetch_assoc()['total'] ?? 0;
$total_q = $koneksi->prepare("SELECT COUNT(*) as total FROM siswa WHERE total_pelanggaran >= ?");
$total_q->bind_param("i", $batas_pelanggaran);
$total_q->execute();
$total_res = $total_q->get_result()->fetch_assoc();
$total_data = $total_res['total'];
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? intval($_GET['page']) : 1;
$total_page = max(1, ceil($total_data / $limit));
$offset = ($page - 1) * $limit;
$q = $koneksi->prepare("SELECT id, nama, kelas, total_pelanggaran FROM siswa WHERE total_pelanggaran >= ? ORDER BY total_pelanggaran DESC LIMIT ? OFFSET ?");
$q->bind_param("iii", $batas_pelanggaran, $limit, $offset);
$q->execute();
$over = $q->get_result() ?: false;
function getSP($poin)
{
    if ($poin >= 76) return "Surat Perjanjian Ketiga (SP 3)";
    if ($poin >= 51) return "Surat Perjanjian Kedua (SP 2)";
    if ($poin >= 25) return "Surat Perjanjian Pertama (SP 1)";
    return "";
}
$peringatan_pelanggaran_q = $koneksi->prepare("SELECT id, nama, kelas, total_pelanggaran FROM siswa WHERE total_pelanggaran >= 25 ORDER BY total_pelanggaran DESC LIMIT 5");
$peringatan_pelanggaran_q->execute();
$peringatan_pelanggaran = $peringatan_pelanggaran_q->get_result();
function getPrestasiPredikat($poin)
{
    if ($poin >= 151) return "Sertifikat, Hadiah & Anugerah Waluya Utama";
    if ($poin >= 126) return "Sertifikat & Hadiah Siswa Berprestasi";
    if ($poin >= 100) return "Sertifikat Siswa Berprestasi";
    return "";
}
$peringatan_prestasi_q = $koneksi->query("
    SELECT id, nama, kelas, total_prestasi
    FROM siswa
    WHERE total_prestasi >= 100
    ORDER BY total_prestasi DESC
    LIMIT 5
");
$max_prestasi_row = $koneksi->query("SELECT nama, total_prestasi FROM siswa ORDER BY total_prestasi DESC LIMIT 1")->fetch_assoc();
$max_prestasi_nama = $max_prestasi_row['nama'] ?? '';
$max_prestasi_score = intval($max_prestasi_row['total_prestasi'] ?? 0);
?>
<style>
    /* Animasi muncul */
    .fade-in {
        animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Badge styling */
    .badge {
        display: inline-block;
        padding: 0.25em 0.6em;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-red {
        background: #fee2e2;
        color: #b91c1c;
    }

    .badge-green {
        background: #d1fae5;
        color: #065f46;
    }
</style>
<div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8 fade-in">
    <!-- HERO HEADER -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl p-8 mb-10 text-white shadow-lg flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold mb-2">Selamat Datang di Pedoman Pancawaluya</h1>
            <p class="text-blue-100">SMA Negeri 2 Banjar, Dinas Pendidikan, Provinsi Jawa Barat</p>
            <p class="mt-2 text-blue-200 text-sm">
                Pedoman Pendidikan Karakter Pancawaluya berisi <b>poin prestasi</b> dan <b>poin pelanggaran</b>.
                Poin prestasi diberikan atas pencapaian dan penghargaan siswa, sedangkan poin pelanggaran diberikan jika melanggar tata tertib.
                Akumulasi kedua poin ini menjadi dasar pemberian sanksi maupun apresiasi sesuai Pedoman Pancawaluya.
            </p>
        </div>
        <button
            id="previewPedomanBtn"
            class="mt-4 md:mt-0 inline-flex items-center bg-white text-blue-700 font-semibold px-4 py-2 rounded-lg shadow hover:bg-blue-50 transition"
            type="button">
            <i class="fas fa-file-pdf text-red-500 text-xl mr-2"></i>
            Pedoman
        </button>
    </div>
    <!-- POPUP MODAL PDF PREVIEW -->
    <div id="pedomanModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
        <div class="bg-white rounded-xl shadow-lg max-w-3xl w-full relative p-0 overflow-hidden">
            <div class="flex justify-between items-center px-6 py-4 border-b">
                <h2 class="font-bold text-lg text-gray-700">Preview Pedoman Pancawaluya</h2>
                <button id="closePedomanModal" class="text-gray-500 hover:text-red-500 text-2xl font-bold" aria-label="Tutup">&times;</button>
            </div>
            <div class="p-0">
                <iframe src="../uploads/pdf/Pedoman_Pancawaluya.pdf" class="w-full" style="height:60vh;" frameborder="0"></iframe>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('previewPedomanBtn').onclick = function() {
            document.getElementById('pedomanModal').classList.remove('hidden');
        };
        document.getElementById('closePedomanModal').onclick = function() {
            document.getElementById('pedomanModal').classList.add('hidden');
        };
        document.getElementById('pedomanModal').addEventListener('click', function(e) {
            if (e.target === this) this.classList.add('hidden');
        });
    </script>
    <!-- STATISTIK CARD -->
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Pelanggaran -->
        <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition transform hover:-translate-y-1 border-l-8 border-red-400">
            <div class="flex items-center space-x-4">
                <i class="fas fa-exclamation-triangle text-red-500 text-4xl"></i>
                <div>
                    <p class="text-red-500 font-semibold">Total Pelanggaran</p>
                    <p class="text-3xl font-bold text-red-700"><?= number_format($count_pelanggaran) ?></p>
                </div>
            </div>
        </div>
        <!-- Prestasi -->
        <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition transform hover:-translate-y-1 border-l-8 border-yellow-400">
            <div class="flex items-center space-x-4">
                <i class="fas fa-trophy text-yellow-500 text-4xl"></i>
                <div>
                    <p class="text-yellow-600 font-semibold">Total Prestasi</p>
                    <p class="text-3xl font-bold text-yellow-700"><?= number_format($count_prestasi) ?></p>
                </div>
            </div>
        </div>
        <!-- Siswa -->
        <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition transform hover:-translate-y-1 border-l-8 border-green-400">
            <div class="flex items-center space-x-4">
                <i class="fas fa-users text-green-500 text-4xl"></i>
                <div>
                    <p class="text-green-600 font-semibold">Total Siswa</p>
                    <p class="text-3xl font-bold text-green-700"><?= number_format($count_siswa) ?></p>
                </div>
            </div>
        </div>
        <!-- Guru -->
        <?php
        $count_guru = $koneksi->query("SELECT COUNT(*) AS total FROM guru")->fetch_assoc()['total'] ?? 0;
        ?>
        <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition transform hover:-translate-y-1 border-l-8 border-blue-400">
            <div class="flex items-center space-x-4">
                <i class="fas fa-chalkboard-teacher text-blue-500 text-4xl"></i>
                <div>
                    <p class="text-blue-600 font-semibold">Total Guru</p>
                    <p class="text-3xl font-bold text-blue-700"><?= number_format($count_guru) ?></p>
                </div>
            </div>
        </div>
    </section>
    <!-- ALERT PELANGGARAN -->
    <?php if ($peringatan_pelanggaran->num_rows > 0): ?>
        <section class="bg-red-50 border-l-8 border-red-500 p-6 rounded-xl shadow mb-10 fade-in">
            <h2 class="font-bold text-xl mb-4 flex items-center gap-2 text-red-700">
                <i class="fas fa-exclamation-circle"></i> Daftar Siswa dengan Pelanggaran
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-red-100">
                        <tr>
                            <th class="px-4 py-2 text-left">No</th>
                            <th class="px-4 py-2 text-left">Nama</th>
                            <th class="px-4 py-2 text-left">Kelas</th>
                            <th class="px-4 py-2 text-left">Jumlah Poin</th>
                            <th class="px-4 py-2 text-left">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        while ($row = $peringatan_pelanggaran->fetch_assoc()): ?>
                            <tr class="<?= $no % 2 == 0 ? 'bg-red-50' : '' ?> hover:bg-red-100 transition">
                                <td class="px-4 py-2"><?= $no++ ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['nama']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['kelas']) ?></td>
                                <td class="px-4 py-2"><span class="badge badge-red"><?= $row['total_pelanggaran'] ?> Poin</span></td>
                                <td class="px-4 py-2 font-semibold"><?= getSP(intval($row['total_pelanggaran'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
    <!-- ALERT PRESTASI -->
    <?php if ($peringatan_prestasi_q->num_rows > 0): ?>
        <section class="bg-green-50 border-l-8 border-green-500 p-6 rounded-xl shadow mb-10 fade-in">
            <h2 class="font-bold text-xl mb-4 flex items-center gap-2 text-green-700">
                <i class="fas fa-trophy"></i> Apresiasi Siswa Berprestasi
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-green-100">
                        <tr>
                            <th class="px-4 py-2 text-left">No</th>
                            <th class="px-4 py-2 text-left">Nama</th>
                            <th class="px-4 py-2 text-left">Kelas</th>
                            <th class="px-4 py-2 text-left">Prestasi</th>
                            <th class="px-4 py-2 text-left">Predikat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($row = $peringatan_prestasi_q->fetch_assoc()):
                            $predikat = getPrestasiPredikat(intval($row['total_prestasi']));
                            if ($row['nama'] === $max_prestasi_nama && $max_prestasi_score < 151) {
                                $predikat .= ($predikat ? ', ' : '') . 'Anugerah Waluya Utama';
                            }
                        ?>
                            <tr class="<?= $no % 2 == 0 ? 'bg-green-50' : '' ?> hover:bg-green-100 transition">
                                <td class="px-4 py-2"><?= $no++ ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['nama']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['kelas']) ?></td>
                                <td class="px-4 py-2"><span class="badge badge-green"><?= $row['total_prestasi'] ?> Poin</span></td>
                                <td class="px-4 py-2 font-semibold"><?= $predikat ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
    <!-- MENU UTAMA -->
    <section class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-6 gap-6 mb-10">
        <a href="input_prestasi.php" class="bg-white p-6 rounded-xl shadow hover:bg-blue-50 hover:shadow-lg transform hover:-translate-y-1 transition flex flex-col items-center">
            <i class="fas fa-medal text-blue-500 text-3xl mb-2"></i>
            <span class="font-semibold text-gray-700">Input Prestasi</span>
        </a>
        <a href="input_pelanggaran.php" class="bg-white p-6 rounded-xl shadow hover:bg-red-50 hover:shadow-lg transform hover:-translate-y-1 transition flex flex-col items-center">
            <i class="fas fa-ban text-red-500 text-3xl mb-2"></i>
            <span class="font-semibold text-gray-700">Input Pelanggaran</span>
        </a>
        <a href="data_siswa.php" class="bg-white p-6 rounded-xl shadow hover:bg-green-50 hover:shadow-lg transform hover:-translate-y-1 transition flex flex-col items-center">
            <i class="fas fa-user-graduate text-green-500 text-3xl mb-2"></i>
            <span class="font-semibold text-gray-700">Data Siswa</span>
        </a>
        <a href="tata_tertib.php" class="bg-white p-6 rounded-xl shadow hover:bg-yellow-50 hover:shadow-lg transform hover:-translate-y-1 transition flex flex-col items-center">
            <i class="fas fa-gavel text-yellow-500 text-3xl mb-2"></i>
            <span class="font-semibold text-gray-700">Poin Pelanggaran</span>
        </a>
        <a href="poin_prestasi.php" class="bg-white p-6 rounded-xl shadow hover:bg-indigo-50 hover:shadow-lg transform hover:-translate-y-1 transition flex flex-col items-center">
            <i class="fas fa-star text-indigo-500 text-3xl mb-2"></i>
            <span class="font-semibold text-gray-700">Poin Prestasi</span>
        </a>
        <a href="pengumuman.php" class="bg-white p-6 rounded-xl shadow hover:bg-purple-50 hover:shadow-lg transform hover:-translate-y-1 transition flex flex-col items-center">
            <i class="fas fa-bullhorn text-purple-500 text-3xl mb-2"></i>
            <span class="font-semibold text-gray-700">Pengumuman</span>
        </a>
        <a href="laporan.php" class="bg-white p-6 rounded-xl shadow hover:bg-purple-50 hover:shadow-lg transform hover:-translate-y-1 transition flex flex-col items-center">
            <i class="fas fa-file-invoice text-purple-500 text-3xl mb-2"></i>
            <span class="font-semibold text-gray-700">Cetak Laporan</span>
        </a>
        <a href="ganti_password.php" class="bg-white p-6 rounded-xl shadow hover:bg-blue-50 hover:shadow-lg transform hover:-translate-y-1 transition flex flex-col items-center">
            <i class="fas fa-key text-blue-500 text-3xl mb-2"></i>
            <span class="font-semibold text-gray-700">Ganti Password</span>
        </a>
    </section>
</div>
<?php include '../inc/footer.php'; ?>