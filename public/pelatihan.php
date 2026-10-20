<?php
/**
 * Pelatihan / Workshop (PRD 8.4).
 * Pengunjung umum hanya melihat pelatihan berlabel "Umum".
 * Anggota dapat mendaftar; admin menyetujui peserta.
 * Materi pelatihan hanya terbuka bagi peserta yang sudah disetujui.
 */
require_once __DIR__ . '/../app/includes/bootstrap.php';

// ---- Mode kelola admin: tambah / ubah / hapus pelatihan langsung di halaman ini ----
// Diletakkan sebelum blok pendaftaran di bawah: partial ini hanya aktif bila
// ?manage=pelatihan (atau POST dari form/form hapus di halaman ini).
$crudEntitas = 'pelatihan';
$crudItemEntity = 'pelatihan';                      // tombol Ubah/Hapus di tiap kartu
$crudQs = '?manage=pelatihan';
$crudAktif = Auth::isAdmin() && (($_GET['manage'] ?? $_POST['manage'] ?? '') === $crudEntitas);
$crudForm = false;
if ($crudAktif) {
    $crudPath = 'pelatihan.php';
    $crudParam = 'manage';
    include __DIR__ . '/../app/includes/crud_handle.php';
    $crudForm = $showForm;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();
    Auth::requireRole('anggota');

    $id = nilai_int($_POST, 'pelatihan_id');
    $p = Pelatihan::detail($id);

    if (!$p) {
        flash_set('error', 'Pelatihan tidak ditemukan.');
        redirect('pelatihan.php');
    }
    if (Pelatihan::sudahTerdaftar($id, (int) Auth::anggotaId())) {
        flash_set('error', 'Kamu sudah terdaftar pada pelatihan ini.');
        redirect('pelatihan-detail.php?id=' . $id);
    }
    if ($p['status'] !== 'Dibuka') {
        flash_set('error', 'Pendaftaran pelatihan ini sudah ditutup.');
        redirect('pelatihan-detail.php?id=' . $id);
    }
    Pelatihan::daftar($id, (int) Auth::anggotaId());
    flash_set('success', 'Pendaftaran terkirim. Menunggu persetujuan admin sebelum materi dapat dibuka.');
    redirect('pelatihan-detail.php?id=' . $id);
}

// Hanya anggota & admin yang boleh melihat pelatihan non-Umum.
// Auth::check() saja tidak cukup: itu tidak membedakan role.
$daftar = ($isPenggunaInternal = in_array(Auth::role(), ['anggota', 'admin'], true))
    ? Pelatihan::semua()
    : Pelatihan::untukPengunjung();

