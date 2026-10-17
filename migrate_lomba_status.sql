-- ---------------------------------------------------------
-- LOMBA: enum status -> 'Akan Datang' / 'Selesai'
-- Nilai lama 'Pendaftaran Dibuka' dipetakan ke 'Akan Datang'
-- supaya data tidak hilang dan tidak tertolak saat ENUM dikunci.
-- Aman dijalankan berulang kali (idempoten).
-- CATATAN: sesuaikan nama database di baris USE bila berbeda.
-- ---------------------------------------------------------
USE db_robotik;

-- 1. Petakan data lama ke nilai yang masih valid.
UPDATE lomba SET status = 'Akan Datang' WHERE status = 'Pendaftaran Dibuka';

-- 2. Kunci ENUM ke dua nilai baru (hanya kalau kolomnya ada).
SET @exist := (SELECT COUNT(*) FROM information_schema.COLUMNS
               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lomba' AND COLUMN_NAME = 'status');
SET @sql := IF(@exist = 1,
  'ALTER TABLE lomba MODIFY status ENUM(''Akan Datang'',''Selesai'') NOT NULL DEFAULT ''Akan Datang''',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;