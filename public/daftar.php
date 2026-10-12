<?php
/**
 * Pendaftaran Anggota Baru + Pakta Integritas (PRD 8.12).
 * Diisi calon anggota tanpa login. Berkas Pakta Integritas yang diunggah
 * disimpan di storage/terbatas sehingga hanya admin yang bisa membukanya.
 */
require_once __DIR__ . '/../app/includes/bootstrap.php';

$errors = [];
$old = ['nama' => '', 'kelas' => '', 'kontak' => '', 'email' => '', 'alasan' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();

    /* Endpoint publik tanpa login: batasi jumlah kiriman agar tidak bisa
       dipakai menghabiskan disk (spam/banjiri unggahan).
       Gunakan DB-backed rate limit (table login_attempts) dengan prefix 'daftar_'. */
    $ipHash = sha1($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $daftarKey = 'daftar_' . $ipHash;
    $stmt = Database::connect()->prepare('SELECT attempts, locked_until FROM login_attempts WHERE username_hash = ?');
    $stmt->execute([$daftarKey]);
    $row = $stmt->fetch();
    $maxDaftar = 3;
    $lockDaftar = 3600; // 1 jam
    if ($row && (int) $row['attempts'] >= $maxDaftar) {
        if ($row['locked_until'] && strtotime($row['locked_until']) > time()) {
            $menit = (int) ceil((strtotime($row['locked_until']) - time()) / 60);
            flash_set('error', "Terlalu banyak pendaftaran. Coba lagi dalam $menit menit.");
            redirect('daftar.php');
        }
        // Lock expired, reset
        Database::connect()->prepare('DELETE FROM login_attempts WHERE username_hash = ?')->execute([$daftarKey]);
    }

    $old = [
        'nama' => trim($_POST['nama'] ?? ''),
        'kelas' => trim($_POST['kelas'] ?? ''),
        'kontak' => trim($_POST['kontak'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'alasan' => trim($_POST['alasan'] ?? ''),
    ];

    if ($old['nama'] === '') {
        $errors['nama'] = 'Nama lengkap wajib diisi.';
    } elseif (mb_strlen($old['nama']) < 3 || mb_strlen($old['nama']) > 100) {
        $errors['nama'] = 'Nama harus 3-100 karakter.';
    }

    if ($old['kelas'] === '') {
        $errors['kelas'] = 'Kelas wajib diisi.';
    } elseif (mb_strlen($old['kelas']) > 40) {
        $errors['kelas'] = 'Maksimal 40 karakter.';
    }

    $kontakAngka = preg_replace('/\D/', '', $old['kontak']);
    if ($old['kontak'] === '') {
        $errors['kontak'] = 'Nomor kontak wajib diisi.';
    } elseif (strlen($kontakAngka) < 9 || strlen($kontakAngka) > 15) {
        $errors['kontak'] = 'Masukkan nomor kontak yang valid (9-15 digit).';
    }

    if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    }

    // Pakta Integritas wajib diunggah (PDF / gambar, maks 5MB)
    if (empty($_FILES['pakta_integritas']['name'])) {
        $errors['pakta_integritas'] = 'Pakta Integritas wajib diunggah.';
    } else {
        $f = $_FILES['pakta_integritas'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $errors['pakta_integritas'] = 'Upload gagal, coba lagi.';
        } elseif (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
            $errors['pakta_integritas'] = 'Format harus PDF, JPG, atau PNG.';
        } elseif ($f['size'] > 5 * 1024 * 1024) {
            $errors['pakta_integritas'] = 'Ukuran maksimal 5MB.';
        }
    }

    if (!$errors) {
        $berkas = simpan_upload(
            'pakta_integritas',
            'pakta-integritas',
            ['pdf', 'jpg', 'jpeg', 'png'],
            5 * 1024 * 1024,
            'PDF, JPG, atau PNG',
            '5MB',
            true
        );

        Pendaftaran::create([
            'nama' => $old['nama'],
            'kelas' => $old['kelas'],
            'kontak' => $old['kontak'],
            'email' => $old['email'] ?: null,
            'alasan' => $old['alasan'] ?: null,
            'pakta_integritas' => $berkas,
            'nama_berkas' => sanitize_filename($_FILES['pakta_integritas']['name']),
            'status' => 'Baru',
        ]);

        // Catat rate limit untuk pendaftaran (IP-based)
        $ipHash = sha1($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $daftarKey = 'daftar_' . $ipHash;
        $now = date('Y-m-d H:i:s');
        $stmt = Database::connect()->prepare('SELECT attempts FROM login_attempts WHERE username_hash = ?');
        $stmt->execute([$daftarKey]);
        $row = $stmt->fetch();
        if ($row) {
            Database::connect()->prepare('UPDATE login_attempts SET attempts = attempts + 1, updated_at = ? WHERE username_hash = ?')
                ->execute([$now, $daftarKey]);
        } else {
            Database::connect()->prepare('INSERT INTO login_attempts (username_hash, attempts, created_at, updated_at) VALUES (?, 1, ?, ?)')
                ->execute([$daftarKey, $now, $now]);
        }

        flash_set('success', 'Pendaftaran terkirim. Admin akan memverifikasi Pakta Integritas kamu sebelum akun diaktifkan.');
        redirect('daftar.php?terkirim=1');
    }

    flash_set('error', 'Periksa kembali isian yang ditandai.');
}

$pageTitle = 'Pendaftaran Anggota';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="form-wrap" style="max-width:760px;">
    <div class="page-head" style="margin-bottom:20px;">
      <div>
        <div class="eyebrow">GABUNG</div>
        <h1>Pendaftaran Anggota</h1>
        <p class="desc">Isi data dan unggah Pakta Integritas. Admin memverifikasinya sebelum akun diaktifkan.</p>
      </div>
    </div>

    <?php if (isset($_GET['terkirim'])): ?>
      <div class="card" style="text-align:center; padding:44px 24px;">
        <div style="font-size:44px; color:var(--teal); margin-bottom:12px;"><i class="bi bi-check-circle"></i></div>
        <h2 style="font-size:20px; margin-bottom:10px;">Pendaftaran Terkirim</h2>
        <p style="color:var(--text-muted); max-width:460px; margin:0 auto 20px;">
          Terima kasih. Admin akan memeriksa Pakta Integritas kamu. Proses ini biasanya memerlukan waktu 1-3 hari kerja.
        </p>
        <a class="btn btn-outline" href="beranda.php">Kembali ke Beranda</a>
      </div>
    <?php else: ?>
      <div class="alert alert-info" style="display:flex; gap:10px; align-items:flex-start; margin-bottom:18px;">
        <i class="bi bi-shield-lock"></i>
        <span>Dokumen Pakta Integritas kamu disimpan di penyimpanan privat portal dan hanya dapat dilihat oleh admin.</span>
      </div>

      <div class="card">
        <form method="post" enctype="multipart/form-data" class="form-grid">
          <?= Csrf::field() ?>

          <div class="field" style="grid-column:1/-1;">
            <label for="nama">Nama Lengkap <span class="req">*</span></label>
            <input type="text" id="nama" name="nama" value="<?= e($old['nama']) ?>" required maxlength="100" class="<?= isset($errors['nama']) ? 'invalid' : '' ?>">
            <?php if (isset($errors['nama'])): ?><span class="err"><?= e($errors['nama']) ?></span><?php endif; ?>
          </div>

          <div class="field">
            <label for="kelas">Kelas <span class="req">*</span></label>
            <input type="text" id="kelas" name="kelas" value="<?= e($old['kelas']) ?>" required maxlength="40" placeholder="Contoh: XI IPA 2" class="<?= isset($errors['kelas']) ? 'invalid' : '' ?>">
            <?php if (isset($errors['kelas'])): ?><span class="err"><?= e($errors['kelas']) ?></span><?php endif; ?>
          </div>

          <div class="field">
            <label for="kontak">Nomor Kontak <span class="req">*</span></label>
            <input type="tel" id="kontak" name="kontak" value="<?= e($old['kontak']) ?>" required placeholder="08xxxxxxxxxx" class="<?= isset($errors['kontak']) ? 'invalid' : '' ?>">
            <?php if (isset($errors['kontak'])): ?><span class="err"><?= e($errors['kontak']) ?></span><?php endif; ?>
          </div>

          <div class="field" style="grid-column:1/-1;">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" maxlength="120" class="<?= isset($errors['email']) ? 'invalid' : '' ?>">
            <?php if (isset($errors['email'])): ?><span class="err"><?= e($errors['email']) ?></span><?php endif; ?>
          </div>

          <div class="field" style="grid-column:1/-1;">
            <label for="alasan">Alasan ingin bergabung</label>
            <textarea id="alasan" name="alasan" rows="3"><?= e($old['alasan']) ?></textarea>
          </div>

          <div class="field" style="grid-column:1/-1;">
            <label for="pakta_integritas">Dokumen Pakta Integritas <span class="req">*</span></label>

            <?php $tplDaftar = pakta_template(); ?>
            <?php if ($tplDaftar['ada']): ?>
              <div class="alert alert-info" style="display:flex; gap:10px; align-items:center; justify-content:space-between; flex-wrap:wrap; margin-bottom:10px;">
                <span style="flex:1; min-width:220px;">
                  <i class="bi bi-file-earmark-arrow-down"></i>
                  Belum punya Pakta Integritas? Unduh formulir kosong, isi, lalu unggah hasilnya di sini.
                </span>
                <a class="btn btn-outline btn-sm" href="<?= base_url('pakta-template.php') ?>" target="_blank" rel="noopener">
                  <i class="bi bi-download"></i> Unduh Template
                </a>
              </div>
            <?php else: ?>
              <div class="alert alert-muted" style="margin-bottom:10px;">
                <i class="bi bi-info-circle"></i>
                Template Pakta Integritas belum tersedia di portal. Hubungi pembina komunitas untuk memperoleh formulir, lalu unggah hasilnya di sini.
              </div>
            <?php endif; ?>

            <input type="file" id="pakta_integritas" name="pakta_integritas" accept=".pdf,.jpg,.jpeg,.png" class="<?= isset($errors['pakta_integritas']) ? 'invalid' : '' ?>">
            <span class="hint">PDF atau gambar hasil pindai, maksimal 5MB.</span>
            <?php if (isset($errors['pakta_integritas'])): ?><span class="err"><?= e($errors['pakta_integritas']) ?></span><?php endif; ?>
          </div>

          <div class="form-actions" style="grid-column:1/-1;">
            <a class="btn btn-outline" href="beranda.php">Batal</a>
            <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Kirim Pendaftaran</button>
          </div>
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>