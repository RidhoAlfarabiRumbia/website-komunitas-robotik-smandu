<?php

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Potong teks dengan aman tanpa bergantung pada ekstensi mbstring.
 */
function trunc(?string $text, int $length = 80, string $suffix = '...'): string
{
    $text = $text ?? '';
    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($text, 0, $length, $suffix);
    }
    return strlen($text) > $length ? substr($text, 0, $length) . $suffix : $text;
}

function redirect(string $path): void
{
    header('Location: ' . base_url($path));
    exit;
}

function base_url(string $path = ''): string
{
    $root = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    // Jika dipanggil dari dalam folder admin/, arahkan relatif ke root proyek.
    if (str_ends_with($root, '/admin')) {
        $root = substr($root, 0, -6);
    }
    return ($root ?: '') . '/' . ltrim($path, '/');
}

/**
 * URL absolut untuk tautan di email. Pakai APP_URL dari .env; bila kosong,
 * susun dari request saat ini (cukup untuk pengembangan lokal).
 */
function app_url(string $path = ''): string
{
    $base = trim((string) getenv('APP_URL'));
    if ($base === '') {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
        $base = ($https ? 'https' : 'http') . '://' . $host . rtrim(dirname($script), '/');
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function tanggal_indo(?string $date): string
{
    if (!$date) return '-';
    $bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $ts = strtotime($date);
    return date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

/**
 * Tampilkan rentang tanggal pelatihan: "10 Oktober 2026 s.d. 24 Oktober 2026".
 */
function tanggal_rentang(?string $mulai, ?string $selesai): string
{
    if (!$mulai) return '-';
    if (!$selesai || $selesai === $mulai) return tanggal_indo($mulai);
    return tanggal_indo($mulai) . ' s.d. ' . tanggal_indo($selesai);
}

function badge_progres(string $progres): string
{
    $map = [
        'Sedang Dikerjakan' => 'badge-blue',
        'Selesai' => 'badge-teal',
    ];
    $cls = $map[$progres] ?? 'badge-muted';
    return '<span class="badge ' . $cls . '">' . e($progres) . '</span>';
}

function badge_publikasi(string $publikasi): string
{
    $map = ['Menunggu' => 'badge-amber', 'Disetujui' => 'badge-teal', 'Ditolak' => 'badge-red'];
    return '<span class="badge ' . ($map[$publikasi] ?? 'badge-muted') . '">' . e($publikasi) . '</span>';
}

/** Tingkat prestasi (PRD 8.7) */
function badge_tingkat(string $tingkat): string
{
    $map = [
        'Sekolah' => 'badge-muted',
        'Kota' => 'badge-blue',
        'Provinsi' => 'badge-teal',
        'Nasional' => 'badge-amber',
        'Internasional' => 'badge-amber',
    ];
    return '<span class="badge ' . ($map[$tingkat] ?? 'badge-muted') . '"><i class="bi bi-award"></i> ' . e($tingkat) . '</span>';
}

/** Status pendaftaran peserta pelatihan */
function badge_status_peserta(string $status): string
{
    $map = ['Pending' => 'badge-amber', 'Disetujui' => 'badge-teal', 'Ditolak' => 'badge-red'];
    return '<span class="badge ' . ($map[$status] ?? 'badge-muted') . '">' . e($status) . '</span>';
}

/** Status pendaftaran anggota baru (PRD 8.12) */
function badge_status_pendaftaran(string $status): string
{
    $map = ['Baru' => 'badge-amber', 'Diverifikasi' => 'badge-teal', 'Ditolak' => 'badge-red'];
    return '<span class="badge ' . ($map[$status] ?? 'badge-muted') . '">' . e($status) . '</span>';
}

/**
 * Flash message sederhana: flash_set('pesan') atau flash_set('success'|'error', 'pesan').
 * Pesan disimpan sebagai ['type' => ..., 'text' => ...] agar penanda bisa menentukan kelas alert.
 */
function flash_set(string $type, ?string $text = null): void
{
    if ($text === null) {
        $text = $type;
        $type = 'success';
    }
    $_SESSION['flash'] = ['type' => $type, 'text' => $text];
}

/** @return array{type:string,text:string}|null */
function flash_get(): ?array
{
    if (empty($_SESSION['flash'])) return null;

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    // Backward compatible dengan pesan yang disimpan sebelum perubahan ini.
    if (is_string($flash)) return ['type' => 'success', 'text' => $flash];

    return [
        'type' => ($flash['type'] === 'error' || $flash['type'] === 'warn') ? 'error' : 'success',
        'text' => (string) ($flash['text'] ?? ''),
    ];
}

/** Kelas CSS alert untuk flash pesan. */
function flash_alert_class(string $type): string
{
    return $type === 'error' ? 'alert-error' : 'alert-success';
}

/**
 * Ambil satu nilai dari $_POST/$_GET sebagai teks.
 *
 * Query seperti "nama[]=x" membuat nilainya berupa array, dan trim()/e()/fungsi
 * bertipe string akan melempar TypeError yang menghentikan seluruh halaman
 * (PHP mengirim "Fatal error" tanpa kode status yang benar). Karena itu nilai
 * non-scalar diperlakukan sebagai tidak ada.
 */
function nilai_teks(array $src, string $key, bool $trim = true): string
{
    $v = $src[$key] ?? null;
    if (!is_scalar($v)) {
        return '';
    }
    $v = (string) $v;
    return $trim ? trim($v) : $v;
}

/**
 * Ambil satu nilai dari $_POST/$_GET sebagai bilangan bulat.
 * Nilai non-scalar menghasilkan 0 — bukan 1 seperti hasil (int) pada array,
 * yang bisa membuat id[]=99 tertunjuk ke baris nomor 1.
 */
function nilai_int(array $src, string $key): int
{
    $v = $src[$key] ?? null;
    return is_scalar($v) ? (int) $v : 0;
}

/**
 * Pencarian daftar di halaman admin.
 * Kata kunci diambil dari GET/POST "q" lalu difilter di PHP terhadap baris yang
 * sudah dimuat — bukan lewat query SQL — sehingga input admin tidak pernah
 * masuk ke SQL.
 */

/** Bersihkan kata kunci: trim, rapatkan spasi, potong 100 karakter. */
function cari_kunci($raw): string
{
    if (!is_scalar($raw)) return ''; // mis. ?q[]=x — abaikan, jangan jadi warning
    $raw = (string) $raw;
    $q = preg_replace('/\s+/u', ' ', trim($raw));
    if ($q === null) $q = trim($raw); // input bukan UTF-8 valid
    if (function_exists('mb_substr')) {
        return mb_substr($q, 0, 100);
    }
    return strlen($q) > 100 ? substr($q, 0, 100) : $q;
}

/**
 * Filter baris berdasarkan kata kunci pada beberapa kolom (case-insensitive).
 * Kolom yang tidak ada pada baris dilewati diam-diam, jadi pemanggil boleh
 * menulis daftar kolom lebih lengkap daripada yang dikembalikan query.
 */
function cari_filter(array $rows, array $kolom, string $q): array
{
    if ($q === '' || !$kolom) {
        return $rows;
    }
    $multi = function_exists('mb_strtolower');
    $needle = $multi ? mb_strtolower($q, 'UTF-8') : strtolower($q);
    $hasil = [];
    foreach ($rows as $row) {
        if (!is_array($row)) continue;
        foreach ($kolom as $col) {
            $val = $row[$col] ?? null;
            if ($val === null || $val === '') continue;
            $hay = $multi ? mb_strtolower((string) $val, 'UTF-8') : strtolower((string) $val);
            if ($hay !== '' && str_contains($hay, $needle)) {
                $hasil[] = $row;
                break;
            }
        }
    }
    return $hasil;
}

/**
 * Query string untuk mempertahankan kata kunci pada link / redirect.
 * Pemanggil menentukan karakter pemisah ('?' atau '&').
 */
function cari_qs(string $q, string $lead = '&'): string
{
    return $q === '' ? '' : $lead . 'q=' . rawurlencode($q);
}

/**
 * Kotak pencarian untuk daftar admin. Kata kunci, tombol Cari, dan Reset.
 * Ringkasan jumlah data hanya ditampilkan bila $tampil/$total diisi.
 *
 * @param array       $params      query string lain yang harus ikut (entity, sub, ...)
 * @param string      $q           kata kunci aktif (hasil dari cari_kunci)
 * @param string      $kelas       'cari-bar' (baris penuh di atas tabel) atau
 *                                 'cari-inline' (baris judul, di kiri tombol Tambah)
 * @param int|null    $tampil      jumlah baris yang lolos filter
 * @param int|null    $total       jumlah baris sebelum filter
 */
function cari_form(array $params, string $q, string $placeholder, ?int $tampil = null, ?int $total = null, string $kelas = 'cari-bar'): string
{
    $html = '<form class="' . e($kelas) . '" method="get" action="">';
    foreach ($params as $k => $v) {
        if ($v === '' || $v === null) continue;
        $html .= '<input type="hidden" name="' . e((string) $k) . '" value="' . e((string) $v) . '">';
    }
    $html .= '<div class="field">'
        . '<input type="search" name="q" value="' . e($q) . '" placeholder="' . e($placeholder) . '" aria-label="' . e($placeholder) . '">'
        . '</div>';
    $html .= '<button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>';
    if ($q !== '') {
        $html .= '<a class="btn btn-outline" href="?' . e(http_build_query($params)) . '">Reset</a>';
    }
    if ($tampil !== null && $total !== null) {
        $ringkasan = $q === '' ? $total . ' data' : 'Menampilkan ' . $tampil . ' dari ' . $total . ' data';
        $html .= '<span class="cari-meta">' . e($ringkasan) . '</span>';
    }
    return $html . '</form>';
}

/**
 * Validasi & simpan file upload gambar (foto karya, galeri, prestasi, dsb).
 * Mengembalikan path relatif tersimpan, atau null jika tidak ada file baru.
 */
function handle_upload(string $inputName, string $subfolder = 'karya'): ?string
{
    return simpan_upload($inputName, $subfolder, ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024, 'JPG, PNG, atau WEBP', '2MB');
}

/**
 * Validasi & simpan file upload video (dokumentasi / laporan kegiatan, PRD 8.14).
 * Batas ukuran jauh lebih besar dari foto (±15-20 menit laporan kegiatan).
 */
function handle_video_upload(string $inputName, string $subfolder = 'kegiatan'): ?string
{
    // Batas 40MB mengikuti upload_max_filesize di php.ini XAMPP.
    return simpan_upload($inputName, $subfolder, ['mp4', 'webm', 'mov', 'm4v'], 40 * 1024 * 1024, 'MP4, WEBM, MOV, atau M4V', '40MB');
}

/**
 * Validasi & simpan dokumen (PDF, PPT, DOC, dll) ke folder publik.
 */
function handle_doc_upload(string $inputName, string $subfolder = 'materi'): ?string
{
    return simpan_upload($inputName, $subfolder, ['pdf', 'ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'], 10 * 1024 * 1024, 'PDF, PPT, DOC, XLS, TXT, atau ZIP', '10MB');
}

/**
 * Simpan berkas DI LUAR document root (folder storage/terbatas).
 * Dipakai untuk berkas materi berbatas dan dokumen Pakta Integritas yang
 * hanya boleh diakses admin / peserta pelatihan yang disetujui (PRD 12.1).
 */
function handle_restricted_upload(string $inputName, string $subfolder, array $allowed, int $maxSize, string $labelFormat, string $labelSize): ?string
{
    return simpan_upload($inputName, $subfolder, $allowed, $maxSize, $labelFormat, $labelSize, true);
}

/**
 * Deteksi tipe MIME nyata dari isi berkas memakai fileinfo (bukan dari
 * nama berkas). Mengembalikan ekstensi yang diizinkan, atau null bila
 * tipe tersebut tidak cocok dengan ekstensi yang diunggah.
 *
 * Tanpa pemeriksaan ini, penyerang bisa mengunggah berkas berlabel .jpg
 * yang sebenarnya HTML/JavaScript (polyglot) dan memakainya untuk XSS.
 */
function mime_terverifikasi(string $tmpPath, string $ext): ?string
{
    $peta = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'gif' => ['image/gif'],
        'pdf' => ['application/pdf'],
        'txt' => ['text/plain'],
        'zip' => ['application/zip'],
        'mp4' => ['video/mp4'],
        'webm' => ['video/webm'],
        'mov' => ['video/quicktime'],
        'm4v' => ['video/x-m4v'],
        'doc' => ['application/msword', 'application/x-msword', 'application/octet-stream'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'ppt' => ['application/vnd.ms-powerpoint', 'application/mspowerpoint'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'],
        'xls' => ['application/vnd.ms-excel', 'application/excel', 'application/octet-stream'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
    ];
    if (!isset($peta[$ext])) {
        return null;
    }

    if (!function_exists('finfo_open')) {
        // finfo tidak tersedia: andalkan pemeriksaan ekstensi saja.
        return $ext;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);

    // Format dokumen office sering terdeteksi sebagai application/zip atau
    // octet-stream depending versi, jadi tetap diterima bila ada di daftar.
    return in_array($mime, $peta[$ext], true) ? $ext : null;
}

/**
 * Tolak gambar dengan dimensi berlebihan (anti "decompression bomb":
 * gambar kecil yang dimekati menjadi sangat besar saat didekode).
 */
function cek_dimensi_gambar(string $tmpPath): bool
{
    if (!function_exists('getimagesize')) {
        return true;
    }
    $info = @getimagesize($tmpPath);
    if ($info === false) {
        return false; // bukan gambar yang valid
    }
    [$w, $h] = $info;
    if ($w <= 0 || $h <= 0) {
        return false;
    }
    // 12000 x 12000 piksel sudah jauh lebih dari cukup untuk dokumentasi sekolah,
    // tetapi masih aman agar tidak membebani server saat diproses.
    return ($w * $h) <= (12000 * 12000);
}

/**
 * Induk cara simpan upload. $terbatas = true berarti ditulis ke storage/terbatas.
 */
function simpan_upload(string $inputName, string $subfolder, array $allowed, int $maxSize, string $labelFormat, string $labelSize, bool $terbatas = false): ?string
{
    if (empty($_FILES[$inputName]['name']) || ($_FILES[$inputName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$inputName];
    // Multipart buatan tangan bisa membuat 'name' berupa array (mis. "foto[][name]").
    // pathinfo() bertipe string dan akan melempar TypeError.
    if (!isset($file['name']) || !is_string($file['name'])) {
        return null;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!isset($file['error']) || !is_scalar($file['error']) || (int) $file['error'] !== UPLOAD_ERR_OK) return null;
    if (!in_array($ext, $allowed, true)) {
        flash_set('Format file tidak didukung. Gunakan ' . $labelFormat . '.');
        return null;
    }
    if (!isset($file['size']) || !is_scalar($file['size']) || (int) $file['size'] > $maxSize) {
        flash_set('Ukuran file maksimal ' . $labelSize . '.');
        return null;
    }
    if (!isset($file['tmp_name']) || !is_string($file['tmp_name'])) {
        return null;
    }
    // Verifikasi isi berkas benar-benar cocok dengan ekstensi yang diklaim.
    if (mime_terverifikasi($file['tmp_name'], $ext) === null) {
        flash_set('Isi file tidak cocok dengan format ' . $labelFormat . '.');
        return null;
    }
    $adalahGambar = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    if ($adalahGambar && !cek_dimensi_gambar($file['tmp_name'])) {
        flash_set('Gambar ditolak: dimensi tidak valid atau terlalu besar.');
        return null;
    }

    $baseDir = $terbatas
        ? dirname(__DIR__, 2) . '/storage/terbatas/'
        : dirname(__DIR__, 2) . '/public/uploads/';
    $dir = $baseDir . $subfolder . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $filename = uniqid($subfolder . '_') . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
        flash_set('Gagal menyimpan berkas di server.');
        return null;
    }

    $relDir = ($terbatas ? 'terbatas/' : 'uploads/') . $subfolder . '/';
    $hasil = $relDir . $filename;

    // Gambar: buat varian ringan (maks 1600px, WebP) agar halaman tidak
    // memuat foto asli yang berat. File asli TIDAK dihapus — tetap arsip.
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        $varian = gambar_varian($dir . $filename, $dir . pathinfo($filename, PATHINFO_FILENAME));
        if ($varian !== null) {
            $hasil = $relDir . basename($varian);
        }
    }

    return $hasil;
}

/**
 * Buat varian gambar lebih ringan (maks sisi 1600px, WebP kualitas 80;
 * fallback JPEG bila WebP tak tersedia). File asli hanya dipakai sebagai
 * sumber, tidak diubah/dihapus. Mengembalikan path varian, atau null bila
 * tidak perlu dibuat / GD tak tersedia (upload tetap memakai file asli).
 */
function gambar_varian(string $sumber, string $basePath): ?string
{
    if (!extension_loaded('gd') || !function_exists('imagecreatefromstring')) {
        return null;
    }
    $data = @file_get_contents($sumber);
    if ($data === false) {
        return null;
    }
    $img = @imagecreatefromstring($data);
    if ($img === false) {
        return null;
    }

    $max = 1600;
    $w = imagesx($img);
    $h = imagesy($img);

    // Sudah WebP dan ukuran wajar: tak perlu varian, pakai file asli.
    if (strtolower(pathinfo($sumber, PATHINFO_EXTENSION)) === 'webp' && $w <= $max && $h <= $max) {
        imagedestroy($img);
        return null;
    }

    if ($w > $max || $h > $max) {
        $scale = min($max / $w, $max / $h);
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));
        $res = imagecreatetruecolor($nw, $nh);
        if ($res === false) {
            imagedestroy($img);
            return null;
        }
        imagealphablending($res, false);
        imagesavealpha($res, true);
        imagefilledrectangle($res, 0, 0, $nw, $nh, imagecolorallocatealpha($res, 0, 0, 0, 127));
        imagecopyresampled($res, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        $img = $res;
        $w = $nw;
        $h = $nh;
    }

    $varian = null;
    if (function_exists('imagewebp')) {
        $coba = $basePath . '_opt.webp';
        if (@imagewebp($img, $coba, 80)) {
            $varian = $coba;
        }
    }
    if ($varian === null) {
        $coba = $basePath . '_opt.jpg';
        $dst = imagecreatetruecolor($w, $h);
        if ($dst !== false) {
            imagefilledrectangle($dst, 0, 0, $w, $h, imagecolorallocate($dst, 255, 255, 255));
            imagealphablending($dst, true);
            imagecopy($dst, $img, 0, 0, 0, 0, $w, $h);
            if (@imagejpeg($dst, $coba, 82)) {
                $varian = $coba;
            }
            imagedestroy($dst);
        }
    }
    imagedestroy($img);
    return $varian;
}

/**
 * Bersihkan nama berkas dari unggahan agar aman disimpan / ditampilkan.
 * Dipakai untuk nama berkas asli pada tabel pendaftaran.
 */
function sanitize_filename(string $name): string
{
    $name = basename(str_replace('\\', '/', $name));
    $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name) ?? '';
    $name = ltrim($name, '._');
    return $name === '' ? 'berkas' : substr($name, 0, 120);
}

/**
 * Template Pakta Integritas yang diunggah admin (PRD 8.12).
 * Disimpan sebagai pengaturan: pakta_template (path), pakta_template_nama,
 * dan pakta_template_diunggah. Formula kosong ini diunduh lewat pakta-template.php.
 * 'ada' hanya true bila path tersimpan DAN berkasnya benar-benar masih ada di disk.
 */
function pakta_template(): array
{
    $path = trim(Pengaturan::nilai('pakta_template'));
    $abs = $path !== '' ? storage_path($path) : null;
    $nama = trim(Pengaturan::nilai('pakta_template_nama'));

    return [
        'path' => $path,
        'nama' => $nama !== '' ? $nama : ($path !== '' ? basename($path) : ''),
        'abs' => $abs,
        'ada' => $abs !== null,
        'diunggah' => trim(Pengaturan::nilai('pakta_template_diunggah')),
    ];
}

/**
 * Path absolut di server untuk berkas upload. Path relatif hasil handle_upload
 * (mis. "uploads/galeri/x.jpg" atau "terbatas/materi/y.pdf") dikembalikan
 * sebagai path absolut, atau null bila berkas tidak ada / di luar jangkauan.
 */
function storage_path(string $relative): ?string
{
    $relative = ltrim(str_replace('\\', '/', $relative), '/');
    if ($relative === '') return null;

    $root = dirname(__DIR__, 2);
    // Path relatif hasil simpan_upload: "terbatas/..." -> storage/, "uploads/..." -> public/uploads/
    if (str_starts_with($relative, 'terbatas/')) {
        $relative = 'storage/' . $relative;
    } elseif (str_starts_with($relative, 'uploads/')) {
        $relative = 'public/' . $relative;
    }

    // Resolve path FIRST (realpath resolves symlinks), then validate
    $full = realpath($root . '/' . $relative);
    if ($full === false || !is_file($full)) return null;

    // Block directory traversal after resolution (defense in depth)
    if (str_contains($full, '..')) return null;

    // Batasi hanya di dalam uploads/ atau storage/terbatas/.
    // Trailing separator wajib: tanpa itu, "uploads_evil" ikut lolos.
    foreach ([realpath($root . '/public/uploads'), realpath($root . '/storage/terbatas')] as $dir) {
        if ($dir === false) {
            continue;
        }
        $dir = rtrim(str_replace('\\', '/', $dir), '/') . '/';
        $fullNorm = str_replace('\\', '/', $full);
        if (str_starts_with($fullNorm, $dir)) {
            return $full;
        }
    }
    return null;
}

/**
 * Kirim berkas ke peramban dengan nama yang rapi (dipakai berkas.php).
 */
function kirim_berkas(string $absPath, string $downloadName, bool $inline = false): void
{
    $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
    $mime = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png', 'webp' => 'image/webp',
        'mp4' => 'video/mp4', 'webm' => 'video/webm',
        'mov' => 'video/quicktime', 'm4v' => 'video/x-m4v',
        'txt' => 'text/plain',
    ][$ext] ?? 'application/octet-stream';

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($absPath));
    header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . rawurlencode($downloadName) . '"');
    header('X-Content-Type-Options: nosniff');
    readfile($absPath);
    exit;
}