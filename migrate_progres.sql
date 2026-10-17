-- ---------------------------------------------------------
-- KARYA: enum progres -> 'Sedang Dikerjakan' / 'Selesai'
-- Nilai lama dipetakan agar data tidak hilang:
--   Idea, In Progress  -> Sedang Dikerjakan
--   Completed, Showcased -> Selesai
-- Aman dijalankan berulang kali (idempoten).
-- ---------------------------------------------------------
USE db_robotik;

-- 1. Tambahkan nilai baru dulu (ENUM lama dibiarkan) supaya pemetaan
--    data bisa ditulis tanpa kehilangan nilai lama.
SET @exist := (SELECT COUNT(*) FROM information_schema.COLUMNS
               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'karya' AND COLUMN_NAME = 'progres');
SET @sql := IF(@exist = 1,
  'ALTER TABLE karya MODIFY progres ENUM(''Idea'',''In Progress'',''Completed'',''Showcased'',''Sedang Dikerjakan'',''Selesai'') NOT NULL DEFAULT ''Idea''',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Petakan data lama ke nilai baru.
UPDATE karya SET progres = 'Sedang Dikerjakan' WHERE progres IN ('Idea', 'In Progress');
UPDATE karya SET progres = 'Selesai'          WHERE progres IN ('Completed', 'Showcased');

-- 3. Kunci ENUM ke dua nilai baru.
SET @sql := IF(@exist = 1,
  'ALTER TABLE karya MODIFY progres ENUM(''Sedang Dikerjakan'',''Selesai'') NOT NULL DEFAULT ''Sedang Dikerjakan''',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;