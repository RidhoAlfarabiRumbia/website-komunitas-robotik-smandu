<?php

class Lomba extends Model
{
    protected static string $table = 'lomba';
    protected static array $fillable = ['nama', 'penyelenggara', 'tanggal', 'lokasi', 'kategori', 'status', 'hasil'];

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
}
