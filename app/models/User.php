<?php

class User extends Model
{
    protected static string $table = 'users';
    protected static array $fillable = ['username', 'email', 'password_hash', 'role', 'anggota_id'];

    /**
     * Setiap perubahan email atau kata sandi membatalkan tautan reset lama.
     * Ini mencegah tautan yang sudah beredar tetap berlaku setelah akun diubah.
     */
    public static function update(int $id, array $data): bool
    {
        $sensitif = array_key_exists('email', $data) || array_key_exists('password_hash', $data);
        $ok = parent::update($id, $data);
        if ($ok && $sensitif) {
            PasswordReset::batalkanTokenUser($id);
        }
        return $ok;
    }

    public static function findByAnggota(int $anggotaId): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE anggota_id = ? LIMIT 1');
        $stmt->execute([$anggotaId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            return null;
        }
        $stmt = self::db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function usernameTaken(string $username, int $exceptId = 0): bool
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM users WHERE username = ? AND id <> ?');
        $stmt->execute([$username, $exceptId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function emailTaken(string $email, int $exceptId = 0): bool
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            return false;
        }
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?');
        $stmt->execute([$email, $exceptId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function createForAnggota(
        int $anggotaId,
        string $username,
        string $password,
        ?string $email = null
    ): int {
        $email = $email !== null && trim($email) !== '' ? strtolower(trim($email)) : null;
        return self::create([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'anggota',
            'anggota_id' => $anggotaId,
        ]);
    }

    /**
     * Hapus akun login milik seorang anggota.
     * Dipanggil sebelum baris anggota dihapus. Database sebenarnya sudah memakai
     * ON DELETE CASCADE pada users.anggota_id, jadi ini lapisan pengaman tambahan
     * (penting bila DB diimpor dari skema lama atau FK belum dibuat).
     */
    public static function deleteByAnggota(int $anggotaId): bool
    {
        $stmt = static::db()->prepare('DELETE FROM users WHERE anggota_id = ?');
        return $stmt->execute([$anggotaId]);
    }

    public static function setPassword(int $userId, string $password): bool
    {
        return self::update($userId, [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }

    /** Verifikasi kata sandi sebuah akun user. */
    public static function verifyPassword(int $userId, string $password): bool
    {
        $user = self::find($userId);
        return ((bool) $user) && password_verify($password, $user['password_hash']);
    }

    /**
     * Ubah username, email, dan/atau kata sandi akun milik user sendiri
     * (halaman Profil anggota & admin). Kata sandi lama wajib cocok;
     * $passwordBaru '' berarti sandi tidak diganti. $email null berarti
     * email tidak ikut diubah.
     *
     * @return string[] daftar pesan error — kosong bila berhasil
     */
    public static function ubahAkun(
        int $userId,
        string $username,
        string $passwordLama,
        string $passwordBaru,
        ?string $email = null
    ): array {
        $errors = [];
        $user = self::find($userId);
        if (!$user) {
            return ['Akun tidak ditemukan.'];
        }

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
        } elseif (!password_verify($passwordLama, $user['password_hash'])) {
            $errors[] = 'Kata sandi saat ini salah.';
        }

        if ($passwordBaru !== '' && strlen($passwordBaru) < 8) {
            $errors[] = 'Kata sandi baru minimal 8 karakter.';
        }

        if ($email !== null) {
            $email = strtolower(trim($email));
            if ($email === '') {
                $errors[] = 'Email wajib diisi.';
            } elseif (strlen($email) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Format email tidak valid.';
            } elseif (self::emailTaken($email, $userId)) {
                $errors[] = 'Email sudah dipakai akun lain.';
            }
        }

        if ($errors) {
            return $errors;
        }

        $data = ['username' => $username];
        if ($passwordBaru !== '') {
            $data['password_hash'] = password_hash($passwordBaru, PASSWORD_DEFAULT);
        }
        $emailLama = strtolower(trim((string) ($user['email'] ?? '')));
        if ($email !== null && $email !== $emailLama) {
            $data['email'] = $email;
        }
        self::update($userId, $data);

        return [];
    }
}
