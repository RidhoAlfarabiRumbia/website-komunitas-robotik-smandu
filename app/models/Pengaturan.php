<?php

class Pengaturan extends Model
{
    protected static string $table = 'pengaturan';
    protected static array $fillable = ['kunci', 'nilai'];

    public static function nilai(string $key, $default = null): ?string
    {
        $stmt = static::db()->prepare('SELECT nilai FROM ' . static::$table . ' WHERE kunci = ?');
        $stmt->execute([$key]);
        $row = $stmt->fetchColumn();
        return $row !== false ? (string) $row : $default;
    }

    public static function set(array $values): void
    {
        $stmt = static::db()->prepare('INSERT INTO ' . static::$table . ' (kunci, nilai) VALUES (?, ?) ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)');
        foreach ($values as $key => $val) {
            $stmt->execute([(string) $key, (string) $val]);
        }
    }
}