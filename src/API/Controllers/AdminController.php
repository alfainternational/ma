<?php
declare(strict_types=1);

namespace App\API\Controllers;

use App\Application\Services\AuthService;
use App\Application\Services\AssessmentService;
use App\Application\Services\ReportService;

class AdminController
{
    public function __construct(
        private AuthService $authService,
        private AssessmentService $assessmentService,
        private ReportService $reportService,
    ) {}

    public function dashboard(): void
    {
        $userRole = $_REQUEST['user_role'] ?? '';

        try {
            if ($userRole !== 'admin') {
                $this->json(false, null, 'غير مصرح بالوصول', 403);
                return;
            }

            $stats = [
                'total_users' => $this->authService->getTotalUsers(),
                'total_sessions' => $this->assessmentService->getTotalSessions(),
                'completed_sessions' => $this->assessmentService->getCompletedSessions(),
                'total_reports' => $this->reportService->getTotalReports(),
                'recent_sessions' => $this->assessmentService->getRecentSessions(limit: 5),
                'recent_reports' => $this->reportService->getRecentReports(limit: 5),
            ];

            $this->json(true, $stats, 'إحصائيات لوحة التحكم');
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 500);
        }
    }

    public function listUsers(): void
    {
        $userRole = $_REQUEST['user_role'] ?? '';
        $page = (int) ($_REQUEST['page'] ?? 1);
        $perPage = (int) ($_REQUEST['per_page'] ?? 20);
        $search = $_REQUEST['search'] ?? null;

        try {
            if ($userRole !== 'admin') {
                $this->json(false, null, 'غير مصرح بالوصول', 403);
                return;
            }

            $users = $this->authService->listUsers(
                page: $page,
                perPage: $perPage,
                search: $search
            );

            $this->json(true, $users['items'], 'قائمة المستخدمين', 200, [
                'current_page' => $users['current_page'],
                'per_page' => $users['per_page'],
                'total' => $users['total'],
                'last_page' => $users['last_page'],
            ]);
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 500);
        }
    }

    public function listSessions(): void
    {
        $userRole = $_REQUEST['user_role'] ?? '';
        $page = (int) ($_REQUEST['page'] ?? 1);
        $perPage = (int) ($_REQUEST['per_page'] ?? 20);
        $status = $_REQUEST['status'] ?? null;

        try {
            if ($userRole !== 'admin') {
                $this->json(false, null, 'غير مصرح بالوصول', 403);
                return;
            }

            $sessions = $this->assessmentService->listAllSessions(
                page: $page,
                perPage: $perPage,
                status: $status
            );

            $this->json(true, $sessions['items'], 'قائمة جميع الجلسات', 200, [
                'current_page' => $sessions['current_page'],
                'per_page' => $sessions['per_page'],
                'total' => $sessions['total'],
                'last_page' => $sessions['last_page'],
            ]);
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 500);
        }
    }

    public function getSystemStats(): void
    {
        $userRole = $_REQUEST['user_role'] ?? '';

        try {
            if ($userRole !== 'admin') {
                $this->json(false, null, 'غير مصرح بالوصول', 403);
                return;
            }

            $stats = [
                'users' => [
                    'total' => $this->authService->getTotalUsers(),
                    'active_today' => $this->authService->getActiveUsersCount(period: 'today'),
                    'active_this_week' => $this->authService->getActiveUsersCount(period: 'week'),
                    'active_this_month' => $this->authService->getActiveUsersCount(period: 'month'),
                ],
                'assessments' => [
                    'total_sessions' => $this->assessmentService->getTotalSessions(),
                    'completed' => $this->assessmentService->getCompletedSessions(),
                    'in_progress' => $this->assessmentService->getInProgressSessions(),
                    'average_completion_time' => $this->assessmentService->getAverageCompletionTime(),
                    'average_score' => $this->assessmentService->getAverageScore(),
                ],
                'reports' => [
                    'total' => $this->reportService->getTotalReports(),
                    'generated_today' => $this->reportService->getReportsCount(period: 'today'),
                    'generated_this_week' => $this->reportService->getReportsCount(period: 'week'),
                    'generated_this_month' => $this->reportService->getReportsCount(period: 'month'),
                ],
                'system' => [
                    'php_version' => PHP_VERSION,
                    'memory_usage' => memory_get_usage(true),
                    'peak_memory_usage' => memory_get_peak_usage(true),
                    'uptime' => time() - (int) ($_SERVER['REQUEST_TIME'] ?? time()),
                ],
            ];

            $this->json(true, $stats, 'إحصائيات النظام');
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
