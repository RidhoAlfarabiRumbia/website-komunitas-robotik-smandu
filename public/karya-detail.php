<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';

$id = nilai_int($_GET, 'id');
$k = Karya::detail($id);
// Karya yang belum tayang hanya boleh dilihat admin atau pembuatnya sendiri
// (Karya::bolehDilihat); pengunjung umum dan anggota lain hanya Disetujui.
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
  <div class="karya-detail">
    <div class="karya-detail-media">
      <span data-lightbox="<?= base_url(e($k['foto'] ?? 'assets/img/karya-robot.svg')) ?>" tabindex="0" role="button" aria-label="Perbesar foto karya"><img src="<?= base_url(e($k['foto'] ?? 'assets/img/karya-robot.svg')) ?>" alt="<?= e($k['judul']) ?>" fetchpriority="high" decoding="async"></span>
    </div>
    <div class="karya-detail-card">
      <h1><?= e($k['judul']) ?></h1>
      <div class="karya-detail-author">
        <?php
        $pembuatList = Karya::pembuat((int) $k['id']);
        if (!$pembuatList) {
            $pembuatList = [['id' => (int) $k['pembuat_id'], 'nama' => $k['pembuat_nama'], 'kelas' => $k['pembuat_kelas'] ?? '']];
        }
        foreach ($pembuatList as $i => $p): ?>
          <?= $i > 0 ? ', ' : '' ?><a href="anggota-detail.php?id=<?= (int) $p['id'] ?>"><?= e($p['nama']) ?></a><?php if (!empty($p['kelas'])): ?><span class="kelas"> · <?= e($p['kelas']) ?></span><?php endif; ?>
        <?php endforeach; ?>
      </div>
      <p class="karya-detail-desc"><?= e($k['deskripsi']) ?></p>
      <?php if ($teknologi): ?>
        <div class="work-tags" style="margin-bottom:16px;">
          <?php foreach ($teknologi as $t): ?><span class="tag"><?= e($t) ?></span><?php endforeach; ?>
        </div>
      <?php endif; ?>
      <?php // Hanya tautan http/https yang boleh diklik (anti javascript: pada data lama).
      if (!empty($k['link_demo']) && preg_match('#^https?://#i', (string) $k['link_demo'])): ?>
        <a class="tag" style="display:inline-flex; align-items:center; gap:6px; margin-bottom:16px;" href="<?= e($k['link_demo']) ?>" target="_blank" rel="noopener"><?= e($k['link_demo']) ?> <i class="bi bi-box-arrow-up-right"></i></a>
      <?php endif; ?>
      <div class="karya-detail-divider"></div>
      <div class="karya-detail-rows">
        <div class="karya-detail-row"><span class="k">Dibuat</span><span class="v"><?= tanggal_indo($k['dibuat_pada']) ?></span></div>
        <div class="karya-detail-row"><span class="k">Status</span><span class="v"><?= e($k['publikasi']) ?></span></div>
        <div class="karya-detail-row"><span class="k">Progres</span><span class="v"><?= e($k['progres']) ?></span></div>
      </div>
    </div>
  </div>
<?php endif; ?>
</div>
<?php include __DIR__ . '/../app/includes/partial_lightbox.php'; ?>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>
