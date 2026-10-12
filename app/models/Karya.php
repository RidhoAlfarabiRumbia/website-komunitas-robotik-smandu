<?php

class Karya extends Model
{
    protected static string $table = 'karya';
    protected static array $fillable = [
        'judul', 'kategori', 'deskripsi', 'progres', 'publikasi',
        'pembuat_id', 'teknologi', 'link_demo', 'foto', 'alasan_tolak',
    ];

    public static function approved(int $limit = 0): array
    {
        $sql = "SELECT k.*, a.nama AS pembuat_nama FROM karya k
                JOIN anggota a ON a.id = k.pembuat_id
                WHERE k.publikasi = 'Approved' ORDER BY k.dibuat_pada DESC";
        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int) $limit;
        }
        return self::db()->query($sql)->fetchAll();
    }

    public static function byStatus(string $status): array
    {
        $stmt = self::db()->prepare(
            "SELECT k.*, a.nama AS pembuat_nama FROM karya k
             JOIN anggota a ON a.id = k.pembuat_id
             WHERE k.publikasi = ? ORDER BY k.dibuat_pada DESC"
        );
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    public static function byPembuat(int $anggotaId): array
    {
        return self::where('pembuat_id', $anggotaId, 'dibuat_pada DESC');
    }

    public static function detail(int $id): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT k.*, a.nama AS pembuat_nama FROM karya k
             JOIN anggota a ON a.id = k.pembuat_id WHERE k.id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function jumlahByStatus(string $status): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM karya WHERE publikasi = ?');
        $stmt->execute([$status]);
        return (int) $stmt->fetchColumn();
    }

    public static function hitungPending(): int
    {
        return self::jumlahByStatus('Pending');
    }

    public static function moderasi(int $id, string $status, ?string $alasan = null): void
    {
        if (!in_array($status, ['Approved', 'Rejected'], true)) {
            return;
        }
        self::update($id, [
            'publikasi' => $status,
            'alasan_tolak' => $status === 'Rejected' ? $alasan : null,
        ]);
    }
}
