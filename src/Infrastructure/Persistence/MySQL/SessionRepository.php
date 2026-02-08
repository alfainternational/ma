<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\MySQL;

use App\Domain\Entities\AssessmentSession;
use App\Domain\Repositories\SessionRepositoryInterface;
use App\Infrastructure\Persistence\Database;
use App\Shared\Utils\UUID;

final class SessionRepository implements SessionRepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(string $id): ?AssessmentSession
    {
        $row = $this->db->fetch(
            'SELECT * FROM assessment_sessions WHERE id = :id LIMIT 1',
            ['id' => UUID::toBin($id)]
        );

        if ($row === null) {
            return null;
        }

        return AssessmentSession::fromArray($this->hydrateRow($row));
    }

    public function findByCompanyId(string $companyId, int $limit = 50, int $offset = 0): array
    {
        $rows = $this->db->fetchAll(
            'SELECT * FROM assessment_sessions WHERE company_id = :company_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset',
            [
                'company_id' => UUID::toBin($companyId),
                'limit'      => $limit,
                'offset'     => $offset,
            ]
        );

        return array_map(
            fn(array $row) => AssessmentSession::fromArray($this->hydrateRow($row)),
            $rows
        );
    }

    public function findByUserId(string $userId): array
    {
        $rows = $this->db->fetchAll(
            'SELECT * FROM assessment_sessions WHERE user_id = :user_id ORDER BY created_at DESC',
            ['user_id' => UUID::toBin($userId)]
        );

        return array_map(
            fn(array $row) => AssessmentSession::fromArray($this->hydrateRow($row)),
            $rows
        );
    }

    public function save(AssessmentSession $session): void
    {
        $data = $session->toArray();

        $this->db->insert('assessment_sessions', [
            'id'                  => UUID::toBin($data['id']),
            'company_id'          => UUID::toBin($data['company_id']),
            'user_id'             => UUID::toBin($data['user_id']),
            'session_name'        => $data['session_name'],
            'session_type'        => $data['session_type'],
            'status'              => $data['status'],
            'current_question_id' => $data['current_question_id'] !== null ? UUID::toBin($data['current_question_id']) : null,
            'questions_answered'  => $data['questions_answered'],
            'questions_total'     => $data['questions_total'],
            'progress_percentage' => $data['progress_percentage'],
            'started_at'          => $data['started_at'],
            'completed_at'        => $data['completed_at'],
            'context_data'        => !empty($data['context_data']) ? json_encode($data['context_data'], JSON_UNESCAPED_UNICODE) : null,
            'created_at'          => $data['created_at'],
            'updated_at'          => $data['updated_at'],
        ]);
    }

    public function update(AssessmentSession $session): void
    {
        $data = $session->toArray();

        $this->db->update(
            'assessment_sessions',
            [
                'session_name'        => $data['session_name'],
                'session_type'        => $data['session_type'],
                'status'              => $data['status'],
                'current_question_id' => $data['current_question_id'] !== null ? UUID::toBin($data['current_question_id']) : null,
                'questions_answered'  => $data['questions_answered'],
                'questions_total'     => $data['questions_total'],
                'progress_percentage' => $data['progress_percentage'],
                'started_at'          => $data['started_at'],
                'completed_at'        => $data['completed_at'],
                'context_data'        => !empty($data['context_data']) ? json_encode($data['context_data'], JSON_UNESCAPED_UNICODE) : null,
                'updated_at'          => date('Y-m-d H:i:s'),
            ],
            'id = :where_id',
            ['where_id' => UUID::toBin($data['id'])]
        );
    }

    public function delete(string $id): void
    {
        $this->db->delete(
            'assessment_sessions',
            'id = :id',
            ['id' => UUID::toBin($id)]
        );
    }

    public function findActiveByCompanyId(string $companyId): ?AssessmentSession
    {
        $row = $this->db->fetch(
            'SELECT * FROM assessment_sessions WHERE company_id = :company_id AND status IN (:s1, :s2) ORDER BY created_at DESC LIMIT 1',
            [
                'company_id' => UUID::toBin($companyId),
                's1'         => 'draft',
                's2'         => 'in_progress',
            ]
        );

        if ($row === null) {
            return null;
        }

        return AssessmentSession::fromArray($this->hydrateRow($row));
    }

    /**
     * Convert binary UUID columns back to string UUIDs in a database row.
     */
    private function hydrateRow(array $row): array
    {
        if (isset($row['id']) && strlen($row['id']) === 16) {
            $row['id'] = UUID::fromBin($row['id']);
        }
        if (isset($row['company_id']) && strlen($row['company_id']) === 16) {
            $row['company_id'] = UUID::fromBin($row['company_id']);
        }
        if (isset($row['user_id']) && strlen($row['user_id']) === 16) {
            $row['user_id'] = UUID::fromBin($row['user_id']);
        }
        if (isset($row['current_question_id']) && strlen($row['current_question_id']) === 16) {
            $row['current_question_id'] = UUID::fromBin($row['current_question_id']);
        }

        return $row;
    }
}
