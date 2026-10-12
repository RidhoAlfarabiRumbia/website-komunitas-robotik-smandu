<?php
/**
 * Kelola Template Pakta Integritas (PRD 8.12).
 *
 * Admin mengunggah satu formulir kosong (PDF/DOC/DOCX). Berkasnya disimpan di
 * public/uploads/pakta-integritas/ dan path-nya dicatat di tabel pengaturan,
 * sehingga halaman publik pakta-template.php bisa streamed tanpa login.
 */
require_once __DIR__ . '/../../app/includes/bootstrap.php';
Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();

    $lama = pakta_template();
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'hapus') {
        if ($lama['abs'] !== null) @unlink($lama['abs']);
        Pengaturan::set([
            'pakta_template' => '',
            'pakta_template_nama' => '',
            'pakta_template_diunggah' => '',
        ]);
        flash_set('success', 'Template Pakta Integritas dihapus.');
        redirect('admin/pakta-template.php');
    }

    // aksi = unggah
    $file = $_FILES['berkas'] ?? null;
    if (!$file || empty($file['name'])) {
        flash_set('error', 'Pilih berkas template terlebih dahulu.');
        redirect('admin/pakta-template.php');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        flash_set('error', in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
            ? 'Ukuran berkas melebihi batas 5MB / batas server.'
            : 'Unggahan gagal, silakan coba lagi.');
        redirect('admin/pakta-template.php');
    }

    // TRUE = simpan DI LUAR document root (storage/terbatas). Template ini
    // hanya boleh diunduh lewat pakta-template.php yang memeriksa hak akses.
    $path = simpan_upload(
        'berkas',
        'pakta-integritas',
        ['pdf', 'doc', 'docx'],
        5 * 1024 * 1024,
        'PDF, DOC, atau DOCX',
        '5MB',
        true
    );

    if ($path !== null) {
        if ($lama['abs'] !== null && $lama['abs'] !== $path) @unlink($lama['abs']);
        Pengaturan::set([
            'pakta_template' => $path,
            'pakta_template_nama' => sanitize_filename($file['name']),
            'pakta_template_diunggah' => date('Y-m-d H:i:s'),
        ]);
        flash_set('success', 'Template Pakta Integritas berhasil diperbarui.');
    }
    // simpan_upload() sudah memasang flash error bila format/ukuran tidak sesuai.

    redirect('admin/pakta-template.php');
}

$tpl = pakta_template();

$pageTitle = 'Template Pakta Integritas';
include __DIR__ . '/../../app/includes/header.php';
?>
<div class="topbar"><div class="topbar-inner admin-topbar">
  <?php include __DIR__ . '/_hamburger.php'; ?>
  <a href="dashboard.php" class="brand"><span class="brand-dot"></span><span class="brand-name"><span>Robotik</span><span>Smandu</span></span><span class="brand-admin">/ Admin</span></a>
  <div style="flex:1;"></div>
  <div class="user-chip">
    <a class="btn btn-outline btn-sm" href="<?= base_url('beranda.php') ?>" title="Lihat Beranda"><i class="bi bi-eye"></i> Beranda</a>
    <button type="button" class="theme-toggle" onclick="toggleTheme()" title="Mode gelap/terang" aria-label="Toggle dark mode">
      <i class="bi bi-moon-stars-fill" id="themeIconDark"></i>
      <i class="bi bi-sun-fill" id="themeIconLight" style="display:none;"></i>
    </button>
    <a class="profile-icon" href="<?= base_url('admin/profil.php') ?>" title="Profil &amp; Kata Sandi"><i class="bi bi-person-circle"></i></a>
    <form method="post" action="<?= base_url('logout.php') ?>" style="display:flex;"><input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>"><button type="submit" class="logout-link" title="Keluar" style="border:0; background:none; cursor:pointer;"><i class="bi bi-box-arrow-right"></i></button></form>
  </div>
