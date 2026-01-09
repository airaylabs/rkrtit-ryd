<?php
/**
 * Core Functions Include File
 * 
 * Loads all helper classes for the recruitment system.
 */

require_once __DIR__ . '/InputSanitizer.php';
require_once __DIR__ . '/FileUploader.php';
require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/questions.php';
require_once __DIR__ . '/scoring.php';

/**
 * Global error handler for API endpoints
 * Converts errors to exceptions for consistent handling
 */
function setupErrorHandling(): void
{
    set_error_handler(function ($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    set_exception_handler(function ($e) {
        error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        ApiResponse::serverError('Internal server error');
    });
}

/**
 * Get JSON input from request body
 *
 * @return array Decoded JSON data
 */
function getJsonInput(): array
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        ApiResponse::error('Invalid JSON input', 400);
    }
    
    return $data ?? [];
}

/**
 * Generate UUID v4
 *
 * @return string UUID string
 */
function generateUuid(): string
{
    $data = random_bytes(16);
    
    // Set version to 0100 (UUID v4)
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * Check if request method matches expected
 *
 * @param string $method Expected HTTP method
 * @return void
 */
function requireMethod(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
        ApiResponse::error('Method not allowed', 405);
    }
}

/**
 * Load environment variables from .env file
 *
 * @param string $path Path to .env file
 * @return void
 */
function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse key=value
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}
