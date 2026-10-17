<?php
/**
 * Materi (PRD 8.2).
 * Dibuka anggota & admin. Berkas materi dilindungi: unduh hanya tersedia bagi
 * peserta pelatihan terkait yang sudah disetujui admin dan selama pelatihan
 * berjalan; admin selalu boleh mengunduh.
 */
require_once __DIR__ . '/../app/includes/bootstrap.php';
Auth::requireMember();

$idAnggota = (int) Auth::anggotaId();
$isAdmin = Auth::isAdmin();

// ---- Mode kelola admin: tambah / ubah / hapus materi langsung di halaman ini ----
// Referensi tetap dikelola dari admin/crud.php?entity=materi&sub=referensi.
$crudEntitas = 'materi';
$crudItemEntity = 'materi';                        // tombol Ubah/Hapus di tiap kartu
$crudQs = '?manage=materi';
$crudAktif = $isAdmin && (($_GET['manage'] ?? $_POST['manage'] ?? '') === $crudEntitas);
$crudForm = false;
if ($crudAktif) {
    $crudPath = 'materi.php';
    $crudParam = 'manage';
    include __DIR__ . '/../app/includes/crud_handle.php';
    $crudForm = $showForm;
}

$materi = Materi::semuaWithPelatihan();
$referensi = Referensi::all('nama ASC');

$pageTitle = 'Materi';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="page-head">
    <div>
      <div class="eyebrow">BELAJAR</div>
      <h1>Materi Pembelajaran</h1>
      <p class="desc">Materi pelatihan terbuka bagi peserta yang telah disetujui admin pada pelatihan terkait, lengkap dengan referensi belajar eksternal.</p>
    </div>
    <?php if ($isAdmin && !$crudForm): ?>
      <a class="btn btn-primary" href="?manage=materi&amp;action=create"><i class="bi bi-plus-circle"></i> Tambah Materi</a>
    <?php endif; ?>
  </div>

  <?php if ($crudAktif && $msg = flash_get()): ?><div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div><?php endif; ?>
  <?php if ($crudForm): ?>
    <div class="section-head" style="justify-content:flex-start;"><h2><?= $editing ? 'Ubah' : 'Tambah' ?> Materi</h2></div>
    <?php include __DIR__ . '/../app/includes/crud_form.php'; ?>
    <div style="margin:30px 0; border-top:1px solid var(--border);"></div>
  <?php endif; ?>

  <?php if ($materi): ?>
    <div class="grid grid-3 grid-stagger reveal">
      <?php foreach ($materi as $m):
        $boleh = Materi::bolehAkses((int) $m['id'], $idAnggota, $isAdmin);
        $hasFile = !empty($m['file_path']);
        $hasLink = !empty($m['link_url']);
        $terkait = array_filter(explode(', ', (string) $m['pelatihan_terkait']));
      ?>
      <div class="card card-admin">
        <?php if ($crudItemEntity === 'materi'): $itemId = (int) $m['id']; include __DIR__ . '/../app/includes/partial_admin_actions.php'; endif; ?>
        <span class="badge badge-blue"><?= e($m['topik']) ?></span>
        <h3 style="font-size:15px; margin:10px 0 6px;"><?= e($m['judul']) ?></h3>
        <p style="font-size:13px; color:var(--text-muted); margin-bottom:12px;"><?= e(trunc($m['deskripsi'] ?? '', 110)) ?></p>

        <?php if ($terkait): ?>
          <p style="font-size:11.5px; color:var(--text-dim); margin-bottom:12px;">
            <i class="bi bi-mortarboard"></i> <?= e(trunc(implode(', ', $terkait), 70)) ?>
          </p>
        <?php else: ?>
          <p style="font-size:11.5px; color:var(--text-dim); margin-bottom:12px;">Materi umum</p>
        <?php endif; ?>

        <div class="work-foot" style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
          <?php if ($boleh && $hasFile): ?>
            <a class="btn btn-outline btn-sm" href="berkas.php?jenis=materi&amp;id=<?= (int) $m['id'] ?>"><i class="bi bi-download"></i> Unduh</a>
          <?php elseif ($boleh && $hasLink): ?>
            <a class="btn btn-outline btn-sm" href="<?= e($m['link_url']) ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Buka</a>
          <?php elseif ($hasLink): ?>
            <a class="btn btn-outline btn-sm" href="<?= e($m['link_url']) ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Tautan</a>
          <?php else: ?>
            <span class="badge badge-muted"><i class="bi bi-lock"></i> Terkunci</span>
          <?php endif; ?>
          <?php if (!$boleh && $hasFile): ?>
            <span style="font-size:11.5px; color:var(--text-dim);">Ikuti pelatihan terkait</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty">Belum ada materi pembelajaran.</div>
  <?php endif; ?>

  <div class="section-head" id="referensi" style="margin-top:38px; scroll-margin-top:86px;">
    <div>
      <div class="eyebrow">BELAJAR</div>
      <h2>Referensi Belajar</h2>
      <p class="desc">Sumber eksternal yang biasa dipakai anggota untuk belajar dan mengerjakan proyek. Selalu terbuka, tanpa batas pelatihan.</p>
    </div>
  </div>
  <?php if ($referensi): ?>
    <div class="grid grid-3 grid-stagger reveal">
      <?php foreach ($referensi as $r): ?>
      <div class="card">
        <h3 style="font-size:15px; margin-bottom:6px;"><i class="bi bi-link-45deg"></i> <?= e($r['nama']) ?></h3>
        <p style="font-size:13px; color:var(--text-muted); margin-bottom:12px;"><?= e($r['deskripsi']) ?></p>
        <a class="btn btn-outline btn-sm" href="<?= e($r['url']) ?>" target="_blank" rel="noopener">Buka situs <i class="bi bi-box-arrow-up-right"></i></a>
      </div>
    <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty">Belum ada referensi belajar.</div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>