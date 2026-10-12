<a href="karya-detail.php?id=<?= $k['id'] ?>" class="work-card">
  <div class="work-thumb"><img src="<?= base_url(e($k['foto'] ?? 'assets/img/karya-robot.svg')) ?>" alt="<?= e($k['judul']) ?>" loading="lazy" decoding="async"></div>
  <div class="work-body">
    <div class="work-meta"><?= badge_progres($k['progres']) ?></div>
    <div class="work-title"><?= e($k['judul']) ?></div>
    <div class="work-desc"><?= e(trunc($k['deskripsi'], 80)) ?></div>
    <div class="work-tags">
      <?php foreach (array_slice(array_map('trim', explode(',', $k['teknologi'] ?? '')), 0, 2) as $t): if ($t !== ''): ?>
        <span class="tag"><?= e($t) ?></span>
      <?php endif; endforeach; ?>
    </div>
    <div class="work-foot"><span class="work-meta"><i class="bi bi-person"></i> <?= e($k['pembuat_nama'] ?? '') ?></span></div>
  </div>
</a>
