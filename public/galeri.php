<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';

// ---- Mode kelola admin: tambah / ubah / hapus galeri langsung di halaman ini ----
$crudEntitas = 'galeri';
$crudItemEntity = 'galeri';                        // tombol Ubah/Hapus di tiap kartu
$crudQs = '?manage=galeri';
$crudAktif = Auth::isAdmin() && (($_GET['manage'] ?? $_POST['manage'] ?? '') === $crudEntitas);
$crudForm = false;
if ($crudAktif) {
    $crudPath = 'galeri.php';
    $crudParam = 'manage';
    include __DIR__ . '/../app/includes/crud_handle.php';
    $crudForm = $showForm;
}

$galeri = Galeri::all('tanggal DESC');
$pageTitle = 'Galeri';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="page-head">
    <div><div class="eyebrow">BERBAGI</div><h1>Galeri Komunitas</h1><p class="desc">Dokumentasi perjalanan komunitas latihan, workshop, lomba, prestasi, hingga proses pembuatan karya.</p></div>
    <?php if (Auth::isAdmin() && !$crudForm): ?>
      <a class="btn btn-primary" href="?manage=galeri&amp;action=create"><i class="bi bi-plus-circle"></i> Tambah Galeri</a>
    <?php endif; ?>
  </div>
  <?php if ($crudAktif && $msg = flash_get()): ?><div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div><?php endif; ?>
  <?php if ($crudForm): ?>
    <div class="section-head" style="justify-content:flex-start;"><h2><?= $editing ? 'Ubah' : 'Tambah' ?> Galeri</h2></div>
    <?php include __DIR__ . '/../app/includes/crud_form.php'; ?>
    <div style="margin:30px 0; border-top:1px solid var(--border);"></div>
  <?php endif; ?>
  <?php if ($galeri): ?>
    <div class="grid grid-3 grid-stagger reveal">
      <?php foreach ($galeri as $g): ?><?php include __DIR__ . '/../app/includes/partial_galeri_card.php'; ?><?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty">Belum ada dokumentasi galeri.</div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../app/includes/partial_lightbox.php'; ?>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>
