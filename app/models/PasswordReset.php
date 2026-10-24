<?php

/**
 * Token reset kata sandi. Token asli hanya dikirim ke pengguna; database
 * menyimpan hash SHA-256 agar bocoran tabel tidak bisa dipakai langsung.
 */
class PasswordReset extends Model
{
    protected static string $table = 'password_resets';
    protected static array $fillable = [
        'user_id', 'email_hash', 'token_hash', 'requested_ip', 'expires_at', 'used_at',
    ];

    public static function emailHash(string $email): string
    {
        return hash('sha256', strtolower(trim($email)));
    }

    /**
     * Batasi permintaan: 3 per email dan 10 per IP dalam 15 menit, plus jeda
     * 60 detik antar-permintaan untuk email yang sama.
     */
    public static function terlaluSering(string $emailHash, ?string $ip): bool
    {
        // Bersihkan sesekali saja agar tidak menulis ke database tiap request.
        if (random_int(1, 20) === 1) {
            self::bersihkan();
        }

        $db = self::db();
        $stmt = $db->prepare(
            'SELECT COUNT(*) FROM password_resets
             WHERE email_hash = ? AND created_at > (NOW() - INTERVAL 15 MINUTE)'
        );
        $stmt->execute([$emailHash]);
        if ((int) $stmt->fetchColumn() >= 3) {
            return true;
        }

        if ($ip !== null && $ip !== '') {
            $stmt = $db->prepare(
                'SELECT COUNT(*) FROM password_resets
                 WHERE requested_ip = ? AND created_at > (NOW() - INTERVAL 15 MINUTE)'
            );
            $stmt->execute([$ip]);
            if ((int) $stmt->fetchColumn() >= 10) {
                return true;
            }
        }

        $stmt = $db->prepare(
            'SELECT COUNT(*) FROM password_resets
             WHERE email_hash = ? AND created_at > (NOW() - INTERVAL 60 SECOND)'
        );
        $stmt->execute([$emailHash]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Catat permintaan. user_id/ token_hash boleh NULL untuk email yang tidak
     * terdaftar; baris itu hanya dipakai untuk rate limit.
     */
    public static function catat(
        string $email,
        ?int $userId,
        ?string $tokenHash,
        ?string $expiresAt,
        ?string $ip
    ): int {
        if ($userId !== null) {
            self::batalkanTokenUser($userId);
        }

        return self::create([
            'user_id' => $userId,
            'email_hash' => self::emailHash($email),
            'token_hash' => $tokenHash,
            'requested_ip' => $ip !== null ? substr($ip, 0, 45) : null,
            'expires_at' => $expiresAt,
        ]);
    }

    public static function cariValid(string $tokenHash): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM password_resets
             WHERE token_hash = ? AND used_at IS NULL AND expires_at IS NOT NULL
             ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$tokenHash]);
        $row = $stmt->fetch();
        if (!$row || strtotime((string) $row['expires_at']) <= time()) {
            return null;
        }
        return $row;
    }

    public static function tandaiTerpakai(int $id): void
    {
        self::db()->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')
            ->execute([$id]);
    }

    /** Token lama tidak boleh dipakai lagi setelah password/email berubah. */
    public static function batalkanTokenUser(int $userId): void
    {
        self::db()->prepare(
            'UPDATE password_resets SET used_at = NOW()
             WHERE user_id = ? AND used_at IS NULL'
        )->execute([$userId]);
    }

    public static function bersihkan(): void
    {
        self::db()->exec(
            'DELETE FROM password_resets WHERE created_at < (NOW() - INTERVAL 7 DAY)'
        );
    }
}
