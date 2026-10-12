<?php
/**
 * Prestasi / Pencapaian (PRD 8.7).
 * Berbeda dengan Karya: prestasi tayang langsung tanpa persetujuan admin
 * (pasca-moderasi). Admin dapat menyembunyikan bila ditemukan tidak valid.
 * Halaman ini terbuka untuk umum.
 */
require_once __DIR__ . '/../app/includes/bootstrap.php';

$daftar = Prestasi::tayang();

$pageTitle = 'Prestasi';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="page-head">
    <div>
      <div class="eyebrow">PENCAPAIAN</div>
      <h1>Prestasi Anggota</h1>
      <p class="desc">Juara, podium, dan penghargaan yang dicapai anggota portal robotik.</p>
    </div>
    <?php if (Auth::role() === 'anggota'): ?>
      <a class="btn btn-primary" href="ajukan-prestasi.php"><i class="bi bi-trophy"></i> Ajukan Prestasi</a>
    <?php endif; ?>
  </div>

  <?php if ($daftar): ?>
    <div class="grid grid-3 grid-stagger reveal">
      <?php foreach ($daftar as $p):
        $tim = Prestasi::tim((int) $p['id']);
      ?>
        <div class="card">
          <?php if ($p['foto']): ?>
            <img src="<?= base_url(e($p['foto'])) ?>" alt="<?= e($p['nama']) ?>" loading="lazy" decoding="async" style="width:100%; height:170px; object-fit:cover; border-radius:var(--radius-md); margin-bottom:14px;">
          <?php else: ?>
            <div class="card-img-fallback" style="height:170px; margin-bottom:14px;">
              <i class="bi bi-trophy"></i>
            </div>
          <?php endif; ?>

          <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:10px;">
            <?php if ($p['hasil']): ?>
              <span class="badge badge-amber"><i class="bi bi-award"></i> <?= e($p['hasil']) ?></span>
            <?php endif; ?>
            <span class="badge badge-blue"><?= e($p['tingkat']) ?></span>
          </div>

          <h3 style="font-size:15.5px; margin-bottom:8px;"><?= e($p['nama']) ?></h3>
          <p style="font-size:13px; color:var(--text-muted); margin-bottom:12px;">
            <?= e($p['lomba_nama'] ?? 'Tanpa nama lomba') ?>
          </p>
          <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:14px;"><?= e(tanggal_indo($p['tanggal'])) ?></p>

          <?php if ($tim): ?>
            <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:14px;">
              <?php foreach (array_slice($tim, 0, 3) as $t): ?>
                <span class="badge badge-muted"><?= e(trunc($t['nama'], 16)) ?></span>
              <?php endforeach; ?>
              <?php if (count($tim) > 3): ?>
                <span class="badge badge-muted">+<?= count($tim) - 3 ?></span>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <a class="btn btn-outline btn-sm btn-block" href="prestasi-detail.php?id=<?= (int) $p['id'] ?>">Lihat Detail</a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty">Belum ada prestasi yang tercatat.</div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>