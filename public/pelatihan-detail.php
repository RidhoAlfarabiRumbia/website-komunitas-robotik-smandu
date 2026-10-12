<?php
/**
 * Detail Pelatihan / Workshop (PRD 8.4).
 * Daftar materi ditampilkan, tetapi berkas hanya bisa diunduh bila pengguna
 * adalah admin atau peserta yang telah disetujui pada pelatihan ini.
 */
require_once __DIR__ . '/../app/includes/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);

// Pendaftaran peserta (hanya anggota)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();
    Auth::requireRole('anggota');

    $id = (int) ($_POST['pelatihan_id'] ?? $id);
    $p = Pelatihan::detail($id);

    if (!$p) {
        flash_set('error', 'Pelatihan tidak ditemukan.');
        redirect('pelatihan.php');
    }
    if (Pelatihan::sudahTerdaftar($id, (int) Auth::anggotaId())) {
        flash_set('error', 'Kamu sudah terdaftar pada pelatihan ini.');
        redirect('pelatihan-detail.php?id=' . $id);
    }
    if ($p['status'] !== 'Dibuka') {
        flash_set('error', 'Pendaftaran pelatihan ini sudah ditutup.');
        redirect('pelatihan-detail.php?id=' . $id);
    }
    if (Pelatihan::sisaKuota($p, (int) $p['jumlah_disetujui']) <= 0) {
        flash_set('error', 'Kuota pelatihan ini sudah penuh.');
        redirect('pelatihan-detail.php?id=' . $id);
    }

    Pelatihan::daftar($id, (int) Auth::anggotaId());
    flash_set('success', 'Pendaftaran terkirim. Materi baru terbuka setelah disetujui admin.');
    redirect('pelatihan-detail.php?id=' . $id);
}

$p = Pelatihan::detail($id);

if (!$p) {
    flash_set('error', 'Pelatihan tidak ditemukan.');
    redirect('pelatihan.php');
}

// Pengunjung hanya boleh membuka pelatihan yang terbuka untuk umum.
if (!Auth::check() && $p['akses'] !== 'Umum') {
    Auth::requireRole('anggota');
}

$isAdmin = Auth::isAdmin();
$isAnggota = Auth::role() === 'anggota';
$saya = $isAnggota ? Pelatihan::pendaftaranAnggota($id, (int) Auth::anggotaId()) : null;

$materi = Pelatihan::materi($id);
$sisa = Pelatihan::sisaKuota($p, (int) $p['jumlah_disetujui']);
$bolehMateri = $isAdmin || ($saya && $saya['status'] === 'Disetujui' && Pelatihan::aktif($p));
$peserta = (int) $p['jumlah_disetujui'];

