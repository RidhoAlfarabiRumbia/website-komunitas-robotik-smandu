<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';
Auth::requireRole('anggota');

$u = Auth::anggota();
$id = (int) $u['id'];
$akun = User::findByAnggota($id);

// Ubah username / kata sandi — wajib CSRF + kata sandi lama (lihat User::ubahAkun)
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();
    if (!$akun) {
        flash_set('error', 'Akun tidak ditemukan.');
        redirect('profil.php');
    }
    $passBaru = nilai_teks($_POST, 'password_baru', false);
    $errors = $passBaru !== nilai_teks($_POST, 'password_baru2', false)
        ? ['Pengulangan kata sandi baru tidak sama.']
        : User::ubahAkun(
            (int) $akun['id'],
            nilai_teks($_POST, 'username'),
            nilai_teks($_POST, 'password_lama', false),
            $passBaru
        );
    if (!$errors) {
        flash_set('success', 'Perubahan username / kata sandi berhasil disimpan.');
        redirect('profil.php');
    }
}

$karyaSaya = Karya::byPembuat($id);
$lombaSaya = Anggota::lombaDiikuti($id);
$pelatihanSaya = Anggota::riwayatPelatihan($id);
$prestasiSaya = Prestasi::byAnggota($id);
$pakta = Anggota::paktaIntegritas($id);

$stat = [
    'karya' => count($karyaSaya),
    'lomba' => count($lombaSaya),
    'pelatihan' => count($pelatihanSaya),
    'prestasi' => count($prestasiSaya),
];
$jumlahKriyaTayang = count(array_filter($karyaSaya, fn($k) => $k['publikasi'] === 'Approved'));

$pageTitle = 'Profil Saya';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <?php if ($msg = flash_get()): ?>
    <div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div>
  <?php endif; ?>

  <div class="card" style="display:flex; gap:20px; align-items:center; margin-bottom:28px; flex-wrap:wrap;">
    <div class="avatar" style="width:64px; height:64px;"><img src="<?= base_url(e($u['foto'] ?? 'assets/img/avatar-1.svg')) ?>" alt=""></div>
    <div style="flex:1; min-width:200px;">
      <h1 style="font-size:20px;"><?= e($u['nama']) ?></h1>
      <div class="meta" style="margin:4px 0 8px;"><?= e($u['kelas']) ?> · Fokus <?= e($u['fokus']) ?></div>
      <p style="font-size:13.5px; color:var(--text-muted); max-width:60ch;"><?= e($u['deskripsi']) ?></p>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
      <a class="btn btn-primary" href="ajukan-karya.php"><i class="bi bi-plus-circle"></i> Ajukan Karya</a>
      <a class="btn btn-outline" href="ajukan-prestasi.php"><i class="bi bi-trophy"></i> Ajukan Prestasi</a>
    </div>
  </div>

  <div class="grid grid-4" style="margin-bottom:28px;">
    <div class="stat"><div class="num"><?= $stat['karya'] ?></div><div class="lbl">Karya diajukan</div></div>
    <div class="stat"><div class="num"><?= $jumlahKriyaTayang ?></div><div class="lbl">Karya tayang</div></div>
    <div class="stat"><div class="num"><?= $stat['pelatihan'] ?></div><div class="lbl">Pelatihan diikuti</div></div>
    <div class="stat"><div class="num"><?= $stat['prestasi'] ?></div><div class="lbl">Prestasi</div></div>
  </div>

  <section class="section">
  <div class="section-head">
    <h2>Karya Saya</h2>
    <span class="muted"><?= $stat['karya'] ?> karya</span>
  </div>
  <div class="table-wrap">
    <?php if ($karyaSaya): foreach ($karyaSaya as $k): ?>
      <div class="list-item list-item-pad">
        <div class="list-thumb"><img src="<?= base_url(e($k['foto'] ?? 'assets/img/karya-robot.svg')) ?>" alt="" loading="lazy" decoding="async"></div>
        <div class="grow">
          <a href="karya-detail.php?id=<?= (int) $k['id'] ?>" class="ttl"><?= e($k['judul']) ?></a>
          <div class="meta">Diajukan <?= tanggal_indo($k['dibuat_pada']) ?></div>
        </div>
        <div style="display:flex; gap:6px; flex-wrap:wrap; justify-content:flex-end;">
          <?= badge_progres($k['progres']) ?>
          <?= badge_publikasi($k['publikasi']) ?>
        </div>
      </div>
      <?php if ($k['publikasi'] === 'Rejected' && $k['alasan_tolak']): ?>
        <div style="padding:0 18px 14px 84px; font-size:12.5px; color:var(--red);">Catatan admin: <?= e($k['alasan_tolak']) ?></div>
      <?php endif; ?>
    <?php endforeach; else: ?>
      <div class="empty">Belum ada karya yang diajukan.</div>
    <?php endif; ?>
  </div>
  </section>

  <section class="section">
  <div class="section-head">
    <h2>Prestasi Saya</h2>
    <a class="btn btn-outline btn-sm" href="ajukan-prestasi.php"><i class="bi bi-plus"></i> Ajukan</a>
  </div>
  <div class="table-wrap">
    <?php if ($prestasiSaya): foreach ($prestasiSaya as $p): ?>
      <div class="list-item list-item-pad">
        <div class="list-thumb" style="display:grid; place-items:center; background:var(--blue-50); color:var(--blue); font-size:18px;">
          <i class="bi bi-trophy"></i>
        </div>
        <div class="grow">
          <a href="prestasi-detail.php?id=<?= (int) $p['id'] ?>" class="ttl"><?= e($p['nama']) ?></a>
          <div class="meta"><?= e($p['lomba_nama'] ?? '-') ?> · <?= tanggal_indo($p['tanggal']) ?></div>
        </div>
        <div style="display:flex; gap:6px; flex-wrap:wrap; justify-content:flex-end;">
          <span class="badge badge-amber"><?= e($p['hasil']) ?></span>
          <span class="badge badge-blue"><?= e($p['tingkat']) ?></span>
        </div>
      </div>
    <?php endforeach; else: ?>
      <div class="empty">Belum ada prestasi tercatat.</div>
    <?php endif; ?>
  </div>
  </section>

  <section class="section">
  <div class="section-head">
    <h2>Riwayat Pelatihan</h2>
    <a class="btn btn-outline btn-sm" href="pelatihan.php"><i class="bi bi-calendar"></i> Lihat pelatihan</a>
  </div>
  <div class="table-wrap">
    <?php if ($pelatihanSaya): foreach ($pelatihanSaya as $p): ?>
      <div class="list-item list-item-pad">
        <div class="list-thumb" style="display:grid; place-items:center; background:var(--blue-50); color:var(--blue); font-size:18px;">
          <i class="bi bi-mortarboard"></i>
        </div>
        <div class="grow">
          <a href="pelatihan-detail.php?id=<?= (int) $p['id'] ?>" class="ttl"><?= e($p['nama']) ?></a>
          <div class="meta"><?= e(tanggal_rentang($p['tanggal_mulai'], $p['tanggal_selesai'])) ?> · <?= e($p['lokasi'] ?: '-') ?></div>
        </div>
        <div style="display:flex; gap:6px; flex-wrap:wrap; justify-content:flex-end;">
          <?= badge_status_peserta($p['status_pendaftaran']) ?>
          <?php if (!Pelatihan::aktif($p)): ?><span class="badge badge-muted">Selesai</span><?php endif; ?>
        </div>
      </div>
    <?php endforeach; else: ?>
      <div class="empty">Belum mengikuti pelatihan. <a href="pelatihan.php" style="color:var(--teal);">Lihat pelatihan tersedia</a>.</div>
    <?php endif; ?>
  </div>
  </section>

  <section class="section">
  <div class="section-head"><h2>Pakta Integritas</h2></div>
  <div class="card">
    <?php if ($pakta['status'] === null): ?>
      <p style="font-size:13.5px; color:var(--text-muted); margin-bottom:12px;">
        Dokumen Pakta Integritas kamu belum tercatat. Hubungi admin bila sudah pernah mengirim.
      </p>
    <?php else: ?>
