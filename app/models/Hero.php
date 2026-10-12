<?php

class Hero extends Model
{
    protected static string $table = 'hero';
    protected static array $fillable = ['foto', 'status'];

    public static function tampil(): array
    {
        // Fitur "urutan" sudah dihapus dari admin: slider tampil sesuai urutan
        // input (id ASC), status tetap menyaring baris yang disembunyikan.
        return static::where('status', 'Tampil', 'id ASC');
    }
}