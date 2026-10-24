<?php
/**
 * Profil admin: ubah username, email & kata sandi akun yang sedang login.
 * Kebijakan yang sama dengan Profil Saya (anggota): kata sandi lama wajib
 * diverifikasi, sandi baru opsional (minimal 8 karakter), CSRF wajib.
 */
require_once __DIR__ . '/../../app/includes/bootstrap.php';
Auth::requireRole('admin');

$akun = User::find((int) ($_SESSION['user_id'] ?? 0));

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();
    if (!$akun) {
        flash_set('error', 'Akun admin tidak ditemukan.');
        redirect('admin/profil.php');
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
        redirect('admin/profil.php');
    }
}

$pageTitle = 'Profil & Kata Sandi';
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
        <div class="eyebrow">AKUN</div>
        <h1>Profil &amp; Kata Sandi</h1>
        <p class="desc">Ubah username, email, dan kata sandi akun admin yang sedang login. Kata sandi lama wajib diisi.</p>
      </div>
    </div>

    <?php if ($msg = flash_get()): ?>
      <div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div>
    <?php endif; ?>

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
        <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
          <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
          <a class="btn btn-outline" href="<?= base_url('admin/dashboard.php') ?>">Kembali</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../app/includes/footer.php'; ?>
