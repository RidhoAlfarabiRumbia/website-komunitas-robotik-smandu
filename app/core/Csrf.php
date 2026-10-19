<?php

class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::token() . '">';
    }

    public static function verify(): void
    {
        // csrf_token[]=x membuat nilainya array; hash_equals() bertipe string
        // dan akan melempar TypeError. Perlakukan sebagai token tidak valid.
        $sent = $_POST['csrf_token'] ?? null;
        if (!is_scalar($sent)) {
            $sent = '';
        }
        $sent = (string) $sent;
        if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $sent)) {
            http_response_code(403);
            die('Permintaan tidak valid (token keamanan tidak cocok). Silakan muat ulang halaman.');
        }
    }
}
