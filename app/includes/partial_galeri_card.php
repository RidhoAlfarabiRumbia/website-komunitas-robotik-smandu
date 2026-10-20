<?php
// Kartu galeri. Dimuat di dalam loop yang menamai variabel $g.
// Tombol Ubah/Hapus hanya muncul untuk admin, dan hanya pada halaman yang
// memang mengelola galeri di sana ($crudItemEntity = 'galeri').
$kartuDikelola = Auth::isAdmin() && ($crudItemEntity ?? '') === 'galeri';
?>
<div class="card-wrap">
<div class="work-card">
  <span class="work-thumb" data-lightbox="<?= base_url(e($g['foto'] ?? 'assets/img/galeri-latihan.svg')) ?>" tabindex="0" role="button" aria-label="Perbesar foto galeri: <?= e($g['judul']) ?>"><img src="<?= base_url(e($g['foto'] ?? 'assets/img/galeri-latihan.svg')) ?>" alt="<?= e($g['judul']) ?>" loading="lazy" decoding="async"></span>
  <div class="work-body">
    <span class="badge badge-blue"><?= e($g['kategori']) ?></span>
    <div class="work-title" style="font-size:14px;"><?= e($g['judul']) ?></div>
    <div class="work-desc"><?= e($g['deskripsi']) ?></div>
    <div class="work-meta"><?= tanggal_indo($g['tanggal']) ?></div>
  </div>
</div>
<?php if ($kartuDikelola): $itemId = (int) $g['id']; include __DIR__ . '/partial_admin_actions.php'; endif; ?>
</div>