$pageTitle = $p['nama'];
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <a class="back-link" href="pelatihan.php"><i class="bi bi-arrow-left"></i> Kembali ke daftar pelatihan</a>

  <div class="detail-wrap">
    <div class="detail-main">
      <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:14px;">
        <span class="badge <?= $p['status'] === 'Dibuka' ? 'badge-teal' : 'badge-muted' ?>"><?= e($p['status']) ?></span>
        <span class="badge <?= $p['akses'] === 'Umum' ? 'badge-blue' : 'badge-amber' ?>">
          <i class="bi <?= $p['akses'] === 'Umum' ? 'bi-globe2' : 'bi-people' ?>"></i> <?= $p['akses'] === 'Umum' ? 'Terbuka Umum' : 'Khusus Anggota' ?>
        </span>
        <?php if (!Pelatihan::aktif($p)): ?><span class="badge badge-muted">Selesai</span><?php endif; ?>
      </div>

      <h1 style="font-size:26px; margin-bottom:12px;"><?= e($p['nama']) ?></h1>
      <?php if ($p['deskripsi']): ?>
        <p style="color:var(--text-muted); line-height:1.75; margin-bottom:22px;"><?= nl2br(e($p['deskripsi'])) ?></p>
      <?php endif; ?>

      <div class="card" style="margin-bottom:22px;">
        <h3 style="font-size:15px; margin-bottom:14px;"><i class="bi bi-journal-text"></i> Materi Pelatihan</h3>

        <?php if (!$materi): ?>
          <p style="color:var(--text-muted); font-size:13.5px;">Materi belum tersedia untuk pelatihan ini.</p>
        <?php else: ?>
          <div class="list-tight">
            <?php foreach ($materi as $m):
              $boleh = $bolehMateri || $isAdmin;
            ?>
              <div class="list-row" style="align-items:center;">
                <div style="min-width:0; flex:1;">
                  <div style="font-size:14px; font-weight:600;"><?= e($m['judul']) ?></div>
                  <div style="font-size:12.5px; color:var(--text-muted);"><?= e($m['topik']) ?><?= $m['deskripsi'] ? ' - ' . e(trunc($m['deskripsi'], 80)) : '' ?></div>
                </div>
                <?php if ($boleh && $m['file_path']): ?>
                  <a class="btn btn-outline btn-sm" href="berkas.php?jenis=materi&amp;id=<?= (int) $m['id'] ?>">
                    <i class="bi bi-download"></i> Unduh
                  </a>
                <?php elseif ($m['link_url']): ?>
                  <a class="btn btn-outline btn-sm" href="<?= e($m['link_url']) ?>" target="_blank" rel="noopener">
                    <i class="bi bi-box-arrow-up-right"></i> Buka
                  </a>
                <?php else: ?>
                  <span class="badge badge-muted"><i class="bi bi-lock"></i> Terkunci</span>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>

          <?php if (!$bolehMateri && !$isAdmin): ?>
            <div class="alert alert-info" style="margin-top:16px; display:flex; gap:10px; align-items:flex-start;">
              <i class="bi bi-lock"></i>
              <span>
                Materi ini terbuka bagi peserta yang <strong>sudah disetujui admin</strong> pada pelatihan ini.
                <?= $saya ? ' Status pendaftaran kamu: ' . e($saya['status']) . '.' : ' Silakan daftar terlebih dahulu.' ?>
              </span>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <aside class="detail-side">
      <div class="card">
        <h3 style="font-size:14px; margin-bottom:14px;">Informasi</h3>
        <div class="side-info-row"><span class="k">Mulai</span><span><?= e(tanggal_indo($p['tanggal_mulai'])) ?></span></div>
        <div class="side-info-row"><span class="k">Selesai</span><span><?= e(tanggal_indo($p['tanggal_selesai'])) ?></span></div>
        <div class="side-info-row"><span class="k">Waktu</span><span><?= e($p['waktu'] ?: '-') ?></span></div>
        <div class="side-info-row"><span class="k">Lokasi</span><span><?= e($p['lokasi'] ?: '-') ?></span></div>
        <div class="side-info-row" style="border:none;"><span class="k">Kuota</span><span><?= (int) $p['kuota'] - $peserta ?> / <?= (int) $p['kuota'] ?></span></div>

        <div style="margin-top:18px;">
          <?php if ($isAdmin): ?>
            <a class="btn btn-primary btn-block" href="admin/pelatihan-peserta.php?id=<?= (int) $p['id'] ?>"><i class="bi bi-people"></i> Kelola Peserta</a>
          <?php elseif ($isAnggota): ?>
            <?php if ($saya): ?>
              <?= badge_status_peserta($saya['status']) ?>
              <p style="font-size:12.5px; color:var(--text-muted); margin-top:10px;">
                Terdaftar sejak <?= e(tanggal_indo($saya['dibuat_pada'])) ?>.
              </p>
            <?php elseif ($p['status'] === 'Dibuka' && $sisa > 0): ?>
              <form method="post">
                <?= Csrf::field() ?>
                <input type="hidden" name="pelatihan_id" value="<?= (int) $p['id'] ?>">
                <button class="btn btn-primary btn-block" type="submit"><i class="bi bi-pencil-square"></i> Daftar Peserta</button>
              </form>
            <?php elseif ($sisa <= 0): ?>
              <div class="alert alert-warn" style="margin:0;">Kuota sudah penuh.</div>
            <?php else: ?>
              <div class="alert alert-muted" style="margin:0;">Pendaftaran sudah ditutup.</div>
            <?php endif; ?>
          <?php else: ?>
            <a class="btn btn-primary btn-block" href="login.php">Masuk untuk Daftar</a>
          <?php endif; ?>
        </div>
      </div>
    </aside>
  </div>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>