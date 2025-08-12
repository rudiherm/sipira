<?php
$page_title = "Kelola Guru";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// -------------------------
// PROSES CRUD SAMA PERSIS
// -------------------------
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $stmt = $koneksi->prepare("DELETE FROM guru WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: kelola_guru.php");
    exit;
}

if (isset($_GET['hapus_semua']) && $_GET['hapus_semua'] == 'true') {
    $koneksi->query("DELETE FROM guru");
    header("Location: kelola_guru.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_guru'])) {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $koneksi->prepare("INSERT INTO guru (nama, username, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nama, $username, $password);
    $stmt->execute();
    header("Location: kelola_guru.php");
    exit;
}

if (isset($_POST['import_excel'])) {
    if (isset($_FILES['file_excel']) && $_FILES['file_excel']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file_excel']['tmp_name'];
        $fileType = $_FILES['file_excel']['type'];
        $allowedTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel'
        ];
        if (in_array($fileType, $allowedTypes)) {
            try {
                $spreadsheet = IOFactory::load($fileTmpPath);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();

                foreach ($rows as $index => $row) {
                    if ($index === 0) continue;
                    $nama = trim($row[0] ?? '');
                    $username = trim($row[1] ?? '');
                    $passwordPlain = trim($row[2] ?? '');

                    if ($nama && $username && $passwordPlain) {
                        $stmtCheck = $koneksi->prepare("SELECT id FROM guru WHERE username=?");
                        $stmtCheck->bind_param("s", $username);
                        $stmtCheck->execute();
                        $stmtCheck->store_result();
                        if ($stmtCheck->num_rows == 0) {
                            $passwordHash = password_hash($passwordPlain, PASSWORD_DEFAULT);
                            $stmtInsert = $koneksi->prepare("INSERT INTO guru (nama, username, password) VALUES (?, ?, ?)");
                            $stmtInsert->bind_param("sss", $nama, $username, $passwordHash);
                            $stmtInsert->execute();
                            $stmtInsert->close();
                        }
                        $stmtCheck->close();
                    }
                }
                header("Location: kelola_guru.php");
                exit;
            } catch (Exception $e) {
                echo "<script>alert('Gagal memproses file: " . $e->getMessage() . "');</script>";
            }
        } else {
            echo "<script>alert('Format file tidak didukung. Silakan upload file XLSX atau XLS.');</script>";
        }
    } else {
        echo "<script>alert('Gagal mengupload file.');</script>";
    }
}

// -------------------------
// PAGINATION
// -------------------------
$limit = 10; // data per halaman
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$totalQuery = $koneksi->query("SELECT COUNT(*) AS total FROM guru");
$totalData = $totalQuery->fetch_assoc()['total'];
$totalPages = ceil($totalData / $limit);

$q = $koneksi->query("SELECT id, nama, username FROM guru ORDER BY id DESC LIMIT $limit OFFSET $offset");
?>

<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<div class="max-w-7xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-8 flex items-center gap-3 text-gray-800">
        <i class="fas fa-user-tie text-blue-600"></i> Kelola Guru
    </h1>

    <!-- Import Excel -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
        <form method="post" enctype="multipart/form-data" class="flex flex-col md:flex-row items-center gap-4">
            <input type="file" name="file_excel" accept=".xlsx, .xls" required class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            <button name="import_excel" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center gap-2 transition">
                <i class="fas fa-file-import"></i> Import Excel
            </button>
        </form>
    </div>

    <!-- Hapus semua -->
    <div class="mb-8 flex justify-end">
        <a href="?hapus_semua=true" onclick="return confirm('Yakin hapus semua data?')" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition font-semibold">
            <i class="fas fa-trash-alt"></i> Hapus Semua Akun
        </a>
    </div>

    <!-- Form Tambah Guru -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
        <form class="grid grid-cols-1 md:grid-cols-4 gap-4" method="post">
            <input type="text" name="nama" placeholder="Nama Guru" required class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            <input type="text" name="username" placeholder="Username" required class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            <input type="password" name="password" placeholder="Password" required class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            <button name="add_guru" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg flex items-center justify-center gap-2 transition font-semibold">
                <i class="fas fa-plus"></i> Tambah
            </button>
        </form>
    </div>

    <!-- Tabel Guru -->
    <div class="overflow-x-auto bg-white rounded-xl shadow-md">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 w-12">No</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Nama</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Username</th>
                    <th class="px-4 py-3 w-24 text-center font-semibold text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php
                $no = $offset + 1;
                while ($r = $q->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-100 transition">
                        <td class="px-4 py-2 text-center"><?= $no++ ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($r['nama']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($r['username']) ?></td>
                        <td class="px-4 py-2 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="edit_guru.php?id=<?= $r['id'] ?>"
                                    class="bg-yellow-400 hover:bg-yellow-500 p-2 rounded-lg text-white transition"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?hapus=<?= $r['id'] ?>"
                                    onclick="return confirm('Yakin hapus data ini?')"
                                    class="bg-red-500 hover:bg-red-600 p-2 rounded-lg text-white transition"
                                    title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>

                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center mt-6">
            <nav class="inline-flex items-center space-x-1">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="px-3 py-1 rounded <?= ($i == $page) ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php include '../inc/footer.php'; ?>