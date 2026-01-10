-- ============================================================================
-- Recruitment System Database Schema v2.0
-- RayCorp & PT Lunaray Cahya Abadi - Multi-Division Recruitment System
-- ============================================================================
-- 
-- Perombakan sistem rekrutmen untuk mendukung:
-- - Multi-divisi dengan karakteristik kerja berbeda
-- - Form aplikasi berbasis value & adab (termasuk spiritualitas kerja)
-- - Tes logika universal 25 soal dengan standar berbeda per posisi
-- - Tes psikologi 5 bagian untuk penempatan peran yang tepat
-- - Integrasi dengan proses offline (interview, probation)
--
-- Position Tracks:
-- - Track A (Operator): fokus kepatuhan & kejujuran
-- - Track B (Staff): fokus konsistensi & refleksi  
-- - Track C (Supervisor_Management): fokus akurasi & kesadaran nilai
--
-- Work Patterns:
-- - Presisi_Monoton: R&D Lab, QC, Produksi detail
-- - Presisi_Dinamis: Supervisor, Planner, Koordinator
-- - Eksploratif_Terstruktur: Product Dev, R&D konsep
-- - Eksploratif_Dinamis: Kreatif, Branding, Campaign
-- ============================================================================

CREATE DATABASE IF NOT EXISTS recruitment 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE recruitment;

-- Drop existing tables if they exist (for clean migration)
DROP TABLE IF EXISTS applicants;
DROP TABLE IF EXISTS admin_users;

