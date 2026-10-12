<?php
/**
 * Pemuat file .env sederhana (native PHP, tanpa library luar).
 */
function load_env(string $path): void
{
    if (!file_exists($path)) {
        die('File .env tidak ditemukan. Salin .env.example menjadi .env lalu sesuaikan.');
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

load_env(__DIR__ . '/../../.env');
