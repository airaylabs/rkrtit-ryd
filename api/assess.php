<?php
/**
 * Assessment Save API Endpoint (v2.0 - Multi-Division Support)
 * 
 * Handles POST request to save HR assessment:
 * - Validates assessment input
 * - Saves HR assessment fields (6 aspek adab A-F)
 * - Updates applicant record with assessor info and timestamp
 * - Auto-recommends STOP if any red indicator detected
 * 
 * Requirements: 6.4
 */

// Set error handling
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e) {
    error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    require_once __DIR__ . '/../includes/ApiResponse.php';
    ApiResponse::serverError('Internal server error');
});

// Include required files
require_once __DIR__ . '/../includes/ApiResponse.php';
require_once __DIR__ . '/../includes/InputSanitizer.php';
require_once __DIR__ . '/../includes/Applicant.php';

// Set CORS headers for API access
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Method not allowed', 405);
}

// ============================================
// Authentication Check
// ============================================

$adminPassword = getenv('ADMIN_PASSWORD') ?: 'admin123';
$providedPassword = null;

// Check Authorization header
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    if (strpos($authHeader, 'Basic ') === 0) {
        $credentials = base64_decode(substr($authHeader, 6));
        $parts = explode(':', $credentials, 2);
        if (count($parts) === 2) {
            $providedPassword = $parts[1];
        }
    } elseif (strpos($authHeader, 'Bearer ') === 0) {
        $providedPassword = substr($authHeader, 7);
    }
}

// Verify password
$requireAuth = getenv('REQUIRE_ADMIN_AUTH') !== 'false';
if ($requireAuth && $providedPassword !== $adminPassword) {
    ApiResponse::unauthorized('Invalid admin credentials');
}

// ============================================
// Parse Request Body
// ============================================

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    ApiResponse::error('Invalid JSON input');
}

// ============================================
// Validate Required Fields
// ============================================

if (empty($data['id'])) {
    ApiResponse::validationError(['id' => 'Applicant ID is required']);
}

// Valid values for assessment fields
$validAdabValues = ['sehat', 'waspada', 'tidak_cocok', null, ''];
$validFitValues = ['selaras', 'abu_abu', 'tidak_cocok', null, ''];
$validDecisionValues = ['lanjut', 'hold', 'stop', null, ''];

// Validate adab assessment values (A-F)
$adabFields = [
    'hr_adab_a_otoritas',
    'hr_adab_b_koreksi',
    'hr_adab_c_tidak_sepakat',
    'hr_adab_d_kesadaran_diri',
    'hr_adab_e_kecocokan_nilai',
    'hr_adab_f1_orientasi_niat',
    'hr_adab_f2_respon_lelah',
    'hr_adab_f3_keikhlasan',
    'hr_adab_f4_spiritual'
];

$errors = [];
foreach ($adabFields as $field) {
    if (isset($data[$field]) && !empty($data[$field]) && !in_array($data[$field], $validAdabValues)) {
        $errors[$field] = "Invalid value. Must be one of: sehat, waspada, tidak_cocok";
    }
}

// Validate fit values
if (isset($data['hr_value_fit']) && !empty($data['hr_value_fit']) && !in_array($data['hr_value_fit'], $validFitValues)) {
    $errors['hr_value_fit'] = "Invalid value. Must be one of: selaras, abu_abu, tidak_cocok";
}

if (isset($data['hr_adab_fit']) && !empty($data['hr_adab_fit']) && !in_array($data['hr_adab_fit'], $validFitValues)) {
    $errors['hr_adab_fit'] = "Invalid value. Must be one of: selaras, abu_abu, tidak_cocok";
}

// Validate decision
if (isset($data['hr_decision']) && !empty($data['hr_decision']) && !in_array($data['hr_decision'], $validDecisionValues)) {
    $errors['hr_decision'] = "Invalid value. Must be one of: lanjut, hold, stop";
}

if (!empty($errors)) {
    ApiResponse::validationError($errors);
}

// ============================================
// Check for Red Indicators (Auto-STOP)
// ============================================

$hasRedIndicator = false;
$redIndicators = [];

foreach ($adabFields as $field) {
    if (isset($data[$field]) && $data[$field] === 'tidak_cocok') {
        $hasRedIndicator = true;
        $redIndicators[] = $field;
    }
}

if (isset($data['hr_value_fit']) && $data['hr_value_fit'] === 'tidak_cocok') {
    $hasRedIndicator = true;
    $redIndicators[] = 'hr_value_fit';
}

if (isset($data['hr_adab_fit']) && $data['hr_adab_fit'] === 'tidak_cocok') {
    $hasRedIndicator = true;
    $redIndicators[] = 'hr_adab_fit';
}

// ============================================
// Verify Applicant Exists
// ============================================

$applicant = new Applicant();
$existingApplicant = $applicant->getById($data['id']);

if (!$existingApplicant) {
    ApiResponse::notFound('Applicant not found');
}

// ============================================
// Prepare Assessment Data
// ============================================

$assessment = [
    // Adab assessment (6 aspek A-F)
    'hr_adab_a_otoritas' => $data['hr_adab_a_otoritas'] ?? null,
    'hr_adab_b_koreksi' => $data['hr_adab_b_koreksi'] ?? null,
    'hr_adab_c_tidak_sepakat' => $data['hr_adab_c_tidak_sepakat'] ?? null,
    'hr_adab_d_kesadaran_diri' => $data['hr_adab_d_kesadaran_diri'] ?? null,
    'hr_adab_e_kecocokan_nilai' => $data['hr_adab_e_kecocokan_nilai'] ?? null,
    'hr_adab_f1_orientasi_niat' => $data['hr_adab_f1_orientasi_niat'] ?? null,
    'hr_adab_f2_respon_lelah' => $data['hr_adab_f2_respon_lelah'] ?? null,
    'hr_adab_f3_keikhlasan' => $data['hr_adab_f3_keikhlasan'] ?? null,
    'hr_adab_f4_spiritual' => $data['hr_adab_f4_spiritual'] ?? null,
    
    // Summary assessment
    'hr_value_fit' => $data['hr_value_fit'] ?? null,
    'hr_adab_fit' => $data['hr_adab_fit'] ?? null,
    'hr_risk_note' => isset($data['hr_risk_note']) ? InputSanitizer::sanitizeText($data['hr_risk_note']) : null,
    'hr_decision' => $data['hr_decision'] ?? null,
    'hr_notes' => isset($data['hr_notes']) ? InputSanitizer::sanitizeText($data['hr_notes']) : null,
    
    // Assessor info
    'hr_assessed_by' => isset($data['hr_assessed_by']) ? InputSanitizer::sanitizeText($data['hr_assessed_by']) : 'Admin'
];

// ============================================
// Save Assessment
// ============================================

$result = $applicant->updateAssessment($data['id'], $assessment);

if (!$result['success']) {
    ApiResponse::serverError($result['error'] ?? 'Failed to save assessment');
}

// ============================================
// Return Success Response
// ============================================

$response = [
    'id' => $data['id'],
    'assessment_saved' => true,
    'assessed_at' => date('Y-m-d H:i:s')
];

// Include red indicator warning if applicable
if ($hasRedIndicator) {
    $response['warning'] = [
        'has_red_indicator' => true,
        'red_indicators' => $redIndicators,
        'recommendation' => 'STOP - Terdapat indikator merah pada penilaian adab/value. Skill tinggi tidak menebus adab buruk.'
    ];
}

ApiResponse::success($response, 'Assessment saved successfully');
