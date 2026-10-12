<?php

class User extends Model
{
    protected static string $table = 'users';
    protected static array $fillable = ['username', 'password_hash', 'role', 'anggota_id'];

    public static function findByAnggota(int $anggotaId): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE anggota_id = ? LIMIT 1');
        $stmt->execute([$anggotaId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function usernameTaken(string $username, int $exceptId = 0): bool
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM users WHERE username = ? AND id <> ?');
        $stmt->execute([$username, $exceptId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function createForAnggota(int $anggotaId, string $username, string $password): int
    {
        return self::create([
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'anggota',
            'anggota_id' => $anggotaId,
        ]);
    }

    public static function setPassword(int $userId, string $password): bool
    {
        $stmt = self::db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        return $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $userId]);
    }

    /** Verifikasi kata sandi sebuah akun user. */
    public static function verifyPassword(int $userId, string $password): bool
    {
        $user = self::find($userId);
        return ((bool) $user) && password_verify($password, $user['password_hash']);
    }

    /**
     * Ubah username dan/atau kata sandi akun milik user sendiri
     * (halaman Profil anggota & admin). Kata sandi lama wajib cocok;
     * $passwordBaru '' berarti sandi tidak diganti.
     *
     * @return string[] daftar pesan error — kosong bila berhasil
     */
    public static function ubahAkun(int $userId, string $username, string $passwordLama, string $passwordBaru): array
    {
        $errors = [];
        $username = trim($username);

        if ($username === '') {
            $errors[] = 'Username wajib diisi.';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username minimal 3 karakter.';
        } elseif (self::usernameTaken($username, $userId)) {
            $errors[] = 'Username sudah dipakai akun lain.';
        }

        if ($passwordLama === '') {
            $errors[] = 'Kata sandi saat ini wajib diisi.';
        } elseif (!self::verifyPassword($userId, $passwordLama)) {
            $errors[] = 'Kata sandi saat ini salah.';
        }

        if ($passwordBaru !== '' && strlen($passwordBaru) < 8) {
            $errors[] = 'Kata sandi baru minimal 8 karakter.';
        }

        if ($errors) {
            return $errors;
        }

        $data = ['username' => $username];
        if ($passwordBaru !== '') {
            $data['password_hash'] = password_hash($passwordBaru, PASSWORD_DEFAULT);
        }
        self::update($userId, $data);

        return [];
    }
}
