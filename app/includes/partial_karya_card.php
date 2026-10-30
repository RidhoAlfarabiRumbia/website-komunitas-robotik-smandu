<?php
// Kartu karya. Dimuat di dalam loop yang menamai variabel $k.
// Tombol Ubah/Hapus hanya muncul untuk admin, dan hanya pada halaman yang
// memang mengelola karya di sana ($crudItemEntity = 'karya').
$kartuDikelola = Auth::isAdmin() && ($crudItemEntity ?? '') === 'karya';
?>
<div class="card-wrap">
<a href="karya-detail.php?id=<?= $k['id'] ?>" class="work-card">
  <span class="work-thumb" data-lightbox="<?= base_url(e($k['foto'] ?? 'assets/img/karya-robot.svg')) ?>" tabindex="0" role="button" aria-label="Perbesar foto karya: <?= e($k['judul']) ?>"><img src="<?= base_url(e($k['foto'] ?? 'assets/img/karya-robot.svg')) ?>" alt="<?= e($k['judul']) ?>" loading="lazy" decoding="async"></span>
  <div class="work-body">
    <div class="work-meta"><?= badge_progres($k['progres']) ?></div>
    <div class="work-title"><?= e($k['judul']) ?></div>
    <div class="work-desc"><?= e(trunc($k['deskripsi'], 80)) ?></div>
    <div class="work-tags">
      <?php foreach (array_slice(array_map('trim', explode(',', $k['teknologi'] ?? '')), 0, 2) as $t): if ($t !== ''): ?>
        <span class="tag"><?= e($t) ?></span>
      <?php endif; endforeach; ?>
    </div>
    <div class="work-foot"><span class="work-meta"><i class="bi bi-person"></i> <span class="work-author"><?= e($k['pembuat_nama'] ?? '') ?></span></span></div>
  </div>
</a>
<?php if ($kartuDikelola): $itemId = (int) $k['id']; include __DIR__ . '/partial_admin_actions.php'; endif; ?>
</div>