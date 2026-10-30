<?php
$profilMenuAdmin = Auth::role() === 'admin';
$profilMenuAnggota = Auth::anggota();
$profilMenuNama = $profilMenuAdmin
    ? (trim((string) ($_SESSION['nama'] ?? '')) ?: 'Admin Robotik')
    : (trim((string) ($profilMenuAnggota['nama'] ?? '')) ?: 'Anggota');
$profilMenuSub = $profilMenuAdmin
    ? 'Administrator'
    : (trim((string) ($profilMenuAnggota['kelas'] ?? '')) ?: 'Anggota');
$profilMenuTautan = $profilMenuAdmin ? 'admin/profil.php' : 'profil.php';
$profilMenuLabel = $profilMenuAdmin ? 'Profil & Kata Sandi' : 'Profil Saya';
$profilMenuFoto = $profilMenuAdmin
    ? ''
    : trim((string) ($profilMenuAnggota['foto'] ?? ''));
$profilMenuAvatar = $profilMenuFoto !== ''
    ? $profilMenuFoto
    : ($profilMenuAdmin ? 'assets/img/avatar-admin.svg' : 'assets/img/avatar-1.svg');
?>
<div class="profile-menu">
  <button type="button" class="profile-icon" id="profileMenuToggle" aria-haspopup="true" aria-expanded="false" aria-controls="profileMenu" aria-label="Menu profil" title="Menu profil">
    <?php if ($profilMenuFoto !== ''): ?>
      <img src="<?= base_url(e($profilMenuFoto)) ?>" alt="">
    <?php else: ?>
      <i class="bi bi-person-circle" aria-hidden="true"></i>
    <?php endif; ?>
  </button>
  <div class="profile-popover" id="profileMenu" hidden>
    <div class="profile-popover-head">
      <span class="profile-popover-avatar" aria-hidden="true"><img src="<?= base_url(e($profilMenuAvatar)) ?>" alt=""></span>
      <div class="profile-popover-identity">
        <strong><?= e($profilMenuNama) ?></strong>
        <span><?= e($profilMenuSub) ?></span>
      </div>
    </div>
    <a class="profile-menu-item" href="<?= base_url($profilMenuTautan) ?>">
      <i class="bi bi-person" aria-hidden="true"></i> <?= e($profilMenuLabel) ?>
    </a>
    <form method="post" action="<?= base_url('logout.php') ?>" class="profile-menu-form">
      <?= Csrf::field() ?>
      <button type="submit" class="profile-menu-item profile-menu-logout">
        <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Keluar
      </button>
    </form>
  </div>
</div>
