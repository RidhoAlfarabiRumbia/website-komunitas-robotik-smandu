<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';

// ---- Mode kelola admin: tambah / ubah / hapus lomba langsung di halaman ini ----
$crudEntitas = 'lomba';
$crudItemEntity = 'lomba';                         // tombol Ubah/Hapus di tiap kartu
$crudQs = '?manage=lomba';
$crudAktif = Auth::isAdmin() && (($_GET['manage'] ?? $_POST['manage'] ?? '') === $crudEntitas);
$crudForm = false;
if ($crudAktif) {
    $crudPath = 'lomba.php';
    $crudParam = 'manage';
    include __DIR__ . '/../app/includes/crud_handle.php';
    $crudForm = $showForm;
}

$lomba = Lomba::all('tanggal DESC');
$pageTitle = 'Lomba';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="page-head">
    <div><div class="eyebrow">BERKOMPETISI</div><h1>Lomba &amp; Kompetisi</h1><p class="desc">Rekam jejak dan informasi kompetisi yang diikuti maupun menjadi target komunitas.</p></div>
    <?php if (Auth::isAdmin() && !$crudForm): ?>
      <a class="btn btn-primary" href="?manage=lomba&amp;action=create"><i class="bi bi-plus-circle"></i> Tambah Lomba</a>
    <?php endif; ?>
  </div>
  <?php if ($crudAktif && $msg = flash_get()): ?><div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div><?php endif; ?>
  <?php if ($crudForm): ?>
    <div class="section-head" style="justify-content:flex-start;"><h2><?= $editing ? 'Ubah' : 'Tambah' ?> Lomba</h2></div>
    <?php include __DIR__ . '/../app/includes/crud_form.php'; ?>
    <div style="margin:30px 0; border-top:1px solid var(--border);"></div>
  <?php endif; ?>
  <?php if ($lomba): ?>
    <div class="grid grid-2 grid-stagger reveal">
      <?php foreach ($lomba as $l): $peserta = Lomba::peserta($l['id']); ?>
      <div class="card<?= Auth::isAdmin() && ($crudItemEntity ?? '') === 'lomba' ? ' card-admin' : '' ?>">
        <?php if ($crudItemEntity === 'lomba'): $itemId = (int) $l['id']; include __DIR__ . '/../app/includes/partial_admin_actions.php'; endif; ?>
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
          <span class="badge <?= $l['status'] === 'Selesai' ? 'badge-muted' : ($l['status'] === 'Akan Datang' ? 'badge-blue' : 'badge-teal') ?>"><?= e($l['status']) ?></span>
          <?php if ($l['hasil']): ?><span class="badge badge-amber"><i class="bi bi-trophy"></i> <?= e($l['hasil']) ?></span><?php endif; ?>
        </div>
        <h3 style="font-size:15px; margin-bottom:6px;"><?= e($l['nama']) ?></h3>
        <div class="side-info-row"><span class="k">Penyelenggara</span><span><?= e($l['penyelenggara']) ?></span></div>
        <div class="side-info-row"><span class="k">Tanggal</span><span><?= tanggal_indo($l['tanggal']) ?></span></div>
        <div class="side-info-row"><span class="k">Lokasi</span><span><?= e($l['lokasi']) ?></span></div>
        <?php if ($peserta): ?>
          <div class="side-info-row" style="border:none;"><span class="k">Peserta</span><span><?= e(implode(', ', array_column($peserta, 'nama'))) ?></span></div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty">Belum ada lomba tercatat.</div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>
