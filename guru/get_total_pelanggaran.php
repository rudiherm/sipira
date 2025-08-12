<?php
include '../inc/koneksi.php';

if (isset($_GET['siswa_id']) && is_numeric($_GET['siswa_id'])) {
    $siswa_id = intval($_GET['siswa_id']);
    
    // Query untuk mengambil total pelanggaran siswa
    $stmt = $koneksi->prepare("SELECT total_pelanggaran FROM siswa WHERE id = ?");
    $stmt->bind_param("i", $siswa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $siswa = $result->fetch_assoc();
        echo json_encode(['total_pelanggaran' => $siswa['total_pelanggaran']]);
    } else {
        echo json_encode(['total_pelanggaran' => 0]);
    }
}
?>
