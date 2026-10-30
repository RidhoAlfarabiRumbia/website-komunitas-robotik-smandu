-- =========================================================
-- MIGRASI: hapus kolom fokus pada tabel anggota
-- Jalankan SATU KALI pada database yang sudah ada.
-- Backup dulu: mysqldump -u root db_robotik > backup.sql
-- PERINGATAN: data fokus anggota lama akan hilang.
-- =========================================================
USE db_robotik;

SET @exist := (SELECT COUNT(*) FROM information_schema.COLUMNS
               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'anggota' AND COLUMN_NAME = 'fokus');
SET @sql := IF(@exist > 0, 'ALTER TABLE anggota DROP COLUMN fokus', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
