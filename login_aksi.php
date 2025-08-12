<?php
// login_aksi.php
session_start();
include 'inc/koneksi.php';

$username = mysqli_real_escape_string($koneksi, $_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo "<script>alert('Masukkan username & password'); window.location='login.php';</script>";
    exit;
}

// List of user tables to check
$tables = [
    ['table' => 'siswa', 'role' => 'siswa'],
    ['table' => 'guru', 'role' => 'guru'],
    ['table' => 'admin', 'role' => 'admin']
];

$user_found = false;
foreach ($tables as $entry) {
    $stmt = $koneksi->prepare("SELECT id, username, password FROM {$entry['table']} WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // User found, verify password
        if (password_verify($password, $row['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $entry['role'];
            // Redirect based on role
            switch ($entry['role']) {
                case 'admin':
                    header('Location: admin/index.php');
                    break;
                case 'guru':
                    header('Location: guru/index.php');
                    break;
                case 'siswa':
                    header('Location: siswa/index.php');
                    break;
            }
            $user_found = true;
            exit;
        }
    }
    $stmt->close();
}

if (!$user_found) {
    // Do not specify whether username or password was wrong
    echo "<script>alert('Username atau password salah'); window.location='login.php';</script>";
    exit;
}
?>