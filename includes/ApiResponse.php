<?php
/**
 * API Response Helper Class
 * 
 * Provides standardized JSON response methods for API endpoints.
 */
class ApiResponse
{
    /**
     * Send a JSON response with proper headers
     *
     * @param array $data Response data
     * @param int $status HTTP status code
     * @return void
     */
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send a success response
     *
     * @param array $data Additional data to include
     * @param string|null $message Optional success message
     * @return void
     */
    public static function success(array $data = [], ?string $message = null): void
    {
        $response = ['success' => true];
        
        if ($message !== null) {
            $response['message'] = $message;
        }
        
        self::json(array_merge($response, $data));
    }

    /**
     * Send an error response
     *
     * @param string $message Error message
     * @param int $status HTTP status code
     * @param array $details Optional error details
     * @return void
     */
    public static function error(string $message, int $status = 400, array $details = []): void
    {
        $response = [
            'success' => false,
            'error' => $message
        ];
        
        if (!empty($details)) {
            $response['details'] = $details;
        }
        
        self::json($response, $status);
    }

    /**
     * Send a validation error response
     *
     * @param array $errors Array of validation error messages
     * @return void
     */
    public static function validationError(array $errors): void
    {
        self::json([
            'success' => false,
            'error' => 'Validation failed',
            'details' => $errors
        ], 400);
    }

    /**
     * Send a not found response
     *
     * @param string $message Optional message
     * @return void
     */
    public static function notFound(string $message = 'Resource not found'): void
    {
        self::error($message, 404);
    }

    /**
     * Send an unauthorized response
     *
     * @param string $message Optional message
     * @return void
     */
    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, 401);
    }

    /**
     * Send a server error response
     *
     * @param string $message Optional message
     * @return void
     */
    public static function serverError(string $message = 'Internal server error'): void
    {
        self::error($message, 500);
    }
}
