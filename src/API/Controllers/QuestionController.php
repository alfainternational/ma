<?php
declare(strict_types=1);

namespace App\API\Controllers;

use App\Application\Services\AssessmentService;

class QuestionController
{
    public function __construct(private AssessmentService $assessmentService) {}

    public function getNext(): void
    {
        $sessionId = $_REQUEST['session_id'] ?? '';
        $userId = $_REQUEST['user_id'] ?? '';

        try {
            $question = $this->assessmentService->getNextQuestion($sessionId, $userId);

            if ($question === null) {
                $this->json(true, null, 'تم الإجابة على جميع الأسئلة');
                return;
            }

            $this->json(true, $question->toArray(), 'السؤال التالي');
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 404);
        }
    }

    public function getByCategory(): void
    {
        $category = $_REQUEST['category'] ?? '';
        $userId = $_REQUEST['user_id'] ?? '';

        try {
            $questions = $this->assessmentService->getQuestionsByCategory($category);

            $this->json(true, array_map(
                fn($q) => $q->toArray(),
                $questions
            ), 'أسئلة الفئة: ' . $category);
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 404);
        }
    }

    public function list(): void
    {
        $userId = $_REQUEST['user_id'] ?? '';
        $userRole = $_REQUEST['user_role'] ?? '';
        $page = (int) ($_REQUEST['page'] ?? 1);
        $perPage = (int) ($_REQUEST['per_page'] ?? 20);
        $category = $_REQUEST['category'] ?? null;

        try {
            if ($userRole !== 'admin') {
                $this->json(false, null, 'غير مصرح بالوصول', 403);
                return;
            }

            $result = $this->assessmentService->listQuestions(
                page: $page,
                perPage: $perPage,
                category: $category
            );

            $this->json(true, $result['items'], 'قائمة الأسئلة', 200, [
                'current_page' => $result['current_page'],
                'per_page' => $result['per_page'],
                'total' => $result['total'],
                'last_page' => $result['last_page'],
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
