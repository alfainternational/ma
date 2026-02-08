<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Entities\Report;
use App\Infrastructure\Persistence\Database;
use App\Shared\Utils\UUID;
use App\Shared\Exceptions\NotFoundException;

class ReportService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function generateReport(string $sessionId, string $reportType = 'executive_summary'): Report
    {
        $session = $this->db->fetch(
            "SELECT BIN_TO_UUID(id) as id, BIN_TO_UUID(company_id) as company_id, status
             FROM assessment_sessions WHERE id = UUID_TO_BIN(?)",
            [$sessionId]
        );

        if (!$session) {
            throw new NotFoundException('الجلسة غير موجودة');
        }

        $answers = $this->db->fetchAll(
            "SELECT BIN_TO_UUID(id) as id, BIN_TO_UUID(question_id) as question_id,
                    answer_value, confidence_score, time_spent_seconds
             FROM answers WHERE session_id = UUID_TO_BIN(?)",
            [$sessionId]
        );

        $scores = $this->calculateScores($answers);
        $recommendations = $this->generateRecommendations($scores);
        $alerts = $this->generateAlerts($scores);

        $reportId = UUID::generate();
        $reportData = [
            'session_summary' => [
                'total_questions' => count($answers),
                'completion_date' => date('Y-m-d H:i:s'),
            ],
            'analysis' => [
                'maturity_level' => $this->getMaturityLevel($scores['overall'] ?? 0),
                'sector_comparison' => 'above_average',
            ],
        ];

        $binId = UUID::toBin($reportId);
        $this->db->execute(
            "INSERT INTO reports (id, session_id, company_id, report_type, status, report_data, scores, recommendations, alerts, generated_at)
             VALUES (?, UUID_TO_BIN(?), UUID_TO_BIN(?), ?, 'ready', ?, ?, ?, ?, NOW())",
            [
                $binId,
                $sessionId,
                $session['company_id'],
                $reportType,
                json_encode($reportData, JSON_UNESCAPED_UNICODE),
                json_encode($scores, JSON_UNESCAPED_UNICODE),
                json_encode($recommendations, JSON_UNESCAPED_UNICODE),
                json_encode($alerts, JSON_UNESCAPED_UNICODE),
            ]
        );

        return Report::fromArray([
            'id' => $reportId,
            'session_id' => $sessionId,
            'company_id' => $session['company_id'],
            'report_type' => $reportType,
            'status' => 'ready',
            'report_data' => $reportData,
            'scores' => $scores,
            'recommendations' => $recommendations,
            'alerts' => $alerts,
            'generated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getReport(string $reportId): ?Report
    {
        $row = $this->db->fetch(
            "SELECT BIN_TO_UUID(id) as id, BIN_TO_UUID(session_id) as session_id,
                    BIN_TO_UUID(company_id) as company_id, report_type, status,
                    report_data, scores, recommendations, alerts,
                    generated_at, expires_at, created_at, updated_at
             FROM reports WHERE id = UUID_TO_BIN(?) AND deleted_at IS NULL",
            [$reportId]
        );

        return $row ? Report::fromArray($row) : null;
    }

    public function getReportsByCompany(string $companyId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT BIN_TO_UUID(id) as id, BIN_TO_UUID(session_id) as session_id,
                    BIN_TO_UUID(company_id) as company_id, report_type, status,
                    scores, generated_at, created_at
             FROM reports WHERE company_id = UUID_TO_BIN(?) AND deleted_at IS NULL
             ORDER BY created_at DESC",
            [$companyId]
        );

        return array_map(fn($row) => Report::fromArray($row), $rows);
    }

    public function exportReport(string $reportId, string $format = 'pdf'): array
    {
        $report = $this->getReport($reportId);
        if (!$report) {
            throw new NotFoundException('التقرير غير موجود');
        }

        return [
            'report_id' => $reportId,
            'format' => $format,
            'filename' => "report_{$reportId}.{$format}",
            'data' => $report->toArray(),
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    private function calculateScores(array $answers): array
    {
        $dimensions = [
            'digital' => 0,
            'marketing' => 0,
            'organizational' => 0,
            'risk' => 0,
            'opportunity' => 0,
        ];

        $counts = array_fill_keys(array_keys($dimensions), 0);

        foreach ($answers as $answer) {
            $value = is_numeric($answer['answer_value'] ?? null) ? (float) $answer['answer_value'] : 5.0;
            $normalized = min(100, ($value / 10) * 100);

            foreach ($dimensions as $dim => &$score) {
                $score += $normalized;
                $counts[$dim]++;
            }
        }

        foreach ($dimensions as $dim => &$score) {
            $score = $counts[$dim] > 0 ? round($score / $counts[$dim], 1) : 0;
        }

        $dimensions['overall'] = round(array_sum($dimensions) / max(count($dimensions), 1), 1);

        return $dimensions;
    }

    private function generateRecommendations(array $scores): array
    {
        $recommendations = [];

        if (($scores['digital'] ?? 0) < 50) {
            $recommendations[] = [
                'title' => 'تطوير البنية الرقمية',
                'description' => 'تحسين التواجد الرقمي وتبني أدوات التسويق الرقمي الحديثة',
                'priority' => 'high',
                'dimension' => 'digital',
            ];
        }

        if (($scores['marketing'] ?? 0) < 50) {
            $recommendations[] = [
                'title' => 'تحسين استراتيجية التسويق',
                'description' => 'وضع خطة تسويقية شاملة مع مؤشرات أداء قابلة للقياس',
                'priority' => 'high',
                'dimension' => 'marketing',
            ];
        }

        if (($scores['organizational'] ?? 0) < 50) {
            $recommendations[] = [
                'title' => 'تعزيز الجاهزية المؤسسية',
                'description' => 'بناء فريق تسويق متخصص وتطوير العمليات الداخلية',
                'priority' => 'medium',
                'dimension' => 'organizational',
            ];
        }

        if (($scores['risk'] ?? 0) < 50) {
            $recommendations[] = [
                'title' => 'إدارة المخاطر التسويقية',
                'description' => 'تطوير خطة لإدارة المخاطر وتنويع القنوات التسويقية',
                'priority' => 'medium',
                'dimension' => 'risk',
            ];
        }

        if (($scores['opportunity'] ?? 0) < 60) {
            $recommendations[] = [
                'title' => 'استغلال الفرص التسويقية',
                'description' => 'استكشاف قنوات جديدة مثل التجارة الإلكترونية والتسويق عبر المؤثرين',
                'priority' => 'medium',
                'dimension' => 'opportunity',
            ];
        }

        return $recommendations;
    }

    private function generateAlerts(array $scores): array
    {
        $alerts = [];

        foreach ($scores as $dimension => $score) {
            if ($dimension === 'overall') continue;

            if ($score < 25) {
                $alerts[] = [
                    'type' => 'critical',
                    'dimension' => $dimension,
                    'message' => "مستوى حرج في {$dimension}: يتطلب تدخل فوري",
                    'score' => $score,
                ];
            } elseif ($score < 40) {
                $alerts[] = [
                    'type' => 'warning',
                    'dimension' => $dimension,
                    'message' => "مستوى منخفض في {$dimension}: يحتاج تحسين عاجل",
                    'score' => $score,
                ];
            }
        }

        return $alerts;
    }

    private function getMaturityLevel(float $score): string
    {
        return match (true) {
            $score >= 90 => 'خبير',
            $score >= 75 => 'متقدم',
            $score >= 50 => 'متوسط',
            $score >= 25 => 'نامي',
            default => 'مبتدئ',
        };
    }
}
