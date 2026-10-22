<?php

class Anggota extends Model
{
    protected static string $table = 'anggota';
    protected static array $fillable = ['nama', 'kelas', 'fokus', 'kontak', 'deskripsi', 'foto'];

    /** Cari anggota berdasarkan nama (dan kelas bila diisi). */
    public static function cari(string $nama, string $kelas = ''): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM anggota WHERE nama = ? LIMIT 1');
        $stmt->execute([$nama]);
        $row = $stmt->fetch();
        if ($row && $kelas !== '' && $row['kelas'] !== $kelas) return null;
        return $row ?: null;
    }

    public static function jumlahKarya(int $anggotaId): int
    {
        $stmt = self::db()->prepare("SELECT COUNT(*) FROM karya WHERE pembuat_id = ? AND publikasi = 'Disetujui'");
        $stmt->execute([$anggotaId]);
        return (int) $stmt->fetchColumn();
    }

    public static function lombaDiikuti(int $anggotaId): array
    {
        $stmt = self::db()->prepare(
            'SELECT l.* FROM lomba l
             JOIN lomba_peserta lp ON lp.lomba_id = l.id
             WHERE lp.anggota_id = ?'
        );
        $stmt->execute([$anggotaId]);
        return $stmt->fetchAll();
    }

    /** Riwayat pelatihan yang pernah diikuti (PRD 8.4 & 8.13). */
    public static function riwayatPelatihan(int $anggotaId, bool $hanyaDisetujui = true): array
    {
        $sql = 'SELECT p.*, pp.status AS status_pendaftaran
                FROM pelatihan p
                JOIN pelatihan_peserta pp ON pp.pelatihan_id = p.id
                WHERE pp.anggota_id = ?';
        if ($hanyaDisetujui) $sql .= ' AND pp.status = "Disetujui"';
        $sql .= ' ORDER BY p.tanggal_mulai DESC';
        $stmt = self::db()->prepare($sql);
        $stmt->execute([$anggotaId]);
        return $stmt->fetchAll();
    }

    public static function jumlahPelatihan(int $anggotaId): int
    {
        $stmt = self::db()->prepare(
            'SELECT COUNT(*) FROM pelatihan_peserta WHERE anggota_id = ? AND status = "Disetujui"'
        );
        $stmt->execute([$anggotaId]);
        return (int) $stmt->fetchColumn();
    }

    public static function jumlahPrestasi(int $anggotaId): int
    {
        $stmt = self::db()->prepare(
            'SELECT COUNT(*) FROM prestasi_peserta pp
             JOIN prestasi p ON p.id = pp.prestasi_id
             WHERE pp.anggota_id = ? AND p.status = "Tayang"'
        );
        $stmt->execute([$anggotaId]);
        return (int) $stmt->fetchColumn();
    }

    /** Status kelengkapan Pakta Integritas (PRD 8.13). */
    public static function paktaIntegritas(int $anggotaId): array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM pendaftaran WHERE anggota_id = ? ORDER BY dibuat_pada DESC LIMIT 1"
        );
        $stmt->execute([$anggotaId]);
        $row = $stmt->fetch();
        if (!$row) return ['status' => null, 'lengkap' => false];
        return [
            'status' => $row['status'],
            'lengkap' => $row['status'] === 'Diverifikasi',
            'tanggal' => $row['diverifikasi_pada'] ?: $row['dibuat_pada'],
        ];
    }
}