<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';

// ---- Mode kelola admin: tambah / ubah / hapus karya langsung di halaman ini ----
$crudEntitas = 'karya';
$crudItemEntity = 'karya';                        // tombol Ubah/Hapus di tiap kartu
$crudQs = '?manage=karya';
$crudAktif = Auth::isAdmin() && (($_GET['manage'] ?? $_POST['manage'] ?? '') === $crudEntitas);
$crudForm = false;
if ($crudAktif) {
    $crudPath = 'karya.php';
    $crudParam = 'manage';
    include __DIR__ . '/../app/includes/crud_handle.php';
    $crudForm = $showForm;
}

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
    <?php if (Auth::isAdmin() && !$crudForm): ?>
      <a class="btn btn-primary" href="?manage=karya&amp;action=create"><i class="bi bi-plus-circle"></i> Tambah Karya</a>
    <?php endif; ?>
  </div>
  <?php if ($crudAktif && $msg = flash_get()): ?><div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div><?php endif; ?>
  <?php if ($crudForm): ?>
    <div class="section-head" style="justify-content:flex-start;"><h2><?= $editing ? 'Ubah' : 'Tambah' ?> Karya</h2></div>
    <?php include __DIR__ . '/../app/includes/crud_form.php'; ?>
    <div style="margin:30px 0; border-top:1px solid var(--border);"></div>
  <?php endif; ?>
  <?php if ($karya): ?>
    <div class="grid grid-3 grid-stagger reveal">
      <?php foreach ($karya as $k): ?><?php include __DIR__ . '/../app/includes/partial_karya_card.php'; ?><?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty">Belum ada karya yang disetujui.</div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>
