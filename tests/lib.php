<?php
/**
 * Bootstrap kecil untuk tes CLI: session, env, autoload, helpers.
 * Dipakai run.php dan tiap file *_test.php (bisa juga dijalankan sendiri).
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Buffer output sejak awal: di CLI, echo membuat headers dianggap sudah
// terkirim sehingga session_regenerate_id() (dipanggil Auth::attempt)
// memunculkan warning. Buffer menahan semua output sampai skrip selesai.
ob_start();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../app/config/env.php';
require_once __DIR__ . '/../app/config/autoload.php';
require_once __DIR__ . '/../app/core/helpers.php';

$GLOBALS['__pass'] = 0;
$GLOBALS['__fail'] = 0;

function ok(bool $cond, string $label): void
{
    if ($cond) {
        $GLOBALS['__pass']++;
        echo "  ok   - $label\n";
    } else {
        $GLOBALS['__fail']++;
        echo "  FAIL - $label\n";
    }
}
