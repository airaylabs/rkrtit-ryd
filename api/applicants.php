<?php
/**
 * Applicants List API Endpoint
 * 
 * Handles GET request to retrieve applicants list:
 * - Optional status filter (LULUS/TIDAK LULUS)
 * - Returns JSON array of applicants
 * - Includes basic info and scores
 * 
 * Requirements: 7.2, 7.3
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

// Check for admin password in Authorization header or query param
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
// Get Optional Status Filter
// ============================================

$statusFilter = null;

if (isset($_GET['status'])) {
    $status = strtoupper(trim($_GET['status']));
    
    // Validate status value
    if (in_array($status, ['LULUS', 'TIDAK LULUS', 'TIDAK_LULUS'])) {
        // Normalize TIDAK_LULUS to TIDAK LULUS
        $statusFilter = str_replace('_', ' ', $status);
    }
}

// ============================================
// Fetch Applicants from Database
// ============================================

$applicant = new Applicant();
$applicants = $applicant->getAll($statusFilter);

// ============================================
// Format Response Data
// ============================================

$formattedApplicants = array_map(function($app) {
    return [
        'id' => $app['id'],
        'nama' => $app['nama'],
        'email' => $app['email'],
        'whatsapp' => $app['whatsapp'],
        'scores' => [
            'technical' => (float)$app['technical_score'],
            'psikotes' => (float)$app['psikotes_score'],
            'overall' => (float)$app['overall_score']
        ],
        'status' => $app['status'],
        'createdAt' => $app['created_at'],
        'whatsappLink' => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $app['whatsapp'])
    ];
}, $applicants);

// ============================================
// Return Success Response
// ============================================

ApiResponse::success([
    'total' => count($formattedApplicants),
    'filter' => $statusFilter,
    'applicants' => $formattedApplicants
]);
