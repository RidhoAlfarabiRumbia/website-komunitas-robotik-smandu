-- =========================================================
-- MIGRASI: hapus kolom kategori pada tabel karya
-- Jalankan SATU KALI pada database yang sudah ada.
-- Backup dulu: mysqldump -u root db_robotik > backup.sql
-- PERINGATAN: data kategori karya lama akan hilang.
-- =========================================================
USE db_robotik;

SET @exist := (SELECT COUNT(*) FROM information_schema.COLUMNS
               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'karya' AND COLUMN_NAME = 'kategori');
SET @sql := IF(@exist > 0, 'ALTER TABLE karya DROP COLUMN kategori', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
