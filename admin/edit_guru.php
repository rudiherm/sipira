<?php
$page_title = "Edit Guru";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';

if (!isset($_GET['id'])) {
    header("Location: kelola_guru.php");
    exit;
}
$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($password)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $koneksi->prepare("UPDATE guru SET nama=?, username=?, password=? WHERE id=?");
        $stmt->bind_param("sssi", $nama, $username, $passwordHash, $id);
    } else {
        $stmt = $koneksi->prepare("UPDATE guru SET nama=?, username=? WHERE id=?");
        $stmt->bind_param("ssi", $nama, $username, $id);
    }
    $stmt->execute();
    header("Location: kelola_guru.php");
    exit;
}

$result = $koneksi->query("SELECT * FROM guru WHERE id=$id");
$r = $result->fetch_assoc();
?>

<div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-5 text-gray-800">✏️ Edit Guru</h1>
    <form method="post" class="space-y-4">
        <div>
            <label class="block text-gray-700 font-medium mb-1">Nama Guru</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($r['nama']) ?>" placeholder="Nama Guru"
                class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none"
                required>
        </div>
        <div>
            <label class="block text-gray-700 font-medium mb-1">Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($r['username']) ?>" placeholder="Username"
                class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none"
                required>
        </div>
        <div>
            <label class="block text-gray-700 font-medium mb-1">Password Baru</label>
            <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah"
                class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>
<div class="flex items-center space-x-2 pt-3">
    <button type="submit"
        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center">
        <i class="fas fa-save mr-2"></i> Simpan
    </button>
    <a href="kelola_guru.php"
        class="px-4 py-2 bg-red-400 hover:bg-red-500 text-white rounded-lg transition flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Batal
    </a>
</div>

    </form>
</div>

<?php include '../inc/footer.php'; ?>
