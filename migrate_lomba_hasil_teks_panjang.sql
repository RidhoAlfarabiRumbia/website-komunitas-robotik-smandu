-- =========================================================
-- MIGRASI: hapus batas panjang hasil lomba
-- Jalankan SATU KALI pada database yang sudah ada.
-- Backup dulu: mysqldump -u root db_robotik > backup.sql
-- =========================================================
USE db_robotik;

ALTER TABLE lomba
  MODIFY hasil TEXT DEFAULT NULL;
