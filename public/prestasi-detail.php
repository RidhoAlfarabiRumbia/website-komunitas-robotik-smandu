<?php
/**
 * Detail Prestasi (PRD 8.7). Terbuka untuk umum.
 */
require_once __DIR__ . '/../app/includes/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();
    Auth::requireRole('admin');

    $id = (int) ($_POST['id'] ?? 0);
    $p = Prestasi::detail($id);
    if (!$p) {
        flash_set('error', 'Prestasi tidak ditemukan.');
        redirect('prestasi.php');
    }
    Prestasi::setStatus($id, $p['status'] === 'Tayang' ? 'Disembunyikan' : 'Tayang');
    flash_set('success', 'Status prestasi diperbarui.');
    redirect('prestasi-detail.php?id=' . $id);
}

$p = Prestasi::detail($id);

if (!$p || ($p['status'] !== 'Tayang' && !Auth::isAdmin())) {
    flash_set('error', 'Prestasi tidak ditemukan.');
    redirect('prestasi.php');
}

$tim = Prestasi::tim($id);

$pageTitle = $p['nama'];
include __DIR__ . '/../app/includes/header.php';
include __DIR__ . '/../app/includes/navbar_anggota.php';
?>
<div class="main">
  <a class="back-link" href="prestasi.php"><i class="bi bi-arrow-left"></i> Kembali ke daftar prestasi</a>

  <div class="detail-wrap">
    <div class="detail-main">
      <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:14px;">
        <?php if ($p['hasil']): ?>
          <span class="badge badge-amber"><i class="bi bi-award"></i> <?= e($p['hasil']) ?></span>
        <?php endif; ?>
        <span class="badge badge-blue"><?= e($p['tingkat']) ?></span>
        <?php if ($p['status'] !== 'Tayang'): ?>
          <span class="badge badge-red">Disembunyikan</span>
        <?php endif; ?>
      </div>

      <h1 style="font-size:26px; margin-bottom:10px;"><?= e($p['nama']) ?></h1>
      <p style="color:var(--text-muted); margin-bottom:20px;">
        <?= e($p['lomba_nama'] ?? 'Tanpa nama lomba') ?> &middot; <?= e(tanggal_indo($p['tanggal'])) ?>
      </p>

      <?php if ($p['foto']): ?>
        <img src="<?= base_url(e($p['foto'])) ?>" alt="<?= e($p['nama']) ?>" style="width:100%; max-height:420px; object-fit:cover; border-radius:var(--radius-md); margin-bottom:22px;">
      <?php endif; ?>

      <?php if ($p['deskripsi']): ?>
        <p style="line-height:1.8; margin-bottom:24px;"><?= nl2br(e($p['deskripsi'])) ?></p>
      <?php endif; ?>

      <?php if ($tim): ?>
        <div class="card">
          <h3 style="font-size:15px; margin-bottom:14px;"><i class="bi bi-people"></i> Peraih Prestasi</h3>
          <div class="list-tight">
            <?php foreach ($tim as $t): ?>
              <div class="list-row">
                <div>
                  <div style="font-size:14px; font-weight:600;"><?= e($t['nama']) ?></div>
                  <?php if (!empty($t['kelas'])): ?>
                    <div style="font-size:12.5px; color:var(--text-muted);"><?= e($t['kelas']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <aside class="detail-side">
      <div class="card">
        <h3 style="font-size:14px; margin-bottom:14px;">Ringkasan</h3>
        <div class="side-info-row"><span class="k">Lomba</span><span><?= e($p['lomba_nama'] ?? '-') ?></span></div>
        <div class="side-info-row"><span class="k">Tingkat</span><span><?= e($p['tingkat']) ?></span></div>
        <div class="side-info-row"><span class="k">Hasil</span><span><?= e($p['hasil'] ?: '-') ?></span></div>
        <div class="side-info-row" style="border:none;"><span class="k">Tanggal</span><span><?= e(tanggal_indo($p['tanggal'])) ?></span></div>

        <?php if ($p['sertifikat']): ?>
          <a class="btn btn-outline btn-block" style="margin-top:16px;" href="<?= base_url(e($p['sertifikat'])) ?>" target="_blank" rel="noopener">
            <i class="bi bi-file-earmark-pdf"></i> Lihat Sertifikat
          </a>
        <?php endif; ?>

        <?php if (Auth::isAdmin()): ?>
          <form method="post" style="margin-top:8px;">
            <?= Csrf::field() ?>
            <input type="hidden" name="aksi" value="toggle">
            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
            <button class="btn <?= $p['status'] === 'Tayang' ? 'btn-outline' : 'btn-primary' ?> btn-block" type="submit">
              <i class="bi bi-eye<?= $p['status'] === 'Tayang' ? '-slash' : '' ?>"></i>
              <?= $p['status'] === 'Tayang' ? 'Sembunyikan' : 'Tampilkan Lagi' ?>
            </button>
          </form>
        <?php endif; ?>
      </div>
    </aside>
  </div>
</div>
<?php include __DIR__ . '/../app/includes/footer.php'; ?>