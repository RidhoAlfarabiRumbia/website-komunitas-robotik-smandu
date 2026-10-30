<?php
require_once __DIR__ . '/../../app/includes/bootstrap.php';
Auth::requireRole('admin');

$profil = komunitas_profil();
$errors = [];
$form = $profil;

$hapusFoto = static function (?string $rel): void {
    if (!is_string($rel) || !preg_match('#^uploads/komunitas/[A-Za-z0-9._-]+$#', $rel)) {
        return;
    }
    $abs = storage_path($rel);
    if ($abs === null) {
        return;
    }
    $dir = realpath(dirname($abs));
    if ($dir === false) {
        return;
    }
    $stem = pathinfo($abs, PATHINFO_FILENAME);
    if (str_ends_with($stem, '_opt')) {
        $stem = substr($stem, 0, -4);
    }
    $prefix = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    foreach ([$prefix . $stem . '.*', $prefix . $stem . '_opt.*'] as $pola) {
        foreach (glob($pola) ?: [] as $file) {
            if (is_file($file) && !@unlink($file)) {
                error_log('Gagal menghapus foto komunitas lama: ' . $file);
            }
        }
    }
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();
    $aksi = nilai_teks($_POST, 'aksi');

    if ($aksi === 'hapus_foto') {
        $lama = (string) $profil['pembina_foto'];
        Pengaturan::set(['pembina_foto' => '']);
        $hapusFoto($lama);
        flash_set('success', 'Foto pembina berhasil dihapus.');
        redirect('admin/komunitas-pengaturan.php');
    }

    $data = [
        'komunitas_nama' => nilai_teks($_POST, 'komunitas_nama'),
        'komunitas_tagline' => nilai_teks($_POST, 'komunitas_tagline'),
        'komunitas_deskripsi' => trim(nilai_teks($_POST, 'komunitas_deskripsi', false)),
        'komunitas_visi' => trim(nilai_teks($_POST, 'komunitas_visi', false)),
        'komunitas_misi' => trim(nilai_teks($_POST, 'komunitas_misi', false)),
        'komunitas_lokasi' => nilai_teks($_POST, 'komunitas_lokasi'),
        'komunitas_email' => nilai_teks($_POST, 'komunitas_email'),
        'komunitas_instagram' => nilai_teks($_POST, 'komunitas_instagram'),
        'pembina_nama' => nilai_teks($_POST, 'pembina_nama'),
        'pembina_jabatan' => nilai_teks($_POST, 'pembina_jabatan'),
        'pembina_bio' => trim(nilai_teks($_POST, 'pembina_bio', false)),
    ];
    $form = array_merge($profil, $data);

    if ($data['komunitas_nama'] === '') {
        $errors[] = 'Nama komunitas wajib diisi.';
    } elseif (strlen($data['komunitas_nama']) > 120) {
        $errors[] = 'Nama komunitas maksimal 120 karakter.';
    }
    if (strlen($data['komunitas_tagline']) > 160) {
        $errors[] = 'Tagline maksimal 160 karakter.';
    }
    if (strlen($data['komunitas_deskripsi']) > 2000) {
        $errors[] = 'Deskripsi maksimal 2.000 karakter.';
    }
    if (strlen($data['komunitas_visi']) > 1000) {
        $errors[] = 'Visi maksimal 1.000 karakter.';
    }
    if (strlen($data['komunitas_misi']) > 2000) {
        $errors[] = 'Misi maksimal 2.000 karakter.';
    }
    if (strlen($data['komunitas_lokasi']) > 160) {
        $errors[] = 'Lokasi maksimal 160 karakter.';
    }
    if ($data['komunitas_email'] !== '' && filter_var($data['komunitas_email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors[] = 'Format email komunitas tidak valid.';
    }
    if ($data['komunitas_email'] !== '' && strlen($data['komunitas_email']) > 120) {
        $errors[] = 'Email maksimal 120 karakter.';
    }
    if (strlen($data['komunitas_instagram']) > 255) {
        $errors[] = 'URL Instagram maksimal 255 karakter.';
    } elseif ($data['komunitas_instagram'] !== '') {
        $skema = strtolower((string) parse_url($data['komunitas_instagram'], PHP_URL_SCHEME));
        if (filter_var($data['komunitas_instagram'], FILTER_VALIDATE_URL) === false
            || !in_array($skema, ['http', 'https'], true)) {
            $errors[] = 'URL Instagram harus berupa alamat http atau https yang valid.';
        }
    }
    if (strlen($data['pembina_nama']) > 120) {
        $errors[] = 'Nama pembina maksimal 120 karakter.';
    }
    if (strlen($data['pembina_jabatan']) > 120) {
        $errors[] = 'Jabatan pembina maksimal 120 karakter.';
    }
    if (strlen($data['pembina_bio']) > 1000) {
        $errors[] = 'Bio pembina maksimal 1.000 karakter.';
    }

    $fotoBaru = null;
    $berkasFoto = $_FILES['pembina_foto'] ?? null;
    if (is_array($berkasFoto) && !empty($berkasFoto['name'])) {
        $fotoBaru = handle_upload('pembina_foto', 'komunitas');
        if ($fotoBaru === null) {
            $errors[] = 'Foto pembina gagal diunggah. Gunakan JPG, PNG, atau WEBP maksimal 2MB.';
        }
    }

    if ($errors && $fotoBaru !== null) {
        $hapusFoto($fotoBaru);
        $fotoBaru = null;
    }

    if (!$errors) {
        if ($fotoBaru !== null) {
            $data['pembina_foto'] = $fotoBaru;
        }
        Pengaturan::set($data);
        if ($fotoBaru !== null && $profil['pembina_foto'] !== '') {
            $hapusFoto($profil['pembina_foto']);
        }
        flash_set('success', 'Profil komunitas berhasil disimpan.');
        redirect('admin/komunitas-pengaturan.php');
    }
}

$fotoTersimpan = $form['pembina_foto'] !== '';
$adaFoto = $fotoTersimpan && storage_path($form['pembina_foto']) !== null;

$pageTitle = 'Profil Komunitas';
include __DIR__ . '/../../app/includes/header.php';
?>
<div class="topbar"><div class="topbar-inner admin-topbar">
  <?php include __DIR__ . '/_hamburger.php'; ?>
  <a href="dashboard.php" class="brand"><span class="brand-dot"></span><span class="brand-name"><span>Robotik</span><span>Smandu</span></span><span class="brand-admin">/ Admin</span></a>
  <div style="flex:1;"></div>
  <div class="user-chip">
    <a class="btn btn-outline btn-sm" href="<?= base_url('komunitas.php') ?>" title="Lihat Profil Komunitas"><i class="bi bi-eye"></i> Lihat Halaman</a>
    <?php include __DIR__ . '/../../app/includes/partial_pengajuan_popup.php'; ?>
    <button type="button" class="theme-toggle" onclick="toggleTheme()" title="Mode gelap/terang" aria-label="Toggle dark mode">
      <i class="bi bi-moon-stars-fill" id="themeIconDark"></i>
      <i class="bi bi-sun-fill" id="themeIconLight" style="display:none;"></i>
    </button>
    <?php include __DIR__ . '/../../app/includes/partial_profil_menu.php'; ?>
  </div>
</div></div>
<div class="admin-body">
  <?php include __DIR__ . '/_sidebar.php'; ?>
  <div class="admin-main">
    <div class="page-head">
      <div>
        <div class="eyebrow">KOMUNITAS</div>
        <h1>Profil Komunitas</h1>
        <p class="desc">Kelola identitas, visi dan misi, kontak, serta profil pembina yang tampil di halaman publik.</p>
      </div>
      <a class="btn btn-outline" href="<?= base_url('komunitas.php') ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Lihat Halaman</a>
    </div>

    <?php if ($msg = flash_get()): ?><div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div><?php endif; ?>
    <?php if ($errors): ?>
      <div class="alert alert-error">
        <ul style="margin:0; padding-left:18px;">
          <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form class="card" method="post" enctype="multipart/form-data">
      <?= Csrf::field() ?>
      <input type="hidden" name="aksi" value="simpan">

      <h3 style="font-size:14px; margin-bottom:12px;"><i class="bi bi-buildings"></i> Identitas</h3>
      <div class="grid grid-2">
        <div class="field">
          <label for="komunitas_nama">Nama komunitas <span class="req">*</span></label>
          <input type="text" id="komunitas_nama" name="komunitas_nama" maxlength="120" required value="<?= e($form['komunitas_nama']) ?>">
        </div>
        <div class="field">
          <label for="komunitas_tagline">Tagline</label>
          <input type="text" id="komunitas_tagline" name="komunitas_tagline" maxlength="160" value="<?= e($form['komunitas_tagline']) ?>">
        </div>
      </div>
      <div class="field">
        <label for="komunitas_deskripsi">Deskripsi singkat</label>
        <textarea id="komunitas_deskripsi" name="komunitas_deskripsi" rows="4" maxlength="2000"><?= e($form['komunitas_deskripsi']) ?></textarea>
      </div>

      <h3 style="font-size:14px; margin:20px 0 12px;"><i class="bi bi-compass"></i> Visi &amp; Misi</h3>
      <div class="field">
        <label for="komunitas_visi">Visi</label>
        <textarea id="komunitas_visi" name="komunitas_visi" rows="3" maxlength="1000"><?= e($form['komunitas_visi']) ?></textarea>
      </div>
      <div class="field">
        <label for="komunitas_misi">Misi</label>
        <textarea id="komunitas_misi" name="komunitas_misi" rows="4" maxlength="2000"><?= e($form['komunitas_misi']) ?></textarea>
        <span class="hint">Satu poin misi per baris.</span>
      </div>

      <h3 style="font-size:14px; margin:20px 0 12px;"><i class="bi bi-geo-alt"></i> Kontak</h3>
      <div class="grid grid-2">
        <div class="field">
          <label for="komunitas_lokasi">Lokasi</label>
          <input type="text" id="komunitas_lokasi" name="komunitas_lokasi" maxlength="160" value="<?= e($form['komunitas_lokasi']) ?>" placeholder="Contoh: SMA Negeri 2 Kota Ternate">
        </div>
        <div class="field">
          <label for="komunitas_email">Email komunitas</label>
          <input type="email" id="komunitas_email" name="komunitas_email" maxlength="120" value="<?= e($form['komunitas_email']) ?>" placeholder="nama@contoh.com">
        </div>
      </div>
      <div class="field">
        <label for="komunitas_instagram">URL Instagram</label>
        <input type="url" id="komunitas_instagram" name="komunitas_instagram" maxlength="255" value="<?= e($form['komunitas_instagram']) ?>" placeholder="https://www.instagram.com/...">
        <span class="hint">Gunakan alamat lengkap yang diawali https://.</span>
      </div>

      <h3 style="font-size:14px; margin:20px 0 12px;"><i class="bi bi-person-badge"></i> Pembina</h3>
      <div class="grid grid-2">
        <div class="field">
          <label for="pembina_nama">Nama pembina</label>
          <input type="text" id="pembina_nama" name="pembina_nama" maxlength="120" value="<?= e($form['pembina_nama']) ?>">
        </div>
        <div class="field">
          <label for="pembina_jabatan">Jabatan</label>
          <input type="text" id="pembina_jabatan" name="pembina_jabatan" maxlength="120" value="<?= e($form['pembina_jabatan']) ?>" placeholder="Contoh: Pembina Komunitas Robotik">
        </div>
      </div>
      <div class="field">
        <label for="pembina_bio">Bio singkat</label>
        <textarea id="pembina_bio" name="pembina_bio" rows="3" maxlength="1000"><?= e($form['pembina_bio']) ?></textarea>
      </div>
      <div class="field">
        <label for="pembina_foto">Foto pembina</label>
        <input type="file" id="pembina_foto" name="pembina_foto" accept="image/jpeg,image/png,image/webp">
        <span class="hint">JPG, PNG, atau WEBP. Maksimal 2MB. Mengunggah foto baru akan mengganti foto lama.</span>
      </div>

      <?php if ($adaFoto): ?>
        <div style="display:flex; gap:12px; align-items:center; margin:4px 0 16px;">
          <img src="<?= base_url(e($form['pembina_foto'])) ?>" alt="Foto pembina saat ini" style="width:76px; height:76px; border-radius:50%; object-fit:cover;">
          <span style="font-size:12.5px; color:var(--text-muted);">Foto pembina saat ini.</span>
        </div>
      <?php elseif ($fotoTersimpan): ?>
        <div class="alert alert-warn">Path foto pembina tersimpan, tetapi berkasnya tidak ditemukan. Unggah foto baru atau hapus data foto.</div>
      <?php endif; ?>

      <div style="display:flex; gap:10px; margin-top:10px; align-items:center; flex-wrap:wrap;">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> Simpan Profil</button>
        <a class="btn btn-outline" href="<?= base_url('komunitas.php') ?>" target="_blank" rel="noopener">Pratinjau Halaman</a>
      </div>
    </form>

    <?php if ($fotoTersimpan): ?>
      <form class="card" method="post" style="margin-top:16px;">
        <?= Csrf::field() ?>
        <input type="hidden" name="aksi" value="hapus_foto">
        <h3 style="font-size:14px; margin-bottom:8px;"><i class="bi bi-trash"></i> Hapus Foto Pembina</h3>
        <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:12px;">Foto akan dihapus dari halaman publik dan berkasnya dibersihkan dari server.</p>
        <button class="btn btn-danger-outline btn-sm" type="submit" onclick="return confirm('Hapus foto pembina?')"><i class="bi bi-trash"></i> Hapus Foto</button>
      </form>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../../app/includes/footer.php'; ?>
