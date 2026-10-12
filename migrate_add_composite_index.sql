-- =========================================================
-- Migration: Add composite index for Pelatihan::idYangDiikuti()
-- Optimizes query: WHERE anggota_id = ? AND status = 'Disetujui'
-- =========================================================

ALTER TABLE pelatihan_peserta 
ADD INDEX idx_peserta_anggota_status (anggota_id, status);