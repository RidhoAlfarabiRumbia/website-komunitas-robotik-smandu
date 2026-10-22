-- =========================================================
-- MIGRASI: ubah status publikasi karya ke Bahasa Indonesia
-- Jalankan SATU KALI pada database yang sudah ada.
-- Backup dulu: mysqldump -u root db_robotik > backup.sql
-- Pemetaan: Pending -> Menunggu, Approved -> Disetujui, Rejected -> Ditolak
-- =========================================================
USE db_robotik;

-- Perluas ENUM dulu agar nilai lama dan baru bisa hidup berdampingan.
ALTER TABLE karya
  MODIFY publikasi ENUM('Pending','Approved','Rejected','Menunggu','Disetujui','Ditolak') NOT NULL DEFAULT 'Pending';

UPDATE karya SET publikasi = CASE publikasi
  WHEN 'Pending' THEN 'Menunggu'
  WHEN 'Approved' THEN 'Disetujui'
  WHEN 'Rejected' THEN 'Ditolak'
  ELSE publikasi
END;

ALTER TABLE karya
  MODIFY publikasi ENUM('Menunggu','Disetujui','Ditolak') NOT NULL DEFAULT 'Menunggu';
