<?php
/**
 * Applicants List API Endpoint (v2.0 - Multi-Division Support)
 * 
 * Handles GET request to retrieve applicants list:
 * - Optional position filter
 * - Optional logic_status filter (aman/rawan/tidak_aman)
 * - Optional hr_decision filter (lanjut/hold/stop/pending)
 * - Optional search by nama/email
 * - Returns JSON array of applicants with new fields
 * 
 * Requirements: 6.1
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
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    ApiResponse::error('Method not allowed', 405);
}

// ============================================
// Optional: Basic Authentication Check
// ============================================

$adminPassword = getenv('ADMIN_PASSWORD') ?: 'admin123';
$providedPassword = null;

// Check Authorization header (Basic auth)
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

// Check query parameter as fallback
if ($providedPassword === null && isset($_GET['password'])) {
    $providedPassword = $_GET['password'];
}

// Verify password if admin authentication is required
$requireAuth = getenv('REQUIRE_ADMIN_AUTH') !== 'false';
if ($requireAuth && $providedPassword !== $adminPassword) {
    ApiResponse::unauthorized('Invalid admin credentials');
}

// ============================================
// Build Filters from Query Parameters
// ============================================

$filters = [];

// Position filter
if (isset($_GET['position']) && !empty($_GET['position'])) {
    $filters['position_applied'] = trim($_GET['position']);
}

// Logic status filter (aman/rawan/tidak_aman)
if (isset($_GET['logic_status']) && !empty($_GET['logic_status'])) {
    $status = strtolower(trim($_GET['logic_status']));
    if (in_array($status, ['aman', 'rawan', 'tidak_aman'])) {
        $filters['logic_status'] = $status;
    }
}

// HR decision filter (lanjut/hold/stop/pending)
if (isset($_GET['hr_decision']) && !empty($_GET['hr_decision'])) {
    $decision = strtolower(trim($_GET['hr_decision']));
    if (in_array($decision, ['lanjut', 'hold', 'stop', 'pending'])) {
        $filters['hr_decision'] = $decision;
    }
}

// Search filter (nama or email)
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = trim($_GET['search']);
}

// Legacy status filter for backward compatibility
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status = strtoupper(trim($_GET['status']));
    if (in_array($status, ['LULUS', 'TIDAK LULUS', 'TIDAK_LULUS', 'REVIEW'])) {
        $filters['status'] = str_replace('_', ' ', $status);
    }
}

// ============================================
// Fetch Applicants from Database
// ============================================

$applicant = new Applicant();

// Use new filtered method if any v2 filters are present
if (!empty($filters['position_applied']) || !empty($filters['logic_status']) || 
    !empty($filters['hr_decision']) || !empty($filters['search'])) {
    $applicants = $applicant->getAllFiltered($filters);
} else {
    // Fallback to legacy method for backward compatibility
    $statusFilter = $filters['status'] ?? null;
    $applicants = $applicant->getAll($statusFilter);
}

// ============================================
// Format Response Data
// ============================================

$formattedApplicants = array_map(function($app) {
    return [
        'id' => $app['id'],
        'nama' => $app['nama'],
        'email' => $app['email'],
        'whatsapp' => $app['whatsapp'],
        
        // Position info (v2.0)
        'position_applied' => $app['position_applied'] ?? null,
        'position_track' => $app['position_track'] ?? null,
        
        // Logic test results (v2.0)
        'logic_score' => (int)($app['logic_score'] ?? $app['technical_score'] ?? 0),
        'logic_correct' => (int)($app['logic_correct'] ?? $app['technical_correct'] ?? 0),
        'logic_total' => (int)($app['logic_total'] ?? $app['technical_total'] ?? 25),
        'logic_status' => $app['logic_status'] ?? null,
        
        // Psychology test results (v2.0)
        'psychology_pattern' => $app['psychology_pattern'] ?? null,
        'psychology_fit_score' => isset($app['psychology_fit_score']) ? (float)$app['psychology_fit_score'] : null,
        'psychology_pattern_mismatch' => isset($app['psychology_pattern_mismatch']) ? (bool)$app['psychology_pattern_mismatch'] : null,
        
        // HR assessment (v2.0)
        'hr_decision' => $app['hr_decision'] ?? null,
        'hr_value_fit' => $app['hr_value_fit'] ?? null,
        'hr_adab_fit' => $app['hr_adab_fit'] ?? null,
        'hr_assessed_by' => $app['hr_assessed_by'] ?? null,
        'hr_assessed_at' => $app['hr_assessed_at'] ?? null,
        
        // Interview & Probation (v2.0)
        'probation_status' => $app['probation_status'] ?? null,
        'final_decision' => $app['final_decision'] ?? null,
        
        // Legacy scores (backward compatibility)
        'scores' => [
            'technical' => (float)($app['technical_score'] ?? $app['logic_score'] ?? 0),
            'psikotes' => (float)($app['psikotes_score'] ?? 0),
            'overall' => (float)($app['overall_score'] ?? 0)
        ],
        'status' => $app['status'] ?? null,
        
        // Timestamps
        'createdAt' => $app['created_at'],
        'updatedAt' => $app['updated_at'] ?? null,
        
        // Utility
        'whatsappLink' => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $app['whatsapp'] ?? '')
    ];
}, $applicants);

// ============================================
// Get Statistics
// ============================================

$stats = $applicant->getStatsV2();

// ============================================
// Return Success Response
// ============================================

ApiResponse::success([
    'total' => count($formattedApplicants),
    'filters' => $filters,
    'stats' => $stats,
    'applicants' => $formattedApplicants
]);
