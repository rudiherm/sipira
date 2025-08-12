<?php
// login.php
session_start();
if (isset($_SESSION['user_id'])) {
  switch ($_SESSION['role']) {
    case 'admin':
      header('Location: admin/index.php');
      exit;
    case 'guru':
      header('Location: guru/index.php');
      exit;
    case 'siswa':
      header('Location: siswa/index.php');
      exit;
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | SMA Negeri 2 Banjar</title>
  <link href="public/assets/css/tailwind.css" rel="stylesheet" />
  <link rel="icon" href="https://tailwindcss.com/_next/static/media/tailwindcss-mark.d52e9897.svg" type="image/svg+xml" />
  <link href="../public/assets/css/tailwind.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-blue-100">
  <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 relative overflow-hidden">
    <div class="absolute -top-20 -right-20 w-40 h-40 bg-blue-200 rounded-full blur-3xl opacity-50"></div>
    <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-purple-200 rounded-full blur-3xl opacity-50"></div>
    <div class="flex justify-center mb-6">
      <img src="assets/logo.png" alt="Logo" class="w-40 h-auto" />
    </div>
<h2 class="text-2xl font-bold text-center text-gray-800 mb-2">Selamat Datang</h2>
<p class="text-center text-gray-500 mb-6 text-sm">Silakan masuk ke akun Anda untuk mengakses fitur dan data yang tersedia. Pastikan informasi login Anda benar agar dapat melanjutkan proses dengan lancar.</p>
    <form action="login_aksi" method="POST" class="space-y-5">
      <div class="relative">
        <label class="block text-sm font-medium text-gray-700 mb-1" for="username">Username</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-3 flex items-center text-gray-400"><i class="fa fa-user"></i></span>
          <input type="text" id="username" name="username" placeholder="Masukkan username" required class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none transition" />
        </div>
      </div>
      <div class="relative">
        <label class="block text-sm font-medium text-gray-700 mb-1" for="password">Password</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-3 flex items-center text-gray-400"><i class="fa fa-lock"></i></span>
          <input type="password" id="password" name="password" placeholder="Masukkan password" required class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none transition" />
        </div>
      </div>
      <button type="submit" class="w-full py-2 px-4 bg-blue-600 text-white rounded-lg font-semibold shadow hover:bg-blue-700 active:scale-95 transition">Masuk</button>
    </form>
  </div>
</body>
</html>