-- ============================================================================
-- APPLICANTS TABLE - Main candidate data storage
-- ============================================================================
CREATE TABLE applicants (
    id VARCHAR(36) PRIMARY KEY,
    
    -- ========================================================================
    -- POSITION SELECTION (Requirement 1)
    -- ========================================================================
    position_applied VARCHAR(100) NOT NULL COMMENT 'Posisi yang dilamar: operator_produksi, staff_kantor, supervisor, rnd_qc_lab, kreatif, product_development, management',
    position_track ENUM('operator', 'staff', 'supervisor_management') NOT NULL COMMENT 'Track penilaian: operator (A), staff (B), supervisor_management (C)',
    expected_work_pattern VARCHAR(50) DEFAULT NULL COMMENT 'Pola kerja yang diharapkan untuk posisi: presisi_monoton, presisi_dinamis, eksploratif_terstruktur, eksploratif_dinamis',
    
    -- ========================================================================
    -- SECTION A: DATA PRIBADI (Personal Data)
    -- ========================================================================
    nama VARCHAR(255) NOT NULL,
    tempat_lahir VARCHAR(100) DEFAULT NULL,
    tanggal_lahir DATE DEFAULT NULL,
    alamat_domisili TEXT DEFAULT NULL,
    whatsapp VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    status_pernikahan ENUM('belum_menikah', 'menikah', 'janda_duda') DEFAULT NULL,
    
    -- CV File Storage
    cv_filename VARCHAR(255) DEFAULT NULL COMMENT 'Encrypted filename on server',
    cv_original_name VARCHAR(255) DEFAULT NULL COMMENT 'Original filename from user',
    cv_mime_type VARCHAR(100) DEFAULT NULL COMMENT 'File MIME type (PDF, JPG, PNG, DOC)',
    
    -- ========================================================================
    -- SECTION B: LATAR BELAKANG (Background)
    -- ========================================================================
    aktivitas_saat_ini TEXT DEFAULT NULL COMMENT 'Aktivitas/pekerjaan saat ini',
    pengalaman_relevan TEXT DEFAULT NULL COMMENT 'Pengalaman kerja yang relevan',
    
    -- ========================================================================
    -- SECTION C: RIWAYAT PENDIDIKAN (Education)
    -- ========================================================================
    pendidikan_institusi VARCHAR(255) DEFAULT NULL,
    pendidikan_jurusan VARCHAR(255) DEFAULT NULL,
    pendidikan_tahun_lulus VARCHAR(10) DEFAULT NULL,
    alasan_jurusan TEXT DEFAULT NULL COMMENT 'Alasan memilih jurusan tersebut',

    -- ========================================================================
    -- SECTION D: VALUE & CARA PANDANG KERJA (Value & Work View)
    -- ========================================================================
    arti_tanggung_jawab TEXT DEFAULT NULL COMMENT 'Arti bekerja dengan tanggung jawab',
    cerita_kesalahan TEXT DEFAULT NULL COMMENT 'Cerita kesalahan kerja dan pembelajaran',
    langkah_target_minim_arahan TEXT DEFAULT NULL COMMENT 'Langkah saat diberi target dengan arahan minim',
    
    -- ========================================================================
    -- SECTION E: ADAB & SIKAP PROFESIONAL (Professional Attitude)
    -- ========================================================================
    arti_adab TEXT DEFAULT NULL COMMENT 'Arti adab dalam dunia kerja',
    respon_tidak_sepakat TEXT DEFAULT NULL COMMENT 'Respon saat tidak sepakat dengan atasan',
    cara_sampaikan_kritik TEXT DEFAULT NULL COMMENT 'Cara menyampaikan kritik/ketidaksetujuan',
    pengalaman_tidak_adil TEXT DEFAULT NULL COMMENT 'Pengalaman merasa diperlakukan tidak adil',
    prioritas_pendapat_vs_sikap TEXT DEFAULT NULL COMMENT 'Prioritas: menyampaikan pendapat vs menjaga sikap (dengan penjelasan)',
    
    -- ========================================================================
    -- SECTION F: MOTIVASI & KEBERMANFAATAN (Motivation & Benefit)
    -- ========================================================================
    alasan_melamar TEXT DEFAULT NULL COMMENT 'Alasan melamar di RayCorp',
    harapan_selain_gaji TEXT DEFAULT NULL COMMENT 'Harapan selain gaji',
    makna_bermanfaat TEXT DEFAULT NULL COMMENT 'Makna bermanfaat dalam bekerja',
    bertahan_saat_lelah TEXT DEFAULT NULL COMMENT 'Apa yang membuat bertahan saat lelah',
    respon_tidak_cocok_sistem TEXT DEFAULT NULL COMMENT 'Respon jika tidak cocok dengan sistem kerja',
    
    -- ========================================================================
    -- SECTION G: KETERSEDIAAN & KOMITMEN (Availability & Commitment)
    -- ========================================================================
    bersedia_probation BOOLEAN DEFAULT TRUE COMMENT 'Bersedia probation dengan evaluasi hasil',
    bersedia_feedback BOOLEAN DEFAULT TRUE COMMENT 'Bersedia menerima feedback jujur',
    kapan_mulai DATE DEFAULT NULL COMMENT 'Kapan bisa mulai bekerja',
    ekspektasi_gaji DECIMAL(15,2) DEFAULT NULL COMMENT 'Ekspektasi gaji (optional)',
    
    -- ========================================================================
    -- LOGIC TEST RESULTS (25 Questions - 7 Sections)
    -- Requirement 3: Universal Logic Test with Position-Based Threshold
    -- ========================================================================
    logic_score INT DEFAULT 0 COMMENT 'Total correct answers (0-25)',
    logic_correct INT DEFAULT 0 COMMENT 'Number of correct answers',
    logic_total INT DEFAULT 25 COMMENT 'Total questions (always 25)',
    logic_threshold INT DEFAULT 0 COMMENT 'Threshold for position (12/14/17/20)',
    logic_status ENUM('aman', 'rawan', 'tidak_aman') DEFAULT 'tidak_aman' COMMENT 'Status based on position threshold',
    logic_answers JSON DEFAULT NULL COMMENT 'All answers submitted by candidate',
    logic_details JSON DEFAULT NULL COMMENT 'Detailed breakdown per section (A-G)',
    
    -- ========================================================================
    -- PSYCHOLOGY TEST RESULTS (5 Sections A-E)
    -- Requirement 4: Universal Psychology Test for Role Placement
    -- ========================================================================
    -- Work Pattern Identification
    psychology_pattern VARCHAR(50) DEFAULT NULL COMMENT 'Identified work pattern: presisi_monoton, presisi_dinamis, eksploratif_terstruktur, eksploratif_dinamis',
    psychology_placement_recommendation TEXT DEFAULT NULL COMMENT 'Recommended placement based on work pattern',
    
    -- Fit Score & Mismatch Detection (Requirement 7)
    psychology_fit_score DECIMAL(5,2) DEFAULT NULL COMMENT 'Fit Score 0-100% based on Position_Scoring_Matrix',
    psychology_pattern_mismatch BOOLEAN DEFAULT FALSE COMMENT 'TRUE if work pattern does not match expected pattern for position',
    psychology_alternative_positions JSON DEFAULT NULL COMMENT 'Alternative positions that better match candidate profile',
    
    -- Section Scores (A-E)
    psychology_section_a_score DECIMAL(4,2) DEFAULT NULL COMMENT 'Section A: Ketelitian & Daya Tahan score',
    psychology_section_b_score DECIMAL(4,2) DEFAULT NULL COMMENT 'Section B: Stabilitas & Respon Kejenuhan score',
    psychology_section_c_score DECIMAL(4,2) DEFAULT NULL COMMENT 'Section C: Pola Respon Perubahan score',
    psychology_section_d_score DECIMAL(4,2) DEFAULT NULL COMMENT 'Section D: Orientasi Kerja score',
    psychology_section_e_score DECIMAL(4,2) DEFAULT NULL COMMENT 'Section E: Logika Kerja Dasar score',
    
    -- Raw Data Storage
    psychology_answers JSON DEFAULT NULL COMMENT 'All psychology test answers',
    psychology_details JSON DEFAULT NULL COMMENT 'Detailed breakdown per section',

    -- ========================================================================
    -- HR ASSESSMENT - RUBRIK ADAB 6 ASPEK (A-F)
    -- Requirement 5: Dual-Standard Assessment System
    -- ========================================================================
    -- Aspek A: Cara Memandang Otoritas & Atasan
    hr_adab_a_otoritas ENUM('sehat', 'waspada', 'tidak_cocok') DEFAULT NULL,
    
    -- Aspek B: Respon Terhadap Koreksi & Umpan Balik
    hr_adab_b_koreksi ENUM('sehat', 'waspada', 'tidak_cocok') DEFAULT NULL,
    
    -- Aspek C: Sikap Saat Tidak Sepakat
    hr_adab_c_tidak_sepakat ENUM('sehat', 'waspada', 'tidak_cocok') DEFAULT NULL,
    
    -- Aspek D: Kesadaran Diri & Refleksi
    hr_adab_d_kesadaran_diri ENUM('sehat', 'waspada', 'tidak_cocok') DEFAULT NULL,
    
    -- Aspek E: Kecocokan dengan Nilai RayCorp
    hr_adab_e_kecocokan_nilai ENUM('sehat', 'waspada', 'tidak_cocok') DEFAULT NULL,
    
    -- Aspek F: Value Kebermanfaatan & Spiritualitas Kerja (4 sub-aspek)
    hr_adab_f1_orientasi_niat ENUM('sehat', 'waspada', 'tidak_cocok') DEFAULT NULL COMMENT 'F1: Orientasi Niat Bekerja (amanah vs transaksional)',
    hr_adab_f2_respon_lelah ENUM('sehat', 'waspada', 'tidak_cocok') DEFAULT NULL COMMENT 'F2: Respon Terhadap Lelah & Kesulitan (memaknai vs mengeluh)',
    hr_adab_f3_keikhlasan ENUM('sehat', 'waspada', 'tidak_cocok') DEFAULT NULL COMMENT 'F3: Keikhlasan & Kerja Tanpa Sorotan',
    hr_adab_f4_spiritual ENUM('sehat', 'waspada', 'tidak_cocok') DEFAULT NULL COMMENT 'F4: Keselarasan dengan Nilai Spiritual RayCorp',
    
    -- ========================================================================
    -- HR ASSESSMENT SUMMARY
    -- ========================================================================
    hr_value_fit ENUM('selaras', 'abu_abu', 'tidak_cocok') DEFAULT NULL COMMENT 'Overall value fit assessment',
    hr_adab_fit ENUM('selaras', 'abu_abu', 'tidak_cocok') DEFAULT NULL COMMENT 'Overall adab fit assessment',
    hr_risk_note TEXT DEFAULT NULL COMMENT 'Risk notes from HR assessment',
    hr_decision ENUM('lanjut', 'hold', 'stop') DEFAULT NULL COMMENT 'HR decision: proceed, hold, or stop',
    hr_notes TEXT DEFAULT NULL COMMENT 'General HR notes',
    hr_assessed_by VARCHAR(255) DEFAULT NULL COMMENT 'HR assessor name/ID',
    hr_assessed_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Assessment timestamp',
    
    -- ========================================================================
    -- INTERVIEW & PROBATION TRACKING (Offline Process Integration)
    -- Requirement 10: Integration with Offline Recruitment Process
    -- ========================================================================
    -- Interview HRD
    interview_hrd_notes TEXT DEFAULT NULL COMMENT 'Notes from HRD interview',
    interview_hrd_date DATE DEFAULT NULL COMMENT 'HRD interview date',
    interview_hrd_result ENUM('lanjut', 'hold', 'stop') DEFAULT NULL COMMENT 'HRD interview result',
    
    -- Interview User (Department Head)
    interview_user_notes TEXT DEFAULT NULL COMMENT 'Notes from User/Department interview',
    interview_user_date DATE DEFAULT NULL COMMENT 'User interview date',
    interview_user_result ENUM('lanjut', 'hold', 'stop') DEFAULT NULL COMMENT 'User interview result',
    
    -- Probation Tracking (0-90 days)
    probation_status ENUM('belum', '0_14_hari', '15_30_hari', '31_90_hari', 'lulus', 'tidak_lulus') DEFAULT 'belum' COMMENT 'Probation phase: adaptasi (0-14), mulai berdiri (15-30), mulai berdampak (31-90)',
    probation_start_date DATE DEFAULT NULL COMMENT 'Probation start date',
    probation_notes TEXT DEFAULT NULL COMMENT 'Probation evaluation notes',
    
    -- Final Decision
    final_decision ENUM('diterima', 'ditolak', 'pending') DEFAULT 'pending' COMMENT 'Final recruitment decision',
    final_decision_date DATE DEFAULT NULL COMMENT 'Final decision date',
    final_decision_by VARCHAR(255) DEFAULT NULL COMMENT 'Person who made final decision',
    
    -- ========================================================================
    -- TIMER TRACKING (seconds)
    -- ========================================================================
    timer_form INT DEFAULT 0 COMMENT 'Time spent on application form',
    timer_logic INT DEFAULT 0 COMMENT 'Time spent on logic test (max 1800s = 30min)',
    timer_psychology INT DEFAULT 0 COMMENT 'Time spent on psychology test',
    timer_total INT DEFAULT 0 COMMENT 'Total time spent',
    
    -- ========================================================================
    -- TIMESTAMPS & METADATA
    -- ========================================================================
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL COMMENT 'When candidate completed all online tests',
    
    -- ========================================================================
    -- INDEXES FOR PERFORMANCE
    -- ========================================================================
    INDEX idx_position (position_applied),
    INDEX idx_position_track (position_track),
    INDEX idx_logic_status (logic_status),
    INDEX idx_psychology_pattern (psychology_pattern),
    INDEX idx_psychology_fit_score (psychology_fit_score),
    INDEX idx_hr_decision (hr_decision),
    INDEX idx_final_decision (final_decision),
    INDEX idx_probation_status (probation_status),
    INDEX idx_created_at (created_at),
    INDEX idx_email (email)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- ADMIN USERS TABLE - Authentication for Admin Panel
-- Requirement 9.4: Admin authentication
-- ============================================================================
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE COMMENT 'Login username',
    password_hash VARCHAR(255) NOT NULL COMMENT 'Bcrypt hashed password',
    nama VARCHAR(255) NOT NULL COMMENT 'Display name',
    role ENUM('hr', 'manager', 'admin', 'value_keeper') DEFAULT 'hr' COMMENT 'User role: hr, manager, admin, value_keeper (founder/senior HR)',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Account active status',
    last_login TIMESTAMP NULL DEFAULT NULL COMMENT 'Last login timestamp',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123 - CHANGE IN PRODUCTION!)
