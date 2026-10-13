<?php
/**
 * Harness subproses: memanggil Csrf::verify() dengan token tidak cocok /
 * kosong — fungsi memanggil die(), jadi harus dijalankan di proses terpisah.
 * Pemakaian: php csrf_fail.php [salah|kosong]
 */
session_start();
require_once __DIR__ . '/../../app/core/Csrf.php';

$_SESSION['csrf_token'] = 'token_asli_abc';
$mode = $argv[1] ?? 'salah';
$_POST['csrf_token'] = ($mode === 'kosong') ? '' : 'token_salah_xyz';

Csrf::verify();
echo 'TIDAK_DIE'; // tidak boleh tercapai