</div></div>
<div class="admin-body">
  <?php include __DIR__ . '/_sidebar.php'; ?>
  <div class="admin-main">
    <div class="page-head">
      <div>
        <div class="eyebrow">PENDAFTARAN</div>
        <h1>Template Pakta Integritas</h1>
        <p class="desc">Formulir kosong yang diunduh calon anggota dari halaman pendaftaran. Cukup satu berkas, dipakai seluruh pendaftar.</p>
      </div>
      <a class="btn btn-outline" href="<?= base_url('daftar.php') ?>" target="_blank"><i class="bi bi-eye"></i> Lihat Pendaftaran</a>
    </div>

    <?php if ($msg = flash_get()): ?><div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div><?php endif; ?>

    <div class="card" style="max-width:680px;">
      <h3 style="font-size:14px; margin-bottom:12px;"><i class="bi bi-file-earmark-arrow-down"></i> Template Saat Ini</h3>

      <?php if ($tpl['ada']): ?>
        <div class="side-info-row"><span class="k">Berkas</span><span><?= e($tpl['nama']) ?></span></div>
        <div class="side-info-row"><span class="k">Diunggah</span><span><?= e($tpl['diunggah'] !== '' ? tanggal_indo($tpl['diunggah']) : '-') ?></span></div>
        <div class="side-info-row" style="border:none;"><span class="k">Ukuran</span><span><?= e(number_format((int) filesize($tpl['abs']) / 1024, 1, ',', '.')) ?> KB</span></div>

        <div style="display:flex; gap:8px; margin-top:16px; flex-wrap:wrap;">
          <a class="btn btn-outline btn-sm" href="<?= base_url('pakta-template.php') ?>" target="_blank" rel="noopener">
            <i class="bi bi-download"></i> Uji Unduhan
          </a>
          <a class="btn btn-outline btn-sm" href="<?= base_url('daftar.php') ?>" target="_blank" rel="noopener">
            <i class="bi bi-box-arrow-up-right"></i> Cek di Halaman Daftar
          </a>
        </div>
      <?php else: ?>
        <?php if ($tpl['path'] !== ''): ?>
          <div class="alert alert-muted" style="margin-bottom:0;">
            Pengaturan menunjuk ke <code><?= e($tpl['path']) ?></code> tetapi berkasnya tidak ditemukan di server.
            Silakan unggah ulang.
          </div>
        <?php else: ?>
          <div class="alert alert-muted" style="margin-bottom:0;">
            Belum ada template. Selama template belum diunggah, halaman
            <code>pakta-template.php</code> akan menampilkan pemberitahuan ke calon anggota.
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <form class="card" style="max-width:680px; margin-top:16px;" method="post" enctype="multipart/form-data">
      <?= Csrf::field() ?>
      <h3 style="font-size:14px; margin-bottom:12px;"><i class="bi bi-upload"></i> <?= $tpl['ada'] ? 'Ganti Template' : 'Unggah Template' ?></h3>

      <div class="field">
        <label for="berkas">Berkas formulir <span class="req">*</span></label>
        <input type="file" id="berkas" name="berkas" accept=".pdf,.doc,.docx" required>
        <span class="hint">PDF, DOC, atau DOCX. Maksimal 5MB. Menyimpan berkas baru akan otomatis mengganti dan menghapus versi lama.</span>
      </div>

      <div style="display:flex; gap:10px; margin-top:10px; align-items:center; flex-wrap:wrap;">
        <button class="btn btn-primary" type="submit" name="aksi" value="unggah"><i class="bi bi-check2"></i> Simpan Template</button>
        <?php if ($tpl['ada']): ?>
          <button class="btn btn-danger-outline" type="submit" name="aksi" value="hapus"
                  onclick="return confirm('Hapus template Pakta Integritas? Calon anggota tidak bisa mengunduhnya lagi.')">
            <i class="bi bi-trash"></i> Hapus
          </button>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../../app/includes/footer.php'; ?>