-- Recruitment System Database Schema - FINAL OPTIMIZED VERSION
-- 
-- Technical: 5 soal (70%)
-- Psikotes: 3 skenario (30%)
-- 
-- Scoring: 0-10 scale
-- - Bagus: 8-10 (LULUS)
-- - Butuh Review: 5-7 (REVIEW)
-- - Belum Lulus: <5 (TIDAK LULUS)

CREATE DATABASE IF NOT EXISTS recruitment 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE recruitment;

DROP TABLE IF EXISTS applicants;

CREATE TABLE applicants (
    id VARCHAR(36) PRIMARY KEY,
    
    -- Personal data
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    whatsapp VARCHAR(20) NOT NULL,
    
    -- CV file
    cv_filename VARCHAR(255),
    cv_original_name VARCHAR(255),
    cv_mime_type VARCHAR(100),
    
    -- Technical scores (5 soal, 70% bobot)
    technical_score DECIMAL(4,1) NOT NULL DEFAULT 0,
    technical_correct INT DEFAULT 0,
    technical_total INT DEFAULT 5,
    technical_answers JSON,
    technical_details JSON,
    
    -- Psikotes scores (3 skenario, 30% bobot)
    psikotes_score DECIMAL(4,1) NOT NULL DEFAULT 0,
    psikotes_categories JSON,
    psikotes_answers JSON,
    psikotes_details JSON,
    
    -- Overall results
    overall_score DECIMAL(4,1) NOT NULL DEFAULT 0,
    status ENUM('LULUS', 'REVIEW', 'TIDAK LULUS') NOT NULL DEFAULT 'TIDAK LULUS',
    status_label VARCHAR(20) DEFAULT NULL,
    recommendation TEXT DEFAULT NULL,
    
    -- Timer (seconds)
    timer_personal INT DEFAULT 0,
    timer_technical INT DEFAULT 0,
    timer_psikotes INT DEFAULT 0,
    timer_total INT DEFAULT 0,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_overall_score (overall_score),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
