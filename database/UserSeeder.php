<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$db = Database::getConnection();

echo "Creating test user...\n";

$userId = \Ramsey\Uuid\Uuid::uuid4()->toString();
$email = 'admin@marketing-ai.com';
$password = password_hash('admin123', PASSWORD_BCRYPT);

$stmt = $db->prepare("INSERT INTO users (id, email, password_hash, full_name, role, status) VALUES (?, ?, ?, ?, 'admin', 'active')");

try {
    $stmt->execute([$userId, $email, $password, 'المسؤول التجريبي']);
    echo "User created: $email / admin123\n";

    // إنشاء شركة تجريبية مرتبطة بالمستخدم
    $companyId = \Ramsey\Uuid\Uuid::uuid4()->toString();
    $stmt = $db->prepare("INSERT INTO companies (id, user_id, name, sector) VALUES (?, ?, 'شركة آفاق المستقبل', 'retail')");
    $stmt->execute([$companyId, $userId]);
    echo "Company created: شركة آفاق المستقبل (Retail)\n";

} catch (Exception $e) {
    echo "User might already exist: " . $e->getMessage() . "\n";
}
