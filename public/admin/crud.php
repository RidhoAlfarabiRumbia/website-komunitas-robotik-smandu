<?php
require_once __DIR__ . '/../../app/includes/bootstrap.php';
require_once __DIR__ . '/../../app/core/CrudConfig.php';
Auth::requireRole('admin');

$config = crud_config();
$entity = $_GET['entity'] ?? 'anggota';
if (!isset($config[$entity])) $entity = 'anggota';
$cfg = $config[$entity];
$akunTypes = ['akun_username', 'password'];
/** @var Model $model */
$model = $cfg['model'];
$action = $_GET['action'] ?? 'list';
$id = (int) ($_GET['id'] ?? 0);

// Referensi tidak lagi punya halaman sendiri: dikelola lewat panel di halaman Materi.
if ($entity === 'referensi') {
    redirect('admin/crud.php?entity=materi');
}
$refPanel = ($entity === 'materi') && (($_GET['sub'] ?? '') === 'referensi');

// ---- Simpan referensi (panel Materi) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['sub_form'] ?? '') === 'referensi') {
    Csrf::verify();
    $refId = (int) ($_POST['id'] ?? 0);
    $refBack = 'admin/crud.php?entity=materi&sub=referensi&action=' . ($refId ? 'edit&id=' . $refId : 'create');
    $refData = [
        'nama' => trim($_POST['nama'] ?? ''),
        'url' => trim($_POST['url'] ?? ''),
        'deskripsi' => trim($_POST['deskripsi'] ?? ''),
    ];
    if ($refData['nama'] === '' || $refData['url'] === '') {
        flash_set('error', 'Nama dan URL referensi wajib diisi.');
        redirect($refBack);
    }
    if (!preg_match('#^https?://#i', $refData['url'])) {
        flash_set('error', 'URL referensi harus berupa tautan http/https.');
        redirect($refBack);
    }
    if ($refId) {
        Referensi::update($refId, $refData);
        flash_set('success', 'Referensi berhasil diperbarui.');
    } else {
        Referensi::create($refData);
        flash_set('success', 'Referensi berhasil ditambahkan.');
    }
    redirect('admin/crud.php?entity=materi&sub=referensi');
}

// ---- Toggle status (slider hero): POST + CSRF, sebelum blok simpan biasa ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'toggle_status' && $id) {
    Csrf::verify();
    if ($entity === 'hero' && !empty($cfg['toggle_status'])) {
        $row = $model::find($id);
        if ($row) {
            $baru = ($row['status'] ?? '') === 'Tampil' ? 'Sembunyi' : 'Tampil';
            $model::update($id, ['status' => $baru]);
            flash_set('success', 'Status slider diubah jadi ' . $baru . '.');
        }
    }
    redirect('admin/crud.php?entity=' . $entity);
}

// ---- Hapus ----
// WAJIB POST + CSRF. Sebelumnya hapus lewat GET sehingga bisa dipicu diam-diam
// dari halaman lain (mis. <img src="crud.php?entity=anggota&action=delete&id=1">).
// Blok ini harus DI ATAS blok simpan, karena blok simpan menangkap semua POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete' && $id) {
    Csrf::verify();
    if ($refPanel) {
        Referensi::delete($id);
        flash_set('success', 'Referensi berhasil dihapus.');
        redirect('admin/crud.php?entity=materi&sub=referensi');
    }
    $model::delete($id);
    flash_set($cfg['label'] . ' berhasil dihapus.');
    redirect('admin/crud.php?entity=' . $entity);
}

