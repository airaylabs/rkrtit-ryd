<?php
/**
 * Database Setup Script
 * 
 * Jalankan script ini untuk membuat database dan tabel
 * 
 * Usage: php setup_database.php
 * Atau buka di browser: http://localhost/recruitment-php/setup_database.php
 */

echo "===========================================\n";
echo "  RECRUITMENT SYSTEM - DATABASE SETUP\n";
echo "===========================================\n\n";

// Load environment variables
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            putenv("$key=$value");
        }
    }
}

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME') ?: 'recruitment';

echo "Configuration:\n";
echo "  Host: $host\n";
echo "  User: $user\n";
echo "  Database: $dbname\n\n";

try {
    // Connect without database first
    echo "1. Connecting to MySQL server...\n";
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "   ✅ Connected successfully!\n\n";

    // Create database
    echo "2. Creating database '$dbname'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "   ✅ Database created/exists!\n\n";

    // Select database
    $pdo->exec("USE `$dbname`");

    // Read and execute schema
    echo "3. Creating tables...\n";
    $schemaFile = __DIR__ . '/database/schema_v2.sql';
    
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }

    $sql = file_get_contents($schemaFile);
    
    // Remove CREATE DATABASE and USE statements (already done above)
    $sql = preg_replace('/CREATE DATABASE.*?;/is', '', $sql);
    $sql = preg_replace('/USE\s+\w+;/i', '', $sql);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $tableCount = 0;
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        // Skip comments
        if (strpos($statement, '--') === 0) continue;
        
        try {
            $pdo->exec($statement);
            
            // Count tables created
            if (stripos($statement, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?`?(\w+)`?/i', $statement, $matches);
                if (isset($matches[1])) {
                    echo "   ✅ Table '{$matches[1]}' created\n";
                    $tableCount++;
                }
            }
            
            // Count inserts
            if (stripos($statement, 'INSERT INTO') !== false) {
                preg_match('/INSERT INTO\s+`?(\w+)`?/i', $statement, $matches);
                if (isset($matches[1])) {
                    echo "   ✅ Data inserted into '{$matches[1]}'\n";
                }
            }
        } catch (PDOException $e) {
            // Ignore "table already exists" errors
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "   ⚠️ Warning: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n";

    // Verify tables
    echo "4. Verifying tables...\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredTables = ['applicants', 'admin_users', 'position_scoring_matrix'];
    $allFound = true;
    
    foreach ($requiredTables as $table) {
        if (in_array($table, $tables)) {
            echo "   ✅ Table '$table' exists\n";
        } else {
            echo "   ❌ Table '$table' NOT FOUND\n";
            $allFound = false;
        }
    }
    
    echo "\n";

    // Check admin user
    echo "5. Checking admin user...\n";
    $stmt = $pdo->query("SELECT username, nama, role FROM admin_users LIMIT 1");
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "   ✅ Admin user exists: {$admin['username']} ({$admin['nama']})\n";
    } else {
        echo "   ⚠️ No admin user found. Creating default...\n";
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO admin_users (username, password_hash, nama, role) VALUES ('admin', '$hash', 'Administrator', 'admin')");
        echo "   ✅ Default admin created (username: admin, password: admin123)\n";
    }
    
    echo "\n";

    // Check position matrix
    echo "6. Checking position scoring matrix...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM position_scoring_matrix");
    $count = $stmt->fetch()['count'];
    echo "   ✅ $count positions configured\n";
    
    echo "\n";
    echo "===========================================\n";
    echo "  ✅ DATABASE SETUP COMPLETE!\n";
    echo "===========================================\n\n";
    
    echo "Next steps:\n";
    echo "1. Start PHP server: php -S localhost:8000\n";
    echo "2. Open browser: http://localhost:8000\n";
    echo "3. Admin panel: http://localhost:8000/admin/\n";
    echo "   - Username: admin\n";
    echo "   - Password: admin123\n\n";

} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
    
    echo "Troubleshooting:\n";
    echo "1. Pastikan MySQL sudah running\n";
    echo "2. Cek kredensial di file .env\n";
    echo "3. Jika pakai XAMPP, pastikan MySQL service sudah start\n";
    echo "4. Jika pakai Laragon, pastikan MySQL sudah running\n\n";
    
    exit(1);
}
