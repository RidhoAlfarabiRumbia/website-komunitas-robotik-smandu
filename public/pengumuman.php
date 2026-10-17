<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';

// ---- Mode kelola admin: tambah / ubah / hapus pengumuman langsung di halaman ini ----
$crudEntitas = 'pengumuman';
$crudItemEntity = 'pengumuman';                    // tombol Ubah/Hapus di tiap baris
$crudQs = '?manage=pengumuman';
$crudAktif = Auth::isAdmin() && (($_GET['manage'] ?? $_POST['manage'] ?? '') === $crudEntitas);
$crudForm = false;
if ($crudAktif) {
    $crudPath = 'pengumuman.php';
    $crudParam = 'manage';
    include __DIR__ . '/../app/includes/crud_handle.php';
    $crudForm = $showForm;
}

$pengumuman = Pengumuman::all('tanggal DESC');
$pageTitle = 'Pengumuman';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="page-head">
    <div><div class="eyebrow">INFORMASI</div><h1>Pengumuman</h1><p class="desc">Informasi penting seputar pendaftaran, jadwal, deadline, dan kegiatan komunitas.</p></div>
    <?php if (Auth::isAdmin() && !$crudForm): ?>
      <a class="btn btn-primary" href="?manage=pengumuman&amp;action=create"><i class="bi bi-plus-circle"></i> Tambah Pengumuman</a>
    <?php endif; ?>
  </div>
  <?php if ($crudAktif && $msg = flash_get()): ?><div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div><?php endif; ?>
  <?php if ($crudForm): ?>
    <div class="section-head" style="justify-content:flex-start;"><h2><?= $editing ? 'Ubah' : 'Tambah' ?> Pengumuman</h2></div>
    <?php include __DIR__ . '/../app/includes/crud_form.php'; ?>
    <div style="margin:30px 0; border-top:1px solid var(--border);"></div>
  <?php endif; ?>
  <?php if ($pengumuman): ?>
    <div class="table-wrap">
      <?php foreach ($pengumuman as $p): ?>
      <div class="list-item list-item-pad">
        <div class="list-thumb"><i class="bi bi-megaphone" style="font-size:18px; color:var(--blue);"></i></div>
        <div class="grow">
          <div class="ttl"><?= e($p['judul']) ?></div>
          <div class="meta"><?= tanggal_indo($p['tanggal']) ?></div>
          <div style="font-size:13px; color:var(--text-muted); margin-top:6px;"><?= e($p['isi']) ?></div>
        </div>
        <?php if ($crudItemEntity === 'pengumuman'): $itemId = (int) $p['id']; include __DIR__ . '/../app/includes/partial_admin_actions.php'; endif; ?>
      </div>
    <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty">Belum ada pengumuman.</div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>
