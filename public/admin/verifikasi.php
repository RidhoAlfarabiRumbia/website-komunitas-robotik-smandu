<?php
/**
 * Verifikasi Pendaftaran Anggota Baru (PRD 8.12).
 * Admin memeriksa dokumen Pakta Integritas (hanya admin yang boleh membuka
 * berkas), lalu memverifikasi atau menolak pendaftaran.
 */
require_once __DIR__ . '/../../app/includes/bootstrap.php';
Auth::requireRole('admin');

/**
 * Buat tautan pembuatan akun (token sekali pakai, berlaku 24 jam) untuk hasil
 * verifikasi dan kirimkan ke email pelamar. Tautan lama untuk email yang sama
 * otomatis tidak berlaku. true bila email sukses terkirim.
 */
function kirimTautanAkun(string $email, string $nama): bool
{
    PasswordReset::batalkanPending($email);

    $token = bin2hex(random_bytes(32));
    $emailHash = PasswordReset::emailHash($email);
    $berlaku = date('Y-m-d H:i:s', time() + 86400); // 24 jam
    PasswordReset::catat($email, null, hash('sha256', $token), $berlaku, $_SERVER['REMOTE_ADDR'] ?? null);

    $tautan = app_url('lengkapi-akun.php?token=' . rawurlencode($token));
    $judul = 'Pendaftaran Diverifikasi — Silakan Buat Akun di Komunitas Robotik';
    $isiHtml = '<p>Halo ' . e($nama) . ',</p>'
        . '<p>Kabar baik! Pendaftaran Anda di Komunitas Robotik telah diverifikasi. '
        . 'Langkah terakhir: silakan pilih nama pengguna (username) dan kata sandi '
        . 'untuk mengaktifkan akun Anda melalui tautan berikut:</p>'
        . '<p><a href="' . e($tautan) . '">' . e($tautan) . '</a></p>'
        . '<p>Tautan ini berlaku 24 jam dan hanya dapat digunakan sekali.</p>'
        . '<p style="font-size:13px;">Salam,<br>Komunitas Robotik SMA Negeri 2 Kota Ternate</p>';
    $isiTeks = "Halo " . $nama . ",\n\n"
        . "Kabar baik! Pendaftaran Anda di Komunitas Robotik telah diverifikasi.\n"
        . "Langkah terakhir: silakan pilih nama pengguna (username) dan kata sandi "
        . "untuk mengaktifkan akun Anda melalui tautan berikut:\n\n"
        . $tautan . "\n\n"
        . "Tautan ini berlaku 24 jam dan hanya dapat digunakan sekali.\n\n"
        . "Salam,\n"
        . "Komunitas Robotik SMA Negeri 2 Kota Ternate\n";
    return Mailer::send($email, $judul, $isiHtml, $isiTeks);
}

