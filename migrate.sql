-- =========================================================
-- MIGRASI PRD Revisi 1.1 -> database yang SUDAH ADA
-- Jalankan file ini SATU KALI pada instalasi lama.
-- Backup dulu: mysqldump -u root db_robotik > backup.sql
-- =========================================================
USE db_robotik;

-- ---------------------------------------------------------
-- 1. ANGGOTA: kolom kontak (dipakai dari formulir pendaftaran)
-- ---------------------------------------------------------
SET @exist := (SELECT COUNT(*) FROM information_schema.COLUMNS
               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'anggota' AND COLUMN_NAME = 'kontak');
SET @sql := IF(@exist = 0, 'ALTER TABLE anggota ADD COLUMN kontak VARCHAR(60) DEFAULT NULL AFTER fokus', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------
-- 2. TABEL BARU: Pelatihan / Workshop (PRD 8.4)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS pelatihan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(150) NOT NULL,
  deskripsi TEXT,
  tanggal_mulai DATE NOT NULL,
  tanggal_selesai DATE DEFAULT NULL,
  waktu VARCHAR(60) DEFAULT NULL,
  lokasi VARCHAR(150) DEFAULT NULL,
  kuota INT NOT NULL DEFAULT 20,
  status ENUM('Dibuka','Ditutup') NOT NULL DEFAULT 'Dibuka',
  akses ENUM('Umum','Anggota') NOT NULL DEFAULT 'Anggota',
  dibuat_pada DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pelatihan_peserta (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pelatihan_id INT NOT NULL,
  anggota_id INT DEFAULT NULL,
  nama VARCHAR(120) NOT NULL DEFAULT '-',
  kontak VARCHAR(60) NOT NULL DEFAULT '-',
  asal VARCHAR(120) DEFAULT NULL,
  status ENUM('Pending','Disetujui','Ditolak') NOT NULL DEFAULT 'Pending',
  catatan VARCHAR(255) DEFAULT NULL,
  dibuat_pada DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pelatihan_id) REFERENCES pelatihan(id) ON DELETE CASCADE,
  FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE SET NULL,
  INDEX idx_peserta_pelatihan (pelatihan_id, status),
  INDEX idx_peserta_anggota (anggota_id, status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS materi_pelatihan (
  materi_id INT NOT NULL,
  pelatihan_id INT NOT NULL,
  PRIMARY KEY (materi_id, pelatihan_id),
  FOREIGN KEY (materi_id) REFERENCES materi(id) ON DELETE CASCADE,
  FOREIGN KEY (pelatihan_id) REFERENCES pelatihan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 3. TABEL BARU: Prestasi (PRD 8.7, tayang langsung)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS prestasi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(180) NOT NULL,
  lomba_id INT DEFAULT NULL,
  tanggal DATE NOT NULL,
  tingkat ENUM('Sekolah','Kota','Provinsi','Nasional','Internasional') NOT NULL DEFAULT 'Sekolah',
  hasil VARCHAR(120) DEFAULT NULL,
  deskripsi TEXT,
  foto VARCHAR(191) DEFAULT NULL,
  sertifikat VARCHAR(191) DEFAULT NULL,
  status ENUM('Tayang','Disembunyikan') NOT NULL DEFAULT 'Tayang',
  dibuat_pada DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (lomba_id) REFERENCES lomba(id) ON DELETE SET NULL,
  INDEX idx_prestasi_status (status, tanggal)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS prestasi_peserta (
  id INT AUTO_INCREMENT PRIMARY KEY,
  prestasi_id INT NOT NULL,
  anggota_id INT DEFAULT NULL,
  nama VARCHAR(120) NOT NULL,
  FOREIGN KEY (prestasi_id) REFERENCES prestasi(id) ON DELETE CASCADE,
  FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 4. TABEL BARU: Pendaftaran + Pakta Integritas (PRD 8.12)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS pendaftaran (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(120) NOT NULL,
  kelas VARCHAR(40) DEFAULT NULL,
  kontak VARCHAR(60) NOT NULL,
  email VARCHAR(120) DEFAULT NULL,
  alasan TEXT,
  pakta_integritas VARCHAR(191) DEFAULT NULL,
  nama_berkas VARCHAR(191) DEFAULT NULL,
  status ENUM('Baru','Diverifikasi','Ditolak') NOT NULL DEFAULT 'Baru',
  catatan VARCHAR(255) DEFAULT NULL,
  anggota_id INT DEFAULT NULL,
  dibuat_pada DATETIME DEFAULT CURRENT_TIMESTAMP,
  diverifikasi_pada DATETIME DEFAULT NULL,
  FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE SET NULL,
  INDEX idx_pendaftaran_status (status, dibuat_pada)
) ENGINE=InnoDB;

-- Penyelarasan kolom tabel baru agar sama persis dengan database.sql:
--   pelatihan_peserta.nama / .kontak -> default '-' (boleh diisi '-')
--   prestasi.sertifikat              -> boleh NULL (sertifikat opsional)
ALTER TABLE pelatihan_peserta
  MODIFY nama VARCHAR(120) NOT NULL DEFAULT '-',
  MODIFY kontak VARCHAR(60) NOT NULL DEFAULT '-';
ALTER TABLE prestasi
  MODIFY tingkat ENUM('Sekolah','Kota','Provinsi','Nasional','Internasional') NOT NULL DEFAULT 'Sekolah',
  MODIFY sertifikat VARCHAR(191) DEFAULT NULL;

-- Penamaan ulang: "Fakta Integritas" -> "Pakta Integritas".
-- Berlaku untuk instalasi yang sudah menjalankan versi migrate.sql sebelumnya.
SET @exist := (SELECT COUNT(*) FROM information_schema.COLUMNS
               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pendaftaran' AND COLUMN_NAME = 'fakta_integritas');
SET @sql := IF(@exist = 1, 'ALTER TABLE pendaftaran CHANGE COLUMN fakta_integritas pakta_integritas VARCHAR(191) DEFAULT NULL', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Path berkas lama (storage/terbatas/fakta-integritas/...) ikut menyesuaikan nama folder.
UPDATE pendaftaran
   SET pakta_integritas = REPLACE(pakta_integritas, 'terbatas/fakta-integritas/', 'terbatas/pakta-integritas/')
 WHERE pakta_integritas LIKE 'terbatas/fakta-integritas/%';

-- ---------------------------------------------------------
-- 5. KARYA: enum progres -> Idea / In Progress / Completed / Showcased
--    (PRD 8.5). Nilai lama dipetakan agar data tidak hilang.
-- ---------------------------------------------------------
ALTER TABLE karya
  MODIFY progres ENUM('Idea','In Progress','Completed','Showcased','Sedang Dikerjakan','Selesai') NOT NULL DEFAULT 'Idea';

UPDATE karya SET progres = 'In Progress' WHERE progres = 'Sedang Dikerjakan';
UPDATE karya SET progres = 'Completed' WHERE progres = 'Selesai';

ALTER TABLE karya
  MODIFY progres ENUM('Idea','In Progress','Completed','Showcased') NOT NULL DEFAULT 'Idea';

SET @exist := (SELECT COUNT(*) FROM information_schema.STATISTICS
               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'karya' AND INDEX_NAME = 'idx_publikasi');
SET @sql := IF(@exist = 0, 'ALTER TABLE karya ADD INDEX idx_publikasi (publikasi, dibuat_pada)', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------
-- 6. MEDIA: kolom video untuk laporan kegiatan (PRD 8.14)
-- ---------------------------------------------------------
SET @exist := (SELECT COUNT(*) FROM information_schema.COLUMNS
               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'galeri' AND COLUMN_NAME = 'video');
SET @sql := IF(@exist = 0, 'ALTER TABLE galeri ADD COLUMN video VARCHAR(191) DEFAULT NULL AFTER foto', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.COLUMNS
               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kegiatan' AND COLUMN_NAME = 'video');
SET @sql := IF(@exist = 0, 'ALTER TABLE kegiatan ADD COLUMN video VARCHAR(191) DEFAULT NULL AFTER status', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------
-- 7. GALERI: kategori 'Prestasi' tidak lagi dipakai (PRD 8.8)
--    Dokumentasi prestasi/sertifikat dipindah ke tabel prestasi.
-- ---------------------------------------------------------
UPDATE galeri SET kategori = 'Pameran' WHERE kategori = 'Prestasi';

-- ---------------------------------------------------------
-- 8. MATERI: berkas dipindah ke area terbatas di luar document root
--    (file-nya dipindahkan oleh skrip migrasi PHP, path di sini disiapkan)
-- ---------------------------------------------------------
UPDATE materi
   SET file_path = CONCAT('terbatas/materi/', SUBSTRING_INDEX(file_path, '/', -1))
 WHERE file_path LIKE 'uploads/materi/%';

-- ---------------------------------------------------------
-- 9. Akun admin kedua (PRD 5.1: pembina + murid yang ditugaskan)
-- ---------------------------------------------------------
INSERT IGNORE INTO users (username, password_hash, role, anggota_id)
VALUES ('pembina', '$2b$10$gA45g5QtC.vAdjUmkxnl/OnGPqkOfP2wEzUiH7BTn.6R80gHsNWl2', 'admin', NULL);

-- =========================================================
-- 10. DATA CONTOH untuk modul baru
-- Dibersihkan dulu agar migrasi tetap aman bila dijalankan ulang
-- =========================================================
DELETE FROM pelatihan_peserta WHERE pelatihan_id IN (SELECT id FROM pelatihan WHERE nama LIKE 'Workshop %');
DELETE FROM prestasi_peserta WHERE prestasi_id IN (SELECT id FROM prestasi WHERE nama LIKE 'Juara %');
DELETE FROM pendaftaran WHERE nama IN ('Rangga Saputra','Dewi Lestari','Fajar Nugroho');
DELETE FROM pelatihan WHERE nama LIKE 'Workshop %';
DELETE FROM prestasi WHERE nama LIKE 'Juara %';
DELETE FROM materi_pelatihan;

INSERT INTO pelatihan (id, nama, deskripsi, tanggal_mulai, tanggal_selesai, waktu, lokasi, kuota, status, akses) VALUES
(1,'Workshop Mini Arduino untuk Pemula','Pengenalan rangkaian dan pemrograman Arduino dari nol, diakhiri proyek robot pemadam api sederhana.','2026-10-10','2026-10-24','Sabtu, 09.00 - 12.00','Lab Robotika',30,'Dibuka','Umum'),
(2,'Workshop Sensor dan IoT dengan ESP32','Praktik langsung membaca sensor, mengirim data ke dashboard, dan menghubungkan ESP32 dengan WiFi sekolah.','2026-11-07','2026-11-21','Sabtu, 09.00 - 12.00','Lab Komputer 1',20,'Dibuka','Anggota'),
(3,'Workshop Pemrograman Lanjutan','Pembahasan algoritma, struktur data, dan machine learning ringan untuk proyek robot.','2026-09-12','2026-09-26','Sabtu, 10.00 - 13.00','Lab Komputer 2',16,'Ditutup','Anggota'),
(4,'Workshop Terbuka Umum: Demo Robot Sortir','Pameran karya untuk siswa luar sekolah. Komunitas memamerkan lengan robot sortir warna.','2026-12-05','2026-12-05','Sabtu, 09.00 - 11.00','Aula Sekolah',50,'Dibuka','Umum')
ON DUPLICATE KEY UPDATE nama = VALUES(nama);

INSERT INTO pelatihan_peserta (pelatihan_id, anggota_id, nama, kontak, asal, status) VALUES
(1,1,'Ahmad Fauzan Rizki','0812-3456-7890','SMA Negeri 2','Disetujui'),
(1,2,'Nabila Putri Salsabila','0813-2200-1122','SMA Negeri 2','Disetujui'),
(1,3,'Muhammad Rafi Ardiansyah','0852-9988-7766','SMA Negeri 2','Disetujui'),
(1,NULL,'Rangga Saputra','0877-4411-9090','SMP Negeri 5 Ternate','Disetujui'),
(2,4,'Siti Zahra Amelia','0812-7788-5544','SMA Negeri 2','Disetujui'),
(2,6,'Keisha Ayu Lestari','0812-4433-2211','SMA Negeri 2','Pending'),
(2,NULL,'Dewi Lestari','0813-9911-2233','SMA Negeri 1 Ternate','Pending'),
(3,5,'Bagas Dwi Prasetyo','0853-6677-3322','SMA Negeri 2','Disetujui');

-- Dipetakan berdasarkan JUDUL agar aman terhadap perbedaan ID antar instalasi.
DELETE FROM materi_pelatihan;
INSERT IGNORE INTO materi_pelatihan (materi_id, pelatihan_id)
SELECT m.id, mp.pelatihan_id FROM (
  SELECT 'Dasar Pemrograman Arduino' AS judul, 1 AS pelatihan_id
  UNION ALL SELECT 'Pengenalan Python untuk Robotika', 2
  UNION ALL SELECT 'Dasar IoT dengan ESP32', 2
  UNION ALL SELECT 'Elektronika Dasar: Sensor dan Aktuator', 3
  UNION ALL SELECT 'Algoritma dan Logika Pemrograman', 3
) mp JOIN materi m ON m.judul = mp.judul;

INSERT INTO prestasi (id, nama, lomba_id, tanggal, tingkat, hasil, deskripsi, foto, sertifikat, status, dibuat_pada) VALUES
(1,'Juara 2 KRPAI 2025 Regional Sulut-Malut',1,'2025-10-05','Provinsi','Juara 2','Tim pemadam api membawa pulang medali pada Regional KRPAI tingkat provinsi setelah melewati final dengan baik.','uploads/prestasi/prestasi_krpai.jpg','uploads/prestasi/sertifikat_krpai_2025.pdf','Tayang','2025-10-06'),
(2,'Juara 2 Lomba Karya Inovasi Siswa Provinsi Malut 2025',2,'2025-11-12','Provinsi','Juara 2','Inovasi sistem pemantauan lahan sekolah berhasil meraih Juara 2 tingkat provinsi.','uploads/prestasi/prestasi_inovasi.jpg','uploads/prestasi/sertifikat_inovasi_2025.pdf','Tayang','2025-11-13'),
(3,'Juara 3 KRPAI 2026 Regional',3,'2026-03-15','Provinsi','Juara 3','Tim komunitas melaju ke final setelah perbaikan algoritma PID dan pendinginan motor.','uploads/prestasi/prestasi_krpai_2026.jpg','uploads/prestasi/sertifikat_krpai_2026.pdf','Tayang','2026-03-16'),
(4,'Juara 1 Lomba Line Tracer Antar SMA Kota Ternate',NULL,'2026-05-09','Kota','Juara 1','Robot line tracer meraih Juara 1 tingkat kota dengan waktu solving 18 detik.','uploads/prestasi/prestasi_line_tracer.jpg','uploads/prestasi/sertifikat_line_tracer_2026.pdf','Tayang','2026-05-10')
ON DUPLICATE KEY UPDATE nama = VALUES(nama);

INSERT IGNORE INTO prestasi_peserta (prestasi_id, anggota_id, nama) VALUES
(1,1,'Ahmad Fauzan Rizki'),(1,5,'Bagas Dwi Prasetyo'),
(2,2,'Nabila Putri Salsabila'),
(3,1,'Ahmad Fauzan Rizki'),(3,6,'Keisha Ayu Lestari'),
(4,1,'Ahmad Fauzan Rizki'),(4,3,'Muhammad Rafi Ardiansyah');

INSERT INTO pendaftaran (nama, kelas, kontak, email, alasan, status, dibuat_pada) VALUES
('Rangga Saputra','X IPA 2','0877-4411-9090','rangga@email.com','Ingin belajar pemrograman robotika dan ikut lomba.','Baru','2026-10-02 08:15:00'),
('Dewi Lestari','XI IPS 1','0813-9911-2233','dewi.lestari@email.com','Tertarik pada bidang AI dan deteksi objek.','Baru','2026-10-02 09:40:00'),
('Fajar Nugroho','X TKJ 3','0857-6677-8899','fajar.n@email.com','Meminat pada robotika dan elektronik dasar.','Diverifikasi','2026-09-12 10:05:00')
ON DUPLICATE KEY UPDATE nama = VALUES(nama);