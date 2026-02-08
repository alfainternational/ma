<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\MySQL;

use App\Domain\Entities\Question;
use App\Domain\Repositories\QuestionRepositoryInterface;
use App\Infrastructure\Persistence\Database;
use App\Shared\Utils\UUID;

final class QuestionRepository implements QuestionRepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(string $id): ?Question
    {
        $row = $this->db->fetch(
            'SELECT * FROM questions WHERE id = :id LIMIT 1',
            ['id' => UUID::toBin($id)]
        );

        if ($row === null) {
            return null;
        }

        return Question::fromArray($this->hydrateRow($row));
    }

    public function findByCategory(string $category): array
    {
        $rows = $this->db->fetchAll(
            'SELECT * FROM questions WHERE category = :category AND is_active = 1 ORDER BY display_order ASC',
            ['category' => $category]
        );

        return array_map(
            fn(array $row) => Question::fromArray($this->hydrateRow($row)),
            $rows
        );
    }

    public function findBySector(string $sector): array
    {
        $rows = $this->db->fetchAll(
            'SELECT * FROM questions WHERE industry_specific = :sector AND is_active = 1 ORDER BY display_order ASC',
            ['sector' => $sector]
        );

        return array_map(
            fn(array $row) => Question::fromArray($this->hydrateRow($row)),
            $rows
        );
    }

    public function findAll(int $limit = 250, int $offset = 0): array
    {
        $rows = $this->db->fetchAll(
            'SELECT * FROM questions WHERE is_active = 1 ORDER BY category ASC, display_order ASC LIMIT :limit OFFSET :offset',
            ['limit' => $limit, 'offset' => $offset]
        );

        return array_map(
            fn(array $row) => Question::fromArray($this->hydrateRow($row)),
            $rows
        );
    }

    public function findNextQuestion(string $sessionId, ?string $currentQuestionId = null): ?Question
    {
        if ($currentQuestionId === null) {
            // No current question: return the first unanswered active question for this session.
            $row = $this->db->fetch(
                'SELECT q.* FROM questions q
                 WHERE q.is_active = 1
                   AND q.id NOT IN (
                       SELECT a.question_id FROM answers a WHERE a.session_id = :session_id
                   )
                 ORDER BY q.display_order ASC
                 LIMIT 1',
                ['session_id' => UUID::toBin($sessionId)]
            );
        } else {
            // Find the display_order of the current question, then get the next unanswered one.
            $currentRow = $this->db->fetch(
                'SELECT display_order, category FROM questions WHERE id = :id LIMIT 1',
                ['id' => UUID::toBin($currentQuestionId)]
            );

            if ($currentRow === null) {
                return null;
            }

            $row = $this->db->fetch(
                'SELECT q.* FROM questions q
                 WHERE q.is_active = 1
                   AND q.display_order > :current_order
                   AND q.id NOT IN (
                       SELECT a.question_id FROM answers a WHERE a.session_id = :session_id
                   )
                 ORDER BY q.display_order ASC
                 LIMIT 1',
                [
                    'current_order' => $currentRow['display_order'],
                    'session_id'    => UUID::toBin($sessionId),
                ]
            );
        }

        if ($row === null) {
            return null;
        }

        return Question::fromArray($this->hydrateRow($row));
    }

    public function countByCategory(string $category): int
    {
        $row = $this->db->fetch(
            'SELECT COUNT(*) AS total FROM questions WHERE category = :category AND is_active = 1',
            ['category' => $category]
        );

        return (int) ($row['total'] ?? 0);
    }

    public function countAll(): int
    {
        $row = $this->db->fetch(
            'SELECT COUNT(*) AS total FROM questions WHERE is_active = 1'
        );

        return (int) ($row['total'] ?? 0);
    }

    /**
     * Convert binary UUID columns back to string UUIDs in a database row.
     */
    private function hydrateRow(array $row): array
    {
        if (isset($row['id']) && strlen($row['id']) === 16) {
            $row['id'] = UUID::fromBin($row['id']);
        }

        return $row;
    }
}
