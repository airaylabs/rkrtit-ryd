<?php
/**
 * Notification API Endpoint
 * 
 * Sends notification to n8n webhook after successful submission:
 * - POST to n8n webhook URL
 * - Includes applicant summary and timer data
 * - Handles webhook failure gracefully (log but don't block)
 * 
 * Requirements: 8.1, 8.2, 8.3, 8.4
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

// Set CORS headers for API access
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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
// Get Input Data
// ============================================

$rawInput = file_get_contents('php://input');
$inputData = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    ApiResponse::error('Invalid JSON input');
}

// ============================================
// Validate Required Fields
// ============================================

$requiredFields = ['applicantId', 'nama', 'email', 'status'];
$validationErrors = InputSanitizer::validateRequired($inputData, $requiredFields);

if (!empty($validationErrors)) {
    ApiResponse::validationError($validationErrors);
}

// ============================================
// Get n8n Webhook URL from Environment
// ============================================

// Load environment variables if .env file exists
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
}

$webhookUrl = getenv('N8N_WEBHOOK_URL');

if (empty($webhookUrl)) {
    // Log warning but don't fail - webhook is optional
    error_log('N8N_WEBHOOK_URL not configured. Notification skipped.');
    ApiResponse::success([
        'message' => 'Notification skipped - webhook not configured',
        'sent' => false
    ]);
}

// ============================================
// Prepare Notification Payload
// ============================================

$payload = [
    'event' => 'new_application',
    'timestamp' => date('c'),
    'applicant' => [
        'id' => InputSanitizer::sanitizeString($inputData['applicantId']),
        'nama' => InputSanitizer::sanitizeString($inputData['nama']),
        'email' => InputSanitizer::validateEmail($inputData['email']),
        'whatsapp' => isset($inputData['whatsapp']) 
            ? InputSanitizer::validatePhone($inputData['whatsapp']) 
            : null,
        'status' => InputSanitizer::sanitizeString($inputData['status'])
    ],
    'scores' => [
        'overall' => (float)($inputData['overallScore'] ?? 0),
        'technical' => (float)($inputData['technicalScore'] ?? 0),
        'psikotes' => (float)($inputData['psikotesScore'] ?? 0)
    ],
    'timer' => [
        'personal' => (int)($inputData['timer']['personal'] ?? 0),
        'technical' => (int)($inputData['timer']['technical'] ?? 0),
        'psikotes' => (int)($inputData['timer']['psikotes'] ?? 0),
        'total' => (int)($inputData['timer']['total'] ?? 0)
    ]
];

// ============================================
// Send Notification to n8n Webhook
// ============================================

$result = sendWebhookNotification($webhookUrl, $payload);

if ($result['success']) {
    ApiResponse::success([
        'message' => 'Notification sent successfully',
        'sent' => true,
        'webhookResponse' => $result['response'] ?? null
    ]);
} else {
    // Log error but return success - webhook failure shouldn't block the process
    error_log('Webhook notification failed: ' . ($result['error'] ?? 'Unknown error'));
    
    ApiResponse::success([
        'message' => 'Notification failed but application was saved',
        'sent' => false,
        'error' => $result['error'] ?? 'Unknown error'
    ]);
}

/**
 * Send POST request to webhook URL
 * 
 * @param string $url Webhook URL
 * @param array $payload Data to send
 * @return array Result with success status
 */
function sendWebhookNotification(string $url, array $payload): array
{
    $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
    
    // Use cURL for HTTP request
    $ch = curl_init($url);
    
    if ($ch === false) {
        return [
            'success' => false,
            'error' => 'Failed to initialize cURL'
        ];
    }
    
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $jsonPayload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonPayload)
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10, // 10 second timeout
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => 'cURL error: ' . $error
        ];
    }
    
    // Consider 2xx status codes as success
    if ($httpCode >= 200 && $httpCode < 300) {
        return [
            'success' => true,
            'response' => $response,
            'httpCode' => $httpCode
        ];
    }
    
    return [
        'success' => false,
        'error' => "HTTP error: {$httpCode}",
        'response' => $response
    ];
}
