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
// $akunList, $akunUsername, $akunEmail, $akunEmailList, $paktaList, $anggotaOptions, $pelatihanOptions, $terpilihPelatihan,
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
$akunTypes = ['akun_username', 'akun_email', 'password'];
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
    // Pengaman double-submit yang sama dengan simpan utama.
    if (!form_guard_periksa(nilai_teks($_POST, 'token_kirim'))) {
        flash_set('error', 'Form sudah terkirim atau kedaluwarsa. Muat ulang halaman bila ingin mengisi lagi.');
        redirect($crudBaseMateri . '&sub=referensi' . $qs);
    }
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
        if (!Referensi::find($refId)) {
            flash_set('error', 'Referensi tidak ditemukan.');
            redirect($crudBaseMateri . '&sub=referensi' . $qs);
        }
        if (!Referensi::update($refId, $refData)) {
            flash_set('error', 'Referensi gagal diperbarui.');
            redirect($refBack);
        }
        flash_set('success', 'Referensi berhasil diperbarui.');
    } else {
        if (Referensi::create($refData) <= 0) {
            flash_set('error', 'Referensi gagal ditambahkan.');
            redirect($refBack);
        }
        flash_set('success', 'Referensi berhasil ditambahkan.');
    }
    redirect($crudBaseMateri . '&sub=referensi' . $qs);
}

