<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';
Auth::requireMember();

$lomba = Lomba::all('tanggal DESC');
$pageTitle = 'Lomba';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="page-head">
    <div><div class="eyebrow">BERKOMPETISI</div><h1>Lomba &amp; Kompetisi</h1><p class="desc">Rekam jejak dan informasi kompetisi yang diikuti maupun menjadi target komunitas.</p></div>
  </div>
  <?php if ($lomba): ?>
    <div class="grid grid-2 grid-stagger reveal">
      <?php foreach ($lomba as $l): $peserta = Lomba::peserta($l['id']); ?>
      <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
          <span class="badge <?= $l['status'] === 'Selesai' ? 'badge-muted' : ($l['status'] === 'Akan Datang' ? 'badge-blue' : 'badge-teal') ?>"><?= e($l['status']) ?></span>
          <?php if ($l['hasil']): ?><span class="badge badge-amber"><i class="bi bi-trophy"></i> <?= e($l['hasil']) ?></span><?php endif; ?>
        </div>
        <h3 style="font-size:15px; margin-bottom:6px;"><?= e($l['nama']) ?></h3>
        <div class="side-info-row"><span class="k">Penyelenggara</span><span><?= e($l['penyelenggara']) ?></span></div>
        <div class="side-info-row"><span class="k">Tanggal</span><span><?= tanggal_indo($l['tanggal']) ?></span></div>
        <div class="side-info-row"><span class="k">Lokasi</span><span><?= e($l['lokasi']) ?></span></div>
        <div class="side-info-row"><span class="k">Kategori</span><span><?= e($l['kategori']) ?></span></div>
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
