<?php

class Karya extends Model
{
    protected static string $table = 'karya';
    protected static array $fillable = [
        'judul', 'deskripsi', 'progres', 'publikasi',
        'pembuat_id', 'teknologi', 'link_demo', 'foto', 'alasan_tolak',
    ];

    public static function approved(int $limit = 0): array
    {
        $sql = "SELECT k.id, k.judul, k.deskripsi, k.progres, k.publikasi, k.pembuat_id, k.teknologi, k.link_demo, k.foto, k.dibuat_pada,
                       COALESCE((SELECT GROUP_CONCAT(a2.nama ORDER BY a2.nama SEPARATOR ', ')
                                 FROM karya_pembuat kp JOIN anggota a2 ON a2.id = kp.anggota_id
                                 WHERE kp.karya_id = k.id), a.nama) AS pembuat_nama
                FROM karya k
                JOIN anggota a ON a.id = k.pembuat_id
                WHERE k.publikasi = 'Disetujui' ORDER BY k.dibuat_pada DESC";
        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int) $limit;
        }
        return self::db()->query($sql)->fetchAll();
    }

    public static function byStatus(string $status): array
    {
        $stmt = self::db()->prepare(
            "SELECT k.*,
                    COALESCE((SELECT GROUP_CONCAT(a2.nama ORDER BY a2.nama SEPARATOR ', ')
                              FROM karya_pembuat kp JOIN anggota a2 ON a2.id = kp.anggota_id
                              WHERE kp.karya_id = k.id), a.nama) AS pembuat_nama
             FROM karya k
             JOIN anggota a ON a.id = k.pembuat_id
             WHERE k.publikasi = ? ORDER BY k.dibuat_pada DESC"
        );
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    public static function byPembuat(int $anggotaId): array
    {
        $stmt = self::db()->prepare(
            "SELECT k.*,
                    COALESCE((SELECT GROUP_CONCAT(a2.nama ORDER BY a2.nama SEPARATOR ', ')
                              FROM karya_pembuat kp2 JOIN anggota a2 ON a2.id = kp2.anggota_id
                              WHERE kp2.karya_id = k.id), a.nama) AS pembuat_nama
             FROM karya k
             JOIN anggota a ON a.id = k.pembuat_id
             JOIN karya_pembuat kp ON kp.karya_id = k.id
             WHERE kp.anggota_id = ?
             ORDER BY k.dibuat_pada DESC"
        );
        $stmt->execute([$anggotaId]);
        return $stmt->fetchAll();
    }

    public static function detail(int $id): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT k.*, a.kelas AS pembuat_kelas,
                    COALESCE((SELECT GROUP_CONCAT(a2.nama ORDER BY a2.nama SEPARATOR ', ')
                              FROM karya_pembuat kp JOIN anggota a2 ON a2.id = kp.anggota_id
                              WHERE kp.karya_id = k.id), a.nama) AS pembuat_nama
             FROM karya k
             JOIN anggota a ON a.id = k.pembuat_id WHERE k.id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function pembuat(int $karyaId): array
    {
        $stmt = self::db()->prepare(
            'SELECT a.id, a.nama, a.kelas FROM karya_pembuat kp
             JOIN anggota a ON a.id = kp.anggota_id
             WHERE kp.karya_id = ? ORDER BY a.nama ASC'
        );
        $stmt->execute([$karyaId]);
        return $stmt->fetchAll();
    }

    public static function validPembuatIds(array $anggotaIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $anggotaIds), static fn(int $id): bool => $id > 0)));
        if (!$ids) {
            return [];
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = self::db()->prepare('SELECT id FROM anggota WHERE id IN (' . $in . ')');
        $stmt->execute($ids);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public static function simpanPembuat(int $karyaId, array $anggotaIds): void
    {
        $valid = self::validPembuatIds($anggotaIds);
        self::db()->prepare('DELETE FROM karya_pembuat WHERE karya_id = ?')->execute([$karyaId]);
        if (!$valid) {
            return;
        }
        $stmt = self::db()->prepare('INSERT INTO karya_pembuat (karya_id, anggota_id) VALUES (?, ?)');
        foreach ($valid as $id) {
            $stmt->execute([$karyaId, $id]);
        }
    }

    public static function pembuatMap(array $karyaIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $karyaIds), static fn(int $id): bool => $id > 0)));
        if (!$ids) {
            return [];
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = self::db()->prepare(
            "SELECT kp.karya_id, GROUP_CONCAT(a.nama ORDER BY a.nama SEPARATOR ', ') AS nama
             FROM karya_pembuat kp JOIN anggota a ON a.id = kp.anggota_id
             WHERE kp.karya_id IN ($in) GROUP BY kp.karya_id"
        );
        $stmt->execute($ids);
        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[(int) $row['karya_id']] = (string) $row['nama'];
        }
        return $map;
    }

    /**
     * Aturan visibilitas karya (PRD 8.7 + keamanan): Disetujui terlihat semua
     * orang; Menunggu/Ditolak hanya admin atau pembuatnya sendiri.
     * Anggota login lain TIDAK boleh membuka karya yang belum tayang lewat URL.
     */
    public static function bolehDilihat(?array $k, ?int $anggotaId, bool $isAdmin): bool
    {
        if (!$k) {
            return false;
        }
        if (($k['publikasi'] ?? '') === 'Disetujui') {
            return true;
        }
        if ($isAdmin) {
            return true;
        }
        if ($anggotaId === null || !isset($k['id'])) {
            return false;
        }
        $stmt = self::db()->prepare('SELECT 1 FROM karya_pembuat WHERE karya_id = ? AND anggota_id = ? LIMIT 1');
        $stmt->execute([(int) $k['id'], $anggotaId]);
        if ($stmt->fetchColumn()) {
            return true;
        }
        return isset($k['pembuat_id']) && $anggotaId === (int) $k['pembuat_id'];
    }

    public static function jumlahByStatus(string $status): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM karya WHERE publikasi = ?');
        $stmt->execute([$status]);
        return (int) $stmt->fetchColumn();
    }

    public static function hitungMenunggu(): int
    {
        return self::jumlahByStatus('Menunggu');
    }

    public static function moderasi(int $id, string $status, ?string $alasan = null): void
    {
        if (!in_array($status, ['Disetujui', 'Ditolak'], true)) {
            return;
        }
        self::update($id, [
            'publikasi' => $status,
            'alasan_tolak' => $status === 'Ditolak' ? $alasan : null,
        ]);
    }
}
