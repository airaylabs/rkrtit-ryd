<?php
/**
 * Submission API Endpoint - FINAL OPTIMIZED VERSION
 * 
 * Technical: 5 soal (70%)
 * Psikotes: 3 skenario (30%)
 * 
 * Scoring: 0-10 scale
 * - Bagus: 8-10 (LULUS)
 * - Butuh Review: 5-7 (REVIEW)
 * - Belum Lulus: <5 (TIDAK LULUS)
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
require_once __DIR__ . '/../includes/Applicant.php';

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
    
    $jsonFields = ['technicalAnswers', 'psikotesAnswers', 'timer'];
    foreach ($jsonFields as $field) {
        if (isset($inputData[$field]) && is_string($inputData[$field])) {
            $decoded = json_decode($inputData[$field], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $inputData[$field] = $decoded;
            }
        }
    }
}

// Validate required fields
$requiredFields = ['nama', 'email', 'whatsapp'];
$validationErrors = InputSanitizer::validateRequired($inputData, $requiredFields);

if (!empty($validationErrors)) {
    ApiResponse::validationError($validationErrors);
}

// Sanitize personal data
$nama = InputSanitizer::sanitizeString($inputData['nama']);
$email = InputSanitizer::validateEmail($inputData['email']);
$whatsapp = InputSanitizer::validatePhone($inputData['whatsapp']);

if ($email === null) {
    ApiResponse::error('Invalid email format');
}

if ($whatsapp === null) {
    ApiResponse::error('Invalid phone number format');
}

// Process CV upload
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
// CALCULATE TECHNICAL SCORE (5 soal, 70%)
// ============================================

$technicalAnswers = $inputData['technicalAnswers'] ?? [];

$sanitizedTechnicalAnswers = [];
foreach ($technicalAnswers as $questionId => $answer) {
    $sanitizedKey = InputSanitizer::sanitizeString($questionId);
    $sanitizedAnswer = strtoupper(trim($answer));
    if (preg_match('/^[A-E]$/', $sanitizedAnswer)) {
        $sanitizedTechnicalAnswers[$sanitizedKey] = $sanitizedAnswer;
    }
}

$technicalScorer = new TechnicalScorer();
$technicalResult = $technicalScorer->calculate($sanitizedTechnicalAnswers);

// ============================================
// CALCULATE PSIKOTES SCORE (3 skenario, 30%)
// ============================================

$psikotesAnswers = $inputData['psikotesAnswers'] ?? [];

$sanitizedPsikotesAnswers = [];
foreach ($psikotesAnswers as $scenarioId => $answer) {
    $sanitizedKey = InputSanitizer::sanitizeString($scenarioId);
    $sanitizedAnswer = strtoupper(trim($answer));
    if (preg_match('/^[A-E]$/', $sanitizedAnswer)) {
        $sanitizedPsikotesAnswers[$sanitizedKey] = $sanitizedAnswer;
    }
}

$psikotesScorer = new PsikotesScorer();
$psikotesResult = $psikotesScorer->calculate($sanitizedPsikotesAnswers);

// ============================================
// CALCULATE OVERALL SCORE
// ============================================

$overallResult = OverallScorer::calculate(
    $technicalResult['score'],
    $psikotesResult['score']
);

// ============================================
// PROCESS TIMER DATA
// ============================================

$timerData = $inputData['timer'] ?? [];
$timerPersonal = InputSanitizer::sanitizeInt($timerData['personal'] ?? 0) ?? 0;
$timerTechnical = InputSanitizer::sanitizeInt($timerData['technical'] ?? 0) ?? 0;
$timerPsikotes = InputSanitizer::sanitizeInt($timerData['psikotes'] ?? 0) ?? 0;
$timerTotal = $timerPersonal + $timerTechnical + $timerPsikotes;

// ============================================
// SAVE TO DATABASE
// ============================================

$applicantData = [
    'nama' => $nama,
    'email' => $email,
    'whatsapp' => $whatsapp,
    'cv_filename' => $cvData['filename'],
    'cv_original_name' => $cvData['originalName'],
    'cv_mime_type' => $cvData['mimeType'],
    
    // Technical scores
    'technical_score' => $technicalResult['score'],
    'technical_correct' => $technicalResult['correctCount'],
    'technical_total' => $technicalResult['totalQuestions'],
    'technical_answers' => $sanitizedTechnicalAnswers,
    'technical_details' => $technicalResult['details'],
    
    // Psikotes scores
    'psikotes_score' => $psikotesResult['score'],
    'psikotes_categories' => $psikotesResult['categories'],
    'psikotes_answers' => $sanitizedPsikotesAnswers,
    'psikotes_details' => $psikotesResult['details'],
    
    // Overall
    'overall_score' => $overallResult['overallScore'],
    'status' => $overallResult['status'],
    'status_label' => $overallResult['statusLabel'],
    'recommendation' => $overallResult['recommendation'],
    
    // Timer
    'timer_personal' => $timerPersonal,
    'timer_technical' => $timerTechnical,
    'timer_psikotes' => $timerPsikotes,
    'timer_total' => $timerTotal
];

$applicant = new Applicant();
$saveResult = $applicant->create($applicantData);

if (!$saveResult['success']) {
    ApiResponse::serverError('Failed to save application. Please try again.');
}

// ============================================
// SEND WEBHOOK NOTIFICATION
// ============================================

$notificationSent = false;
$webhookUrl = getenv('N8N_WEBHOOK_URL');

if (!empty($webhookUrl)) {
    $notificationPayload = [
        'event' => 'new_application',
        'timestamp' => date('c'),
        'applicant' => [
            'id' => $saveResult['id'],
            'nama' => $nama,
            'email' => $email,
            'whatsapp' => $whatsapp,
            'status' => $overallResult['status'],
            'statusLabel' => $overallResult['statusLabel']
        ],
        'scores' => [
            'overall' => $overallResult['overallScore'],
            'technical' => $technicalResult['score'],
            'psikotes' => $psikotesResult['score']
        ],
        'timer' => [
            'personal' => $timerPersonal,
            'technical' => $timerTechnical,
            'psikotes' => $timerPsikotes,
            'total' => $timerTotal
        ],
        'details' => [
            'technicalCorrect' => $technicalResult['correctCount'],
            'technicalTotal' => $technicalResult['totalQuestions'],
            'recommendation' => $overallResult['recommendation'],
            'technicalContribution' => $overallResult['technicalContribution'],
            'psikotesContribution' => $overallResult['psikotesContribution']
        ]
    ];
    
    $notificationSent = sendWebhookNotification($webhookUrl, $notificationPayload);
}

// ============================================
// RETURN SUCCESS RESPONSE
// ============================================

ApiResponse::success([
    'message' => 'Application submitted successfully',
    'applicantId' => $saveResult['id'],
    'notificationSent' => $notificationSent,
    'result' => [
        'nama' => $nama,
        'email' => $email,
        'status' => $overallResult['status'],
        'statusLabel' => $overallResult['statusLabel'],
        'recommendation' => $overallResult['recommendation'],
        'overallScore' => $overallResult['overallScore'],
        
        // Technical breakdown
        'technicalScore' => $technicalResult['score'],
        'technicalStatus' => $technicalResult['status'],
        'technicalCorrect' => $technicalResult['correctCount'],
        'technicalTotal' => $technicalResult['totalQuestions'],
        
        // Psikotes breakdown
        'psikotesScore' => $psikotesResult['score'],
        'psikotesStatus' => $psikotesResult['status'],
        'psikotesCategories' => $psikotesResult['categories'],
        'psikotesFeedback' => $psikotesResult['feedback'],
        
        // Contributions
        'technicalContribution' => $overallResult['technicalContribution'],
        'psikotesContribution' => $overallResult['psikotesContribution'],
        
        // Timer
        'timer' => [
            'personal' => $timerPersonal,
            'technical' => $timerTechnical,
            'psikotes' => $timerPsikotes,
            'total' => $timerTotal
        ],
        
        // Thresholds for reference
        'thresholds' => $overallResult['thresholds'],
        'weights' => $overallResult['weights']
    ]
]);

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
