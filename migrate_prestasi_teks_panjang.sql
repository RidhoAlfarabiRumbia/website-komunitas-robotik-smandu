-- =========================================================
-- MIGRASI: hapus batas panjang nama dan hasil prestasi
-- Jalankan SATU KALI pada database yang sudah ada.
-- Backup dulu: mysqldump -u root db_robotik > backup.sql
-- =========================================================
USE db_robotik;

ALTER TABLE prestasi
  MODIFY nama TEXT NOT NULL,
  MODIFY hasil TEXT DEFAULT NULL;
