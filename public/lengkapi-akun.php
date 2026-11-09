<?php
/**
 * Lengkapi Akun — tahap kedua alur pendaftaran anggota (PRD 8.12/8.15).
 * Tautan di email verifikasi membawa token sekali pakai (hash SHA-256 di
 * password_resets, user_id NULL, berlaku 24 jam). Anggota dipasangkan dari
 * baris pendaftaran berdasarkan email_hash token — bukan dari input pengguna,
 * sehingga tidak bisa dipalsukan.
 */
require_once __DIR__ . '/../app/includes/bootstrap.php';

if (Auth::check()) {
    redirect(Auth::role() === 'admin' ? 'admin/dashboard.php' : 'beranda.php');
}

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow');

$token = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? nilai_teks($_POST, 'token')
    : nilai_teks($_GET, 'token');

if ($token === '') {
    redirect('error.php?code=token');
}

$reset = PasswordReset::cariValid(hash('sha256', $token));
// Token milik alur lupa password (punya user_id) tidak diterima di halaman ini.
if (!$reset || !empty($reset['user_id'])) {
    redirect('error.php?code=token');
}

// Pasangkan token dengan baris pendaftaran yang memang sudah diverifikasi.
$p = null;
foreach (Pendaftaran::semua('Diverifikasi') as $row) {
    if (empty($row['email']) || empty($row['anggota_id'])) continue;
    if (PasswordReset::emailHash((string) $row['email']) === $reset['email_hash']) {
        $p = $row;
        break;
    }
}
$anggota = $p ? Anggota::find((int) $p['anggota_id']) : null;
if (!$p || !$anggota) {
    redirect('error.php?code=token');
}

// Akun sudah aktif sebelumnya: langsung arahkan masuk.
if (User::findByAnggota((int) $anggota['id'])) {
    flash_set('success', 'Akunmu sudah aktif. Silakan masuk.');
    redirect('login.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();

    $username = nilai_teks($_POST, 'username');
    $password = nilai_teks($_POST, 'password', false);
    $ulangi = nilai_teks($_POST, 'password2', false);

    if ($username === '') {
        $errors[] = 'Username wajib diisi.';
    } elseif (mb_strlen($username) < 3) {
        $errors[] = 'Username minimal 3 karakter.';
    } elseif (mb_strlen($username) > 60) {
        $errors[] = 'Username maksimal 60 karakter.';
    } elseif (User::usernameTaken($username)) {
        $errors[] = 'Username sudah dipakai akun lain.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Kata sandi minimal 8 karakter.';
    } elseif (strlen($password) > 4096) {
        $errors[] = 'Kata sandi terlalu panjang.';
    } elseif ($password !== $ulangi) {
        $errors[] = 'Pengulangan kata sandi tidak sama.';
    }

    if (!$errors) {
        $db = Database::connect();
        try {
            $db->beginTransaction();

            // Klaim token secara atomik: token yang sama tidak bisa dipakai dua kali.
            if (!PasswordReset::tandaiTerpakai((int) $reset['id'])) {
                $db->rollBack();
                flash_set('error', 'Tautan sudah digunakan atau kedaluwarsa.');
                redirect('error.php?code=token');
            }

            if (User::createForAnggota((int) $anggota['id'], $username, $password, (string) $p['email']) <= 0) {
                throw new RuntimeException('Gagal membuat akun anggota.');
            }

            $db->commit();
            flash_set('success', 'Selamat, akunmu sudah aktif! Silakan masuk dengan username dan kata sandi barumu.');
            redirect('login.php');
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[LENGKAPI AKUN] ' . $e->getMessage());
            $errors[] = 'Akun gagal dibuat. Silakan coba lagi.';
        }
    }
}

$pageTitle = 'Lengkapi Akun';
include __DIR__ . '/../app/includes/header.php';
?>
<div class="login-wrap">
  <div class="card" style="width:100%; max-width:480px; padding:32px;">
    <div class="eyebrow">AKUN</div>
    <h1 style="font-size:22px; margin-bottom:8px;">Lengkapi Akun</h1>
    <p style="color:var(--text-muted); font-size:13.5px; margin-bottom:20px;">
      Pendaftaranmu sudah diverifikasi. Pilih username dan kata sandi untuk
      mengaktifkan akun: <strong><?= e($anggota['nama']) ?></strong>. Tautan ini hanya berlaku sekali.
    </p>

    <?php if ($errors): foreach ($errors as $err): ?>
      <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; endif; ?>

    <form method="post">
      <?= Csrf::field() ?>
      <input type="hidden" name="token" value="<?= e($token) ?>">
      <div class="field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" minlength="3" maxlength="60" required autocomplete="username" autofocus>
      </div>
      <div class="field">
        <label for="password">Kata sandi</label>
        <input type="password" id="password" name="password" minlength="8" maxlength="4096" required autocomplete="new-password">
        <span class="hint">Minimal 8 karakter.</span>
      </div>
      <div class="field">
        <label for="password2">Ulangi kata sandi</label>
        <input type="password" id="password2" name="password2" minlength="8" maxlength="4096" required autocomplete="new-password">
      </div>
      <button class="btn btn-primary" style="width:100%;" type="submit">Aktifkan Akun</button>
    </form>

    <p style="font-size:12.5px; margin-top:16px;">
      <a href="<?= base_url('login.php') ?>" style="color:var(--teal); text-decoration:none;">Kembali ke halaman masuk</a>
    </p>
  </div>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>
