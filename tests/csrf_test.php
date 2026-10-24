<?php
/**
 * Tes token & verifikasi CSRF.
 * Jalankan: C:\xampp\php\php.exe tests/csrf_test.php
 */
require_once __DIR__ . '/lib.php';

// --- Token ---
$t1 = Csrf::token();
ok(strlen($t1) === 64 && ctype_xdigit($t1), 'token terdiri dari 64 karakter hex');
ok(Csrf::token() === $t1, 'token tidak diganti setiap dipanggil (stabil dalam sesi)');

// --- Field form ---
$field = Csrf::field();
ok(str_contains($field, 'name="csrf_token"'), 'field() memuat input csrf_token');
ok(str_contains($field, 'value="' . $t1 . '"'), 'field() memuat token aktif');

// --- verify(): token cocok → lolos (proses ini tidak boleh berhenti) ---
$_POST['csrf_token'] = $t1;
$sebelumGagal = $GLOBALS['__fail'];
Csrf::verify();
ok($GLOBALS['__fail'] === $sebelumGagal, 'verify() menerima token yang cocok');

// --- verify(): token salah / kosong → harus die.
// Karena die() menghentikan proses, diuji lewat subproses (tests/harness/csrf_fail.php).
$php = PHP_BINARY;
$harness = __DIR__ . '/harness/csrf_fail.php';

$out = [];
exec(escapeshellarg($php) . ' ' . escapeshellarg($harness) . ' salah 2>&1', $out, $kode);
$hasil = implode("\n", $out);
ok(str_contains($hasil, 'tidak valid'), 'token salah → pesan "tidak valid" ditampilkan');
ok(!str_contains($hasil, 'TIDAK_DIE'), 'token salah → eksekusi berhenti (die)');

$out = [];
exec(escapeshellarg($php) . ' ' . escapeshellarg($harness) . ' kosong 2>&1', $out, $kode);
$hasil = implode("\n", $out);
ok(str_contains($hasil, 'tidak valid'), 'token kosong → pesan "tidak valid" ditampilkan');
ok(!str_contains($hasil, 'TIDAK_DIE'), 'token kosong → eksekusi berhenti (die)');

$dipanggil = false;
$_POST['csrf_token'] = 'token_salah_xyz';
Csrf::verify(function () use (&$dipanggil): void {
    $dipanggil = true;
});
ok($dipanggil, 'verify() memanggil callback saat token tidak cocok');

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_PORT'] = 80;
unset($_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO'], $_SERVER['HTTP_REFERER'], $_SERVER['HTTP_SEC_FETCH_SITE']);
$_SERVER['HTTP_ORIGIN'] = 'http://localhost';
ok(Csrf::sameOrigin(), 'sameOrigin() menerima origin yang sama');

$_SERVER['HTTP_ORIGIN'] = 'https://contoh-lain.test';
ok(!Csrf::sameOrigin(), 'sameOrigin() menolak origin lain');

unset($_SERVER['HTTP_ORIGIN']);
$_SERVER['HTTP_SEC_FETCH_SITE'] = 'same-origin';
ok(Csrf::sameOrigin(), 'sameOrigin() mengenali Sec-Fetch-Site same-origin');
