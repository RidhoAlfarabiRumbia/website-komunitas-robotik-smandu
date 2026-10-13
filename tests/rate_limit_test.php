<?php
/**
 * Tes rate limit login (Auth::attempt / sisaJedaLogin) memakai database
 * TERPISAH db_robotik_test. db_robotik TIDAK disentuh; database uji
 * dihapus di akir tes.
 * Jalankan: C:\xampp\php\php.exe tests/rate_limit_test.php
 */
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/../app/config/Database.php';

if (!extension_loaded('pdo_mysql')) {
    echo "  SKIP - pdo_mysql tidak tersedia. Jalankan dengan C:\\xampp\\php\\php.exe\n";
    return;
}

$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

try {
    $admin = new PDO("mysql:host=$host", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $admin->exec('DROP DATABASE IF EXISTS db_robotik_test');
    $admin->exec('CREATE DATABASE db_robotik_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $admin->exec('USE db_robotik_test');
    $admin->exec("CREATE TABLE login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username_hash CHAR(40) NOT NULL,
        attempts INT NOT NULL DEFAULT 1,
        locked_until DATETIME DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_username_hash (username_hash)
    ) ENGINE=InnoDB");
    $admin->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(60) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin','anggota') NOT NULL DEFAULT 'anggota',
        anggota_id INT DEFAULT NULL
    ) ENGINE=InnoDB");
} catch (PDOException $e) {
    echo "  SKIP - MySQL tidak bisa dihubungi: " . $e->getMessage() . "\n";
    return;
}

// Arahkan koneksi aplikasi ke database uji SEBELUM koneksi pertama.
putenv('DB_NAME=db_robotik_test');

$u = 'tes_rate_limit_' . substr(uniqid(), -6);

// Percobaan gagal 1-4: tercatat, tapi belum terkunci.
for ($i = 1; $i <= 4; $i++) {
    ok(Auth::attempt($u, 'salah') === false, "percobaan gagal ke-$i → false");
    ok(Auth::sisaJedaLogin($u) === 0, "percobaan gagal ke-$i → belum terkunci");
}

// Percobaan ke-5: terkunci ±15 menit.
ok(Auth::attempt($u, 'salah') === false, 'percobaan gagal ke-5 → false');
$sisa = Auth::sisaJedaLogin($u);
ok($sisa > 0 && $sisa <= 900, "terkunci setelah 5x gagal (sisa {$sisa}s)");
ok(Auth::attempt($u, 'password_benar_pun') === false, 'masih terkunci → attempt() selalu false');

// User valid di database uji: setelah lock kedaluwarsa, password benar
// harus sukses dan counter direset.
$db = Database::connect();
$stmt = $db->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'admin')");
$stmt->execute([$u, password_hash('rahasia123', PASSWORD_DEFAULT)]);

$hash = sha1(strtolower(trim($u)));
// Pakai waktu PHP (bukan NOW() MySQL): zona waktu server bisa berbeda,
// dan Auth menghitung lock dengan time()/date() PHP.
$lalu = date('Y-m-d H:i:s', time() - 60);
$db->prepare('UPDATE login_attempts SET locked_until = ? WHERE username_hash = ?')
    ->execute([$lalu, $hash]);
ok(Auth::sisaJedaLogin($u) === 0, 'lock kedaluwarsa → sisaJedaLogin = 0');
ok(Auth::attempt($u, 'rahasia123') === true, 'password benar setelah lock expired → sukses');
ok(Auth::sisaJedaLogin($u) === 0, 'login sukses → counter direset');

// Bereskan: hapus database uji. db_robotik TIDAK disentuh.
$admin->exec('DROP DATABASE IF EXISTS db_robotik_test');
echo "  (database uji db_robotik_test sudah dihapus)\n";
