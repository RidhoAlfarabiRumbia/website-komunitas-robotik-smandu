<?php
require_once __DIR__ . '/../../app/includes/bootstrap.php';
Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();
    $id = (int) $_POST['id'];
    $aksi = $_POST['aksi'];
    if ($aksi === 'approve') {
        Karya::moderasi($id, 'Approved');
        flash_set('Karya disetujui dan kini tayang di portal.');
    } elseif ($aksi === 'reject') {
        Karya::moderasi($id, 'Rejected', trim($_POST['alasan'] ?? '') ?: 'Perlu perbaikan sebelum diajukan kembali.');
        flash_set('Karya ditolak dan anggota dapat melihat catatannya.');
    }
    redirect('admin/moderasi.php?status=' . urlencode($_POST['status_kembali'] ?? 'Pending'));
}

$status = $_GET['status'] ?? 'Pending';
if (!in_array($status, ['Pending', 'Approved', 'Rejected'], true)) $status = 'Pending';
$list = Karya::byStatus($status);

$pageTitle = 'Moderasi Karya';
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
    <form method="post" action="<?= base_url('logout.php') ?>" style="display:flex;"><input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>"><button type="submit" class="logout-link" title="Keluar"><i class="bi bi-box-arrow-right"></i></button></form>
  </div>
</div></div>
<div class="admin-body">
  <?php include __DIR__ . '/_sidebar.php'; ?>
  <div class="admin-main">
    <div class="page-head">
      <div><div class="eyebrow">MODERASI</div><h1>Pengajuan Karya</h1><p class="desc">Tinjau karya yang diajukan anggota, lalu setujui atau tolak sebelum tampil di portal.</p></div>
    </div>
    <?php if ($msg = flash_get()): ?><div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div><?php endif; ?>
    <div class="role-tabs" style="max-width:360px; margin-bottom:20px;">
      <?php foreach (['Pending','Approved','Rejected'] as $s): ?>
        <a href="?status=<?= $s ?>" class="role-tab <?= $status === $s ? 'active' : '' ?>" style="display:block; text-align:center;"><?= $s ?></a>
      <?php endforeach; ?>
    </div>
    <div class="grid grid-2">
      <?php if ($list): foreach ($list as $k):
        $teknologi = array_filter(array_map('trim', explode(',', $k['teknologi'] ?? '')));
      ?>
        <div class="card">
          <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
            <div style="display:flex; gap:8px;"><?= badge_progres($k['progres']) ?><?= badge_publikasi($k['publikasi']) ?></div>
          </div>
          <h3 style="font-size:15px; margin-bottom:4px;"><?= e($k['judul']) ?></h3>
          <div class="meta" style="margin-bottom:8px;">oleh <?= e($k['pembuat_nama']) ?> Â· <?= tanggal_indo($k['dibuat_pada']) ?></div>
          <p style="font-size:13px; color:var(--text-muted); margin-bottom:12px;"><?= e($k['deskripsi']) ?></p>
          <?php if ($teknologi): ?>
            <div class="work-tags" style="margin-bottom:14px;"><?php foreach ($teknologi as $t): ?><span class="tag"><?= e($t) ?></span><?php endforeach; ?></div>
          <?php endif; ?>

          <?php if ($k['publikasi'] === 'Pending'): ?>
            <div style="display:flex; gap:8px;">
              <form method="post" style="display:inline;">
                <?= Csrf::field() ?>
                <input type="hidden" name="id" value="<?= $k['id'] ?>">
                <input type="hidden" name="aksi" value="approve">
                <input type="hidden" name="status_kembali" value="<?= $status ?>">
                <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-check-lg"></i> Setujui</button>
              </form>
              <button class="btn btn-danger-outline btn-sm" type="button" onclick="document.getElementById('reject-<?= $k['id'] ?>').style.display='block'"><i class="bi bi-x-lg"></i> Tolak</button>
            </div>
            <form method="post" id="reject-<?= $k['id'] ?>" style="display:none; margin-top:10px;">
              <?= Csrf::field() ?>
              <input type="hidden" name="id" value="<?= $k['id'] ?>">
              <input type="hidden" name="aksi" value="reject">
              <input type="hidden" name="status_kembali" value="<?= $status ?>">
              <textarea name="alasan" rows="2" placeholder="Alasan penolakan" style="width:100%; border:1px solid var(--border); border-radius:22px; padding:8px; font-size:13px; margin-bottom:8px;"></textarea>
              <button class="btn btn-danger-outline btn-sm" type="submit">Kirim penolakan</button>
            </form>
          <?php elseif ($k['publikasi'] === 'Rejected' && $k['alasan_tolak']): ?>
            <div style="font-size:12.5px; color:var(--red);">Alasan: <?= e($k['alasan_tolak']) ?></div>
          <?php endif; ?>
        </div>
      <?php endforeach; else: ?>
        <div class="empty" style="grid-column:1/-1;">Tidak ada karya berstatus <?= e($status) ?>.</div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../app/includes/footer.php'; ?>
