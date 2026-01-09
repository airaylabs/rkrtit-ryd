<?php
/**
 * Password Hash Generator
 * 
 * CARA PAKAI:
 * 1. Buka: https://recruitment.rayandra.com/generate-hash.php?pass=PASSWORD_KAMU
 * 2. Copy hash yang muncul
 * 3. Paste ke file .env di ADMIN_PASSWORD_HASH=
 * 4. HAPUS FILE INI SETELAH SELESAI!
 */

if (!isset($_GET['pass']) || empty($_GET['pass'])) {
    echo "<h2>Password Hash Generator</h2>";
    echo "<p>Cara pakai: <code>?pass=PASSWORD_KAMU</code></p>";
    echo "<p>Contoh: <code>generate-hash.php?pass=admin123</code></p>";
    exit;
}

$password = $_GET['pass'];
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Password Hash Generator</h2>";
echo "<p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>";
echo "<p><strong>Hash:</strong></p>";
echo "<textarea style='width:100%;height:100px;font-family:monospace;'>" . $hash . "</textarea>";
echo "<p style='color:red;'><strong>⚠️ HAPUS FILE INI SETELAH SELESAI SETUP!</strong></p>";
