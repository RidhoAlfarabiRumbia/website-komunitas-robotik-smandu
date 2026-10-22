<?php
// Logika CRUD (validasi, simpan, hapus, toggle status) + pengumpulan data untuk
// form. Dipakai bersama oleh halaman admin (public/admin/crud.php) dan halaman
// fitur publik (karya.php, galeri.php, dst) ketika admin mengelola konten.
//
// WAJIB dipanggil SEBELUM header.php (semua redirect di sini masih boleh
// mengirim header).
//
// Variabel yang harus diisi halaman induk sebelum include:
//   $crudEntitas  entitas yang dikelola (kunci crud_config()), mis. 'karya'
//   $crudPath     path halaman tanpa query, mis. 'karya.php' atau 'admin/crud.php'
//   $crudParam    nama parameter GET penanda mode kelola: 'manage' di halaman
//                 fitur publik, default 'entity' untuk halaman admin
//   $crudReferensi  true hanya di halaman yang punya panel Referensi
//                  (admin/crud.php). Default false.
//
// Menghasilkan variabel untuk form/daftar: $crudQs (query action per item),
// $crudBase (URL kembali lengkap), $crudCfg, $crudModel, $rows, $editing,
// $showForm, $refPanel, $q, $qs, $searchCols, $totalRows, $listHasFoto,
// $akunList, $akunUsername, $anggotaOptions, $pelatihanOptions, $terpilihPelatihan,
// $terpilihAnggota, $peraihMode, $peraihManualTeks.

// Halaman fitur publik boleh diakses semua orang, tapi partial ini hanya untuk
// admin. Halaman induk sudah memeriksa; ini pengaman lapis kedua.
if (!Auth::isAdmin()) {
    http_response_code(403);
    exit('Akses ditolak.');
}

// crud_config() adalah fungsi, bukan kelas, jadi tidak ikut autoload.
require_once __DIR__ . '/../core/CrudConfig.php';

$crudReferensi = $crudReferensi ?? false;

// Nama parameter GET yang dipakai halaman untuk menandai mode kelola:
// 'entity' di halaman admin, 'manage' di halaman fitur publik.
$crudParam = $crudParam ?? 'entity';
$crudPath = $crudPath ?? 'admin/crud.php';

$config = crud_config();
$crudEntitas = $crudEntitas ?? ($_GET['entity'] ?? 'anggota');
// Entitas harus berupa string yang dikenal: URL seperti ?entity[]=karya membuat
// $crudEntitas berupa array dan isset($config[...]) akan melempar TypeError.
if (!is_string($crudEntitas) || !isset($config[$crudEntitas])) $crudEntitas = 'anggota';
// Action form per item (query saja) — aman dipakai di halaman mana pun.
$crudQs = '?' . $crudParam . '=' . $crudEntitas;
// Tujuan redirect setelah aksi CRUD.
$crudBase = $crudPath . $crudQs;
// URL entitas 'materi' di halaman yang sama (dipakai panel Referensi).
$crudBaseMateri = $crudPath . '?' . $crudParam . '=materi';
$crudCfg = $config[$crudEntitas];
$akunTypes = ['akun_username', 'password'];
/** @var Model $crudModel */
$crudModel = $crudCfg['model'];
$action = $_GET['action'] ?? 'list';
$id = nilai_int($_GET, 'id');

// Pencarian daftar: kata kunci ikut dibawa ke link aksi, form, dan redirect
// supaya admin tidak perlu mengetiknya lagi setelah simpan/batal.
$q = cari_kunci($_GET['q'] ?? $_POST['q'] ?? '');
$qs = cari_qs($q);

// Referensi tidak punya halaman sendiri: dikelola lewat panel di halaman Materi.
if ($crudEntitas === 'referensi' && $crudReferensi) {
    redirect($crudBaseMateri);
}
$refPanel = $crudReferensi
    && ($crudEntitas === 'materi')
    && (($_GET['sub'] ?? '') === 'referensi');

