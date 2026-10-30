-- =========================================================
-- MIGRASI: karya dengan lebih dari satu pembuat
-- Jalankan SATU KALI pada database yang sudah ada.
-- Backup dulu: mysqldump -u root db_robotik > backup.sql
-- =========================================================
USE db_robotik;

CREATE TABLE IF NOT EXISTS karya_pembuat (
  karya_id INT NOT NULL,
  anggota_id INT NOT NULL,
  PRIMARY KEY (karya_id, anggota_id),
  FOREIGN KEY (karya_id) REFERENCES karya(id) ON DELETE CASCADE,
  FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE,
  INDEX idx_karya_pembuat_anggota (anggota_id)
) ENGINE=InnoDB;

-- Semua pembuat lama tetap tercatat sebagai pembuat tunggal.
INSERT IGNORE INTO karya_pembuat (karya_id, anggota_id)
SELECT id, pembuat_id FROM karya;

SELECT (SELECT COUNT(*) FROM karya) AS jumlah_karya,
       (SELECT COUNT(*) FROM karya_pembuat) AS jumlah_relasi;
