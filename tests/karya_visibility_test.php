<?php
/**
 * Tes aturan visibilitas karya: Karya::bolehDilihat() (murni, tanpa DB).
 * Jalankan: C:\xampp\php\php.exe tests/karya_visibility_test.php
 */
require_once __DIR__ . '/lib.php';

$approved = ['publikasi' => 'Approved', 'pembuat_id' => 5];
$pending  = ['publikasi' => 'Pending',  'pembuat_id' => 5];
$rejected = ['publikasi' => 'Rejected', 'pembuat_id' => 5];

// Tamu (belum login)
ok(Karya::bolehDilihat($approved, null, false),  'tamu: Approved terlihat');
ok(!Karya::bolehDilihat($pending, null, false),  'tamu: Pending tersembunyi');
ok(!Karya::bolehDilihat($rejected, null, false), 'tamu: Rejected tersembunyi');

// Anggota lain (login, tapi bukan pembuat)
ok(Karya::bolehDilihat($approved, 7, false),  'anggota lain: Approved terlihat');
ok(!Karya::bolehDilihat($pending, 7, false),  'anggota lain: Pending tersembunyi');
ok(!Karya::bolehDilihat($rejected, 7, false), 'anggota lain: Rejected tersembunyi');

// Pembuat sendiri
ok(Karya::bolehDilihat($approved, 5, false), 'pembuat: Approved terlihat');
ok(Karya::bolehDilihat($pending, 5, false),  'pembuat: Pending terlihat (cek dari profil)');
ok(Karya::bolehDilihat($rejected, 5, false), 'pembuat: Rejected terlihat (alasan tolak)');

// Admin
ok(Karya::bolehDilihat($approved, null, true), 'admin: Approved terlihat');
ok(Karya::bolehDilihat($pending, null, true),  'admin: Pending terlihat');
ok(Karya::bolehDilihat($rejected, null, true), 'admin: Rejected terlihat');

// Kasus tepi
ok(!Karya::bolehDilihat(null, 5, false), 'karya null → selalu tersembunyi');
ok(!Karya::bolehDilihat(null, 5, true),  'karya null → tetap tersembunyi meski admin');
ok(!Karya::bolehDilihat(['publikasi' => 'Pending'], 5, false), 'tanpa pembuat_id → tersembunyi');
ok(Karya::bolehDilihat(['publikasi' => 'Pending'], 5, true),   'tanpa pembuat_id → terlihat oleh admin');
