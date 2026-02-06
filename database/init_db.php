<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// تحميل متغيرات البيئة
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$user = $_ENV['DB_USERNAME'] ?? 'root';
$pass = $_ENV['DB_PASSWORD'] ?? '';
$dbName = $_ENV['DB_DATABASE'] ?? 'marketing_ai';

try {
    // 1. الاتصال بدون تحديد قاعدة بيانات لإنشائها
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Creating database '$dbName'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database created or already exists.\n";

    $pdo->exec("USE `$dbName` "); 
    $pdo = new PDO("mysql:host=$host;dbname=$dbName", $user, $pass);
    
    // 3. قراءة وتطبيق ملف schema.sql
    $schemaFile = __DIR__ . '/schema.sql';
    if (file_exists($schemaFile)) {
        echo "Applying schema from schema.sql...\n";
        $sql = file_get_contents($schemaFile);
        $pdo->exec($sql);
        echo "Schema applied successfully.\n";
    } else {
        echo "Warning: schema.sql not found.\n";
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
