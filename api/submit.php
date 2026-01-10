<?php
/**
 * Submission API Endpoint - Multi-Division Recruitment System v2.0
 * 
 * Handles complete application submission including:
 * - Position selection with track mapping
 * - Form sections A-G (Personal, Background, Education, Value, Adab, Motivation, Availability)
 * - Logic Test (25 questions, 7 sections) with position-based threshold
 * - Psychology Test (5 sections A-E) with work pattern identification
 * - Fit Score calculation and pattern mismatch detection
 * 
 * Requirements: 2.5, 7.1, 7.2, 7.3, 7.4, 8.1, 8.2, 8.3
 */

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e) {
    error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    require_once __DIR__ . '/../includes/ApiResponse.php';
    ApiResponse::serverError('Internal server error');
});

require_once __DIR__ . '/../includes/ApiResponse.php';
require_once __DIR__ . '/../includes/InputSanitizer.php';
require_once __DIR__ . '/../includes/FileUploader.php';
require_once __DIR__ . '/../includes/scoring.php';
require_once __DIR__ . '/../includes/questions.php';
require_once __DIR__ . '/../includes/position_scoring_matrix.php';
require_once __DIR__ . '/../config/database.php';

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Method not allowed', 405);
}

// Get input data
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($contentType, 'application/json') !== false) {
    $rawInput = file_get_contents('php://input');
    $inputData = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        ApiResponse::error('Invalid JSON input');
    }
} else {
    $inputData = $_POST;
    
    // Decode JSON fields if sent as strings
    $jsonFields = ['logicAnswers', 'psychologyAnswers', 'timer'];
    foreach ($jsonFields as $field) {
        if (isset($inputData[$field]) && is_string($inputData[$field])) {
            $decoded = json_decode($inputData[$field], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $inputData[$field] = $decoded;
            }
        }
    }
}

// ============================================
// VALIDATE REQUIRED FIELDS
// ============================================

$requiredFields = ['nama', 'email', 'whatsapp', 'position_applied'];
$validationErrors = InputSanitizer::validateRequired($inputData, $requiredFields);

if (!empty($validationErrors)) {
    ApiResponse::validationError($validationErrors);
}

// Validate position
$positionApplied = InputSanitizer::sanitizeString($inputData['position_applied'] ?? '');
if (!PositionScoringMatrix::isValidPosition($positionApplied)) {
    ApiResponse::error('Invalid position selected');
}

// ============================================
// SANITIZE PERSONAL DATA (Section A)
// ============================================

$nama = InputSanitizer::sanitizeString($inputData['nama']);
$email = InputSanitizer::validateEmail($inputData['email']);
$whatsapp = InputSanitizer::validatePhone($inputData['whatsapp']);

if ($email === null) {
    ApiResponse::error('Invalid email format');
}

if ($whatsapp === null) {
    ApiResponse::error('Invalid phone number format');
}

// Get position track and expected work pattern
$positionTrack = PositionScoringMatrix::getPositionTrack($positionApplied);
$expectedWorkPattern = PositionScoringMatrix::getExpectedWorkPattern($positionApplied);

// ============================================
// PROCESS CV UPLOAD
// ============================================

$cvData = ['filename' => null, 'originalName' => null, 'mimeType' => null];

