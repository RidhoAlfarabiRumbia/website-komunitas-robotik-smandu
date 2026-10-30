<?php
/**
 * Halaman error umum. Dipanggil lewat ?code=<kode>, misalnya:
 *   error.php?code=404  -> halaman tidak ditemukan
 *   error.php?code=token -> tautan reset kata sandi tidak valid/kedaluwarsa
 *
 * Pesan diambil hanya dari daftar di bawah, tidak pernah dari query string,
 * agar parameter tidak bisa dipakai untuk menyisipkan teks/HTML sembarangan.
 */
require_once __DIR__ . '/../app/includes/bootstrap.php';

header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Robots-Tag: noindex, nofollow');

$kode = nilai_teks($_GET, 'code');

$daftar = [
    '400' => [
        'status' => 400,
        'judul' => 'Permintaan Tidak Valid',
        'pesan' => 'Permintaan tidak dapat diproses. Periksa kembali tautan atau data yang dikirim.',
        'ikon' => 'exclamation-circle',
    ],
    '401' => [
        'status' => 401,
        'judul' => 'Perlu Masuk',
        'pesan' => 'Anda perlu masuk ke portal untuk membuka halaman ini.',
        'ikon' => 'lock',
    ],
    '403' => [
        'status' => 403,
        'judul' => 'Akses Ditolak',
        'pesan' => 'Anda tidak memiliki izin untuk membuka halaman ini.',
        'ikon' => 'shield-lock',
    ],
    '404' => [
        'status' => 404,
        'judul' => 'Halaman Tidak Ditemukan',
        'pesan' => 'Halaman yang Anda cari tidak tersedia atau sudah dipindahkan.',
        'ikon' => 'compass',
    ],
    '429' => [
        'status' => 429,
        'judul' => 'Terlalu Banyak Permintaan',
        'pesan' => 'Terlalu banyak percobaan. Tunggu beberapa saat lalu coba lagi.',
        'ikon' => 'clock-history',
    ],
    '500' => [
        'status' => 500,
        'judul' => 'Kesalahan Server',
        'pesan' => 'Terjadi kesalahan di server. Silakan coba beberapa saat lagi.',
        'ikon' => 'x-circle',
    ],
    '503' => [
        'status' => 503,
        'judul' => 'Layanan Tidak Tersedia',
        'pesan' => 'Layanan sedang tidak tersedia. Silakan coba beberapa saat lagi.',
        'ikon' => 'hourglass-split',
    ],
    'token' => [
        'status' => 400,
        'judul' => 'Tautan Tidak Valid',
        'pesan' => 'Tautan reset kata sandi tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.',
        'ikon' => 'key',
        'aksi' => 'lupa-password.php',
        'aksi_label' => 'Minta tautan reset baru',
    ],
];

$error = $daftar[$kode] ?? $daftar['404'];
http_response_code($error['status']);

$pageTitle = $error['judul'];
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="card" style="max-width:520px; margin:40px auto; padding:48px 28px; text-align:center;">
    <div style="font-size:46px; color:var(--red); margin-bottom:12px;" aria-hidden="true">
      <i class="bi bi-<?= e($error['ikon']) ?>"></i>
    </div>
    <div class="eyebrow">Error <?= (int) $error['status'] ?></div>
    <h1 style="font-size:22px; margin-bottom:10px;"><?= e($error['judul']) ?></h1>
    <p style="color:var(--text-muted); font-size:13.5px; max-width:420px; margin:0 auto 22px;">
      <?= e($error['pesan']) ?>
    </p>
    <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
      <?php if (isset($error['aksi'])): ?>
        <a class="btn btn-primary" href="<?= base_url($error['aksi']) ?>"><i class="bi bi-arrow-repeat"></i> <?= e($error['aksi_label']) ?></a>
        <a class="btn btn-outline" href="<?= base_url('beranda.php') ?>">Kembali ke Beranda</a>
      <?php else: ?>
        <?php if ($error['status'] === 401): ?>
          <a class="btn btn-primary" href="<?= base_url('login.php') ?>"><i class="bi bi-box-arrow-in-right"></i> Masuk ke Portal</a>
        <?php endif; ?>
        <a class="btn <?= $error['status'] === 401 ? 'btn-outline' : 'btn-primary' ?>" href="<?= base_url('beranda.php') ?>">Kembali ke Beranda</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>
