<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';

$pengumuman = Pengumuman::all('tanggal DESC');
$pageTitle = 'Pengumuman';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="page-head">
    <div><div class="eyebrow">INFORMASI</div><h1>Pengumuman</h1><p class="desc">Informasi penting seputar pendaftaran, jadwal, deadline, dan kegiatan komunitas.</p></div>
  </div>
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
      </div>
    <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty">Belum ada pengumuman.</div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>
