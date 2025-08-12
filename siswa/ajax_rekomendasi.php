<?php
include '../inc/koneksi.php';
include '../inc/auth.php';
require_role('siswa');

header('Content-Type: application/json');

$judul = $_GET['judul'] ?? '';
$id_ignore = intval($_GET['id_ignore'] ?? 0);

if (empty($judul)) {
    echo json_encode([]);
    exit;
}

// Pisah judul jadi kata2, filter kata pendek
$words = array_filter(array_map('trim', explode(' ', $judul)), fn($w) => strlen($w) > 3);

if (count($words) === 0) {
    echo json_encode([]);
    exit;
}

// Buat query LIKE untuk tiap kata
$likes = [];
$params = [];
foreach ($words as $word) {
    $likes[] = "judul LIKE ?";
    $params[] = "%$word%";
}
$where_like = implode(' OR ', $likes);

$sql = "SELECT id, judul, tgl_posting FROM pengumuman WHERE ($where_like) AND id != ? ORDER BY tgl_posting DESC LIMIT 5";

$stmt = $koneksi->prepare($sql);
if ($stmt === false) {
    echo json_encode([]);
    exit;
}

// Tambahkan $id_ignore ke parameter
$params[] = $id_ignore;

// Buat tipe parameter, semua kata LIKE = 's', id_ignore = 'i'
$types = str_repeat('s', count($words)) . 'i';

// Bind param dengan unpack semua sekaligus
$stmt->bind_param($types, ...$params);

$stmt->execute();
$result = $stmt->get_result();

$recommendations = [];
while ($row = $result->fetch_assoc()) {
    $recommendations[] = [
        'id' => $row['id'],
        'judul' => $row['judul'],
        'tgl_posting' => date('d M Y', strtotime($row['tgl_posting'])),
    ];
}

echo json_encode($recommendations);
