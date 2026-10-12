<?php
/**
 * Penyajian berkas terbatas (PRD 8.2 & 12.1).
 *
 * Berkas materi berbatas dan dokumen Pakta Integritas TIDAK berada di dalam
 * document root, sehingga hanya bisa diambil lewat halaman ini yang memeriksa
 * hak akses terlebih dahulu.
 *
 *   ?jenis=materi&id=7     -> admin, atau peserta pelatihan yang disetujui
 *   ?jenis=pakta&id=3      -> hanya admin
 */
require_once __DIR__ . '/../app/includes/bootstrap.php';

$jenis = $_GET['jenis'] ?? '';
$id = (int) ($_GET['id'] ?? 0);
$inline = !empty($_GET['lihat']);

if ($id <= 0) {
    http_response_code(400);
    exit('Permintaan tidak valid.');
}

$abs = null;
$namaUnduh = 'berkas';

if ($jenis === 'materi') {
    $m = Materi::find($id);
    if (!$m || empty($m['file_path'])) {
        http_response_code(404);
        exit('Berkas materi tidak ditemukan.');
    }
    $abs = storage_path($m['file_path']);
    $namaUnduh = preg_replace('/[^A-Za-z0-9._-]/', '_', $m['judul']) . '.' . pathinfo($m['file_path'], PATHINFO_EXTENSION);

    // Admin selalu boleh. Anggota hanya bila peserta pelatihan terkait yang disetujui & masih aktif.
    if (!Auth::isAdmin()) {
        if (!Auth::check()) {
            redirect('login.php');
        }
        if (!Materi::bolehAkses($id, Auth::anggotaId())) {
            http_response_code(403);
            exit('Materi ini terbuka bagi peserta pelatihan yang terkait dan sudah disetujui admin.');
        }
    }
} elseif ($jenis === 'pakta') {
    // Dokumen Pakta Integritas hanya untuk admin (PRD 12.1)
    Auth::requireRole('admin');
    $p = Pendaftaran::find($id);
    if (!$p || empty($p['pakta_integritas'])) {
        http_response_code(404);
        exit('Dokumen tidak ditemukan.');
    }
    $abs = storage_path($p['pakta_integritas']);
    $ext = pathinfo($p['pakta_integritas'], PATHINFO_EXTENSION);
    $namaUnduh = preg_replace('/[^A-Za-z0-9._-]/', '_', $p['nama']) . '_pakta_integritas.' . $ext;
} else {
    http_response_code(400);
    exit('Jenis berkas tidak dikenal.');
}

if (!$abs) {
    http_response_code(404);
    exit('Berkas tidak ditemukan di server.');
}

kirim_berkas($abs, $namaUnduh, $inline);