if (isset($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
    $uploadResult = FileUploader::upload($_FILES['cv']);
    
    if (!$uploadResult['success']) {
        ApiResponse::error('CV upload failed: ' . $uploadResult['error']);
    }
    
    $cvData = [
        'filename' => $uploadResult['filename'],
        'originalName' => $uploadResult['originalName'],
        'mimeType' => $uploadResult['mimeType']
    ];
}

// ============================================
// SANITIZE FORM SECTIONS (B-G)
// ============================================

// Section A: Personal Data (additional fields)
$tempatLahir = InputSanitizer::sanitizeString($inputData['tempat_lahir'] ?? '');
$tanggalLahir = !empty($inputData['tanggal_lahir']) ? $inputData['tanggal_lahir'] : null;
$alamatDomisili = InputSanitizer::sanitizeString($inputData['alamat_domisili'] ?? '');
$statusPernikahan = InputSanitizer::sanitizeString($inputData['status_pernikahan'] ?? '');

// Section B: Background
$aktivitasSaatIni = InputSanitizer::sanitizeString($inputData['aktivitas_saat_ini'] ?? '');
$pengalamanRelevan = InputSanitizer::sanitizeString($inputData['pengalaman_relevan'] ?? '');

// Section C: Education
$pendidikanInstitusi = InputSanitizer::sanitizeString($inputData['pendidikan_institusi'] ?? '');
$pendidikanJurusan = InputSanitizer::sanitizeString($inputData['pendidikan_jurusan'] ?? '');
$pendidikanTahunLulus = InputSanitizer::sanitizeString($inputData['pendidikan_tahun_lulus'] ?? '');
$alasanJurusan = InputSanitizer::sanitizeString($inputData['alasan_jurusan'] ?? '');

// Section D: Value & Work View
$artiTanggungJawab = InputSanitizer::sanitizeString($inputData['arti_tanggung_jawab'] ?? '');
$ceritaKesalahan = InputSanitizer::sanitizeString($inputData['cerita_kesalahan'] ?? '');
$langkahTargetMinimArahan = InputSanitizer::sanitizeString($inputData['langkah_target_minim_arahan'] ?? '');

// Section E: Adab & Professional Attitude
$artiAdab = InputSanitizer::sanitizeString($inputData['arti_adab'] ?? '');
$responTidakSepakat = InputSanitizer::sanitizeString($inputData['respon_tidak_sepakat'] ?? '');
$caraSampaikanKritik = InputSanitizer::sanitizeString($inputData['cara_sampaikan_kritik'] ?? '');
$pengalamanTidakAdil = InputSanitizer::sanitizeString($inputData['pengalaman_tidak_adil'] ?? '');
$prioritasPendapatVsSikap = InputSanitizer::sanitizeString($inputData['prioritas_pendapat_vs_sikap'] ?? '');

// Section F: Motivation & Benefit
$alasanMelamar = InputSanitizer::sanitizeString($inputData['alasan_melamar'] ?? '');
$harapanSelainGaji = InputSanitizer::sanitizeString($inputData['harapan_selain_gaji'] ?? '');
$maknaBermanfaat = InputSanitizer::sanitizeString($inputData['makna_bermanfaat'] ?? '');
$bertahanSaatLelah = InputSanitizer::sanitizeString($inputData['bertahan_saat_lelah'] ?? '');
$responTidakCocokSistem = InputSanitizer::sanitizeString($inputData['respon_tidak_cocok_sistem'] ?? '');

// Section G: Availability & Commitment
$bersediaProbation = filter_var($inputData['bersedia_probation'] ?? true, FILTER_VALIDATE_BOOLEAN);
$bersediaFeedback = filter_var($inputData['bersedia_feedback'] ?? true, FILTER_VALIDATE_BOOLEAN);
$kapanMulai = !empty($inputData['kapan_mulai']) ? $inputData['kapan_mulai'] : null;
$ekspektasiGaji = InputSanitizer::sanitizeFloat($inputData['ekspektasi_gaji'] ?? null);

// ============================================
// CALCULATE LOGIC TEST SCORE (25 questions)
// Requirement 7.2, 8.1: Position-based threshold
// ============================================

$logicAnswers = $inputData['logicAnswers'] ?? [];

// Sanitize logic answers
$sanitizedLogicAnswers = [];
foreach ($logicAnswers as $questionId => $answer) {
    $sanitizedKey = InputSanitizer::sanitizeString($questionId);
    if (is_string($answer)) {
        $sanitizedAnswer = strtoupper(trim($answer));
        if (preg_match('/^[A-E]$/', $sanitizedAnswer)) {
            $sanitizedLogicAnswers[$sanitizedKey] = $sanitizedAnswer;
        }
    }
}

$logicScorer = new LogicScorer();
$logicResult = $logicScorer->calculate($sanitizedLogicAnswers, $positionApplied);

// ============================================
// CALCULATE PSYCHOLOGY TEST SCORE (5 sections)
// Requirement 7.3, 7.4, 8.2: Work pattern & Fit Score
// ============================================

$psychologyAnswers = $inputData['psychologyAnswers'] ?? [];

// Sanitize psychology answers (more complex due to Section A interactive elements)
$sanitizedPsychologyAnswers = [];
foreach ($psychologyAnswers as $questionId => $answer) {
    $sanitizedKey = InputSanitizer::sanitizeString($questionId);
    
    // Section A answers are objects with counts
    if (is_array($answer)) {
        $sanitizedPsychologyAnswers[$sanitizedKey] = [
            'marked_count' => InputSanitizer::sanitizeInt($answer['marked_count'] ?? 0) ?? 0,
            'circle_count' => InputSanitizer::sanitizeInt($answer['circle_count'] ?? 0) ?? 0,
            'cross_count' => InputSanitizer::sanitizeInt($answer['cross_count'] ?? 0) ?? 0,
        ];
    } else {
        // Sections B-E are multiple choice
        $sanitizedAnswer = strtoupper(trim($answer));
        if (preg_match('/^[A-D]$/', $sanitizedAnswer)) {
            $sanitizedPsychologyAnswers[$sanitizedKey] = $sanitizedAnswer;
        }
    }
}

$psychologyScorer = new PsychologyScorer();
$psychologyResult = $psychologyScorer->getFullAssessment($sanitizedPsychologyAnswers, $positionApplied);

// ============================================
// PROCESS TIMER DATA
// ============================================

$timerData = $inputData['timer'] ?? [];
$timerForm = InputSanitizer::sanitizeInt($timerData['form'] ?? 0) ?? 0;
$timerLogic = InputSanitizer::sanitizeInt($timerData['logic'] ?? 0) ?? 0;
$timerPsychology = InputSanitizer::sanitizeInt($timerData['psychology'] ?? 0) ?? 0;
$timerTotal = $timerForm + $timerLogic + $timerPsychology;

// ============================================
// DETERMINE OVERALL STATUS
// Requirement 8.3: Status determination
// ============================================

$overallStatus = determineOverallStatus(
    $logicResult['status'],
    $psychologyResult['fitScore'],
    $psychologyResult['patternMismatch']
);

// ============================================
// SAVE TO DATABASE
// ============================================

$applicantId = generateApplicantId($nama);

try {
    $db = Database::getInstance();
    $db->beginTransaction();
    
    $sql = "INSERT INTO applicants (
        id,
        -- Position
        position_applied, position_track, expected_work_pattern,
        -- Section A: Personal
        nama, tempat_lahir, tanggal_lahir, alamat_domisili, whatsapp, email, status_pernikahan,
        cv_filename, cv_original_name, cv_mime_type,
        -- Section B: Background
        aktivitas_saat_ini, pengalaman_relevan,
        -- Section C: Education
        pendidikan_institusi, pendidikan_jurusan, pendidikan_tahun_lulus, alasan_jurusan,
        -- Section D: Value
        arti_tanggung_jawab, cerita_kesalahan, langkah_target_minim_arahan,
        -- Section E: Adab
        arti_adab, respon_tidak_sepakat, cara_sampaikan_kritik, pengalaman_tidak_adil, prioritas_pendapat_vs_sikap,
        -- Section F: Motivation
        alasan_melamar, harapan_selain_gaji, makna_bermanfaat, bertahan_saat_lelah, respon_tidak_cocok_sistem,
        -- Section G: Availability
        bersedia_probation, bersedia_feedback, kapan_mulai, ekspektasi_gaji,
        -- Logic Test Results
        logic_score, logic_correct, logic_total, logic_threshold, logic_status, logic_answers, logic_details,
        -- Psychology Test Results
        psychology_pattern, psychology_placement_recommendation, psychology_fit_score, psychology_pattern_mismatch, psychology_alternative_positions,
        psychology_section_a_score, psychology_section_b_score, psychology_section_c_score, psychology_section_d_score, psychology_section_e_score,
        psychology_answers, psychology_details,
        -- Timer
        timer_form, timer_logic, timer_psychology, timer_total,
        -- Timestamps
        completed_at
    ) VALUES (
        :id,
        :position_applied, :position_track, :expected_work_pattern,
        :nama, :tempat_lahir, :tanggal_lahir, :alamat_domisili, :whatsapp, :email, :status_pernikahan,
        :cv_filename, :cv_original_name, :cv_mime_type,
        :aktivitas_saat_ini, :pengalaman_relevan,
        :pendidikan_institusi, :pendidikan_jurusan, :pendidikan_tahun_lulus, :alasan_jurusan,
        :arti_tanggung_jawab, :cerita_kesalahan, :langkah_target_minim_arahan,
        :arti_adab, :respon_tidak_sepakat, :cara_sampaikan_kritik, :pengalaman_tidak_adil, :prioritas_pendapat_vs_sikap,
        :alasan_melamar, :harapan_selain_gaji, :makna_bermanfaat, :bertahan_saat_lelah, :respon_tidak_cocok_sistem,
        :bersedia_probation, :bersedia_feedback, :kapan_mulai, :ekspektasi_gaji,
        :logic_score, :logic_correct, :logic_total, :logic_threshold, :logic_status, :logic_answers, :logic_details,
        :psychology_pattern, :psychology_placement_recommendation, :psychology_fit_score, :psychology_pattern_mismatch, :psychology_alternative_positions,
        :psychology_section_a_score, :psychology_section_b_score, :psychology_section_c_score, :psychology_section_d_score, :psychology_section_e_score,
        :psychology_answers, :psychology_details,
        :timer_form, :timer_logic, :timer_psychology, :timer_total,
        NOW()
    )";
    
    $stmt = $db->prepare($sql);

    $params = [
        'id' => $applicantId,
        // Position
        'position_applied' => $positionApplied,
        'position_track' => $positionTrack,
        'expected_work_pattern' => $expectedWorkPattern,
        // Section A: Personal
        'nama' => $nama,
        'tempat_lahir' => $tempatLahir ?: null,
        'tanggal_lahir' => $tanggalLahir,
        'alamat_domisili' => $alamatDomisili ?: null,
        'whatsapp' => $whatsapp,
        'email' => $email,
        'status_pernikahan' => in_array($statusPernikahan, ['belum_menikah', 'menikah', 'janda_duda']) ? $statusPernikahan : null,
        'cv_filename' => $cvData['filename'],
        'cv_original_name' => $cvData['originalName'],
        'cv_mime_type' => $cvData['mimeType'],
        // Section B: Background
        'aktivitas_saat_ini' => $aktivitasSaatIni ?: null,
        'pengalaman_relevan' => $pengalamanRelevan ?: null,
        // Section C: Education
        'pendidikan_institusi' => $pendidikanInstitusi ?: null,
        'pendidikan_jurusan' => $pendidikanJurusan ?: null,
        'pendidikan_tahun_lulus' => $pendidikanTahunLulus ?: null,
        'alasan_jurusan' => $alasanJurusan ?: null,
        // Section D: Value
        'arti_tanggung_jawab' => $artiTanggungJawab ?: null,
        'cerita_kesalahan' => $ceritaKesalahan ?: null,
        'langkah_target_minim_arahan' => $langkahTargetMinimArahan ?: null,
        // Section E: Adab
        'arti_adab' => $artiAdab ?: null,
        'respon_tidak_sepakat' => $responTidakSepakat ?: null,
        'cara_sampaikan_kritik' => $caraSampaikanKritik ?: null,
        'pengalaman_tidak_adil' => $pengalamanTidakAdil ?: null,
        'prioritas_pendapat_vs_sikap' => $prioritasPendapatVsSikap ?: null,
        // Section F: Motivation
        'alasan_melamar' => $alasanMelamar ?: null,
        'harapan_selain_gaji' => $harapanSelainGaji ?: null,
        'makna_bermanfaat' => $maknaBermanfaat ?: null,
        'bertahan_saat_lelah' => $bertahanSaatLelah ?: null,
        'respon_tidak_cocok_sistem' => $responTidakCocokSistem ?: null,
        // Section G: Availability
        'bersedia_probation' => $bersediaProbation ? 1 : 0,
        'bersedia_feedback' => $bersediaFeedback ? 1 : 0,
        'kapan_mulai' => $kapanMulai,
        'ekspektasi_gaji' => $ekspektasiGaji,
        // Logic Test Results
        'logic_score' => $logicResult['score'],
        'logic_correct' => $logicResult['score'],
        'logic_total' => $logicResult['total'],
        'logic_threshold' => $logicResult['threshold'],
        'logic_status' => $logicResult['status'],
        'logic_answers' => json_encode($sanitizedLogicAnswers),
        'logic_details' => json_encode($logicResult['details']),
        // Psychology Test Results
        'psychology_pattern' => $psychologyResult['workPattern'],
        'psychology_placement_recommendation' => $psychologyResult['placementRecommendation'],
        'psychology_fit_score' => $psychologyResult['fitScore'],
        'psychology_pattern_mismatch' => $psychologyResult['patternMismatch'] ? 1 : 0,
        'psychology_alternative_positions' => json_encode($psychologyResult['alternativePositions']),
        'psychology_section_a_score' => $psychologyResult['sectionScores']['section_a'] ?? 0,
        'psychology_section_b_score' => $psychologyResult['sectionScores']['section_b'] ?? 0,
        'psychology_section_c_score' => $psychologyResult['sectionScores']['section_c'] ?? 0,
        'psychology_section_d_score' => $psychologyResult['sectionScores']['section_d'] ?? 0,
        'psychology_section_e_score' => $psychologyResult['sectionScores']['section_e'] ?? 0,
        'psychology_answers' => json_encode($sanitizedPsychologyAnswers),
        'psychology_details' => json_encode($psychologyResult['details']),
        // Timer
        'timer_form' => $timerForm,
        'timer_logic' => $timerLogic,
        'timer_psychology' => $timerPsychology,
        'timer_total' => $timerTotal,
    ];
    
    $stmt->execute($params);
    $db->commit();
    
} catch (PDOException $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    error_log('Database error: ' . $e->getMessage());
    ApiResponse::serverError('Failed to save application. Please try again.');
}

