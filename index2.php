<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sistem Informasi Poin Prestasi dan Pelanggaran Siswa | SMA Negeri 2 Banjar</title>
  <!-- Favicon -->
  <link rel="icon" href="https://tailwindcss.com/_next/static/media/tailwindcss-mark.d52e9897.svg" type="image/svg+xml" />
  
  <link href="public/assets/css/tailwind.css" rel="stylesheet">
  
  <!-- Link FontAwesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <!-- Google Fonts: Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }
    .glass {
      background: rgba(255, 255, 255, 0.7);
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>
<body class="bg-gradient-to-br from-blue-100 via-white to-blue-200 text-gray-800">

  <!-- Navbar -->
  <header class="bg-white/80 glass shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <img src="assets/logo.png" alt="Logo" class="w-16 h-auto" />
        <span class="text-xl font-bold text-blue-800">SMA Negeri 2 Banjar</span>
      </div>
      <nav class="hidden md:flex gap-8 text-base font-semibold">
        <a href="#fitur" class="hover:text-blue-600 transition duration-300 ease-in-out">Fitur</a>
        <a href="#tentang" class="hover:text-blue-600 transition duration-300 ease-in-out">Tentang</a>
        <a href="login.php" class="text-blue-600 hover:underline transition duration-300 ease-in-out">Masuk</a>
      </nav>
      <button class="md:hidden p-2 rounded-lg bg-blue-100 text-blue-700" onclick="document.getElementById('mobileNav').classList.toggle('hidden')">
        <i class="fas fa-bars text-xl"></i>
      </button>
    </div>
    <!-- Mobile Nav -->
    <div id="mobileNav" class="md:hidden hidden px-4 pb-4">
      <nav class="flex flex-col gap-2 text-base font-semibold">
        <a href="#fitur" class="hover:text-blue-600 transition duration-300 ease-in-out">Fitur</a>
        <a href="#tentang" class="hover:text-blue-600 transition duration-300 ease-in-out">Tentang</a>
        <a href="login.php" class="text-blue-600 hover:underline transition duration-300 ease-in-out">Masuk</a>
      </nav>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="relative flex items-center justify-center min-h-[60vh] py-24 px-4 overflow-hidden">
    <!-- Decorative Backgrounds -->
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute -top-40 -left-40 w-[500px] h-[500px] bg-gradient-to-br from-blue-400 via-blue-200 to-blue-300 opacity-30 rounded-full blur-3xl"></div>
      <div class="absolute -bottom-40 -right-40 w-[500px] h-[500px] bg-gradient-to-tr from-blue-400 via-blue-200 to-blue-300 opacity-30 rounded-full blur-3xl"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] bg-gradient-to-br from-blue-100 via-white to-blue-100 opacity-20 rounded-full blur-2xl"></div>
    </div>
    <div class="relative z-10 w-full max-w-3xl text-center">
      <h1 class="text-5xl md:text-6xl font-extrabold text-blue-800 mb-6 leading-tight drop-shadow-lg">
        Sistem Poin Siswa <span class="text-blue-600">SMA Negeri 2 Banjar</span>
      </h1>
      <p class="text-gray-700 text-xl md:text-2xl mb-10 font-medium">
        Pantau prestasi & pelanggaran siswa secara <span class="text-blue-600 font-semibold">real-time</span>, transparan, dan mudah digunakan.
      </p>
      <div class="flex flex-col sm:flex-row justify-center gap-4">
        <a href="login.php" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-400 text-white rounded-full font-semibold shadow-xl hover:scale-105 hover:shadow-2xl transition duration-300 flex items-center justify-center gap-2">
          <i class="fas fa-sign-in-alt"></i> Masuk
        </a>
        <a href="#fitur" class="px-8 py-3 bg-white/80 text-blue-700 rounded-full font-semibold shadow hover:bg-blue-50 transition duration-300 border border-blue-200 flex items-center justify-center gap-2">
          <i class="fas fa-list"></i> Lihat Fitur
        </a>
      </div>
    </div>
  </section>

  <!-- Fitur Section -->
  <section id="fitur" class="py-20 px-4 bg-white/80 glass">
    <div class="max-w-6xl mx-auto">
      <h2 class="text-3xl font-extrabold text-center text-blue-800 mb-14">Fitur Utama</h2>
      <div class="grid md:grid-cols-3 gap-10">
        <!-- Fitur 1 -->
        <div class="p-8 bg-blue-50 rounded-2xl shadow-lg hover:scale-105 hover:shadow-xl transition transform duration-300">
          <div class="w-14 h-14 bg-blue-600 text-white flex items-center justify-center rounded-full mb-5 text-2xl shadow">
            <i class="fas fa-chart-line"></i>
          </div>
          <h3 class="text-xl font-bold mb-3 text-blue-700">Rekap Poin Prestasi</h3>
          <p class="text-gray-700 text-base">Catat dan kelola prestasi siswa secara digital untuk mendukung motivasi belajar.</p>
        </div>
        <!-- Fitur 2 -->
        <div class="p-8 bg-red-50 rounded-2xl shadow-lg hover:scale-105 hover:shadow-xl transition transform duration-300">
          <div class="w-14 h-14 bg-red-600 text-white flex items-center justify-center rounded-full mb-5 text-2xl shadow">
            <i class="fas fa-exclamation-triangle"></i>
          </div>
          <h3 class="text-xl font-bold mb-3 text-red-700">Pencatatan Pelanggaran</h3>
          <p class="text-gray-700 text-base">Memudahkan guru dalam mencatat pelanggaran siswa dan mengontrol disiplin sekolah.</p>
        </div>
        <!-- Fitur 3 -->
        <div class="p-8 bg-green-50 rounded-2xl shadow-lg hover:scale-105 hover:shadow-xl transition transform duration-300">
          <div class="w-14 h-14 bg-green-600 text-white flex items-center justify-center rounded-full mb-5 text-2xl shadow">
            <i class="fas fa-file-alt"></i>
          </div>
          <h3 class="text-xl font-bold mb-3 text-green-700">Laporan & Analisis</h3>
          <p class="text-gray-700 text-base">Menyediakan laporan terperinci untuk membantu evaluasi dan pengambilan keputusan.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Tentang Section -->
  <section id="tentang" class="py-20 px-4">
    <div class="max-w-4xl mx-auto text-center glass rounded-3xl p-12 shadow-2xl border border-blue-100">
      <div class="flex justify-center mb-6">
        <span class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-blue-300 text-white text-4xl shadow-lg border-4 border-white">
          <i class="fas fa-school"></i>
        </span>
      </div>
      <h2 class="text-4xl font-extrabold text-blue-800 mb-6 drop-shadow">Tentang Aplikasi</h2>
      <p class="text-gray-700 text-lg mb-6 leading-relaxed">
        Aplikasi ini dirancang khusus untuk <span class="font-semibold text-blue-700">SMA Negeri 2 Banjar</span> guna mempermudah pengelolaan data prestasi dan pelanggaran siswa.
        Sistem ini memungkinkan pemantauan perkembangan siswa secara <span class="text-blue-600 font-semibold">real-time</span>, serta memberikan transparansi bagi siswa untuk mengetahui poin prestasi maupun pelanggaran mereka.
      </p>
      <div class="flex flex-wrap justify-center gap-4 mt-8">
        <div class="flex items-center gap-2 px-5 py-2 bg-blue-50 rounded-full text-blue-700 font-semibold shadow hover:bg-blue-100 transition">
          <i class="fas fa-lock text-xl"></i> Data Aman
        </div>
        <div class="flex items-center gap-2 px-5 py-2 bg-green-50 rounded-full text-green-700 font-semibold shadow hover:bg-green-100 transition">
          <i class="fas fa-bolt text-xl"></i> Real-Time
        </div>
        <div class="flex items-center gap-2 px-5 py-2 bg-yellow-50 rounded-full text-yellow-700 font-semibold shadow hover:bg-yellow-100 transition">
          <i class="fas fa-chalkboard-teacher text-xl"></i> Mudah Digunakan
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gradient-to-r from-blue-100 via-white to-blue-100 glass border-t py-10 text-center text-base text-gray-700 shadow-inner">
    <div class="max-w-4xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6 px-4">
      <div class="flex items-center gap-3 mb-4 md:mb-0">
        <img src="assets/logo.png" alt="Logo" class="w-10 h-auto" />
      </div>
      <div class="flex gap-6 text-blue-600 text-lg mb-4 md:mb-0">
        <a href="https://instagram.com/" target="_blank" class="hover:text-blue-800 transition duration-300" title="Instagram">
          <i class="fab fa-instagram"></i>
        </a>
        <a href="mailto:info@sman2banjar.sch.id" class="hover:text-blue-800 transition duration-300" title="Email">
          <i class="fas fa-envelope"></i>
        </a>
      </div>
      <div class="text-sm text-gray-500 mt-2 md:mt-0">
        &copy; 2025 <span class="font-semibold text-blue-700">SMA Negeri 2 Banjar</span>. Semua Hak Dilindungi.
        <br class="block md:hidden" />
        <span class="block mt-2 text-blue-700 text-xs md:inline md:ml-2">
          Dibangun dengan <span class="text-red-500">♥</span> untuk Kesiswaan
        </span>
      </div>
    </div>
  </footer>

</body>
</html>
