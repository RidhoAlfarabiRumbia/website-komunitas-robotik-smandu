<?php

class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        if (self::terlaluPercobaan($username)) {
            return false;
        }

        // Anti session fixation: regenerate ID SEBELUM set session data
        session_regenerate_id(true);

        $stmt = Database::connect()->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Selalu jalankan password_verify meski user tidak ada, agar waktu
        // merespons tidak membocorkan username mana yang terdaftar.
        $hash = $user['password_hash'] ?? '$2y$10$invalidinvalidinvalidinvalidinvalidinvalidinvalidinvalidiu';
        $cocok = password_verify($password, $hash);

        if (!$user || !$cocok) {
            self::catatGagal($username);
            return false;
        }
        self::resetPercobaan($username);

        // Cek keabsahan anggota DULUAN, sebelum apa pun ditulis ke session.
        // Kalau ini tidak diperiksa di sini dan kita return false setelah
        // $_SESSION['user_id'] terisi, sesi berisi "sudah masuk" padahal
        // datanya tidak ada — halaman berikutnya mengira pengguna masuk.
        $anggota = null;
        if ($user['role'] === 'anggota') {
            $anggota = Anggota::find((int) $user['anggota_id']);
            // Akun tanpa baris anggota (mis. anggota dihapus dari panel admin)
            // tidak boleh masuk: halamannya akan kosong semua.
            if (!$anggota) {
                return false;
            }
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'anggota') {
            $_SESSION['anggota'] = $anggota;
        } else {
            $_SESSION['nama'] = 'Admin Robotik';
        }

        // Token CSRF ikut dirotasi setelah login agar tidak dipakai lintas sesi.
        unset($_SESSION['csrf_token']);
        return true;
    }

    /* ---------- Rate limit login (database-backed) ---------- */

    private const MAX_PERCOBAAN = 5;
    private const JEDA_PERCOBAAN = 900; // 15 menit

    private static function kunci(string $username): string
    {
        return sha1(strtolower(trim($username)));
    }

    private static function terlaluPercobaan(string $username): bool
    {
        $hash = self::kunci($username);
        $stmt = Database::connect()->prepare(
            'SELECT attempts, locked_until FROM login_attempts WHERE username_hash = ?'
        );
        $stmt->execute([$hash]);
        $row = $stmt->fetch();
        if (!$row) return false;
        if ($row['attempts'] >= self::MAX_PERCOBAAN) {
            if ($row['locked_until'] && strtotime($row['locked_until']) > time()) {
                return true;
            }
            // Lock expired, reset
            self::resetPercobaan($username);
        }
        return false;
    }

    private static function catatGagal(string $username): void
    {
        $hash = self::kunci($username);
        $db = Database::connect();
        $stmt = $db->prepare('SELECT attempts FROM login_attempts WHERE username_hash = ?');
        $stmt->execute([$hash]);
        $row = $stmt->fetch();
        $now = date('Y-m-d H:i:s');
        if ($row) {
            $attempts = (int) $row['attempts'] + 1;
            if ($attempts >= self::MAX_PERCOBAAN) {
                $locked = date('Y-m-d H:i:s', time() + self::JEDA_PERCOBAAN);
                $db->prepare('UPDATE login_attempts SET attempts = ?, locked_until = ?, updated_at = ? WHERE username_hash = ?')
                    ->execute([$attempts, $locked, $now, $hash]);
            } else {
                $db->prepare('UPDATE login_attempts SET attempts = ?, updated_at = ? WHERE username_hash = ?')
                    ->execute([$attempts, $now, $hash]);
            }
        } else {
            $db->prepare('INSERT INTO login_attempts (username_hash, attempts, created_at, updated_at) VALUES (?, 1, ?, ?)')
                ->execute([$hash, $now, $now]);
        }
    }

    private static function resetPercobaan(string $username): void
    {
        $hash = self::kunci($username);
        Database::connect()->prepare('DELETE FROM login_attempts WHERE username_hash = ?')
            ->execute([$hash]);
    }

    /** Sisa detik sebelum username boleh dicoba lagi; 0 = tidak terkunci. */
    public static function sisaJedaLogin(string $username): int
    {
        $hash = self::kunci($username);
        $stmt = Database::connect()->prepare(
            'SELECT attempts, locked_until FROM login_attempts WHERE username_hash = ?'
        );
        $stmt->execute([$hash]);
        $row = $stmt->fetch();
        if (!$row) return 0;
        if ((int) $row['attempts'] < self::MAX_PERCOBAAN) return 0;
        if (!$row['locked_until']) return 0;
        return max(0, strtotime($row['locked_until']) - time());
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function role(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    /** Id anggota milik user yang sedang login (null untuk admin). */
    public static function anggotaId(): ?int
    {
        $a = self::anggota();
        return $a ? (int) $a['id'] : null;
    }

    /** Refresh snapshot anggota di session (dipanggil setelah edit profil). */
    public static function refreshAnggota(): void
    {
        if (self::role() === 'anggota' && self::anggotaId()) {
            $_SESSION['anggota'] = Anggota::find((int) self::anggotaId());
        }
    }

    public static function anggota(): ?array
    {
        return $_SESSION['anggota'] ?? null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect('login.php');
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireLogin();
        if (self::role() !== $role) {
            redirect(self::role() === 'admin' ? 'admin/dashboard.php' : 'beranda.php');
        }
    }

    public static function requireMember(): void
    {
        self::requireLogin();
        if (!in_array(self::role(), self::memberRoles(), true)) {
            redirect('login.php');
        }
    }

    /** Roles yang boleh akses halaman khusus anggota (extensible). */
    public static function memberRoles(): array
    {
        return ['anggota', 'admin'];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $p['path'],
                'domain' => $p['domain'],
                'secure' => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => 'Lax',
            ]);
        }
        session_destroy();
    }
}
