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

Csrf::verify();
Auth::logout();
redirect('beranda.php');