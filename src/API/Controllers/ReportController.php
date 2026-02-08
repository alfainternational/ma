<?php
declare(strict_types=1);

namespace App\API\Controllers;

use App\Application\Services\ReportService;
use App\Application\Services\AnalysisService;

class ReportController
{
    public function __construct(
        private ReportService $reportService,
        private AnalysisService $analysisService,
    ) {}

    public function generate(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $userId = $_REQUEST['user_id'] ?? '';

        try {
            $analysis = $this->analysisService->analyze($data['session_id'] ?? '');

            $report = $this->reportService->generate(
                sessionId: $data['session_id'] ?? '',
                userId: $userId,
                analysisResult: $analysis,
                type: $data['report_type'] ?? 'comprehensive'
            );

            $this->json(true, $report->toArray(), 'تم إنشاء التقرير بنجاح', 201);
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 422);
        }
    }

    public function get(): void
    {
        $reportId = $_REQUEST['report_id'] ?? '';
        $userId = $_REQUEST['user_id'] ?? '';

        try {
            $report = $this->reportService->getReport($reportId, $userId);
            $this->json(true, $report->toArray(), 'بيانات التقرير');
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 404);
        }
    }

    public function list(): void
    {
        $companyId = $_REQUEST['company_id'] ?? '';
        $userId = $_REQUEST['user_id'] ?? '';
        $page = (int) ($_REQUEST['page'] ?? 1);
        $perPage = (int) ($_REQUEST['per_page'] ?? 15);

        try {
            $reports = $this->reportService->listReports(
                companyId: $companyId,
                userId: $userId,
                page: $page,
                perPage: $perPage
            );

            $this->json(true, $reports['items'], 'قائمة التقارير', 200, [
                'current_page' => $reports['current_page'],
                'per_page' => $reports['per_page'],
                'total' => $reports['total'],
                'last_page' => $reports['last_page'],
            ]);
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 500);
        }
    }

    public function export(): void
    {
        $reportId = $_REQUEST['report_id'] ?? '';
        $userId = $_REQUEST['user_id'] ?? '';
        $format = $_REQUEST['format'] ?? 'pdf';

        try {
            $allowedFormats = ['pdf', 'xlsx', 'csv', 'html'];
            if (!in_array($format, $allowedFormats, true)) {
                $this->json(false, null, 'صيغة التصدير غير مدعومة: ' . $format, 400);
                return;
            }

            $exportData = $this->reportService->export($reportId, $userId, $format);

            header('Content-Type: ' . $exportData['content_type']);
            header('Content-Disposition: attachment; filename="' . $exportData['filename'] . '"');
            header('Content-Length: ' . strlen($exportData['content']));
            echo $exportData['content'];
        } catch (\Exception $e) {
            $this->json(false, null, $e->getMessage(), 404);
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
