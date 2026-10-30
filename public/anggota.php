<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';

// ---- Mode kelola admin: tambah / ubah / hapus anggota langsung di halaman ini ----
$crudEntitas = 'anggota';
$crudItemEntity = 'anggota';                       // tombol Ubah/Hapus di tiap kartu
$crudQs = '?manage=anggota';
$crudAktif = Auth::isAdmin() && (($_GET['manage'] ?? $_POST['manage'] ?? '') === $crudEntitas);
$crudForm = false;
if ($crudAktif) {
    $crudPath = 'anggota.php';
    $crudParam = 'manage';
    include __DIR__ . '/../app/includes/crud_handle.php';
    $crudForm = $showForm;
}

$daftarAnggota = Anggota::all('nama ASC');
$pageTitle = 'Anggota';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="page-head">
    <div><div class="eyebrow">KOMUNITAS</div><h1>Anggota Komunitas</h1><p class="desc">Kenali anggota komunitas beserta kontribusinya.</p></div>
    <?php if (Auth::isAdmin() && !$crudForm): ?>
      <a class="btn btn-primary" href="?manage=anggota&amp;action=create"><i class="bi bi-plus-circle"></i> Tambah Anggota</a>
    <?php endif; ?>
  </div>
  <?php if ($crudAktif && $msg = flash_get()): ?><div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div><?php endif; ?>
  <?php if ($crudForm): ?>
    <div class="section-head" style="justify-content:flex-start;"><h2><?= $editing ? 'Ubah' : 'Tambah' ?> Anggota</h2></div>
    <?php include __DIR__ . '/../app/includes/crud_form.php'; ?>
    <div style="margin:30px 0; border-top:1px solid var(--border);"></div>
  <?php endif; ?>
  <?php if ($daftarAnggota): ?>
    <div class="grid grid-3 grid-stagger reveal">
      <?php foreach ($daftarAnggota as $a): ?>
      <div class="card-wrap<?= Auth::isAdmin() && ($crudItemEntity ?? '') === 'anggota' ? ' card-wrap-admin' : '' ?>">
      <a href="anggota-detail.php?id=<?= $a['id'] ?>" class="card" style="display:flex; gap:14px; align-items:center;">
        <div class="avatar" style="width:46px; height:46px;"><img src="<?= base_url(e($a['foto'] ?? 'assets/img/avatar-1.svg')) ?>" alt="" loading="lazy" decoding="async"></div>
        <div><div class="ttl"><?= e($a['nama']) ?></div><div class="meta"><?= e($a['kelas']) ?></div></div>
      </a>
      <?php if ($crudItemEntity === 'anggota'): $itemId = (int) $a['id']; include __DIR__ . '/../app/includes/partial_admin_actions.php'; endif; ?>
      </div>
    <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty">Belum ada anggota terdaftar.</div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>
