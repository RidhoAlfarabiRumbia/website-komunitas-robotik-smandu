-- =========================================================
-- MIGRASI: fitur lupa password via email
-- Jalankan SATU KALI pada database yang sudah ada.
-- Backup dulu: mysqldump -u root db_robotik > backup.sql
-- =========================================================
USE db_robotik;

SET @ada_email := (SELECT COUNT(*) FROM information_schema.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email');
SET @sql := IF(@ada_email = 0,
  'ALTER TABLE users ADD COLUMN email VARCHAR(120) DEFAULT NULL AFTER username',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @ada_index := (SELECT COUNT(*) FROM information_schema.STATISTICS
                   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'uq_users_email');
SET @sql := IF(@ada_index = 0,
  'ALTER TABLE users ADD UNIQUE KEY uq_users_email (email)',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  email_hash CHAR(64) NOT NULL,
  token_hash CHAR(64) DEFAULT NULL,
  requested_ip VARCHAR(45) DEFAULT NULL,
  expires_at DATETIME DEFAULT NULL,
  used_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_password_resets_token (token_hash),
  KEY idx_password_resets_email_created (email_hash, created_at),
  KEY idx_password_resets_ip_created (requested_ip, created_at),
  KEY idx_password_resets_user (user_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
