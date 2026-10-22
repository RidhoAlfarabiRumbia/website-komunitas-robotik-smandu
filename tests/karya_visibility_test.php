<?php
/**
 * Tes aturan visibilitas karya: Karya::bolehDilihat() (murni, tanpa DB).
 * Jalankan: C:\xampp\php\php.exe tests/karya_visibility_test.php
 */
require_once __DIR__ . '/lib.php';

$disetujui = ['publikasi' => 'Disetujui', 'pembuat_id' => 5];
$menunggu  = ['publikasi' => 'Menunggu',  'pembuat_id' => 5];
$ditolak   = ['publikasi' => 'Ditolak',   'pembuat_id' => 5];

// Tamu (belum login)
ok(Karya::bolehDilihat($disetujui, null, false),  'tamu: Disetujui terlihat');
ok(!Karya::bolehDilihat($menunggu, null, false),  'tamu: Menunggu tersembunyi');
ok(!Karya::bolehDilihat($ditolak, null, false),   'tamu: Ditolak tersembunyi');

// Anggota lain (login, tapi bukan pembuat)
ok(Karya::bolehDilihat($disetujui, 7, false),  'anggota lain: Disetujui terlihat');
ok(!Karya::bolehDilihat($menunggu, 7, false),  'anggota lain: Menunggu tersembunyi');
ok(!Karya::bolehDilihat($ditolak, 7, false),   'anggota lain: Ditolak tersembunyi');

// Pembuat sendiri
ok(Karya::bolehDilihat($disetujui, 5, false), 'pembuat: Disetujui terlihat');
ok(Karya::bolehDilihat($menunggu, 5, false),  'pembuat: Menunggu terlihat (cek dari profil)');
ok(Karya::bolehDilihat($ditolak, 5, false),   'pembuat: Ditolak terlihat (alasan tolak)');

// Admin
ok(Karya::bolehDilihat($disetujui, null, true), 'admin: Disetujui terlihat');
ok(Karya::bolehDilihat($menunggu, null, true),  'admin: Menunggu terlihat');
ok(Karya::bolehDilihat($ditolak, null, true),   'admin: Ditolak terlihat');

// Kasus tepi
ok(!Karya::bolehDilihat(null, 5, false), 'karya null → selalu tersembunyi');
ok(!Karya::bolehDilihat(null, 5, true),  'karya null → tetap tersembunyi meski admin');
ok(!Karya::bolehDilihat(['publikasi' => 'Menunggu'], 5, false), 'tanpa pembuat_id → tersembunyi');
ok(Karya::bolehDilihat(['publikasi' => 'Menunggu'], 5, true),   'tanpa pembuat_id → terlihat oleh admin');
