<?php
/**
 * Input Sanitizer Class
 * 
 * Handles sanitization and validation of all user inputs
 * to prevent XSS attacks and ensure data integrity.
 */
class InputSanitizer
{
    /**
     * Sanitize string input with htmlspecialchars
     * Prevents XSS attacks by encoding special characters
     *
     * @param string $input Raw input string
     * @return string Sanitized string
     */
    public static function sanitizeString(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate and sanitize email address
     *
     * @param string $email Raw email input
     * @return string|null Validated email or null if invalid
     */
    public static function validateEmail(string $email): ?string
    {
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /**
     * Validate phone number (digits only, optional leading +)
     *
     * @param string $phone Raw phone input
     * @return string|null Validated phone or null if invalid
     */
    public static function validatePhone(string $phone): ?string
    {
        // Remove all non-digit characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        // Validate format: optional + followed by 10-15 digits
        return preg_match('/^\+?[0-9]{10,15}$/', $phone) ? $phone : null;
    }

    /**
     * Recursively sanitize array of inputs
     *
     * @param array $data Array of inputs to sanitize
     * @return array Sanitized array
     */
    public static function sanitizeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $sanitizedKey = is_string($key) ? self::sanitizeString($key) : $key;
            
            if (is_string($value)) {
                $sanitized[$sanitizedKey] = self::sanitizeString($value);
            } elseif (is_array($value)) {
                $sanitized[$sanitizedKey] = self::sanitizeArray($value);
            } else {
                $sanitized[$sanitizedKey] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Validate that required fields are present and not empty
     *
     * @param array $data Data array to check
     * @param array $fields List of required field names
     * @return array Array of error messages (empty if all valid)
     */
    public static function validateRequired(array $data, array $fields): array
    {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $errors[] = "Field '{$field}' is required";
            }
        }
        return $errors;
    }

    /**
     * Sanitize integer input
     *
     * @param mixed $input Raw input
     * @return int|null Validated integer or null if invalid
     */
    public static function sanitizeInt($input): ?int
    {
        $filtered = filter_var($input, FILTER_VALIDATE_INT);
        return $filtered !== false ? $filtered : null;
    }

    /**
     * Sanitize float input
     *
     * @param mixed $input Raw input
     * @return float|null Validated float or null if invalid
     */
    public static function sanitizeFloat($input): ?float
    {
        $filtered = filter_var($input, FILTER_VALIDATE_FLOAT);
        return $filtered !== false ? $filtered : null;
    }
}
