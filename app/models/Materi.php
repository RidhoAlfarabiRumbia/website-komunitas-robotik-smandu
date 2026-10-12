<?php

/**
 * Materi (PRD 8.2).
 * Akses dibatasi: materi hanya dapat dilihat/diunduh oleh anggota yang
 * terdaftar dan disetujui sebagai peserta pada Pelatihan/Workshop terkait,
 * selama periode pelatihan berlangsung.
 */
class Materi extends Model
{
    protected static string $table = 'materi';
    protected static array $fillable = ['judul', 'topik', 'deskripsi', 'file_path', 'link_url'];

    /** Semua materi; $idPelatihan opsional untuk menambahkan nama pelatihan terkait. */
    public static function semuaWithPelatihan(string $orderBy = 'topik ASC, judul ASC', ?int $limit = null): array
    {
        $allowed = ['topik ASC, judul ASC', 'judul ASC', 'dibuat_pada DESC'];
        $order = in_array($orderBy, $allowed, true) ? $orderBy : 'topik ASC, judul ASC';

        $sql = 'SELECT m.*,
                    GROUP_CONCAT(p.nama ORDER BY p.tanggal_mulai DESC SEPARATOR ", ") AS pelatihan_terkait,
                    COUNT(DISTINCT p.id) AS jumlah_pelatihan
             FROM materi m
             LEFT JOIN materi_pelatihan mp ON mp.materi_id = m.id
             LEFT JOIN pelatihan p ON p.id = mp.pelatihan_id
             GROUP BY m.id
             ORDER BY ' . $order;
        if ($limit !== null && $limit > 0) {
            $sql .= ' LIMIT ' . $limit;
        }
        return self::db()->query($sql)->fetchAll();
    }

    /** ID pelatihan yang terkait dengan sebuah materi. */
    public static function pelatihanTerkait(int $materiId): array
    {
        $stmt = self::db()->prepare(
            'SELECT p.* FROM pelatihan p
             JOIN materi_pelatihan mp ON mp.pelatihan_id = p.id
             WHERE mp.materi_id = ? ORDER BY p.tanggal_mulai DESC'
        );
        $stmt->execute([$materiId]);
        return $stmt->fetchAll();
    }

    /**
     * Apakah anggota yang diberikan boleh membuka berkas materi ini?
     * True bila admin, atau sudah menjadi peserta yang DISETUJUI pada salah
     * satu pelatihan terkait yang periodenya masih berjalan.
     */
    public static function bolehAkses(int $materiId, ?int $anggotaId, bool $isAdmin = false): bool
    {
        if ($isAdmin) return true;
        if (!$anggotaId) return false;

        $stmt = self::db()->prepare(
            'SELECT COUNT(*) FROM materi_pelatihan mp
             JOIN pelatihan p ON p.id = mp.pelatihan_id
             JOIN pelatihan_peserta pp ON pp.pelatihan_id = p.id
             WHERE mp.materi_id = ?
               AND pp.anggota_id = ?
               AND pp.status = "Disetujui"
               AND (p.tanggal_selesai IS NULL OR p.tanggal_selesai = "" OR p.tanggal_selesai >= CURDATE())'
        );
        $stmt->execute([$materiId, $anggotaId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Keterangan singkat untuk ditampilkan pada kartu yang terkunci. */
    public static function namaPelatihanAktif(int $materiId): array
    {
        $stmt = self::db()->prepare(
            'SELECT p.nama, p.tanggal_selesai FROM materi_pelatihan mp
             JOIN pelatihan p ON p.id = mp.pelatihan_id
             WHERE mp.materi_id = ?
               AND (p.tanggal_selesai IS NULL OR p.tanggal_selesai = "" OR p.tanggal_selesai >= CURDATE())
             ORDER BY p.tanggal_mulai ASC'
        );
        $stmt->execute([$materiId]);
        return $stmt->fetchAll();
    }

    /** Simpan/hapus relasi materi <-> pelatihan (dipakai admin CRUD). */
    public static function simpanPelatihan(int $materiId, array $pelatihanIds): void
    {
        self::db()->prepare('DELETE FROM materi_pelatihan WHERE materi_id = ?')->execute([$materiId]);
        $stmt = self::db()->prepare('INSERT IGNORE INTO materi_pelatihan (materi_id, pelatihan_id) VALUES (?, ?)');
        foreach ($pelatihanIds as $id) {
            $id = (int) $id;
            if ($id > 0) $stmt->execute([$materiId, $id]);
        }
    }
}