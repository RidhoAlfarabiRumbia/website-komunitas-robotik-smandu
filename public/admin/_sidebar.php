<?php
// Partial ini berada DI DALAM document root, jadi bisa dipanggil langsung
// lewat URL. Tolak bila halaman induk belum melakukan pemeriksaan admin.
if (session_status() !== PHP_SESSION_ACTIVE || !Auth::isAdmin()) {
    http_response_code(403);
    exit('Akses ditolak.');
}

$current = basename($_SERVER['SCRIPT_NAME']);
// entity bisa berupa array bila URL dibuat seperti ?entity[]=karya — coerce aman
// supaya admin_link() tidak melempar TypeError.
$entity = is_string($_GET['entity'] ?? null) ? $_GET['entity'] : '';
$pendingCount = Karya::hitungPending();
$pendingPeserta = Pelatihan::pendingPeserta();
$pendingDaftar = Pendaftaran::hitungStatus('Baru');

function admin_link(string $file, string $label, string $icon, string $current, string $entity = '', string $checkEntity = ''): string
{
    $active = ($current === $file) && ($checkEntity === '' || $entity === $checkEntity);
    $href = $checkEntity ? "$file?entity=$checkEntity" : $file;
    return '<a class="side-link ' . ($active ? 'active' : '') . '" href="' . $href . '"' . ($active ? ' aria-current="page"' : '') . '><i class="bi ' . $icon . '"></i> ' . $label . '</a>';
}
?>
<div class="sidebar" id="adminSidebar">
  <button type="button" class="hamburger side-close" id="sidebarClose" aria-label="Tutup menu admin"><i class="bi bi-list"></i></button>
  <div class="side-label">Utama</div>
  <?= admin_link('dashboard.php', 'Dashboard', 'bi-grid-1x2', $current) ?>

  <div class="side-label">Kelola Konten</div>
  <?= admin_link('crud.php', 'Anggota', 'bi-people', $current, $entity, 'anggota') ?>
  <?= admin_link('crud.php', 'Materi', 'bi-journal-bookmark', $current, $entity, 'materi') ?>
  <?= admin_link('crud.php', 'Karya', 'bi-cpu', $current, $entity, 'karya') ?>
  <?= admin_link('crud.php', 'Prestasi', 'bi-award', $current, $entity, 'prestasi') ?>
  <?= admin_link('crud.php', 'Pelatihan', 'bi-mortarboard', $current, $entity, 'pelatihan') ?>
  <?= admin_link('crud.php', 'Galeri', 'bi-images', $current, $entity, 'galeri') ?>
  <?= admin_link('crud.php', 'Kegiatan', 'bi-calendar-event', $current, $entity, 'kegiatan') ?>
  <?= admin_link('crud.php', 'Lomba', 'bi-trophy', $current, $entity, 'lomba') ?>
  <?= admin_link('crud.php', 'Pengumuman', 'bi-megaphone', $current, $entity, 'pengumuman') ?>
  <?= admin_link('crud.php', 'Slider', 'bi-easel', $current, $entity, 'hero') ?>

  <div class="side-label">Moderasi</div>
  <a class="side-link <?= $current === 'moderasi.php' ? 'active' : '' ?>" href="moderasi.php" <?= $current === 'moderasi.php' ? 'aria-current="page"' : '' ?>>
    <i class="bi bi-check2-square"></i> Pengajuan Karya
    <?php if ($pendingCount): ?><span class="badge-count"><?= $pendingCount ?></span><?php endif; ?>
  </a>
  <a class="side-link <?= $current === 'pelatihan-peserta.php' ? 'active' : '' ?>" href="pelatihan-peserta.php" <?= $current === 'pelatihan-peserta.php' ? 'aria-current="page"' : '' ?>>
    <i class="bi bi-person-check"></i> Peserta Pelatihan
    <?php if ($pendingPeserta): ?><span class="badge-count"><?= $pendingPeserta ?></span><?php endif; ?>
  </a>
  <a class="side-link <?= $current === 'verifikasi.php' ? 'active' : '' ?>" href="verifikasi.php" <?= $current === 'verifikasi.php' ? 'aria-current="page"' : '' ?>>
    <i class="bi bi-file-earmark-check"></i> Verifikasi Pendaftaran
    <?php if ($pendingDaftar): ?><span class="badge-count"><?= $pendingDaftar ?></span><?php endif; ?>
  </a>

  <div class="side-label">Pendaftaran</div>
  <?= admin_link('pakta-template.php', 'Template Pakta Integritas', 'bi-file-earmark-text', $current) ?>

  <div class="side-label">Akun</div>
  <?= admin_link('profil.php', 'Profil &amp; Kata Sandi', 'bi-person-gear', $current) ?>
</div>
