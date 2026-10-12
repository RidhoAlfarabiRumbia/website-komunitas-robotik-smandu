<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';

$galeri = Galeri::all('tanggal DESC');
$pageTitle = 'Galeri';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="page-head">
    <div><div class="eyebrow">BERBAGI</div><h1>Galeri Komunitas</h1><p class="desc">Dokumentasi perjalanan komunitas — latihan, workshop, lomba, prestasi, hingga proses pembuatan karya.</p></div>
  </div>
  <?php if ($galeri): ?>
    <div class="grid grid-3 grid-stagger reveal">
      <?php foreach ($galeri as $g): ?><?php include __DIR__ . '/../app/includes/partial_galeri_card.php'; ?><?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty">Belum ada dokumentasi galeri.</div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>
