<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';
$kegiatan = Kegiatan::all('tanggal DESC');
$pageTitle = 'Kegiatan';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="page-head">
    <div><div class="eyebrow">AKTIVITAS</div><h1>Kegiatan Komunitas</h1><p class="desc">Jadwal latihan, workshop, rapat, dan kegiatan persiapan lomba.</p></div>
  </div>
  <?php if ($kegiatan): ?>
    <div class="table-wrap">
      <?php foreach ($kegiatan as $k): ?>
      <div class="list-item list-item-pad">
        <div class="list-thumb"><i class="bi bi-calendar-event" style="font-size:20px; color:var(--teal);"></i></div>
        <div class="grow">
          <div class="ttl"><?= e($k['nama']) ?></div>
          <div class="meta"><?= tanggal_indo($k['tanggal']) ?> · <?= e($k['waktu']) ?> · <?= e($k['lokasi']) ?></div>
          <div style="font-size:13px; color:var(--text-muted); margin-top:4px;"><?= e($k['deskripsi']) ?></div>
          <?php if ($k['status'] === 'Selesai'):
            $vid = trim((string) ($k['video'] ?? ''));
            if ($vid !== '' && preg_match('#^https?://#i', $vid)): ?>
            <div class="keg-video">
              <a class="btn btn-outline btn-sm" href="<?= e($vid) ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-play-circle"></i> Tonton Video Laporan
              </a>
            </div>
          <?php
            elseif ($vid !== '' && preg_match('#^uploads/kegiatan/[A-Za-z0-9._-]+\.(mp4|webm|mov|m4v)$#i', $vid)): ?>
            <div class="keg-video">
              <video controls preload="metadata" src="<?= base_url(e($vid)) ?>"></video>
            </div>
          <?php endif; endif; ?>
        </div>
        <span class="badge <?= $k['status'] === 'Selesai' ? 'badge-muted' : 'badge-teal' ?>"><?= e($k['status']) ?></span>
      </div>
    <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty">Belum ada kegiatan terjadwal.</div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>
