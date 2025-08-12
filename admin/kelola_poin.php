<?php
$page_title = "Kelola Poin";
include '../inc/header.php';
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('admin');

$file = __DIR__.'/batas_pelanggaran.txt';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $b = intval($_POST['batas']);
    file_put_contents($file, $b);
    $msg = "Batas disimpan.";
}
$batas = file_exists($file) ? intval(file_get_contents($file)) : 100;
?>
<h1 class="text-2xl font-bold mb-4">Kelola Poin</h1>
<?php if(isset($msg)): ?><div class="bg-green-100 p-3 rounded mb-3"><?=htmlspecialchars($msg)?></div><?php endif; ?>

<form method="post" class="bg-white p-6 rounded shadow space-y-4">
  <label class="block">Batas Poin Pelanggaran (>= akan dianggap melampaui)</label>
  <input name="batas" type="number" value="<?=$batas?>" class="border p-2 rounded w-40">
  <div><button class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button></div>
</form>

<?php include '../inc/footer.php'; ?>