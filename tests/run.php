<?php
/**
 * Runner tes CLI. Jalankan: C:\xampp\php\php.exe tests/run.php
 * (PHP CLI bawaan sistem tidak punya pdo_mysql/fileinfo/gd — pakai PHP XAMPP.)
 */
require_once __DIR__ . '/lib.php';

$files = glob(__DIR__ . '/*_test.php');
sort($files);

foreach ($files as $f) {
    echo "\n== " . basename($f) . " ==\n";
    require $f;
}

printf("\nRingkasan: %d lulus, %d gagal.\n", $GLOBALS['__pass'], $GLOBALS['__fail']);
exit($GLOBALS['__fail'] > 0 ? 1 : 0);
