<?php
declare(strict_types=1);

namespace App\API\Controllers;

use App\Application\Services\AssessmentService;

class AssessmentController
{
    public function __construct(private AssessmentService $assessmentService) {}

    public function createSession(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $userId = $_REQUEST['user_id'] ?? '';

        try {
            $session = $this->assessmentService->createSession(
                userId: $userId,
                companyId: $data['company_id'] ?? '',
                type: $data['type'] ?? 'full'
            );

            $this->json(true, $session->toArray(), 'تم إنشاء جلسة التقييم بنجاح', 201);
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 422);
        }
    }

    public function getSession(): void
    {
        $sessionId = $_REQUEST['session_id'] ?? '';
        $userId = $_REQUEST['user_id'] ?? '';

        try {
            $session = $this->assessmentService->getSession($sessionId, $userId);
            $this->json(true, $session->toArray(), 'بيانات جلسة التقييم');
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 404);
        }
    }

    public function submitAnswer(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $userId = $_REQUEST['user_id'] ?? '';

        try {
            $result = $this->assessmentService->submitAnswer(
                sessionId: $data['session_id'] ?? '',
                questionId: $data['question_id'] ?? '',
                answer: $data['answer'] ?? '',
                userId: $userId
            );

            $this->json(true, $result, 'تم حفظ الإجابة بنجاح');
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 422);
        }
    }

    public function getProgress(): void
    {
        $sessionId = $_REQUEST['session_id'] ?? '';
        $userId = $_REQUEST['user_id'] ?? '';

        try {
            $progress = $this->assessmentService->getProgress($sessionId, $userId);
            $this->json(true, $progress, 'تقدم جلسة التقييم');
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 404);
        }
    }

    public function completeSession(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $userId = $_REQUEST['user_id'] ?? '';

        try {
            $result = $this->assessmentService->completeSession(
                $data['session_id'] ?? '',
                $userId
            );

            $this->json(true, $result, 'تم إكمال جلسة التقييم بنجاح');
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 422);
        }
    }

    public function listSessions(): void
    {
        $companyId = $_REQUEST['company_id'] ?? '';
        $userId = $_REQUEST['user_id'] ?? '';
        $page = (int) ($_REQUEST['page'] ?? 1);
        $perPage = (int) ($_REQUEST['per_page'] ?? 15);

        try {
            $sessions = $this->assessmentService->listSessions(
                companyId: $companyId,
                userId: $userId,
                page: $page,
                perPage: $perPage
            );

            $this->json(true, $sessions['items'], 'قائمة جلسات التقييم', 200, [
                'current_page' => $sessions['current_page'],
                'per_page' => $sessions['per_page'],
                'total' => $sessions['total'],
                'last_page' => $sessions['last_page'],
            ]);
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 500);
        }
    }

    private function json(bool $success, mixed $data, string $message, int $code = 200, ?array $meta = null): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => $success,
            'data' => $data,
            'message' => $message,
            'meta' => $meta,
        ], JSON_UNESCAPED_UNICODE);
    }
}
