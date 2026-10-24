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

    public static function verify(?callable $onFailure = null): void
    {
        // csrf_token[]=x membuat nilainya array; hash_equals() bertipe string
        // dan akan melempar TypeError. Perlakukan sebagai token tidak valid.
        $sent = $_POST['csrf_token'] ?? null;
        if (!is_scalar($sent)) {
            $sent = '';
        }
        $sent = (string) $sent;
        if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $sent)) {
            if ($onFailure !== null) {
                $onFailure();
                return;
            }
            http_response_code(403);
            die('Permintaan tidak valid (token keamanan tidak cocok). Silakan muat ulang halaman.');
        }
    }

    public static function sameOrigin(): bool
    {
        $requestOrigin = self::origin(self::scheme() . '://' . ($_SERVER['HTTP_HOST'] ?? ''));
        $appOrigin = self::origin(app_url(''));
        $source = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ($source === '' && !empty($_SERVER['HTTP_REFERER'])) {
            $source = $_SERVER['HTTP_REFERER'];
        }
        if ($source === '') {
            return ($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '') === 'same-origin';
        }
        $sourceOrigin = self::origin($source);
        if ($sourceOrigin === null) {
            return false;
        }
        return ($requestOrigin !== null && hash_equals($requestOrigin, $sourceOrigin))
            || ($appOrigin !== null && hash_equals($appOrigin, $sourceOrigin));
    }

    private static function scheme(): string
    {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
            || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
        return $https ? 'https' : 'http';
    }

    private static function origin(string $url): ?string
    {
        $parts = parse_url($url);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }
        $scheme = strtolower($parts['scheme']);
        $host = strtolower($parts['host']);
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
        return $scheme . '://' . $host . ':' . $port;
    }
}
