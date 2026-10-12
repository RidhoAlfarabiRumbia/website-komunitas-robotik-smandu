-- =========================================================
-- Portal Komunitas Robotik SMA Negeri 2 - Skema Database
-- Sesuai PRD Revisi 1.1 (Oktober 2026)
-- Import file ini di phpMyAdmin / mysql client untuk instalasi baru
-- Untuk database yang SUDAH ADA, gunakan migrate.sql
-- =========================================================

CREATE DATABASE IF NOT EXISTS db_robotik CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_robotik;

-- ---------------------------------------------------------
-- ANGGOTA
-- ---------------------------------------------------------
CREATE TABLE anggota (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(120) NOT NULL,
  kelas VARCHAR(40) NOT NULL,
  fokus VARCHAR(80) NOT NULL,
  kontak VARCHAR(60) DEFAULT NULL,
  deskripsi TEXT,
  foto VARCHAR(191) DEFAULT NULL,
  dibuat_pada DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- USERS (akun login: admin & anggota)
-- ---------------------------------------------------------
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','anggota') NOT NULL,
  anggota_id INT DEFAULT NULL,
  dibuat_pada DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- MATERI (akses dibatasi oleh pelatihan terkait, lihat 8.2)
-- ---------------------------------------------------------
CREATE TABLE materi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(150) NOT NULL,
  topik VARCHAR(60) NOT NULL,
  deskripsi TEXT,
  file_path VARCHAR(191) DEFAULT NULL,
  link_url VARCHAR(191) DEFAULT NULL,
  dibuat_pada DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- PELATIHAN / WORKSHOP (Baru - PRD 8.4)
-- ---------------------------------------------------------
CREATE TABLE pelatihan (
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

CREATE TABLE pelatihan_peserta (
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
  INDEX idx_peserta_anggota (anggota_id, status),
  INDEX idx_peserta_status (status)
) ENGINE=InnoDB;

-- Relasi materi <-> pelatihan (satu materi bisa dipakai beberapa pelatihan)
CREATE TABLE materi_pelatihan (
  materi_id INT NOT NULL,
  pelatihan_id INT NOT NULL,
  PRIMARY KEY (materi_id, pelatihan_id),
  FOREIGN KEY (materi_id) REFERENCES materi(id) ON DELETE CASCADE,
  FOREIGN KEY (pelatihan_id) REFERENCES pelatihan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- REFERENSI (terbuka untuk semua anggota, PRD 8.3)
-- ---------------------------------------------------------
CREATE TABLE referensi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  url VARCHAR(191) NOT NULL,
  deskripsi TEXT
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- KARYA (wajib moderasi pra-publikasi, PRD 8.5-8.6)
-- ---------------------------------------------------------
CREATE TABLE karya (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(150) NOT NULL,
  kategori VARCHAR(50) NOT NULL,
  deskripsi TEXT,
  progres ENUM('Idea','In Progress','Completed','Showcased') NOT NULL DEFAULT 'Idea',
  publikasi ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  pembuat_id INT NOT NULL,
  teknologi VARCHAR(255) DEFAULT NULL,
  link_demo VARCHAR(191) DEFAULT NULL,
  foto VARCHAR(191) DEFAULT NULL,
  alasan_tolak TEXT DEFAULT NULL,
  dibuat_pada DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pembuat_id) REFERENCES anggota(id) ON DELETE CASCADE,
  INDEX idx_publikasi (publikasi, dibuat_pada)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- LOMBA (PRD 8.10)
-- ---------------------------------------------------------
CREATE TABLE lomba (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(150) NOT NULL,
  penyelenggara VARCHAR(120) DEFAULT NULL,
  tanggal DATE NOT NULL,
  lokasi VARCHAR(120) DEFAULT NULL,
  kategori VARCHAR(80) DEFAULT NULL,
  status ENUM('Pendaftaran Dibuka','Akan Datang','Selesai') NOT NULL DEFAULT 'Akan Datang',
  hasil VARCHAR(80) DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE lomba_peserta (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lomba_id INT NOT NULL,
  anggota_id INT NOT NULL,
  FOREIGN KEY (lomba_id) REFERENCES lomba(id) ON DELETE CASCADE,
  FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- PRESTASI (Baru - PRD 8.7, tayang langsung tanpa moderasi)
-- ---------------------------------------------------------
CREATE TABLE prestasi (
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

CREATE TABLE prestasi_peserta (
  id INT AUTO_INCREMENT PRIMARY KEY,
  prestasi_id INT NOT NULL,
  anggota_id INT DEFAULT NULL,
  nama VARCHAR(120) NOT NULL,
  FOREIGN KEY (prestasi_id) REFERENCES prestasi(id) ON DELETE CASCADE,
  FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- GALERI (dokumentasi umum, PRD 8.8 - tanpa prestasi/sertifikat)
-- ---------------------------------------------------------
CREATE TABLE galeri (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(150) NOT NULL,
  kategori VARCHAR(50) NOT NULL,
  deskripsi TEXT,
  tanggal DATE NOT NULL,
  foto VARCHAR(191) DEFAULT NULL,
  video VARCHAR(191) DEFAULT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- KEGIATAN (PRD 8.9, dukung video laporan kegiatan 8.14)
-- ---------------------------------------------------------
CREATE TABLE kegiatan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(150) NOT NULL,
  deskripsi TEXT,
  tanggal DATE NOT NULL,
  waktu VARCHAR(40) DEFAULT NULL,
  lokasi VARCHAR(120) DEFAULT NULL,
  status ENUM('Akan Datang','Selesai') NOT NULL DEFAULT 'Akan Datang',
  video VARCHAR(191) DEFAULT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- PENDAFTARAN ANGGOTA + PAKTA INTEGRITAS (Baru - PRD 8.12)
-- Berkas hasil pindai disimpan DI LUAR document root (storage/terbatas)
-- ---------------------------------------------------------
CREATE TABLE pendaftaran (
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

-- ---------------------------------------------------------
-- PENGUMUMAN (PRD 8.11)
-- ---------------------------------------------------------
CREATE TABLE pengumuman (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(150) NOT NULL,
  isi TEXT NOT NULL,
  tipe VARCHAR(50) DEFAULT 'Umum',
  tanggal DATE NOT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- HERO (slider banner beranda)
-- ---------------------------------------------------------
CREATE TABLE hero (
  id INT AUTO_INCREMENT PRIMARY KEY,
  foto VARCHAR(191) DEFAULT NULL,
  status ENUM('Tampil','Sembunyi') NOT NULL DEFAULT 'Tampil',
  dibuat_pada DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- PENGATURAN (teks & opsi yang bisa dikustomisasi admin)
-- ---------------------------------------------------------
CREATE TABLE pengaturan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kunci VARCHAR(60) NOT NULL UNIQUE,
  nilai TEXT,
  dibuat_pada DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- DATA CONTOH (dummy, berbahasa Indonesia)
-- =========================================================

INSERT INTO anggota (id, nama, kelas, fokus, kontak, deskripsi, foto) VALUES
(1,'Ahmad Fauzan Rizki','XI RPL 1','Robotika & AI','0812-3456-7890','Membina line follower dan sistem robot kompetisi sejak kelas X.','assets/img/avatar-1.svg'),
(2,'Nabila Putri Salsabila','XI RPL 2','Internet of Things','0813-2200-1122','Fokus pada proyek monitoring berbasis sensor dan ESP32.','assets/img/avatar-2.svg'),
(3,'Muhammad Rafi Ardiansyah','X TKJ 1','Elektronika','0852-9988-7766','Anggota baru yang aktif belajar rangkaian dasar dan Arduino.','assets/img/avatar-3.svg'),
(4,'Siti Zahra Amelia','XI RPL 1','Programming','0812-7788-5544','Mengembangkan aplikasi pendukung untuk kegiatan komunitas.','assets/img/avatar-4.svg'),
(5,'Bagas Dwi Prasetyo','X TKJ 2','Robotika','0853-6677-3322','Merakit dan menguji mekanisme lengan robot sortir.','assets/img/avatar-5.svg'),
(6,'Keisha Ayu Lestari','XII RPL 1','AI & Data','0812-4433-2211','Mengembangkan model deteksi objek sederhana untuk robot.','assets/img/avatar-6.svg');

-- password untuk semua akun di bawah = "demo123"
-- hash dibuat dengan password_hash('demo123', PASSWORD_DEFAULT)
INSERT INTO users (username, password_hash, role, anggota_id) VALUES
('admin', '$2b$10$gA45g5QtC.vAdjUmkxnl/OnGPqkOfP2wEzUiH7BTn.6R80gHsNWl2', 'admin', NULL),
('pembina', '$2b$10$gA45g5QtC.vAdjUmkxnl/OnGPqkOfP2wEzUiH7BTn.6R80gHsNWl2', 'admin', NULL),
('ahmad.fauzan', '$2b$10$gA45g5QtC.vAdjUmkxnl/OnGPqkOfP2wEzUiH7BTn.6R80gHsNWl2', 'anggota', 1),
('nabila.putri', '$2b$10$gA45g5QtC.vAdjUmkxnl/OnGPqkOfP2wEzUiH7BTn.6R80gHsNWl2', 'anggota', 2),
('rafi.ardiansyah', '$2b$10$gA45g5QtC.vAdjUmkxnl/OnGPqkOfP2wEzUiH7BTn.6R80gHsNWl2', 'anggota', 3),
('zahra.amelia', '$2b$10$gA45g5QtC.vAdjUmkxnl/OnGPqkOfP2wEzUiH7BTn.6R80gHsNWl2', 'anggota', 4),
('bagas.dwi', '$2b$10$gA45g5QtC.vAdjUmkxnl/OnGPqkOfP2wEzUiH7BTn.6R80gHsNWl2', 'anggota', 5),
('keisha.ayu', '$2b$10$gA45g5QtC.vAdjUmkxnl/OnGPqkOfP2wEzUiH7BTn.6R80gHsNWl2', 'anggota', 6);

INSERT INTO materi (judul, topik, deskripsi, file_path, link_url) VALUES
('Dasar Pemrograman Arduino','Robotika','Pengenalan IDE Arduino, struktur program, dan input/output digital-analog.',NULL,NULL),
('Mengantar Robot Line Follower','Robotika','Panduan lengkap: merakit, kalibrasi sensor array, dan menyetel PID lengan robot.','terbatas/materi/materi_6ab70c207a7f0.pdf',NULL),
('Pengenalan Python untuk Robotika','Programming','Sintaks dasar Python dan penerapannya pada kontrol sederhana perangkat.',NULL,NULL),
('Dasar IoT dengan ESP32','IoT','Menghubungkan sensor ke ESP32 dan mengirim data ke internet.',NULL,NULL),
('Machine Learning Dasar untuk Deteksi Objek','AI','Konsep dasar klasifikasi gambar dan penerapan model ringan di perangkat kecil.',NULL,NULL),
('Elektronika Dasar: Sensor dan Aktuator','Elektronika','Pengenalan komponen elektronika yang umum dipakai pada proyek robotika.',NULL,NULL),
('Algoritma dan Logika Pemrograman','Pemrograman','Dasar berpikir algoritmik sebelum masuk ke bahasa pemrograman tertentu.',NULL,'https://scratch.mit.edu');

INSERT INTO pelatihan (id, nama, deskripsi, tanggal_mulai, tanggal_selesai, waktu, lokasi, kuota, status, akses) VALUES
(1,'Workshop Mini Arduino untuk Pemula','Pengenalan rangkaian dan pemrograman Arduino dari nol, diakhiri proyek robot pemadam api sederhana.','2026-10-10','2026-10-24','Sabtu, 09.00 - 12.00','Lab Robotika',30,'Dibuka','Umum'),
(2,'Workshop Sensor dan IoT dengan ESP32','Praktik langsung membaca sensor, mengirim data ke dashboard, dan mengintegrasikan ESP32 dengan WiFi sekolah.','2026-11-07','2026-11-21','Sabtu, 09.00 - 12.00','Lab Komputer 1',20,'Dibuka','Anggota'),
(3,'Workshop Pemrograman Lanjutan','Pembahasan algoritma, struktur data, dan machine learning ringan untuk proyek robot sederhana.','2026-09-12','2026-09-26','Sabtu, 10.00 - 13.00','Lab Komputer 2',16,'Ditutup','Anggota'),
(4,'Workshop Terbuka Umum: Demo Robot Sortir','Pameran karya untuk siswa luar sekolah. Komunitas memamerkan lengan robot sortir warna.','2026-12-05','2026-12-05','Sabtu, 09.00 - 11.00','Aula Sekolah',50,'Dibuka','Umum');

INSERT INTO pelatihan_peserta (pelatihan_id, anggota_id, nama, kontak, asal, status) VALUES
(1,1,'Ahmad Fauzan Rizki','0812-3456-7890','SMA Negeri 2','Disetujui'),
(1,2,'Nabila Putri Salsabila','0813-2200-1122','SMA Negeri 2','Disetujui'),
(1,3,'Muhammad Rafi Ardiansyah','0852-9988-7766','SMA Negeri 2','Disetujui'),
(1,NULL,'Rangga Saputra','0877-4411-9090','SMP Negeri 5 Ternate','Disetujui'),
(2,4,'Siti Zahra Amelia','0812-7788-5544','SMA Negeri 2','Disetujui'),
(2,6,'Keisha Ayu Lestari','0812-4433-2211','SMA Negeri 2','Pending'),
(2,NULL,'Dewi Lestari','0813-9911-2233','SMA Negeri 1 Ternate','Pending'),
(3,5,'Bagas Dwi Prasetyo','0853-6677-3322','SMA Negeri 2','Disetujui');

INSERT INTO materi_pelatihan (materi_id, pelatihan_id) VALUES
(1,1),(2,1),
(3,2),(4,2),
(6,3),
(7,3);

INSERT INTO referensi (nama, url, deskripsi) VALUES
('Arduino','https://arduino.cc','Platform hardware dan software open-source untuk proyek elektronika.'),
('Tinkercad','https://tinkercad.com','Simulasi rangkaian elektronika dan desain 3D secara online.'),
('PictoBlox','https://thestempedia.com/product/pictoblox','Pemrograman blok visual untuk robotika dan IoT, cocok untuk pemula.'),
('Scratch','https://scratch.mit.edu','Bahasa pemrograman visual untuk belajar logika dasar.'),
('GitHub','https://github.com','Tempat menyimpan dan mengelola kode proyek komunitas secara kolaboratif.'),
('Python.org','https://python.org','Situs resmi bahasa pemrograman Python beserta dokumentasinya.');

INSERT INTO karya (judul, kategori, deskripsi, progres, publikasi, pembuat_id, teknologi, link_demo, foto, alasan_tolak, dibuat_pada) VALUES
('RoboLine V2 - Line Follower Cerdas','Robot','Robot line follower generasi kedua dengan sensor array 8 kanal dan algoritma PID untuk belokan tajam.','Showcased','Approved',1,'Arduino Uno, Sensor IR Array, Motor DC',NULL,'assets/img/karya-robot.svg',NULL,'2026-03-14'),
('SmartFarm IoT - Monitor Kelembapan Tanah','IoT','Sistem pemantauan kelembapan tanah otomatis dengan notifikasi ke dashboard web sederhana untuk lahan sekolah.','Completed','Approved',2,'ESP32, Sensor Soil Moisture, Firebase','demo.smartfarm.local','assets/img/karya-iot.svg',NULL,'2026-01-22'),
('Deteksi Sampah Otomatis dengan AI','AI','Model klasifikasi gambar untuk memilah sampah organik dan anorganik menggunakan kamera mini.','In Progress','Approved',6,'Python, TensorFlow Lite, Raspberry Pi',NULL,'assets/img/karya-ai.svg',NULL,'2026-05-02'),
('Lengan Robot Sortir Warna','Robot','Lengan robot 4 DOF yang menyortir objek berdasarkan warna menggunakan sensor warna TCS3200.','Completed','Approved',5,'Arduino Mega, Servo, Sensor TCS3200',NULL,'assets/img/karya-robot.svg',NULL,'2025-11-10'),
('Aplikasi Absensi Wajah dengan Python','Programming','Aplikasi absensi anggota komunitas menggunakan pengenalan wajah sederhana berbasis OpenCV.','In Progress','Pending',4,'Python, OpenCV',NULL,'assets/img/karya-programming.svg',NULL,'2026-08-20'),
('Sistem Keamanan Rumah berbasis Arduino','Elektronika','Rancangan sistem keamanan dengan sensor gerak dan notifikasi buzzer, masih tahap perencanaan komponen.','Idea','Pending',3,'Arduino Uno, PIR Sensor',NULL,'assets/img/karya-elektronika.svg',NULL,'2026-09-01'),
('Robot Pemadam Api Mini','Robot','Robot pemadam api skala kecil dengan sensor api dan kipas pemadam, tampil di pameran STEM sekolah.','Showcased','Approved',1,'Arduino Uno, Sensor Api, Kipas DC',NULL,'assets/img/karya-robot.svg',NULL,'2025-09-18'),
('Charger Otomatis untuk Robot Line Follower','Elektronika','Dudukan pengisian daya otomatis untuk robot line follower.','In Progress','Rejected',5,'Modul Charging, Relay',NULL,'assets/img/karya-elektronika.svg','Dokumentasi skema rangkaian belum lengkap, mohon dilengkapi dan diajukan kembali.','2026-06-30');

INSERT INTO lomba (id, nama, penyelenggara, tanggal, lokasi, kategori, status, hasil) VALUES
(1,'KRPAI 2025 Regional Sulut-Malut','Kemendikbudristek','2025-10-05','Manado','Robot Pemadam Api','Selesai','Juara 2'),
(2,'Lomba Karya Inovasi Siswa Provinsi Malut 2025','Dinas Pendidikan Provinsi','2025-11-12','Ternate','Inovasi Robotika','Selesai','Juara 2'),
(3,'KRPAI 2026 Regional','Kemendikbudristek','2026-03-15','Manado','Robot Pemadam Api','Selesai','Juara 3'),
(4,'Lomba Robotik Line Tracer Antar SMA Kota Ternate','MGMP Informatika Kota Ternate','2026-10-20','SMA Negeri 4 Ternate','Line Tracer','Pendaftaran Dibuka',NULL),
(5,'Olimpiade Sains Nasional bidang komputer','BSN','2026-11-25','Jakarta','Komputer','Akan Datang',NULL);

INSERT INTO lomba_peserta (lomba_id, anggota_id) VALUES
(1,1),(1,5),(2,2),(3,1),(3,6);

INSERT INTO prestasi (id, nama, lomba_id, tanggal, tingkat, hasil, deskripsi, foto, sertifikat, status, dibuat_pada) VALUES
(1,'Juara 2 KRPAI 2025 Regional Sulut-Malut',1,'2025-10-05','Provinsi','Juara 2','Tim pemadam api membawa pulang medali pada Regional KRPAI tingkat provinsi setelah melewati final dengan baik.','uploads/prestasi/prestasi_krpai.jpg','uploads/prestasi/sertifikat_krpai_2025.pdf','Tayang','2025-10-06'),
(2,'Juara 2 Lomba Karya Inovasi Siswa Provinsi Malut 2025',2,'2025-11-12','Provinsi','Juara 2','Inovasi sistem pemantauan lahan sekolah berhasil meraih Juara 2 tingkat provinsi.','uploads/prestasi/prestasi_inovasi.jpg','uploads/prestasi/sertifikat_inovasi_2025.pdf','Tayang','2025-11-13'),
(3,'Juara 3 KRPAI 2026 Regional',3,'2026-03-15','Provinsi','Juara 3','Tim komunitas melaju ke final setelah perbaikan algoritma PID dan pendinginan motor.','uploads/prestasi/prestasi_krpai_2026.jpg','uploads/prestasi/sertifikat_krpai_2026.pdf','Tayang','2026-03-16'),
(4,'Juara 1 Lomba Line Tracer Antar SMA Kota Ternate',NULL,'2026-05-09','Kota','Juara 1','Robot line tracerJuara 1 tingkat kota dengan waktu solving 18 detik.','uploads/prestasi/prestasi_line_tracer.jpg','uploads/prestasi/sertifikat_line_tracer_2026.pdf','Tayang','2026-05-10');

INSERT INTO prestasi_peserta (prestasi_id, anggota_id, nama) VALUES
(1,1,'Ahmad Fauzan Rizki'),(1,5,'Bagas Dwi Prasetyo'),
(2,2,'Nabila Putri Salsabila'),
(3,1,'Ahmad Fauzan Rizki'),(3,6,'Keisha Ayu Lestari'),
(4,1,'Ahmad Fauzan Rizki'),(4,3,'Muhammad Rafi Ardiansyah');

INSERT INTO galeri (judul, kategori, deskripsi, tanggal, foto, video) VALUES
('Latihan Rutin Merakit Chassis Line Follower','Latihan','Sesi latihan mingguan merakit chassis dan kalibrasi sensor line follower.','2026-08-15','uploads/galeri/galeri_latihan_chassis.jpg',NULL),
('Workshop Dasar Arduino untuk Anggota Baru','Workshop','Pengenalan pemrograman Arduino bagi anggota baru kelas X.','2026-07-20','uploads/galeri/galeri_workshop_arduino.jpg',NULL),
('KRPAI 2025 Regional Sulut-Malut','Lomba','Perjalanan tim mengikuti Kontes Robot Pemadam Api Indonesia tingkat regional.','2025-10-05','uploads/galeri/galeri_lomba_krpai.jpg',NULL),
('Pameran Karya STEM SMA Negeri 2 Ternate','Pameran','Komunitas memamerkan lima karya unggulan kepada seluruh siswa dan wali murid.','2025-12-02','uploads/galeri/galeri_pameran_stem.jpg',NULL),
('Demo Robot Sortir Warna saat Open House','Demo Robot','Demonstrasi langsung lengan robot sortir warna kepada calon siswa baru.','2026-02-08','uploads/galeri/galeri_demo_robot.jpg',NULL),
('Proses Perakitan Lengan Robot Sortir','Proses Proyek','Dokumentasi tahap perakitan mekanik sebelum robot diuji coba.','2025-10-25','uploads/galeri/galeri_proses_rakitan.jpg',NULL);

INSERT INTO kegiatan (nama, deskripsi, tanggal, waktu, lokasi, status, video) VALUES
('Latihan Rutin Robotika','Latihan mingguan pemrograman dan perakitan robot line follower.','2026-09-19','15.00 - 17.00','Lab Komputer 2','Akan Datang',NULL),
('Workshop Python untuk Pemula','Pengenalan dasar Python untuk anggota baru sebelum masuk ke materi AI.','2026-09-26','13.00 - 15.30','Lab Komputer 1','Akan Datang',NULL),
('Rapat Persiapan KRPAI 2027','Pembagian tugas tim dan evaluasi desain robot untuk KRPAI 2027.','2026-10-03','15.30 - 17.00','Ruang Robotika','Akan Datang',NULL),
('Demo Karya untuk Kepala Sekolah','Presentasi karya unggulan komunitas kepada kepala sekolah dan guru.','2026-08-28','09.00 - 10.00','Aula Sekolah','Selesai',NULL);

INSERT INTO pengumuman (judul, isi, tipe, tanggal) VALUES
('Pendaftaran Anggota Baru Tahun Ajaran 2026/2027','Komunitas Robotik membuka pendaftaran anggota baru untuk siswa kelas X dan XI melalui halaman Pendaftaran. Lengkapi formulir dan Pakta Integritas, lalu tunggu verifikasi admin.','Pendaftaran','2026-09-08'),
('Workshop Mini Arduino untuk Pemula Dibuka','Pendaftaran peserta Workshop Mini Arduino untuk Pemula telah dibuka. Kuota 30 peserta dan terbuka untuk umum termasuk siswa di luar sekolah.','Kegiatan','2026-09-20'),
('Perubahan Jadwal Latihan Rutin','Latihan rutin yang semula hari Rabu dipindahkan menjadi setiap hari Jumat pukul 15.00 WIT di Lab Komputer 2.','Jadwal','2026-09-05'),
('Deadline Proposal Karya untuk Pameran STEM','Anggota yang ingin karyanya ditampilkan pada Pameran STEM sekolah wajib mengumpulkan proposal singkat paling lambat 30 September 2026.','Deadline','2026-09-01');

INSERT INTO hero (foto, status) VALUES
('assets/img/hero-illustration.svg','Tampil'),
('assets/img/IMG-20251222-WA0052.jpg','Tampil');

-- ---------------------------------------------------------
-- PENDAFTARAN ANGGOTA + PAKTA INTEGRITAS (data contoh)
-- ---------------------------------------------------------
INSERT INTO pendaftaran (nama, kelas, kontak, email, alasan, pakta_integritas, nama_berkas, status, dibuat_pada) VALUES
('Rangga Saputra','X IPA 2','0877-4411-9090','rangga@email.com','Ingin belajar pemrograman robotika dan ikut lomba.',NULL,NULL,'Baru','2026-10-02 08:15:00'),
('Dewi Lestari','XI IPS 1','0813-9911-2233','dewi.lestari@email.com','Tertarik pada bidang AI dan deteksi objek.',NULL,NULL,'Baru','2026-10-02 09:40:00'),
('Fajar Nugroho','X TKJ 3','0857-6677-8899','fajar.n@email.com','Meminat pada robotika dan elektronik dasar.',NULL,NULL,'Diverifikasi','2026-09-12 10:05:00');