<div class="side-info-row"><span class="k">Status</span><span><?= badge_status_pendaftaran($pakta['status']) ?></span></div>
        <div class="side-info-row" style="border:none;"><span class="k">Diperbarui</span><span><?= e(tanggal_indo($pakta['tanggal'])) ?></span></div>
      <p style="font-size:12.5px; color:var(--text-muted); margin-top:14px;">
        Dokumen disimpan di penyimpanan privat portal. Hanya admin yang dapat membukanya.
      </p>
    <?php endif; ?>
  </div>
  </section>

  <section class="section">
  <div class="section-head"><h2>Akun &amp; Keamanan</h2></div>
  <div class="card" style="max-width:680px;">
    <?php if ($errors): ?>
      <div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div>
    <?php endif; ?>
    <form method="post">
      <?= Csrf::field() ?>
      <div class="form-grid">
        <div class="field field-full">
          <label>Username</label>
          <input name="username" value="<?= e(nilai_teks($_POST, 'username') ?: ($akun['username'] ?? '')) ?>" minlength="3" required autocomplete="username">
        </div>
        <div class="field">
          <label>Kata sandi saat ini *</label>
          <input type="password" name="password_lama" required autocomplete="current-password">
        </div>
        <div class="field">
          <label>Kata sandi baru</label>
          <input type="password" name="password_baru" minlength="6" autocomplete="new-password">
        </div>
        <div class="field field-full">
          <label>Ulangi kata sandi baru</label>
          <input type="password" name="password_baru2" minlength="6" autocomplete="new-password">
          <span class="hint">Kosongkan kata sandi baru bila hanya ingin mengganti username. Kata sandi lama tetap wajib diisi untuk menyimpan perubahan; sandi baru minimal 6 karakter.</span>
        </div>
      </div>
      <div style="margin-top:14px;">
        <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
      </div>
    </form>
  </div>
  </section>
</div>
<?php include __DIR__ . '/../app/includes/partial_lightbox.php'; ?>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>