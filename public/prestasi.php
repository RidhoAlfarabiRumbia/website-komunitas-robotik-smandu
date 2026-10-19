<?php
/**
 * Prestasi / Pencapaian (PRD 8.7).
 * Berbeda dengan Karya: prestasi tayang langsung tanpa persetujuan admin
 * (pasca-moderasi). Admin dapat menyembunyikan bila ditemukan tidak valid.
 * Halaman ini terbuka untuk umum.
 */
require_once __DIR__ . '/../app/includes/bootstrap.php';

// ---- Mode kelola admin: tambah / ubah / hapus prestasi langsung di halaman ini ----
$crudEntitas = 'prestasi';
$crudItemEntity = 'prestasi';                      // tombol Ubah/Hapus di tiap kartu
$crudQs = '?manage=prestasi';
$crudAktif = Auth::isAdmin() && (($_GET['manage'] ?? $_POST['manage'] ?? '') === $crudEntitas);
$crudForm = false;
if ($crudAktif) {
    $crudPath = 'prestasi.php';
    $crudParam = 'manage';
    include __DIR__ . '/../app/includes/crud_handle.php';
    $crudForm = $showForm;
}

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
    <?php if (Auth::isAdmin() && !$crudForm): ?>
      <a class="btn btn-primary" href="?manage=prestasi&amp;action=create"><i class="bi bi-plus-circle"></i> Tambah Prestasi</a>
    <?php endif; ?>
  </div>

  <?php if ($crudAktif && $msg = flash_get()): ?><div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div><?php endif; ?>
  <?php if ($crudForm): ?>
    <div class="section-head" style="justify-content:flex-start;"><h2><?= $editing ? 'Ubah' : 'Tambah' ?> Prestasi</h2></div>
    <?php include __DIR__ . '/../app/includes/crud_form.php'; ?>
    <div style="margin:30px 0; border-top:1px solid var(--border);"></div>
  <?php endif; ?>

  <?php if ($daftar): ?>
    <div class="grid grid-3 grid-stagger reveal">
      <?php foreach ($daftar as $p):
        $tim = Prestasi::tim((int) $p['id']);
      ?>
        <div class="card<?= Auth::isAdmin() && ($crudItemEntity ?? '') === 'prestasi' ? ' card-admin' : '' ?>">
          <?php if ($crudItemEntity === 'prestasi'): $itemId = (int) $p['id']; include __DIR__ . '/../app/includes/partial_admin_actions.php'; endif; ?>
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