// ---- Simpan (create/update) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();
    $errors = [];
    foreach ($cfg['fields'] as $key => $f) {
        if (!empty($f['required']) && !in_array($f['type'], array_merge(['file', 'doc_file'], $akunTypes))) {
            $val = trim($_POST[$key] ?? '');
            if ($val === '') $errors[] = e($f['label']) . ' wajib diisi.';
        }
    }

    $isAkun = $entity === 'anggota';
    $postId = (int) ($_POST['id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $akun = null;
    if ($isAkun) {
        $akun = $postId ? User::findByAnggota($postId) : null;
        if ($username === '') $errors[] = 'Username wajib diisi.';
        elseif (User::usernameTaken($username, (int) ($akun['id'] ?? 0))) $errors[] = 'Username sudah dipakai akun lain.';
        if (!$akun && $password === '') $errors[] = 'Kata sandi wajib diisi.';
        if ($password !== '' && strlen($password) < 8) $errors[] = 'Kata sandi minimal 8 karakter.';
    }

    // Tautan video harus http/https (anti javascript: dll).
    foreach ($cfg['fields'] as $key => $f) {
        if ($f['type'] !== 'video') continue;
        if (!empty($f['show_when'])) {
            $ck = array_key_first($f['show_when']);
            if ((string) ($_POST[$ck] ?? '') !== $f['show_when'][$ck]) continue;
        }
        $url = trim($_POST[$key] ?? '');
        if ($url !== '' && !preg_match('#^https?://#i', $url)) {
            $errors[] = $f['label'] . ' harus berupa tautan http/https.';
        }
    }

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        $backAction = $postId ? 'edit&id=' . $postId : 'create';
        redirect('admin/crud.php?entity=' . $entity . '&action=' . $backAction);
    }

    // Field relasi multi-pilihan tidak disimpan di tabel utama.
    $relasiMulti = ['pelatihan' => [], 'anggota' => []];
    foreach ($cfg['fields'] as $key => $f) {
        if ($f['type'] === 'pelatihan_multi') {
            $relasiMulti['pelatihan'] = array_merge($relasiMulti['pelatihan'], array_map('intval', (array) ($_POST[$key] ?? [])));
        }
        if ($f['type'] === 'anggota_multi') {
            $relasiMulti['anggota'] = array_merge($relasiMulti['anggota'], array_map('intval', (array) ($_POST[$key] ?? [])));
        }
    }
    $relasiMulti['pelatihan'] = array_values(array_unique(array_filter($relasiMulti['pelatihan'])));
    $relasiMulti['anggota'] = array_values(array_unique(array_filter($relasiMulti['anggota'])));

    $data = [];
    foreach ($cfg['fields'] as $key => $f) {
        if (!empty($f['show_when'])) {
            $ck = array_key_first($f['show_when']);
            if ((string) ($_POST[$ck] ?? '') !== $f['show_when'][$ck]) {
                $data[$key] = null;
                continue;
            }
        }
        if ($f['type'] === 'file') {
            $path = handle_upload($key, $f['subfolder'] ?? $entity);
            if ($path) $data[$key] = $path;
        } elseif ($f['type'] === 'video') {
            $path = handle_video_upload($key . '_file', $f['subfolder'] ?? $entity);
            // Kalau tidak ada file, pakai URL yang diketik admin.
            $data[$key] = $path ?: trim($_POST[$key] ?? '');
        } elseif ($f['type'] === 'doc_file') {
            // Field bertanda restricted (mis. materi) disimpan DI LUAR document
            // root agar tidak bisa diunduh dengan menebak URL-nya; hanya bisa
            // diambil lewat berkas.php yang memeriksa hak akses.
            $sub = $f['subfolder'] ?? $entity;
            $path = !empty($f['restricted'])
                ? handle_restricted_upload($key, $sub, ['pdf', 'ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'], 10 * 1024 * 1024, 'PDF, PPT, DOC, XLS, TXT, atau ZIP', '10MB')
                : handle_doc_upload($key, $sub);
            if ($path) $data[$key] = $path;
        } elseif (in_array($f['type'], ['pelatihan_multi', 'anggota_multi', 'peraih'], true)) {
            // 'peraih' bukan kolom tabel — diproses khusus saat simpanTim prestasi.
            continue;
        } elseif (!in_array($f['type'], $akunTypes)) {
            $data[$key] = trim($_POST[$key] ?? '');
        }
    }

    // Validasi nilai select terhadap opsi yang dikonfigurasi (anti-tampering).
    foreach ($cfg['fields'] as $key => $f) {
        if ($f['type'] !== 'select' || !isset($data[$key])) continue;
        if (!in_array($data[$key], $f['options'], true)) {
            flash_set('error', e($f['label']) . ' tidak valid.');
            redirect('admin/crud.php?entity=' . $entity);
        }
    }

    if (!empty($_POST['id'])) {
        $savedId = (int) $_POST['id'];
        $model::update($savedId, $data);
        flash_set('success', $cfg['label'] . ' berhasil diperbarui.');
    } else {
        $savedId = $model::create($data);
        flash_set('success', $cfg['label'] . ' berhasil ditambahkan.');
    }

    // Simpan relasi many-to-many bila ada
    if ($entity === 'materi') {
        Materi::simpanPelatihan((int) $savedId, $relasiMulti['pelatihan']);
    }
    if ($entity === 'prestasi') {
        // Mode peraih: 'anggota' (checkbox) atau 'manual' (teks pisah koma).
        if (($_POST['peraih_mode'] ?? 'anggota') === 'manual') {
            $manualNames = array_values(array_filter(array_map('trim', explode(',', (string) ($_POST['peraih_manual'] ?? '')))));
            Prestasi::simpanTim((int) $savedId, [], $manualNames);
        } else {
            $anggotaIds = array_values(array_filter(array_map('intval', (array) ($_POST['anggota_ids'] ?? []))));
            Prestasi::simpanTim((int) $savedId, $anggotaIds);
        }
    }
    if ($isAkun) {
        $akun = User::findByAnggota($savedId);
        if ($akun) {
            $userData = ['username' => $username];
            if ($password !== '') $userData['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            User::update((int) $akun['id'], $userData);
        } else {
            User::createForAnggota($savedId, $username, $password);
        }
    }
    redirect('admin/crud.php?entity=' . $entity);
}

$editing = (!$refPanel && $action === 'edit' && $id) ? $model::find($id) : null;
// Prestasi lama: isi awal nama lomba dari relasi lomba_id bila lomba_teks masih kosong.
if ($editing && $entity === 'prestasi' && ($editing['lomba_teks'] ?? '') === '' && !empty($editing['lomba_id'])) {
    $editing['lomba_teks'] = Lomba::find((int) $editing['lomba_id'])['nama'] ?? '';
}
$showForm = (!$refPanel && $action === 'create') || $editing;
$rows = $entity === 'pelatihan'
    ? Pelatihan::semua()
    : ($entity === 'prestasi' ? Prestasi::semua() : $model::all());

$anggotaOptions = in_array($entity, ['karya', 'anggota', 'prestasi'], true) ? Anggota::all('nama ASC') : [];
$pelatihanOptions = $entity === 'materi' ? Pelatihan::semua() : [];

// Nilai terpilih untuk field multi-pilihan saat mode edit
$terpilihPelatihan = [];
$terpilihAnggota = [];
$peraihMode = 'anggota';
$peraihManualTeks = '';
if ($editing) {
    if ($entity === 'materi') {
        $terpilihPelatihan = array_map('intval', array_column(Materi::pelatihanTerkait((int) $editing['id']), 'id'));
    }
    if ($entity === 'prestasi') {
        $tim = Prestasi::tim((int) $editing['id']);
        $terpilihAnggota = array_values(array_filter(array_map('intval', array_column($tim, 'anggota_id'))));
        $manualNames = array_column(array_filter($tim, fn($t) => empty($t['anggota_id'])), 'nama');
        $peraihManualTeks = implode(', ', $manualNames);
        if (!$terpilihAnggota && $manualNames) $peraihMode = 'manual';
    }
}
$akunUsername = '';
$akunList = [];
if ($entity === 'anggota') {
    $akunUsername = $editing ? (User::findByAnggota((int) $editing['id'])['username'] ?? '') : '';
    foreach (User::all() as $u) {
        if ($u['anggota_id'] !== null) $akunList[(int) $u['anggota_id']] = $u['username'];
    }
}

$pageTitle = 'Kelola ' . $cfg['label'];
$listHasFoto = isset($cfg['fields']['foto']);
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
      <?php if ($refPanel): ?>
        <div><div class="eyebrow">MATERI</div><h1>Kelola Referensi Belajar</h1><p class="desc">Tautan sumber belajar yang tampil di halaman Materi untuk seluruh anggota.</p></div>
        <a class="btn btn-outline" href="?entity=materi"><i class="bi bi-arrow-left"></i> Kembali ke Materi</a>
      <?php else: ?>
        <div><div class="eyebrow">KELOLA</div><h1>Kelola <?= e($cfg['label']) ?></h1><p class="desc">Tambah, ubah, atau hapus data <?= strtolower(e($cfg['label'])) ?> pada portal.</p></div>
        <?php if (!$showForm): ?>
          <a class="btn btn-primary" href="?entity=<?= $entity ?>&action=create"><i class="bi bi-plus-circle"></i> Tambah</a>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <?php if ($msg = flash_get()): ?><div class="alert <?= flash_alert_class($msg['type']) ?>"><?= e($msg['text']) ?></div><?php endif; ?>

    <?php if ($refPanel): ?>
      <?php include __DIR__ . '/_referensi_panel.php'; ?>
    <?php elseif ($showForm): $hasWhen = false; ?>
      <form class="crud-form" method="post" enctype="multipart/form-data">
        <?= Csrf::field() ?>
        <?php if ($editing): ?><input type="hidden" name="id" value="<?= $editing['id'] ?>"><?php endif; ?>
        <div class="card crud-form-main">
          <div class="form-grid">
          <?php foreach ($cfg['fields'] as $key => $f):
            if ($key === 'foto' && !($cfg['foto_inline'] ?? false)) continue; // panel kanan, di luar kartu
            $val = $editing[$key] ?? '';
            if ($key === 'username') $val = $akunUsername;
            $full = in_array($f['type'], ['textarea', 'pelatihan_multi', 'anggota_multi', 'peraih']) ? ' field-full' : '';
            if ($key === 'foto' && ($cfg['foto_inline'] ?? false)) $full = ' field-full';
            $whenAttr = '';
            if (!empty($f['show_when'])) {
                $hasWhen = true;
                $wKey = array_key_first($f['show_when']);
                $whenAttr = ' data-when-show="' . e($wKey . ':' . $f['show_when'][$wKey]) . '"';
                $wCur = (string) ($editing[$wKey] ?? ($cfg['fields'][$wKey]['options'][0] ?? ''));
                if ($wCur !== $f['show_when'][$wKey]) $whenAttr .= ' style="display:none;"';
            }
          ?>
            <div class="field<?= $full ?>"<?= $whenAttr ?>>
              <label><?= e($f['label']) ?><?= !empty($f['required']) ? ' *' : '' ?></label>
              <?php if ($f['type'] === 'textarea'): ?>
                <textarea name="<?= $key ?>" rows="4" <?= !empty($f['required']) ? 'required' : '' ?>><?= e($val) ?></textarea>
              <?php elseif ($f['type'] === 'select'): ?>
                <select name="<?= $key ?>">
                  <?php foreach ($f['options'] as $opt): ?>
                    <option <?= $val === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                  <?php endforeach; ?>
                </select>
              <?php elseif ($f['type'] === 'anggota_select'): ?>
                <select name="<?= $key ?>">
                  <?php foreach ($anggotaOptions as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= (int) $val === (int) $a['id'] ? 'selected' : '' ?>><?= e($a['nama']) ?></option>
                  <?php endforeach; ?>
                </select>
              <?php elseif ($f['type'] === 'video'): ?>
                <input name="<?= $key ?>" value="<?= e($val) ?>" placeholder="https://youtube.com/">
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Kosongkan bila tidak ada video. Bisa juga mengunggah berkas video.</div>
                <input type="file" name="<?= $key ?>_file" accept=".mp4,.webm,.mov,.m4v" style="margin-top:6px;">
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Unggahan: MP4/WEBM/MOV/M4V, maks 40MB.</div>
                <?php if ($val): ?><div style="font-size:12px; color:var(--teal); margin-top:4px;">Saat ini: <?= e($val) ?></div><?php endif; ?>
              <?php elseif ($f['type'] === 'peraih'): ?>
                <div class="peraih-mode-row" style="display:flex; gap:18px; margin-bottom:8px;">
                  <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                    <input type="radio" name="peraih_mode" value="anggota" <?= $peraihMode === 'anggota' ? 'checked' : '' ?> onchange="peraihModeChange(this.value)"> Pilih dari anggota
                  </label>
                  <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                    <input type="radio" name="peraih_mode" value="manual" <?= $peraihMode === 'manual' ? 'checked' : '' ?> onchange="peraihModeChange(this.value)"> Manual
                  </label>
                </div>
                <div id="peraihAnggotaWrap"<?= $peraihMode === 'anggota' ? '' : ' style="display:none;"' ?>>
                  <?php if ($anggotaOptions): ?>
                    <div class="peraih-check-list">
                      <?php foreach ($anggotaOptions as $a): ?>
                        <label class="peraih-check-item">
                          <input type="checkbox" name="anggota_ids[]" value="<?= (int) $a['id'] ?>" <?= in_array((int) $a['id'], $terpilihAnggota, true) ? 'checked' : '' ?>>
                          <span><?= e($a['nama']) ?></span>
                        </label>
                      <?php endforeach; ?>
                    </div>
                    <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Centang satu atau lebih anggota.</div>
                  <?php else: ?>
                    <div style="font-size:12px; color:var(--text-muted);">Belum ada data anggota.</div>
                  <?php endif; ?>
                </div>
                <div id="peraihManualWrap"<?= $peraihMode === 'manual' ? '' : ' style="display:none;"' ?>>
                  <textarea name="peraih_manual" rows="3" placeholder="Budi Santoso, Siti Aminah"><?= e($peraihManualTeks) ?></textarea>
                  <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Pisahkan beberapa nama dengan koma.</div>
                </div>
                <script>
                function peraihModeChange(m){
                  document.getElementById('peraihAnggotaWrap').style.display = m === 'anggota' ? '' : 'none';
                  document.getElementById('peraihManualWrap').style.display = m === 'manual' ? '' : 'none';
                }
                </script>
              <?php elseif ($f['type'] === 'pelatihan_multi'): ?>
                <select name="<?= $key ?>[]" multiple size="5">
                  <?php foreach ($pelatihanOptions as $pl): ?>
                    <option value="<?= (int) $pl['id'] ?>" <?= in_array((int) $pl['id'], $terpilihPelatihan, true) ? 'selected' : '' ?>><?= e($pl['nama']) ?></option>
                  <?php endforeach; ?>
                </select>
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Tahan Ctrl / Cmd untuk memilih beberapa. Materi hanya bisa diunduh peserta pelatihan ini.</div>
              <?php elseif ($f['type'] === 'anggota_multi'): ?>
                <select name="<?= $key ?>[]" multiple size="5">
                  <?php foreach ($anggotaOptions as $a): ?>
                    <option value="<?= (int) $a['id'] ?>" <?= in_array((int) $a['id'], $terpilihAnggota, true) ? 'selected' : '' ?>><?= e($a['nama']) ?></option>
                  <?php endforeach; ?>
                </select>
              <?php elseif ($f['type'] === 'akun_username'): ?>
                <input name="<?= $key ?>" value="<?= e($val) ?>" required autocomplete="off">
              <?php elseif ($f['type'] === 'password'): ?>
                <input type="password" name="<?= $key ?>" <?= $editing ? '' : 'required' ?> minlength="8" autocomplete="new-password">
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;"><?= $editing ? 'Kosongkan bila tidak ingin mengubah kata sandi.' : 'Minimal 8 karakter.' ?></div>
              <?php elseif ($f['type'] === 'date'): ?>
                <input type="date" name="<?= $key ?>" value="<?= e($val ? date('Y-m-d', strtotime($val)) : '') ?>" <?= !empty($f['required']) ? 'required' : '' ?>>
              <?php elseif ($f['type'] === 'file'): ?>
                <input type="file" name="<?= $key ?>" accept=".jpg,.jpeg,.png,.webp">
                <span class="hint">Format JPG, JPEG, PNG, atau WEBP. Ukuran maksimal 2MB file yang melebihi batas akan ditolak.</span>
                <?php if ($val): ?>
                  <div style="margin-top:8px;"><img src="<?= base_url(e($val)) ?>" alt="Preview" style="display:block; width:100%; max-width:260px; height:auto; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--surface-2);"></div>
                  <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Saat ini: <?= e($val) ?></div>
                <?php endif; ?>
              <?php elseif ($f['type'] === 'doc_file'): ?>
                <input type="file" name="<?= $key ?>" accept=".pdf,.ppt,.pptx,.doc,.docx,.xls,.xlsx,.txt,.zip">
                <?php if (!empty($f['restricted'])): ?>
                  <span class="hint">Berkas ini terbatas: disimpan di luar document root dan hanya bisa diunduh anggota yang berhak lewat halaman Materi.</span>
                <?php endif; ?>
                <?php if ($val): ?><div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Saat ini: <?= e($val) ?></div><?php endif; ?>
              <?php else: ?>
                <input name="<?= $key ?>" value="<?= e($val) ?>" <?= !empty($f['required']) ? 'required' : '' ?>>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
          </div>
          <?php if ($hasWhen): ?>
          <script>
          document.querySelectorAll('.crud-form [data-when-show]').forEach(function (wrap) {
            var parts = wrap.getAttribute('data-when-show').split(':');
            var ctrl = document.querySelector('.crud-form [name="' + parts[0] + '"]');
            if (!ctrl) return;
            var sync = function () { wrap.style.display = ctrl.value === parts[1] ? '' : 'none'; };
            ctrl.addEventListener('change', sync);
            sync();
          });
          </script>
          <?php endif; ?>
          <div style="display:flex; gap:10px; margin-top:10px;">
            <button class="btn btn-primary" type="submit"><?= $editing ? 'Simpan Perubahan' : 'Tambah Data' ?></button>
            <a class="btn btn-outline" href="?entity=<?= $entity ?>">Batal</a>
          </div>
        </div>
        <?php if (isset($cfg['fields']['foto']) && !($cfg['foto_inline'] ?? false)): $valFoto = $editing['foto'] ?? ''; ?>
          <aside class="card crud-form-foto">
            <div class="field" style="margin-bottom:0;">
              <label><?= e($cfg['fields']['foto']['label']) ?></label>
              <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp">
              <span class="hint">Format JPG, JPEG, PNG, atau WEBP. Ukuran maksimal 2MB file yang melebihi batas akan ditolak.</span>
              <?php if ($valFoto): ?>
                <div style="margin-top:10px;"><img src="<?= base_url(e($valFoto)) ?>" alt="Preview" style="display:block; width:100%; height:170px; object-fit:cover; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--surface-2);"></div>
                <span class="hint">Saat ini: <?= e($valFoto) ?></span>
              <?php endif; ?>
            </div>
          </aside>
        <?php endif; ?>
      </form>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><?php if ($listHasFoto): ?><th style="width:112px;">Foto</th><?php endif; ?><?php foreach ($cfg['list'] as $col): ?><th><?= e(($cfg['column_labels'] ?? [])[$col] ?? $cfg['fields'][$col]['label'] ?? ucfirst($col)) ?></th><?php endforeach; ?><th style="width:120px;">Aksi</th></tr></thead>
          <tbody>
            <?php foreach ($rows as $row): ?>
              <tr>
                <?php if ($listHasFoto): ?>
                  <td><?php if (!empty($row['foto'])): ?><img src="<?= base_url(e($row['foto'])) ?>" alt="" loading="lazy" decoding="async" style="display:block; width:96px; height:96px; object-fit:cover; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--surface-2);"><?php else: ?><span style="color:var(--text-dim); font-size:12px;">-</span><?php endif; ?></td>
                <?php endif; ?>
                <?php foreach ($cfg['list'] as $col): ?>
                  <td><?= $col === 'username'
                          ? e($akunList[(int) $row['id']] ?? '-')
                          : e(trunc((string) ($row[$col] ?? '-'), 60)) ?></td>
                <?php endforeach; ?>
                <td>
                  <div class="row-actions">
                    <?php if (!empty($cfg['toggle_status'])): $tampil = ($row['status'] ?? '') === 'Tampil'; ?>
                      <form method="post" action="?entity=<?= $entity ?>&action=toggle_status&id=<?= (int) $row['id'] ?>" style="display:flex;">
                        <?= Csrf::field() ?>
                        <button type="submit" class="icon-btn<?= $tampil ? ' is-on' : '' ?>"
                                title="<?= $tampil ? 'Sembunyikan dari slider' : 'Tampilkan di slider' ?>"
                                aria-label="<?= $tampil ? 'Sembunyikan' : 'Tampilkan' ?> slider">
                          <i class="bi <?= $tampil ? 'bi-eye' : 'bi-eye-slash' ?>"></i>
                        </button>
                      </form>
                    <?php endif; ?>
                    <a class="icon-btn" href="?entity=<?= $entity ?>&action=edit&id=<?= (int) $row['id'] ?>"><i class="bi bi-pencil"></i></a>
                    <form method="post" action="?entity=<?= $entity ?>&action=delete&id=<?= (int) $row['id'] ?>" style="display:flex;"
                          onsubmit="return confirm('Hapus data ini?')">
                      <?= Csrf::field() ?>
                      <button type="submit" class="icon-btn" title="Hapus"><i class="bi bi-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="<?= count($cfg['list']) + ($listHasFoto ? 2 : 1) ?>"><div class="empty">Belum ada data.</div></td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
    <?php if ($entity === 'materi' && !$showForm && !$refPanel): ?>
      <div style="margin-top:18px;">
        <a class="btn btn-outline" href="?entity=materi&amp;sub=referensi"><i class="bi bi-link-45deg"></i> Kelola Referensi Belajar</a>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../../app/includes/footer.php'; ?>
