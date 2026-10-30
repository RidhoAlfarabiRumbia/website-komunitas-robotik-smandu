<?php
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
$akun = ($reset && !empty($reset['user_id'])) ? User::find((int) $reset['user_id']) : null;

if (!$reset || !$akun) {
    redirect('error.php?code=token');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset) {
    Csrf::verify();

    $password = nilai_teks($_POST, 'password_baru', false);
    $ulangi = nilai_teks($_POST, 'password_baru2', false);

    if (strlen($password) < 8) {
        $errors[] = 'Kata sandi baru minimal 8 karakter.';
    } elseif (strlen($password) > 4096) {
        $errors[] = 'Kata sandi terlalu panjang.';
    } elseif ($password !== $ulangi) {
        $errors[] = 'Pengulangan kata sandi baru tidak sama.';
    }

    if (!$errors && User::setPassword((int) $reset['user_id'], $password)) {
        PasswordReset::tandaiTerpakai((int) $reset['id']);
        flash_set('success', 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru.');
        redirect('login.php');
    }

    if (!$errors) {
        $errors[] = 'Kata sandi gagal diubah. Silakan coba lagi.';
    }
}

$pageTitle = 'Reset Kata Sandi';
include __DIR__ . '/../app/includes/header.php';
?>
<div class="login-wrap">
  <div class="card" style="width:100%; max-width:480px; padding:32px;">
    <div class="eyebrow">AKUN</div>
    <h1 style="font-size:22px; margin-bottom:8px;">Reset kata sandi</h1>

    <?php if ($errors): foreach ($errors as $err): ?>
      <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; endif; ?>

    <?php if ($reset && $akun): ?>
      <p style="color:var(--text-muted); font-size:13.5px; margin-bottom:20px;">
        Buat kata sandi baru untuk akun <strong><?= e($akun['username']) ?></strong>.
        Tautan ini hanya berlaku sekali.
      </p>

      <form method="post">
        <?= Csrf::field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="field">
          <label for="password_baru">Kata sandi baru</label>
          <input type="password" id="password_baru" name="password_baru" minlength="8" required autocomplete="new-password" autofocus>
        </div>
        <div class="field">
          <label for="password_baru2">Ulangi kata sandi baru</label>
          <input type="password" id="password_baru2" name="password_baru2" minlength="8" required autocomplete="new-password">
        </div>
        <button class="btn btn-primary" style="width:100%;" type="submit">Simpan kata sandi baru</button>
      </form>
    <?php endif; ?>

    <p style="font-size:12.5px; margin-top:16px;">
      <a href="<?= base_url('lupa-password.php') ?>" style="color:var(--teal); text-decoration:none;">Minta tautan reset baru</a>
      &nbsp;·&nbsp;
      <a href="<?= base_url('login.php') ?>" style="color:var(--teal); text-decoration:none;">Kembali ke halaman masuk</a>
    </p>
  </div>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>
