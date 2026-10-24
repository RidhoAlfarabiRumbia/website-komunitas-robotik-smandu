<?php
/**
 * Kunci & Periode Pendaftaran Anggota (PRD 8.12).
 *
 * Admin dapat membuka/menutup pendaftaran secara manual atau mengikuti jadwal
 * tanggal. Konfigurasi disimpan di tabel pengaturan sehingga tidak memerlukan
 * perubahan skema database.
 */
require_once __DIR__ . '/../../app/includes/bootstrap.php';
Auth::requireRole('admin');

$errors = [];
$mode = '';
$mulai = '';
$selesai = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();

    $mode = strtolower(nilai_teks($_POST, 'mode'));
    $mulai = nilai_teks($_POST, 'mulai');
    $selesai = nilai_teks($_POST, 'selesai');

    $modeValid = ['buka', 'tutup', 'jadwal'];
    $zona = new DateTimeZone('Asia/Jayapura');
    $parseTanggal = static function (string $nilai) use ($zona): ?DateTimeImmutable {
        if ($nilai === '') return null;
        $tanggal = DateTimeImmutable::createFromFormat('!Y-m-d', $nilai, $zona);
        $error = DateTimeImmutable::getLastErrors();
        if (!$tanggal
            || ($error !== false && ($error['warning_count'] > 0 || $error['error_count'] > 0))
            || $tanggal->format('Y-m-d') !== $nilai) {
            return null;
        }
        return $tanggal;
    };

    if (!in_array($mode, $modeValid, true)) {
        $errors[] = 'Pilih status pendaftaran yang valid.';
    }

    $awal = $parseTanggal($mulai);
    $akhir = $parseTanggal($selesai);

    if ($mode === 'jadwal') {
        if ($mulai === '' || $selesai === '') {
            $errors[] = 'Mode jadwal wajib memiliki tanggal mulai dan tanggal selesai.';
        } elseif (!$awal || !$akhir) {
            $errors[] = 'Format tanggal tidak valid. Gunakan tanggal yang benar.';
        } elseif ($akhir < $awal) {
            $errors[] = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
        }
    } else {
        if ($mulai !== '' && !$awal) {
            $errors[] = 'Format tanggal mulai tidak valid.';
        }
        if ($selesai !== '' && !$akhir) {
            $errors[] = 'Format tanggal selesai tidak valid.';
        }
        if ($awal && $akhir && $akhir < $awal) {
            $errors[] = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
        }
    }

    if (!$errors) {
        Pengaturan::set([
            'pendaftaran_mode' => $mode,
            'pendaftaran_mulai' => $mulai,
            'pendaftaran_selesai' => $selesai,
        ]);
        flash_set('success', 'Pengaturan kunci pendaftaran berhasil disimpan.');
        redirect('admin/pendaftaran-pengaturan.php');
    }
} else {
    $mode = strtolower(trim((string) Pengaturan::nilai('pendaftaran_mode', '')));
    $mulai = trim((string) Pengaturan::nilai('pendaftaran_mulai', ''));
    $selesai = trim((string) Pengaturan::nilai('pendaftaran_selesai', ''));
}

$gate = pendaftaran_gate();
$modeValid = ['buka', 'tutup', 'jadwal'];
$modeLabel = [
    'buka' => 'Buka sekarang',
    'tutup' => 'Tutup sekarang',
    'jadwal' => 'Ikuti jadwal',
][$mode] ?? ($mode === '' ? 'Belum diatur' : 'Tidak valid');
$formMode = in_array($mode, $modeValid, true) ? $mode : 'tutup';
$badgeClass = $gate['buka']
    ? 'badge badge-teal'
    : ($gate['status'] === 'tidak_valid' ? 'badge badge-amber' : 'badge badge-red');

$pageTitle = 'Kunci Pendaftaran';
include __DIR__ . '/../../app/includes/header.php';
?>
<div class="topbar"><div class="topbar-inner admin-topbar">
  <?php include __DIR__ . '/_hamburger.php'; ?>
  <a href="dashboard.php" class="brand"><span class="brand-dot"></span><span class="brand-name"><span>Robotik</span><span>Smandu</span></span><span class="brand-admin">/ Admin</span></a>
  <div style="flex:1;"></div>
  <div class="user-chip">
    <a class="btn btn-outline btn-sm" href="<?= base_url('beranda.php') ?>" title="Lihat Beranda"><i class="bi bi-eye"></i> Beranda</a>
    <?php include __DIR__ . '/../../app/includes/partial_pengajuan_popup.php'; ?>
    <button type="button" class="theme-toggle" onclick="toggleTheme()" title="Mode gelap/terang" aria-label="Toggle dark mode">
      <i class="bi bi-moon-stars-fill" id="themeIconDark"></i>
      <i class="bi bi-sun-fill" id="themeIconLight" style="display:none;"></i>
    </button>
    <a class="profile-icon" href="<?= base_url('admin/profil.php') ?>" title="Profil &amp; Kata Sandi"><i class="bi bi-person-circle"></i></a>
    <form method="post" action="<?= base_url('logout.php') ?>" style="display:flex;"><input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>"><button type="submit" class="logout-link" title="Keluar"><i class="bi bi-box-arrow-right"></i></button></form>
  </div>
