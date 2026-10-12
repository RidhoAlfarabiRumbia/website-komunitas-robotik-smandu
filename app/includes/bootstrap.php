<?php
/**
 * Bootstrap aplikasi: session, header keamanan, config, autoload, helper.
 * Semua halaman di public/ wajib me-require file ini sebagai langkah pertama.
 */

/* ---------- 1. Cookie & sesi lebih aman ----------
 * httponly  : cookie sesi tidak bisa dibaca JavaScript (mitigasi XSS).
 * samesite  : Lax mencegah cookie ikut terkirim pada cross-site POST (mitigasi CSRF).
 * strict    : hanya terima ID sesi yang punya data di server (anti session fixation).
 * secure    : hanya via HTTPS bila situs dijalankan di HTTPS.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if ($https) {
        ini_set('session.cookie_secure', '1');
    }
    ini_set('session.gc_maxlifetime', '1800'); // idle 30 menit
    // CATATAN: jangan set session.sid_length — INI setting itu deprecated di
    // PHP 8.3 dan memunculkan output sebelum session_start(), yang membuat
    // "headers already sent" dan merusak session. Panjang ID sesi bawaan
    // sudah cukup aman.

    session_name('ROBOTIKSESSID');
    session_start();

    /* ---------- 2. Header keamanan ----------
     * HSTS hanya dikirim bila lewat HTTPS, agar tidak merusak pengembangan lokal.
     */
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header_remove('X-Powered-By');
        if ($https) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}

/* ---------- 3. Masa berlaku sesi: idle 30 menit, absolut 8 jam ---------- */
$sekarang = time();
if (isset($_SESSION['terakhir_aktif'])
    && ($sekarang - (int) $_SESSION['terakhir_aktif'] > 1800
        || $sekarang - (int) ($_SESSION['dimulai'] ?? $sekarang) > 28800)) {
    $_SESSION = [];
    session_destroy();
    session_start();
    session_regenerate_id(true);
}
$_SESSION['terakhir_aktif'] = $sekarang;
$_SESSION['dimulai'] ??= $sekarang;

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../core/helpers.php';