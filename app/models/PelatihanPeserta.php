<?php

/** Peserta pelatihan / workshop (PRD 8.4). */
class PelatihanPeserta extends Model
{
    protected static string $table = 'pelatihan_peserta';
    protected static array $fillable = ['pelatihan_id', 'anggota_id', 'nama', 'kontak', 'asal', 'status', 'catatan'];

    public static function detail(int $id): ?array
    {
        return self::find($id);
    }

    /**
     * Status peserta. "Disetujui" inilah yang membuka akses materi pelatihan
     * (lihat Materi::bolehAkses()).
     */
    public static function setStatus(int $id, string $status): void
    {
        if (!in_array($status, ['Pending', 'Disetujui', 'Ditolak'], true)) return;
        self::update($id, ['status' => $status]);
    }

    public static function find(int $id): ?array
    {
        return parent::find($id);
    }

    public static function delete(int $id): bool
    {
        return parent::delete($id);
    }
}