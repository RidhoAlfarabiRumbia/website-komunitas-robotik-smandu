<?php

/**
 * Kelas dasar model. Setiap entitas (Anggota, Karya, dst) mewarisi kelas ini
 * sehingga operasi CRUD tidak ditulis berulang di setiap kelas.
 */
abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];
    protected static array $orderableColumns = [];

    protected static function db(): PDO
    {
        return Database::connect();
    }

    protected static function validateOrderBy(string $orderBy): string
    {
        if ($orderBy === '') {
            return '';
        }
        $allowed = static::$orderableColumns ?: array_merge(static::$fillable, [static::$primaryKey, 'dibuat_pada', 'diperbarui_pada']);
        $parts = preg_split('/\s*,\s*/', trim($orderBy));
        $validated = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if (!preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s+(ASC|DESC)$/i', $part, $m)) {
                continue;
            }
            $col = $m[1];
            $dir = strtoupper($m[2]);
            if (in_array($col, $allowed, true)) {
                $validated[] = $col . ' ' . $dir;
            }
        }
        return $validated ? implode(', ', $validated) : '';
    }

    public static function all(string $orderBy = '', ?int $limit = null): array
    {
        $sql = 'SELECT * FROM ' . static::$table;
        $orderBy = static::validateOrderBy($orderBy);
        if ($orderBy) {
            $sql .= ' ORDER BY ' . $orderBy;
        }
        if ($limit !== null && $limit > 0) {
            $sql .= ' LIMIT ' . (int) $limit;
        }
        return static::db()->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = static::db()->prepare('SELECT * FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Jumlah baris tanpa memuat barisnya (COUNT(*)). */
    public static function jumlah(): int
    {
        return (int) static::db()->query('SELECT COUNT(*) FROM ' . static::$table)->fetchColumn();
    }

    public static function where(string $column, $value, string $orderBy = ''): array
    {
        $sql = 'SELECT * FROM ' . static::$table . ' WHERE ' . $column . ' = ?';
        $orderBy = static::validateOrderBy($orderBy);
        if ($orderBy) {
            $sql .= ' ORDER BY ' . $orderBy;
        }
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $data = array_intersect_key($data, array_flip(static::$fillable));
        $cols = array_keys($data);
        $placeholders = array_fill(0, count($cols), '?');
        $sql = 'INSERT INTO ' . static::$table . ' (' . implode(',', $cols) . ') VALUES (' . implode(',', $placeholders) . ')';
        $stmt = static::db()->prepare($sql);
        $stmt->execute(array_values($data));
        return (int) static::db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $data = array_intersect_key($data, array_flip(static::$fillable));
        $set = implode(',', array_map(fn($c) => "$c = ?", array_keys($data)));
        $sql = 'UPDATE ' . static::$table . ' SET ' . $set . ' WHERE ' . static::$primaryKey . ' = ?';
        $stmt = static::db()->prepare($sql);
        return $stmt->execute([...array_values($data), $id]);
    }

    public static function delete(int $id): bool
    {
        $stmt = static::db()->prepare('DELETE FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = ?');
        return $stmt->execute([$id]);
    }

    public static function fields(): array
    {
        return static::$fillable;
    }

    public static function table(): string
    {
        return static::$table;
    }
}
