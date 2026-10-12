<?php
/**
 * Verifikasi Pendaftaran Anggota Baru (PRD 8.12).
 * Admin memeriksa dokumen Pakta Integritas (hanya admin yang boleh membuka
 * berkas), lalu memverifikasi atau menolak pendaftaran.
 */
require_once __DIR__ . '/../../app/includes/bootstrap.php';
Auth::requireRole('admin');

$filter = $_GET['status'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();

    $id = (int) ($_POST['id'] ?? 0);
    $aksi = $_POST['aksi'] ?? '';
    $p = Pendaftaran::find($id);
    if (!$p) {
        flash_set('error', 'Data pendaftaran tidak ditemukan.');
        redirect('admin/verifikasi.php');
    }

    $catatan = trim($_POST['catatan'] ?? '');

    if ($aksi === 'verifikasi') {
        // Buat akun anggota + login sehingga anggota bisa langsung masuk
        $username = trim($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '') {
            flash_set('error', 'Username wajib diisi untuk mengaktifkan akun.');
            redirect('admin/verifikasi.php?id=' . $id);
        }
        if (User::usernameTaken($username)) {
            flash_set('error', 'Username sudah dipakai akun lain.');
            redirect('admin/verifikasi.php?id=' . $id);
        }
        if (strlen($password) < 8) {
            flash_set('error', 'Kata sandi minimal 8 karakter.');
            redirect('admin/verifikasi.php?id=' . $id);
        }

        // Keamanan: JANGAN pernah otomatis menempelkan akun ini ke anggota
        // existing berdasarkan pencocokan nama+kelas. Orang bisa mendaftar
        // memakai nama & kelas anggota asli lalu, saat admin memverifikasi,
        // username+sandi miliknya terpasang ke akun anggota tersebut (account
        // takeover). Satu-satunya sumber kebenaran adalah $p['anggota_id'] yang
        // sudah terisi dari verifikasi sebelumnya.
        $anggotaId = (int) ($p['anggota_id'] ?? 0);
        if (!$anggotaId) {
            $anggotaId = (int) Anggota::create([
                'nama' => $p['nama'],
                'kelas' => $p['kelas'],
                'fokus' => 'Robotika',
                'kontak' => $p['kontak'],
                'deskripsi' => $p['alasan'] ?: 'Anggota baru terdaftar lewat formulir pendaftaran.',
            ]);
        }

        if (!User::findByAnggota($anggotaId)) {
            User::createForAnggota($anggotaId, $username, $password);
        }

        Pendaftaran::verifikasi($id, 'Diverifikasi', $catatan, $anggotaId);
        flash_set('success', 'Pendaftaran diverifikasi. Akun anggota sudah aktif.');
    } elseif ($aksi === 'tolak') {
        Pendaftaran::verifikasi($id, 'Ditolak', $catatan ?: 'Dokumen tidak valid.');
        flash_set('success', 'Pendaftaran ditolak.');
    } elseif ($aksi === 'hapus') {
        Pendaftaran::delete($id);
        flash_set('success', 'Data pendaftaran dihapus.');
    }

    redirect('admin/verifikasi.php');
}

$daftar = Pendaftaran::semua($filter);
$detail = (int) ($_GET['id'] ?? 0) ? Pendaftaran::detail((int) $_GET['id']) : null;

