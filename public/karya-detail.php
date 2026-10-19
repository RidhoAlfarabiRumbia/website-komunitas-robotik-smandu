<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';

$id = nilai_int($_GET, 'id');
$k = Karya::detail($id);
// Karya yang belum tayang hanya boleh dilihat admin atau pembuatnya sendiri
// (Karya::bolehDilihat); pengunjung umum dan anggota lain hanya Approved.
if (!Karya::bolehDilihat($k, Auth::anggotaId(), Auth::isAdmin())) {
    $k = null;
}
$pageTitle = 'Detail Karya';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
<?php if (!$k): ?>
  <div class="empty"><i class="bi bi-exclamation-circle" style="font-size:24px;"></i><p>Karya tidak ditemukan.</p></div>
<?php else:
  $teknologi = array_filter(array_map('trim', explode(',', $k['teknologi'] ?? '')));
?>
  <div class="breadcrumb"><a href="karya.php">Karya</a> / <?= e($k['judul']) ?></div>
  <div class="detail-hero"><span data-lightbox="<?= base_url(e($k['foto'] ?? 'assets/img/karya-robot.svg')) ?>" tabindex="0" role="button" aria-label="Perbesar foto karya"><img src="<?= base_url(e($k['foto'] ?? 'assets/img/karya-robot.svg')) ?>" alt="<?= e($k['judul']) ?>" fetchpriority="high" decoding="async"></span></div>
  <div class="two-col">
    <div>
      <div style="display:flex; gap:8px; margin-bottom:10px;"><?= badge_progres($k['progres']) ?><?= badge_publikasi($k['publikasi']) ?></div>
      <h1 style="font-size:24px; margin-bottom:10px;"><?= e($k['judul']) ?></h1>
      <p style="color:var(--text-muted); line-height:1.7; margin-bottom:18px;"><?= e($k['deskripsi']) ?></p>
      <?php if ($teknologi): ?>
        <h3 style="font-size:14px; margin-bottom:8px;">Teknologi yang digunakan</h3>
        <div class="work-tags" style="margin-bottom:20px;">
          <?php foreach ($teknologi as $t): ?><span class="tag"><?= e($t) ?></span><?php endforeach; ?>
        </div>
      <?php endif; ?>
      <?php // Hanya tautan http/https yang boleh diklik (anti javascript: pada data lama).
      if (!empty($k['link_demo']) && preg_match('#^https?://#i', (string) $k['link_demo'])): ?>
        <h3 style="font-size:14px; margin-bottom:8px;">Tautan proyek</h3>
        <a class="tag" style="display:inline-flex; align-items:center; gap:6px;" href="<?= e($k['link_demo']) ?>" target="_blank" rel="noopener"><?= e($k['link_demo']) ?> <i class="bi bi-box-arrow-up-right"></i></a>
      <?php endif; ?>
    </div>
    <div>
      <div class="card">
        <div class="side-info-row"><span class="k">Pembuat</span><a href="anggota-detail.php?id=<?= $k['pembuat_id'] ?>"><?= e($k['pembuat_nama']) ?></a></div>
        <div class="side-info-row"><span class="k">Kategori</span><span><?= e($k['kategori']) ?></span></div>
        <div class="side-info-row"><span class="k">Diajukan</span><span><?= tanggal_indo($k['dibuat_pada']) ?></span></div>
      </div>
    </div>
  </div>
<?php endif; ?>
</div>
<?php include __DIR__ . '/../app/includes/partial_lightbox.php'; ?>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>
