<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Entities\Answer;
use App\Domain\Entities\AssessmentSession;
use App\Domain\Entities\Question;
use App\Domain\Repositories\SessionRepositoryInterface;
use App\Domain\Repositories\QuestionRepositoryInterface;
use App\Infrastructure\Persistence\Database;
use App\Shared\Exceptions\NotFoundException;
use App\Shared\Exceptions\ValidationException;
use App\Shared\Exceptions\BusinessLogicException;
use App\Shared\Utils\UUID;

final class AssessmentService
{
    public function __construct(
        private readonly SessionRepositoryInterface $sessionRepository,
        private readonly QuestionRepositoryInterface $questionRepository,
        private readonly Database $db,
    ) {}

    /**
     * Create a new assessment session for a company.
     *
     * @throws ValidationException When required parameters are invalid.
     */
    public function createSession(
        string $companyId,
        string $userId,
        string $type = 'full',
    ): AssessmentSession {
        $allowedTypes = ['full', 'quick', 'deep_dive', 'follow_up'];

        if (!in_array($type, $allowedTypes, true)) {
            throw new ValidationException("Invalid session type: {$type}");
        }

        $questions    = $this->questionRepository->findBySessionType($type);
        $totalCount   = count($questions);
        $firstQuestion = $totalCount > 0 ? $questions[0] : null;

        $session = new AssessmentSession(
            id:                UUID::generate(),
            companyId:         $companyId,
            userId:            $userId,
            sessionName:       $this->generateSessionName($type),
            sessionType:       $type,
            status:            'draft',
            currentQuestionId: $firstQuestion?->getId(),
            questionsAnswered: 0,
            questionsTotal:    $totalCount,
            progressPercentage: 0.0,
        );

        $this->sessionRepository->save($session);

        return $session;
    }

    /**
     * Retrieve a session by its ID.
     *
     * @throws NotFoundException When the session does not exist.
     */
    public function getSession(string $id): ?AssessmentSession
    {
        return $this->sessionRepository->findById($id);
    }

    /**
     * Submit an answer for a specific question within a session.
     *
     * @param array{answer_value?: string, confidence_score?: float, time_spent_seconds?: int, is_skipped?: bool, skip_reason?: string} $answerData
     *
     * @throws NotFoundException     When the session or question does not exist.
     * @throws BusinessLogicException When the session is not in a submittable state.
     */
    public function submitAnswer(
        string $sessionId,
        string $questionId,
        array $answerData,
    ): Answer {
        $session = $this->sessionRepository->findById($sessionId);

        if ($session === null) {
            throw new NotFoundException("Session not found: {$sessionId}");
        }

        if (!in_array($session->getStatus(), ['draft', 'in_progress'], true)) {
            throw new BusinessLogicException(
                "Cannot submit answers to a session with status '{$session->getStatus()}'"
            );
        }

        $question = $this->questionRepository->findById($questionId);

        if ($question === null) {
            throw new NotFoundException("Question not found: {$questionId}");
        }

        if ($question->isRequired() && empty($answerData['answer_value']) && empty($answerData['is_skipped'])) {
            throw new ValidationException('This question requires an answer');
        }

        // Start the session on first answer submission
        if ($session->getStatus() === 'draft') {
            $session->start();
        }

        $normalizedAnswer = $this->normalizeAnswer($question, $answerData);

        $answer = new Answer(
            id:               UUID::generate(),
            sessionId:        $sessionId,
            questionId:       $questionId,
            answerValue:      $answerData['answer_value'] ?? null,
            answerNormalized: $normalizedAnswer,
            confidenceScore:  (float) ($answerData['confidence_score'] ?? 1.0),
            source:           'user',
            timeSpentSeconds: (int) ($answerData['time_spent_seconds'] ?? 0),
            isSkipped:        (bool) ($answerData['is_skipped'] ?? false),
            skipReason:       $answerData['skip_reason'] ?? null,
        );

        $this->db->beginTransaction();

        try {
            $answerArray = $answer->toArray();
            $answerArray['answer_normalized'] = json_encode(
                $answerArray['answer_normalized'],
                JSON_THROW_ON_ERROR
            );
            unset($answerArray['created_at'], $answerArray['updated_at']);
            $answerArray['created_at'] = date('Y-m-d H:i:s');
            $answerArray['updated_at'] = date('Y-m-d H:i:s');

            $this->db->insert('answers', $answerArray);

            // Advance session to the next question
            $answeredCount = $this->countAnsweredQuestions($sessionId);
            $nextQuestion  = $this->resolveNextQuestion($sessionId, $session->getSessionType());

            $session->advanceQuestion(
                $nextQuestion?->getId() ?? $questionId,
                $answeredCount,
                $session->getQuestionsTotal(),
            );

            $this->sessionRepository->update($session);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }

        return $answer;
    }

    /**
     * Get the next unanswered question for a session.
     *
     * @throws NotFoundException When the session does not exist.
     */
    public function getNextQuestion(string $sessionId): ?Question
    {
        $session = $this->sessionRepository->findById($sessionId);

        if ($session === null) {
            throw new NotFoundException("Session not found: {$sessionId}");
        }

        if ($session->getStatus() === 'completed') {
            return null;
        }

        return $this->resolveNextQuestion($sessionId, $session->getSessionType());
    }

