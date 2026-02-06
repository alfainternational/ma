<?php

namespace App\API;

use App\Service\AssessmentService;
use Exception;

/**
 * Class ReportController
 * مسؤول عن استرجاع وتوليد التقارير النهائية.
 */
class ReportController {
    private AssessmentService $assessment;

    public function __construct() {
        $this->assessment = new AssessmentService();
    }

    /**
     * تشغيل التحليل النهائي وتوليد التقرير.
     */
    public function generateReport(string $sessionId): array {
        try {
            $analysis = $this->assessment->processSession($sessionId);
            
            return [
                'status' => 'success',
                'data' => $analysis,
                'message' => 'تم توليد التحليل والتقرير بنجاح'
            ];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
