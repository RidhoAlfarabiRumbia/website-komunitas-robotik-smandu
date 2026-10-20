<?php

/**
 * Pelatihan / Workshop (PRD 8.4).
 * Modul ini yang mengatur akses Materi: materi hanya terbuka bagi peserta
 * yang disetujui pada pelatihan terkait, selama periode pelatihan berjalan.
 */
class Pelatihan extends Model
{
    protected static string $table = 'pelatihan';
    protected static array $fillable = [
        'nama', 'deskripsi', 'tanggal_mulai', 'tanggal_selesai',
        'waktu', 'lokasi', 'mode', 'status', 'akses',
    ];

    public static function semua(): array
    {
        return self::db()->query(
            'SELECT p.*,
                    (SELECT COUNT(*) FROM pelatihan_peserta pp WHERE pp.pelatihan_id = p.id AND pp.status = "Disetujui") AS jumlah_disetujui,
                    (SELECT COUNT(*) FROM pelatihan_peserta pp WHERE pp.pelatihan_id = p.id AND pp.status = "Pending")   AS jumlah_pending
             FROM pelatihan p
             ORDER BY p.tanggal_mulai ASC'
        )->fetchAll();
    }

    /** Daftar yang boleh dilihat pengunjung umum: hanya pelatihan akses Umum. */
    public static function untukPengunjung(?int $limit = null): array
    {
        $sql = 'SELECT p.*,
                    (SELECT COUNT(*) FROM pelatihan_peserta pp WHERE pp.pelatihan_id = p.id AND pp.status = "Disetujui") AS jumlah_disetujui
             FROM pelatihan p
             WHERE p.akses = "Umum"
             ORDER BY p.tanggal_mulai ASC';
        if ($limit !== null && $limit > 0) {
            $sql .= ' LIMIT ' . $limit;
        }
        return self::db()->query($sql)->fetchAll();
    }

    /**
     * Aptika pelatihan masih berjalan (periode belum berakhir).
     */
    public static function aktif(?array $pelatihan): bool
    {
        if (!$pelatihan) return false;
        $selesai = $pelatihan['tanggal_selesai'] ?: $pelatihan['tanggal_mulai'];
        return strcmp((string) $selesai, date('Y-m-d')) >= 0;
    }

    /** Apakah pendaftaran peserta masih dibuka (status Dibuka, belum daftar, tanggal valid)? */
    public static function bisaDaftar(?array $pelatihan): bool
    {
        if (!$pelatihan) return false;
        if ($pelatihan['status'] !== 'Dibuka') return false;

        $today = date('Y-m-d');
        $mulai = $pelatihan['tanggal_mulai'] ?? null;
        $selesai = $pelatihan['tanggal_selesai'] ?? $pelatihan['tanggal_mulai'];

        // Tanggal mulai tidak boleh sudah lewat
        if ($mulai && $today > $mulai) return false;
        // Tanggal selesai tidak boleh sudah lewat
        if ($selesai && $today > $selesai) return false;

        return true;
    }

    /** Daftarkan seorang anggota sebagai peserta (menunggu persetujuan admin). */
    public static function daftar(int $pelatihanId, ?int $anggotaId, string $nama = ''): void
    {
        $a = $anggotaId ? (Anggota::find($anggotaId) ?: []) : [];

        self::db()->prepare(
            'INSERT INTO pelatihan_peserta (pelatihan_id, anggota_id, nama, kontak, asal, status)
             VALUES (?, ?, ?, ?, ?, "Pending")'
        )->execute([
            $pelatihanId,
            $anggotaId ?: null,
            $nama !== '' ? $nama : ($a['nama'] ?? '-'),
            $a['kontak'] ?? '-',
            $a['kelas'] ?? ($nama !== '' ? 'Pendaftaran umum' : 'SMA Negeri 2'),
        ]);
    }

    /** Daftarkan peserta umum (tanpa akun) pada pelatihan terbuka untuk umum. */
    public static function daftarUmum(int $pelatihanId, string $nama, string $kontak): void
    {
        self::db()->prepare(
            'INSERT INTO pelatihan_peserta (pelatihan_id, anggota_id, nama, kontak, asal, status)
             VALUES (?, NULL, ?, ?, "Umum", "Pending")'
        )->execute([$pelatihanId, $nama, $kontak]);
    }

    public static function jumlahDisetujui(int $pelatihanId): int
    {
        return (int) self::db()->query(
            'SELECT COUNT(*) FROM pelatihan_peserta WHERE pelatihan_id = ' . (int) $pelatihanId . ' AND status = "Disetujui"'
        )->fetchColumn();
    }

