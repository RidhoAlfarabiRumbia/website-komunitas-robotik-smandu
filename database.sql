-- =========================================================
-- Portal Komunitas Robotik SMA Negeri 2 - Skema Database
-- Sumber: dump db_robotik_4 (7 November 2026), disanitasi
-- Data sensitif (email, kontak, hash sandi, log login, dan dokumen
-- pendaftaran privat) telah diganti/dihapus.
-- Akun demo: admin / anggota1 / anggota2 (sandi: demo123).
-- Ganti sandi akun demo sebelum dipakai di produksi.
-- Import file ini untuk instalasi baru.
-- =========================================================

CREATE DATABASE IF NOT EXISTS db_robotik CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_robotik;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_robotik`
--

-- --------------------------------------------------------

--
-- Table structure for table `anggota`
--

CREATE TABLE `anggota` (
  `id` int(11) NOT NULL,
  `nama` varchar(120) NOT NULL,
  `kelas` varchar(40) NOT NULL,
  `kontak` varchar(60) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(191) DEFAULT NULL,
  `dibuat_pada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `anggota`
--

INSERT INTO `anggota` (`id`, `nama`, `kelas`, `kontak`, `deskripsi`, `foto`, `dibuat_pada`) VALUES
(24, 'Ridho Alfarabi Rumbia', 'Mahasiswa', NULL, 'tertarik dengan bidang robotik dan ingin serius mendalami bidang tersebut', 'uploads/anggota/anggota_6aec72fab8c53_opt.webp', '2026-11-03 12:56:44'),
(25, 'M Randi A Rahman', 'Mahasiswa', NULL, 'ingin mendalami ilmu di bidang robotik', NULL, '2026-11-04 10:09:08');

-- --------------------------------------------------------

--
-- Table structure for table `galeri`
--

