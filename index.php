<?php
/**
 * RayCorp Recruitment System - FINAL OPTIMIZED VERSION
 * 
 * Technical: 3 soal (5 sub-pertanyaan) - 70%
 * Psikotes: 3 skenario - 30%
 */

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/includes/questions.php';

$technicalQuestions = getAllTechnicalQuestions();
$psikotesSkenario = getPsikotesSkenario();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment IT Staff Developer - RayCorp</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/anti-cheat.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🚀 <span class="brand-ray">Ray</span><span class="brand-corp">Corp</span> Recruitment</h1>
            <p>IT Staff Developer Position</p>
        </header>

        <!-- Landing Page -->
        <div id="landing-page" class="form-card landing-page">
            <div class="landing-header">
                <h2>Selamat Datang di Test Rekrutmen</h2>
                <p>IT Staff Developer - RayCorp</p>
            </div>

            <div class="info-box blue">
                <h3 class="info-box-title">📋 Tentang Test Ini</h3>
                <p>Test ini terdiri dari <strong>3 tahap</strong> yang harus diselesaikan dalam satu sesi.</p>
                <div class="stage-grid">
                    <div class="stage-card">
                        <div class="stage-icon">📝</div>
                        <div class="stage-title">Tahap 1</div>
                        <div class="stage-desc">Data Diri & CV</div>
                        <div class="stage-time">~2 menit</div>
                    </div>
                    <div class="stage-card">
                        <div class="stage-icon">💻</div>
                        <div class="stage-title">Tahap 2</div>
                        <div class="stage-desc">Technical Test</div>
                        <div class="stage-time">~5 menit</div>
                    </div>
                    <div class="stage-card">
                        <div class="stage-icon">🧠</div>
                        <div class="stage-title">Tahap 3</div>
                        <div class="stage-desc">Psikotes</div>
                        <div class="stage-time">~3 menit</div>
                    </div>
                </div>
            </div>

            <div class="info-box yellow">
                <h3 class="info-box-title">⚠️ Peraturan Test</h3>
                <ul class="rules-list">
                    <li><span class="bullet">•</span> Kerjakan sendiri <span class="highlight">tanpa bantuan orang lain</span></li>
                    <li class="danger"><span class="bullet">•</span> <strong>TIDAK BOLEH</strong> menggunakan internet (Google, ChatGPT, dll)</li>
                    <li><span class="bullet">•</span> Setelah submit, jawaban <span class="highlight">tidak dapat diubah</span></li>
                    <li><span class="bullet">•</span> Scoring: <span class="highlight">Bagus (8-10)</span>, <span class="highlight">Review (5-7)</span>, <span class="highlight">Belum Lulus (&lt;5)</span></li>
                </ul>
            </div>

            <div class="info-box purple">
                <h3 class="info-box-title">⏱️ Waktu Pengerjaan Dicatat</h3>
                <div class="timer-box">
                    <span class="time-value">≤ 10</span>
                    <span class="time-unit">menit total</span>
                    <p class="time-note">Kandidat yang cepat dengan hasil baik mendapat nilai plus</p>
                </div>
            </div>

            <div class="info-box green">
                <h3 class="info-box-title">✅ Sebelum Mulai, Pastikan:</h3>
                <ul class="checklist">
                    <li><span class="check">✓</span> CV sudah siap (PDF, JPG, PNG, DOC - max 5MB)</li>
                    <li><span class="check">✓</span> Koneksi internet stabil</li>
                    <li><span class="check">✓</span> Waktu luang sekitar 10-15 menit</li>
                </ul>
            </div>

            <div class="start-section">
                <button type="button" class="btn-start" onclick="startTest()">🚀 Mulai Test Sekarang</button>
                <p class="start-note">Dengan memulai test, Anda menyetujui bahwa data yang diberikan adalah benar</p>
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
                        <div class="step-label">Tes Teknis</div>
                    </div>
                    <div class="progress-step" data-step="3">
                        <div class="step-circle">3</div>
                        <div class="step-label">Psikotes</div>
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
                <!-- Step 1: Personal Data -->
                <div id="step-1" class="form-section active">
                    <h2 class="section-title">Data Pribadi</h2>
                    <p class="section-subtitle">Lengkapi informasi diri Anda.</p>

                    <div class="form-group">
                        <label class="form-label" for="nama">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" id="nama" class="form-input" placeholder="Masukkan nama lengkap">
                        <div class="error-message">Nama lengkap wajib diisi</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" class="form-input" placeholder="contoh@email.com">
                        <div class="error-message">Email tidak valid</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="whatsapp">Nomor WhatsApp <span class="required">*</span></label>
                        <input type="tel" id="whatsapp" class="form-input" placeholder="08123456789">
                        <div class="error-message">Nomor WhatsApp tidak valid</div>
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

                    <div class="form-actions">
                        <div></div>
                        <button type="button" class="btn btn-primary" onclick="nextStep()">Lanjut ke Tes Teknis →</button>
                    </div>
                </div>

                <!-- Step 2: Technical Test (5 soal) -->
                <div id="step-2" class="form-section test-content">
                    <h2 class="section-title">💻 Tes Teknis</h2>
                    <p class="section-subtitle">Jawab 5 pertanyaan berikut. Bobot: 70% dari total nilai.</p>

                    <?php foreach ($technicalQuestions as $section): ?>
                    <div class="question-card question-container">
                        <h3 class="question-title no-select"><?php echo htmlspecialchars($section['title']); ?></h3>
                        <div class="question-description code-snippet">
                            <?php echo nl2br(htmlspecialchars($section['description'])); ?>
                        </div>

                        <?php foreach ($section['questions'] as $question): ?>
                        <div class="sub-question" data-question="<?php echo htmlspecialchars($question['id']); ?>">
                            <div class="sub-question-label no-select"><?php echo htmlspecialchars($question['label']); ?></div>
                            <div class="radio-group question-options">
                                <?php foreach ($question['options'] as $option): ?>
                                <label class="radio-option answer-option">
                                    <input type="radio" name="<?php echo htmlspecialchars($question['id']); ?>" value="<?php echo htmlspecialchars($option['value']); ?>">
                                    <span class="radio-option-label"><?php echo htmlspecialchars($option['value'] . '. ' . $option['label']); ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="prevStep()">← Kembali</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep()">Lanjut ke Psikotes →</button>
                    </div>
                </div>

                <!-- Step 3: Psikotes (3 skenario) -->
                <div id="step-3" class="form-section test-content">
                    <h2 class="section-title">🧠 Psikotes</h2>
                    <p class="section-subtitle">Pilih jawaban yang paling menggambarkan sikap Anda. Bobot: 30% dari total nilai.</p>

                    <?php foreach ($psikotesSkenario as $scenario): ?>
                    <div class="question-card scenario-question question-container" data-scenario="<?php echo htmlspecialchars($scenario['id']); ?>">
                        <h3 class="question-title no-select"><?php echo htmlspecialchars($scenario['title']); ?></h3>
                        <div class="question-description no-select"><?php echo htmlspecialchars($scenario['description']); ?></div>
                        <div class="radio-group question-options">
                            <?php foreach ($scenario['options'] as $option): ?>
                            <label class="radio-option answer-option">
                                <input type="radio" name="<?php echo htmlspecialchars($scenario['id']); ?>" value="<?php echo htmlspecialchars($option['value']); ?>">
                                <span class="radio-option-label"><?php echo htmlspecialchars($option['value'] . '. ' . $option['label']); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="prevStep()">← Kembali</button>
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

    <script src="assets/js/anti-cheat.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
