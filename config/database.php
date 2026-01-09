<?php
/**
 * Database Configuration and Connection Class
 * 
 * Implements PDO singleton pattern with secure configuration:
 * - ATTR_ERRMODE set to exception for proper error handling
 * - ATTR_EMULATE_PREPARES set to false for true prepared statements
 * - Credentials loaded from environment variables
 */

class Database {
    private static ?Database $instance = null;
    private PDO $pdo;
    
    /**
     * Private constructor - loads credentials from environment and establishes connection
     * @throws PDOException if connection fails
     */
    private function __construct() {
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
        
        // Get credentials from environment variables
        $host = getenv('DB_HOST') ?: 'localhost';
        $dbname = getenv('DB_NAME') ?: 'recruitment';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        
        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        
        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            // Log the actual error for debugging
            error_log('Database connection failed: ' . $e->getMessage());
            // Throw generic error to user
            throw new PDOException('Database connection failed. Please try again later.');
        }
    }
    
    /**
     * Get PDO instance (singleton pattern)
     * @return PDO
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserializing of the instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
