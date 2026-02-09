<?php
/**
 * Pattern Detector - كاشف الأنماط
 * Pattern matching and anomaly detection
 */
class PatternDetector {
    private array $patterns = [];

    public function __construct() {
        $this->loadPatterns();
    }

    public function detectAll(array $answers, array $context): array {
        $answersMap = $this->mapAnswers($answers);
        return [
            'red_flags' => $this->detectRedFlags($answersMap),
            'green_flags' => $this->detectGreenFlags($answersMap),
            'anomalies' => $this->detectAnomalies($answersMap, $context['sector'] ?? ''),
        ];
    }

    public function detectRedFlags(array $answers): array {
        $flags = [];
        $revTrend = $answers['revenue_trend'] ?? '';
        $profitMargin = (float)($answers['profit_margin'] ?? 20);
        $churnRate = (float)($answers['churn_rate'] ?? 0);
        $budgetPercent = (float)($answers['marketing_budget_percent'] ?? 5);

        if ($revTrend === 'declining') $flags[] = ['flag' => 'declining_revenue', 'message' => 'إيرادات في انخفاض', 'severity' => 'critical'];
        if ($profitMargin < 5) $flags[] = ['flag' => 'low_profit_margin', 'message' => 'هامش ربح منخفض جداً', 'severity' => 'critical'];
        if ($churnRate > 30) $flags[] = ['flag' => 'high_churn', 'message' => 'معدل فقدان عملاء مرتفع', 'severity' => 'high'];
        if ($budgetPercent > 40) $flags[] = ['flag' => 'excessive_budget', 'message' => 'إنفاق تسويقي مفرط', 'severity' => 'high'];
        if (!in_array($answers['has_website'] ?? '', ['yes', 'نعم', '1', true], true)) {
            $flags[] = ['flag' => 'no_website', 'message' => 'لا يوجد موقع إلكتروني', 'severity' => 'high'];
        }
        if (($answers['competition_level'] ?? '') === 'very_high' && ($answers['differentiation'] ?? 5) < 3) {
            $flags[] = ['flag' => 'no_differentiation', 'message' => 'لا يوجد تميز واضح مع منافسة شديدة', 'severity' => 'high'];
        }

        return $flags;
    }

    public function detectGreenFlags(array $answers): array {
        $flags = [];
        $satisfaction = (float)($answers['customer_satisfaction'] ?? 0);
        $revTrend = $answers['revenue_trend'] ?? '';
        $nps = (float)($answers['nps_score'] ?? 0);

        if ($satisfaction > 8) $flags[] = ['flag' => 'high_satisfaction', 'message' => 'رضا عملاء ممتاز', 'impact' => 'positive'];
        if ($revTrend === 'growing') $flags[] = ['flag' => 'growing_revenue', 'message' => 'إيرادات في نمو', 'impact' => 'positive'];
        if ($nps > 50) $flags[] = ['flag' => 'high_nps', 'message' => 'مؤشر ولاء عملاء ممتاز', 'impact' => 'positive'];
        if (in_array($answers['data_driven_decisions'] ?? '', ['yes', 'نعم'], true)) {
            $flags[] = ['flag' => 'data_driven', 'message' => 'اتخاذ قرارات مبنية على البيانات', 'impact' => 'positive'];
        }

        return $flags;
    }

    public function detectAnomalies(array $answers, string $sector): array {
        $anomalies = [];
        $revenue = (float)($answers['annual_revenue'] ?? 0);
        $employees = (int)($answers['employee_count'] ?? 1);

        if ($employees > 0 && $revenue > 0) {
            $revenuePerEmployee = $revenue / $employees;
            if ($revenuePerEmployee > 2000000) {
                $anomalies[] = ['type' => 'high_revenue_per_employee', 'message' => 'إيراد مرتفع جداً لكل موظف', 'action' => 'verify_data'];
            }
            if ($revenuePerEmployee < 20000) {
                $anomalies[] = ['type' => 'low_revenue_per_employee', 'message' => 'إيراد منخفض جداً لكل موظف', 'action' => 'investigate'];
            }
        }

        return $anomalies;
    }

    public function matchPattern(string $patternId, array $data): bool {
        foreach ($this->patterns as $pattern) {
            if (($pattern['id'] ?? '') === $patternId) {
                $conditions = $pattern['conditions'] ?? [];
                foreach ($conditions as $condition) {
                    $field = $condition['field'] ?? '';
                    $operator = $condition['operator'] ?? '==';
                    $value = $condition['value'] ?? null;
                    $actual = $data[$field] ?? null;
                    if (!$this->evaluateCondition($actual, $operator, $value)) return false;
                }
                return true;
            }
        }
        return false;
    }

    public function loadPatterns(): void {
        $file = BASE_PATH . '/data/patterns.json';
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            $this->patterns = $data['patterns'] ?? $data ?? [];
        }
    }

    private function evaluateCondition($actual, string $operator, $expected): bool {
        if ($actual === null) return false;
        return match ($operator) {
            '==' => $actual == $expected,
            '!=' => $actual != $expected,
            '>' => (float)$actual > (float)$expected,
            '<' => (float)$actual < (float)$expected,
            '>=' => (float)$actual >= (float)$expected,
            '<=' => (float)$actual <= (float)$expected,
            'in' => is_array($expected) && in_array($actual, $expected),
            default => false,
        };
    }

    private function mapAnswers(array $answers): array {
        $map = [];
        foreach ($answers as $a) {
            $key = $a['question_id'] ?? '';
            $map[$key] = $a['answer_value'] ?? null;
            if (isset($a['field_mapping'])) $map[$a['field_mapping']] = $a['answer_value'] ?? null;
        }
        return $map;
    }
}
