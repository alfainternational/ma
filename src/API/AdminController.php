<?php
namespace App\API;

use App\Config\Database;
use PDO;

class AdminController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getDashboardStats(): array {
        // Total Users
        $totalUsers = $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        
        // Total Sessions (Completed Assessments roughly)
        $totalSessions = $this->db->query("SELECT COUNT(*) FROM assessment_sessions")->fetchColumn();
        
        // Detailed User List
        $stmt = $this->db->query("
            SELECT u.id, u.full_name as name, u.email, u.role, u.created_at,
            (SELECT COUNT(*) FROM assessment_sessions s WHERE s.user_id = u.id) as session_count
            FROM users u
            ORDER BY u.created_at DESC
        ");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'stats' => [
                'total_users' => $totalUsers,
                'total_sessions' => $totalSessions
            ],
            'users' => $users
        ];
    }
    public function getAllQuestions(): array {
        return $this->db->query("SELECT * FROM questions ORDER BY category, display_order")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getQuestion(string $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM questions WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function saveQuestion(array $data): array {
        try {
            // Determine ID (New or Update)
            $isNew = empty($data['id']);
            $id = $isNew ? 'Q_' . strtoupper(uniqid()) : $data['id'];

            if ($isNew) {
                $stmt = $this->db->prepare("
                    INSERT INTO questions (id, category, subcategory, question_ar, question_en, question_type, options, display_order, active, 
                                           why_it_matters_ar, why_it_matters_en, risks_of_neglect_ar, risks_of_neglect_en, educational_tips_ar, educational_tips_en)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $id, 
                    $data['category'], 
                    $data['subcategory'] ?? null,
                    $data['question_ar'],
                    $data['question_en'] ?? $data['question_ar'],
                    $data['question_type'],
                    json_encode($data['options'] ?? [], JSON_UNESCAPED_UNICODE),
                    $data['display_order'] ?? 0,
                    1,
                    $data['why_it_matters_ar'] ?? null,
                    $data['why_it_matters_en'] ?? ($data['why_it_matters_ar'] ?? null),
                    $data['risks_of_neglect_ar'] ?? null,
                    $data['risks_of_neglect_en'] ?? ($data['risks_of_neglect_ar'] ?? null),
                    $data['educational_tips_ar'] ?? null,
                    $data['educational_tips_en'] ?? ($data['educational_tips_ar'] ?? null)
                ]);
            } else {
                $stmt = $this->db->prepare("
                    UPDATE questions SET 
                    category=?, subcategory=?, question_ar=?, question_en=?, question_type=?, options=?, display_order=?, active=?,
                    why_it_matters_ar=?, why_it_matters_en=?, risks_of_neglect_ar=?, risks_of_neglect_en=?, educational_tips_ar=?, educational_tips_en=?
                    WHERE id=?
                ");
                $stmt->execute([
                    $data['category'], 
                    $data['subcategory'] ?? null,
                    $data['question_ar'],
                    $data['question_en'],
                    $data['question_type'],
                    json_encode($data['options'] ?? [], JSON_UNESCAPED_UNICODE),
                    $data['display_order'] ?? 0,
                    $data['active'] ?? 1,
                    $data['why_it_matters_ar'] ?? null,
                    $data['why_it_matters_en'] ?? ($data['why_it_matters_ar'] ?? null),
                    $data['risks_of_neglect_ar'] ?? null,
                    $data['risks_of_neglect_en'] ?? ($data['risks_of_neglect_ar'] ?? null),
                    $data['educational_tips_ar'] ?? null,
                    $data['educational_tips_en'] ?? ($data['educational_tips_ar'] ?? null),
                    $id
                ]);
            }

            return ['status' => 'success', 'id' => $id];
        } catch (\PDOException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteQuestion(string $id): array {
        try {
             $this->db->prepare("DELETE FROM questions WHERE id = ?")->execute([$id]);
             return ['status' => 'success'];
        } catch (\PDOException $e) {
             return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