-- Password hash generated with: password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO admin_users (username, password_hash, nama, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');


-- ============================================================================
-- POSITION SCORING MATRIX TABLE - Configuration for Position-Based Scoring
-- Requirement 7: Position-Based Scoring Matrix
-- ============================================================================
CREATE TABLE position_scoring_matrix (
    id INT AUTO_INCREMENT PRIMARY KEY,
    position_code VARCHAR(50) NOT NULL UNIQUE COMMENT 'Position identifier: operator_produksi, staff_kantor, supervisor, rnd_qc_lab, kreatif, product_development, management',
    position_name VARCHAR(100) NOT NULL COMMENT 'Display name for position',
    position_track ENUM('operator', 'staff', 'supervisor_management') NOT NULL COMMENT 'Assessment track',
    
    -- Logic Test Threshold (Requirement 7.2)
    logic_threshold INT NOT NULL COMMENT 'Minimum correct answers required (out of 25)',
    
    -- Expected Work Pattern (Requirement 7.3)
    expected_work_pattern VARCHAR(50) DEFAULT NULL COMMENT 'Expected pattern: presisi_monoton, presisi_dinamis, eksploratif_terstruktur, eksploratif_dinamis, or NULL for flexible',
    
    -- Psychology Weight Matrix (Requirement 7.3)
    -- Values: 0=tidak penting, 1=rendah, 2=sedang, 3=tinggi, 4=sangat tinggi
    psych_weight_section_a INT NOT NULL DEFAULT 2 COMMENT 'Weight for Section A: Ketelitian & Daya Tahan (0-4)',
    psych_weight_section_b INT NOT NULL DEFAULT 2 COMMENT 'Weight for Section B: Stabilitas & Respon Kejenuhan (0-4)',
    psych_weight_section_c INT NOT NULL DEFAULT 2 COMMENT 'Weight for Section C: Pola Respon Perubahan (0-4)',
    psych_weight_section_d INT NOT NULL DEFAULT 2 COMMENT 'Weight for Section D: Orientasi Kerja (0-4)',
    psych_weight_section_e INT NOT NULL DEFAULT 2 COMMENT 'Weight for Section E: Logika Kerja Dasar (0-4)',
    
    -- Position Description
    description TEXT DEFAULT NULL COMMENT 'Position description and requirements',
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Whether position is currently open for recruitment',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_position_code (position_code),
    INDEX idx_position_track (position_track),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INSERT DEFAULT POSITION SCORING MATRIX DATA
-- Based on Design Document specifications
-- ============================================================================
INSERT INTO position_scoring_matrix (
    position_code, 
    position_name, 
    position_track, 
    logic_threshold, 
    expected_work_pattern,
    psych_weight_section_a,
    psych_weight_section_b,
    psych_weight_section_c,
    psych_weight_section_d,
    psych_weight_section_e,
    description
) VALUES 
-- Operator Produksi: Track A, threshold 12, Presisi Monoton
(
    'operator_produksi',
    'Operator Produksi',
    'operator',
    12,
    'presisi_monoton',
    4, -- Section A: Ketelitian - sangat tinggi
    4, -- Section B: Stabilitas - sangat tinggi
    1, -- Section C: Dinamis - rendah
    1, -- Section D: Eksplorasi - rendah
    3, -- Section E: Logika Kerja - tinggi
    'Posisi level pabrik/produksi. Fokus pada kepatuhan SOP, kejujuran, dan sikap dasar. Kerja presisi monoton.'
),
-- Staff Kantor: Track B, threshold 17, Flexible (semua pattern diterima)
(
    'staff_kantor',
    'Staff Kantor (Admin/Finance/dll)',
    'staff',
    17,
    NULL, -- Flexible - semua pattern diterima
    2, -- Section A: Ketelitian - sedang
    2, -- Section B: Stabilitas - sedang
    2, -- Section C: Dinamis - sedang
    2, -- Section D: Eksplorasi - sedang
    3, -- Section E: Logika Kerja - tinggi
    'Posisi level kantor administratif. Fokus pada konsistensi, ketelitian, dan refleksi.'
),
-- Supervisor: Track C, threshold 20, Presisi Dinamis
(
    'supervisor',
    'Supervisor',
    'supervisor_management',
    20,
    'presisi_dinamis',
    3, -- Section A: Ketelitian - tinggi
    3, -- Section B: Stabilitas - tinggi
    4, -- Section C: Dinamis - sangat tinggi
    2, -- Section D: Eksplorasi - sedang
    3, -- Section E: Logika Kerja - tinggi
    'Posisi level pengawas. Fokus pada akurasi, tanggung jawab, koordinasi, dan adaptasi cepat.'
),
-- R&D/QC/Lab: Track B, threshold 17, Presisi Monoton
(
    'rnd_qc_lab',
    'R&D / QC / Lab',
    'staff',
    17,
    'presisi_monoton',
    4, -- Section A: Ketelitian - sangat tinggi
    4, -- Section B: Stabilitas - sangat tinggi
    1, -- Section C: Dinamis - rendah
    1, -- Section D: Eksplorasi - rendah
    4, -- Section E: Logika Kerja - sangat tinggi
    'Posisi Research & Development dan Quality Control. Butuh ketelitian ekstrem dan tahan rutinitas.'
),
-- Kreatif: Track B, threshold 14, Eksploratif Dinamis
(
    'kreatif',
    'Kreatif / Branding / Campaign',
    'staff',
    14,
    'eksploratif_dinamis',
    1, -- Section A: Ketelitian - rendah
    1, -- Section B: Stabilitas - rendah
    4, -- Section C: Dinamis - sangat tinggi
    4, -- Section D: Eksplorasi - sangat tinggi
    2, -- Section E: Logika Kerja - sedang
    'Posisi kreatif dan branding. Butuh ide out-of-the-box dan adaptasi tinggi.'
),
-- Product Development: Track B, threshold 17, Eksploratif Terstruktur
(
    'product_development',
    'Product Development',
    'staff',
    17,
    'eksploratif_terstruktur',
    2, -- Section A: Ketelitian - sedang
    2, -- Section B: Stabilitas - sedang
    3, -- Section C: Dinamis - tinggi
    3, -- Section D: Eksplorasi - tinggi
    3, -- Section E: Logika Kerja - tinggi
    'Posisi pengembangan produk. Butuh kreativitas dalam sistem yang terstruktur.'
),
-- Management: Track C, threshold 20, Presisi Dinamis
(
    'management',
    'Management',
    'supervisor_management',
    20,
    'presisi_dinamis',
    2, -- Section A: Ketelitian - sedang
    3, -- Section B: Stabilitas - tinggi
    3, -- Section C: Dinamis - tinggi
    2, -- Section D: Eksplorasi - sedang
    4, -- Section E: Logika Kerja - sangat tinggi
    'Posisi level manajemen. Fokus pada akurasi tinggi, tanggung jawab, dan kesadaran nilai.'
);