$pageTitle = 'Pelatihan / Workshop';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="page-head">
    <div>
      <div class="eyebrow">BELAJAR</div>
      <h1>Pelatihan &amp; Workshop</h1>
      <p class="desc">Kegiatan belajar terjadwal. Materi hanya terbuka bagi peserta yang telah disetujui admin.</p>
    </div>
    <?php if (!Auth::check()): ?>
      <a class="btn btn-outline" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Masuk untuk Daftar</a>
    <?php endif; ?>
    <?php if (Auth::isAdmin() && !$crudForm): ?>
      <a class="btn btn-primary" href="?manage=pelatihan&amp;action=create"><i class="bi bi-plus-circle"></i> Tambah Pelatihan</a>
    <?php endif; ?>
  </div>

  <?php if ($crudAktif && $msg = flash_get()): ?><div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div><?php endif; ?>
  <?php if ($crudForm): ?>
    <div class="section-head" style="justify-content:flex-start;"><h2><?= $editing ? 'Ubah' : 'Tambah' ?> Pelatihan</h2></div>
    <?php include __DIR__ . '/../app/includes/crud_form.php'; ?>
    <div style="margin:30px 0; border-top:1px solid var(--border);"></div>
  <?php endif; ?>

  <?php if (!Auth::check()): ?>
    <div class="alert alert-info" style="display:flex; gap:10px; align-items:flex-start;">
      <i class="bi bi-info-circle"></i>
      <span>Kamu melihat pelatihan yang terbuka untuk umum. Daftar pelatihan khusus anggota setelah <a href="<?= base_url('login.php') ?>" style="color:var(--teal);">masuk ke portal</a>.</span>
    </div>
  <?php endif; ?>

  <?php if ($daftar): ?>
    <div class="grid grid-2 grid-stagger reveal">
      <?php foreach ($daftar as $p):
        $saya = Auth::role() === 'anggota' ? Pelatihan::pendaftaranAnggota((int) $p['id'], (int) Auth::anggotaId()) : null;
      ?>
        <div class="card<?= Auth::isAdmin() && ($crudItemEntity ?? '') === 'pelatihan' ? ' card-admin' : '' ?>">
          <?php if ($crudItemEntity === 'pelatihan'): $itemId = (int) $p['id']; include __DIR__ . '/../app/includes/partial_admin_actions.php'; endif; ?>
          <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:10px;">
            <span class="badge <?= $p['status'] === 'Dibuka' ? 'badge-teal' : 'badge-muted' ?>"><?= e($p['status']) ?></span>
            <span class="badge <?= $p['akses'] === 'Umum' ? 'badge-blue' : 'badge-amber' ?>">
              <i class="bi <?= $p['akses'] === 'Umum' ? 'bi-globe2' : 'bi-people' ?>"></i> <?= $p['akses'] === 'Umum' ? 'Terbuka Umum' : 'Khusus Anggota' ?>
            </span>
            <?php if (!Pelatihan::aktif($p)): ?><span class="badge badge-muted">Selesai</span><?php endif; ?>
          </div>

          <h3 style="font-size:16px; margin-bottom:8px;"><?= e($p['nama']) ?></h3>
          <p style="font-size:13.5px; color:var(--text-muted); margin-bottom:14px;"><?= e(trunc($p['deskripsi'] ?? '', 150)) ?></p>

          <div class="side-info-row"><span class="k">Jadwal</span><span><?= e(tanggal_rentang($p['tanggal_mulai'], $p['tanggal_selesai'])) ?></span></div>
          <div class="side-info-row"><span class="k">Waktu</span><span><?= e($p['waktu'] ?: '-') ?></span></div>
          <div class="side-info-row"><span class="k">Lokasi</span><span><?= e($p['lokasi'] ?: '-') ?></span></div>
          <div class="side-info-row" style="border:none;"><span class="k">Mode</span><span><i class="bi <?= $p['mode'] === 'Luring' ? 'bi-geo-alt' : 'bi-globe2' ?>"></i> <?= $p['mode'] === 'Luring' ? 'Luring (Tatap Muka)' : 'Daring (Online)' ?></span></div>

          <div style="display:flex; gap:8px; margin-top:16px; flex-wrap:wrap; align-items:center;">
            <a class="btn btn-outline btn-sm" href="pelatihan-detail.php?id=<?= (int) $p['id'] ?>">Lihat Detail</a>

            <?php if (Auth::isAdmin()): ?>
              <a class="btn btn-primary btn-sm" href="admin/pelatihan-peserta.php?id=<?= (int) $p['id'] ?>">Kelola Peserta</a>
            <?php elseif (Auth::role() === 'anggota'): ?>
              <?php if ($saya): ?>
                <?= badge_status_peserta($saya['status']) ?>
              <?php elseif ($p['status'] === 'Dibuka'): ?>
                <form method="post" style="display:inline;">
                  <?= Csrf::field() ?>
                  <input type="hidden" name="pelatihan_id" value="<?= (int) $p['id'] ?>">
                  <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-pencil-square"></i> Daftar</button>
                </form>
              <?php else: ?>
                <span class="badge badge-muted">Pendaftaran Ditutup</span>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty">Belum ada pelatihan/workshop yang terdaftar.</div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>