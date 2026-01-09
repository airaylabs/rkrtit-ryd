<?php
/**
 * File Uploader Class
 * 
 * Handles secure file uploads with MIME type validation,
 * size limits, and randomized filenames.
 */

require_once __DIR__ . '/InputSanitizer.php';

class FileUploader
{
    /**
     * Allowed MIME types for CV uploads
     */
    private const ALLOWED_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    /**
     * Maximum file size in bytes (5MB)
     */
    private const MAX_SIZE = 5 * 1024 * 1024;

    /**
     * Upload directory path
     */
    private const UPLOAD_DIR = __DIR__ . '/../uploads/';

    /**
     * Upload a file securely
     *
     * @param array $file $_FILES array element
     * @return array Result with success status and file info or error
     */
    public static function upload(array $file): array
    {
        // Check for upload errors
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return self::getUploadError($file['error'] ?? UPLOAD_ERR_NO_FILE);
        }

        // Validate file size
        if ($file['size'] > self::MAX_SIZE) {
            return [
                'success' => false,
                'error' => 'File too large. Maximum size is 5MB.'
            ];
        }

        // Validate MIME type server-side using finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, self::ALLOWED_TYPES, true)) {
            return [
                'success' => false,
                'error' => 'Invalid file type. Allowed types: PDF, JPG, PNG, DOC, DOCX.'
            ];
        }

        // Ensure upload directory exists
        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }

        // Generate random filename to prevent path traversal
        $extension = self::getExtensionFromMime($mimeType);
        $newFilename = bin2hex(random_bytes(16)) . '.' . $extension;
        $targetPath = self::UPLOAD_DIR . $newFilename;

        // Sanitize original filename for storage
        $originalName = InputSanitizer::sanitizeString(
            pathinfo($file['name'], PATHINFO_BASENAME)
        );

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return [
                'success' => true,
                'filename' => $newFilename,
                'originalName' => $originalName,
                'mimeType' => $mimeType,
                'size' => $file['size']
            ];
        }

        return [
            'success' => false,
            'error' => 'Failed to save uploaded file.'
        ];
    }

    /**
     * Get file extension from MIME type
     *
     * @param string $mimeType MIME type
     * @return string File extension
     */
    private static function getExtensionFromMime(string $mimeType): string
    {
        $mimeToExt = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx'
        ];

        return $mimeToExt[$mimeType] ?? 'bin';
    }

    /**
     * Get human-readable upload error message
     *
     * @param int $errorCode PHP upload error code
     * @return array Error response array
     */
    private static function getUploadError(int $errorCode): array
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit.',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit.',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by server extension.'
        ];

        return [
            'success' => false,
            'error' => $messages[$errorCode] ?? 'Unknown upload error.'
        ];
    }

    /**
     * Delete an uploaded file
     *
     * @param string $filename Filename to delete
     * @return bool True if deleted successfully
     */
    public static function delete(string $filename): bool
    {
        $filepath = self::UPLOAD_DIR . basename($filename);
        
        if (file_exists($filepath) && is_file($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }

    /**
     * Get the full path to an uploaded file
     *
     * @param string $filename Filename
     * @return string|null Full path or null if not exists
     */
    public static function getPath(string $filename): ?string
    {
        $filepath = self::UPLOAD_DIR . basename($filename);
        
        if (file_exists($filepath) && is_file($filepath)) {
            return $filepath;
        }
        
        return null;
    }
}
