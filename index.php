<?php
/**
 * RayCorp Recruitment System - Multi-Division Version
 * 
 * Flow: Pilih Posisi → Form Aplikasi → Tes Logika → Tes Psikologi → Hasil → (Offline) Interview → Probation
 * 
 * Supports multiple positions:
 * - Operator Produksi (Track A)
 * - Staff Kantor (Track B)
 * - Supervisor (Track C)
 * - R&D/QC/Lab (Track B - Presisi Monoton)
 * - Kreatif/Product Development (Track B - Eksploratif)
 * - Management (Track C)
 */

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/includes/questions.php';

// Position configuration with track and expected work pattern mapping
$positions = [
    'operator_produksi' => [
        'label' => 'Operator Produksi',
        'track' => 'operator',
        'expected_pattern' => 'precision_monoton',
        'description' => 'Kerja presisi monoton, fokus kepatuhan SOP',
        'icon' => '🏭',
        'logic_threshold' => 12
    ],
    'staff_kantor' => [
        'label' => 'Staff Kantor (Admin/Finance/dll)',
        'track' => 'staff',
        'expected_pattern' => null, // flexible - semua pattern diterima
        'description' => 'Kerja administratif, fokus konsistensi',
        'icon' => '💼',
        'logic_threshold' => 17
    ],
    'supervisor' => [
        'label' => 'Supervisor',
        'track' => 'supervisor_management',
        'expected_pattern' => 'precision_dynamic',
        'description' => 'Kerja presisi dinamis, fokus koordinasi',
        'icon' => '👔',
        'logic_threshold' => 20
    ],
    'rnd_qc_lab' => [
        'label' => 'R&D / QC / Lab',
        'track' => 'staff',
        'expected_pattern' => 'precision_monoton',
        'description' => 'Kerja presisi monoton tinggi, fokus ketelitian ekstrem',
        'icon' => '🔬',
        'logic_threshold' => 17
    ],
    'kreatif' => [
        'label' => 'Kreatif / Branding',
        'track' => 'staff',
        'expected_pattern' => 'explorative_dynamic',
        'description' => 'Kerja eksploratif dinamis, fokus inovasi',
        'icon' => '🎨',
        'logic_threshold' => 14
    ],
    'product_development' => [
        'label' => 'Product Development',
        'track' => 'staff',
        'expected_pattern' => 'explorative_structured',
        'description' => 'Kerja eksploratif terstruktur, fokus pengembangan produk',
        'icon' => '💡',
        'logic_threshold' => 17
    ],
    'management' => [
        'label' => 'Management',
        'track' => 'supervisor_management',
        'expected_pattern' => 'precision_dynamic',
        'description' => 'Kerja strategis, fokus tanggung jawab nilai',
        'icon' => '📊',
        'logic_threshold' => 20
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment - RayCorp & PT Lunaray Cahya Abadi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/result-summary.css">
    <link rel="stylesheet" href="assets/css/anti-cheat.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🚀 <span class="brand-ray">Ray</span><span class="brand-corp">Corp</span> Recruitment</h1>
            <p>RayCorp & PT Lunaray Cahya Abadi</p>
        </header>

        <!-- Landing Page -->
        <div id="landing-page" class="form-card landing-page">
            <div class="landing-header">
                <h2>Selamat Datang di Sistem Rekrutmen</h2>
                <p>Temukan posisi yang tepat untuk Anda</p>
            </div>

            <div class="info-box blue">
                <h3 class="info-box-title">📋 Alur Rekrutmen</h3>
                <p>Proses rekrutmen terdiri dari <strong>tahap online</strong> dan <strong>tahap offline</strong>.</p>
                <div class="stage-grid-extended">
                    <div class="stage-card">
                        <div class="stage-number">1</div>
                        <div class="stage-icon">🎯</div>
                        <div class="stage-title">Pilih Posisi</div>
                        <div class="stage-desc">Pilih divisi/posisi yang Anda lamar</div>
                        <div class="stage-badge online">Online</div>
                    </div>
                    <div class="stage-card">
                        <div class="stage-number">2</div>
                        <div class="stage-icon">📝</div>
                        <div class="stage-title">Form Aplikasi</div>
                        <div class="stage-desc">Data diri, value & adab</div>
                        <div class="stage-badge online">Online</div>
                    </div>
                    <div class="stage-card">
                        <div class="stage-number">3</div>
                        <div class="stage-icon">🧮</div>
                        <div class="stage-title">Tes Logika</div>
                        <div class="stage-desc">25 soal dalam 30 menit</div>
                        <div class="stage-badge online">Online</div>
                    </div>
                    <div class="stage-card">
                        <div class="stage-number">4</div>
                        <div class="stage-icon">🧠</div>
                        <div class="stage-title">Tes Psikologi</div>
                        <div class="stage-desc">Pola kerja & penempatan</div>
                        <div class="stage-badge online">Online</div>
                    </div>
                    <div class="stage-card">
                        <div class="stage-number">5</div>
                        <div class="stage-icon">📊</div>
                        <div class="stage-title">Hasil</div>
                        <div class="stage-desc">Ringkasan & rekomendasi</div>
                        <div class="stage-badge online">Online</div>
                    </div>
                    <div class="stage-card offline-stage">
                        <div class="stage-number">6</div>
                        <div class="stage-icon">🤝</div>
                        <div class="stage-title">Interview</div>
                        <div class="stage-desc">HRD & User</div>
                        <div class="stage-badge offline">Offline</div>
                    </div>
                    <div class="stage-card offline-stage">
                        <div class="stage-number">7</div>
                        <div class="stage-icon">⭐</div>
                        <div class="stage-title">Probation</div>
                        <div class="stage-desc">0-90 hari evaluasi</div>
                        <div class="stage-badge offline">Offline</div>
                    </div>
                </div>
            </div>

            <div class="info-box yellow">
                <h3 class="info-box-title">⚠️ Peraturan Test</h3>
                <ul class="rules-list">
                    <li><span class="bullet">•</span> Kerjakan sendiri <span class="highlight">tanpa bantuan orang lain</span></li>
                    <li class="danger"><span class="bullet">•</span> <strong>TIDAK BOLEH</strong> menggunakan internet (Google, ChatGPT, dll)</li>
                    <li><span class="bullet">•</span> Setelah submit, jawaban <span class="highlight">tidak dapat diubah</span></li>
                    <li><span class="bullet">•</span> Tes Logika: <span class="highlight">25 soal dalam 30 menit</span></li>
                    <li><span class="bullet">•</span> Tes Psikologi: <span class="highlight">5 bagian untuk identifikasi pola kerja</span></li>
                    <li><span class="bullet">•</span> Standar kelulusan <span class="highlight">berbeda sesuai posisi</span> yang dilamar</li>
                </ul>
            </div>

            <div class="info-box purple">
                <h3 class="info-box-title">🎯 Tentang Tes Ini</h3>
                <div class="test-info-grid">
                    <div class="test-info-item">
                        <div class="test-info-icon">🧮</div>
                        <div class="test-info-content">
                            <h4>Tes Logika Universal</h4>
                            <p>25 soal mencakup: Pola Angka, Instruksi, Hitung Praktis, Ketelitian, Urutan Kerja, Situasional, dan Pemahaman.</p>
                        </div>
                    </div>
                    <div class="test-info-item">
                        <div class="test-info-icon">🧠</div>
                        <div class="test-info-content">
                            <h4>Tes Psikologi Penempatan</h4>
                            <p>5 bagian untuk mengidentifikasi pola kerja Anda: Presisi Monoton, Presisi Dinamis, Eksploratif Terstruktur, atau Eksploratif Dinamis.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-box green">
                <h3 class="info-box-title">✅ Sebelum Mulai, Pastikan:</h3>
                <ul class="checklist">
                    <li><span class="check">✓</span> CV sudah siap (PDF, JPG, PNG, DOC - max 5MB)</li>
                    <li><span class="check">✓</span> Koneksi internet stabil</li>
                    <li><span class="check">✓</span> Waktu luang sekitar 45-60 menit</li>
                    <li><span class="check">✓</span> Siap menjawab pertanyaan tentang value dan adab kerja</li>
                </ul>
            </div>

            <div class="info-box cyan">
                <h3 class="info-box-title">📌 Proses Selanjutnya (Offline)</h3>
                <p>Setelah menyelesaikan tes online, kandidat yang lolos akan dihubungi untuk:</p>
                <ul class="offline-process-list">
                    <li><span class="step-icon">🤝</span> <strong>Interview HRD</strong> - Penilaian value & adab oleh Value Keeper</li>
                    <li><span class="step-icon">👥</span> <strong>Interview User</strong> - Penilaian kecocokan dengan tim</li>
                    <li><span class="step-icon">⭐</span> <strong>Probation</strong> - Evaluasi 0-90 hari (adaptasi → berdiri → berdampak)</li>
                </ul>
            </div>

            <div class="start-section">
                <button type="button" class="btn-start" onclick="showPositionSelection()">🎯 Pilih Posisi & Mulai</button>
                <p class="start-note">Dengan memulai test, Anda menyetujui bahwa data yang diberikan adalah benar</p>
            </div>
        </div>

        <!-- Position Selection Page -->
        <div id="position-selection-page" class="form-card" style="display: none;">
            <div class="landing-header">
                <h2>Pilih Posisi yang Anda Lamar</h2>
                <p>Pilih posisi yang sesuai dengan minat dan kemampuan Anda</p>
            </div>

            <div class="position-grid">
                <?php foreach ($positions as $key => $position): ?>
                <div class="position-card" data-position="<?php echo htmlspecialchars($key); ?>" 
                     data-track="<?php echo htmlspecialchars($position['track']); ?>"
                     data-pattern="<?php echo htmlspecialchars($position['expected_pattern'] ?? ''); ?>"
                     onclick="selectPosition('<?php echo htmlspecialchars($key); ?>')">
                    <div class="position-icon"><?php echo $position['icon']; ?></div>
                    <div class="position-info">
                        <h3 class="position-title"><?php echo htmlspecialchars($position['label']); ?></h3>
                        <p class="position-desc"><?php echo htmlspecialchars($position['description']); ?></p>
                        <div class="position-meta">
                            <span class="track-badge track-<?php echo htmlspecialchars($position['track']); ?>">
                                <?php 
                                    $trackLabels = [
                                        'operator' => 'Track A - Operator',
                                        'staff' => 'Track B - Staff',
                                        'supervisor_management' => 'Track C - Supervisor/Management'
                                    ];
                                    echo $trackLabels[$position['track']];
                                ?>
                            </span>
                        </div>
                    </div>
                    <div class="position-check">
                        <span class="check-icon">✓</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="position-info-box" id="selected-position-info" style="display: none;">
                <h4>Posisi Terpilih: <span id="selected-position-name"></span></h4>
                <div class="position-details">
                    <div class="detail-item">
                        <span class="detail-label">Track Penilaian:</span>
                        <span class="detail-value" id="selected-track"></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Standar Tes Logika:</span>
                        <span class="detail-value" id="selected-threshold"></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Pola Kerja yang Diharapkan:</span>
                        <span class="detail-value" id="selected-pattern"></span>
                    </div>
                </div>
            </div>

            <div class="form-actions position-actions">
                <button type="button" class="btn btn-secondary" onclick="backToLanding()">← Kembali</button>
                <button type="button" class="btn btn-primary" id="btn-start-test" onclick="startTest()" disabled>Lanjut ke Form Aplikasi →</button>
            </div>
        </div>

        <!-- Main Form -->
        <div id="main-form" class="secure-assessment" style="display: none;">
            <div class="progress-container">
                <div class="progress-steps">
                    <div class="progress-step active" data-step="1">
                        <div class="step-circle">1</div>
                        <div class="step-label">Data Pribadi</div>
                    </div>
                    <div class="progress-step" data-step="2">
                        <div class="step-circle">2</div>
                        <div class="step-label">Tes Logika</div>
                    </div>
                    <div class="progress-step" data-step="3">
                        <div class="step-circle">3</div>
                        <div class="step-label">Tes Psikologi</div>
                    </div>
                    <div class="progress-step" data-step="4">
                        <div class="step-circle">4</div>
                        <div class="step-label">Hasil</div>
                    </div>
                </div>
                <div class="timer-display">
                    <span id="timer-display">00:00</span>
                    <div class="timer-label">Waktu Pengerjaan</div>
                </div>
            </div>

            <div class="form-card">
                <!-- Step 1: Personal Data & Application Form -->
                <div id="step-1" class="form-section active">
                    <h2 class="section-title">📝 Form Aplikasi</h2>
                    <p class="section-subtitle">Lengkapi informasi diri dan jawab pertanyaan value & adab.</p>

                    <!-- Selected Position Display -->
                    <div class="selected-position-banner" id="form-position-banner">
                        <span class="position-label">Posisi yang dilamar:</span>
                        <span class="position-value" id="form-position-display"></span>
                    </div>

                    <!-- Section A: Data Pribadi -->
                    <div class="form-subsection">
                        <h3 class="subsection-title">A. Data Pribadi</h3>
                        
                        <div class="form-group">
                            <label class="form-label" for="nama">Nama Lengkap <span class="required">*</span></label>
                            <input type="text" id="nama" class="form-input" placeholder="Masukkan nama lengkap">
                            <div class="error-message">Nama lengkap wajib diisi</div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="tempat_lahir">Tempat Lahir</label>
                                <input type="text" id="tempat_lahir" class="form-input" placeholder="Kota kelahiran">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="tanggal_lahir">Tanggal Lahir</label>
                                <input type="date" id="tanggal_lahir" class="form-input">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="alamat_domisili">Alamat Domisili</label>
                            <textarea id="alamat_domisili" class="form-input form-textarea" placeholder="Alamat tempat tinggal saat ini" rows="2"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="whatsapp">Nomor WhatsApp <span class="required">*</span></label>
                                <input type="tel" id="whatsapp" class="form-input" placeholder="08123456789">
                                <div class="error-message">Nomor WhatsApp tidak valid</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="email">Email <span class="required">*</span></label>
                                <input type="email" id="email" class="form-input" placeholder="contoh@email.com">
                                <div class="error-message">Email tidak valid</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="status_pernikahan">Status Pernikahan</label>
                            <select id="status_pernikahan" class="form-input form-select">
                                <option value="">-- Pilih --</option>
                                <option value="belum_menikah">Belum Menikah</option>
                                <option value="menikah">Menikah</option>
                                <option value="janda_duda">Janda/Duda</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Upload CV <span class="required">*</span></label>
                            <div id="cv-dropzone" class="file-upload">
                                <div class="file-upload-icon">📄</div>
                                <div class="file-upload-text">Klik atau drag file ke sini</div>
                                <div class="file-upload-hint">PDF, JPG, PNG, DOC (Maks. 5MB)</div>
                                <div id="cv-filename" class="file-name" style="display: none;"></div>
                            </div>
                            <input type="file" id="cv-input" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="display: none;">
                            <div class="error-message" id="cv-error">CV wajib diupload</div>
                        </div>
                    </div>

                    <!-- Section B: Latar Belakang -->
                    <div class="form-subsection">
                        <h3 class="subsection-title">B. Latar Belakang</h3>
                        
                        <div class="form-group">
                            <label class="form-label" for="aktivitas_saat_ini">Aktivitas Saat Ini</label>
                            <textarea id="aktivitas_saat_ini" class="form-input form-textarea" placeholder="Apa yang sedang Anda kerjakan saat ini? (bekerja/kuliah/lainnya)" rows="2"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="pengalaman_relevan">Pengalaman Kerja Relevan</label>
                            <textarea id="pengalaman_relevan" class="form-input form-textarea" placeholder="Ceritakan pengalaman kerja yang relevan dengan posisi yang dilamar" rows="3"></textarea>
                        </div>
                    </div>

                    <!-- Section C: Riwayat Pendidikan -->
                    <div class="form-subsection">
                        <h3 class="subsection-title">C. Riwayat Pendidikan</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="pendidikan_institusi">Institusi Pendidikan Terakhir</label>
                                <input type="text" id="pendidikan_institusi" class="form-input" placeholder="Nama sekolah/universitas">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="pendidikan_jurusan">Jurusan</label>
                                <input type="text" id="pendidikan_jurusan" class="form-input" placeholder="Jurusan/program studi">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="pendidikan_tahun_lulus">Tahun Lulus</label>
                                <input type="text" id="pendidikan_tahun_lulus" class="form-input" placeholder="2024">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="alasan_jurusan">Alasan Memilih Jurusan</label>
                                <input type="text" id="alasan_jurusan" class="form-input" placeholder="Mengapa memilih jurusan tersebut?">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="backToPositionSelection()">← Kembali</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep()">Lanjut ke Value & Adab →</button>
                    </div>
                </div>

                <!-- Step 1b: Value & Adab Questions (Part of Form) -->
                <div id="step-1b" class="form-section">
                    <h2 class="section-title">📝 Form Aplikasi (Lanjutan)</h2>
                    <p class="section-subtitle">Jawab pertanyaan tentang value dan adab kerja Anda.</p>

                    <!-- Section D: Value & Cara Pandang Kerja -->
                    <div class="form-subsection">
                        <h3 class="subsection-title">D. Value & Cara Pandang Kerja</h3>
                        
                        <div class="form-group">
                            <label class="form-label" for="arti_tanggung_jawab">Apa arti "bekerja dengan tanggung jawab" menurut Anda? <span class="required">*</span></label>
                            <textarea id="arti_tanggung_jawab" class="form-input form-textarea" placeholder="Jelaskan pandangan Anda..." rows="3"></textarea>
                            <div class="error-message">Pertanyaan ini wajib dijawab</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="cerita_kesalahan">Ceritakan pengalaman saat Anda melakukan kesalahan dalam pekerjaan. Apa yang Anda pelajari? <span class="required">*</span></label>
                            <textarea id="cerita_kesalahan" class="form-input form-textarea" placeholder="Ceritakan pengalaman Anda..." rows="3"></textarea>
                            <div class="error-message">Pertanyaan ini wajib dijawab</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="langkah_target_minim_arahan">Jika diberi target dengan arahan minim, langkah apa yang akan Anda ambil? <span class="required">*</span></label>
                            <textarea id="langkah_target_minim_arahan" class="form-input form-textarea" placeholder="Jelaskan langkah-langkah Anda..." rows="3"></textarea>
                            <div class="error-message">Pertanyaan ini wajib dijawab</div>
                        </div>
                    </div>

                    <!-- Section E: Adab & Sikap Profesional -->
                    <div class="form-subsection">
                        <h3 class="subsection-title">E. Adab & Sikap Profesional</h3>
                        
                        <div class="form-group">
                            <label class="form-label" for="arti_adab">Apa arti "adab" dalam dunia kerja menurut Anda? <span class="required">*</span></label>
                            <textarea id="arti_adab" class="form-input form-textarea" placeholder="Jelaskan pandangan Anda..." rows="3"></textarea>
                            <div class="error-message">Pertanyaan ini wajib dijawab</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="respon_tidak_sepakat">Bagaimana respon Anda saat tidak sepakat dengan keputusan atasan? <span class="required">*</span></label>
                            <textarea id="respon_tidak_sepakat" class="form-input form-textarea" placeholder="Jelaskan cara Anda merespon..." rows="3"></textarea>
                            <div class="error-message">Pertanyaan ini wajib dijawab</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="cara_sampaikan_kritik">Bagaimana cara Anda menyampaikan kritik atau ketidaksetujuan? <span class="required">*</span></label>
                            <textarea id="cara_sampaikan_kritik" class="form-input form-textarea" placeholder="Jelaskan cara Anda..." rows="3"></textarea>
                            <div class="error-message">Pertanyaan ini wajib dijawab</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="pengalaman_tidak_adil">Ceritakan pengalaman saat Anda merasa diperlakukan tidak adil. Bagaimana Anda menghadapinya?</label>
                            <textarea id="pengalaman_tidak_adil" class="form-input form-textarea" placeholder="Ceritakan pengalaman Anda..." rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Mana yang lebih Anda prioritaskan: menyampaikan pendapat atau menjaga sikap? <span class="required">*</span></label>
                            <div class="radio-group-vertical">
                                <label class="radio-option">
                                    <input type="radio" name="prioritas_pendapat_vs_sikap" value="pendapat">
                                    <span class="radio-option-label">Menyampaikan pendapat lebih penting</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="prioritas_pendapat_vs_sikap" value="sikap">
                                    <span class="radio-option-label">Menjaga sikap lebih penting</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="prioritas_pendapat_vs_sikap" value="seimbang">
                                    <span class="radio-option-label">Keduanya sama penting, tergantung situasi</span>
                                </label>
                            </div>
                            <div class="form-group" style="margin-top: 10px;">
                                <label class="form-label" for="prioritas_penjelasan">Jelaskan alasan Anda:</label>
                                <textarea id="prioritas_penjelasan" class="form-input form-textarea" placeholder="Jelaskan alasan pilihan Anda..." rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="prevStep()">← Kembali</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep()">Lanjut ke Motivasi →</button>
                    </div>
                </div>

                <!-- Step 1c: Motivation & Availability -->
                <div id="step-1c" class="form-section">
                    <h2 class="section-title">📝 Form Aplikasi (Lanjutan)</h2>
                    <p class="section-subtitle">Ceritakan motivasi dan ketersediaan Anda.</p>

                    <!-- Section F: Motivasi & Kebermanfaatan -->
                    <div class="form-subsection">
                        <h3 class="subsection-title">F. Motivasi & Kebermanfaatan</h3>
                        
                        <div class="form-group">
                            <label class="form-label" for="alasan_melamar">Mengapa Anda melamar di RayCorp? <span class="required">*</span></label>
                            <textarea id="alasan_melamar" class="form-input form-textarea" placeholder="Jelaskan alasan Anda..." rows="3"></textarea>
                            <div class="error-message">Pertanyaan ini wajib dijawab</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="harapan_selain_gaji">Selain gaji, apa yang Anda harapkan dari pekerjaan ini?</label>
                            <textarea id="harapan_selain_gaji" class="form-input form-textarea" placeholder="Jelaskan harapan Anda..." rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="makna_bermanfaat">Apa makna "bermanfaat" dalam bekerja menurut Anda? <span class="required">*</span></label>
                            <textarea id="makna_bermanfaat" class="form-input form-textarea" placeholder="Jelaskan pandangan Anda..." rows="3"></textarea>
                            <div class="error-message">Pertanyaan ini wajib dijawab</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="bertahan_saat_lelah">Apa yang membuat Anda bertahan saat merasa lelah dalam bekerja? <span class="required">*</span></label>
                            <textarea id="bertahan_saat_lelah" class="form-input form-textarea" placeholder="Jelaskan..." rows="3"></textarea>
                            <div class="error-message">Pertanyaan ini wajib dijawab</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="respon_tidak_cocok_sistem">Bagaimana respon Anda jika merasa tidak cocok dengan sistem kerja yang ada?</label>
                            <textarea id="respon_tidak_cocok_sistem" class="form-input form-textarea" placeholder="Jelaskan respon Anda..." rows="3"></textarea>
                        </div>
                    </div>

                    <!-- Section G: Ketersediaan & Komitmen -->
                    <div class="form-subsection">
                        <h3 class="subsection-title">G. Ketersediaan & Komitmen</h3>
                        
                        <div class="form-group">
                            <label class="form-label">Apakah Anda bersedia menjalani masa probation dengan evaluasi hasil? <span class="required">*</span></label>
                            <div class="radio-group-horizontal">
                                <label class="radio-option">
                                    <input type="radio" name="bersedia_probation" value="ya">
                                    <span class="radio-option-label">Ya, bersedia</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="bersedia_probation" value="tidak">
                                    <span class="radio-option-label">Tidak</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Apakah Anda bersedia menerima feedback jujur tentang kinerja Anda? <span class="required">*</span></label>
                            <div class="radio-group-horizontal">
                                <label class="radio-option">
                                    <input type="radio" name="bersedia_feedback" value="ya">
                                    <span class="radio-option-label">Ya, bersedia</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="bersedia_feedback" value="tidak">
                                    <span class="radio-option-label">Tidak</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="kapan_mulai">Kapan Anda bisa mulai bekerja?</label>
                                <input type="date" id="kapan_mulai" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="ekspektasi_gaji">Ekspektasi Gaji (Rp)</label>
                                <input type="number" id="ekspektasi_gaji" class="form-input" placeholder="Contoh: 5000000">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="prevStep()">← Kembali</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep()">Lanjut ke Tes Logika →</button>
                    </div>
                </div>

                <!-- Step 2: Logic Test -->
                <div id="step-2" class="form-section test-content">
                    <h2 class="section-title">🧮 Tes Logika</h2>
                    <p class="section-subtitle">25 soal dalam 30 menit. Standar kelulusan sesuai posisi yang dilamar.</p>

                    <!-- Logic Test Timer -->
                    <div class="logic-test-header">
                        <div class="logic-timer-box">
                            <div class="logic-timer-icon">⏱️</div>
                            <div class="logic-timer-content">
                                <div class="logic-timer-label">Sisa Waktu</div>
                                <div class="logic-timer-value" id="logic-timer">30:00</div>
                            </div>
                        </div>
                        <div class="logic-progress-box">
                            <div class="logic-progress-label">Progress</div>
                            <div class="logic-progress-bar">
                                <div class="logic-progress-fill" id="logic-progress-fill" style="width: 0%"></div>
                            </div>
                            <div class="logic-progress-text"><span id="logic-answered">0</span> / 25 soal dijawab</div>
                        </div>
                    </div>

                    <!-- Section Navigation -->
                    <div class="logic-section-nav" id="logic-section-nav">
                        <button type="button" class="section-nav-btn active" data-section="section_a" onclick="showLogicSection('section_a')">
                            <span class="section-nav-letter">A</span>
                            <span class="section-nav-title">Pola Angka</span>
                            <span class="section-nav-count" id="nav-count-section_a">0/4</span>
                        </button>
                        <button type="button" class="section-nav-btn" data-section="section_b" onclick="showLogicSection('section_b')">
                            <span class="section-nav-letter">B</span>
                            <span class="section-nav-title">Instruksi</span>
                            <span class="section-nav-count" id="nav-count-section_b">0/3</span>
                        </button>
                        <button type="button" class="section-nav-btn" data-section="section_c" onclick="showLogicSection('section_c')">
                            <span class="section-nav-letter">C</span>
                            <span class="section-nav-title">Hitung Praktis</span>
                            <span class="section-nav-count" id="nav-count-section_c">0/5</span>
                        </button>
                        <button type="button" class="section-nav-btn" data-section="section_d" onclick="showLogicSection('section_d')">
                            <span class="section-nav-letter">D</span>
                            <span class="section-nav-title">Ketelitian</span>
                            <span class="section-nav-count" id="nav-count-section_d">0/5</span>
                        </button>
                        <button type="button" class="section-nav-btn" data-section="section_e" onclick="showLogicSection('section_e')">
                            <span class="section-nav-letter">E</span>
                            <span class="section-nav-title">Urutan Kerja</span>
                            <span class="section-nav-count" id="nav-count-section_e">0/3</span>
                        </button>
                        <button type="button" class="section-nav-btn" data-section="section_f" onclick="showLogicSection('section_f')">
                            <span class="section-nav-letter">F</span>
                            <span class="section-nav-title">Situasional</span>
                            <span class="section-nav-count" id="nav-count-section_f">0/2</span>
                        </button>
                        <button type="button" class="section-nav-btn" data-section="section_g" onclick="showLogicSection('section_g')">
                            <span class="section-nav-letter">G</span>
                            <span class="section-nav-title">Pemahaman</span>
                            <span class="section-nav-count" id="nav-count-section_g">0/3</span>
                        </button>
                    </div>

                    <!-- Logic Test Questions Container -->
                    <div class="logic-test-container" id="logic-test-container">
                        <!-- Questions will be rendered by JavaScript -->
                    </div>

                    <!-- Section Navigation Buttons -->
                    <div class="logic-section-actions">
                        <button type="button" class="btn btn-secondary" id="btn-prev-section" onclick="prevLogicSection()" style="display: none;">← Bagian Sebelumnya</button>
                        <button type="button" class="btn btn-primary" id="btn-next-section" onclick="nextLogicSection()">Bagian Selanjutnya →</button>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="prevStep()">← Kembali ke Form</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep()">Lanjut ke Tes Psikologi →</button>
                    </div>
                </div>

                <!-- Step 3: Psychology Test -->
                <div id="step-3" class="form-section test-content">
                    <h2 class="section-title">🧠 Tes Psikologi</h2>
                    <p class="section-subtitle">5 bagian untuk mengidentifikasi pola kerja Anda.</p>

                    <!-- Psychology Test Header -->
                    <div class="psychology-test-header">
                        <div class="psychology-timer-box" id="psychology-timer-box" style="display: none;">
                            <div class="psychology-timer-icon">⏱️</div>
                            <div class="psychology-timer-content">
                                <div class="psychology-timer-label">Sisa Waktu Bagian A</div>
                                <div class="psychology-timer-value" id="psychology-timer">07:00</div>
                            </div>
                        </div>
                        <div class="psychology-progress-box">
                            <div class="psychology-progress-label">Progress Tes Psikologi</div>
                            <div class="psychology-progress-bar">
                                <div class="psychology-progress-fill" id="psychology-progress-fill" style="width: 0%"></div>
                            </div>
                            <div class="psychology-progress-text">Bagian <span id="psychology-current-section">1</span> dari 5</div>
                        </div>
                    </div>

                    <!-- Section Navigation -->
                    <div class="psychology-section-nav" id="psychology-section-nav">
                        <button type="button" class="psych-section-nav-btn active" data-section="section_a" onclick="showPsychologySection('section_a')">
                            <span class="psych-section-nav-letter">A</span>
                            <span class="psych-section-nav-title">Ketelitian</span>
                            <span class="psych-section-nav-badge">7 menit</span>
                        </button>
                        <button type="button" class="psych-section-nav-btn" data-section="section_b" onclick="showPsychologySection('section_b')">
                            <span class="psych-section-nav-letter">B</span>
                            <span class="psych-section-nav-title">Stabilitas</span>
                            <span class="psych-section-nav-badge">4 soal</span>
                        </button>
                        <button type="button" class="psych-section-nav-btn" data-section="section_c" onclick="showPsychologySection('section_c')">
                            <span class="psych-section-nav-letter">C</span>
                            <span class="psych-section-nav-title">Perubahan</span>
                            <span class="psych-section-nav-badge">4 soal</span>
                        </button>
                        <button type="button" class="psych-section-nav-btn" data-section="section_d" onclick="showPsychologySection('section_d')">
                            <span class="psych-section-nav-letter">D</span>
                            <span class="psych-section-nav-title">Orientasi</span>
                            <span class="psych-section-nav-badge">4 soal</span>
                        </button>
                        <button type="button" class="psych-section-nav-btn" data-section="section_e" onclick="showPsychologySection('section_e')">
                            <span class="psych-section-nav-letter">E</span>
                            <span class="psych-section-nav-title">Logika Kerja</span>
                            <span class="psych-section-nav-badge">6 soal</span>
                        </button>
                    </div>

                    <!-- Psychology Test Questions Container -->
                    <div class="psychology-test-container" id="psychology-test-container">
                        <!-- Questions will be rendered by JavaScript -->
                    </div>

                    <!-- Section Navigation Buttons -->
                    <div class="psychology-section-actions">
                        <button type="button" class="btn btn-secondary" id="btn-prev-psych-section" onclick="prevPsychologySection()" style="display: none;">← Bagian Sebelumnya</button>
                        <button type="button" class="btn btn-primary" id="btn-next-psych-section" onclick="nextPsychologySection()">Bagian Selanjutnya →</button>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="prevStep()">← Kembali ke Tes Logika</button>
                        <button type="button" class="btn btn-success" onclick="submitForm()">Submit & Lihat Hasil 🎯</button>
                    </div>
                </div>

                <!-- Step 4: Result -->
                <div id="step-4" class="form-section">
                    <div class="result-container">
                        <div class="result-header">
                            <div class="result-icon">⏳</div>
                            <h2 class="result-status">Memproses...</h2>
                            <p class="result-message">Mohon tunggu, hasil sedang dihitung.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <div class="loading-text">Mengirim data...</div>
        </div>
    </div>

    <!-- Position Data for JavaScript -->
    <script>
        const positionData = <?php echo json_encode($positions); ?>;
        const patternLabels = {
            'precision_monoton': 'Presisi Monoton (R&D/QC/Lab/Produksi)',
            'precision_dynamic': 'Presisi Dinamis (Supervisor/Koordinator)',
            'explorative_structured': 'Eksploratif Terstruktur (Product Dev)',
            'explorative_dynamic': 'Eksploratif Dinamis (Kreatif/Branding)',
            '': 'Fleksibel (Semua pola diterima)'
        };
        
        // Logic Test Questions Data
        const logicQuestions = <?php echo json_encode($logicQuestions); ?>;
        
        // Psychology Test Questions Data
        const psychologyQuestions = <?php echo json_encode($psychologyQuestions); ?>;
    </script>
    <script src="assets/js/anti-cheat.js"></script>
    <script src="assets/js/result-summary.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
