<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';
$karya = Karya::approved();
$pageTitle = 'Karya';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="page-head">
    <div><div class="eyebrow">BERKARYA</div><h1>Karya Anggota</h1><p class="desc">Karya yang telah disetujui dan ditampilkan di portal — mulai dari robot, IoT, hingga AI.</p></div>
    <?php if (Auth::check() && Auth::role() === 'anggota'): ?>
      <a class="btn btn-primary" href="ajukan-karya.php"><i class="bi bi-plus-circle"></i> Ajukan Karya</a>
    <?php elseif (!Auth::check()): ?>
      <a class="btn btn-outline" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Masuk untuk Berkarya</a>
    <?php endif; ?>
  </div>
  <?php if ($karya): ?>
    <div class="grid grid-3 grid-stagger reveal">
      <?php foreach ($karya as $k): ?><?php include __DIR__ . '/../app/includes/partial_karya_card.php'; ?><?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty">Belum ada karya yang disetujui.</div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>
