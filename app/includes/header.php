<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Portal komunitas robotik SMA Negeri 2 — materi, karya, galeri, kegiatan, lomba, dan pengumuman.">
<meta name="theme-color" content="#1668b3">
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='13' fill='%231668b3'/%3E%3Crect x='18' y='18' width='28' height='28' rx='6' fill='none' stroke='white' stroke-width='5'/%3E%3Ccircle cx='32' cy='32' r='4' fill='white'/%3E%3C/svg%3E">
<title><?= isset($pageTitle) ? e($pageTitle) . ' · ' : '' ?>Portal Komunitas Robotik</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"></noscript>
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>?v=<?= filemtime(__DIR__ . '/../../public/assets/css/style.css') ?>">
<script>
(function(){
  var t=localStorage.getItem('theme');
  if(t==='dark'||(!t&&matchMedia('(prefers-color-scheme:dark)').matches)){
    document.documentElement.classList.add('dark');
  }
})();
</script>
<noscript>
  <!-- Tanpa JavaScript, elemen .reveal (opacity:0) tidak akan pernah terlihat.
       Paksa tampil agar konten halaman tetap terbaca. -->
  <style>.reveal{opacity:1 !important;transform:none !important;}</style>
</noscript>
</head>
<body>
