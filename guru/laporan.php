<?php
$page_title = "Laporan";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('guru');

// Filter
$jenis = $_GET['jenis'] ?? 'prestasi';
$kelas = $_GET['kelas'] ?? '';
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';

$where = [];
$params = [];
$types = '';

if ($kelas) {
    $where[] = "s.kelas = ?";
    $params[] = $kelas;
    $types .= 's';
}
if ($tgl_awal && $tgl_akhir) {
    $where[] = "p.tanggal BETWEEN ? AND ?";
    $params[] = $tgl_awal;
    $params[] = $tgl_akhir;
    $types .= 'ss';
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

if ($jenis === 'prestasi') {
    $sql = "SELECT s.nama, s.kelas, p.keterangan, p.poin, p.tanggal
            FROM prestasi p
            JOIN siswa s ON s.id = p.id_siswa
            $where_sql
            ORDER BY p.tanggal DESC";
} else {
    $sql = "SELECT s.nama, s.kelas, p.keterangan, p.poin, p.tanggal, g.nama AS nama_guru
            FROM pelanggaran p
            JOIN siswa s ON s.id = p.id_siswa
            LEFT JOIN guru g ON g.id = p.id_guru
            $where_sql
            ORDER BY p.tanggal DESC";
}

$stmt = $koneksi->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Data untuk grafik
$statistik = [];
while ($row = $result->fetch_assoc()) {
    $statistik[$row['kelas']] = ($statistik[$row['kelas']] ?? 0) + $row['poin'];
    $data_tabel[] = $row;
}
$result->data_seek(0); // reset pointer untuk tabel HTML

$kelas_list = $koneksi->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas");
?>

<body class="bg-gray-100 p-6">

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold flex items-center gap-3">
            <?= $jenis === 'prestasi' ? '🏆' : '⚠️' ?>
            Laporan <?=htmlspecialchars(ucfirst($jenis))?>
        </h1>
        <p class="text-gray-600">Filter dan lihat data <?= $jenis ?> siswa secara detail.</p>
    </div>

    <!-- Filter -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <form method="get" class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1" for="jenis">Jenis</label>
                <select id="jenis" name="jenis" class="border rounded w-full p-2">
                    <option value="prestasi" <?= $jenis === 'prestasi' ? 'selected' : '' ?>>Prestasi</option>
                    <option value="pelanggaran" <?= $jenis === 'pelanggaran' ? 'selected' : '' ?>>Pelanggaran</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1" for="kelas">Kelas</label>
                <select id="kelas" name="kelas" class="border rounded w-full p-2">
                    <option value="">Semua</option>
                    <?php while ($r = $kelas_list->fetch_assoc()) : ?>
                        <option value="<?=htmlspecialchars($r['kelas'])?>" <?= $kelas === $r['kelas'] ? 'selected' : '' ?>>
                            <?=htmlspecialchars($r['kelas'])?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1" for="tgl_awal">Tanggal Awal</label>
                <input type="date" id="tgl_awal" name="tgl_awal" value="<?=htmlspecialchars($tgl_awal)?>" class="border rounded w-full p-2" />
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1" for="tgl_akhir">Tanggal Akhir</label>
                <input type="date" id="tgl_akhir" name="tgl_akhir" value="<?=htmlspecialchars($tgl_akhir)?>" class="border rounded w-full p-2" />
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded w-full hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-filter"></i> Tampilkan
                </button>
            </div>
        </form>
    </div>

    <!-- Grafik -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <canvas id="chartLaporan" height="100"></canvas>
    </div>

    <!-- Tabel -->
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table id="laporanTable" class="min-w-full text-sm">
            <thead class="<?= $jenis === 'prestasi' ? 'bg-green-50' : 'bg-red-50' ?>">
                <tr>
                    <th class="border px-4 py-2 text-center">No</th>
                    <th class="border px-4 py-2 text-left">Nama</th>
                    <th class="border px-4 py-2 text-left">Kelas</th>
                    <th class="border px-4 py-2 text-left">Keterangan</th>
                    <th class="border px-4 py-2 text-center">Poin</th>
                    <th class="border px-4 py-2 text-center">Tanggal</th>
                    <?php if ($jenis === 'pelanggaran'): ?>
                        <th class="border px-4 py-2 text-left">Guru Pelapor</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                if (empty($data_tabel)) {
                    echo '<tr><td colspan="' . ($jenis === 'pelanggaran' ? '7' : '6') . '" class="text-center p-4 text-gray-500">Tidak ada data ditemukan</td></tr>';
                } else {
                    foreach ($data_tabel as $row):
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="border px-4 py-2 text-center"><?= $no++ ?></td>
                    <td class="border px-4 py-2 font-medium"><?= htmlspecialchars($row['nama']) ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($row['kelas']) ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($row['keterangan']) ?></td>
                    <td class="border px-4 py-2 text-center">
                        <span class="px-2 py-1 rounded text-white <?= $jenis === 'prestasi' ? 'bg-green-500' : 'bg-red-500' ?>">
                            <?= htmlspecialchars($row['poin']) ?>
                        </span>
                    </td>
                    <td class="border px-4 py-2 text-center"><?= htmlspecialchars($row['tanggal']) ?></td>
                    <?php if ($jenis === 'pelanggaran'): ?>
                        <td class="border px-4 py-2"><?= htmlspecialchars($row['nama_guru'] ?? '-') ?></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; } ?>
            </tbody>
        </table>
    </div>

    <!-- Tombol -->
    <div class="mt-6 flex justify-end gap-3">
        <button onclick="exportExcel()" class="bg-yellow-600 text-white px-5 py-2 rounded hover:bg-yellow-700 transition flex items-center gap-2">
            <i class="fa-solid fa-file-excel"></i> Excel
        </button>
        <button onclick="exportPDF()" class="bg-red-600 text-white px-5 py-2 rounded hover:bg-red-700 transition flex items-center gap-2">
            <i class="fa-solid fa-file-pdf"></i> PDF
        </button>
        <button onclick="window.print()" class="bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700 transition flex items-center gap-2">
            <i class="fa-solid fa-print"></i> Cetak
        </button>
    </div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<script>
    // Data Grafik
    const ctx = document.getElementById('chartLaporan').getContext('2d');
    const chartData = <?= json_encode([
        'labels' => array_keys($statistik),
        'data' => array_values($statistik)
    ]) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Total Poin',
                data: chartData.data,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // Export Excel
    function exportExcel() {
        const table = document.getElementById("laporanTable");
        const wb = XLSX.utils.table_to_book(table, { sheet: "Laporan" });
        XLSX.writeFile(wb, "laporan_<?= $jenis ?>.xlsx");
    }

    // Export PDF
    function exportPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.text("Laporan <?= ucfirst($jenis) ?>", 14, 15);
        doc.autoTable({ html: '#laporanTable', startY: 20 });
        doc.save("laporan_<?= $jenis ?>.pdf");
    }
</script>

<?php include '../inc/footer.php'; ?>