// ============================================
// SEND WEBHOOK NOTIFICATION (Optional)
// ============================================

$notificationSent = false;
$webhookUrl = getenv('N8N_WEBHOOK_URL');

if (!empty($webhookUrl)) {
    $baseUrl = getenv('APP_URL') ?: 'https://recruitment.rayandra.com';
    $cvUrl = null;
    
    if (!empty($cvData['filename'])) {
        $cvUrl = $baseUrl . '/uploads/' . $cvData['filename'];
    }
    
    $notificationPayload = [
        'event' => 'new_application',
        'timestamp' => date('c'),
        'applicant' => [
            'id' => $applicantId,
            'nama' => $nama,
            'email' => $email,
            'whatsapp' => $whatsapp,
            'position' => PositionScoringMatrix::getPositionName($positionApplied),
            'positionCode' => $positionApplied,
            'positionTrack' => $positionTrack,
        ],
        'logicTest' => [
            'score' => $logicResult['score'],
            'total' => $logicResult['total'],
            'threshold' => $logicResult['threshold'],
            'status' => $logicResult['status'],
            'statusLabel' => $logicResult['statusLabel'],
        ],
        'psychologyTest' => [
            'workPattern' => $psychologyResult['workPattern'],
            'workPatternName' => $psychologyResult['workPatternName'],
            'fitScore' => $psychologyResult['fitScore'],
            'patternMismatch' => $psychologyResult['patternMismatch'],
        ],
        'overallStatus' => $overallStatus,
        'cv' => [
            'filename' => $cvData['filename'],
            'originalName' => $cvData['originalName'],
            'downloadUrl' => $cvUrl,
        ],
        'timer' => [
            'form' => $timerForm,
            'logic' => $timerLogic,
            'psychology' => $timerPsychology,
            'total' => $timerTotal,
        ],
    ];
    
    $notificationSent = sendWebhookNotification($webhookUrl, $notificationPayload);
}

