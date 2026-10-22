<?php
require_once __DIR__ . '/../../app/includes/bootstrap.php';
Auth::requireRole('admin');

$pending = Karya::byStatus('Menunggu');
$totalAnggota = Anggota::jumlah();
$totalKaryaTayang = Karya::jumlahByStatus('Disetujui');
$pendingPeserta = Pelatihan::pendingPeserta();
$pendingDaftar = Pendaftaran::hitungStatus('Baru');
$totalPelatihan = Pelatihan::jumlah();
$totalPrestasi = Prestasi::jumlahTayang();

$pageTitle = 'Dashboard Admin';
include __DIR__ . '/../../app/includes/header.php';
?>
<div class="topbar"><div class="topbar-inner admin-topbar">
  <?php include __DIR__ . '/_hamburger.php'; ?>
  <a href="dashboard.php" class="brand"><span class="brand-dot"></span><span class="brand-name"><span>Robotik</span><span>Smandu</span></span><span class="brand-admin">/ Admin</span></a>
  <div style="flex:1;"></div>
  <div class="user-chip">
    <a class="btn btn-outline btn-sm" href="<?= base_url('beranda.php') ?>" title="Lihat Beranda"><i class="bi bi-eye"></i> Beranda</a>
    <?php include __DIR__ . '/../../app/includes/partial_pengajuan_popup.php'; ?>
    <button type="button" class="theme-toggle" onclick="toggleTheme()" title="Mode gelap/terang" aria-label="Toggle dark mode">
      <i class="bi bi-moon-stars-fill" id="themeIconDark"></i>
      <i class="bi bi-sun-fill" id="themeIconLight" style="display:none;"></i>
    </button>
    <a class="profile-icon" href="<?= base_url('admin/profil.php') ?>" title="Profil &amp; Kata Sandi"><i class="bi bi-person-circle"></i></a>
    <form method="post" action="<?= base_url('logout.php') ?>" style="display:flex;"><input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>"><button type="submit" class="logout-link" title="Keluar"><i class="bi bi-box-arrow-right"></i></button></form>
  </div>
</div></div>
<div class="admin-body">
  <?php include __DIR__ . '/_sidebar.php'; ?>
  <div class="admin-main">
    <div class="page-head">
      <div><div class="eyebrow">DASHBOARD</div><h1>Ringkasan Komunitas</h1><p class="desc">Pantau aktivitas dan pengajuan yang perlu ditindaklanjuti.</p></div>
    </div>
    <div class="grid grid-4 grid-stagger admin-stats">
      <div class="stat"><div class="num"><?= $totalAnggota ?></div><div class="lbl">Anggota terdaftar</div></div>
      <div class="stat"><div class="num"><?= $totalKaryaTayang ?></div><div class="lbl">Karya tayang</div></div>
      <div class="stat"><div class="num"><?= $totalPrestasi ?></div><div class="lbl">Prestasi tayang</div></div>
      <div class="stat"><div class="num"><?= $totalPelatihan ?></div><div class="lbl">Pelatihan / Workshop</div></div>
    </div>

    <div class="grid grid-3 admin-counters">
      <a class="card card-counter" href="moderasi.php">
        <i class="bi bi-check2-square" style="color:<?= $pending ? 'var(--amber)' : 'var(--teal)' ?>"></i>
        <div class="counter-body">
          <div class="counter-num"><?= count($pending) ?></div>
          <div class="meta">Pengajuan karya menunggu</div>
        </div>
      </a>
      <a class="card card-counter" href="pelatihan-peserta.php">
        <i class="bi bi-person-check" style="color:<?= $pendingPeserta ? 'var(--amber)' : 'var(--teal)' ?>"></i>
        <div class="counter-body">
          <div class="counter-num"><?= $pendingPeserta ?></div>
          <div class="meta">Peserta pelatihan menunggu</div>
        </div>
      </a>
      <a class="card card-counter" href="verifikasi.php">
        <i class="bi bi-file-earmark-check" style="color:<?= $pendingDaftar ? 'var(--amber)' : 'var(--teal)' ?>"></i>
        <div class="counter-body">
          <div class="counter-num"><?= $pendingDaftar ?></div>
          <div class="meta">Pendaftaran anggota baru</div>
        </div>
      </a>
    </div>

    <div class="section-head"><h2>Pengajuan terbaru</h2><a class="see-all" href="moderasi.php">Buka moderasi <i class="bi bi-arrow-right"></i></a></div>
    <div class="table-wrap">
      <?php if ($pending): foreach ($pending as $k): ?>
        <div class="list-item list-item-pad">
          <div class="list-thumb"><img src="<?= base_url(e($k['foto'] ?? 'assets/img/karya-robot.svg')) ?>" alt="" loading="lazy" decoding="async"></div>
          <div class="grow"><div class="ttl"><?= e($k['judul']) ?></div><div class="meta">oleh <?= e($k['pembuat_nama']) ?> Â· <?= tanggal_indo($k['dibuat_pada']) ?></div></div>
          <a class="btn btn-outline btn-sm" href="moderasi.php">Review</a>
        </div>
      <?php endforeach; else: ?>
        <div class="empty">Tidak ada pengajuan yang menunggu.</div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../app/includes/footer.php'; ?>
