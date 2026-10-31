<?php
$navItems = [
    'beranda.php' => 'Beranda',
    'komunitas.php' => 'Komunitas',
    'karya.php' => 'Karya',
    'galeri.php' => 'Galeri',
    'pelatihan.php' => 'Pelatihan',
    'lomba.php' => 'Lomba',
    'kegiatan.php' => 'Kegiatan',
    'materi.php' => 'Materi',
    'prestasi.php' => 'Prestasi',
    'pengumuman.php' => 'Pengumuman',
    'anggota.php' => 'Anggota',
];

// Menu ini hanya untuk pengguna terdaftar; pengunjung umum hanya melihat
// Materi yang butuh login — sisanya publik.
// Admin ikut melihat semuanya — PRD 7: "Seluruh menu anggota".
// Referensi kini menempel di dalam Materi.
$anggotaOnly = ['materi.php'];

if (!Auth::check()) {
    foreach ($anggotaOnly as $f) unset($navItems[$f]);
}

$current = basename($_SERVER['SCRIPT_NAME']);
?>
<div class="topbar">
  <div class="topbar-inner">
    <?php if (empty($hideNavMenu)): ?>
    <button type="button" class="nav-toggle" id="navToggle" aria-label="Buka menu navigasi" aria-controls="navMenu" aria-expanded="false"><i class="bi bi-list"></i></button>
    <div class="sidebar-backdrop" id="navBackdrop"></div>
    <?php endif; ?>
    <a href="<?= base_url('beranda.php') ?>" class="brand"><span class="brand-dot"></span><span class="brand-name"><span>Robotik</span><span>Smandu</span></span></a>
    <?php if (empty($hideNavMenu)): ?>
    <nav class="nav-main" id="navMenu">
      <button type="button" class="nav-toggle side-close" id="navClose" aria-label="Tutup menu navigasi"><i class="bi bi-list"></i></button>
      <?php foreach ($navItems as $file => $label): ?>
        <a href="<?= base_url($file) ?>" class="<?= $current === $file ? 'active' : '' ?>" <?= $current === $file ? 'aria-current="page"' : '' ?>><?= $label ?></a>
      <?php endforeach; ?>
    </nav>
    <?php endif; ?>
    <div class="user-chip">
      <button type="button" class="theme-toggle" onclick="toggleTheme()" title="Mode gelap/terang" aria-label="Toggle dark mode">
        <i class="bi bi-moon-stars-fill" id="themeIconDark"></i>
        <i class="bi bi-sun-fill" id="themeIconLight" style="display:none;"></i>
      </button>
      <?php if (!Auth::check()): ?>
        <?php if (empty($hideMasukBtn)): ?>
          <a class="btn btn-primary btn-sm" href="<?= base_url('login.php') ?>"><i class="bi bi-box-arrow-in-right"></i> Masuk</a>
        <?php endif; ?>
      <?php elseif (Auth::role() === 'admin'): ?>
        <a class="btn btn-primary btn-sm" href="<?= base_url('admin/dashboard.php') ?>" title="Panel Admin"><i class="bi bi-speedometer2"></i> Admin</a>
        <?php include __DIR__ . '/partial_profil_menu.php'; ?>
      <?php else: ?>
        <?php include __DIR__ . '/partial_profil_menu.php'; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<script>
(function(){
  var topbar = document.querySelector('.topbar');
  if (!topbar) return;
  function syncTopbar(){
    document.documentElement.style.setProperty('--topbar-h', topbar.getBoundingClientRect().height + 'px');
  }
  syncTopbar();
  if (window.ResizeObserver) {
    new ResizeObserver(syncTopbar).observe(topbar);
  } else {
    window.addEventListener('resize', syncTopbar);
  }
})();
</script>
