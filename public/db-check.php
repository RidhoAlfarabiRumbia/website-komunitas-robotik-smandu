<?php
// File diagnostik sementara. Hapus setelah selesai.
require_once __DIR__ . '/../app/includes/bootstrap.php';

$host = env_val('DB_HOST');
$name = env_val('DB_NAME');
$user = env_val('DB_USER');
$pass = env_val('DB_PASS');

echo "PHP version: " . PHP_VERSION . "\n";
echo "putenv exists: " . var_export(function_exists('putenv'), true) . "\n";
echo "getenv exists: " . var_export(function_exists('getenv'), true) . "\n";
echo "open_basedir: " . (ini_get('open_basedir') ?: '(kosong)') . "\n";
echo "DB_HOST: " . ($host !== '' ? 'TERISI' : 'KOSONG') . "\n";
echo "DB_NAME: " . ($name !== '' ? 'TERISI' : 'KOSONG') . "\n";
echo "DB_USER: " . ($user !== '' ? 'TERISI' : 'KOSONG') . "\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "KONEKSI DB: OK\n";
} catch (Throwable $e) {
    echo "KONEKSI DB: GAGAL\n";
    echo "Pesan: " . $e->getMessage() . "\n";
}
