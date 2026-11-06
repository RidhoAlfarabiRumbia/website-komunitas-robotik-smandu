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
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
            continue;
        }
        $value = trim($value);
        if (preg_match('/^(["\'])(.*)\1\s*(?:#.*)?$/s', $value, $m)) {
            $value = $m[2];
            if ($m[1] === '"') {
                $value = str_replace(['\\n', '\\r', '\\t', '\\"', '\\\\'], ["\n", "\r", "\t", '"', '\\'], $value);
            }
        } else {
            $value = trim((string) preg_replace('/(?:^|\s+)#.*$/', '', $value));
        }
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

load_env(__DIR__ . '/../../.env');
