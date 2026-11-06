<?php

class Lomba extends Model
{
    protected static string $table = 'lomba';
    protected static array $fillable = ['nama', 'penyelenggara', 'tanggal', 'lokasi', 'status', 'hasil'];

    public static function peserta(int $lombaId): array
    {
        $stmt = self::db()->prepare(
            'SELECT a.* FROM anggota a
             JOIN lomba_peserta lp ON lp.anggota_id = a.id
             WHERE lp.lomba_id = ?'
        );
        $stmt->execute([$lombaId]);
        return $stmt->fetchAll();
    }

    /** Peserta beberapa lomba sekaligus, dikelompokkan per lomba_id. */
    public static function pesertaMap(array $lombaIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $lombaIds), fn(int $id): bool => $id > 0)));
        if (!$ids) return [];

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = self::db()->prepare(
            'SELECT lp.lomba_id, a.nama FROM lomba_peserta lp
             JOIN anggota a ON a.id = lp.anggota_id
             WHERE lp.lomba_id IN (' . $placeholders . ')
             ORDER BY a.nama ASC'
        );
        $stmt->execute($ids);

        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[(int) $row['lomba_id']][] = $row;
        }
        return $map;
    }
}
