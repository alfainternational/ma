<?php

namespace App\Config;

use PDO;
use PDOException;

/**
 * Class Database
 * مسؤول عن الاتصال بقاعدة بيانات MySQL.
 */
class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
                $db   = $_ENV['DB_DATABASE'] ?? 'marketing_ai';
                $user = $_ENV['DB_USERNAME'] ?? 'root';
                $pass = $_ENV['DB_PASSWORD'] ?? '';
                $port = $_ENV['DB_PORT'] ?? '3306';
                $charset = 'utf8mb4';

                $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=$charset";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
        return self::$instance;
    }
}
