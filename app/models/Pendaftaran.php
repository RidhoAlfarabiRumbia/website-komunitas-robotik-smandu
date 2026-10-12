<?php

/**
 * Pendaftaran Anggota Baru + Pakta Integritas (PRD 8.12).
 * Diisi oleh calon anggota tanpa login. Berkas hasil pindai Pakta Integritas
 * disimpan di storage/terbatas sehingga hanya admin yang dapat membukanya.
 */
class Pendaftaran extends Model
{
    protected static string $table = 'pendaftaran';
    protected static array $fillable = [
        'nama', 'kelas', 'kontak', 'email', 'alasan',
        'pakta_integritas', 'nama_berkas', 'status', 'catatan',
        'anggota_id', 'diverifikasi_pada',
    ];

    public static function semua(string $status = ''): array
    {
        if ($status !== '') {
            $stmt = self::db()->prepare('SELECT * FROM pendaftaran WHERE status = ? ORDER BY dibuat_pada DESC');
            $stmt->execute([$status]);
            return $stmt->fetchAll();
        }
        return self::db()->query(
            'SELECT * FROM pendaftaran ORDER BY FIELD(status, "Baru", "Diverifikasi", "Ditolak"), dibuat_pada DESC'
        )->fetchAll();
    }

    public static function hitungStatus(string $status): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM pendaftaran WHERE status = ?');
        $stmt->execute([$status]);
        return (int) $stmt->fetchColumn();
    }

    public static function detail(int $id): ?array
    {
        return self::find($id);
    }

    /** Catat hasil verifikasi admin. */
    public static function verifikasi(int $id, string $status, string $catatan = '', ?int $anggotaId = null): void
    {
        if (!in_array($status, ['Diverifikasi', 'Ditolak'], true)) return;
        self::update($id, [
            'status' => $status,
            'catatan' => $catatan !== '' ? $catatan : null,
            'anggota_id' => $anggotaId,
            'diverifikasi_pada' => date('Y-m-d H:i:s'),
        ]);
    }

    /** Apakah anggota sudah punya Pakta Integritas terverifikasi? */
    public static function sudahTerverifikasiUntuk(int $anggotaId): bool
    {
        $stmt = self::db()->prepare(
            "SELECT COUNT(*) FROM pendaftaran WHERE anggota_id = ? AND status = 'Diverifikasi'"
        );
        $stmt->execute([$anggotaId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}