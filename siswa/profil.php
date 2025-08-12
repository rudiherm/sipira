<?php
$page_title = "Ganti Password";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('siswa');

$id = $_SESSION['user_id'];

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ganti_password'])) {
    $password_baru = trim($_POST['password_baru']);
    $password_konfirmasi = trim($_POST['password_konfirmasi']);

    if ($password_baru !== $password_konfirmasi) {
        $error = "Password konfirmasi tidak cocok.";
    } elseif (strlen($password_baru) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        // Update password di database
        $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
        $stmt = $koneksi->prepare("UPDATE siswa SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed_password, $id);
        if ($stmt->execute()) {
            $success = "Password berhasil diubah.";
        } else {
            $error = "Gagal mengubah password.";
        }
        $stmt->close();
    }
}

// Ambil data siswa
$stmt = $koneksi->prepare("SELECT nama, kelas, foto FROM siswa WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

$foto_folder = '/uploads/foto_siswa/';
$foto_url = '';

if (!empty($data['foto'])) {
    $foto_path = __DIR__ . '/../' . trim($foto_folder, '/') . '/' . $data['foto'];
    if (file_exists($foto_path)) {
        $foto_url = $foto_folder . $data['foto'];
    }
}
?>

<!-- Kontainer utama responsif -->
<div class="max-w-4xl mx-auto p-4 md:p-8 bg-white rounded-3xl shadow-lg mt-8 mb-12">
    <!-- Header -->
    <h1 class="text-3xl font-extrabold mb-6 text-center md:text-left text-gray-900">Ganti Password</h1>
    <div class="flex flex-col md:flex-row items-center md:space-x-8 space-y-4 md:space-y-0">
        <!-- Pesan sukses/error -->
        <?php if (isset($success)): ?>
            <div class="w-full md:w-1/3 mb-4 text-green-700 bg-green-50 border-l-4 border-green-400 p-4 rounded-md">
                <?= $success ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="w-full md:w-1/3 mb-4 text-red-700 bg-red-50 border-l-4 border-red-400 p-4 rounded-md">
                <?= $error ?>
            </div>
        <?php endif; ?>
        <!-- Form ganti password -->
        <form method="POST" class="w-full md:w-2/3 bg-gray-50 p-6 rounded-xl shadow-md space-y-6">
            <div>
                <label for="password_baru" class="block text-gray-700 font-medium mb-2">Password Baru</label>
                <input
                    type="password"
                    name="password_baru"
                    id="password_baru"
                    required
                    placeholder="Masukkan password baru"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                />
            </div>
            <div>
                <label for="password_konfirmasi" class="block text-gray-700 font-medium mb-2">Konfirmasi Password</label>
                <input
                    type="password"
                    name="password_konfirmasi"
                    id="password_konfirmasi"
                    required
                    placeholder="Ulangi password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                />
            </div>
            <button
                type="submit"
                name="ganti_password"
                class="w-full bg-blue-600 text-white py-3 px-6 rounded-xl font-semibold shadow-md hover:bg-blue-700 transition active:scale-95"
            >
                Ganti Password
            </button>
        </form>
    </div>
</div>

<?php include '../inc/footer.php'; ?>