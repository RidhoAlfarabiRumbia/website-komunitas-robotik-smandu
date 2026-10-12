<?php

class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance === null) {
            $host = getenv('DB_HOST');
            $name = getenv('DB_NAME');
            $user = getenv('DB_USER');
            $pass = getenv('DB_PASS');

            $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                // Jangan pernah menampilkan detail koneksi ke pengunjung:
                // pesan PDO membocorkan host, nama database, dan nama user.
                error_log('[DB] ' . $e->getMessage());
                http_response_code(503);
                die('Layanan sedang tidak tersedia. Silakan coba beberapa saat lagi.');
            }
        }
        return self::$instance;
    }
}
