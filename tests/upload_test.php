<?php
/**
 * Tes validasi upload (tanpa HTTP sungguhan — semua lewat $_FILES palsu).
 * Jalankan: C:\xampp\php\php.exe tests/upload_test.php
 */
require_once __DIR__ . '/lib.php';

$pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
$tmpPng = sys_get_temp_dir() . '/tes_cli_' . uniqid() . '.png';
file_put_contents($tmpPng, $pngData);
$tmpTxt = sys_get_temp_dir() . '/tes_cli_' . uniqid() . '.txt';
file_put_contents($tmpTxt, 'bukan gambar');

function fakeUpload(string $name, string $tmp, int $size, int $error = UPLOAD_ERR_OK): array
{
    return ['name' => $name, 'type' => 'application/octet-stream', 'tmp_name' => $tmp, 'error' => $error, 'size' => $size];
}

// --- 1. Tidak ada file ---
unset($_FILES['u']);
ok(simpan_upload('u', 'karya', ['jpg'], 2 * 1024 * 1024, 'JPG', '2MB') === null, 'tanpa file → null');
ok(flash_get() === null, 'tanpa file → tanpa pesan flash');

// --- 2. Ekstensi tidak didukung ---
$_FILES['u'] = fakeUpload('script.php', $tmpTxt, 10);
ok(simpan_upload('u', 'karya', ['jpg'], 2 * 1024 * 1024, 'JPG', '2MB') === null, 'ekstensi .php ditolak');
$fl = flash_get();
ok($fl !== null && str_contains($fl['text'], 'Format file tidak didukung'), 'flash format ditampilkan');

// --- 3. Kelebihan ukuran foto (batas 2MB) ---
$_FILES['u'] = fakeUpload('foto.jpg', $tmpPng, 3 * 1024 * 1024);
ok(handle_upload('u', 'karya') === null, 'foto 3MB ditolak (batas 2MB)');
$fl = flash_get();
ok($fl !== null && str_contains($fl['text'], '2MB'), 'flash batas 2MB ditampilkan');

// --- 4. Kelebihan ukuran video (batas 40MB) ---
$_FILES['u'] = fakeUpload('video.mp4', $tmpTxt, 41 * 1024 * 1024);
ok(handle_video_upload('u', 'kegiatan') === null, 'video 41MB ditolak (batas 40MB)');
$fl = flash_get();
ok($fl !== null && str_contains($fl['text'], '40MB'), 'flash batas 40MB ditampilkan');

// --- 5. Kelebihan ukuran dokumen (batas 10MB) ---
$_FILES['u'] = fakeUpload('dok.pdf', $tmpTxt, 11 * 1024 * 1024);
ok(handle_doc_upload('u', 'materi') === null, 'dokumen 11MB ditolak (batas 10MB)');
$fl = flash_get();
ok($fl !== null && str_contains($fl['text'], '10MB'), 'flash batas 10MB ditampilkan');

// --- 6. Isi file tidak cocok: PNG asli berlabel .jpg (polyglot/xss) ---
$_FILES['u'] = fakeUpload('palsu.jpg', $tmpPng, filesize($tmpPng));
ok(handle_upload('u', 'karya') === null, 'PNG berlabel .jpg ditolak');
$fl = flash_get();
ok($fl !== null && str_contains($fl['text'], 'Isi file tidak cocok'), 'flash mime ditampilkan');

// --- 7. Error upload dari PHP ---
$_FILES['u'] = fakeUpload('foto.jpg', $tmpPng, 0, UPLOAD_ERR_INI_SIZE);
ok(handle_upload('u', 'karya') === null, 'UPLOAD_ERR_INI_SIZE → null');

// --- 8. cek_dimensi_gambar langsung ---
ok(cek_dimensi_gambar($tmpPng) === true, 'PNG 1x1 lolos cek dimensi');
ok(cek_dimensi_gambar($tmpTxt) === false, 'file teks gagal cek dimensi');

// --- 9. Upload valid: semua validasi lolos, hanya move_uploaded_file
//        yang gagal di CLI (butuh request HTTP sungguhan) ---
$_FILES['u'] = fakeUpload('ok.png', $tmpPng, filesize($tmpPng));
ok(handle_upload('u', 'karya') === null, 'upload valid: null di CLI (move_uploaded_file butuh HTTP)');
$fl = flash_get();
ok($fl !== null && str_contains($fl['text'], 'Gagal menyimpan berkas'), 'validasi lolos — gagal hanya di penyimpanan CLI');

unset($_FILES['u']);
@unlink($tmpPng);
@unlink($tmpTxt);
