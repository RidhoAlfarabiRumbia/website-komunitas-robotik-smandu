-- =========================================================
-- Migration: Add login_attempts table for persistent rate limiting
-- =========================================================

CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username_hash CHAR(40) NOT NULL,
    attempts INT NOT NULL DEFAULT 1,
    locked_until DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username_hash (username_hash)
) ENGINE=InnoDB;