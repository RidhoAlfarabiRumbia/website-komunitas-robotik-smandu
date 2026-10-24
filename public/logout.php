<?php
/**
 * Keluar dari portal.
 * Hanya POST + token CSRF: lewat GET, pihak lain bisa memaksa admin/anggota
 * logout diam-diam cukup dengan menyisipkan <img src=".../logout.php"> di halaman.
 */
require_once __DIR__ . '/../app/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('beranda.php');
}

Csrf::verify(function (): void {
    if (!Auth::check()) {
        redirect('beranda.php');
    }
    if (!Csrf::sameOrigin()) {
        http_response_code(403);
        exit('Permintaan tidak valid.');
    }
});

Auth::logout();
redirect('beranda.php');