$pageTitle = 'Verifikasi Pendaftaran';
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
        <div class="eyebrow">MODERASI</div>
        <h1>Verifikasi Pendaftaran</h1>
        <p class="desc">Periksa Pakta Integritas calon anggota sebelum mengaktifkan akunnya.</p>
      </div>
    </div>

    <?php if ($msg = flash_get()): ?><div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div><?php endif; ?>

    <?php if ($detail): ?>
      <a class="btn btn-outline btn-sm" href="verifikasi.php?status=<?= e($filter) ?>" style="margin-bottom:14px;"><i class="bi bi-arrow-left"></i> Kembali</a>

      <div class="two-col">
        <div class="card">
          <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; align-items:center;">
            <strong style="font-size:15px;"><?= e($detail['nama']) ?></strong>
            <?= badge_status_pendaftaran($detail['status']) ?>
          </div>

          <div class="side-info-row"><span class="k">Kelas</span><span><?= e($detail['kelas']) ?></span></div>
          <div class="side-info-row"><span class="k">Kontak</span><span><?= e($detail['kontak']) ?></span></div>
          <div class="side-info-row"><span class="k">Email</span><span><?= e($detail['email'] ?: '-') ?></span></div>
          <div class="side-info-row"><span class="k">Dikirim</span><span><?= e(tanggal_indo($detail['dibuat_pada'])) ?></span></div>
          <?php if ($detail['diverifikasi_pada']): ?>
            <div class="side-info-row" style="border:none;"><span class="k">Diproses</span><span><?= e(tanggal_indo($detail['diverifikasi_pada'])) ?></span></div>
          <?php endif; ?>

          <?php if ($detail['alasan']): ?>
            <p style="font-size:13px; color:var(--text-muted); margin-top:16px;"><?= e($detail['alasan']) ?></p>
          <?php endif; ?>

          <?php if ($detail['catatan']): ?>
            <div class="alert alert-muted" style="margin-top:14px;">Catatan: <?= e($detail['catatan']) ?></div>
          <?php endif; ?>
        </div>

        <div>
          <div class="card" style="margin-bottom:16px;">
            <h3 style="font-size:14px; margin-bottom:12px;"><i class="bi bi-file-earmark-lock"></i> Pakta Integritas</h3>
            <?php if ($detail['pakta_integritas']): ?>
              <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:12px; word-break:break-all;"><?= e($detail['nama_berkas'] ?: $detail['pakta_integritas']) ?></p>
              <a class="btn btn-outline btn-sm btn-block" href="<?= base_url('berkas.php?jenis=pakta&id=' . (int) $detail['id']) ?>" target="_blank" rel="noopener">
                <i class="bi bi-eye"></i> Lihat Dokumen
              </a>
            <?php else: ?>
              <p style="font-size:12.5px; color:var(--text-muted);">Dokumen tidak terlampir.</p>
            <?php endif; ?>
          </div>

          <?php if ($detail['status'] === 'Baru'): ?>
            <form class="card" method="post">
              <?= Csrf::field() ?>
              <input type="hidden" name="id" value="<?= (int) $detail['id'] ?>">
              <h3 style="font-size:14px; margin-bottom:12px;">Verifikasi</h3>

              <div class="field">
                <label>Username akun <span class="req">*</span></label>
                <input name="username" value="<?= e(strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $detail['nama']))) ?>" maxlength="50" autocomplete="off">
              </div>
              <div class="field">
                <label>Kata sandi <span class="req">*</span></label>
                <input type="password" name="password" minlength="8" autocomplete="new-password">
                <span class="hint">Minimal 8 karakter. Sampaikan ke calon anggota lewat kanal aman.</span>
              </div>
              <div class="field">
                <label>Catatan (opsional)</label>
                <textarea name="catatan" rows="2"><?= e($detail['catatan'] ?: '') ?></textarea>
              </div>

              <div class="form-actions" style="justify-content:stretch; gap:8px;">
                <button class="btn btn-primary" type="submit" name="aksi" value="verifikasi" style="flex:1;"><i class="bi bi-check-lg"></i> Verifikasi</button>
                <button class="btn btn-danger-outline" type="submit" name="aksi" value="tolak" style="flex:1;">Tolak</button>
              </div>
            </form>
          <?php else: ?>
            <form method="post" class="card">
              <?= Csrf::field() ?>
              <input type="hidden" name="id" value="<?= (int) $detail['id'] ?>">
              <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:12px;">Pendaftaran ini sudah diproses.</p>
              <button class="btn btn-danger-outline btn-block" type="submit" name="aksi" value="hapus" onclick="return confirm('Hapus data pendaftaran ini?')">
                <i class="bi bi-trash"></i> Hapus Data
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <form method="get" style="margin-bottom:16px;">
        <div class="field" style="max-width:220px; margin:0;">
          <label>Status</label>
          <select name="status" onchange="this.form.submit()">
            <option value="">Semua status</option>
            <?php foreach (['Baru', 'Diverifikasi', 'Ditolak'] as $s): ?>
              <option value="<?= $s ?>" <?= $filter === $s ? 'selected' : '' ?>><?= e($s) ?> (<?= Pendaftaran::hitungStatus($s) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>

      <div class="table-wrap">
        <table>
          <thead><tr><th>Nama</th><th>Kelas</th><th>Kontak</th><th>Status</th><th>Dikirim</th><th style="width:100px;">Aksi</th></tr></thead>
          <tbody>
            <?php foreach ($daftar as $r): ?>
              <tr>
                <td><?= e($r['nama']) ?></td>
                <td><?= e($r['kelas']) ?></td>
                <td><?= e($r['kontak']) ?></td>
                <td><?= badge_status_pendaftaran($r['status']) ?></td>
                <td style="font-size:12.5px; color:var(--text-muted);"><?= e(tanggal_indo($r['dibuat_pada'])) ?></td>
                <td><a class="btn btn-outline btn-sm" href="?id=<?= (int) $r['id'] ?><?= $filter ? '&status=' . e($filter) : '' ?>">Periksa</a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$daftar): ?><tr><td colspan="6"><div class="empty">Belum ada pendaftaran.</div></td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../../app/includes/footer.php'; ?>