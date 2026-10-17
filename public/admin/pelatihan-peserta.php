<?php
/**
 * Kelola Peserta Pelatihan (PRD 8.4).
 * Admin menyetujui atau menolak peserta sesuai kuota. Status "Disetujui" inilah
 * yang membuka akses materi untuk anggota tersebut.
 */
require_once __DIR__ . '/../../app/includes/bootstrap.php';
Auth::requireRole('admin');

$id = (int) ($_GET['id'] ?? 0);
$filterPelatihan = $id;
$filterStatus = $_GET['status'] ?? '';
// Pencarian daftar; kata kunci ikut dibawa ke filter & redirect.
$q = cari_kunci($_GET['q'] ?? $_POST['q'] ?? '');
$qs = cari_qs($q);            // untuk URL yang sudah punya query string
$qsBase = cari_qs($q, '?');   // untuk URL tanpa query string

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();

    $aksi = $_POST['aksi'] ?? '';
    $pesertaId = (int) ($_POST['peserta_id'] ?? 0);
    $row = PelatihanPeserta::find($pesertaId);
    if (!$row) {
        flash_set('error', 'Data peserta tidak ditemukan.');
        redirect('admin/pelatihan-peserta.php' . ($id ? '?id=' . $id . $qs : $qsBase));
    }

    $redirectKe = 'admin/pelatihan-peserta.php?id=' . (int) $row['pelatihan_id'] . $qs;

    if ($aksi === 'setuju') {
        $p = Pelatihan::find((int) $row['pelatihan_id']);
        $terpakai = Pelatihan::jumlahDisetujui((int) $row['pelatihan_id']);
        if ($p && Pelatihan::sisaKuota($p, $terpakai) <= 0) {
            flash_set('error', 'Kuota pelatihan sudah penuh. Naikkan kuota terlebih dahulu.');
            redirect($redirectKe);
        }
        PelatihanPeserta::setStatus($pesertaId, 'Disetujui');
        flash_set('success', 'Peserta disetujui. Materi pelatihan kini terbuka untuknya.');
    } elseif ($aksi === 'tolak') {
        PelatihanPeserta::setStatus($pesertaId, 'Ditolak');
        flash_set('success', 'Peserta ditolak.');
    } elseif ($aksi === 'hapus') {
        PelatihanPeserta::delete($pesertaId);
        flash_set('success', 'Data peserta dihapus.');
    }

    redirect($redirectKe);
}

$pelatihan = $id ? Pelatihan::detail($id) : null;
if ($id && !$pelatihan) {
    flash_set('error', 'Pelatihan tidak ditemukan.');
    redirect('admin/pelatihan-peserta.php' . $qsBase);
}

$semuaPelatihan = Pelatihan::semua();

// Daftar peserta: seluruh pelatihan, atau hanya satu pelatihan terpilih
if ($pelatihan) {
    $peserta = Pelatihan::peserta($id, $filterStatus);
} else {
    $peserta = $filterStatus ? Pelatihan::semuaPeserta($filterStatus) : Pelatihan::semuaPeserta();
}
$totalPeserta = count($peserta);
if ($q !== '') {
    // Kolom 'pelatihan_nama' hanya ada pada daftar lintas pelatihan.
    $peserta = cari_filter($peserta, ['nama', 'kontak', 'kelas', 'asal', 'pelatihan_nama', 'catatan'], $q);
}