</div></div>
<div class="admin-body">
  <?php include __DIR__ . '/_sidebar.php'; ?>
  <div class="admin-main">
    <div class="page-head">
      <div>
        <div class="eyebrow">PENDAFTARAN</div>
        <h1>Kunci Pendaftaran</h1>
        <p class="desc">Atur kapan formulir pendaftaran anggota dapat diakses. Saat ditutup, kiriman baru tidak diproses dan tidak masuk ke antrean verifikasi.</p>
      </div>
      <a class="btn btn-outline" href="<?= base_url('daftar.php') ?>" target="_blank" rel="noopener"><i class="bi bi-eye"></i> Lihat Pendaftaran</a>
    </div>

    <?php if ($msg = flash_get()): ?><div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div><?php endif; ?>

    <div class="card" style="max-width:760px;">
      <div style="display:flex; gap:10px; align-items:center; justify-content:space-between; flex-wrap:wrap; margin-bottom:12px;">
        <h3 style="font-size:14px; margin:0;"><i class="bi bi-lock"></i> Status Saat Ini</h3>
        <span class="<?= e($badgeClass) ?>"><?= e($gate['label']) ?></span>
      </div>
      <p style="font-size:13px; color:var(--text-muted); margin-bottom:16px;"><?= e($gate['pesan']) ?></p>

      <div class="side-info-row"><span class="k">Mode</span><span><?= e($modeLabel) ?></span></div>
      <?php if ($gate['mode'] === 'jadwal'): ?>
        <div class="side-info-row"><span class="k">Tanggal mulai</span><span><?= e($gate['mulai'] !== '' ? tanggal_indo($gate['mulai']) : '-') ?></span></div>
        <div class="side-info-row"><span class="k">Tanggal selesai</span><span><?= e($gate['selesai'] !== '' ? tanggal_indo($gate['selesai']) : '-') ?></span></div>
      <?php endif; ?>
      <div class="side-info-row" style="border:none;"><span class="k">Zona waktu</span><span>WIT (Asia/Jayapura)</span></div>

      <?php if (!$gate['diatur']): ?>
        <div class="alert alert-muted" style="margin:16px 0 0;">
          <i class="bi bi-info-circle"></i> Belum ada pengaturan. Pendaftaran dianggap tertutup sampai Anda menyimpannya.
        </div>
      <?php elseif ($gate['status'] === 'tidak_valid'): ?>
        <div class="alert alert-warn" style="margin:16px 0 0;">
          <i class="bi bi-exclamation-triangle"></i> Jadwal tersimpan tidak valid. Perbaiki tanggal di bawah agar pendaftaran bisa dibuka sesuai jadwal.
        </div>
      <?php endif; ?>
    </div>

    <form class="card" style="max-width:760px; margin-top:16px;" method="post">
      <?= Csrf::field() ?>
      <h3 style="font-size:14px; margin-bottom:12px;"><i class="bi bi-sliders"></i> Atur Kunci</h3>

      <?php if ($errors): ?>
        <div class="alert alert-error">
          <ul style="margin:0; padding-left:18px;">
            <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <div class="field">
        <label for="mode">Status pendaftaran <span class="req">*</span></label>
        <select id="mode" name="mode" required>
          <option value="tutup" <?= $formMode === 'tutup' ? 'selected' : '' ?>>Tutup sekarang</option>
          <option value="buka" <?= $formMode === 'buka' ? 'selected' : '' ?>>Buka sekarang</option>
          <option value="jadwal" <?= $formMode === 'jadwal' ? 'selected' : '' ?>>Ikuti jadwal</option>
        </select>
        <span class="hint">Mode buka/tutup mengalahkan jadwal. Pilih <strong>Ikuti jadwal</strong> untuk membuka dan menutup otomatis berdasarkan tanggal.</span>
      </div>

      <div id="jadwalFields">
        <div class="field">
          <label for="mulai">Tanggal mulai</label>
          <input type="date" id="mulai" name="mulai" value="<?= e($mulai) ?>">
          <span class="hint">Pendaftaran dibuka mulai pukul 00.00 WIT.</span>
        </div>
        <div class="field">
          <label for="selesai">Tanggal selesai</label>
          <input type="date" id="selesai" name="selesai" value="<?= e($selesai) ?>">
          <span class="hint">Pendaftaran ditutup pada pukul 23.59 WIT di tanggal ini. Tanggal selesai harus sama atau setelah tanggal mulai.</span>
        </div>
      </div>

      <div style="display:flex; gap:10px; margin-top:10px; align-items:center; flex-wrap:wrap;">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> Simpan Pengaturan</button>
        <a class="btn btn-outline" href="verifikasi.php">Buka Verifikasi Pendaftaran</a>
      </div>
    </form>
  </div>
</div>
<script>
(function () {
  var mode = document.getElementById('mode');
  var jadwal = document.getElementById('jadwalFields');
  if (!mode || !jadwal) return;
  function sync() {
    var aktif = mode.value === 'jadwal';
    jadwal.style.display = aktif ? '' : 'none';
    var inputs = jadwal.querySelectorAll('input');
    inputs.forEach(function (input) { input.required = aktif; });
  }
  mode.addEventListener('change', sync);
  sync();
})();
</script>
<?php include __DIR__ . '/../../app/includes/footer.php'; ?>
