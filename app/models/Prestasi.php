<?php

/**
 * Prestasi / pencapaian (PRD 8.7).
 * Berbeda dengan Karya: prestasi tayang langsung tanpa persetujuan admin
 * (pasca-moderation). Admin hanya meninjau kembali dan dapat menyembunyikan
 * atau menghapus bila ditemukan tidak valid.
 */
class Prestasi extends Model
{
    protected static string $table = 'prestasi';
    protected static array $fillable = [
        'nama', 'lomba_id', 'tanggal', 'tingkat', 'hasil',
        'deskripsi', 'foto', 'sertifikat', 'status',
    ];

    /** Prestasi yang tayang di portal (tanpa moderasi pra-publikasi). */
    public static function tayang(int $limit = 0): array
    {
        $sql = 'SELECT p.*, l.nama AS lomba_nama FROM prestasi p
                LEFT JOIN lomba l ON l.id = p.lomba_id
                WHERE p.status = "Tayang" ORDER BY p.tanggal DESC';
        if ($limit > 0) $sql .= ' LIMIT ' . (int) $limit;
        return self::db()->query($sql)->fetchAll();
    }

    /** Semua prestasi termasuk yang disembunyikan admin (untuk panel admin). */
    public static function semua(): array
    {
        return self::db()->query(
            'SELECT p.*, l.nama AS lomba_nama FROM prestasi p
             LEFT JOIN lomba l ON l.id = p.lomba_id
             ORDER BY p.tanggal DESC'
        )->fetchAll();
    }

    public static function detail(int $id): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT p.*, l.nama AS lomba_nama FROM prestasi p
             LEFT JOIN lomba l ON l.id = p.lomba_id WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function jumlahTayang(): int
    {
        return (int) self::db()->query('SELECT COUNT(*) FROM prestasi WHERE status = "Tayang"')->fetchColumn();
    }

    /** Anggota atau tim yang mencapai prestasi tersebut. */
    public static function tim(int $id): array
    {
        $stmt = self::db()->prepare(
            'SELECT pp.*, a.foto FROM prestasi_peserta pp
             LEFT JOIN anggota a ON a.id = pp.anggota_id
             WHERE pp.prestasi_id = ? ORDER BY pp.nama ASC'
        );
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    /** Prestasi yang dicapai seorang anggota (untuk halaman profil). */
    public static function byAnggota(int $anggotaId, bool $hanyaTayang = true): array
    {
        $sql = 'SELECT p.*, l.nama AS lomba_nama FROM prestasi p
                LEFT JOIN lomba l ON l.id = p.lomba_id
                JOIN prestasi_peserta pp ON pp.prestasi_id = p.id
                WHERE pp.anggota_id = ?';
        if ($hanyaTayang) $sql .= ' AND p.status = "Tayang"';
        $sql .= ' ORDER BY p.tanggal DESC';
        $stmt = self::db()->prepare($sql);
        $stmt->execute([$anggotaId]);
        return $stmt->fetchAll();
    }

/** Simpan anggota terpilih sebagai penerima prestasi (pada create/update). */
    public static function simpanTim(int $prestasiId, array $anggotaIds): void
    {
        self::db()->prepare('DELETE FROM prestasi_peserta WHERE prestasi_id = ?')->execute([$prestasiId]);
        $stmt = self::db()->prepare('INSERT INTO prestasi_peserta (prestasi_id, anggota_id, nama) VALUES (?, ?, ?)');
        foreach ($anggotaIds as $id) {
            $id = (int) $id;
            if ($id <= 0) continue;
            $nama = Anggota::find($id)['nama'] ?? 'Anggota';
            $stmt->execute([$prestasiId, $id, $nama]);
        }
    }

    /** Post-moderation: admin menyembunyikan atau menampilkan kembali. */
    public static function setStatus(int $id, string $status): void
    {
        if (!in_array($status, ['Tayang', 'Disembunyikan'], true)) return;
        self::update($id, ['status' => $status]);
    }
}