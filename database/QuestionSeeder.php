<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use Dotenv\Dotenv;

// تحميل متغيرات البيئة
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

/**
 * QuestionSeeder
 * يقوم بقراءة وتحميل بنك الأسئلة من ملف JSON إلى قاعدة البيانات.
 */
class QuestionSeeder {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function run() {
        $jsonFile = __DIR__ . '/../marketing_ai_question_bank.txt';
        if (!file_exists($jsonFile)) {
            die("Error: marketing_ai_question_bank.txt not found.\n");
        }

        $data = json_decode(file_get_contents($jsonFile), true);
        if (!$data || !isset($data['question_bank']['questions'])) {
            die("Error: Invalid JSON format.\n");
        }

        $questions = $data['question_bank']['questions'];
        echo "Found " . count($questions) . " questions. Starting import...\n";

        $stmt = $this->db->prepare("
            INSERT INTO questions (
                id, category, subcategory, question_ar, question_en, 
                question_type, required, priority, display_order, 
                help_text_ar, options, metadata
            ) VALUES (
                :id, :category, :subcategory, :question_ar, :question_en, 
                :type, :required, :priority, :order, 
                :help, :options, :metadata
            ) ON DUPLICATE KEY UPDATE 
                question_ar = VALUES(question_ar), 
                options = VALUES(options)
        ");

        foreach ($questions as $q) {
            $stmt->execute([
                ':id'          => $q['id'],
                ':category'    => $q['category'],
                ':subcategory' => $q['subcategory'] ?? null,
                ':question_ar' => $q['question_ar'],
                ':question_en' => $q['question_en'],
                ':type'        => $q['type'],
                ':required'    => $q['required'] ? 1 : 0,
                ':priority'    => $q['priority'] ?? 'medium',
                ':order'       => $q['order'] ?? 0,
                ':help'        => $q['help_text'] ?? null,
                ':options'     => isset($q['options']) ? json_encode($q['options'], JSON_UNESCAPED_UNICODE) : null,
                ':metadata'    => json_encode($q['metadata'] ?? [], JSON_UNESCAPED_UNICODE)
            ]);
            echo "Imported: " . $q['id'] . "\n";
        }

        echo "Import completed successfully.\n";
    }
}

$seeder = new QuestionSeeder();
$seeder->run();