    public static function peserta(int $pelatihanId, string $status = ''): array
    {
        $sql = 'SELECT pp.*, a.kelas FROM pelatihan_peserta pp
                LEFT JOIN anggota a ON a.id = pp.anggota_id
                WHERE pp.pelatihan_id = ?';
        if ($status !== '') {
            $sql .= ' AND pp.status = ?';
        }
        $sql .= ' ORDER BY FIELD(pp.status, "Pending", "Disetujui", "Ditolak"), pp.dibuat_pada ASC';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($status !== '' ? [$pelatihanId, $status] : [$pelatihanId]);
        return $stmt->fetchAll();
    }

    /** Materi yang terkait dengan pelatihan ini (PRD 8.4). */
    public static function materi(int $pelatihanId): array
    {
        $stmt = self::db()->prepare(
            'SELECT m.* FROM materi m
             JOIN materi_pelatihan mp ON mp.materi_id = m.id
             WHERE mp.pelatihan_id = ?
             ORDER BY m.topik ASC, m.judul ASC'
        );
        $stmt->execute([$pelatihanId]);
        return $stmt->fetchAll();
    }

    /** Semua peserta lintas pelatihan (untuk halaman admin), termasuk nama pelatihan. */
    public static function semuaPeserta(string $status = ''): array
    {
        $sql = 'SELECT pp.*, a.kelas, p.nama AS pelatihan_nama
                FROM pelatihan_peserta pp
                LEFT JOIN anggota a ON a.id = pp.anggota_id
                LEFT JOIN pelatihan p ON p.id = pp.pelatihan_id';
        if ($status !== '') {
            $sql .= ' WHERE pp.status = ?';
        }
        $sql .= ' ORDER BY FIELD(pp.status, "Pending", "Disetujui", "Ditolak"), pp.dibuat_pada DESC';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($status !== '' ? [$status] : []);
        return $stmt->fetchAll();
    }

    /** Semua materi milik seorang peserta yang disetujui, selama pelatihan aktif. */
    public static function materiUntukAnggota(int $anggotaId, bool $aktifSaja = true, ?int $limit = null): array
    {
        $sql = 'SELECT m.*, p.nama AS pelatihan_nama, p.tanggal_mulai, p.tanggal_selesai
                FROM materi m
                JOIN materi_pelatihan mp ON mp.materi_id = m.id
                JOIN pelatihan p ON p.id = mp.pelatihan_id
                JOIN pelatihan_peserta pp ON pp.pelatihan_id = p.id AND pp.anggota_id = ?
                WHERE pp.status = "Disetujui"';
        if ($aktifSaja) {
            $sql .= ' AND (p.tanggal_selesai IS NULL OR p.tanggal_selesai = "" OR p.tanggal_selesai >= CURDATE())';
        }
        $sql .= ' GROUP BY m.id, p.nama, p.tanggal_mulai, p.tanggal_selesai ORDER BY p.tanggal_mulai DESC';
        if ($limit !== null && $limit > 0) {
            $sql .= ' LIMIT ' . $limit;
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute([$anggotaId]);
        return $stmt->fetchAll();
    }

    /** ID pelatihan yang sedang diikuti & disetujui anggota ini. */
    public static function idYangDiikuti(int $anggotaId, bool $aktifSaja = true): array
    {
        $sql = 'SELECT DISTINCT p.id FROM pelatihan p
                JOIN pelatihan_peserta pp ON pp.pelatihan_id = p.id
                WHERE pp.anggota_id = ? AND pp.status = "Disetujui"';
        if ($aktifSaja) {
            $sql .= ' AND (p.tanggal_selesai IS NULL OR p.tanggal_selesai = "" OR p.tanggal_selesai >= CURDATE())';
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute([$anggotaId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /** Jumlah peserta yang menunggu persetujuan admin (badge sidebar). */
    public static function pendingPeserta(): int
    {
        return (int) self::db()->query('SELECT COUNT(*) FROM pelatihan_peserta WHERE status = "Pending"')->fetchColumn();
    }

    /** Detail pelatihan + materialenya. */
    public static function detail(int $id): ?array
    {
        $row = self::find($id);
        if (!$row) return null;
        $row['jumlah_disetujui'] = self::jumlahDisetujui($id);
        return $row;
    }

    /** Peserta pada pelatihan atas nama anggota tertentu. */
    public static function pendaftaranAnggota(int $pelatihanId, int $anggotaId): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM pelatihan_peserta WHERE pelatihan_id = ? AND anggota_id = ? ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$pelatihanId, $anggotaId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function sudahTerdaftar(int $pelatihanId, int $anggotaId): bool
    {
        return self::pendaftaranAnggota($pelatihanId, $anggotaId) !== null;
    }
}