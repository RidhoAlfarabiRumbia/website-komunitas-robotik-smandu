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
        $sql = "SELECT k.id, k.judul, k.deskripsi, k.progres, k.publikasi, k.pembuat_id, k.teknologi, k.link_demo, k.foto, k.dibuat_pada, a.nama AS pembuat_nama FROM karya k
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
        if ($anggotaId === null || !isset($k['pembuat_id'])) {
            return false;
        }
        return $anggotaId === (int) $k['pembuat_id'];
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
