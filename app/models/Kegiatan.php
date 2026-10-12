<?php

class Kegiatan extends Model
{
    protected static string $table = 'kegiatan';
    protected static array $fillable = ['nama', 'deskripsi', 'tanggal', 'waktu', 'lokasi', 'status', 'video'];
}