// ---- Simpan referensi (panel Materi) ----
if ($crudReferensi && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['sub_form'] ?? '') === 'referensi') {
    Csrf::verify();
    $refId = nilai_int($_POST, 'id');
    $refBack = $crudBaseMateri . '&sub=referensi&action=' . ($refId ? 'edit&id=' . $refId : 'create') . $qs;
    $refData = [
        'nama' => nilai_teks($_POST, 'nama'),
        'url' => nilai_teks($_POST, 'url'),
        'deskripsi' => nilai_teks($_POST, 'deskripsi'),
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
    redirect($crudBaseMateri . '&sub=referensi' . $qs);
}

// ---- Toggle status (slider hero): POST + CSRF, sebelum blok simpan biasa ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'toggle_status' && $id) {
    Csrf::verify();
    if ($crudEntitas === 'hero' && !empty($crudCfg['toggle_status'])) {
        $row = $crudModel::find($id);
        if ($row) {
            $baru = ($row['status'] ?? '') === 'Tampil' ? 'Sembunyi' : 'Tampil';
            $crudModel::update($id, ['status' => $baru]);
            flash_set('success', 'Status slider diubah jadi ' . $baru . '.');
        }
    }
    redirect($crudBase . $qs);
}

// ---- Hapus ----
// WAJIB POST + CSRF. Hapus lewat GET bisa dipicu diam-diam dari halaman
// lain (mis. <img src="...?manage=karya&action=delete&id=1">).
// Blok ini harus DI ATAS blok simpan, karena blok simpan menangkap semua POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete' && $id) {
    Csrf::verify();
    if ($refPanel) {
        Referensi::delete($id);
        flash_set('success', 'Referensi berhasil dihapus.');
redirect($crudBaseMateri . '&sub=referensi' . $qs);
    }
    // Akun login ikut dihapus (lapisan pengaman: users.anggota_id sudah memakai
    // ON DELETE CASCADE di database).
    if ($crudEntitas === 'anggota') {
        User::deleteByAnggota($id);
    }
    $crudModel::delete($id);
    flash_set($crudCfg['label'] . ' berhasil dihapus.');
    redirect($crudBase . $qs);
}

