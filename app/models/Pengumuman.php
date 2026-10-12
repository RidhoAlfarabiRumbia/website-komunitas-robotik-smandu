<?php

class Pengumuman extends Model
{
    protected static string $table = 'pengumuman';
    protected static array $fillable = ['judul', 'isi', 'tipe', 'tanggal'];
}
