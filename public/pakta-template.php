<?php
/**
 * Unduh formulir Pakta Integritas yang kosong (PRD 8.12).
 *
 * Halaman ini publik: calon anggota belum punya akun saat mengisi pendaftaran,
 * jadi tidak boleh memerlukan login. Berkasnya sendiri diunggah admin lewat
 * admin/pakta-template.php dan disimpan lewat pengaturan "pakta_template".
 */
require_once __DIR__ . '/../app/includes/bootstrap.php';

$tpl = pakta_template();

if ($tpl['ada']) {
    kirim_berkas($tpl['abs'], $tpl['nama'] !== '' ? $tpl['nama'] : 'Pakta-Integritas.pdf');
}

$pageTitle = 'Template Pakta Integritas';
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <div class="form-wrap" style="max-width:640px;">
    <a class="back-link" href="daftar.php"><i class="bi bi-arrow-left"></i> Kembali ke Pendaftaran</a>

    <div class="page-head" style="margin-bottom:20px;">
      <div>
        <div class="eyebrow">PAKTA INTEGRITAS</div>
        <h1>Template Pakta Integritas</h1>
        <p class="desc">Formulir kosong untuk diunduh, diisi, lalu diunggah kembali pada formulir pendaftaran.</p>
      </div>
    </div>

    <div class="card" style="text-align:center; padding:40px 24px;">
      <div style="font-size:40px; color:var(--amber, #f59e0b); margin-bottom:12px;">
        <i class="bi bi-file-earmark-x"></i>
      </div>
      <h2 style="font-size:19px; margin-bottom:10px;">Template Belum Tersedia</h2>
      <p style="color:var(--text-muted); max-width:440px; margin:0 auto 18px;">
        Admin belum mengunggah formulir Pakta Integritas. Hubungi pembina komunitas
        untuk memperoleh formulir, atau isi kolom keterangan pada formulir pendaftaran
        terlebih dahulu.
      </p>
      <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
        <a class="btn btn-primary" href="daftar.php"><i class="bi bi-pencil-square"></i> Ke Formulir Pendaftaran</a>
        <a class="btn btn-outline" href="beranda.php">Kembali ke Beranda</a>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>