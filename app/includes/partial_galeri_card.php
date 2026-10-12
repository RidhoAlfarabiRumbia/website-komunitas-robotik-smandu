<div class="work-card">
  <div class="work-thumb"><img src="<?= base_url(e($g['foto'] ?? 'assets/img/galeri-latihan.svg')) ?>" alt="<?= e($g['judul']) ?>" loading="lazy" decoding="async"></div>
  <div class="work-body">
    <span class="badge badge-blue"><?= e($g['kategori']) ?></span>
    <div class="work-title" style="font-size:14px;"><?= e($g['judul']) ?></div>
    <div class="work-desc"><?= e($g['deskripsi']) ?></div>
    <div class="work-meta"><?= tanggal_indo($g['tanggal']) ?></div>
  </div>
</div>
