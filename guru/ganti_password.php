<?php
session_start();
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('guru');
$page_title = "Ganti Password Guru";
// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_baru = $_POST['password_baru'] ?? '';
    $password_ulang = $_POST['password_ulang'] ?? '';
    // Validasi input
    if (empty($password_baru) || empty($password_ulang)) {
        $error = "Harap isi semua field.";
    } elseif ($password_baru !== $password_ulang) {
        $error = "Password tidak cocok.";
    } else {
        // Update password
        $guru_id = $_SESSION['user_id'];
        $hash_password = password_hash($password_baru, PASSWORD_DEFAULT);
        $stmt = $koneksi->prepare("UPDATE guru SET password=? WHERE id=?");
        $stmt->bind_param("si", $hash_password, $guru_id);
        if ($stmt->execute()) {
            $success = "Password berhasil diubah.";
        } else {
            $error = "Gagal mengubah password.";
        }
        $stmt->close();
    }
}
?>
<!-- Include header -->
<?php include '../inc/header.php'; ?>
<!-- Kontainer utama -->
<div class="min-h-screen flex items-center justify-center bg-gradient-to-r from-blue-100 via-blue-200 to-blue-300 p-4">
    <!-- Card utama -->
    <div class="bg-white rounded-xl shadow-lg max-w-lg w-full p-8 transition-transform transform hover:scale-105">
        <h2 class="text-3xl font-bold text-blue-700 mb-6 text-center">Ganti Password Guru</h2>
        <!-- Tombol kembali -->
        <div class="mb-4 text-center">
            <a href="index.php" class="inline-block px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition font-medium">
                &larr; Kembali
            </a>
        </div>
        <!-- Pesan error/sukses -->
        <?php if (isset($error)): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg border border-red-300">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg border border-green-300">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        <!-- Form ganti password -->
        <form method="POST" class="space-y-4">
            <div>
                <label for="password_baru" class="block mb-2 text-gray-700 font-semibold">Password Baru</label>
                <input
                    type="password"
                    id="password_baru"
                    name="password_baru"
                    placeholder="Masukkan password baru"
                    required
                    autocomplete="new-password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition">
            </div>
            <div>
                <label for="password_ulang" class="block mb-2 text-gray-700 font-semibold">Ulangi Password</label>
                <input
                    type="password"
                    id="password_ulang"
                    name="password_ulang"
                    placeholder="Ulangi password"
                    required
                    autocomplete="new-password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition">
            </div>
            <div>
                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition">
                    Simpan Password
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Include footer -->
<?php include '../inc/footer.php'; ?>