$pageTitle = 'Peserta Pelatihan';
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
      <div>
        <div class="eyebrow">MODERASI</div>
        <h1>Peserta Pelatihan</h1>
        <p class="desc">Setujui peserta sesuai kuota. Hanya peserta disetujui yang dapat mengunduh materi pelatihan.</p>
      </div>
      <a class="btn btn-outline" href="crud.php?entity=pelatihan"><i class="bi bi-mortarboard"></i> Kelola Pelatihan</a>
    </div>

    <?php if ($msg = flash_get()): ?><div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div><?php endif; ?>

    <form method="get" style="display:flex; gap:10px; align-items:flex-end; margin-bottom:16px; flex-wrap:wrap;">
      <div class="field" style="margin:0; min-width:260px;">
        <label>Pelatihan</label>
        <select name="id" onchange="this.form.submit()">
          <option value="0">Semua pelatihan</option>
          <?php foreach ($semuaPelatihan as $pl): ?>
            <option value="<?= (int) $pl['id'] ?>" <?= $id === (int) $pl['id'] ? 'selected' : '' ?>>
              <?= e($pl['nama']) ?> (<?= (int) $pl['jumlah_disetujui'] ?>/<?= (int) $pl['kuota'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field" style="margin:0; min-width:170px;">
        <label>Status</label>
        <select name="status" onchange="this.form.submit()">
          <option value="">Semua status</option>
          <?php foreach (['Pending', 'Disetujui', 'Ditolak'] as $s): ?>
            <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= e($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field" style="margin:0; flex:1 1 240px; min-width:220px;">
        <label>Cari</label>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Nama, kontak, asal, atau catatan">
      </div>
      <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
      <?php if ($q !== ''): ?><a class="btn btn-outline" href="?id=<?= (int) $id ?>&status=<?= e($filterStatus) ?>">Reset</a><?php endif; ?>
      <span class="cari-meta"><?= $q === '' ? e($totalPeserta . ' data') : e('Menampilkan ' . count($peserta) . ' dari ' . $totalPeserta . ' data') ?></span>
    </form>

    <?php if ($pelatihan): ?>
      <div class="card" style="margin-bottom:18px;">
        <div style="display:flex; gap:6px; flex-wrap:wrap; align-items:center;">
          <strong style="font-size:14px;"><?= e($pelatihan['nama']) ?></strong>
          <span class="badge badge-teal"><?= e(tanggal_rentang($pelatihan['tanggal_mulai'], $pelatihan['tanggal_selesai'])) ?></span>
          <span class="badge <?= $pelatihan['akses'] === 'Umum' ? 'badge-blue' : 'badge-amber' ?>"><?= e($pelatihan['akses']) ?></span>
          <span class="badge badge-muted"><?= (int) $pelatihan['jumlah_disetujui'] ?> disetujui / <?= (int) $pelatihan['kuota'] ?> kuota</span>
        </div>
      </div>
    <?php endif; ?>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Nama</th><th>Kontak</th><th>Kelas</th><th>Pelatihan</th><th>Status</th><th>Daftar</th><th style="width:170px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($peserta as $r): ?>
            <tr>
              <td><?= e($r['nama']) ?><?= empty($r['anggota_id']) ? ' <span class="badge badge-muted">Umum</span>' : '' ?></td>
              <td><?= e($r['kontak'] ?: '-') ?></td>
              <td><?= e($r['kelas'] ?: '-') ?></td>
              <td><?= e($r['pelatihan_nama'] ?? '-') ?></td>
              <td><?= badge_status_peserta($r['status']) ?></td>
              <td style="font-size:12.5px; color:var(--text-muted);"><?= e(tanggal_indo($r['dibuat_pada'])) ?></td>
              <td>
                <div class="row-actions">
                  <?php if ($r['status'] !== 'Disetujui'): ?>
                    <form method="post" style="display:inline;">
                      <?= Csrf::field() ?>
                      <input type="hidden" name="aksi" value="setuju">
                      <input type="hidden" name="peserta_id" value="<?= (int) $r['id'] ?>">
                      <button class="icon-btn" type="submit" title="Setujui"><i class="bi bi-check-lg"></i></button>
                    </form>
                  <?php endif; ?>
                  <?php if ($r['status'] !== 'Ditolak'): ?>
                    <form method="post" style="display:inline;">
                      <?= Csrf::field() ?>
                      <input type="hidden" name="aksi" value="tolak">
                      <input type="hidden" name="peserta_id" value="<?= (int) $r['id'] ?>">
                      <button class="icon-btn" type="submit" title="Tolak"><i class="bi bi-x-lg"></i></button>
                    </form>
                  <?php endif; ?>
                  <form method="post" style="display:inline;">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="peserta_id" value="<?= (int) $r['id'] ?>">
                    <button class="icon-btn" type="submit" title="Hapus" onclick="return confirm('Hapus peserta ini?')"><i class="bi bi-trash"></i></button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$peserta): ?><tr><td colspan="7"><div class="empty"><?= $q !== '' ? 'Data tidak ditemukan.' : 'Belum ada peserta.' ?></div></td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../app/includes/footer.php'; ?>