// ---- Simpan (create/update) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();
    $errors = [];
    foreach ($crudCfg['fields'] as $key => $f) {
        if (!empty($f['required']) && !in_array($f['type'], array_merge(['file', 'doc_file'], $akunTypes))) {
            $val = nilai_teks($_POST, $key);
            if ($val === '') $errors[] = e($f['label']) . ' wajib diisi.';
        }
    }

    $isAkun = $crudEntitas === 'anggota';
    $postId = nilai_int($_POST, 'id');
    $username = nilai_teks($_POST, 'username');
    $password = nilai_teks($_POST, 'password', false);
    $akun = null;
    if ($isAkun) {
        $akun = $postId ? User::findByAnggota($postId) : null;
        if ($username === '') $errors[] = 'Username wajib diisi.';
        elseif (User::usernameTaken($username, (int) ($akun['id'] ?? 0))) $errors[] = 'Username sudah dipakai akun lain.';
        if (!$akun && $password === '') $errors[] = 'Kata sandi wajib diisi.';
        if ($password !== '' && strlen($password) < 8) $errors[] = 'Kata sandi minimal 8 karakter.';
    }

    // Tautan wajib http/https (anti javascript: dll). Berlaku untuk field
    // bertipe 'video' (video kegiatan/galeri/prestasi) dan field teks yang
    // ditandai 'link' => true (link_demo karya, link_url materi).
    foreach ($crudCfg['fields'] as $key => $f) {
        if ($f['type'] !== 'video' && empty($f['link'])) continue;
        if (!empty($f['show_when'])) {
            $ck = array_key_first($f['show_when']);
            if (nilai_teks($_POST, $ck, false) !== $f['show_when'][$ck]) continue;
        }
        $url = nilai_teks($_POST, $key);
        if ($url !== '' && !preg_match('#^https?://#i', $url)) {
            $errors[] = $f['label'] . ' harus berupa tautan http/https.';
        }
    }

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        $backAction = $postId ? 'edit&id=' . $postId : 'create';
        redirect($crudBase . '&action=' . $backAction . $qs);
    }

    // Field relasi multi-pilihan tidak disimpan di tabel utama.
    $relasiMulti = ['pelatihan' => [], 'anggota' => []];
    foreach ($crudCfg['fields'] as $key => $f) {
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
    foreach ($crudCfg['fields'] as $key => $f) {
        if (!empty($f['show_when'])) {
            $ck = array_key_first($f['show_when']);
            if (nilai_teks($_POST, $ck, false) !== $f['show_when'][$ck]) {
                $data[$key] = null;
                continue;
            }
        }
        if ($f['type'] === 'file') {
            $path = handle_upload($key, $f['subfolder'] ?? $crudEntitas);
            if ($path) $data[$key] = $path;
        } elseif ($f['type'] === 'video') {
            $path = handle_video_upload($key . '_file', $f['subfolder'] ?? $crudEntitas);
            // Kalau tidak ada file, pakai URL yang diketik admin.
            $data[$key] = $path ?: nilai_teks($_POST, $key);
        } elseif ($f['type'] === 'doc_file') {
            // Field bertanda restricted (mis. materi) disimpan DI LUAR document
            // root agar tidak bisa diunduh dengan menebak URL-nya; hanya bisa
            // diambil lewat berkas.php yang memeriksa hak akses.
            $sub = $f['subfolder'] ?? $crudEntitas;
            $path = !empty($f['restricted'])
                ? handle_restricted_upload($key, $sub, ['pdf', 'ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'], 10 * 1024 * 1024, 'PDF, PPT, DOC, XLS, TXT, atau ZIP', '10MB')
                : handle_doc_upload($key, $sub);
            if ($path) $data[$key] = $path;
        } elseif (in_array($f['type'], ['pelatihan_multi', 'anggota_multi', 'peraih'], true)) {
            // 'peraih' bukan kolom tabel — diproses khusus saat simpanTim prestasi.
            continue;
        } elseif (!in_array($f['type'], $akunTypes)) {
            $data[$key] = nilai_teks($_POST, $key);
        }
    }

    // Validasi nilai select terhadap opsi yang dikonfigurasi (anti-tampering).
    foreach ($crudCfg['fields'] as $key => $f) {
        if ($f['type'] !== 'select' || !isset($data[$key])) continue;
        if (!in_array($data[$key], $f['options'], true)) {
            flash_set('error', e($f['label']) . ' tidak valid.');
            redirect($crudBase . $qs);
        }
    }

    if ($postId > 0) {
        $savedId = $postId;
        $crudModel::update($savedId, $data);
        flash_set('success', $crudCfg['label'] . ' berhasil diperbarui.');
    } else {
        $savedId = $crudModel::create($data);
        flash_set('success', $crudCfg['label'] . ' berhasil ditambahkan.');
    }

    // Simpan relasi many-to-many bila ada
    if ($crudEntitas === 'materi') {
        Materi::simpanPelatihan((int) $savedId, $relasiMulti['pelatihan']);
    }
    if ($crudEntitas === 'prestasi') {
        // Mode peraih: 'anggota' (checkbox) atau 'manual' (teks pisah koma).
        if (($_POST['peraih_mode'] ?? 'anggota') === 'manual') {
            $manualNames = array_values(array_filter(array_map('trim', explode(',', nilai_teks($_POST, 'peraih_manual')))));
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

    // Karya hanya tampil di halaman publik bila publikasinya Disetujui. Kalau admin
    // menambah karya dari halaman fitur dengan status lain, karyanya tidak akan
    // terlihat di daftar — arahkan ke moderasi supaya tidak dikira gagal simpan.
    if ($crudEntitas === 'karya' && $crudParam === 'manage') {
        $publikasi = (string) ($data['publikasi'] ?? 'Menunggu');
        if ($publikasi !== 'Disetujui') {
            flash_set('success', 'Karya berhasil disimpan dengan status ' . $publikasi
                . '. Halaman Karya hanya menampilkan karya Disetujui, jadi belum tayang. Tinjau di Moderasi.');
            redirect('admin/moderasi.php?status=' . urlencode($publikasi));
        }
    }
    redirect($crudBase . $qs);
}

$editing = (!$refPanel && $action === 'edit' && $id) ? $crudModel::find($id) : null;
// Prestasi lama: isi awal nama lomba dari relasi lomba_id bila lomba_teks masih kosong.
if ($editing && $crudEntitas === 'prestasi' && ($editing['lomba_teks'] ?? '') === '' && !empty($editing['lomba_id'])) {
    $editing['lomba_teks'] = Lomba::find((int) $editing['lomba_id'])['nama'] ?? '';
}
$showForm = (!$refPanel && $action === 'create') || $editing;

// Daftar baris hanya dibutuhkan halaman yang memang menampilkannya (daftar admin
// atau panel di halaman fitur). Menghemat query saat form dibuka.
$rows = [];
$totalRows = 0;
if (!$showForm && !$refPanel) {
    $rows = $crudEntitas === 'pelatihan'
        ? Pelatihan::semua()
        : ($crudEntitas === 'prestasi' ? Prestasi::semua() : $crudModel::all());
    $totalRows = count($rows);
}

$anggotaOptions = in_array($crudEntitas, ['karya', 'anggota', 'prestasi'], true) ? Anggota::all('nama ASC') : [];
$pelatihanOptions = $crudEntitas === 'materi' ? Pelatihan::semua() : [];

// Nilai terpilih untuk field multi-pilihan saat mode edit
$terpilihPelatihan = [];
$terpilihAnggota = [];
$peraihMode = 'anggota';
$peraihManualTeks = '';
if ($editing) {
    if ($crudEntitas === 'materi') {
        $terpilihPelatihan = array_map('intval', array_column(Materi::pelatihanTerkait((int) $editing['id']), 'id'));
    }
    if ($crudEntitas === 'prestasi') {
        $tim = Prestasi::tim((int) $editing['id']);
        $terpilihAnggota = array_values(array_filter(array_map('intval', array_column($tim, 'anggota_id'))));
        $manualNames = array_column(array_filter($tim, fn($t) => empty($t['anggota_id'])), 'nama');
        $peraihManualTeks = implode(', ', $manualNames);
        if (!$terpilihAnggota && $manualNames) $peraihMode = 'manual';
    }
}
$akunUsername = '';
$akunList = [];
if ($crudEntitas === 'anggota') {
    $akunUsername = $editing ? (User::findByAnggota((int) $editing['id'])['username'] ?? '') : '';
    foreach (User::all() as $u) {
        if ($u['anggota_id'] !== null) $akunList[(int) $u['anggota_id']] = $u['username'];
    }
}

$listHasFoto = isset($crudCfg['fields']['foto']);
$searchCols = $crudCfg['search'] ?? [];
// ---- Pencarian daftar (filter di PHP, bukan query SQL) ----
// Kolom '_cari_*' tidak ada di tabel: diisi di sini dari data yang sudah
// dimuat (username anggota, nama pembuat karya) supaya ikut bisa dicari.
if (!$showForm && !$refPanel && $searchCols && $q !== '') {
    if ($crudEntitas === 'anggota') {
        foreach ($rows as &$row) {
            $row['_cari_username'] = $akunList[(int) $row['id']] ?? '';
        }
        unset($row);
    }
    if ($crudEntitas === 'karya') {
        $namaAnggota = array_column($anggotaOptions, 'nama', 'id');
        foreach ($rows as &$row) {
            $row['_cari_pembuat'] = $namaAnggota[(int) ($row['pembuat_id'] ?? 0)] ?? '';
        }
        unset($row);
    }
    $rows = cari_filter($rows, $searchCols, $q);
}