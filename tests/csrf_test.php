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