    /**
     * Mark a session as completed.
     *
     * @throws NotFoundException     When the session does not exist.
     * @throws BusinessLogicException When the session cannot be completed.
     */
    public function completeSession(string $sessionId): AssessmentSession
    {
        $session = $this->sessionRepository->findById($sessionId);

        if ($session === null) {
            throw new NotFoundException("Session not found: {$sessionId}");
        }

        if ($session->getStatus() === 'completed') {
            throw new BusinessLogicException('Session is already completed');
        }

        if (!in_array($session->getStatus(), ['in_progress', 'draft'], true)) {
            throw new BusinessLogicException(
                "Cannot complete a session with status '{$session->getStatus()}'"
            );
        }

        $answeredCount   = $this->countAnsweredQuestions($sessionId);
        $requiredCount   = $this->countRequiredQuestions($session->getSessionType());
        $unansweredCount = $requiredCount - $answeredCount;

        if ($unansweredCount > 0) {
            throw new BusinessLogicException(
                "Cannot complete session: {$unansweredCount} required question(s) remain unanswered"
            );
        }

        $session->complete();
        $this->sessionRepository->update($session);

        return $session;
    }

    /**
     * Get progress details for a session.
     *
     * @return array{session_id: string, status: string, questions_answered: int, questions_total: int, progress_percentage: float, current_question_id: ?string, started_at: ?string, estimated_remaining_minutes: int}
     *
     * @throws NotFoundException When the session does not exist.
     */
    public function getSessionProgress(string $sessionId): array
    {
        $session = $this->sessionRepository->findById($sessionId);

        if ($session === null) {
            throw new NotFoundException("Session not found: {$sessionId}");
        }

        $answeredCount = $this->countAnsweredQuestions($sessionId);
        $remaining     = max(0, $session->getQuestionsTotal() - $answeredCount);

        $avgTimePerQuestion    = $this->calculateAverageTimePerQuestion($sessionId);
        $estimatedMinRemaining = (int) ceil(($remaining * $avgTimePerQuestion) / 60);

        return [
            'session_id'                  => $session->getId(),
            'status'                      => $session->getStatus(),
            'questions_answered'          => $answeredCount,
            'questions_total'             => $session->getQuestionsTotal(),
            'progress_percentage'         => $session->getQuestionsTotal() > 0
                ? round(($answeredCount / $session->getQuestionsTotal()) * 100, 2)
                : 0.0,
            'current_question_id'         => $session->getCurrentQuestionId(),
            'started_at'                  => $session->getStartedAt(),
            'estimated_remaining_minutes' => $estimatedMinRemaining,
        ];
    }

    /**
     * Get all sessions belonging to a company.
     *
     * @return AssessmentSession[]
     */
    public function getSessionsByCompany(string $companyId): array
    {
        return $this->sessionRepository->findByCompanyId($companyId);
    }

    // ---------------------------------------------------------------
    //  Private helpers
    // ---------------------------------------------------------------

    private function generateSessionName(string $type): string
    {
        $labels = [
            'full'      => 'Full Assessment',
            'quick'     => 'Quick Assessment',
            'deep_dive' => 'Deep Dive Assessment',
            'follow_up' => 'Follow-Up Assessment',
        ];

        $label = $labels[$type] ?? 'Assessment';

        return $label . ' - ' . date('Y-m-d H:i');
    }

    /**
     * Normalize raw answer data according to the question type for scoring.
     */
    private function normalizeAnswer(Question $question, array $answerData): ?array
    {
        if (empty($answerData['answer_value'])) {
            return null;
        }

        $value = $answerData['answer_value'];

        return match ($question->getQuestionType()) {
            'likert_scale'    => ['numeric_value' => (int) $value, 'max' => 5],
            'multiple_choice' => ['selected' => $value, 'options' => $question->getOptions()],
            'yes_no'          => ['boolean' => mb_strtolower($value) === 'yes'],
            'numeric'         => ['numeric_value' => (float) $value],
            'percentage'      => ['percentage' => min(100.0, max(0.0, (float) $value))],
            default           => ['raw' => $value],
        };
    }

    private function countAnsweredQuestions(string $sessionId): int
    {
        $row = $this->db->fetch(
            'SELECT COUNT(*) AS cnt FROM answers WHERE session_id = :session_id',
            ['session_id' => $sessionId],
        );

        return (int) ($row['cnt'] ?? 0);
    }

    private function countRequiredQuestions(string $sessionType): int
    {
        $questions = $this->questionRepository->findBySessionType($sessionType);

        return count(array_filter($questions, fn(Question $q): bool => $q->isRequired()));
    }

    /**
     * Determine the next unanswered question for the session.
     */
    private function resolveNextQuestion(string $sessionId, string $sessionType): ?Question
    {
        $answeredIds = $this->db->fetchAll(
            'SELECT question_id FROM answers WHERE session_id = :session_id',
            ['session_id' => $sessionId],
        );

        $answeredSet = array_column($answeredIds, 'question_id');
        $questions   = $this->questionRepository->findBySessionType($sessionType);

        foreach ($questions as $question) {
            if (!in_array($question->getId(), $answeredSet, true)) {
                return $question;
            }
        }

        return null;
    }

    private function calculateAverageTimePerQuestion(string $sessionId): float
    {
        $row = $this->db->fetch(
            'SELECT AVG(time_spent_seconds) AS avg_time FROM answers WHERE session_id = :session_id AND time_spent_seconds > 0',
            ['session_id' => $sessionId],
        );

        $avg = (float) ($row['avg_time'] ?? 0);

        // Default to 90 seconds if no data is available yet
        return $avg > 0 ? $avg : 90.0;
    }
}
