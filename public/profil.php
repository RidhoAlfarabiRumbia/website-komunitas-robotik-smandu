<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';
Auth::requireRole('anggota');

$u = Auth::anggota();
$id = (int) $u['id'];
$akun = User::findByAnggota($id);

/**
 * Hapus berkas foto profil lama dengan aman.
 * Hanya bekerja untuk path di uploads/anggota dan ikut membersihkan varian
 * _opt yang dibuat handle_upload().
 */
$hapusBerkasFoto = static function (?string $rel): void {
    if (!is_string($rel) || !preg_match('#^uploads/anggota/[A-Za-z0-9._-]+$#', $rel)) {
        return;
    }
    $dir = realpath(__DIR__ . '/uploads/anggota');
    $path = realpath(__DIR__ . '/' . $rel);
    if ($dir === false || $path === false) {
        return;
    }
    $prefix = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (!str_starts_with($path, $prefix) || !is_file($path)) {
        return;
    }
    $stem = pathinfo($path, PATHINFO_FILENAME);
    if (str_ends_with($stem, '_opt')) {
        $stem = substr($stem, 0, -4);
    }
    foreach ([$prefix . $stem . '.*', $prefix . $stem . '_opt.*'] as $pola) {
        foreach (glob($pola) ?: [] as $file) {
            if (is_file($file) && !@unlink($file)) {
                error_log('Gagal menghapus foto profil lama: ' . $file);
            }
        }
    }
};

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'foto_profil') {
    Csrf::verify();
    $uploadError = (isset($_FILES['foto']['error']) && is_scalar($_FILES['foto']['error']))
        ? (int) $_FILES['foto']['error']
        : UPLOAD_ERR_NO_FILE;

    if ($uploadError === UPLOAD_ERR_NO_FILE || empty($_FILES['foto']['name'])) {
        flash_set('error', 'Pilih foto profil terlebih dahulu.');
        redirect('profil.php');
    }
    if (in_array($uploadError, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
        flash_set('error', 'Ukuran foto terlalu besar. Maksimal 2MB.');
        redirect('profil.php');
    }

    $foto = handle_upload('foto', 'anggota');
    if ($foto === null) {
        if ($uploadError !== UPLOAD_ERR_OK) {
            flash_set('error', 'Upload foto gagal. Coba lagi.');
        }
        redirect('profil.php');
    }
    $fotoLama = (string) ($u['foto'] ?? '');
    if (!Anggota::update($id, ['foto' => $foto])) {
        flash_set('error', 'Foto profil gagal disimpan. Coba lagi.');
        redirect('profil.php');
    }
    $hapusBerkasFoto($fotoLama);
    Auth::refreshAnggota();
    flash_set('success', 'Foto profil berhasil diperbarui.');
    redirect('profil.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus_foto') {
    Csrf::verify();
    $fotoLama = (string) ($u['foto'] ?? '');
    if ($fotoLama === '') {
        flash_set('error', 'Tidak ada foto profil yang bisa dihapus.');
        redirect('profil.php');
    }
    if (!Anggota::update($id, ['foto' => null])) {
        flash_set('error', 'Foto profil gagal dihapus. Coba lagi.');
        redirect('profil.php');
    }
    $hapusBerkasFoto($fotoLama);
    Auth::refreshAnggota();
    flash_set('success', 'Foto profil berhasil dihapus.');
    redirect('profil.php');
}

// Ubah username / email / kata sandi — wajib CSRF + kata sandi lama (lihat User::ubahAkun)
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
            $passBaru,
            nilai_teks($_POST, 'email')
        );
    if (!$errors) {
        flash_set('success', 'Perubahan username / email / kata sandi berhasil disimpan.');
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
$jumlahKriyaTayang = count(array_filter($karyaSaya, fn($k) => $k['publikasi'] === 'Disetujui'));

$pageTitle = 'Profil Saya';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <?php if ($msg = flash_get()): ?>
    <div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div>
  <?php endif; ?>

  <div class="card" style="display:flex; gap:20px; align-items:center; margin-bottom:28px; flex-wrap:wrap;">
    <button type="button" class="avatar-btn" id="fotoProfilTrigger" aria-label="Ubah foto profil" aria-haspopup="dialog" aria-controls="fotoProfilModal">
      <span class="avatar" style="width:64px; height:64px;"><img src="<?= base_url(e($u['foto'] ?? 'assets/img/avatar-1.svg')) ?>" alt="Foto profil <?= e($u['nama']) ?>"></span>
      <span class="avatar-badge" aria-hidden="true"><i class="bi bi-camera"></i></span>
    </button>
    <div style="flex:1; min-width:200px;">
      <h1 style="font-size:20px;"><?= e($u['nama']) ?></h1>
      <div class="meta" style="margin:4px 0 8px;"><?= e($u['kelas']) ?></div>
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
      <?php if ($k['publikasi'] === 'Ditolak' && $k['alasan_tolak']): ?>
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
        <div class="field field-full">
          <label>Email *</label>
          <input type="email" name="email" value="<?= e(nilai_teks($_POST, 'email') ?: ($akun['email'] ?? '')) ?>" maxlength="120" required autocomplete="email">
          <span class="hint">Dipakai untuk reset kata sandi. Pastikan email ini aktif dan milik Anda.</span>
        </div>
        <div class="field">
          <label>Kata sandi saat ini *</label>
          <input type="password" name="password_lama" required autocomplete="current-password">
        </div>
        <div class="field">
          <label>Kata sandi baru</label>
          <input type="password" name="password_baru" minlength="8" autocomplete="new-password">
        </div>
        <div class="field field-full">
          <label>Ulangi kata sandi baru</label>
          <input type="password" name="password_baru2" minlength="8" autocomplete="new-password">
          <span class="hint">Kosongkan kata sandi baru bila hanya ingin mengganti username/email. Kata sandi lama tetap wajib diisi untuk menyimpan perubahan; sandi baru minimal 8 karakter.</span>
        </div>
      </div>
      <div style="margin-top:14px;">
        <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
      </div>
    </form>
  </div>
  </section>
</div>

<div class="foto-modal" id="fotoProfilModal" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="fotoProfilJudul">
  <div class="foto-card">
    <button type="button" class="foto-close" id="fotoProfilClose" aria-label="Tutup pengaturan foto profil"><i class="bi bi-x-lg"></i></button>
    <div class="foto-head">
      <div class="foto-avatar">
        <img id="fotoProfilPreview" src="<?= base_url(e($u['foto'] ?? 'assets/img/avatar-1.svg')) ?>" data-current-src="<?= base_url(e($u['foto'] ?? 'assets/img/avatar-1.svg')) ?>" alt="Foto profil saat ini">
      </div>
      <h2 id="fotoProfilJudul">Foto Profil</h2>
      <p class="foto-desc"><?= empty($u['foto']) ? 'Belum ada foto profil. Pilih foto untuk mulai.' : 'Pilih foto baru untuk mengganti foto profil saat ini.' ?></p>
    </div>
    <form method="post" enctype="multipart/form-data" class="foto-form" id="fotoProfilForm">
      <?= Csrf::field() ?>
      <input type="hidden" name="aksi" value="foto_profil">
      <input type="file" name="foto" id="fotoProfilInput" accept="image/jpeg,image/png,image/webp" hidden>
      <div class="foto-actions">
        <button type="button" class="btn btn-outline" id="fotoProfilPilih"><i class="bi bi-image"></i> Pilih Foto</button>
        <button type="submit" class="btn btn-primary" id="fotoProfilSimpan" disabled><i class="bi bi-check2"></i> Simpan Foto</button>
      </div>
      <p class="hint foto-hint" id="fotoProfilInfo" aria-live="polite">JPG, PNG, atau WEBP. Maksimal 2MB.</p>
      <p class="foto-error" id="fotoProfilError" role="alert" hidden></p>
    </form>
    <?php if (!empty($u['foto'])): ?>
      <form method="post" class="foto-hapus-form" id="fotoProfilHapusForm">
        <?= Csrf::field() ?>
        <input type="hidden" name="aksi" value="hapus_foto">
        <button type="submit" class="btn btn-danger-outline btn-sm"><i class="bi bi-trash"></i> Hapus Foto</button>
      </form>
    <?php endif; ?>
  </div>
</div>
<script>
(function(){
  var modal = document.getElementById('fotoProfilModal');
  var trigger = document.getElementById('fotoProfilTrigger');
  if (!modal || !trigger) return;
  var close = document.getElementById('fotoProfilClose');
  var pilih = document.getElementById('fotoProfilPilih');
  var input = document.getElementById('fotoProfilInput');
  var simpan = document.getElementById('fotoProfilSimpan');
  var preview = document.getElementById('fotoProfilPreview');
  var info = document.getElementById('fotoProfilInfo');
  var error = document.getElementById('fotoProfilError');
  var form = document.getElementById('fotoProfilForm');
  var hapusForm = document.getElementById('fotoProfilHapusForm');
  var fotoAwal = preview ? preview.getAttribute('data-current-src') : '';
  var infoAwal = info ? info.textContent : '';
  var fokusSebelum = null;

  function pesanError(teks) {
    if (!error) return;
    error.textContent = teks || '';
    error.hidden = !teks;
  }

  function reset() {
    if (input) input.value = '';
    if (simpan) simpan.disabled = true;
    if (preview) preview.src = fotoAwal;
    if (info) info.textContent = infoAwal;
    pesanError('');
  }

  function buka() {
    fokusSebelum = document.activeElement;
    reset();
    modal.hidden = false;
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    if (close) close.focus();
  }

  function tutup() {
    modal.hidden = true;
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    reset();
    if (fokusSebelum && typeof fokusSebelum.focus === 'function') fokusSebelum.focus();
  }

  trigger.addEventListener('click', buka);
  if (close) close.addEventListener('click', tutup);
  modal.addEventListener('click', function(e){
    if (e.target === modal) tutup();
  });
  document.addEventListener('keydown', function(e){
    if (!modal.hidden && e.key === 'Escape') tutup();
  });

  if (pilih && input) {
    pilih.addEventListener('click', function(){ input.click(); });
  }

  if (input) {
    input.addEventListener('change', function(){
      pesanError('');
      var file = input.files && input.files[0];
      if (!file) {
        reset();
        return;
      }
      var tipe = ['image/jpeg', 'image/png', 'image/webp'];
      if (file.type && tipe.indexOf(file.type) === -1) {
        reset();
        pesanError('Format file harus JPG, PNG, atau WEBP.');
        return;
      }
      if (file.size > 2 * 1024 * 1024) {
        reset();
        pesanError('Ukuran foto maksimal 2MB.');
        return;
      }

      var reader = new FileReader();
      reader.onload = function(e){
        if (preview) preview.src = String(e.target.result);
      };
      reader.onerror = function(){
        reset();
        pesanError('Foto tidak bisa dibaca. Coba file lain.');
      };
      reader.readAsDataURL(file);
      if (info) info.textContent = 'Dipilih: ' + file.name;
      if (simpan) simpan.disabled = false;
    });
  }

  if (form) {
    form.addEventListener('submit', function(){
      if (simpan) {
        simpan.disabled = true;
        simpan.textContent = 'Menyimpan...';
      }
    });
  }

  if (hapusForm) {
    hapusForm.addEventListener('submit', function(e){
      if (!window.confirm('Hapus foto profil? Foto akan kembali ke avatar default.')) {
        e.preventDefault();
      }
    });
  }
})();
</script>
<?php include __DIR__ . '/../app/includes/partial_lightbox.php'; ?>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>