CREATE TABLE `galeri` (
  `id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal` date NOT NULL,
  `foto` varchar(191) DEFAULT NULL,
  `video` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `galeri`
--

INSERT INTO `galeri` (`id`, `judul`, `kategori`, `deskripsi`, `tanggal`, `foto`, `video`) VALUES
(22, 'Dokumentasi workshop pengenalan python', 'Workshop', '', '2026-09-06', 'uploads/galeri/galeri_6aec7175d7586_opt.webp', '');

-- --------------------------------------------------------

--
-- Table structure for table `hero`
--

CREATE TABLE `hero` (
  `id` int(11) NOT NULL,
  `foto` varchar(191) DEFAULT NULL,
  `status` enum('Tampil','Sembunyi') NOT NULL DEFAULT 'Tampil',
  `dibuat_pada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hero`
--

INSERT INTO `hero` (`id`, `foto`, `status`, `dibuat_pada`) VALUES
(21, 'uploads/hero/hero_6aec705a3cb71_opt.webp', 'Tampil', '2026-11-03 12:59:37'),
(22, 'uploads/hero/hero_6aec70663ce03_opt.webp', 'Tampil', '2026-11-03 13:00:01'),
(23, 'uploads/hero/hero_6aec70794ac27_opt.webp', 'Tampil', '2026-11-03 13:00:50'),
(24, 'uploads/hero/hero_6aec708790e69_opt.webp', 'Tampil', '2026-11-03 13:01:08'),
(26, 'uploads/hero/hero_6aec709646a43_opt.webp', 'Tampil', '2026-11-03 13:02:25'),
(28, 'uploads/hero/hero_6aec70abe8770_opt.webp', 'Tampil', '2026-11-03 13:02:59'),
(29, 'uploads/hero/hero_6aec70c00252b_opt.webp', 'Tampil', '2026-11-03 13:03:19'),
(30, 'uploads/hero/hero_6aec70d7efbbf_opt.webp', 'Tampil', '2026-11-03 13:03:34'),
(31, 'uploads/hero/hero_6aec70eb3362b_opt.webp', 'Tampil', '2026-11-03 13:03:47');

-- --------------------------------------------------------

--
-- Table structure for table `karya`
--

CREATE TABLE `karya` (
  `id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `progres` enum('Sedang Dikerjakan','Selesai') NOT NULL DEFAULT 'Sedang Dikerjakan',
  `publikasi` enum('Menunggu','Disetujui','Ditolak') NOT NULL DEFAULT 'Menunggu',
  `pembuat_id` int(11) NOT NULL,
  `teknologi` varchar(255) DEFAULT NULL,
  `link_demo` varchar(191) DEFAULT NULL,
  `foto` varchar(191) DEFAULT NULL,
  `alasan_tolak` text DEFAULT NULL,
  `dibuat_pada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `karya`
--

INSERT INTO `karya` (`id`, `judul`, `deskripsi`, `progres`, `publikasi`, `pembuat_id`, `teknologi`, `link_demo`, `foto`, `alasan_tolak`, `dibuat_pada`) VALUES
(34, 'Dashboard Monitoring Kualitas Air Berbasis Python Streamlit', '', 'Selesai', 'Disetujui', 24, 'Python, Streamlit', '', 'uploads/karya/karya_6aec7150d9525_opt.webp', NULL, '2026-11-05 13:34:31');

-- --------------------------------------------------------

--
-- Table structure for table `karya_pembuat`
--

CREATE TABLE `karya_pembuat` (
  `karya_id` int(11) NOT NULL,
  `anggota_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `karya_pembuat`
--

INSERT INTO `karya_pembuat` (`karya_id`, `anggota_id`) VALUES
(34, 24),
(34, 25);

-- --------------------------------------------------------

--
-- Table structure for table `kegiatan`
--

CREATE TABLE `kegiatan` (
  `id` int(11) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal` date NOT NULL,
  `waktu` varchar(40) DEFAULT NULL,
  `lokasi` varchar(120) DEFAULT NULL,
  `status` enum('Akan Datang','Selesai') NOT NULL DEFAULT 'Akan Datang',
  `video` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kegiatan`
--

INSERT INTO `kegiatan` (`id`, `nama`, `deskripsi`, `tanggal`, `waktu`, `lokasi`, `status`, `video`) VALUES
(14, 'kegiatan workshop pengenalan python', '', '2026-09-06', '10.00', 'Lab Kimia SMAN 2 Kota Ternate', 'Selesai', '');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `username_hash` char(40) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 1,
  `locked_until` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lomba`
--

CREATE TABLE `lomba` (
  `id` int(11) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `penyelenggara` varchar(120) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `lokasi` varchar(120) DEFAULT NULL,
  `status` enum('Akan Datang','Selesai') NOT NULL DEFAULT 'Akan Datang',
  `hasil` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lomba`
--

INSERT INTO `lomba` (`id`, `nama`, `penyelenggara`, `tanggal`, `lokasi`, `status`, `hasil`) VALUES
(5, 'International Youth Robotic Competition (IYRC) 2025', 'International Youth Robot Association (IYRA)', '2025-12-21', 'Shenzen, China', 'Selesai', 'Juara I kategori Wonder Planet jenjang SMA, Juara II kategori Wonder Planet jenjang SMA.');

-- --------------------------------------------------------

--
-- Table structure for table `lomba_peserta`
--

CREATE TABLE `lomba_peserta` (
  `id` int(11) NOT NULL,
  `lomba_id` int(11) NOT NULL,
  `anggota_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materi`
--

CREATE TABLE `materi` (
  `id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `topik` varchar(60) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `file_path` varchar(191) DEFAULT NULL,
  `link_url` varchar(191) DEFAULT NULL,
  `dibuat_pada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `materi`
--

INSERT INTO `materi` (`id`, `judul`, `topik`, `deskripsi`, `file_path`, `link_url`, `dibuat_pada`) VALUES
(23, 'Materi pengenalan c++', 'Pemrograman', '', 'terbatas/materi/materi_6aec720b51d9e.pdf', '', '2026-11-05 21:09:47');

-- --------------------------------------------------------

--
-- Table structure for table `materi_pelatihan`
--

CREATE TABLE `materi_pelatihan` (
  `materi_id` int(11) NOT NULL,
  `pelatihan_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `materi_pelatihan`
--

INSERT INTO `materi_pelatihan` (`materi_id`, `pelatihan_id`) VALUES
(23, 6);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email_hash` char(64) NOT NULL,
  `token_hash` char(64) DEFAULT NULL,
  `requested_ip` varchar(45) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pelatihan`
--

CREATE TABLE `pelatihan` (
  `id` int(11) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `waktu` varchar(60) DEFAULT NULL,
  `lokasi` varchar(150) DEFAULT NULL,
  `mode` enum('Daring','Luring') NOT NULL DEFAULT 'Daring',
  `status` enum('Dibuka','Ditutup') NOT NULL DEFAULT 'Dibuka',
  `akses` enum('Umum','Anggota') NOT NULL DEFAULT 'Anggota',
  `dibuat_pada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pelatihan`
--

INSERT INTO `pelatihan` (`id`, `nama`, `deskripsi`, `tanggal_mulai`, `tanggal_selesai`, `waktu`, `lokasi`, `mode`, `status`, `akses`, `dibuat_pada`) VALUES
(6, 'Workshop Pengenalan C++', '', '2026-10-04', '2026-10-04', '20.00 WIT', 'Daring', 'Daring', 'Dibuka', 'Anggota', '2026-11-05 13:36:32'),
(7, 'workshop dasar arduino', '', '2026-11-05', '2026-11-07', '20.00 WIT', 'Daring', 'Daring', 'Ditutup', 'Anggota', '2026-11-05 14:55:15');

-- --------------------------------------------------------

--
-- Table structure for table `pelatihan_peserta`
--

CREATE TABLE `pelatihan_peserta` (
  `id` int(11) NOT NULL,
  `pelatihan_id` int(11) NOT NULL,
  `anggota_id` int(11) DEFAULT NULL,
  `nama` varchar(120) NOT NULL DEFAULT '-',
  `kontak` varchar(60) NOT NULL DEFAULT '-',
  `asal` varchar(120) DEFAULT NULL,
  `status` enum('Pending','Disetujui','Ditolak') NOT NULL DEFAULT 'Pending',
  `catatan` varchar(255) DEFAULT NULL,
  `dibuat_pada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id` int(11) NOT NULL,
  `nama` varchar(120) NOT NULL,
  `kelas` varchar(40) DEFAULT NULL,
  `kontak` varchar(60) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `pakta_integritas` varchar(191) DEFAULT NULL,
  `nama_berkas` varchar(191) DEFAULT NULL,
  `status` enum('Baru','Diverifikasi','Ditolak') NOT NULL DEFAULT 'Baru',
  `catatan` varchar(255) DEFAULT NULL,
  `anggota_id` int(11) DEFAULT NULL,
  `dibuat_pada` datetime DEFAULT current_timestamp(),
  `diverifikasi_pada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pendaftaran`
--

INSERT INTO `pendaftaran` (`id`, `nama`, `kelas`, `kontak`, `email`, `alasan`, `pakta_integritas`, `nama_berkas`, `status`, `catatan`, `anggota_id`, `dibuat_pada`, `diverifikasi_pada`) VALUES
(21, 'Ridho Alfarabi Rumbia', 'Mahasiswa', '0812-0000-0024', 'pendaftar1@example.invalid', 'Ingin belajar robotika dan mengembangkan keterampilan.', NULL, NULL, 'Diverifikasi', NULL, 24, '2026-11-03 12:51:30', '2026-11-03 04:56:44'),
(22, 'M Randi A Rahman', 'Mahasiswa', '0812-0000-0025', 'pendaftar2@example.invalid', 'Ingin mendalami ilmu di bidang robotik.', NULL, NULL, 'Diverifikasi', NULL, 25, '2026-11-04 10:04:16', '2026-11-04 02:09:08');

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL,
  `kunci` varchar(60) NOT NULL,
  `nilai` text DEFAULT NULL,
  `dibuat_pada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `kunci`, `nilai`, `dibuat_pada`) VALUES
(102, 'pendaftaran_mode', 'buka', '2026-11-03 12:39:57'),
(103, 'pendaftaran_mulai', '', '2026-11-03 12:39:57'),
(104, 'pendaftaran_selesai', '', '2026-11-03 12:39:57'),
(105, 'pakta_template', 'terbatas/pakta-integritas/pakta-integritas_6ae9585ad8db0.docx', '2026-11-03 12:43:22'),
(106, 'pakta_template_nama', 'Template_Pakta_Integritas_1_.docx', '2026-11-03 12:43:22'),
(107, 'pakta_template_diunggah', '2026-11-03 04:43:22', '2026-11-03 12:43:22'),
(108, 'komunitas_nama', 'Komunitas Robotik SMA Negeri 2 Kota Ternate', '2026-11-03 19:29:07'),
(109, 'komunitas_tagline', 'Belajar, berkarya, dan berkompetisi.', '2026-11-03 19:29:07'),
(110, 'komunitas_deskripsi', 'Komunitas robotik SMA Negeri 2 Kota Ternate. Wadah belajar, berkarya, dan berkompetisi.', '2026-11-03 19:29:07'),
(111, 'komunitas_visi', '', '2026-11-03 19:29:07'),
(112, 'komunitas_misi', '', '2026-11-03 19:29:07'),
(113, 'komunitas_lokasi', '', '2026-11-03 19:29:07'),
(114, 'komunitas_email', '', '2026-11-03 19:29:07'),
(115, 'komunitas_instagram', '', '2026-11-03 19:29:07'),
(116, 'komunitas_facebook', '', '2026-11-03 19:29:07'),
(117, 'komunitas_youtube', '', '2026-11-03 19:29:07'),
(118, 'komunitas_tiktok', '', '2026-11-03 19:29:07'),
(119, 'pembina_nama', 'Iriany S.Pd', '2026-11-03 19:29:07'),
(120, 'pembina_jabatan', '', '2026-11-03 19:29:07'),
(121, 'pembina_bio', '', '2026-11-03 19:29:07');

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `isi` text NOT NULL,
  `tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengumuman`
--

INSERT INTO `pengumuman` (`id`, `judul`, `isi`, `tanggal`) VALUES
(15, 'Link W3school untuk belajar bahasa pemrograman', 'https://www.w3schools.com/', '2026-11-03');

-- --------------------------------------------------------

--
-- Table structure for table `prestasi`
--

CREATE TABLE `prestasi` (
  `id` int(11) NOT NULL,
  `nama` text NOT NULL,
  `lomba_id` int(11) DEFAULT NULL,
  `lomba_teks` varchar(150) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `tingkat` enum('Sekolah','Kota','Provinsi','Nasional','Internasional') NOT NULL DEFAULT 'Sekolah',
  `hasil` text DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(191) DEFAULT NULL,
  `sertifikat` varchar(191) DEFAULT NULL,
  `status` enum('Tayang','Disembunyikan') NOT NULL DEFAULT 'Tayang',
  `dibuat_pada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prestasi`
--

INSERT INTO `prestasi` (`id`, `nama`, `lomba_id`, `lomba_teks`, `tanggal`, `tingkat`, `hasil`, `deskripsi`, `foto`, `sertifikat`, `status`, `dibuat_pada`) VALUES
(8, 'Dua Siswi SMAN 2 Kota Ternate Berhasil meraih sejumlah prestasi pada lomba International Youth Robotic Competition (IYRC) 2025 di Shenzen China', NULL, 'International Youth Robotic Competition (IYRC) 2025', '2025-12-21', 'Internasional', 'Medali Emas kategori Innovation Creative (Proyek Terbaik Kigomo Robotik), Medali Perak kategori Presentasi Terbaik proyek, Innovation Creative Kigomo Robotik, Juara I kategori Wonder Planet jenjang SMA, Juara II kategori Wonder Planet jenjang SMA.', '', 'uploads/content/content_6aec703d208d9_opt.webp', NULL, 'Tayang', '2026-11-05 13:13:57');

-- --------------------------------------------------------

--
-- Table structure for table `prestasi_peserta`
--

CREATE TABLE `prestasi_peserta` (
  `id` int(11) NOT NULL,
  `prestasi_id` int(11) NOT NULL,
  `anggota_id` int(11) DEFAULT NULL,
  `nama` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prestasi_peserta`
--

INSERT INTO `prestasi_peserta` (`id`, `prestasi_id`, `anggota_id`, `nama`) VALUES
(66, 8, NULL, 'Andini Putri Faruk'),
(67, 8, NULL, 'Siti Magfira');

-- --------------------------------------------------------

--
-- Table structure for table `referensi`
--

CREATE TABLE `referensi` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `url` varchar(191) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `referensi`
--

INSERT INTO `referensi` (`id`, `nama`, `url`, `deskripsi`) VALUES
(13, 'Platfrom Belajar Bahasa Pemrograman', 'https://www.w3schools.com', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(60) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','anggota') NOT NULL,
  `anggota_id` int(11) DEFAULT NULL,
  `dibuat_pada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--
-- Semua akun demo memakai sandi: demo123
-- Hash dibuat dengan password_hash('demo123', PASSWORD_DEFAULT)
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `anggota_id`, `dibuat_pada`) VALUES
(62, 'admin', NULL, '$2b$10$gA45g5QtC.vAdjUmkxnl/OnGPqkOfP2wEzUiH7BTn.6R80gHsNWl2', 'admin', NULL, '2026-11-03 12:29:16'),
(63, 'anggota1', NULL, '$2b$10$gA45g5QtC.vAdjUmkxnl/OnGPqkOfP2wEzUiH7BTn.6R80gHsNWl2', 'anggota', 24, '2026-11-03 12:56:44'),
(64, 'anggota2', NULL, '$2b$10$gA45g5QtC.vAdjUmkxnl/OnGPqkOfP2wEzUiH7BTn.6R80gHsNWl2', 'anggota', 25, '2026-11-04 10:09:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `galeri`
--
ALTER TABLE `galeri`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hero`
--
ALTER TABLE `hero`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `karya`
--
ALTER TABLE `karya`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pembuat_id` (`pembuat_id`),
  ADD KEY `idx_publikasi` (`publikasi`,`dibuat_pada`);

--
-- Indexes for table `karya_pembuat`
--
ALTER TABLE `karya_pembuat`
  ADD PRIMARY KEY (`karya_id`,`anggota_id`),
  ADD KEY `idx_karya_pembuat_anggota` (`anggota_id`);

--
-- Indexes for table `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_username_hash` (`username_hash`);

--
-- Indexes for table `lomba`
--
ALTER TABLE `lomba`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lomba_peserta`
--
ALTER TABLE `lomba_peserta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lomba_id` (`lomba_id`),
  ADD KEY `anggota_id` (`anggota_id`);

--
-- Indexes for table `materi`
--
ALTER TABLE `materi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `materi_pelatihan`
--
ALTER TABLE `materi_pelatihan`
  ADD PRIMARY KEY (`materi_id`,`pelatihan_id`),
  ADD KEY `pelatihan_id` (`pelatihan_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_password_resets_token` (`token_hash`),
  ADD KEY `idx_password_resets_email_created` (`email_hash`,`created_at`),
  ADD KEY `idx_password_resets_ip_created` (`requested_ip`,`created_at`),
  ADD KEY `idx_password_resets_user` (`user_id`);

--
-- Indexes for table `pelatihan`
--
ALTER TABLE `pelatihan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pelatihan_peserta`
--
ALTER TABLE `pelatihan_peserta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_peserta_pelatihan` (`pelatihan_id`,`status`),
  ADD KEY `idx_peserta_anggota` (`anggota_id`,`status`),
  ADD KEY `idx_peserta_status` (`status`),
  ADD KEY `idx_peserta_anggota_status` (`anggota_id`,`status`);

--
-- Indexes for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `anggota_id` (`anggota_id`),
  ADD KEY `idx_pendaftaran_status` (`status`,`dibuat_pada`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kunci` (`kunci`);

--
-- Indexes for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `prestasi`
--
ALTER TABLE `prestasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lomba_id` (`lomba_id`),
  ADD KEY `idx_prestasi_status` (`status`,`tanggal`);

--
-- Indexes for table `prestasi_peserta`
--
ALTER TABLE `prestasi_peserta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prestasi_id` (`prestasi_id`),
  ADD KEY `anggota_id` (`anggota_id`);

--
-- Indexes for table `referensi`
--
ALTER TABLE `referensi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `anggota_id` (`anggota_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `galeri`
--
ALTER TABLE `galeri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `hero`
--
ALTER TABLE `hero`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `karya`
--
ALTER TABLE `karya`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `kegiatan`
--
ALTER TABLE `kegiatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `lomba`
--
ALTER TABLE `lomba`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `lomba_peserta`
--
ALTER TABLE `lomba_peserta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `materi`
--
ALTER TABLE `materi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pelatihan`
--
ALTER TABLE `pelatihan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pelatihan_peserta`
--
ALTER TABLE `pelatihan_peserta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `prestasi`
--
ALTER TABLE `prestasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `prestasi_peserta`
--
ALTER TABLE `prestasi_peserta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `referensi`
--
ALTER TABLE `referensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `karya`
--
ALTER TABLE `karya`
  ADD CONSTRAINT `karya_ibfk_1` FOREIGN KEY (`pembuat_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `karya_pembuat`
--
ALTER TABLE `karya_pembuat`
  ADD CONSTRAINT `karya_pembuat_ibfk_1` FOREIGN KEY (`karya_id`) REFERENCES `karya` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `karya_pembuat_ibfk_2` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lomba_peserta`
--
ALTER TABLE `lomba_peserta`
  ADD CONSTRAINT `lomba_peserta_ibfk_1` FOREIGN KEY (`lomba_id`) REFERENCES `lomba` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lomba_peserta_ibfk_2` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `materi_pelatihan`
--
ALTER TABLE `materi_pelatihan`
  ADD CONSTRAINT `materi_pelatihan_ibfk_1` FOREIGN KEY (`materi_id`) REFERENCES `materi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `materi_pelatihan_ibfk_2` FOREIGN KEY (`pelatihan_id`) REFERENCES `pelatihan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pelatihan_peserta`
--
ALTER TABLE `pelatihan_peserta`
  ADD CONSTRAINT `pelatihan_peserta_ibfk_1` FOREIGN KEY (`pelatihan_id`) REFERENCES `pelatihan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pelatihan_peserta_ibfk_2` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD CONSTRAINT `pendaftaran_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `prestasi`
--
ALTER TABLE `prestasi`
  ADD CONSTRAINT `prestasi_ibfk_1` FOREIGN KEY (`lomba_id`) REFERENCES `lomba` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `prestasi_peserta`
--
ALTER TABLE `prestasi_peserta`
  ADD CONSTRAINT `prestasi_peserta_ibfk_1` FOREIGN KEY (`prestasi_id`) REFERENCES `prestasi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prestasi_peserta_ibfk_2` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
