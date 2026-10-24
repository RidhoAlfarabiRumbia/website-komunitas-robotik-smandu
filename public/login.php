<?php
require_once __DIR__ . '/../app/includes/bootstrap.php';

if (Auth::check()) {
    redirect(Auth::role() === 'admin' ? 'admin/dashboard.php' : 'beranda.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();
    $username = nilai_teks($_POST, 'username');
    $password = nilai_teks($_POST, 'password', false);
    if ($username === '') $errors[] = 'Username wajib diisi.';
    if ($password === '') $errors[] = 'Kata sandi wajib diisi.';
    if (!$errors) {
        $jeda = Auth::sisaJedaLogin($username);
        if ($jeda > 0) {
            $menit = (int) ceil($jeda / 60);
            $errors[] = "Terlalu banyak percobaan gagal. Coba lagi dalam $menit menit.";
        } elseif (Auth::attempt($username, $password)) {
            // Popup pengajuan terbaru ditampilkan sekali di dashboard admin.
            if (Auth::role() === 'admin') {
                $_SESSION['popup_pengajuan'] = true;
            }
            redirect(Auth::role() === 'admin' ? 'admin/dashboard.php' : 'beranda.php');
        } else {
            $errors[] = 'Username atau kata sandi salah.';
        }
    }
}

$pageTitle = 'Masuk';
include __DIR__ . '/../app/includes/header.php';
?>
<div class="login-wrap">
  <div class="login-card">
    <div class="login-side">
      <div class="brand-mark"> KOMUNITAS ROBOTIK SMA NEGERI 2</div>
      <h1>Belajar, berkarya, dan tumbuh bersama komunitas robotik Sma Negeri 2 Kota Ternate.</h1>
      <div style="margin-top:22px;">
        <?php foreach (['Belajar','Berkarya','Berkompetisi','Berbagi','Regenerasi'] as $i => $s): ?>
          <div class="cycle-step">
            <div class="node"><?= $i + 1 ?></div><div class="line"></div>
            <div><?= $s ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="login-form">
      <h2 style="font-size:18px;">Masuk ke portal</h2>
      <p style="color:var(--text-muted); font-size:13px; margin-bottom:20px;">Gunakan akun yang tersedia untuk masuk ke portal.</p>

      <?php if ($errors): foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; endif; ?>

      <form method="post">
        <?= Csrf::field() ?>

        <div class="field">
          <label>Username</label>
          <input type="text" name="username" id="usernameField" required>
        </div>

        <div class="field"><label>Kata sandi</label><input type="password" name="password" required></div>
        <p style="font-size:12.5px; margin:-4px 0 14px; text-align:right;"><a href="<?= base_url('lupa-password.php') ?>" style="color:var(--teal); text-decoration:none;">Lupa password?</a></p>
        <button class="btn btn-primary" style="width:100%;" type="submit">Masuk ke portal <i class="bi bi-arrow-right"></i></button>
      </form>
      <p style="font-size:12px; margin-top:12px;"><a href="<?= base_url('beranda.php') ?>" style="color:var(--teal); text-decoration:none;"><i class="bi bi-arrow-left"></i> Kembali ke Beranda</a></p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../app/includes/footer.php'; ?>