// ============================================
// RETURN SUCCESS RESPONSE
// ============================================

ApiResponse::success([
    'message' => 'Application submitted successfully',
    'applicantId' => $applicantId,
    'notificationSent' => $notificationSent,
    'result' => [
        // Applicant Info
        'nama' => $nama,
        'email' => $email,
        'position' => PositionScoringMatrix::getPositionName($positionApplied),
        'positionCode' => $positionApplied,
        'positionTrack' => $positionTrack,
        
        // Overall Status
        'overallStatus' => $overallStatus['status'],
        'overallStatusLabel' => $overallStatus['label'],
        
        // Logic Test Results
        'logicTest' => [
            'score' => $logicResult['score'],
            'total' => $logicResult['total'],
            'percentage' => $logicResult['percentage'],
            'threshold' => $logicResult['threshold'],
            'status' => $logicResult['status'],
            'statusLabel' => $logicResult['statusLabel'],
            'passedThreshold' => $logicResult['passedThreshold'],
            'sectionScores' => $logicResult['sectionScores'],
        ],
        
        // Psychology Test Results
        'psychologyTest' => [
            'workPattern' => $psychologyResult['workPattern'],
            'workPatternName' => $psychologyResult['workPatternName'],
            'workPatternDescription' => $psychologyResult['workPatternDescription'],
            'fitScore' => $psychologyResult['fitScore'],
            'fitScoreLabel' => $psychologyResult['fitScoreLabel'],
            'patternMismatch' => $psychologyResult['patternMismatch'],
            'expectedPattern' => $psychologyResult['expectedPattern'],
            'placementRecommendation' => $psychologyResult['placementRecommendation'],
            'alternativePositions' => $psychologyResult['alternativePositions'],
            'sectionScores' => $psychologyResult['sectionScores'],
        ],
        
        // Timer
        'timer' => [
            'form' => $timerForm,
            'logic' => $timerLogic,
            'psychology' => $timerPsychology,
            'total' => $timerTotal,
        ],
        
        // Next Steps Info
        'nextSteps' => getNextStepsMessage($overallStatus['status']),
    ]
]);

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Generate unique applicant ID
 * Format: RC-XXX00 (RC = RayCorp, XXX = first 3 letters of name, 00 = random)
 */