// ---- Toggle status (slider hero): POST + CSRF, sebelum blok simpan biasa ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'toggle_status' && $id) {
    Csrf::verify();
    if ($crudEntitas === 'hero' && !empty($crudCfg['toggle_status'])) {
        $row = $crudModel::find($id);
        if (!$row) {
            flash_set('error', 'Data slider tidak ditemukan.');
        } else {
            $baru = ($row['status'] ?? '') === 'Tampil' ? 'Sembunyi' : 'Tampil';
            if ($crudModel::update($id, ['status' => $baru])) {
                flash_set('success', 'Status slider diubah jadi ' . $baru . '.');
            } else {
                flash_set('error', 'Status slider gagal diubah.');
            }
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
    // Baris lama diambil sebelum delete: kolom berkasnya dipakai untuk
    // membersihkan disk setelah penghapusan baris berhasil.
    $rowHapus = $crudModel::find($id);
    if ($refPanel) {
        if (Referensi::delete($id)) {
            flash_set('success', 'Referensi berhasil dihapus.');
        } else {
            flash_set('error', 'Referensi gagal dihapus.');
        }
        redirect($crudBaseMateri . '&sub=referensi' . $qs);
    }
    // Akun login ikut dihapus (lapisan pengaman: users.anggota_id sudah memakai
    // ON DELETE CASCADE di database).
    if ($crudEntitas === 'anggota') {
        User::deleteByAnggota($id);
    }
    if ($crudModel::delete($id)) {
        // Baris benar-benar terhapus dari database: berkas yang dirujuk baris
        // itu ikut dihapus agar tidak jadi orphan di public/uploads maupun
        // storage. URL eksternal/placeholder otomatis dilewati oleh
        // hapus_upload() bila pathnya tidak valid.
        if ($rowHapus) {
            foreach ($crudCfg['fields'] as $key => $f) {
                $tipeF = $f['type'];
                $berkasKolom = $tipeF === 'file' || $tipeF === 'doc_file' || $tipeF === 'video';
                if ($berkasKolom && !empty($rowHapus[$key])) {
                    hapus_upload((string) $rowHapus[$key]);
                }
            }
        }
        flash_set($crudCfg['label'] . ' berhasil dihapus.');
    } else {
        flash_set('error', $crudCfg['label'] . ' gagal dihapus atau sudah tidak ada.');
    }
    redirect($crudBase . $qs);
}

// ---- Simpan (create/update) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();
    // Pengaman double-submit: token sekali-pakai. Klik berulang (jaringan
    // lambat, retry) yang berhasil sampai server hanya boleh tercatat sekali.
    if (!form_guard_periksa(nilai_teks($_POST, 'token_kirim'))) {
        flash_set('error', 'Form sudah terkirim atau kedaluwarsa. Muat ulang halaman bila ingin mengisi lagi.');
        redirect($crudBase . $qs);
    }
    $errors = [];
    $materiBaru = null; // path berkas "Unggah Materi" pada tambah pelatihan (bukan kolom tabel)
    $paktaBaru = null; // path berkas Pakta Integritas pada tambah anggota (bukan kolom tabel)
    foreach ($crudCfg['fields'] as $key => $f) {
        if (empty($f['required'])) continue;
        if (in_array($f['type'], ['anggota_multi', 'pelatihan_multi'], true)) {
            $vals = $_POST[$key] ?? null;
            if (!is_array($vals) || !array_filter($vals, static fn($v): bool => is_scalar($v) && (int) $v > 0)) {
                $errors[] = e($f['label']) . ' wajib diisi.';
            }
            continue;
        }
        if (in_array($f['type'], array_merge(['file', 'doc_file'], $akunTypes), true)) continue;
        $val = nilai_teks($_POST, $key);
        if ($val === '') $errors[] = e($f['label']) . ' wajib diisi.';
    }

    $isAkun = $crudEntitas === 'anggota';
    $postId = nilai_int($_POST, 'id');
    $username = nilai_teks($_POST, 'username');
    $akunEmail = strtolower(nilai_teks($_POST, 'akun_email'));
    $password = nilai_teks($_POST, 'password', false);
    $akun = null;
    if ($isAkun) {
        $akun = $postId ? User::findByAnggota($postId) : null;
        if ($username === '') $errors[] = 'Username wajib diisi.';
        elseif (mb_strlen($username) > 60) $errors[] = 'Username maksimal 60 karakter.';
        elseif (User::usernameTaken($username, (int) ($akun['id'] ?? 0))) $errors[] = 'Username sudah dipakai akun lain.';
        if ($akunEmail === '') $errors[] = 'Email login wajib diisi.';
        elseif (strlen($akunEmail) > 120 || !filter_var($akunEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email login tidak valid.';
        elseif (User::emailTaken($akunEmail, (int) ($akun['id'] ?? 0))) $errors[] = 'Email login sudah dipakai akun lain.';
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
            $adaFile = !empty($_FILES[$key]['name']);
            $path = handle_upload($key, $f['subfolder'] ?? $crudEntitas);
            if ($adaFile && $path === null) {
                $errors[] = e($f['label']) . ' gagal diunggah.';
            } elseif ($path) {
                $data[$key] = $path;
            }
        } elseif ($f['type'] === 'video') {
            $adaFile = !empty($_FILES[$key . '_file']['name']);
            $path = handle_video_upload($key . '_file', $f['subfolder'] ?? $crudEntitas);
            if ($adaFile && $path === null) {
                $errors[] = e($f['label']) . ' gagal diunggah.';
            } else {
                // Kalau tidak ada file, pakai URL yang diketik admin.
                $data[$key] = $path ?: nilai_teks($_POST, $key);
            }
        } elseif ($f['type'] === 'doc_file') {
            // Field bertanda restricted (mis. materi) disimpan DI LUAR document
            // root agar tidak bisa diunduh dengan menebak URL-nya; hanya bisa
            // diambil lewat berkas.php yang memeriksa hak akses.
            $sub = $f['subfolder'] ?? $crudEntitas;
            $adaFile = !empty($_FILES[$key]['name']);
            $path = !empty($f['restricted'])
                ? handle_restricted_upload($key, $sub, ['pdf', 'ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'], 10 * 1024 * 1024, 'PDF, PPT, DOC, XLS, TXT, atau ZIP', '10MB')
                : handle_doc_upload($key, $sub);
            if ($adaFile && $path === null) {
                $errors[] = e($f['label']) . ' gagal diunggah.';
            } elseif ($path) {
                $data[$key] = $path;
            }
        } elseif ($f['type'] === 'materi_baru') {
            // Pseudo-field: berkas materi opsional saat tambah pelatihan (bukan
            // kolom tabel). Berkas disimpan lebih dulu di luar document root;
            // baris materi + relasinya dibuat dalam transaksi pelatihan.
            if (!empty($_FILES[$key]['name'])) {
                $materiBaru = handle_restricted_upload(
                    $key,
                    $f['subfolder'] ?? 'materi',
                    ['pdf', 'ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'],
                    10 * 1024 * 1024,
                    'PDF, PPT, DOC, XLS, TXT, atau ZIP',
                    '10MB'
                );
                if ($materiBaru === null) $errors[] = 'Berkas materi gagal diunggah.';
            }
        } elseif ($f['type'] === 'pakta_baru') {
            // Pseudo-field: Dokumen Pakta Integritas opsional saat tambah
            // anggota secara manual. Ketentuan berkas sama dengan formulir
            // pendaftaran; dipasangkan lewat baris pendaftaran Diverifikasi.
            if (!empty($_FILES[$key]['name'])) {
                $paktaBaru = handle_restricted_upload(
                    $key,
                    $f['subfolder'] ?? 'pakta-integritas',
                    ['pdf', 'jpg', 'jpeg', 'png'],
                    5 * 1024 * 1024,
                    'PDF, JPG, atau PNG',
                    '5MB'
                );
                if ($paktaBaru === null) $errors[] = 'Dokumen Pakta Integritas gagal diunggah. Gunakan PDF, JPG, atau PNG maksimal 5MB.';
            }
        } elseif (in_array($f['type'], ['pelatihan_multi', 'anggota_multi', 'peraih'], true)) {
            // 'peraih' bukan kolom tabel — diproses khusus saat simpanTim prestasi.
            continue;
        } elseif (!in_array($f['type'], $akunTypes)) {
            $data[$key] = nilai_teks($_POST, $key);
        }
    }

    $pembuatKarya = [];
    if ($crudEntitas === 'karya') {
        $pembuatKarya = Karya::validPembuatIds($relasiMulti['anggota']);
        if (!$pembuatKarya) {
            flash_set('error', 'Pembuat karya wajib dipilih minimal satu.');
            $backAction = $postId ? 'edit&id=' . $postId : 'create';
            redirect($crudBase . '&action=' . $backAction . $qs);
        }
        $data['pembuat_id'] = $pembuatKarya[0];
    }

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        $backAction = $postId ? 'edit&id=' . $postId : 'create';
        redirect($crudBase . '&action=' . $backAction . $qs);
    }

    // Validasi nilai select terhadap opsi yang dikonfigurasi (anti-tampering).
    foreach ($crudCfg['fields'] as $key => $f) {
        if ($f['type'] !== 'select' || !isset($data[$key])) continue;
        if (!in_array($data[$key], $f['options'], true)) {
            flash_set('error', e($f['label']) . ' tidak valid.');
            redirect($crudBase . $qs);
        }
    }

    if ($postId > 0 && !$data) {
        // Model::update() tidak bisa membentuk UPDATE tanpa kolom.
        flash_set('error', 'Tidak ada perubahan untuk disimpan.');
        redirect($crudBase . '&action=edit&id=' . $postId . $qs);
    }

    // Baris lama diambil SEBELUM update: dipakai menegaskan barisnya masih ada
    // dan menjadi bahan pembersihan berkas lama yang kelak digantikan.
    $rowLama = $postId > 0 ? $crudModel::find($postId) : null;
    if ($postId > 0 && !$rowLama) {
        flash_set('error', $crudCfg['label'] . ' tidak ditemukan. Mungkin sudah dihapus.');
        redirect($crudBase . $qs);
    }

    $dbSimpan = Database::connect();
    try {
        $dbSimpan->beginTransaction();

        if ($postId > 0) {
            $savedId = $postId;
            if (!$crudModel::update($savedId, $data)) {
                throw new RuntimeException('Update ' . $crudEntitas . ' gagal.');
            }
        } else {
            $savedId = (int) $crudModel::create($data);
            if ($savedId <= 0) {
                throw new RuntimeException('Create ' . $crudEntitas . ' gagal.');
            }
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
        if ($crudEntitas === 'karya') {
            Karya::simpanPembuat((int) $savedId, $pembuatKarya);
        }
        if ($isAkun) {
            $akun = User::findByAnggota($savedId);
            if ($akun) {
                $userData = ['username' => $username, 'email' => $akunEmail];
                if ($password !== '') $userData['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                if (!User::update((int) $akun['id'], $userData)) {
                    throw new RuntimeException('Update akun anggota gagal.');
                }
            } elseif (User::createForAnggota($savedId, $username, $password, $akunEmail) <= 0) {
                throw new RuntimeException('Create akun anggota gagal.');
            }
        }

        // Dokumen Pakta Integritas dari formulir tambah anggota manual dipasangkan
        // sebagai baris pendaftaran milik anggota ini (status Diverifikasi) agar
        // tombol Lihat di kolom daftar & data Profil berfungsi tanpa cara lain.
        if ($crudEntitas === 'anggota' && $paktaBaru !== null && $postId === 0) {
            $pendaftaranPaktaId = (int) Pendaftaran::create([
                'nama' => (string) ($data['nama'] ?? ''),
                'kelas' => (string) ($data['kelas'] ?? ''),
                'kontak' => '',
                'email' => '',
                'alasan' => 'Penambahan langsung oleh admin.',
                'pakta_integritas' => $paktaBaru,
                'nama_berkas' => sanitize_filename((string) ($_FILES['pakta_baru']['name'] ?? '')),
                'status' => 'Diverifikasi',
                'anggota_id' => (int) $savedId,
                'diverifikasi_pada' => date('Y-m-d H:i:s'),
            ]);
            if ($pendaftaranPaktaId <= 0) {
                throw new RuntimeException('Create pendaftaran pakta anggota gagal.');
            }
        }

        // Materi yang diunggah bersama form tambah pelatihan dibuat dalam
        // transaksi yang sama supaya tidak yatim bila pelatihan gagal.
        if ($crudEntitas === 'pelatihan' && $materiBaru !== null) {
            $materiIdBaru = (int) Materi::create([
                'judul' => mb_substr((string) ($data['nama'] ?? ''), 0, 150),
                'topik' => 'Pelatihan',
                'file_path' => $materiBaru,
            ]);
            if ($materiIdBaru <= 0) {
                throw new RuntimeException('Create materi dari pelatihan gagal.');
            }
            Materi::simpanPelatihan($materiIdBaru, [(int) $savedId]);
        }

        $dbSimpan->commit();

        // Pembersihan berkas LAMA yang digantikan saat edit. Nilai baru sudah
        // lewat commit berarti database memakai path baru; path lama yang masih
        // berbeda dihapus dari disk. Video dari URL eksternal aman otomatis:
        // hapus_upload() menolak path yang bukan milik uploads/terbatas.
        if ($rowLama) {
            foreach ($crudCfg['fields'] as $key => $f) {
                $tipeF = $f['type'];
                $berkasKolom = $tipeF === 'file' || $tipeF === 'doc_file' || $tipeF === 'video';
                if (!$berkasKolom) continue;
                $baru = (string) ($data[$key] ?? '');
                $lama = (string) ($rowLama[$key] ?? '');
                if ($baru !== '' && $lama !== '' && $baru !== $lama) {
                    hapus_upload($lama);
                }
            }
        }

        flash_set('success', $crudCfg['label'] . ($postId > 0 ? ' berhasil diperbarui.' : ' berhasil ditambahkan.')
            . ($materiBaru !== null ? ' Materi baru tersimpan dan terkait pelatihan ini.' : ''));
    } catch (Throwable $e) {
        if ($dbSimpan->inTransaction()) {
            $dbSimpan->rollBack();
        }
        // Berkas materi yang sudah terlanjur tersimpan tidak boleh jadi sampah
        // ketika transaksi digulung balik.
        if ($materiBaru !== null) {
            @unlink(dirname(__DIR__, 2) . '/storage/' . $materiBaru);
        }
        if ($paktaBaru !== null) {
            @unlink(dirname(__DIR__, 2) . '/storage/' . $paktaBaru);
        }
        error_log('[CRUD] ' . $e->getMessage());
        flash_set('error', $crudCfg['label'] . ' gagal disimpan. Silakan coba lagi.');
        $kembali = $postId > 0 ? 'edit&id=' . $postId : 'create';
        redirect($crudBase . '&action=' . $kembali . $qs);
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
$materiPelatihan = [];
if ($editing) {
    if ($crudEntitas === 'materi') {
        $terpilihPelatihan = array_map('intval', array_column(Materi::pelatihanTerkait((int) $editing['id']), 'id'));
    }
    if ($crudEntitas === 'pelatihan') {
        // Materi yang sudah terkait pelatihan ini (ditampilkan baca-saja di form).
        $materiPelatihan = Pelatihan::materi((int) $editing['id']);
    }
    if ($crudEntitas === 'prestasi') {
        $tim = Prestasi::tim((int) $editing['id']);
        $terpilihAnggota = array_values(array_filter(array_map('intval', array_column($tim, 'anggota_id'))));
        $manualNames = array_column(array_filter($tim, fn($t) => empty($t['anggota_id'])), 'nama');
        $peraihManualTeks = implode(', ', $manualNames);
        if (!$terpilihAnggota && $manualNames) $peraihMode = 'manual';
    }
    if ($crudEntitas === 'karya') {
        $terpilihAnggota = array_values(array_filter(array_map('intval', array_column(Karya::pembuat((int) $editing['id']), 'id'))));
        if (!$terpilihAnggota && !empty($editing['pembuat_id'])) {
            $terpilihAnggota = [(int) $editing['pembuat_id']];
        }
    }
}
$akunUsername = '';
$akunEmail = '';
$akunList = [];
$akunEmailList = [];
if ($crudEntitas === 'anggota') {
    $akunEdit = $editing ? User::findByAnggota((int) $editing['id']) : null;
    $akunUsername = (string) ($akunEdit['username'] ?? '');
    $akunEmail = (string) ($akunEdit['email'] ?? '');
    foreach (User::all() as $u) {
        if ($u['anggota_id'] !== null) {
            $akunList[(int) $u['anggota_id']] = $u['username'];
            $akunEmailList[(int) $u['anggota_id']] = $u['email'];
        }
    }
}

$paktaList = [];
if ($crudEntitas === 'anggota' && !$showForm && !$refPanel) {
    $paktaList = Pendaftaran::paktaPerAnggota(array_column($rows, 'id'));
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
            $row['_cari_email'] = $akunEmailList[(int) $row['id']] ?? '';
        }
        unset($row);
    }
    if ($crudEntitas === 'karya') {
        $petaPembuat = Karya::pembuatMap(array_column($rows, 'id'));
        foreach ($rows as &$row) {
            $row['_cari_pembuat'] = $petaPembuat[(int) $row['id']] ?? '';
        }
        unset($row);
    }
    $rows = cari_filter($rows, $searchCols, $q);
}

$totalRows = count($rows);
$perHalaman = 20;
$totalHalaman = max(1, (int) ceil($totalRows / $perHalaman));
$halaman = max(1, min($totalHalaman, nilai_int($_GET, 'page')));
$rows = array_slice($rows, ($halaman - 1) * $perHalaman, $perHalaman);