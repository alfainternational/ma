<?php
/**
 * Question Model
 * Marketing AI System
 */
class Question {
    private Database $db;
    private array $questionsCache = [];

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getById(string $id): ?array {
        if (isset($this->questionsCache[$id])) {
            return $this->questionsCache[$id];
        }

        $q = $this->db->fetch("SELECT * FROM questions WHERE id = :id AND active = TRUE", ['id' => $id]);
        if ($q) {
            $q = $this->decodeJsonFields($q);
            $this->questionsCache[$id] = $q;
        }
        return $q;
    }

    public function getAll(bool $activeOnly = true): array {
        $sql = "SELECT * FROM questions";
        if ($activeOnly) $sql .= " WHERE active = TRUE";
        $sql .= " ORDER BY display_order ASC";

        $questions = $this->db->fetchAll($sql);
        return array_map([$this, 'decodeJsonFields'], $questions);
    }

    public function getByCategory(string $category): array {
        $questions = $this->db->fetchAll(
            "SELECT * FROM questions WHERE category = :cat AND active = TRUE ORDER BY display_order ASC",
            ['cat' => $category]
        );
        return array_map([$this, 'decodeJsonFields'], $questions);
    }

    public function getBySector(string $sector): array {
        $questions = $this->db->fetchAll(
            "SELECT * FROM questions WHERE (sector_specific IS NULL OR sector_specific = :sector) AND active = TRUE ORDER BY display_order ASC",
            ['sector' => $sector]
        );
        return array_map([$this, 'decodeJsonFields'], $questions);
    }

    public function getByPriority(string $priority): array {
        $questions = $this->db->fetchAll(
            "SELECT * FROM questions WHERE priority = :pri AND active = TRUE ORDER BY display_order ASC",
            ['pri' => $priority]
        );
        return array_map([$this, 'decodeJsonFields'], $questions);
    }

    public function getTotalCount(bool $activeOnly = true): int {
        if ($activeOnly) {
            return $this->db->count('questions', 'active = TRUE');
        }
        return $this->db->count('questions');
    }

    public function getCategoryStats(): array {
        return $this->db->fetchAll(
            "SELECT category, COUNT(*) as count,
                    SUM(CASE WHEN priority = 'critical' THEN 1 ELSE 0 END) as critical_count
             FROM questions WHERE active = TRUE GROUP BY category ORDER BY MIN(display_order)"
        );
    }

    public function loadFromJson(string $filePath): int {
        if (!file_exists($filePath)) {
            throw new RuntimeException("Questions file not found: {$filePath}");
        }

        $data = json_decode(file_get_contents($filePath), true);
        if (!$data) {
            throw new RuntimeException("Invalid JSON in questions file");
        }

        $count = 0;
        $questions = $data['questions'] ?? $data['question_bank']['questions'] ?? [];

        foreach ($questions as $q) {
            $this->insertOrUpdate($q);
            $count++;
        }

        return $count;
    }

    private function insertOrUpdate(array $q): void {
        $existing = $this->db->fetch("SELECT id FROM questions WHERE id = :id", ['id' => $q['id']]);

        $data = [
            'category' => $q['category'] ?? 'general',
            'subcategory' => $q['subcategory'] ?? null,
            'question_ar' => $q['question_ar'],
            'question_en' => $q['question_en'] ?? $q['question_ar'],
            'type' => $q['type'],
            'options' => isset($q['options']) ? json_encode($q['options']) : null,
            'validation_rules' => isset($q['validation_rules']) ? json_encode($q['validation_rules']) : null,
            'input_config' => isset($q['input_config']) ? json_encode($q['input_config']) : null,
            'help_text_ar' => $q['help_text'] ?? $q['help_text_ar'] ?? null,
            'help_text_en' => $q['help_text_en'] ?? null,
            'required' => $q['required'] ?? true,
            'priority' => $q['priority'] ?? 'medium',
            'display_order' => $q['order'] ?? $q['display_order'] ?? 999,
            'weight' => $q['metadata']['weight'] ?? $q['weight'] ?? 1.0,
            'sector_specific' => $q['sector_specific'] ?? null,
            'expert_usage' => isset($q['expert_usage']) ? json_encode($q['expert_usage']) : null,
            'ai_processing' => isset($q['ai_processing']) ? json_encode($q['ai_processing']) : null,
            'metadata' => isset($q['metadata']) ? json_encode($q['metadata']) : null,
        ];

        if ($existing) {
            $this->db->update('questions', $data, "id = :id", ['id' => $q['id']]);
        } else {
            $data['id'] = $q['id'];
            $fields = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            $this->db->query("INSERT INTO questions ({$fields}) VALUES ({$placeholders})", $data);
        }
    }

    private function decodeJsonFields(array $q): array {
        $jsonFields = ['options', 'validation_rules', 'input_config', 'skip_logic', 'follow_up_questions', 'expert_usage', 'ai_processing', 'metadata'];
        foreach ($jsonFields as $field) {
            if (isset($q[$field]) && is_string($q[$field])) {
                $q[$field] = json_decode($q[$field], true);
            }
        }
        return $q;
    }
}