function generateApplicantId(string $nama): string
{
    $nameClean = preg_replace('/[^A-Za-z]/', '', $nama);
    $namePrefix = strtoupper(substr($nameClean, 0, 3));
    $namePrefix = str_pad($namePrefix, 3, 'X');
    $random = str_pad(random_int(0, 99), 2, '0', STR_PAD_LEFT);
    return "RC-{$namePrefix}{$random}";
}

/**
 * Determine overall status based on logic test and psychology test results
 * Requirement 8.3: Status determination
 * 
 * Status Logic:
 * - Aman: Logic passed + Fit Score ≥70%
 * - Rawan: Logic passed + (Fit Score 60-69% OR pattern mismatch)
 * - Tidak Aman: Logic failed OR Fit Score <60%
 */
function determineOverallStatus(string $logicStatus, float $fitScore, bool $patternMismatch): array
{
    // If logic test failed, overall is tidak_aman
    if ($logicStatus === 'tidak_aman') {
        return [
            'status' => 'tidak_aman',
            'label' => 'Tidak Aman',
            'color' => 'red',
            'description' => 'Tidak lolos standar minimum tes logika'
        ];
    }
    
    // If fit score is below 60%, overall is tidak_aman
    if ($fitScore < 60) {
        return [
            'status' => 'tidak_aman',
            'label' => 'Tidak Aman',
            'color' => 'red',
            'description' => 'Fit Score di bawah 60% - tidak cocok dengan posisi yang dilamar'
        ];
    }
    
    // If logic is rawan, overall is rawan
    if ($logicStatus === 'rawan') {
        return [
            'status' => 'rawan',
            'label' => 'Rawan',
            'color' => 'yellow',
            'description' => 'Skor logika mendekati batas minimum'
        ];
    }
    
    // If fit score is 60-69% or pattern mismatch, overall is rawan
    if ($fitScore < 70 || $patternMismatch) {
        $desc = [];
        if ($fitScore < 70) {
            $desc[] = 'Fit Score 60-69%';
        }
        if ($patternMismatch) {
            $desc[] = 'Pola kerja tidak sesuai dengan posisi';
        }
        
        return [
            'status' => 'rawan',
            'label' => 'Rawan',
            'color' => 'yellow',
            'description' => implode(', ', $desc)
        ];
    }
    
    // All good - aman
    return [
        'status' => 'aman',
        'label' => 'Aman',
        'color' => 'green',
        'description' => 'Lolos standar minimum dan cocok dengan posisi'
    ];
}

/**
 * Get next steps message based on overall status
 */
function getNextStepsMessage(string $status): string
{
    $messages = [
        'aman' => 'Terima kasih telah menyelesaikan tes. Tim HR kami akan menghubungi Anda dalam 3-5 hari kerja untuk proses interview.',
        'rawan' => 'Terima kasih telah menyelesaikan tes. Tim HR kami akan mengevaluasi hasil Anda dan menghubungi jika ada kecocokan.',
        'tidak_aman' => 'Terima kasih telah menyelesaikan tes. Sayangnya hasil Anda belum memenuhi kriteria untuk posisi ini. Silakan coba lagi di lain waktu atau melamar posisi lain yang lebih sesuai.'
    ];
    
    return $messages[$status] ?? $messages['rawan'];
}

/**
 * Send webhook notification to n8n
 */
function sendWebhookNotification(string $url, array $payload): bool
{
    $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
    
    $ch = curl_init($url);
    if ($ch === false) return false;
    
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $jsonPayload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10
    ]);
    
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode >= 200 && $httpCode < 300;
}
