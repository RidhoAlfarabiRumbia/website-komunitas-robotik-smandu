<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';
$daftarAnggota = Anggota::all('nama ASC');
$pageTitle = 'Anggota';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="page-head">
    <div><div class="eyebrow">KOMUNITAS</div><h1>Anggota Komunitas</h1><p class="desc">Kenali anggota komunitas beserta fokus dan kontribusinya.</p></div>
  </div>
  <?php if ($daftarAnggota): ?>
    <div class="grid grid-3 grid-stagger reveal">
      <?php foreach ($daftarAnggota as $a): ?>
      <a href="anggota-detail.php?id=<?= $a['id'] ?>" class="card" style="display:flex; gap:14px; align-items:center;">
        <div class="avatar" style="width:46px; height:46px;"><img src="<?= base_url(e($a['foto'] ?? 'assets/img/avatar-1.svg')) ?>" alt="" loading="lazy" decoding="async"></div>
        <div><div class="ttl"><?= e($a['nama']) ?></div><div class="meta"><?= e($a['kelas']) ?></div></div>
      </a>
    <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty">Belum ada anggota terdaftar.</div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>
