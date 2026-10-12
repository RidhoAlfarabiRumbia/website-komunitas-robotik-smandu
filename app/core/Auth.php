<?php

class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        if (self::terlaluPercobaan($username)) {
            return false;
        }

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

        // Anti session fixation: ID sesi baru setiap kali hak akses berubah.
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'anggota') {
            $anggota = Anggota::find((int) $user['anggota_id']);
            $_SESSION['anggota'] = $anggota;
        } else {
            $_SESSION['nama'] = 'Admin Robotik';
        }

        // Token CSRF ikut dirotasi setelah login agar tidak dipakai lintas sesi.
        unset($_SESSION['csrf_token']);
        return true;
    }

    /* ---------- Rate limit login sederhana (in-memory per server) ---------- */

    private const MAX_PERCOBAAN = 5;
    private const JEDA_PERCOBAAN = 900; // 15 menit

    private static function kunci(string $username): string
    {
        return 'login_' . sha1(strtolower(trim($username)));
    }

    private static function terlaluPercobaan(string $username): bool
    {
        $d = $_SESSION['rate_limit'] ?? [];
        $k = self::kunci($username);
        if (($d[$k]['jumlah'] ?? 0) >= self::MAX_PERCOBAAN) {
            return true;
        }
        return false;
    }

    private static function catatGagal(string $username): void
    {
        $k = self::kunci($username);
        $d = $_SESSION['rate_limit'] ?? [];
        $jumlah = (int) ($d[$k]['jumlah'] ?? 0) + 1;
        if ($jumlah >= self::MAX_PERCOBAAN) {
            $d[$k]['jumlah'] = self::MAX_PERCOBAAN;
            $d[$k]['sampai'] = time() + self::JEDA_PERCOBAAN;
        } else {
            $d[$k]['jumlah'] = $jumlah;
        }
        $_SESSION['rate_limit'] = $d;
    }

    private static function resetPercobaan(string $username): void
    {
        $d = $_SESSION['rate_limit'] ?? [];
        unset($d[self::kunci($username)]);
        $_SESSION['rate_limit'] = $d;
    }

    /** Sisa detik sebelum username boleh dicoba lagi; 0 = tidak terkunci. */
    public static function sisaJedaLogin(string $username): int
    {
        $d = $_SESSION['rate_limit'] ?? [];
        $k = self::kunci($username);
        if (($d[$k]['jumlah'] ?? 0) < self::MAX_PERCOBAAN) {
            return 0;
        }
        return max(0, (int) ($d[$k]['sampai'] ?? 0) - time());
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
        if (!in_array(self::role(), ['anggota', 'admin'], true)) {
            redirect('login.php');
        }
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
