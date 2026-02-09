<?php
/**
 * Answer Model
 * Marketing AI System
 */
class Answer {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function save(int $sessionId, string $questionId, $answerValue, ?int $timeTaken = null): int {
        // Check if answer already exists (upsert)
        $existing = $this->db->fetch(
            "SELECT id FROM answers WHERE session_id = :sid AND question_id = :qid",
            ['sid' => $sessionId, 'qid' => $questionId]
        );

        $normalized = $this->normalizeAnswer($questionId, $answerValue);

        if ($existing) {
            $this->db->update('answers', [
                'answer_value' => is_array($answerValue) ? json_encode($answerValue) : (string)$answerValue,
                'answer_normalized' => json_encode($normalized),
                'time_taken_seconds' => $timeTaken,
                'answered_at' => date('Y-m-d H:i:s'),
            ], 'id = :id', ['id' => $existing['id']]);
            return $existing['id'];
        }

        return $this->db->insert('answers', [
            'session_id' => $sessionId,
            'question_id' => $questionId,
            'answer_value' => is_array($answerValue) ? json_encode($answerValue) : (string)$answerValue,
            'answer_normalized' => json_encode($normalized),
            'confidence_score' => 1.00,
            'time_taken_seconds' => $timeTaken,
        ]);
    }

    public function getBySession(int $sessionId): array {
        $answers = $this->db->fetchAll(
            "SELECT a.*, q.category, q.type as question_type, q.weight
             FROM answers a
             JOIN questions q ON a.question_id = q.id
             WHERE a.session_id = :sid
             ORDER BY a.answered_at ASC",
            ['sid' => $sessionId]
        );

        $result = [];
        foreach ($answers as $a) {
            $a['answer_normalized'] = json_decode($a['answer_normalized'], true);
            $result[$a['question_id']] = $a;
        }
        return $result;
    }

    public function getAnswerValue(int $sessionId, string $questionId) {
        $answer = $this->db->fetch(
            "SELECT answer_value, answer_normalized FROM answers WHERE session_id = :sid AND question_id = :qid",
            ['sid' => $sessionId, 'qid' => $questionId]
        );
        return $answer ? $answer['answer_value'] : null;
    }

    public function getAnswerCount(int $sessionId): int {
        return $this->db->count('answers', 'session_id = :sid', ['sid' => $sessionId]);
    }

    public function deleteBySession(int $sessionId): int {
        return $this->db->delete('answers', 'session_id = :sid', ['sid' => $sessionId]);
    }

    private function normalizeAnswer(string $questionId, $value): array {
        return [
            'raw' => $value,
            'numeric' => is_numeric($value) ? (float)$value : null,
            'processed_at' => date('Y-m-d H:i:s'),
        ];
    }
}
