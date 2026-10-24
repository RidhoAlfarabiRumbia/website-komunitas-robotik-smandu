<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';

if (Auth::check()) {
    redirect(Auth::role() === 'admin' ? 'admin/dashboard.php' : 'beranda.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();

    $email = strtolower(nilai_teks($_POST, 'email'));
    if ($email === '') {
        $errors[] = 'Email wajib diisi.';
    } elseif (strlen($email) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    if (!$errors) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $emailHash = PasswordReset::emailHash($email);

        if (!PasswordReset::terlaluSering($emailHash, $ip)) {
            $user = User::findByEmail($email);
            if ($user) {
                $token = bin2hex(random_bytes(32));
                PasswordReset::catat(
                    $email,
                    (int) $user['id'],
                    hash('sha256', $token),
                    date('Y-m-d H:i:s', time() + 1800),
                    $ip
                );

                $tautan = app_url('reset-password.php?token=' . rawurlencode($token));
                $teks = "Halo,\n\n"
                    . "Kami menerima permintaan reset kata sandi untuk akun Portal Robotik ini.\n"
                    . "Buka tautan berikut untuk membuat kata sandi baru (berlaku 30 menit):\n\n"
                    . $tautan . "\n\n"
                    . "Abaikan email ini jika Anda tidak meminta reset. Kata sandi tidak berubah.\n";
                $html = '<p>Halo,</p>'
                    . '<p>Kami menerima permintaan reset kata sandi untuk akun Portal Robotik ini.</p>'
                    . '<p><a href="' . e($tautan) . '">Buat kata sandi baru</a> (berlaku 30 menit).</p>'
                    . '<p style="font-size:12px; color:#666;">Jika tombol tidak berfungsi, salin tautan ini:<br>'
                    . e($tautan) . '</p>'
                    . '<p>Abaikan email ini jika Anda tidak meminta reset. Kata sandi tidak berubah.</p>';

                Mailer::send($email, 'Reset kata sandi Portal Robotik', $html, $teks);
            } else {
                // Tetap catat untuk rate limit, tanpa membuat token.
                PasswordReset::catat($email, null, null, null, $ip);
            }
        }

        // Selalu pesan generik agar tidak membocorkan apakah email terdaftar.
        flash_set('success', 'Jika email tersebut terdaftar, kami sudah mengirim tautan reset kata sandi. Periksa kotak masuk dan folder spam.');
        redirect('lupa-password.php');
    }
}

$pageTitle = 'Lupa Password';
include __DIR__ . '/../app/includes/header.php';
?>
<div class="login-wrap">
  <div class="card" style="width:100%; max-width:480px; padding:32px;">
    <div class="eyebrow">AKUN</div>
    <h1 style="font-size:22px; margin-bottom:8px;">Lupa password?</h1>
    <p style="color:var(--text-muted); font-size:13.5px; margin-bottom:20px;">
      Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat kata sandi baru.
    </p>

    <?php if ($msg = flash_get()): ?>
      <div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div>
    <?php endif; ?>

    <?php if ($errors): foreach ($errors as $err): ?>
      <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; endif; ?>

    <form method="post">
      <?= Csrf::field() ?>
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" maxlength="120" required autocomplete="email" autofocus>
      </div>
      <button class="btn btn-primary" style="width:100%;" type="submit">Kirim tautan reset</button>
    </form>

    <p style="font-size:12.5px; color:var(--text-muted); margin-top:16px;">
      Email harus sudah terdaftar di akun Anda. Jika belum, hubungi admin komunitas.
    </p>
    <p style="font-size:12.5px; margin-top:12px;">
      <a href="<?= base_url('login.php') ?>" style="color:var(--teal); text-decoration:none;"><i class="bi bi-arrow-left"></i> Kembali ke halaman masuk</a>
    </p>
  </div>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>
