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

    public static function nilaiBanyak(array $keys): array
    {
        $keys = array_values(array_unique(array_filter(
            $keys,
            static fn($key): bool => is_string($key) && $key !== ''
        )));
        if (!$keys) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $stmt = static::db()->prepare('SELECT kunci, nilai FROM ' . static::$table . ' WHERE kunci IN (' . $placeholders . ')');
        $stmt->execute($keys);

        $hasil = [];
        foreach ($stmt->fetchAll() as $row) {
            $hasil[(string) $row['kunci']] = (string) $row['nilai'];
        }
        return $hasil;
    }

    public static function set(array $values): void
    {
        $stmt = static::db()->prepare('INSERT INTO ' . static::$table . ' (kunci, nilai) VALUES (?, ?) ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)');
        foreach ($values as $key => $val) {
            $stmt->execute([(string) $key, (string) $val]);
        }
    }
}