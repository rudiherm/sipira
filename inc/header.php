<?php
if (session_status() == PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1" />
  <meta name="description" content="Sistem Informasi Poin Prestasi dan Pelanggaran Siswa SMA Negeri 2 Banjar" />
  <meta name="author" content="Rudi Hermawan" />
  <meta name="theme-color" content="#2563eb" />
  <title>
    <?= isset($page_title) 
      ? htmlspecialchars($page_title) . " - Sistem Poin SMA Negeri 2 Banjar" 
      : "Sistem Informasi Poin Prestasi & Pelanggaran SMA Negeri 2 Banjar" ?>
  </title>
  
  <!-- Favicon -->
  <link rel="icon" href="https://tailwindcss.com/_next/static/media/tailwindcss-mark.d52e9897.svg" type="image/svg+xml" />

  <!-- Google Fonts: Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />

  <!-- Tailwind CSS -->
  <link href="../public/assets/css/tailwind.css" rel="stylesheet">

  <!-- FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <style>
    /* Terapkan font Poppins sebagai font utama */
    body {
      font-family: 'Poppins', sans-serif;
    }
  </style>
</head>

<body class="bg-gradient-to-br from-blue-50 to-gray-100 min-h-screen flex flex-col">

<!-- Navbar -->
<nav class="bg-white/90 backdrop-blur shadow-lg rounded-b-xl p-3 sm:p-4 sticky top-0 z-50 transition-all duration-300">
  <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-4">
    
    <!-- Logo -->
    <a href="index.php" aria-label="Beranda Sistem Poin" class="flex items-center gap-3">
      <img src="../assets/logo.png" alt="Logo Sistem Poin" class="w-16 sm:w-20 h-auto drop-shadow-md" />
      <span class="hidden sm:block font-semibold text-lg text-gray-800 tracking-wide">
        SMA Negeri 2 Banjar
      </span>
    </a>

    <!-- Menu User -->
    <div class="flex items-center gap-2 sm:gap-4 text-sm flex-wrap justify-end">
      <?php if (isset($_SESSION['username'])): ?>
        <button type="button"
          id="changePasswordBtn"
          class="text-gray-700 bg-blue-100 px-3 py-1 rounded-full shadow flex items-center gap-2 select-none text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
          aria-label="Ganti Password"
        >
          <i class="fas fa-user-circle text-blue-500"></i>
          <span><b><?= htmlspecialchars($_SESSION['username']) ?></b></span>
        </button>
        <a href="../logout.php" 
           aria-label="Logout" 
           class="bg-red-500 text-white px-3 py-1 rounded-lg hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400 transition flex items-center gap-2 text-xs sm:text-sm">
          <i class="fas fa-right-from-bracket"></i>
          Logout
        </a>
      <?php else: ?>
        <a href="../login.php" 
           aria-label="Login" 
           class="bg-blue-600 text-white px-3 py-1 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 transition flex items-center gap-2 text-xs sm:text-sm">
          <i class="fas fa-right-to-bracket"></i>
          Login
        </a>
      <?php endif; ?>
        </div>

      
</nav>

<!-- Main Content -->
<main class="w-full max-w-7xl mx-auto p-4 sm:p-6 md:p-8 bg-white rounded-xl shadow mt-4 sm:mt-8 flex-grow transition-all duration-300">
