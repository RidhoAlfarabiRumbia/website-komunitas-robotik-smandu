-- =========================================================
-- Migrasi 2026-10-20: Kuota pelatihan diganti informasi mode
-- "Kuota Peserta" dihapus; diganti kolom `mode` (Daring/Luring)
-- yang dipilih admin lewat form CRUD pelatihan.
-- =========================================================
-- Jika sudah pernah dijalankan, kolom `kuota` tidak ada dan
-- perintah di bawah akan berhenti di error pertama (aman untuk
-- lihat pesan lalu cek DESC pelatihan).

ALTER TABLE pelatihan
  ADD COLUMN mode ENUM('Daring','Luring') NOT NULL DEFAULT 'Daring' AFTER lokasi,
  DROP COLUMN kuota;

-- Verifikasi hasil:
-- DESCRIBE pelatihan;
