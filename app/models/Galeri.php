<?php

class Galeri extends Model
{
    protected static string $table = 'galeri';
    protected static array $fillable = ['judul', 'kategori', 'deskripsi', 'tanggal', 'foto', 'video'];
}
