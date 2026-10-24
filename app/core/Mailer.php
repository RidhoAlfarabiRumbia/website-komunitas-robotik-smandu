<?php

/**
 * Pengirim email SMTP sederhana tanpa library eksternal.
 * Konfigurasi diambil dari .env:
 *   MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD,
 *   MAIL_ENCRYPTION (tls|ssl|none), MAIL_FROM, MAIL_FROM_NAME
 */
class Mailer
{
    public static function send(string $to, string $subject, string $html, string $text = ''): bool
    {
        $to = trim($to);
        if (!filter_var($to, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $to)) {
            return false;
        }

        $host = trim((string) getenv('MAIL_HOST'));
        $encryption = strtolower(trim((string) (getenv('MAIL_ENCRYPTION') ?: 'tls')));
        if (!in_array($encryption, ['tls', 'ssl', 'none'], true)) {
            error_log('[Mailer] MAIL_ENCRYPTION harus tls, ssl, atau none.');
            return false;
        }
        $port = (int) (getenv('MAIL_PORT') ?: ($encryption === 'ssl' ? 465 : 587));
        $username = (string) getenv('MAIL_USERNAME');
        $password = (string) getenv('MAIL_PASSWORD');
        $from = trim((string) (getenv('MAIL_FROM') ?: $username));
        $fromName = trim((string) (getenv('MAIL_FROM_NAME') ?: 'Portal Robotik'));

        if ($host === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
            error_log('[Mailer] MAIL_HOST/MAIL_FROM belum dikonfigurasi dengan benar.');
            return false;
        }

        // Header tidak boleh mengandung CR/LF (cegah header injection).
        $subject = str_replace(["\r", "\n"], ' ', $subject);
        $fromName = str_replace(["\r", "\n"], ' ', $fromName);
        $text = $text !== '' ? $text : strip_tags($html);

        $remote = ($encryption === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
        $context = stream_context_create([
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
        if (!is_resource($socket)) {
            error_log("[Mailer] Gagal terhubung ke $host:$port ($errno $errstr).");
            return false;
        }
        stream_set_timeout($socket, 15);

        try {
            self::harapkan($socket, [220], 'salam server');
            self::kirim($socket, 'EHLO ' . self::namaHost(), [250], 'EHLO');

            if ($encryption === 'tls') {
                self::kirim($socket, 'STARTTLS', [220], 'STARTTLS');
                if (!@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('Negosiasi TLS gagal.');
                }
                self::kirim($socket, 'EHLO ' . self::namaHost(), [250], 'EHLO setelah TLS');
            }

            if ($username !== '') {
                self::kirim($socket, 'AUTH LOGIN', [334], 'AUTH LOGIN');
                self::kirim($socket, base64_encode($username), [334], 'AUTH username');
                self::kirim($socket, base64_encode($password), [235], 'AUTH password');
            }

            self::kirim($socket, 'MAIL FROM:<' . $from . '>', [250], 'MAIL FROM');
            self::kirim($socket, 'RCPT TO:<' . $to . '>', [250, 251, 252], 'RCPT TO');
            self::kirim($socket, 'DATA', [354], 'DATA');

            $pesan = self::bangunPesan($from, $fromName, $to, $subject, $html, $text);
            self::tulis($socket, $pesan, 'isi email');
            self::harapkan($socket, [250], 'isi email');

            // QUIT boleh gagal tanpa membatalkan hasil pengiriman.
            @fwrite($socket, "QUIT\r\n");
            return true;
        } catch (Throwable $e) {
            error_log('[Mailer] ' . $e->getMessage());
            return false;
        } finally {
            if (is_resource($socket)) {
                fclose($socket);
            }
        }
    }

    private static function namaHost(): string
    {
        $host = gethostname() ?: 'localhost';
        return preg_replace('/[^a-zA-Z0-9.-]/', '', $host) ?: 'localhost';
    }

    private static function bangunPesan(
        string $from,
        string $fromName,
        string $to,
        string $subject,
        string $html,
        string $text
    ): string {
        $boundary = '=_robotik_' . bin2hex(random_bytes(12));
        $domain = str_contains($from, '@') ? substr(strrchr($from, '@'), 1) : 'localhost';
        $subject = function_exists('mb_encode_mimeheader')
            ? mb_encode_mimeheader($subject, 'UTF-8', 'B')
            : $subject;
        $fromName = function_exists('mb_encode_mimeheader')
            ? mb_encode_mimeheader($fromName, 'UTF-8', 'B')
            : $fromName;

        $headers = [
            'Date: ' . date(DATE_RFC2822),
            'From: ' . $fromName . ' <' . $from . '>',
            'To: <' . $to . '>',
            'Subject: ' . $subject,
            'Message-ID: <' . bin2hex(random_bytes(16)) . '@' . $domain . '>',
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];

        $body = [
            '--' . $boundary,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            '',
            rtrim(chunk_split(base64_encode($text), 76, "\r\n")),
            '--' . $boundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            '',
            rtrim(chunk_split(base64_encode($html), 76, "\r\n")),
            '--' . $boundary . '--',
        ];

        $pesan = implode("\r\n", $headers) . "\r\n\r\n" . implode("\r\n", $body);
        $pesan = preg_replace('/^\./m', '..', rtrim($pesan, "\r\n") . "\r\n");
        return $pesan . ".\r\n";
    }

    private static function kirim($socket, string $perintah, array $kodeDiharapkan, string $label): void
    {
        self::tulis($socket, $perintah . "\r\n", $label);
        self::harapkan($socket, $kodeDiharapkan, $label);
    }

    private static function tulis($socket, string $data, string $label): void
    {
        while ($data !== '') {
            $tertulis = @fwrite($socket, $data);
            if ($tertulis === false || $tertulis === 0) {
                throw new RuntimeException("SMTP $label gagal dikirim.");
            }
            $data = substr($data, $tertulis);
        }
    }

    private static function harapkan($socket, array $kodeDiharapkan, string $label): void
    {
        [$kode, $balasan] = self::baca($socket);
        if (!in_array($kode, $kodeDiharapkan, true)) {
            throw new RuntimeException("SMTP $label ditolak (kode $kode): $balasan");
        }
    }

    /** @return array{0:int,1:string} */
    private static function baca($socket): array
    {
        $baris = [];
        while (($line = fgets($socket, 515)) !== false) {
            $baris[] = rtrim($line, "\r\n");
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }
        $terakhir = $baris ? end($baris) : '';
        return [(int) substr((string) $terakhir, 0, 3), implode(' | ', $baris)];
    }
}
