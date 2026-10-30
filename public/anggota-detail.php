<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';
$id = nilai_int($_GET, 'id');
$a = Anggota::find($id);
$pageTitle = 'Profil Anggota';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
<?php if (!$a): ?>
  <div class="empty">Anggota tidak ditemukan.</div>
<?php else:
  $karyaAnggota = array_filter(Karya::byPembuat($id), fn($k) => $k['publikasi'] === 'Disetujui');
  foreach ($karyaAnggota as &$kk) { $kk['pembuat_nama'] = $a['nama']; } unset($kk);
  $lombaAnggota = Anggota::lombaDiikuti($id);
  $prestasi = count(array_filter($lombaAnggota, fn($l) => $l['hasil'] && str_starts_with($l['hasil'], 'Juara')));
?>
  <div class="breadcrumb"><a href="anggota.php">Anggota</a> / <?= e($a['nama']) ?></div>
  <div class="card" style="display:flex; gap:20px; align-items:center; margin-bottom:28px;">
    <div class="avatar" style="width:64px; height:64px;"><img src="<?= base_url(e($a['foto'] ?? 'assets/img/avatar-1.svg')) ?>" alt=""></div>
    <div>
      <h1 style="font-size:20px;"><?= e($a['nama']) ?></h1>
      <div class="meta" style="margin:4px 0 8px;"><?= e($a['kelas']) ?> Fokus <?= e($a['fokus']) ?></div>
      <p style="font-size:13.5px; color:var(--text-muted); max-width:60ch;"><?= e($a['deskripsi']) ?></p>
    </div>
  </div>
  <div class="grid grid-3" style="margin-bottom:28px;">
    <div class="stat"><div class="num"><?= count($karyaAnggota) ?></div><div class="lbl">Karya ditampilkan</div></div>
    <div class="stat"><div class="num"><?= count($lombaAnggota) ?></div><div class="lbl">Lomba diikuti</div></div>
    <div class="stat"><div class="num"><?= $prestasi ?></div><div class="lbl">Prestasi diraih</div></div>
  </div>
  <div class="section-head"><h2>Karya</h2></div>
  <div class="grid grid-3">
    <?php if ($karyaAnggota): foreach ($karyaAnggota as $k): ?><?php include __DIR__ . '/../app/includes/partial_karya_card.php'; ?><?php endforeach; else: ?>
      <div class="empty">Belum ada karya yang ditampilkan.</div>
    <?php endif; ?>
  </div>
<?php endif; ?>
</div>
<?php include __DIR__ . '/../app/includes/partial_lightbox.php'; ?>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>