$filter = nilai_teks($_GET, 'status');
// Pencarian daftar; kata kunci ikut dibawa ke link detail & redirect.
$q = cari_kunci($_GET['q'] ?? $_POST['q'] ?? '');
$qs = cari_qs($q);            // untuk URL yang sudah punya query string
$qsBase = cari_qs($q, '?');   // untuk URL tanpa query string

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();

    $id = nilai_int($_POST, 'id');
    $aksi = nilai_teks($_POST, 'aksi');
    $p = Pendaftaran::find($id);
    if (!$p) {
        flash_set('error', 'Data pendaftaran tidak ditemukan.');
        redirect('admin/verifikasi.php' . $qsBase);
    }

    $catatan = nilai_teks($_POST, 'catatan');

    if ($aksi === 'verifikasi') {
        // Tautan buat-akun dikirim ke email yang diisi pelamar di formulir;
        // username & kata sandi ditetapkan pelamar sendiri lewat tautan tersebut.
        $email = strtolower(trim((string) ($p['email'] ?? '')));
        if ($email === '' || strlen($email) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash_set('error', 'Email pendaftaran belum terisi dengan benar, sehingga tautan pembuatan akun tidak bisa dikirim.');
            redirect('admin/verifikasi.php?id=' . $id . $qs);
        }

        // Keamanan: JANGAN pernah otomatis menempelkan akun ini ke anggota
        // existing berdasarkan pencocokan nama+kelas. Orang bisa mendaftar
        // memakai nama & kelas anggota asli lalu, saat admin memverifikasi,
        // akunnya terpasang ke anggota tersebut (account takeover). Satu-satunya
        // sumber kebenaran adalah $p['anggota_id'] yang sudah terisi dari
        // verifikasi sebelumnya.
        $anggotaId = (int) ($p['anggota_id'] ?? 0);

        $db = Database::connect();
        try {
            $db->beginTransaction();

            if (!$anggotaId) {
                $anggotaId = (int) Anggota::create([
                    'nama' => $p['nama'],
                    'kelas' => $p['kelas'],
                    'kontak' => $p['kontak'],
                    'deskripsi' => $p['alasan'] ?: 'Anggota baru terdaftar lewat formulir pendaftaran.',
                ]);
                if ($anggotaId <= 0) {
                    throw new RuntimeException('Gagal membuat data anggota.');
                }
            }

            if (!Pendaftaran::verifikasi($id, 'Diverifikasi', $catatan, $anggotaId)) {
                throw new RuntimeException('Gagal memperbarui data pendaftaran.');
            }
            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[VERIFIKASI] ' . $e->getMessage());
            flash_set('error', 'Verifikasi gagal disimpan. Silakan coba lagi.');
            redirect('admin/verifikasi.php?id=' . $id . $qs);
        }

        // Email dikirim setelah commit: kegagalan SMTP tidak boleh menggagalkan
        // verifikasi, cukup dicatat di log (tidak diekspos ke calon anggota).
        if (kirimTautanAkun($email, (string) $p['nama'])) {
            flash_set('success', 'Pendaftaran diverifikasi. Tautan pembuatan akun dikirim ke ' . $email . '.');
        } else {
            error_log('[VERIFIKASI] Gagal mengirim tautan pembuatan akun ke ' . $email);
            flash_set('error', 'Pendaftaran diverifikasi, tetapi email gagal terkirim. Periksa konfigurasi SMTP lalu gunakan tombol Kirim Ulang Tautan.');
        }
    } elseif ($aksi === 'kirim_ulang') {
        // Antisipasi email tidak terkirim (mis. kotak masuk penuh/layanan
        // email bermasalah): kirim ulang; tautan lama otomatis tidak berlaku.
        if ($p['status'] !== 'Diverifikasi') {
            flash_set('error', 'Kirim ulang hanya untuk pendaftaran yang sudah diverifikasi.');
            redirect('admin/verifikasi.php?id=' . $id . $qs);
        }
        $email = strtolower(trim((string) ($p['email'] ?? '')));
        if ($email === '' || strlen($email) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash_set('error', 'Email pendaftaran belum terisi dengan benar, tautan tidak bisa dikirim.');
            redirect('admin/verifikasi.php?id=' . $id . $qs);
        }
        if (User::findByAnggota((int) ($p['anggota_id'] ?? 0))) {
            flash_set('success', 'Akun pendaftaran ini sudah aktif. Tidak perlu kirim ulang.');
            redirect('admin/verifikasi.php?id=' . $id . $qs);
        }
        if (kirimTautanAkun($email, (string) $p['nama'])) {
            flash_set('success', 'Tautan baru dikirim ke ' . $email . '. Berlaku 24 jam.');
        } else {
            error_log('[VERIFIKASI] Gagal kirim ulang tautan buat akun ke ' . $email);
            flash_set('error', 'Email gagal terkirim. Periksa konfigurasi SMTP, lalu coba lagi.');
        }
    } elseif ($aksi === 'tolak') {
        if (Pendaftaran::verifikasi($id, 'Ditolak', $catatan ?: 'Dokumen tidak valid.')) {
            flash_set('success', 'Pendaftaran ditolak.');
        } else {
            flash_set('error', 'Pendaftaran gagal ditolak.');
        }
    } elseif ($aksi === 'hapus') {
        if (Pendaftaran::delete($id)) {
            flash_set('success', 'Data pendaftaran dihapus.');
        } else {
            flash_set('error', 'Data pendaftaran gagal dihapus.');
        }
    }

    redirect('admin/verifikasi.php' . $qsBase);
}

$daftar = Pendaftaran::semua($filter);
if ($q !== '') {
    $daftar = cari_filter($daftar, ['nama', 'kelas', 'kontak', 'email', 'alasan', 'catatan', 'nama_berkas'], $q);
}
$totalDaftar = count($daftar);
$perHalaman = 20;
$totalHalaman = max(1, (int) ceil($totalDaftar / $perHalaman));
$halaman = max(1, min($totalHalaman, nilai_int($_GET, 'page')));
$daftar = array_slice($daftar, ($halaman - 1) * $perHalaman, $perHalaman);
$detail = nilai_int($_GET, 'id') ? Pendaftaran::detail(nilai_int($_GET, 'id')) : null;
$akunDetail = $detail && !empty($detail['anggota_id'])
    ? User::findByAnggota((int) $detail['anggota_id'])
    : null;

