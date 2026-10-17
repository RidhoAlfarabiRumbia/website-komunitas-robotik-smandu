-- ---------------------------------------------------------
-- PENGUMUMAN: buang kolom `tipe`
-- Field "Tipe" dihapus dari form admin dan dari tampilan publik,
-- jadi kolomnya ikut dibuang agar tidak menyesatkan.
-- CATATAN: nilai Tipe pada pengumuman yang SUDAH ADA akan hilang
-- (tidak dipindah ke tempat lain). Backup dulu bila masih perlu.
-- Aman dijalankan berulang kali (idempoten).
-- CATATAN: sesuaikan nama database di baris USE bila berbeda.
-- ---------------------------------------------------------
USE db_robotik;

SET @exist := (SELECT COUNT(*) FROM information_schema.COLUMNS
               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pengumuman' AND COLUMN_NAME = 'tipe');
SET @sql := IF(@exist = 1,
  'ALTER TABLE pengumuman DROP COLUMN tipe',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;