$pageTitle = 'Verifikasi Pendaftaran';
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
    <?php include __DIR__ . '/../../app/includes/partial_profil_menu.php'; ?>
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
      <a class="btn btn-outline btn-sm" href="verifikasi.php?status=<?= e($filter) ?><?= e($qs) ?>" style="margin-bottom:14px;"><i class="bi bi-arrow-left"></i> Kembali</a>

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

              <div class="alert alert-info" style="margin-bottom:14px;">
                <i class="bi bi-envelope-paper"></i>
                Saat diverifikasi, data anggota dibuat dan tautan pembuatan akun dikirim ke email pelamar
                (<?= e($detail['email'] ?: 'belum terisi') ?>). Username &amp; kata sandi ditetapkan pelamar
                sendiri lewat tautan tersebut; tautan berlaku 24 jam dan hanya dipakai sekali.
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
            <?php
                $adaAkun = $akunDetail !== null;
                $emailPendaftaran = (string) ($detail['email'] ?? '');
                $emailPendaftaranValid = $emailPendaftaran !== '' && filter_var($emailPendaftaran, FILTER_VALIDATE_EMAIL);
                $tautanInfo = ($detail['status'] === 'Diverifikasi' && $emailPendaftaranValid && !$adaAkun)
                    ? PasswordReset::statusEmail($emailPendaftaran)
                    : null;
                $tautanTerpakai = $tautanInfo && !empty($tautanInfo['used_at']);
                $tautanKedaluwarsa = $tautanInfo
                    && empty($tautanInfo['used_at'])
                    && strtotime((string) $tautanInfo['expires_at']) <= time();
            ?>
            <?php if ($detail['status'] === 'Diverifikasi'): ?>
              <div class="card" style="margin-bottom:16px;">
                <h3 style="font-size:14px; margin-bottom:10px;"><i class="bi bi-envelope-paper"></i> Tautan Pembuatan Akun</h3>
                <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:12px;">
                  <?php if ($adaAkun): ?>
                    Akun sudah aktif dengan username <strong><?= e($akunDetail['username']) ?></strong>.
                  <?php elseif (!$emailPendaftaranValid): ?>
                    Email pendaftaran belum terisi dengan benar, tautan tidak bisa dikirim.
                  <?php elseif (!$tautanInfo): ?>
                    Tautan belum pernah dibuat.
                  <?php elseif ($tautanTerpakai): ?>
                    Tautan terakhir sudah digunakan.
                  <?php elseif ($tautanKedaluwarsa): ?>
                    Tautan terakhir sudah kedaluwarsa.
                  <?php else: ?>
                    Tautan aktif — dikirim <?= e(tanggal_indo((string) ($tautanInfo['created_at'] ?? ''))) ?>, berlaku sampai <?= e(tanggal_indo((string) $tautanInfo['expires_at'])) ?>.
                  <?php endif; ?>
                </p>
                <?php if (!$adaAkun && $emailPendaftaranValid): ?>
                  <form method="post">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="id" value="<?= (int) $detail['id'] ?>">
                    <button class="btn btn-outline btn-sm" type="submit" name="aksi" value="kirim_ulang"
                            onclick="return konfirmasiKirim(event, 'Kirim ulang tautan pembuatan akun? Tautan lama tidak berlaku lagi.', 'Ya, kirim ulang')">
                      <i class="bi bi-send"></i> Kirim Ulang Tautan
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            <?php endif; ?>
            <form method="post" class="card">
              <?= Csrf::field() ?>
              <input type="hidden" name="id" value="<?= (int) $detail['id'] ?>">
              <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:12px;">Pendaftaran ini sudah diproses.</p>
              <button class="btn btn-danger-outline btn-block" type="submit" name="aksi" value="hapus"
                      onclick="return konfirmasiKirim(event, 'Hapus data pendaftaran ini?', 'Ya, hapus')">
                <i class="bi bi-trash"></i> Hapus Data
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <form method="get" style="margin-bottom:16px;">
        <div style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
          <div class="field" style="max-width:220px; margin:0;">
            <label>Status</label>
            <select name="status" onchange="this.form.submit()">
              <option value="">Semua status</option>
              <?php foreach (['Baru', 'Diverifikasi', 'Ditolak'] as $s): ?>
                <option value="<?= $s ?>" <?= $filter === $s ? 'selected' : '' ?>><?= e($s) ?> (<?= Pendaftaran::hitungStatus($s) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field" style="flex:1 1 240px; min-width:220px; margin:0;">
            <label>Cari</label>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Nama, kelas, kontak, atau email">
          </div>
          <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
          <?php if ($q !== ''): ?><a class="btn btn-outline" href="?status=<?= e($filter) ?>">Reset</a><?php endif; ?>
        </div>
      </form>
      <div class="cari-info"><?= $q === '' ? e($totalDaftar . ' data') : e('Menampilkan ' . count($daftar) . ' dari ' . $totalDaftar . ' data') ?></div>

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
                <td><a class="btn btn-outline btn-sm" href="?id=<?= (int) $r['id'] ?><?= $filter ? '&status=' . e($filter) : '' ?><?= e($qs) ?>">Periksa</a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$daftar): ?><tr><td colspan="6"><div class="empty"><?= $q !== '' ? 'Data tidak ditemukan.' : 'Belum ada pendaftaran.' ?></div></td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
      <?= paginasi_bar($halaman, $totalHalaman, ['status' => $filter, 'q' => $q]) ?>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../../app/includes/footer.php'; ?>